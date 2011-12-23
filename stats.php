<?php
$all = isset($_GET['all']) && $_GET['all'] == 'true' ? '&all=true' : '';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
    <title>JobMine Interview Notifier Stats</title>
    <style type="text/css">
      body {
        font-family: "Lucida Grande", Tahoma, Verdana, Helvetica;
      }
    </style>
  </head>
  <body>
    <h1>JobMine Interview Notifier Stats</h1>
    <h2>Last 5 Days</h2>
    <img src="/stats_img.php?type=1<?=$all?>" />
    <img src="/stats_img.php?type=2<?=$all?>" />
    <img src="/stats_img.php?type=3<?=$all?>" />
    <img src="/stats_img.php?type=4<?=$all?>" />
    <img src="/stats_img.php?type=5<?=$all?>" />
    <img src="/stats_img.php?type=6<?=$all?>" />
    <img src="/stats_img.php?type=7<?=$all?>" />
  </body>
</html>
