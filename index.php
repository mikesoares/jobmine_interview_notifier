<?php

$ip = $_SERVER['REMOTE_ADDR'];

// block CECS from accessing the site
if(strpos(gethostbyaddr($ip), "cecs") !== false) {
  header("HTTP/2.0 404 Not Found");
  echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
    <html><head>
    <title>404 Not Found</title>
    </head><body>
    <h1>Not Found</h1>
    <p>The requested URL / was not found on this server.</p>
    <hr>
    <address>Apache/2.2.3 (CentOS) Server at '.$server_hostname.' Port 443</address>
    </body></html>';
  exit;
}

  require_once("includes/config.php");

  echo $header;
  echo $mage_title;
?>

        <p>Enter your JobMine login details below and an e-mail address<br />to setup or reset automated interview notifications (if any).</p>
        <p><small>By filling out and submitting this form, you understand and acknowledge that your credentials will be <strong>encrypted and stored</strong> in our database for the <strong>sole purpose</strong> of sending you automated notifications without your interaction. You also agree not to hold <?php echo $your_full_name; ?> of <?php echo $uw_affiliation; ?> responsible, directly or indirectly, for anything that may happen, apart from the receipt of automated notifications, as a result of using this service. <?php echo $your_full_name; ?> agrees not to decrypt, release, and/or use the information you submit below, except where mentioned above.</small></p>
<p style="color: red"><small>NOTE: Notifications may be slightly delayed while we try to deal with the increased demand of this service.</small></p>
        <form action="validate.php" method="post" autocomplete="off">
          <div>
            <div class="form_cont">
              <div class="options">					
                JobMine User ID:<br /> 
                Password:<br />
                E-Mail:<br />
                Mobile Phone:<br />
                Only send me interview notifications:<br />
                Please do not send me <br />updates/notices about this service:<br />
                Override <em>Employed</em> <span class="red">*</span>: 
              </div>
              <div class="inputs">
                <input type="text" name="userid" /><br />
                <input type="password" name="pwd" /><br />
                <input type="text" name="email" /><br />
                <input type="text" name="phone" /><br />
                <input type="checkbox" name="interview_only" value="1" /><br /><br />
                <input type="checkbox" name="optout" value="1" /><br />
                <input type="checkbox" name="override" value="1" />
              </div>
            </div>
            <div class="clear">
              <small><span class="red">*</span>Check this box if you have any old applications that are marked as <em>Employed</em> or if you aren't receiving any notifications.</small><br />
              <input type="submit" value="submit" />
            </div>
          </div>
        </form> 
<?php echo $footer; ?>
