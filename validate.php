<?php

require_once("includes/config.php");
require_once("includes/useful_funcs.php");

if($_POST) {
  echo $header;

  $userid = isset($_POST['userid'])?safeSql($_POST['userid']):'';
  $password = isset($_POST['pwd'])?safeSql($_POST['pwd']):'';
  $email = isset($_POST['email'])?safeSql($_POST['email']):'';
  $phone = isset($_POST['phone'])?safeSql($_POST['phone']):'';
  $interview_only = isset($_POST['interview_only']) && $_POST['interview_only'] === "1"?1:0;
  $override = isset($_POST['override']) && $_POST['override'] === "1"?1:0;
  $optout = isset($_POST['optout']) && $_POST['optout'] === "1"?1:0;

  if($userid != '' && $password != '' && $email != '' && validEmail($email)) {
    $check_query_raw = sprintf("SELECT `username` FROM `users` WHERE `username` = '%s'", $userid);
    $check_query = mysql_query($check_query_raw) or die(mysql_error());

    $jobmine = jobMineMe($userid, $password);

    if($phone != '') {
      $phone = validate_phone($phone);
      if(!$phone) {
        echo $mage_title;
        echo "<p>Your phone number was incorrectly formatted.<br />Please go back and try again.</p>";
        echo $back_button;
        echo $footer;
        exit;
      }
    }

    if(!$jobmine) {
      echo $mage_title;
      echo "<p>We couldn't verify your JobMine account at this time.<br />Please make sure your username and password are correct, <br />that you are signing up while JobMine is open, and try again.</p>";
      echo $back_button;
      echo $footer;
      exit;
    }
 
    // parse the page
    $parsed_apps = parsePage($jobmine);

    // reset if the username is already in the db
    if(mysql_num_rows($check_query) > 0) {
      $reset_query = mysql_query(sprintf("DELETE `apps`.* FROM `apps` LEFT JOIN `users` ON `users`.`id` = `apps`.`user_id` WHERE `users`.`username` = '%s'", $userid)) or die(mysql_error());
      $insert_query_raw = sprintf("UPDATE `users` SET `password` = AES_ENCRYPT('%s', '%s'), `active` = 1, `override` = %d, `interview_only` = %d, `optout` = %d, `app_check` = '%s', `email` = '%s', `phone` = '%s'  WHERE `username` = '%s'", $password, $secret_key, $override, $interview_only, $optout, md5NoSession($jobmine), $email, $phone, $userid);
      $subject = "JobMine Interview Notifier Successfully Reset!";
      $body = sprintf("Thank you for your interest in the JobMine Interview Notifier.  Notifications for your account, %s, have been reset successfully.  Please note that because this web application is still in the process of being tested, you should not solely rely on its notifications to know if you have an interview or not.\n\nThanks for your understanding and happy JobMining!", $userid);
    } else {
      $insert_query_raw = sprintf("INSERT INTO `users` (`username`, `password`, `email`, `phone`, `app_check`, `override`, `interview_only`, `optout`) VALUES ('%s', AES_ENCRYPT('%s', '%s'), '%s', '%s', '%s', %d, %d, %d)", $userid, $password, $secret_key, $email, $phone, md5NoSession($jobmine), $override, $interview_only, $optout);

      $subject = "JobMine Interview Notifier Successfully Configured!";
      $body = sprintf("Thank you for your interest in the JobMine Interview Notifier.  Notifications for your account, %s, have been setup successfully.  Please note that because this web application is still in the process of being tested, you should not solely rely on its notifications to know if you have an interview or not.\n\nThanks for your understanding and happy JobMining!", $userid);
    }

    $hour = date('G');
    $day = date('j');
    $month = date('n');
    $year = date('Y');

    // tracking
    $log_check_query = @mysql_query('SELECT * FROM `log` WHERE `type` = "new" AND `year` = '.$year.' AND `month` = '.$month.' AND `day` = '.$day.' AND `hour` = '.$hour.';');

    if(@mysql_num_rows($log_check_query) == 0) {
      @mysql_query('INSERT INTO `log` (`year`, `month`, `day`, `hour`, `type`) VALUES ('.$year.', '.$month.', '.$day.', '.$hour.', "new");');
    }

    @mysql_query('UPDATE `log` SET `count` = `count` + 1 WHERE `type` = "new" AND `year` = '.$year.' AND `month` = '.$month.' AND `day` = '.$day.' AND `hour` = '.$hour.';');

    if(!phpMailerSend($email, $subject, $body)) {
      $output_message = "Your account could not be configured correctly at this time.<br />Please try again later.";
    } else {
      // add to db
      $insert_query = mysql_query($insert_query_raw) or die(mysql_error());

      // get user id from db
      $get_id_query_raw = sprintf("SELECT `id` AS id, `username` AS name FROM `users` WHERE `username` = '%s'", $userid);
      $get_id_query = mysql_query($get_id_query_raw) or die(mysql_error());

      // grab user info
      $user = mysql_fetch_array($get_id_query);

      // add initial entries
      foreach($parsed_apps as $key => $value) {
        // no need to notify users in these cases
        if($value['job_status'] == 'Cancelled' || $value['app_status'] == 'Not Selected' || $value['app_status'] == 'Sign Off' || (strlen($value['app_status']) < 3 && $value['job_status'] == 'Ranking Completed') || ($value['job_status'] == 'Filled' && $value['app_status'] != 'Employed')) {
          $allow_update = 0;
        } else {
          $allow_update = 1;
        }

        // encoding issue hack
        if(strlen($value['app_status']) < 3) {
          $value['app_status'] = "";
        }

        // add to db
        $insert_query_raw = "INSERT INTO `apps` (`user_id`, `job_id`, `job_status`, `app_status`, `allow_update`) VALUES ('".$user['id']."', '".$key."', '".$value['job_status']."', '".$value['app_status']."', '".$allow_update."')";
        $insert_query = mysql_query($insert_query_raw) or die(mysql_error());
      }

      $output_message = sprintf("Thank you for your interest in the JobMine Interview Notifier (BETA).<br />A confirmation e-mail has been sent to %s.<br /><strong>Please check your spam/junk folder if it does not appear in your inbox.</strong><br />Be sure to whitelist our e-mail address (%s).", $email, $mailer_user);
    }
  } else {
    $output_message = "One or more of the fields you filled out were empty or not properly formatted.<br />Please go back and try again.";
  }

  echo $mage_title;
?>
  <p><?php echo $output_message; ?></p>
<?php	
  echo $back_button;
  echo $footer;

} else {
  homepageRedirect();
}

?>
