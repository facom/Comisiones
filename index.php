<?php
$content="";

////////////////////////////////////////////////////////////////////////
//LOAD CONFIGURATION
////////////////////////////////////////////////////////////////////////
$HOST=$_SERVER["HTTP_HOST"];
$SCRIPTNAME=$_SERVER["SCRIPT_FILENAME"];
$ROOTDIR=rtrim(shell_exec("dirname $SCRIPTNAME"));
require("$ROOTDIR/etc/configuration.php");
////////////////////////////////////////////////////////////////////////
//HEADER
////////////////////////////////////////////////////////////////////////
$content.=<<<C
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
  <script src="etc/jquery.js"></script>
  $style

  <script>
    function selectCorta(selection){
	if($(selection).val().localeCompare("noremunerada")==0){
	  $(".discorta").hide();
	  $(".discortashow").show();
	}else{
	  $(".discorta").show();
	  $(".discortashow").hide();
	}
    }
  </script>

</head>
<body>
C;

////////////////////////////////////////////////////////////////////////
//MAIN PAGE
////////////////////////////////////////////////////////////////////////
$content.=<<<C
<table width=100% border=0>
<tr>
<td width=10%><image src="$LOGOUDEA/udea_fcen.jpg"/ height=120px></td>
<td valign=bottom>
  <b style='font-size:32'><a href=index.php?$USERSTRING>Solicitud de Comisiones</a></b><br/>
  <b style='font-size:24'>Decanatura</b><br/>
  <b style='font-size:24'>Facultad de Ciencias Exactas y Naturales</b><br/>
  <b style='font-size:24'>Universidad de Antioquia</b><br/>
</td>
</table>
<hr/>
<form action="index.php" method="post" enctype="multipart/form-data" accept-charset="utf-8">
C;

////////////////////////////////////////////////////////////////////////
//BASIC VARIABLES
////////////////////////////////////////////////////////////////////////
$qerror=0;
$inputform=1;
$error="";

//BASIC PERMISSION
$qperm=0;
$qmant=0;

//CHECK MAINTAINANCE
$out=array_search($usercedula,$MAINTAINANCE);
if(!isBlank($out)){
  $qmant=1;
}
//CHECK DIRECTOR
$out=array_search($usercedula,$DIRECTORS);
if(!isBlank($out)){
  $qperm=1;
}
//CHECK DEAN
if($usercedula==$DIRECTORS["decanatura"] or
   $qmant){
  $qperm=2;
}

////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
//PROCESSING
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////

