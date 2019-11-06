<?php

function getNamedAddress($mysqli, $rota, $user) {
  error_log("User: $user");

  if ($stmt = $mysqli->prepare("SELECT `email` FROM `members` WHERE `username`= ? LIMIT 1")){
    $addr=Array(); $i=-1;
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows()==1) {
      $stmt->bind_result($email);
      $addr[++$i] = $email;
      $stmt->close();
      return $addr;
    }
  }
}

function getAddresses($type="single",$rota,$user_id="") {
  $addr=Array(); $i=-1;
  $base = $_SERVER['DOCUMENT_ROOT'];
  include_once $base.'/inc/db_connect.php';
  if ($type=="single" and $stmt = $mysqli->prepare("SELECT email FROM members WHERE id= ? LIMIT 1")){
    $stmt->bind_param('i',$user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows()==1) {
      $stmt->bind_result($email);
      while ($stmt->fetch()){
        $addr[++$i] = $email;
      }
      $stmt->close();
      return $addr;
    }
  } elseif ($type=="multi" and $stmt = $mysqli->prepare("SELECT email FROM members WHERE rota= ?")) {
    $stmt->bind_param('s',$rota);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows>=1) {
      $stmt->bind_result($email);
      while ($stmt->fetch()){
//        if ($email == "adam.fordyce@iongeo.com" || $email == "salah.ahmed@iongeo.com") {
//           error_log($email);
//           $addr[++$i] = $email;
//        } 
          error_log("$email for $rota");
          $addr[++$i] = $email;
      }
      $stmt->close();
      return $addr;
    }
  }
}

