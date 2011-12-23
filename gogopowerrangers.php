<?php

require_once("/path/to/jobmine_notifer/includes/config.php");
require_once($website_dir."/includes/useful_funcs.php");

$hour = date('G');
$day = date('j');
$month = date('n');
$year = date('Y');

$log_check_query = @mysql_query('SELECT * FROM `log` WHERE `year` = '.$year.' AND `month` = '.$month.' AND `day` = '.$day.' AND `hour` = '.$hour.';');
$user_check_query = @mysql_query('SELECT * FROM `log` WHERE `type` = \'new\' AND `year` = '.$year.' AND `month` = '.$month.' AND `day` = '.$day.' AND `hour` = '.$hour.';');

// for tracking
if(@mysql_num_rows($log_check_query) <= 1) {
  $dau = @mysql_num_rows(@mysql_query('SELECT * FROM `users` WHERE `active` = 1'));
  $iau = @mysql_num_rows(@mysql_query('SELECT * FROM `users`'));
  $tj = @mysql_num_rows(@mysql_query('SELECT * FROM `apps`'));
  $aj = @mysql_num_rows(@mysql_query('SELECT * FROM `apps` WHERE `allow_update` = 1'));
  $uj = @mysql_num_rows(@mysql_query('SELECT DISTINCT `job_id` FROM `apps`'));

  // tracking
  @mysql_query('INSERT INTO `log` (`year`, `month`, `day`, `hour`, `type`) VALUES ('.$year.', '.$month.', '.$day.', '.$hour.', "nselect");');
  @mysql_query('INSERT INTO `log` (`year`, `month`, `day`, `hour`, `type`) VALUES ('.$year.', '.$month.', '.$day.', '.$hour.', "select");');
  @mysql_query('INSERT INTO `log` (`year`, `month`, `day`, `hour`, `type`) VALUES ('.$year.', '.$month.', '.$day.', '.$hour.', "sms");');
  @mysql_query('INSERT INTO `log` (`year`, `month`, `day`, `hour`, `type`) VALUES ('.$year.', '.$month.', '.$day.', '.$hour.', "email");');

  // counts
  @mysql_query('INSERT INTO `log` (`year`, `month`, `day`, `hour`, `type`, `count`) VALUES ('.$year.', '.$month.', '.$day.', '.$hour.', "dau", '.$dau.');');
  @mysql_query('INSERT INTO `log` (`year`, `month`, `day`, `hour`, `type`, `count`) VALUES ('.$year.', '.$month.', '.$day.', '.$hour.', "iau", '.$iau.');');
  @mysql_query('INSERT INTO `log` (`year`, `month`, `day`, `hour`, `type`, `count`) VALUES ('.$year.', '.$month.', '.$day.', '.$hour.', "tj", '.$tj.');');
  @mysql_query('INSERT INTO `log` (`year`, `month`, `day`, `hour`, `type`, `count`) VALUES ('.$year.', '.$month.', '.$day.', '.$hour.', "aj", '.$aj.');');
  @mysql_query('INSERT INTO `log` (`year`, `month`, `day`, `hour`, `type`, `count`) VALUES ('.$year.', '.$month.', '.$day.', '.$hour.', "uj", '.$uj.');');
}

// create new row
if(@mysql_num_rows($user_check_query) == 0) {
    @mysql_query('INSERT INTO `log` (`year`, `month`, `day`, `hour`, `type`) VALUES ('.$year.', '.$month.', '.$day.', '.$hour.', "new");');
}

// grab all user info
$get_query_raw = "SELECT `id` AS id, `username` AS name, AES_DECRYPT(`password`, '".$secret_key."') AS pass, `email` AS email, `app_check` AS app, `phone` AS phone, `active` as active, `override` as override, `interview_only` as interview_only FROM `users`";
$get_query = mysql_query($get_query_raw) or die(mysql_error());

