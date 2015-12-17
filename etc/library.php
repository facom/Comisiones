<?php
////////////////////////////////////////////////////////////////////////
//ROUTINES
////////////////////////////////////////////////////////////////////////
function isBlank($string)
{
  if(!preg_match("/\w+/",$string)){return 1;}
  return 0;
}

function sqlNoblank($out)
{
  $res=mysqli_fetch_array($out);
  $len=count($res);
  if($len==0){return 0;}
  return $res;
}

function errorMessage($msg)
{
$error=<<<E
  <div style=background:lightgray;padding:10px>
    <i style='color:red'>$msg</i>
    </div><br/>
E;
 return $error;
}

function generateSelection($values,$name,$value,$disabled="",$readonly=0)
{
  $parts=$values;
  $selection="";
  if($readonly){
    $selection.="<input type='hidden' name='$name' value='$value'>";
    $selection.=$value;
    return $selection;
  }
  $selection.="<select name='$name' style='' $disabled>";
  foreach(array_keys($parts) as $part){
    $show=$parts[$part];
    $selected="";
    if($part==$value){$selected="selected";}
    $selection.="<option value='$part' $selected>$show";
  }
  $selection.="</select>";
  return $selection;
}

function mysqlCmd($sql,$qout=0,$qlog=1)
{
  global $DB,$DATE;
  if($qlog==1){
    $fl=fopen("log/mysql.log","a");
    fwrite($fl,"$DATE: $sql\n");
    fclose($fl);
  }
  if(!($out=mysqli_query($DB,$sql))){
    die("Error:".mysqli_error($DB));
  }
  if(!($result=sqlNoblank($out))){
    return 0;
  }
  if($qout){
    $result=array($result);
    while($row=mysqli_fetch_array($out)){
      array_push($result,$row);
    }
  }
  return $result;
}

function generateRandomString($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }
  return $randomString;
}

function upAccents($string)
{
  $string=strtoupper($string);
  $accents=array("á"=>"Á","é"=>"É","í"=>"Í","ó"=>"Ó","ú"=>"Ú");
  foreach(array_keys($accents) as $acc){
    $string=preg_replace("/$acc/",$accents["$acc"],$string);
  }
  return $string;
}

function sendMail($email,$subject,$message,$headers="")
{
  date_default_timezone_set('Etc/UTC');
  $mail = new PHPMailer;
  $mail->isSMTP();
  $mail->SMTPDebug = 0;
  $mail->Debugoutput = 'html';
  $mail->Host = 'smtp.gmail.com';
  $mail->Port = 587;
  $mail->SMTPSecure = 'tls';
  $mail->SMTPAuth = true;
  $mail->Username = $GLOBALS["EMAIL_USERNAME"];
  $mail->Password = $GLOBALS["EMAIL_PASSWORD"];
  $mail->setFrom($mail->Username, 'Sistema de Solicitud de Comisiones FCEN/UdeA');
  $mail->addReplyTo($mail->Username, 'Sistema de Solicitud de Comisiones FCEN/UdeA');
  $mail->addAddress($email,"Destinatario");
  $mail->Subject=$subject;
  $mail->CharSet="UTF-8";
  $mail->Body=$message;
  $mail->IsHTML(true);
  if(!($status=$mail->send())) {
    $status="Mailer Error:".$mail->ErrorInfo;
  }
  return $status;
}

function getComisionInfo($comisionid)
{
  global $FIELDS_COMISIONES,$FIELDS_PROFESORES;
  $results=mysqlCmd("select * from Comisiones where comisionid='$comisionid'");
  $comision=array();
  foreach($FIELDS_COMISIONES as $field){
    $fieldn=$field;
    if($field=="extra1"){$field="diaspermiso";}
    $comision["$field"]=$results[$fieldn];
  }
  foreach($FIELDS_PROFESORES as $field){
    $fieldn=$field;
    if($field=="extra1"){$field="diasdisponible";}
    if($field=="extra2"){$field="ano";}
    $comision["$field"]=$results[$fieldn];
  }
  return $comision;
}

?>