function email_rota($file,$period,$rota,$user,$type,$action="Updates") {
  date_default_timezone_set('UTC');
  $base = $_SERVER['DOCUMENT_ROOT'];
  $data=file_get_contents("$base/data/$file");
  $json= json_decode($data, true);

  if ($type == "single"){
    // Email whoever requeted the rota
    $addr=getAddresses($type,$rota,$user);
  } elseif($type == "multi") { 
    require_once $base.'/inc/db_connect.php';
    $addr=Array(); $i=-1;
    // Email all members requeted the rota
    foreach( $json['people'] as $k => $v) {
      //error_log($json['people'][$k]['name']);
      $name = $json['people'][$k]['name'];
      if (strlen($name) > 0 and strlen($rota) > 0 ) {
        error_log("Name: ".$name);
        $addr[++$i] = &getNamedAddress($mysqli, $rota, $name);
      }
    }
  }
  preg_match_all("/\d+-\d+-\d+/", $file, $matches); // Get date from filename
  list($startString,$endString)=$matches[0];         // Get start and end date dd-mm-yyyy
  $s=date_parse_from_format("d-m-Y",$startString);
  $e=date_parse_from_format("d-m-Y",$endString);
  $start_ts = mktime(00,00,01,$s['month'],$s['day'],$s['year']); // Start of day @ 00:00:01
  $end_ts = mktime(23,59,59,$e['month'],$e['day'],$e['year']); // End of day @ 23:59:59
  $sid=(60*60*24);
  $days = round(($end_ts-$start_ts)/$sid)+1;

  require_once $base.'/inc/PHPMailer-master/PHPMailerAutoload.php';
  $results_messages = array();
  $mail = new PHPMailer(true);
  $mail->CharSet = 'utf-8';
  ini_set('default_charset', 'UTF-8');
  class phpmailerAppException extends phpmailerException {}
 
  try {
    $mail->isSMTP();
    $mail->SMTPDebug  = 3;
    $mail->Host       = "smtp.zoho.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = 'updates@supportrota.co.uk';
    $mail->Password   = 'D9kFj4Md';
    $mail->SMTPSecure = "tls";
    $mail->Port       = "587";
    $mail->addReplyTo(ADMIN_EMAIL, "Rota Updates");
    $mail->setFrom(ADMIN_EMAIL, "Rota Updates");
    foreach($addr as $k => $v) {
      $mail->addAddress("$v", "$v");
    }
    $subject = $json['title'] . " " . $action;
    $mail->Subject  = $subject;
    $out=array(); $i=-1;
    $out[++$i] = "<!doctype html><html xmlns='http://www.w3.org/1999/xhtml' xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office'><body>";
    $out[++$i] = "<head>";
    $out[++$i] = "<title></title>";
    $out[++$i] = "  <!--[if !mso]><!-- -->";
    $out[++$i] = "  <meta http-equiv='X-UA-Compatible' content='IE=edge'>";
    $out[++$i] = " <!--<![endif]-->";
    $out[++$i] = "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
    $out[++$i] = "</head><body>";
//    $out[++$i] = "<div><p>Hello,</p>";
//    $out[++$i] = "<br />";    
//    $out[++$i] = "<p>Please find the latest ".$json['name']." for the period ".$json['title']."</p>";
//    $out[++$i] = "<br />";    
    $out[++$i] = "<table style='border-style: solid; border-width: 1px; border-color: black; border-collapse: collapse;padding:10px;margin:auto;font:italic 20px/40px Georgia,serif;min-width:600px;text-align:center;'>";
    $out[++$i] = "  <thead style=''>";
    $out[++$i] = "    <th colspan='$days' style='background-color:#FFC;text-align:center;padding:10px;'>".$json['title']."</th>";
    $out[++$i] = "  </thead>";
    $out[++$i] = "  <tbody>";
    $out[++$i] = "    <tr>";
    $out[++$i] = "      <td style='border-style: solid; border-width: 1px; border-color: black;padding:10px;'></td>";
    for ($day=$start_ts;$day<=$end_ts;$day+=$sid){                              
      $out[++$i] = "    <td style='border-style: solid; border-width: 1px; border-color: black;padding:10px;'>".date("d",$day)."</td>";
    }     
    $out[++$i] = "    </tr>";
    foreach ($json['people'] as $k => $v){
      $out[++$i] = "    <tr>";  
      $out[++$i] = "      <td style='border-style: solid; border-width: 1px; border-color: black;padding:10px; min-width:50px'>".$json['people'][$k]['name']."</td>";
      $pd=-1;
      $sum=0;
      foreach ($json['people'][$k]['data']['booked'] as $k2 => $v2) {
        ++$pd;
        $dom=date("j",$start_ts+($pd*$sid));
        if (($start_ts+($pd*$sid)) < $end_ts)
        {
          $dow=date("N",$start_ts+($pd*$sid));
          $weekend=false;
          if ($dow==6 or $dow==7){ $weekend=true;}
          $entry="";
          $color="#CFC";
          if ($v2=="0"){
            if ($weekend) { $color="#66B3FF"; } else {$color="#CFC";}
          } elseif ($v2=="1") {
            if ($weekend) { $color="#FF80AA"; $entry="1.0"; $sum+="1.0"; } else {$color="#FCD"; $entry="0.5"; $sum+="0.5";}
          } elseif ($v2=="2") {
            if ($weekend) { $color="#FF80AA"; $entry="0.5"; $sum+="0.5"; } else {$color="#FCD"; $entry="0.25"; $sum+="0.25";}
          } elseif ($v2=="3") {
            $entry="H";
            $color="#a366ff";
            #if ($weekend) { $color="#a366ff"; } else {$color="#f0e6ff";}
            #if ($weekend) { $color="#a366ff"; } else {$color="#f0e6ff";}
          }
          $out[++$i] = "      <td style='border-style: solid; border-width: 1px; border-color: black;background-color:$color;min-width:25px;'>$entry</td>";
        }
      }
      $pd=-1;
      $out[++$i] = "    </tr>";
      $sum=0;
    }
    $out[++$i] = "  </tbody>";
    $out[++$i] = "</table>";
//    $out[++$i] = "<br />";
    $out[++$i] = "</body></html>";
    $message=implode("",$out);
    $body = $message;
    $mail->WordWrap = 78;
    $mail->msgHTML($body, dirname(__FILE__), true); //Create message bodies and embed images
    try {
      $mail->send();
      $results_messages[] = "Message has been sent using SMTP";
    }
    catch (phpmailerException $e) {
      throw new phpmailerAppException('Unable to send to: ' . $to. ': '.$e->getMessage());
    }
  } catch (phpmailerAppException $e) {
    $results_messages[] = $e->errorMessage();
    error_log($e->errorMessage());
  }
 
  if (count($results_messages) > 0) {
    return true;
//    return json_encode($results_messages);
  }
}

