<?php

date_default_timezone_set('UTC');

function alog($who, $what, $level) {

}


function parse_get($dirty){
  $clean = htmlspecialchars($dirty); 
  return strip_tags($clean);
}

function getFile($period){
  try {
    $indir=array_filter(scandir("data"),function($item){
      return is_file("data/" . $item);
    });
  
    foreach($indir as $key=>$value) {
      if ($value !=="^." && $value !==".."){
        @preg_match_all("/\d+-\d+-\d+/", $value, $matches); // Get date from filename
        @list($startString,$endString)=array_pad($matches[0], 2, null);         // Get start and end date dd-mm-yyyy
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
          $p=date("d-m-Y",$period);
        }
      } else {
          error_log("Failed");
          return false;
      }
    }
  }
  
  catch(Exception $e) {
    error_log("Exception Raised");
    error_log("Error ".$e->getMessage());
  }
}

function getJson(){
  if (isset($_GET['period'])) {
    $file = getFile($_GET['period']);
  } else {
    $file = getFile(time());
  }

  if(is_file("data/$file")){
    $json_string = file_get_contents("data/$file");
    $json_a = json_decode($json_string, true);
    $people = $json_a['people'];
    // Sort the people array, natural cmp
    uasort($people, function($item1, $item2){
      return strnatcmp($item1["name"] , $item2["name"]);
    });
    $json_a['people'] = $people;
    echo json_encode($json_a);
  }
}

function removePerson() {
  if (isset($_GET['person']) and isset($_GET['period']) and isset($_GET['days'])) {
    $file = getFile($_GET['period']);
    $name = $_GET['person'];
    $json=file_get_contents("data/$file");
    $json_a= json_decode($json,true);
    $array = array();

    foreach ($json_a['people'] as $key=>$val){
      if ($name!=$val['name']){
        $array[$key]=$val;
      }
    }
    $json_a['people'] = $array;
    $fp = fopen("data/$file", 'w');
    fwrite($fp, json_encode($json_a));
    fclose($fp);
    getJson();
  }
}

function addPerson() {
  if (isset($_GET['person']) and isset($_GET['period']) and isset($_GET['days'])) {
    $name = parse_get($_GET['person']);
    $file = getFile($_GET['period']);
    $json=file_get_contents("data/$file");
    $json_a= json_decode($json,true);
    $array = array();

    foreach ($json_a['people'] as $key=>$val){
      $array[$key]=$val;
    }
    $days=array();   
    for($n=0;$n<$_GET['days'];$n++){
      $day[$n]=0;
    }

    $data=array( "booked"=>$day,"totalDays"=>"0","totalHoliday"=>"0");
    $new=array( "name"=>"$name","data"=>$data);
    array_push($array,$new);
    $json_a['people'] = $array;
    $fp = fopen("data/$file", 'w');
    fwrite($fp, json_encode($json_a));
    fclose($fp);
    getJson();
  }
}

function savePerson() {
  if (isset($_GET['person']) and isset($_GET['period']) and isset($_GET['i']) and isset($_GET['d'])) {
    $who = parse_get($_GET['person']);
    $period = parse_get($_GET['period']);
    $index = parse_get($_GET['i']);
    $value = parse_get($_GET['d']);
    $file = getFile($_GET['period']);

    $log_data = [$period, $index, $value, $file ];
    alog($who, $log_data , "LOG");

    $json=file_get_contents("data/$file");
    $json_a= json_decode($json,true);
    $array = array();
    $peopleidx=0;
    foreach ($json_a['people'] as $key=>$val){
      if ($val['name'] == $who) {
        $peopleidx=$key;
        foreach ($json_a['people'][$key]['data']['booked'] as $k => $v) {
          if ($k==$index) {
            $array[$k]=$value;
          } else {
            $array[$k]=$v;
          }
        }
        $json_a['people'][$peopleidx]['data']['booked'] = $array;
        $perms = substr(sprintf('%o', fileperms(DATA_PATH.$file)), -4);
        error_log($perms);
	$fp = fopen("data/$file", 'w');
	fwrite($fp, json_encode($json_a));
	fclose($fp);
      }
    }
  }
}

function saveComments() {
  error_log("saveComments".$_GET['period']);
  if (isset($_GET['person']) and isset($_GET['period']) and isset($_GET['d']) and isset($_GET['days'])) {
    $who = parse_get($_GET['person']);
    $period = parse_get($_GET['period']);
    $value = $_GET['d'];
    $days = parse_get($_GET['days']);
    $file = getFile($period);
  //  if(is_file("data/$file")){
   //   $json=file_get_contents("data/$file");
    //  $json_a= json_decode($json,true);
    //} else {
    //  error_log("No $file");
   // }
    /*$array = array();
    $peopleidx=0;
    $new = $value;
    for ($d=0;$d++;$d<$days){
       error_log("$d");
       foreach($new['id'] as $key=>$val){
 
          error_log("Id $d "+$val);
          if ($key == $d){
             error_log("Id $d "+$val);
          }
       }
    }
/*    foreach ($json_a['people'] as $key=>$val){
      if ($val['name'] == $who) {
        $peopleidx=$key;
        foreach ($json_a['people'][$key]['data']['booked'] as $k => $v) {
          if ($k==$index) {
            $array[$k]=$value;
          } else {
            $array[$k]=$v;
          }
        }
        $json_a['people'][$peopleidx]['data']['booked'] = $array;
        file_put_contents("data/$file", json_encode($json_a));
      }
    } */
  }
}