if(isset($operation)){

  //////////////////////////////////////////////////////////////
  //ACTUALIZAR USUARIO
  //////////////////////////////////////////////////////////////
  if($operation=="Actualizar"){
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //GRABAR DATOS EN BASE DE DATOS
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $fields_profesores="";
    $values_profesores="";
    $fval_profesores="";
    foreach($FIELDS_PROFESORES as $field){
      $value=$$field;
      $fields_profesores.="$field,";
      $values_profesores.="'$value',";
      $fval_profesores.="$field='$value',";
    }
    $fields_profesores=trim($fields_profesores,",");
    $values_profesores=trim($values_profesores,",");
    $fval_profesores=trim($fval_profesores,",");
    $sql="insert into Profesores ($fields_profesores) values ($values_profesores) on duplicate key update $fval_profesores";
    mysqlCmd($sql);
    $error=errorMessage("Información de usuario guardada.");
    $inputform=0;
  }//End Actualizar usuario

  //////////////////////////////////////////////////////////////
  //GUARDAR SOLICITUD
  //////////////////////////////////////////////////////////////
  if($operation=="Guardar"){
    
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //CREAR DIRECTORIO DE COMISION
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    shell_exec("mkdir -p comisiones/$comisionid");

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //SUBIR LOS ARCHIVOS
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $file1=$_FILES["file_anexo1"];
    $file2=$_FILES["file_anexo2"];
    $file3=$_FILES["file_anexo3"];

    if($file1["size"]>0){
      $name=$file1["name"];
      $tmp=$file1["tmp_name"];
      $anexo1=$name;
      shell_exec("cp $tmp comisiones/$comisionid/'$name'");
    }
    if($file2["size"]>0){
      $name=$file2["name"];
      $tmp=$file2["tmp_name"];
      $anexo2=$name;
      shell_exec("cp $tmp comisiones/$comisionid/'$name'");
    }
    if($file3["size"]>0){
      $name=$file3["name"];
      $tmp=$file3["tmp_name"];
      $anexo3=$name;
      shell_exec("cp $tmp comisiones/$comisionid/'$name'");
    }

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    if($qperm==1 and $vistobueno=="Si" and $tipocom=="noremunerada"){
      $parts=preg_split("/-/",$DATE);
      $year=$parts[0];
      if($year!=$ano){
	$diasdisponible=6;
	$ano=$year;
      }else{
	$diasdisponible=$diasdisponible-$diaspermiso;
      }
    }
    
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //CHECK STATUS
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    if($vistobueno=="Si"){
      $estado="vistobueno";
    }else if($estado!="devuelta"){
      $estado="solicitada";
    }

    if($aprobacion=="Si"){
      $estado="aprobada";
      if($tipocom!="noremunerada"){
	shell_exec("echo $resolucion >> etc/resoluciones.txt");
      }else{
	$resolucion="99999";
      }
    }else{
      if($vistobueno=="Si"){
	$estado="vistobueno";
      }else if($estado!="devuelta"){
	$estado="solicitada";
      }
    }
    if($qperm==0 and $estado=="devuelta"){$estado="solicitada";}

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //GRABAR DATOS EN BASE DE DATOS
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $fl=fopen("comisiones/$comisionid/comision-$comisionid.txt","w");
    $fields_comisiones="";
    $values_comisiones="";
    $fval_comisiones="";
    foreach($FIELDS_COMISIONES as $field){
      $fieldn=$field;
      if($field=="extra1"){$field="diaspermiso";}
      $value=$$field;
      //echo "$field, $value<br/>";
      $fields_comisiones.="$fieldn,";
      $values_comisiones.="'$value',";
      $fval_comisiones.="$fieldn='$value',";
      fwrite($fl,"$fieldn = $value\n");
    }
    $fields_comisiones=trim($fields_comisiones,",");
    $values_comisiones=trim($values_comisiones,",");
    $fval_comisiones=trim($fval_comisiones,",");

    $fields_profesores="";
    $values_profesores="";
    $fval_profesores="";
    foreach($FIELDS_PROFESORES as $field){
      $fieldn=$field;
      if($field=="extra1"){$field="diasdisponible";}
      if($field=="extra2"){$field="ano";}
      $value=$$field;
      $fields_profesores.="$fieldn,";
      $values_profesores.="'$value',";
      $fval_profesores.="$fieldn='$value',";
      fwrite($fl,"$fieldn = $value\n");
    }
    $fields_profesores=trim($fields_profesores,",");
    $values_profesores=trim($values_profesores,",");
    $fval_profesores=trim($fval_profesores,",");
    fclose($fl);

    $sql="insert into Comisiones ($fields_comisiones) values ($values_comisiones) on duplicate key update $fval_comisiones";
    mysqlCmd($sql);

    $sql="insert into Profesores ($fields_profesores) values ($values_profesores) on duplicate key update $fval_profesores";
    mysqlCmd($sql);
    $error=errorMessage("Comisión '$comisionid' guardada.");
    $inputform=0;

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //GRABAR DATOS EN ARCHIVOS DE TEXTO
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    foreach($TEXTS as $text){
      $value=$$text;
      $fl=fopen("comisiones/$comisionid/$text.txt","w");
      fwrite($fl,$value);
      fclose($fl);
    }

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //ENVIAR CORREO DE NOTIFICACION
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $qcopy=0;
    $out=mysqlCmd("select cedulajefe,emailinst from Institutos where institutoid='$institutoid'");
    $cedulajefeinst=$out[0];
    $emailinst=$out[1];
    //echo "$cedulajefeinst,$emailinst<br/>";
    //echo "qperm=$qperm,qnew=$qnew<br/>";

    if($estado=="solicitada"){
      if($qnew){
	$out=mysqlCmd("select cedulajefe,emailinst from Institutos where institutoid='$institutoid'");
	$cedulajefe=$out[0];
	$emailcopia=$emailinst;
	$qcopy=1;
	
	$out=mysqlCmd("select email from Profesores where cedula='$cedulajefe'");
	$emailjefe=$out[0];
	$ttipocom=$TIPOSCOM[$tipocom];
	$destino="Director";
	
	$subject="[Comisiones] Nueva solicitud de comisión que requiere visto bueno";
$message=<<<M
  Se&ntilde;or(a) Director(a),
<p>
Una nueva solicitud de Comisión ha sido radicada en
el <a href='bit.ly/fcen-comisiones'>Sistema de Solicitudes</a>.  Esta
es la información básica de la solicitud:
</p>
<ul>
<li>Fecha de radicación: $radicacion</li>
<li>Tipo de comision: $ttipocom</li>
<li>Fecha de comision: $fecha</li>
<li>Cédula: $cedula</li>
<li>Nombre: $nombre</li>
</ul>
<p>
Por favor evalue la solicitud y en caso de ser necesario
otorgue su visto bueno para continuar con el trámite.
</p>
<b>Sistema de Solicitud de Comisiones<br/>
Decanatura, FCEN</b>
M;
      }
    }else if($estado=="vistobueno"){
      $out=mysqlCmd("select cedulajefe,emailinst from Institutos where institutoid='decanatura'");
      $cedulajefe=$out[0];
      $emailcopia=$out[1];
      $qcopy=1;
      
      $out=mysqlCmd("select email from Profesores where cedula='$cedulajefe'");
      $emailjefe=$out[0];
      $destino="Decano";
      
      $subject="[Comisiones] Una solicitud de comisión ha recibido visto bueno";
$message=<<<M
  Se&ntilde;or(a) Decano(a),
<p>
La solicitud radicada en el <a href='bit.ly/fcen-comisiones'>Sistema
de Solicitudes</a> identificada con número '$comisionid' ha recibido
visto bueno del Director de Instituto.
</p>
<p>
Por favor evalue la solicitud y en caso de ser necesario otorgue su
aprobación continuar con el trámite.
</p>
<b>Sistema de Solicitud de Comisiones<br/>
Decanatura, FCEN</b>
M;
       $qnew=1;
    }else if($estado=="aprobada"){
      if(!file_exists("comisiones/$comisionid/.notified")){
	//echo "Creating notificaction file...<br/>";
	shell_exec("date > comisiones/$comisionid/.notified");

	$out=mysqlCmd("select email from Profesores where cedula='$cedulajefeinst'");
	$emailcopia=$out[0];
	$qcopy=1;
	$emailjefe=$email;

$restxt=<<<R
El número de resolución de decanatura es el $resolucion de $fecharesolucion.
</p>
<p>
Para obtener una copia de la resolución de click en <a href="$URL/comisiones/$comisionid/resolucion-$comisionid.pdf">este enlace</a>.
  En caso de que el enlace este roto (no se haya expedido la resolución) pregunte en la vicedecanatura por la misma o espere a que el link aparezca en el Sistema de Solicitudes.
</p>
R;
        if($tipocom=="noremunerada"){
	  $restxt="";
	}
	$subject="[Comisiones] Su solicitud de comisión/permiso ha sido aprobada";
$message=<<<M
  Se&ntilde;or(a) Profesor(a),
<p>
Su solicitud de comisión/permiso radicada en
el <a href='bit.ly/fcen-comisiones'>Sistema de Solicitudes</a> en
fecha $radicacion e identificada con número '$comisionid' ha sido
aprobada.  $restxt
<b>Sistema de Solicitud de Comisiones<br/>
Decanatura, FCEN</b>
M;
        $qnew=1;
      }else{
	$qnew=0;
      }
    }else if($estado=="devuelta"){
      $out=mysqlCmd("select email from Profesores where cedula='$cedulajefeinst'");
      $emailcopia=$out[0];
      $qcopy=1;
      $emailjefe=$email;
      $destino="Solicitante";
      
      $subject="[Comisiones] Su solicitud de comisión/permiso ha sido devuelta.";
$message=<<<M
  Se&ntilde;or(a) Decano(a),
<p>
La solicitud radicada en el <a href='bit.ly/fcen-comisiones'>Sistema
de Solicitudes</a> identificada con número '$comisionid' ha sido
devuelta. La razón de la devolución se reproduce abajo:
</p>
<blockquote>
$respuesta
</blockquote>
<p>
Vaya al sistema y modifique la solicitud de acuerdo a las sugerencias
indicadas.
</p>
<b>Sistema de Solicitud de Comisiones<br/>
Decanatura, FCEN</b>
M;
      $qnew=1;
    }else{
      $qnew=0;
    }

    if($qnew){
      $emailcco=$EMAIL_USERNAME;
      $headers="";
      $headers.="From: noreply@udea.edu.co\r\n";
      $headers.="Reply-to: noreply@udea.edu.co\r\n";
      $headers.="MIME-Version: 1.0\r\n";
      $headers.="MIME-Version: 1.0\r\n";
      $headers.="Content-type: text/html\r\n";
      $simulation="";
      $randstr=generateRandomString(5);
      $fl=fopen("log/mails/mail-$comisionid-$randstr.html","w");
      fwrite($fl,"Subject: $subject<br/>\n>");
      fwrite($fl,"Email: $emailjefe<br/>\n>");
      fwrite($fl,"Copia: $emailcopia, <br/>\n>");
      fwrite($fl,"Message:<br/>\n$message\n<br/>\n");
      fclose($fl);

      //SEND MESSAGE TO USER
      $emailuser=$email;
      $estadoactual=$ESTADOS[$estado];
      $subjectactual="[Comisiones] Actualización de Solicitud de Comisión/Permiso $comisionid";
$messageactual=<<<M
  Se&ntilde;or(a) Profesor(a),
<p>
Su solicitud de comisión/permiso radicada en
el <a href='bit.ly/fcen-comisiones'>Sistema de Solicitudes</a> en
fecha $radicacion e identificada con número '$comisionid' ha sido
actualizada.
</p>
<p>
Estado: $estadoactual<br/>
Fecha de actualización: $actualizacion.
</p>
<b>Sistema de Solicitud de Comisiones<br/>
Decanatura, FCEN</b>
M;

      if($HOST!="localhost"){
      	sendMail($emailjefe,$subject,$message,$headers);
	sendMail($emailuser,$subjectactual,$messageactual);
	if($qcopy){
	  sendMail($emailcopia,"[Copia] ".$subject,$message,$headers);
	  sendMail($emailcco,"[Historico] ".$subject,$message,$headers);
	}
      }
      else{$simulation="(simulación)";}
      $error.=errorMessage("Notificación enviada a $destino $emailjefe. $simulation");
      if($qcopy){
	$error.=errorMessage("Una copia ha sido enviada también a $emailcopia. $simulation");
      }
    }
  }//End Guardar

  //////////////////////////////////////////////////////////////
  //GENERAR RESOLUCION
  //////////////////////////////////////////////////////////////
  if($operation=="Resolucion"){
    
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //GET RESOLUCION DATA
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $solicitud=mysqlCmd("select * from Comisiones where comisionid='$comisionid';",$qout=1);
    foreach($FIELDS_COMISIONES as $field){
      $value=$solicitud[0][$field];
      $$field=$value;
    }
    $profesor=mysqlCmd("select * from Profesores where cedula='$cedula';",$qout=1);
    foreach($FIELDS_PROFESORES as $field){
      $value=$profesor[0][$field];
      $$field=$value;
    }
    $instituto=mysqlCmd("select * from Institutos where institutoid='$institutoid';",$qout=1);

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //PREPROCESS DATA
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $rtipo=$TIPOS[$tipo];
    $rtipoid=$TIPOSID[$tipoid];
    $rtipocom=upAccents($TIPOSCOM[$tipocom]);
    $rinstituto=$INSTITUTOS[$institutoid];

    //PROFESSOR NAME
    $parts=preg_split("/\s+/",$nombre);
    $rnombre=strtoupper($nombre);

    //echo "TIPO:$rtipo,$tipocom,$rtipocom,$rtipoid,$rnombre,$rinstituto,$fecha<br/>";

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //STYLES
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $tablewidth="650px";
    $leftmargin="25px";
    $tablestyle="'border-collapse:collapse;margin-left:$leftmargin'";
    $vspace="40px";
    $titlestyle="'text-align:center;font-weight:bold'";

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //CREATE FILE
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $fl=fopen("comisiones/$comisionid/resolucion-$comisionid.html","w");
$resoltxt=<<<R
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <style type="text/css">
  BODY{
  font-size:12px;
  font-family:Times;
  }
  td{
  font-size:18px;
  font-family:Times;
  }
  </style>
</head>

<body>

<table border=0 width=$tablewidth style=$tablestyle>
  <tr>
    <td width=10%>
      <img src="../../images/udea.jpg" width=100>
    </td>
    <td width=20%></td>
    <td width=40% style='text-align:center'>
      <b>FACULTAD DE CIENCIAS EXACTAS<br/>Y NATURALES</b>
    </td>
  </tr>
</table>

<table border=0 width=$tablewidth style=$tablestyle>
<tr><td>

<div style="height:$vspace"></div>

<p style=$titlestyle>
RESOLUCION DE DECANATO $resolucion
</p>

<p style=$titlestyle>
PARA LA CUAL SE CONCEDE UNA $rtipocom
</p>

<p align="justify">
LA DECANA DE LA FACULTAD DE CIENCIAS EXACTAS Y NATURALES en
uso de sus atribuciones conferidas mediante artículo 34, literal ñ del
Acuerdo Superior Nro. 1 de 1994.
</p> 

<p style=$titlestyle>
RESUELVE:
</p>

<p align="justify">
<b>ARTÍCULO ÚNICO</b>: Conceder al profesor <b>$rnombre</b> $rtipoid
$cedula, $rtipo del $rinstituto, comisión de $fecha para $actividad a
realizarse en $lugar.
</p>

<p align="justify">
<i>
Al reintegrarse a sus actividades deberá presentar ante la oficina de
la Decanato de la Facultad, constancias que acrediten su cumplimiento.
</i>
</p>

<p style=$titlestyle>
COMUNÍQUESE Y CÚMPLASE
</p>

<p>
Dada en Medellín el $fecharesolucion.
</p>
<p>
<img src="../../images/decano.jpg" width=300px><br/>
<b>NORA EUGENIA RESTREPO SÁNCHEZ</b><br/>
$DECANOTXT, Facultad de Ciencias Exactas y Naturales
</p>

<p style="font-size:14px">
Copia: $COORDINADOR, $COORDINADORTXT de Talento Humano Archivo.
</p>

</body>
</html>
</td></tr>
</table>
R;
    fwrite($fl,$resoltxt);
    fclose($fl);

    $fl=fopen("comisiones/$comisionid/resolucion-blank-$comisionid.html","w");
$resoltxt=<<<R
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <style type="text/css">
  BODY{
  font-size:12px;
  font-family:Times;
  }
  td{
  font-size:18px;
  font-family:Times;
  }
  </style>
</head>

<body>

<table border=0 width=$tablewidth style=$tablestyle>
  <tr>
    <td width=10%>
      <img src="../../images/udea-fake.jpg" width=100>
    </td>
    <td width=20%></td>
    <td width=40% style='text-align:center'>
      <!--<b>FACULTAD DE CIENCIAS EXACTAS<br/>Y NATURALES</b>-->
    </td>
  </tr>
</table>

<table border=0 width=$tablewidth style=$tablestyle>
<tr><td>

<div style="height:$vspace"></div>

<p style=$titlestyle>
RESOLUCION DE DECANATO $resolucion
</p>

<p style=$titlestyle>
PARA LA CUAL SE CONCEDE UNA $rtipocom
</p>

<p align="justify">
LA DECANA DE LA FACULTAD DE CIENCIAS EXACTAS Y NATURALES en
uso de sus atribuciones conferidas mediante artículo 34, literal ñ del
Acuerdo Superior Nro. 1 de 1994.
</p> 

<p style=$titlestyle>
RESUELVE:
</p>

<p align="justify">
<b>ARTÍCULO ÚNICO</b>: Conceder al profesor <b>$rnombre</b> $rtipoid
$cedula, $rtipo del $rinstituto, comisión de $fecha para $actividad a
realizarse en $lugar.
</p>

<p align="justify">
<i>
Al reintegrarse a sus actividades deberá presentar ante la oficina de
la Decanato de la Facultad, constancias que acrediten su cumplimiento.
</i>
</p>

<p style=$titlestyle>
COMUNÍQUESE Y CÚMPLASE
</p>

<p>
Dada en Medellín el $fecharesolucion.
</p>
<p>
<img src="../../images/decano-fake.jpg" width=300px><br/>
<b>NORA EUGENIA RESTREPO SÁNCHEZ</b><br/>
$DECANOTXT, Facultad de Ciencias Exactas y Naturales
</p>

<p style="font-size:14px">
Copia: $COORDINADOR, $COORDINADORTXT de Talento Humano Archivo.
</p>

</body>
</html>
</td></tr>
</table>
R;
    fwrite($fl,$resoltxt);
    fclose($fl);

    shell_exec("cd comisiones/$comisionid;$H2PDF resolucion-$comisionid.html resolucion-$comisionid.pdf &> pdf.log");
    shell_exec("cd comisiones/$comisionid;$H2PDF resolucion-blank-$comisionid.html resolucion-blank-$comisionid.pdf &> pdf.log");
    $error=errorMessage("Archivos de resolución $comisionid generados.");
  }

  //////////////////////////////////////////////////////////////
  //BORRAR SOLICITUD
  //////////////////////////////////////////////////////////////
  if($operation=="Borrar"){
    mysqlCmd("delete from Comisiones where comisionid='$comisionid'");
    shell_exec("mv comisiones/$comisionid trash/");
    $error=errorMessage("Comisión '$comisionid' enviada a la papelera de reciclaje.");
  }
}

