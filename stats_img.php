<?php

require_once('includes/config.php');
require_once('jpgraph/jpgraph.php');
require_once('jpgraph/jpgraph_line.php');

$all = isset($_GET['all']) && $_GET['all'] == 'true' ? true : false;
$type = isset($_GET['type'])?$_GET['type']:0;

switch($type) {
  case 1:
    $column = array('select');
    $text = 'Interviews by Hour';
    break;
  case 2:
    $column = array('nselect');
    $text = 'Rejections by Hour';
    break;
  case 3:
    $column = array('email');
    $text = 'E-Mail Notifications by Hour';
    break;
  case 4:
    $column = array('sms');
    $text = 'SMS Notifications by Hour';
    break;
  case 5:
    $column = array('new');
    $text = 'New Users by Hour';
    break;
  case 6:
    $column = array('iau', 'dau');
    $text = 'Users by Hour';
    $legend = array('Total Users', 'Hourly Active Users');
    break;
  case 7:
    $column = array('tj', 'aj', 'uj');
    $text = 'Jobs by Hour';
    $legend = array('Total Jobs Tracked', 'Actively Tracked Job Applications', 'Unique Jobs Tracked');
//    $fill = true;
    break;
  default:
    die();
}

if(is_array($column)) {
  $queries = array();
  $graphs = array();

  foreach($column as $col) {
    if($all) {
      $queries[] = mysql_query("SELECT * FROM  `log` WHERE `type` = '$col' ORDER BY `log_id` ASC") or die(mysql_error());
    } else {
      $queries[] = mysql_query("SELECT * FROM (SELECT * FROM `log` WHERE `type` = '$col' ORDER BY `log_id` DESC LIMIT 80) as `tbl` ORDER BY `tbl`.`log_id` ASC") or die(mysql_error());
    }
    $graphs[] = array('x' => array(), 'y' => array());
  }

  for($i = 0; $i < count($queries); $i++) {
    if(@mysql_num_rows($queries[$i]) > 0) {
      while($row = @mysql_fetch_assoc($queries[$i])) {
        $graphs[$i]['x'][] = $row['hour'];
        $graphs[$i]['y'][] = $row['count'];
      }
    } else {
      die();
    }
  }
  
  $ngraph = null;

  if($all) {
    $ngraph = new Graph(1600, 1200); 
  } else {
    $ngraph = new Graph(800, 600); 
  }

  $ngraph->SetScale("textint");
  $theme_class = new UniversalTheme;

  $ngraph->SetTheme($theme_class);
  $ngraph->title->Set($text);
  $ngraph->SetBox(false);

  $ngraph->yaxis->HideZeroLabel();
  $ngraph->yaxis->HideLine(false);
  $ngraph->yaxis->HideTicks(false, false);

  $ngraph->xgrid->Show();
  $ngraph->xgrid->SetLineStyle("solid");
  $ngraph->xaxis->SetTickLabels($graphs[0]['x']);
  $ngraph->xgrid->SetColor('#E3E3E3');

  for($i = 0; $i < count($graphs); $i++) {
    $lines[$i] = new LinePlot($graphs[$i]['y']);
    $ngraph->Add($lines[$i]);
    
    if(isset($legend)) {
      $lines[$i]->SetLegend($legend[$i]);
    }

    if(isset($fill)) {
      $lines[$i]->SetFilled($fill);
    }
  }
  
  $ngraph->legend->SetFrameWeight(1);

  // Output line
  $ngraph->Stroke();
}