function getPeople(){
   // Need to fitler based on rota
  if (isset($_GET['rota'])) {
    $addr=Array(); $i=-1;
    $rota = parse_get($_GET['rota']);
    include_once 'inc/db_connect.php';
    if ($stmt = $mysqli->prepare("SELECT username FROM members")){
      $stmt->execute();
      $results = $stmt->bind_result($user);
      #$num_of_row = $results->num_rows;
      #error_log("Rows: $num_of_row");
      #error_log("Rows: $results");

      while($stmt->fetch()){
	  //error_log("Error: $user");
          $addr['people'][++$i]['name'] = $user;
      }
      $stmt->free_result();
      $stmt->close();
    }
     
    echo json_encode($addr);
  }
}

function email() {
  include_once("email/functions.php");
  $file = getFile(parse_get($_GET['period']));
  $period = parse_get($_GET['period']);
  $rota = parse_get($_GET['rota']);
  $user = parse_get($_GET['user_id']);
  $type = parse_get($_GET['type']);
  email_rota($file,$period,$rota,$user,$type, "Rota Request");
}

function email_monthly(){
  include_once("email/functions.php");
  $file = getFile(parse_get($_GET['period']));
  $rota = parse_get($_GET['rota']);
  $user = parse_get($_GET['user_id']);
  $type = parse_get($_GET['type']);
  email_monthly_rota($file,$period,$rota,$user_id,$type);
}

function createPeriod() {
  // Create new period
  if (isset($_GET['period']) and isset($_GET['days']) and isset($_GET['who'])) {
    $period = parse_get($_GET['period']);
    $days = parse_get($_GET['days']);
    $start = parse_get($_GET['period']);
    $end = $start+(($days-2)*24*60*60);
    
    $s=date("d-m-Y",$start);
    $e=date("d-m-Y",$end);
    $file = "rota_${s}-${e}.json";
    $array = array();
    $who = json_decode($_GET['who'],true);
    $persons=Array();
    $persons['title'] = sprintf("%s/%s", date("M",$start), date("M",$end));
    $persons['period'] = sprintf("%s-%s", date("W",$start), date("W",$end));
    $persons['name'] = "Marine Support On Call Rota";
    $persons['start'] = date("Y-m-d\\TG:i:s",$start);
    $persons['end'] = date("Y-m-d\\TG:i:s",$end);
    $idx=-1;

    foreach($who as $key => $value){
      foreach($value as $k => $v){
        $data= Array();
        for ($i=0; $i<$days; $i++){
          $data[$i] = 0;
        }
        $content = Array();
        $content['name']=$v;
        $email=preg_replace('/ /','.',$v);
        $content['email']=sprintf("%s@iongeo.com", strtolower($email));
        $content['data']['booked']=$data;
        $content['data']['totalDays']="0";
        $content['data']['totalHoliday']="0";
        $persons['people'][++$idx] = $content;
      }
    }
    $fp = fopen("data/$file", 'w');
    fwrite($fp, json_encode($persons));
    fclose($fp);
  }
}

/*
   deletePeriod() - 
*/
function deletePeriod() {
  if (isset($_GET['period'])) {
    $file = getFile(parse_get($_GET['period']));
    if (is_file("data/$file")){
      unlink("data/$file");
      return true;
    }
  }
}

/*
   getLastEntry() return the timestamp from the last day in the last period
*/
function getLastEntry() {
  date_default_timezone_set('UTC');
  $indir=array_filter(scandir("data"),function($item){
    return is_file("data/" . $item);
  });
  $a=array();
  foreach($indir as $key=>$value) {
    if ($value !=="." && $value !==".." && $value !== ".htaccess" ){
      preg_match_all("/\d+-\d+-\d+/", $value, $matches); // Get date from filename
      @list($startString,$endString)=array_pad($matches[0], 2, null);         // Get start and end date dd-mm-yyyy
      $e=date_parse_from_format("d-m-Y",$endString);
      $end_ts = mktime(23,59,59,$e['month'],$e['day'],$e['year']); // End of day @ 23:59:59
      array_push($a,$end_ts);
    }
  }
  asort($a);             // Sort array
  $last=array_pop($a);   // Return last entry from the array
  echo $last;
  return;
}

$request = parse_get($_GET['request']);
if (function_exists($request)) {
  call_user_func($request);
} else {
  error_log("Invalid request $request");
}
?>