////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
//DISPLAYING
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////
//BASIC CHECK
////////////////////////////////////////////////////////////////////////
if(isset($action)){
  $inputform=0;
}

if(isBlank($usercedula) and isset($action)){
 $inputform=1;
 $qerror=1;
}

////////////////////////////////////////////////////////////////////////
//CHECK USER
////////////////////////////////////////////////////////////////////////
if(isset($usercedula) and isset($userpass)){
  $sql="select * from Profesores where cedula='$usercedula'";
  if(!($out=mysqli_query($DB,$sql))){
    die("Error:".mysqli_error($DB));
  }
  if(!($profesor=sqlNoblank($out))){
    $error=errorMessage("Cecula no reconocida");
    $inputform=1;
    $qerror=1;
  }else{
    if($userpass==$profesor["pass"]){
      foreach(array_keys($profesor) as $field){
	if(preg_match("/^\d+$/",$field)){continue;}
	$fieldn=$field;
	if($field=="extra1"){$field="diasdisponible";}
	if($field=="extra2"){$field="ano";}
	$$field=$profesor[$fieldn];
      }
    }else{
      $error=errorMessage("Contraseña equivocada o recientemente cambiada");
      $inputform=1;
      $qerror=1;
    }
    $userinstituto=$profesor["institutoid"];
    $useremail=$profesor["email"];
  }
}//End check cedula

