<?php
date_default_timezone_set('UTC');
function parse_get($dirty){
  $clean = htmlspecialchars($dirty); 
  return strip_tags($clean);
}

function getFile($period){
  $indir=array_filter(scandir("../data"),function($item){
    return is_file("../data/" . $item);
  });
  foreach($indir as $key=>$value) {
    if ($value !=="^." && $value !==".."){
      @preg_match_all("/\d+-\d+-\d+/", $value, $matches); // Get date from filename
      list($startString,$endString)=$matches[0];         // Get start and end date dd-mm-yyyy
      date_default_timezone_set('UTC');
      $s=date_parse_from_format("d-m-Y",$startString);
      $e=date_parse_from_format("d-m-Y",$endString);
      $start_ts = mktime(0,0,0,$s['month'],$s['day'],$s['year']);
      $end_ts = mktime(23,59,59,$e['month'],$e['day'],$e['year']);
      $now_ts = $period;
      $tmp = time();
      if (($now_ts >= $start_ts) && ($now_ts <= $end_ts)){
        return $value;
      } else {
        settype($period,"integer");
        $p=date("d-m-Y",$period);
      }
    } else {
        error_log("Failed");
        return false;
    }
  }
}

function sendallupdate(){
  include_once("functions.php");
  $file = getFile(parse_get($_GET['period']));
  $period = parse_get($_GET['period']);
  $rota = parse_get($_GET['rota']);
  email_rota($file,$period,$rota,null,"multi","Recently Changed");
}

$request = parse_get($_GET['request']);
if (function_exists($request)) {
  call_user_func($request);
} else {
  error_log("Invalid request $request");
}
?>