// let's check all users
while($user = mysql_fetch_array($get_query)) {
  // skip user if opted out
  if($user['active'] == 0) {
    continue;
  }

  // retrieve application page
  $jobmine = jobMineMe($user['name'], $user['pass']);

  // skip parsing this user's applications if problem with the page
  if(!$jobmine) {
    continue;
  }

  // take an md5 hash of the page to optimize comparisons
  $app_page = md5NoSession($jobmine);

  if($app_page != $user['app']) {
    // some variables
    $stored_apps = array();
    $changed_apps = array();
    $new_apps = array();
    $interview_count = 0;

    // set flag for sms
    $send_sms = false;

    // parse the page
    $parsed_apps = parsePage($jobmine);

    // check stored applications
    $get_apps_raw = "SELECT `job_id` AS id, `job_status` AS status, `app_status` AS app, `allow_update` AS allowed FROM `apps` WHERE `user_id` = ".$user['id'];
    $get_apps_query = mysql_query($get_apps_raw) or die(mysql_error());

    // grab the apps stored in the db
    if(mysql_num_rows($get_apps_query) > 0) {
      while($app = mysql_fetch_array($get_apps_query)) {
        $stored_apps[(int)$app['id']] = array(
          'allowed' => (int)$app['allowed'],
          'status'=> $app['status'], 
          'app'	=> $app['app']
        );
      }
    }

    // go through parsed page
    foreach($parsed_apps as $key => $value) {

      // encoding issue hack
      if(strlen($value['app_status']) < 3) {
        $value['app_status'] = "";
      }
      
      if($value['app_status'] == 'Employed' && $user['override'] == 0) {
        mysql_query("UPDATE `users` SET `active` = 0 WHERE `id` = ".$user['id']);
        mysql_query("UPDATE `apps` SET `allow_update` = 0 WHERE `user_id` = ".$user['id']);
      }	

      // no need to notify users any further after these cases
      if($value['job_status'] == 'Cancelled' || ($value['job_status'] == 'Filled' && $value['app_status'] != 'Employed') ) {
        $js_disallow = " , `allow_update` = 0 ";
        $as_disallow = "";
        $allow_update = 0;
        // second case here is really obscure... it seems to happen when an application goes straight from available to ranking completed
      } else if($value['app_status'] == 'Not Ranked' || $value['app_status'] == 'Not Selected' || $value['app_status'] == 'Sign Off' || ($value['app_status'] == "" && $value['job_status'] == 'Ranking Completed')) {
        $as_disallow = " , `allow_update` = 0 ";
        $js_disallow = "";
        $allow_update = 0;
      } else {
        $as_disallow = $js_disallow = "";
        $allow_update = 1;
      }

      if(array_key_exists($key, $stored_apps)) {
        // skip over cases listed above
        if($stored_apps[$key]['allowed'] == 0) {
          continue;
          // update this case but don't notify
        } else if($stored_apps[$key]['status'] == 'Posted' && $value['job_status'] == 'Applications Available') {
          $js_update_query_raw = sprintf("UPDATE `apps` SET `job_status` = '%s' WHERE `user_id` = ".$user['id']." AND `job_id` = ".$key, $value['job_status']);
          mysql_query($js_update_query_raw) or die(mysql_error());

          continue;
        }

        // mark down changes for job status & update db
        if($value['job_status'] != $stored_apps[$key]['status']) {
          if($user['interview_only'] == 0) {
            $changed_apps[] = array('employer' => $value['employer'], 'title' => $value['title'], 'old' => $stored_apps[$key]['status'], 'new' => $value['job_status']);
          }
          $js_update_query_raw = sprintf("UPDATE `apps` SET `job_status` = '%s' ".$js_disallow." WHERE `user_id` = ".$user['id']." AND `job_id` = ".$key, $value['job_status']);
          mysql_query($js_update_query_raw) or die(mysql_error());
        }

        // mark down changes for application status & update db
        if($value['app_status'] != $stored_apps[$key]['app']) {
          if($user['interview_only'] == 0 || ($user['interview_only'] == 1 && $value['app_status'] == 'Selected')) {
            $changed_apps[] = array('employer' => $value['employer'], 'title' => $value['title'], 'old' => $stored_apps[$key]['app'], 'new' => $value['app_status']);
          }
          $as_update_query_raw = sprintf("UPDATE `apps` SET `app_status` = '%s' ".$as_disallow." WHERE `user_id` = ".$user['id']." AND `job_id` = ".$key, $value['app_status']);
          mysql_query($as_update_query_raw) or die(mysql_error());

          // tracking
          if($value['app_status'] == 'Not Selected') {
            @mysql_query('UPDATE `log` SET `count` = `count` + 1 WHERE `type` = "nselect" AND `year` = '.$year.' AND `month` = '.$month.' AND `day` = '.$day.' AND `hour` = '.$hour.';');
          }

          // check for interviews
          if($value['app_status'] == "Selected") {
            // tracking
            @mysql_query('UPDATE `log` SET `count` = `count` + 1 WHERE `type` = "select" AND `year` = '.$year.' AND `month` = '.$month.' AND `day` = '.$day.' AND `hour` = '.$hour.';');
            
            if($user['phone'] != NULL && $user['phone'] != "") {
              // setup sms
              $send_sms = true;
            }

            $interview_count++;
          }
        }
      } else {
        // add new interviews to separate array
        if($value['app_status'] == 'Selected') {
          $new_apps[] = array('employer' => $value['employer'], 'title' => $value['title'], 'new' => $value['app_status']);

          // tracking
          @mysql_query('UPDATE `log` SET `count` = `count` + 1 WHERE `type` = "select" AND `year` = '.$year.' AND `month` = '.$month.' AND `day` = '.$day.' AND `hour` = '.$hour.';');

          // check for interviews
          if($user['phone'] != NULL && $user['phone'] != "") {
            // trigger flag for sms
            $send_sms = true;
          }

          $interview_count++;
        }

        // add to db
        $insert_query_raw = "INSERT INTO `apps` (`user_id`, `job_id`, `job_status`, `app_status`, `allow_update`) VALUES ('".$user['id']."', '".$key."', '".$value['job_status']."', '".$value['app_status']."', '".$allow_update."')";
        mysql_query($insert_query_raw) or die(mysql_error());
      }
    }

    // remove old entries
    foreach($stored_apps as $key => $value) {
      if(!array_key_exists($key, $parsed_apps)) {
        $delete_query_raw = "DELETE FROM `apps` WHERE `user_id` = ".$user['id']." AND `job_id` = ".$key;
        mysql_query($delete_query_raw) or die(mysql_error());
      }
    }

    // send an e-mail notification/sms if there are any status changes
    if(count($changed_apps) > 0) {
      $subject = '';

      if($interview_count > 0) {
        $subject .= $interview_count == 1 ? "[$interview_count NEW INTERVIEW!] " : "[$interview_count NEW INTERVIEWS!] ";
      }

      $subject .= "JobMine Notification for {$user['name']}!";
      $body = "This is to notify you that your JobMine application list has been modified. A change summary has been included below:\n\n";

      // construct the rest of the body based on the status changes
      foreach($new_apps as $app) {
        $body .= sprintf("%s - %s\nApplication was newly added. Application status is set to '%s'.\n\n", $app['employer'], $app['title'], $app['new']);
      }

      foreach($changed_apps as $app) {
        $body .= sprintf("%s - %s\nStatus changed from '%s' to '%s'.\n\n", $app['employer'], $app['title'], $app['old'], $app['new']);
      }

      $body .= "Please log in to JobMine at https://jobmine.ccol.uwaterloo.ca/psp/SS/?cmd=login for more details.\n\n-- The JobMine Interview Notifier Team";

      // tracking
      @mysql_query('UPDATE `log` SET `count` = `count` + 1 WHERE `type` = "email" AND `year` = '.$year.' AND `month` = '.$month.' AND `day` = '.$day.' AND `hour` = '.$hour.';');

      // try sending email - if it fails, try again
      if(!phpMailerSend($user['email'], $subject, $body)) {
        phpMailerSend($user['email'], $subject, $body);
      }

      // now send sms
      if($send_sms) {
        sendSms($user['phone'], $interview_count);

        // tracking
        @mysql_query('UPDATE `log` SET `count` = `count` + 1 WHERE `type` = "sms" AND `year` = '.$year.' AND `month` = '.$month.' AND `day` = '.$day.' AND `hour` = '.$hour.';') or die(mysql_error());

        $sms_update_query_raw = sprintf("UPDATE `users` SET `sms_count` = `sms_count`+1 WHERE `username` = '%s'", $user['name']);
        mysql_query($sms_update_query_raw) or die(mysql_error());
      }
    }

    // update the md5 hash in the database to reflect the changes noted above
    $app_update_query_raw = sprintf("UPDATE `users` SET `app_check` = '%s' WHERE `username` = '%s'", $app_page, $user['name']);
    mysql_query($app_update_query_raw) or die(mysql_error());
  }
}

?>