function email_monthly_rota($file,$period,$rota,$user_id,$type) {
  date_default_timezone_set('UTC');
  $addr=getAddresses($type,$rota,$user);
  $base = $_SERVER['DOCUMENT_ROOT'];
  $data=file_get_contents("$base/data/$file");
  $json= json_decode($data,true);
  preg_match_all("/\d+-\d+-\d+/", $file, $matches); // Get date from filename
  list($startString,$endString)=$matches[0];         // Get start and end date dd-mm-yyyy
  $s=date_parse_from_format("d-m-Y",$startString);
  $e=date_parse_from_format("d-m-Y",$endString);
  $start_ts = mktime(00,00,01,$s['month'],$s['day'],$s['year']); // Start of day @ 00:00:01
  $end_ts = mktime(23,59,59,$e['month'],$e['day'],$e['year']); // End of day @ 23:59:59
  $sid=(60*60*24);
  $days = round(($end_ts-$start_ts)/$sid)+1;

  require_once $base.'/inc/PHPMailer-master/PHPMailerAutoload.php';
  $results_messages = array();
  $mail = new PHPMailer(true);
  $mail->CharSet = 'utf-8';
  ini_set('default_charset', 'UTF-8');
  class phpmailerAppException extends phpmailerException {}
 
  try {
    $mail->isSMTP();
    $mail->SMTPDebug  = 3;
    $mail->Host       = "smtp.zoho.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = 'updates@supportrota.co.uk';
    $mail->Password   = 'D9kFj4Md';
    $mail->SMTPSecure = "tls";
    $mail->Port       = "587";
    $mail->addReplyTo(ADMIN_EMAIL, "Rota Updates");
    $mail->setFrom(ADMIN_EMAIL, "Rota Updates");
    foreach($addr as $k => $v) {
      if(!PHPMailer::validateAddress($v)) {
        throw new phpmailerAppException("Email address " . $v . " is invalid -- aborting!");
      } else {
        $mail->addAddress("$v", "$v");
      }
    }
    $subject = $json['title'];
    $mail->Subject  = $subject;
    $out=array(); $i=-1;
    $out[++$i] = "<!doctype html><html xmlns='http://www.w3.org/1999/xhtml' xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office'><body>";
    $out[++$i] = "<head>";
    $out[++$i] = "<title></title>";
    $out[++$i] = "  <!--[if !mso]><!-- -->";
    $out[++$i] = "  <meta http-equiv='X-UA-Compatible' content='IE=edge'>";
    $out[++$i] = " <!--<![endif]-->";
    $out[++$i] = "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
    $out[++$i] = "</head><body>";
    $out[++$i] = "<p>Hello,</p>";
    $out[++$i] = "<br />";    
    $out[++$i] = "<p>Please find the latest ".$json['name']." for the period ".$json['title']."</p>";
    $out[++$i] = "<br />";    
    $out[++$i] = "<table style='border-style: solid; border-width: 1px; border-color: black; border-collapse: collapse;padding:1px;margin:auto;font:italic 10px/20px Georgia,serif;min-width:600px;text-align:center;'>";
    $out[++$i] = "  <thead>";
    $out[++$i] = "    <tr><th colspan='100'><center>Towed Streamer 24hr support</center></th></tr>";
    $out[++$i] = "    <tr><th>Period</th><td>"+$json['title']+"</td><th>Year</th><td>"+"#YEAR#"+"</td></tr>";
    $out[++$i] = "    <tr role='row'><th colspan='100'>Support Phone</th></tr>";
    $out[++$i] = "  </thead>";
    $out[++$i] = "  <tbody>";
    $out[++$i] = "    <tr>";
    $out[++$i] = "      <th>Day</th>";
    $out[++$i] = "      <th>Engineer</th>";
    $out[++$i] = "      <th>Claim</th>";
    $out[++$i] = "      <th>Comment</th>";
    $out[++$i] = "    </tr>";

    $start_day=date("j",$start_ts);

    $pd=-1;
    $sum=0;
    for ($day=$start_ts;$day<=$end_ts;$day+=$sid){
      ++$pd;
      $out[++$i] = "    <tr>";
      $out[++$i] = "      <td style='border-style: solid; border-width: 1px; border-color: black;padding:1px;'>".date("d",$day)."</td>";
      $dom=date("j",$start_ts+($pd*$sid));
      if (($start_ts+($pd*$sid)) < $end_ts){
        foreach ($json['people'] as $k => $v){
          $e = ($v['data']['booked'][$pd]);
          $color="red";
          if ($e=="1"){
            $dow=date("N",$start_ts+($pd*$sid));
            $weekend=false;
            if ($dow==6 or $dow==7){ $weekend=true;}
            if ($weekend) { $color="#66b3ff"; $entry="1.0"; $sum+="1.0"; } else {$color="#e6f2ff"; $entry="0.5"; $sum+="0.5";}
            $out[++$i] = "      <td style='border-style: solid; border-width: 1px; border-color: black;padding:1px; min-width:50px; background-color:$color'>".$json['people'][$k]['name']."</td>";
            $out[++$i] = "      <td style='border-style: solid; border-width: 1px; border-color: black;padding:1px; min-width:50px; background-color:$color'>".$sum."</td>";
            $out[++$i] = "      <td style='border-style: solid; border-width: 1px; border-color: black;padding:1px; min-width:50px; background-color:$color'>"."#COMMENT#"."</td>";
          }
          $sum=0;
        }
      }
      $out[++$i] = "    </tr>";
    }
    $pd=-1; # reset
    $out[++$i] = "  </tbody>";
    $out[++$i] = "</table>";
    $out[++$i] = "<br />";
    $out[++$i] = "<br />";    
    $out[++$i] = "</body></html>";

    $message=implode("",$out);
    $body = $message;

    $mail->WordWrap = 78;
    $mail->msgHTML($body, dirname(__FILE__), true);
  
    try {
      $mail->send();
      $results_messages[] = "Message has been sent using SMTP";
    } catch (phpmailerException $e) {
      throw new phpmailerAppException('Unable to send to: ' . $to. ': '.$e->getMessage());
    }
  } catch (phpmailerAppException $e) {
    $results_messages[] = $e->errorMessage();
    error_log($e->errorMessage());
  }
 
  if (count($results_messages) > 0) {
    return json_encode($results_messages);
  }
}

?>
