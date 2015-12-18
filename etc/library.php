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
    if($field=="extra1"){$field="diaspermiso";}
    $comision["$field"]=$results[$field];
  }
  $cedula=$comision["cedula"];
  $profesor=mysqlCmd("select * from Profesores where cedula='$cedula';");
  foreach($FIELDS_PROFESORES as $field){
    $comision["$field"]=$profesor[$field];
  }
  $institutoid=$comision["institutoid"];
  $instituto=mysqlCmd("select * from Institutos where institutoid='$institutoid';");
  $comision["instituto"]=$instituto["instituto"];
  return $comision;
}

function array2Globals($list)
{
  foreach(array_keys($list) as $key){
    $GLOBALS["$key"]=$list["$key"];
  }
}

function fechaRango($id,$start="",$end=""){
$code=<<<C
<input $disp3 id="fecharango" name="fecharango">
<script>
    $("#$id").daterangepicker({
        presetRanges: [{
            text: 'Hoy',
	    dateStart: function() { return moment() },
	    dateEnd: function() { return moment() }
	}, {
            text: 'Mañana',
	    dateStart: function() { return moment().add('days', 1) },
	    dateEnd: function() { return moment().add('days', 1) }
	}, {
            text: 'La próxima semana',
            dateStart: function() { return moment().add('weeks', 1).startOf('week') },
            dateEnd: function() { return moment().add('weeks', 1).endOf('week') }
	}],
	datepickerOptions: {
            minDate: 0,
            maxDate: null
        },
	applyOnMenuSelect: false,
	initialText : 'Seleccione el rango de fechas...',
	applyButtonText : 'Escoger',
	clearButtonText : 'Limpiar',
	cancelButtonText : 'Cancelar',
    });
    jQuery(function($){
        $.datepicker.regional['es'] = {
            closeText: 'Cerrar',
            prevText: '&#x3c;Ant',
            nextText: 'Sig&#x3e;',
            currentText: 'Hoy',
            monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                         'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
            monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
                              'Jul','Ago','Sep','Oct','Nov','Dic'],
            dayNames: ['Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado'],
            dayNamesShort: ['Dom','Lun','Mar','Mi&eacute;','Juv','Vie','S&aacute;b'],
            dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
            weekHeader: 'Sm',
            dateFormat: 'dd/mm/yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''};
        $.datepicker.setDefaults($.datepicker.regional['es']);
    });
C;

  if(!isBlank($start)){
$code.=<<<C
  $("#$id").daterangepicker({
      onOpen: $("#$id").daterangepicker(
          "setRange",
          {start:$.datepicker.parseDate("yy-mm-dd","$start"),
           end:$.datepicker.parseDate("yy-mm-dd","$end")}
      )
  });
C;
  }else{
$code.=<<<C
  var today = moment().toDate();
  var tomorrow = moment().add('days', 1).startOf('day').toDate();
  $("#$id").daterangepicker({
    onOpen: $("#$id").daterangepicker("setRange",{start: today,end: tomorrow})
    });
C;
  }
    
  $code.="</script>";
  return $code;
}

function str2Array($string)
{
  $string=preg_replace("/[{}\"]/","",$string);
  $comps=preg_split("/,/",$string);
  
  $list=array();
  foreach($comps as $comp){
    $parts=preg_split("/:/",$comp);
    $key=$parts[0];
    $value=$parts[1];
    $list["$key"]=$value;
  }
  return $list;
}

?>