////////////////////////////////////////////////////////////////////////
//BASIC FORM
////////////////////////////////////////////////////////////////////////
if($inputform==1){
$content.=<<<C
$error
<table width=30%>
  <tr>
    <td width=50%>
      Ingrese su cedula:
    </td>
    <td><input name="usercedula" value="$usercedula" size=11 maxlength=11></td>
  </tr>
  <tr>
    <td>
      Ingrese su contraseña:<br/>
    </td>
    <td><input type="password" name="userpass" value="$userpass" size=11 maxlength=11></td>
  </tr>
  <tr>
    <td colspan=2>
      <i style="color:red;font-size:12px">Para profesores la contraseña es la misma cédula.</i>
    </td>
  </tr>
  <tr>
    <td colspan=2>
      <input type='submit' name='action' value='Solicitar'>
      <input type='submit' name='action' value='Consultar'>
    </td>
  </tr>
</table>
C;
  if(!isBlank($error)){goto footer;}
 }else{
  $permisos=$PERMISOS[$qperm];
  if($qmant){$permisos.=" (Mantenimiento)";}
$content.=<<<C
  <i style=font-size:10px>Esta conectado como <b>$nombre ($usercedula)</b>, Permisos: $permisos</i>
<hr/>
C;
 }

////////////////////////////////////////////////////////////////////////
//SOLICITAR
////////////////////////////////////////////////////////////////////////
if($action=="Solicitar"){

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //LOAD COMISION
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  if(isset($loadcomision)){
    $results=mysqlCmd("select * from Comisiones where comisionid='$comisionid'");
    foreach($FIELDS_COMISIONES as $field){
      //$$field=utf8_encode($results[$field]);
      $fieldn=$field;
      if($field=="extra1"){$field="diaspermiso";}
      $$field=$results[$fieldn];
    }
    $results=mysqlCmd("select * from Profesores where cedula='$cedula'");
    foreach($FIELDS_PROFESORES as $field){
      //$$field=utf8_encode($results[$field]);
      $fieldn=$field;
      if($field=="extra1"){$field="diasdisponible";}
      if($field=="extra2"){$field="ano";}
      $$field=$results[$fieldn];
    }
    foreach($TEXTS as $text){
      $$text=shell_exec("cat comisiones/$comisionid/$text.txt");
    }
    $error=errorMessage("Solicitud $comisionid cargada");
  }  

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //FECHA
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $result=mysqlCmd("select now();");
  $actualizacion=$result[0];

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //DEFAULT VALUES
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $qnew=0;
  if(isBlank($radicacion)){
    $radicacion=$actualizacion;
    $qnew=1;
  }
  if(isBlank($estado)){$estado="solicitada";}
  if(isBlank($comisionid)){$comisionid=generateRandomString(5);}
  if(isBlank($fecha)){$fecha="Mes DD de 20XX a Mes DD de 20XX";}
  if(isBlank($lugar)){$lugar="Ciudad (País)";}
  if(isBlank($idioma)){$idioma="Español";}
  if(isBlank($actividad)){$actividad="Asistir al Nombre del Evento";}
  if(isBlank($aprobacion)){$aprobacion="No";}
  if(isBlank($vistobueno)){$vistobueno="No";}
  if($estado=="devuelta"){$qnew=1;}
  
  if($aprobacion=="No"){
    $resolucion=shell_exec("tail -n 1 etc/resoluciones.txt")+1;
    setlocale(LC_TIME,"");
    setlocale(LC_TIME,"es_ES.UTF-8") or setlocale(LC_TIME,"es_ES");
    $fecharesolucion=strftime("%d de %B de %Y");
    $fecharesolucion=ucfirst($fecharesolucion);
  }
  $disabled="readonly='readonly'";
  
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //ESTADO
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //CHECK RESOLUTION
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  if(file_exists("comisiones/$comisionid/resolucion-$comisionid.html")){
$reslink=<<<R
  <a href=comisiones/$comisionid/resolucion-$comisionid.html target="_blank">
    Resolucion
  </a>
  (<a href=comisiones/$comisionid/resolucion-$comisionid.pdf target="_blank">pdf</a>)
R;
 
 $extrares="";
 if($qperm==2){
   $extrares="<!-- -------------------------------------------------- -->
    <a href=comisiones/$comisionid/resolucion-blank-$comisionid.html target='_blank'>
      Resolución imprimible</a>
  (<a href=comisiones/$comisionid/resolucion-blank-$comisionid.pdf target='_blank'>pdf imprimible</a>)
";
 }

  }else{
    $reslink="<i>Resolución no generada</i>";
  }

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //VERIFY PERMISSIONS
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $notification="";
  $disp1="";
  $disp2="";
  $disp3="";
  $disp4="";
  $tabcolor="white";
  if($qperm==0){
    $disp1="style='display:none'";
    $disp4="style='display:none'";
  }
  if($qperm==1){
    $disp2="style='display:none'";
  }

  if($vistobueno=="Si"){
    $notification="<i style='color:blue'>Esta solicitud ya ha recibido visto bueno del director.</i>";
    $tabcolor="lightblue";
    if($qperm<=1){
      $disp3="disabled";
    }
  }
  if($aprobacion=="Si"){
    $notification="<i style='color:blue'>Esta solicitud ya ha sido aprobada</i>";
    $tabcolor="lightgreen";
    if($qperm<=1){
      $disp3="disabled";
    }
  }
  
  $discortastyle="";
  $discortashowstyle="style='display:none'";
  if($tipocom=="noremunerada"){
    $discortastyle="style='display:none'";
    $discortashowstyle="";
  }

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //GENERATE TIPO
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $estadosel=generateSelection($ESTADOS,"estado",$estado,$disabled="");
  $tiposel=generateSelection($TIPOS,"tipo",$tipo,$disabled="$disp3");
  $tipoidsel=generateSelection($TIPOSID,"tipoid",$tipoid,$disabled="$disp3");
  $instsel=generateSelection($INSTITUTOS,"institutoid",$institutoid,$disabled="$disp3");
  $dedsel=generateSelection($SINO,"dedicacion",$dedicacion,$disabled="$disp3");
  $vobosel=generateSelection($SINO,"vistobueno",$vistobueno,$disabled="$disp3");
  $aprosel=generateSelection($SINO,"aprobacion",$aprobacion,$disabled="$disp3");
  $tipocomsel=generateSelection($TIPOSCOM,"tipocom",$tipocom,$disabled="$disp3 onchange='selectCorta(this)'");
  $diasvec=array();
  for($i=1;$i<=$diasdisponible;$i++){
    $diasvec["$i"]=$i;
  }
  $diassel=generateSelection($diasvec,"diaspermiso",$diaspermiso,$disabled="$disp3");
  $generar="
  <a href=?$USERSTRING&comisionid=$comisionid&operation=Resolucion&action=Consultar>
    Generar
  </a>";
  if($tipocom=="noremunerada"){
    $generar="<i>No Resolución</i>";
  }
  if($aprobacion=="No"){
    $generar="<i>No aprobada</i>";
  }

$content.=<<<C
  <a href="?$USERSTRING&action=Consultar">Lista de Solicitudes</a> | <a href="?$USERSTRING">Salir</a>
<p/>
$error
<a href="?usercedula=$usercedula&userpass=$userpass&action=Solicitar">Nueva Solicitud</a>
<h2>Solicitud de Comisión</h2>
<a href="JavaScript:void(null)" onclick="$('.ayuda').toggle('fast',null);" style="font-size:12px">Mostrar/Ocultar ayuda</a>
<p></p>
$notification
<table width=600px style="background:$tabcolor">
<tr><td width=40%></td></td width=60%></tr>
<!---------------------------------------------------------------------->
<tr>
<td colspan=2>
<input $disp3 type='submit' name='operation' value='Guardar'>
<input $disp3 type='submit' name='operation' value='Borrar'>
<input $disp3 type='submit' name='action' value='Cancelar'><br/><br/>
</td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Tipo de Documento:</td>
<td>$tipoidsel</td>
</tr>
</tr>
<tr class=ayuda>
<td colspan=2></td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Documento:</td>
<td><input $disp3 type="text" name="cedula" value="$cedula" size=11></td>
</tr>
</tr>
<tr class=ayuda>
<td colspan=2></td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Apellidos y Nombre:</td>
<td><input $disp3 type="text" name="nombre" value="$nombre" size=30></td>
</tr>
<tr class=ayuda>
<td colspan=2>Apellidos y nombres del profesor. Mayúscula sostenida.</td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>E-mail:</td>
<td><input $disp3 type="text" name="email" value="$email" size=30></td>
</tr>
<tr class=ayuda>
<td colspan=2>Preferiblemente el correo institucional.</td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Instituto:</td>
<td>$instsel</td>
</tr>
<tr class=ayuda>
<td colspan=2>Instituto que da el visto bueno para la comisión.</td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Tipo de vinculacion:</td>
<td>$tiposel</td>
</tr>
<tr class=ayuda>
<td colspan=2></td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Dedicación Exclusiva:</td>
<td>$dedsel</td>
</tr>
<tr class=ayuda>
<td colspan=2>Es excluyente comisión de estudio si se tiene
dedicación exclusiva vigente.
</td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Contraseña usuario:</td>
<td><input $disp3 type="password" name="pass" value="$pass" size=11>
<input $disp3 type='submit' name='operation' value='Actualizar'>
</td>
</tr>
<tr class=ayuda>
<td colspan=2>El número solo se asigna una vez se ha aprobado la
comisión.</td>
</tr>
<!---------------------------------------------------------------------->
<td colspan=2>
</td>
<!---------------------------------------------------------------------->
<tr>
<td>Tipo de comisión:</td>
<td>$tipocomsel</td>
</tr>
<tr class=ayuda>
<td colspan=2>Indique el tipo de comisión solicitada.</td>
</tr>
<!---------------------------------------------------------------------->
<tr class="discortashow" $discortashowstyle>
<td>
Días disponibles (año $ano):
<input type="hidden" name="ano" value="$ano">
</td>
<td>
$diasdisponible
<input type="hidden" name="diasdisponible" value="$diasdisponible">
</td>
</tr>
<tr class=ayuda>
<td colspan=2>Número de días restantes para este año.</td>
</tr>
<!---------------------------------------------------------------------->
<tr class="discortashow" $discortashowstyle>
<td>Número de Días:</td>
<td>$diassel</td>
</tr>
<tr class=ayuda>
<td colspan=2>Duración del permiso.</td>
</tr>
<!---------------------------------------------------------------------->
<tr class="discorta" $discortastyle>
<td>Lugar de la comisión:</td>
<td><input $disp3 type="text" name="lugar" value="$lugar" size=30></td>
</tr>
<tr class=ayuda>
<td colspan=2>Indique ciudad(es), país(es).</td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Fechas de la comisión:</td>
<td><input $disp3 type="text" name="fecha" value="$fecha" size=30></td>
</tr>
<tr class=ayuda>
<td colspan=2>Indique las fechas de la comisión (fecha inicial - fecha
final) incluyendo las fechas de viaje. El decano solo puede conceder
hasta 30 días calendario, si fuese mayor duración la solicitud debe ir
a un acta de consejo de facultad para que sea recomendada a
vicerrectoría de docencia.</td>
</tr>
<!---------------------------------------------------------------------->
<tr class="discorta" $discortastyle>
<td>Motivo de la comisión:</td>
<td><input $disp3 type="text" name="actividad" value="$actividad" size=30></td>
</tr>
<tr class=ayuda>
<td colspan=2>Utilice verbos como "Asistir", "Atender la invitación",
"Participar", etc.</td>
</tr>
<!---------------------------------------------------------------------->
<tr class="discorta" $discortastyle>
<td>Idioma de la actividad:</td>
<td><input $disp3 type="text" name="idioma" value="$idioma" size=30></td>
</tr>
<tr class=ayuda>
<td colspan=2>Indique el idioma en el que se realiza la actividad.</td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Justificación:</td>
<td>
<textarea name="presentacion" cols=30 rows=10>$presentacion</textarea>
</td>
</tr>
<tr class=ayuda>
<td colspan=2>Justifiqué la comisión.  En caso de que tenga dedicación
exclusiva vigente, se debe justificar cómo la comisión se enmarca en
los compromisos de la dedicación exclusiva.  Si es una comisión corta
y tiene responsabilidades docentes indique claramente quién se
encargará de sus responsabilidades durante su ausencia.</td>
</tr>
<!---------------------------------------------------------------------->
<tr class="discorta" $discortastyle>
<td>Anexo 1:</td>
<td>
  <input $disp3 type="file" name="file_anexo1" value="$file_anexo1"><br/>
  Archivo: <a href="comisiones/$comisionid/$anexo1" target="_blank">$anexo1</a>
  <input $disp3 type="hidden" name="anexo1" value="$anexo1">
</td>
</tr>
<tr class=ayuda>
<td colspan=2>Anexe la carta de invitación o cualquier otro soporte de la comisión.</td>
</tr>
<!---------------------------------------------------------------------->
<tr class="discorta" $discortastyle>
<td>Anexo 2:</td>
<td>
  <input $disp3 type="file" name="file_anexo2" value="$file_anexo2"><br/>
  Archivo: <a href="comisiones/$comisionid/$anexo2" target="_blank">$anexo2</a>
  <input $disp3 type="hidden" name="anexo2" value="$anexo2">
</td>
</tr>
<tr class=ayuda>
<td colspan=2></td>
</tr>
<!---------------------------------------------------------------------->
<tr class="discorta" $discortastyle>
<td>Anexo 3:</td>
<td>
  <input $disp3 type="file" name="file_anexo3" value="$file_anexo3"><br/>
  Archivo: <a href="comisiones/$comisionid/$anexo3" target="_blank">$anexo3</a>
  <input $disp3 type="hidden" name="anexo3" value="$anexo3">
</td>
</tr>
<tr class=ayuda>
<td colspan=2></td>
</tr>
<!---------------------------------------------------------------------->
<tr class="discorta" $discortastyle>
<td>Resolución:</td>
<td>
  $reslink, $extrares
</td>
</tr>
<tr class=ayuda>
<td colspan=2></td>
</tr>

<!---------------------------------------------------------------------->
<!---------------------------------------------------------------------->
<!---------------------------------------------------------------------->
<tr>
<td colspan=2><hr/><b>Información de la Solicitud</b></td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Identificador de la Comisión:</td>
<td><input $disp3 type="text" name="comisionid" value="$comisionid" size=15 readonly="readonly"></td>
</tr>
<tr class=ayuda>
  <td colspan=2>Identificador único de la comisión</td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Fecha radicacion:</td>
<td><input $disp3 type="text" name="radicacion" value="$radicacion" size=15 readonly="readonly"></td>
</tr>
<tr class=ayuda>
<td colspan=2>Fecha en la que es presentada la solicitud</td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Fecha actualización:</td>
<td><input $disp3 type="text" name="actualizacion" value="$actualizacion" size=15 readonly="readonly"></td>
</tr>
<tr class=ayuda>
<td colspan=2>Última fecha en la que cambio el estado de la
solicitud.</td>
</tr>
<!---------------------------------------------------------------------->
<tr $disp1>
<td>Estado:</td>
<td>$estadosel</td>
</tr>
<tr class=ayuda>
<td colspan=2>Estado de la solicitud. Puede ser: Solicitada (apenas se
radico), Devuelta (la solicitud ha sido devuelta, ver respuesta para
razón), Visto Bueno Director (la solicitud ha recibido el visto bueno
del director pero espera aprobación de Decano), Aprobada por Decano
(solicitud aprobada).</td>
</tr>

<!---------------------------------------------------------------------->
<tr $disp1>
<td colspan=2>
<!---------------------------------------------------------------------->
<!---------------------------------------------------------------------->
<table width=100%>
<tr>
<td colspan=2><hr/><b>Reservado para la administración</b></td>
</tr>
<!---------------------------------------------------------------------->
<tr $disp2 class="discorta" $discortastyle>
<td>Número de Resolucion:</td>
<td><input $disp3 type="text" name="resolucion" value="$resolucion" size=11></td>
</tr>
<tr class=ayuda>
<td colspan=2>El número solo se asigna una vez se ha aprobado la
comisión.</td>
</tr>
<!---------------------------------------------------------------------->
<tr $disp2 class="discorta" $discortastyle>
<td>Fecha de Resolucion:</td>
<td><input $disp3 type="text" name="fecharesolucion" value="$fecharesolucion" size=20></td>
</tr>
<tr class=ayuda>
<td colspan=2>Indique la fecha de la resolución.</td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Visto bueno Director:</td>
<td>$vobosel</td>
</tr>
<!---------------------------------------------------------------------->
<tr $disp2>
<td>Aprobación Decano:</td>
<td>$aprosel</td>
</tr>
<!---------------------------------------------------------------------->
<tr>
<td>Respuesta:</td>
<td>
<textarea name="respuesta" cols=30 rows=10>$respuesta</textarea>
</td>
</tr>
<!---------------------------------------------------------------------->
<tr $disp2 class="discorta" $discortastyle>
<td>Resolución:</td>
<td>$generar</td>
</tr>
</table>
<!---------------------------------------------------------------------->
<!---------------------------------------------------------------------->
</td>
</tr>

<!---------------------------------------------------------------------->
<tr>
<td colspan=2>
<input $disp3 type='submit' name='operation' value='Guardar'>
<input $disp3 type='submit' name='operation' value='Borrar'>
<input $disp3 type='submit' name='action' value='Cancelar'>
</td>
</tr>
</table>
<input $disp3 type='hidden' name='qnew' value='$qnew'>
<input $disp3 type='hidden' name='action' value='Consultar'>
<input $disp3 type='hidden' name='usercedula' value='$usercedula'>
<input $disp3 type='hidden' name='userpass' value='$userpass'>
<input $disp3 type='hidden' name='actualiza' value='$usercedula'>
</form>
C;
}//End of Solicitar

