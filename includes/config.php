<?php

// only configuration here ---------------------------
$website_dir = "/path/to/jobmine_notifier/";

$db_name = "somedbname";
$db_host = "127.0.0.1";
$db_user = "someusername";
$db_pass = "somedbpass";
$secret_key = "somesecretkey";

$mailer_host = "some.host.com";
$mailer_user = "someuser@host.com";			// EX: jmnotifier@ispeakofcake.com
$mailer_pass = "somepassword";

$tropo_api_token = "sometoken";

$homepage = "https://my.homepage.com/";		// EX: https://jobmine.ispeakofcake.com
$server_hostname = "my.homepage.com";		// EX: jobmine.ispeakofcake.com

$your_full_name = "My Full Name";
$uw_affiliation = "2012 Computer Engineering";
// stop here -----------------------------------------

$link = mysql_connect($db_host, $db_user, $db_pass);
if (!$link) {
	die('Could not connect to database: ' . mysql_error());
} else {
	mysql_select_db($db_name, $link);
}

$header = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <title>JobMine Interview Notifier (BETA)</title>
                <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
                <meta name="Keywords" content="jobmine interview notifier, university of waterloo, awesome" />
                <link rel="stylesheet" href="css/site.css" type="text/css" media="screen" />
        </head>
        <body>
                <div class="container">
                        <div class="content">
EOF;

$mage_title = <<<EOF
<h1>JobMine Interview Notifier (BETA)</h1>
<img src="minejobs.jpg" width="400" height="300" alt="I will mine ALL the jobs." />
EOF;

$back_button = <<<EOF
<p>Click <a href="$homepage" title="Go Back">here</a> to return to the homepage.</p>
EOF;

$footer = <<<EOF
       </body>
</html>
EOF;

?>