////////////////////////////////////////////////////////////////////////
//LISTA DE SOLICITUDES
////////////////////////////////////////////////////////////////////////
if($action=="Consultar"){
  
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //VERIFY PERMISSIONS
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $where="";
  $generar="";
  if($qperm==0){
    $where="where cedula='$usercedula'";
  }else if($qperm==1){
    $where="where institutoid='$userinstituto'";
  }

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //VERIFY PERMISSIONS
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $fields="comisionid,cedula,estado,actualizacion,actualiza,aprobacion,institutoid,tipocom,vistobueno";
  $solicitudes=mysqlCmd("select $fields,TIMESTAMP(radicacion) as radicacion from Comisiones $where order by radicacion desc;",$qout=1);
  if($solicitudes==0){$nsolicitudes=0;}
  else{$nsolicitudes=count($solicitudes);}
  
$table=<<<T
<table width=80% border=1px>
<tr>
  <td width=10%>Comisión</td>
  <td width=10%>Radicación</td>
  <td width=10%>Actualización</td>
  <td>Tipo</td>
  <td>Estado</td>
  <td>Instituto</td>
  <td width=40%>Solicitante</td>
  <td>Acciones</td>
  <td>Descargas</td>
  <td>Otras</td>
</tr>
T;
  
  for($i=0;$i<$nsolicitudes;$i++){
    $comision=$solicitudes[$i];
    $tcomisionid=$comision['comisionid'];
    $tcedula=$comision['cedula'];
    $testadox=$comision['estado'];
    $testado=$ESTADOS[$testadox];
    $ttipocomx=$comision['tipocom'];
    $ttipocom=$TIPOSCOM[$ttipocomx];

    $estadocolor=$COLORS[$testadox];
    if($ttipocomx=="noremunerada"){
      $estadocolor=$COLORS[$testadox."_noremunerada"];
    }

    $tradicacion=$comision['radicacion'];
    $tinstituto=$INSTITUTOS[$comision['institutoid']];
    $tactualiza=$comision['actualiza'];
    $tactualizacion=$comision['actualizacion'];
    $taprobacion=$comision['aprobacion'];
    $tvistobueno=$comision['vistobueno'];

    $results=mysqlCmd("select nombre from Profesores where cedula='$tcedula'");
    $tnombre=$results[0];
    $generar="";
    $reslink="";
    $extrares="";
    if($qperm==2 and $taprobacion=="Si" and $ttipocomx!="noremunerada"){
      $generar="<!-- -------------------------------------------------- -->
    <a href=?$USERSTRING&comisionid=$tcomisionid&operation=Resolucion&action=Consultar>
      Generar</a>";
    }
    if(file_exists("comisiones/$tcomisionid/resolucion-$tcomisionid.html")){
      $reslink="<!-- -------------------------------------------------- -->
    <a href=comisiones/$tcomisionid/resolucion-$tcomisionid.html target='_blank'>
      Resolucion</a>
  (<a href=comisiones/$tcomisionid/resolucion-$tcomisionid.pdf target='_blank'>pdf</a>)
";
      $extrares="";
      if($qperm==2){
	$extrares="<!-- -------------------------------------------------- -->
    <a href=comisiones/$tcomisionid/resolucion-blank-$tcomisionid.html target='_blank'>
      Resolución imprimible</a>
  (<a href=comisiones/$tcomisionid/resolucion-blank-$tcomisionid.pdf target='_blank'>pdf imprimible</a>)
";
      }
      
    }else{
      $reslink="<i>No disponible</i>";
    }
    $borrar="";
    if($taprobacion!="Si" and $tvistobueno!="Si"){
      $borrar="<!-- -------------------------------------------------- -->
  <a href=?$USERSTRING&comisionid=$tcomisionid&operation=Borrar&action=Consultar>
  Borrar</a>";
    }   

$table.=<<<T
<tr style='background:$estadocolor'>
  <td>
    <a href=?$USERSTRING&loadcomision&comisionid=$tcomisionid&action=Solicitar>
      $tcomisionid
    </a>
  </td>
  <td>$tradicacion</td>
  <td>$tactualizacion<br/>Usuario:$actualiza</td>
  <td>$ttipocom</td>
  <td>$testado</td>
  <td>$tinstituto</td>
  <td>$tcedula, $tnombre</td>
  <td>
    $generar
  </td>
  <td>
  $reslink
  $extrares
  </td>
  <td>
$borrar
  </td>
</tr>
T;
  }
$table.=<<<T
</table>
T;

 if($nsolicitudes==0){
   $table="<i>No hay solicitudes.</i>";
 }

 $informes="";
 if($qperm){
   $informes="<a href='?usercedula=$cedula&userpass=$userpass&action=Informes'>Informes</a>";
 }

$content.=<<<C
$error
<a href="?usercedula=$usercedula&userpass=$userpass&action=Solicitar">Nueva Solicitud</a> | 
<a href="?$USERSTRING&action=Consultar">Lista de Solicitudes</a> | <a href="?$USERSTRING">Salir</a>
<h2>Lista de solicitudes.</h2>
  Número de solicitudes: $nsolicitudes
<p></p>
  <table border=0px><tr>
  <td>Convenciones:</td>
  <td style=background:#FFFF99>Comisión Solicitada</td>
  <td style=background:#FFCC99>Permiso Solicitado</td>
  <td style=background:#99CCFF>Visto Bueno</td>
  <td style=background:#00CC99>Comisión Aprobada</td>
  <td style=background:#33CCCC>Permiso Aprobado</td>
  </tr></table>
<p></p>
$table
<p></p>
$informes
C;
 }

////////////////////////////////////////////////////////////////////////
//LISTA DE SOLICITUDES
////////////////////////////////////////////////////////////////////////
if($action=="Informes"){

  if($qperm==0){
    $error=errorMessage("No autorizado para generar informes.");
    $content.="$error";
    goto footer;
  }
  
  if(isBlank($command)){
    $command="* from Comisiones";
  }
  if(preg_match("/;/",$command) or 
     preg_match("/select/i",$command)){
    $error=errorMessage("El comando esta equivocado.");
    $cmd="select null";
  }else{
    $cmd="select $command";
  }

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //DATABASE QUERY
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $solicitudes=mysqlCmd($cmd,$qout=1);
  if($solicitudes==0){$nsolicitudes=0;}
  else{$nsolicitudes=count($solicitudes);}

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //DATABASE CHARACTERISTICS
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  $file="scratch/informe-$usercedula.csv";
  $fl=fopen($file,"w");

  $fields=array();
  $fields_txt="";
  foreach(array_keys($solicitudes[0]) as $field){
    if(preg_match("/^\d+$/",$field)){continue;}
    array_push($fields,$field);
    $fields_txt.="$field;";
  }
  fwrite($fl,utf8_encode(trim($fields_txt,";")."\n"));

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //GENERATE TABLE
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$table=<<<T
<b>Número de registros</b>: $nsolicitudes<br/>
<b>Archivo de informe</b>: <a href="scratch/informe-$usercedula.csv">informe</a>
<br/><br/>
<table border=1px>
  <tr>
T;
 foreach($fields as $field){
$table.=<<<T
<td>$field</td>
T;
 }
$table.="</tr>";
 
 foreach($solicitudes as $solicitud){
   $table.="<tr>";
   $values="";
   foreach($fields as $field){
     $value=$solicitud[$field];
     $values.="\"$value\";";
     if($field=="comisionid"){
$value=<<<C
    <a href=?$USERSTRING&loadcomision&comisionid=$comisionid&action=Solicitar>
      $value
    </a>
C;
     }
$table.=<<<T
<td>$value</td>
T;
   }
   fwrite($fl,utf8_encode(trim($values,";")."\n"));
   $table.="</tr>";
 }
 $table.="</table>";
 fclose($fl);

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //GENERATE REPORTE
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

$tabla=<<<T
T;

$content.=<<<C
$error
<a href="?usercedula=$cedula&userpass=$userpass&action=Solicitar">Nueva Solicitud</a> | 
<a href="?usercedula=$cedula&userpass=$userpass&action=Consultar">Consultar</a> |
<a href="?$USERSTRING">Salir</a>
<h2>Informes Completos</h2>
  <form method="GET">
  <input type="hidden" name="usercedula" value="$usercedula">
  <input type="hidden" name="action" value="Informes">
  Buscar: <input type="text" name="command" value="$command" size=100>
  <input type="submit" name="execute" value="Proceda">
  </form>
<a href="JavaScript:void(null)"
   onclick="$('#ejemplos').toggle('fast',null);">Ejemplos</a><br/>
<table id="ejemplos" border=0px style="margin-left:10px;font-size:12px;display:none;color:blue;">
  <tr>
    <td>
      Muestre todas las comisiones presentadas a la fecha:<br/>
      <pre>
	select * from Comisiones
      </pre>
    </td>
  </tr>
  <tr>
    <td>
      Muestre las comisiones presentadas por la cedula 71755174:<br/>
      <pre>
	select * from Comisiones where cedula='71755174'
      </pre>
    </td>
  </tr>
</table>
<p></p>
$table
C;
}

////////////////////////////////////////////////////////////////////////
//FOOTER
////////////////////////////////////////////////////////////////////////
footer:
$content.=<<<C
</form>
<hr/>
<div style="font-size:10px;font-style:italic">
Desarrollado por <a href="mailto:jorge.zuluaga@udea.edu.co">Jorge I. Zuluaga</a> (C) 2015.
</div>
</body>
</html>
C;

////////////////////////////////////////////////////////////////////////
//DISPLAY CONTENT
////////////////////////////////////////////////////////////////////////
echo $content;
?>
