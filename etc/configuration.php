<?php
////////////////////////////////////////////////////////////////////////
//USER CONFIGURATION
////////////////////////////////////////////////////////////////////////
$DECANOTXT="decana";
$COORDINADOR="Dra. Aura Aleida Jaramillo Valencia";
$COORDINADORTXT="coordinadora";

$TIPOS=array("Vinculado"=>"Docente de Tiempo Completo",
	     "Ocasional"=>"Docente Ocasional de Tiempo Completo",
	     "Visitante"=>"Profesor Visitante");

$TIPOSCOM=array("servicios"=>"Comisión de Servicios",
		"estudio"=>"Comisión de Estudios",	
		"noremunerada"=>"Comisión no Remunerada"
		);

$INSTITUTOS=array("fisica"=>"Instituto de Física",
		  "biologia"=>"Instituto de Biología",
		  "quimica"=>"Instituto de Química",
		  "matematicas"=>"Instituto de Matemáticas",
		  "decanatura"=>"Decanatura",
		  );

$ESTADOS=array("solicitada"=>"Solicitada",
	       "devuelta"=>"Devuelta",
	       "vistobueno"=>"Visto Bueno Director",
	       "aprobada"=>"Aprobada por Decano");

$TIPOSID=array("cedula"=>"Cédula de Ciudadanía",
	       "extranjeria"=>"Cédula de Extranjería",
	       "pasaporte"=>"Pasaporte");

$SINO=array("No"=>"No","Si"=>"Si");

////////////////////////////////////////////////////////////////////////
//CONFIGURATION
////////////////////////////////////////////////////////////////////////
$LOGOUDEA="http://astronomia-udea.co/principal/sites/default/files";
$USER="comisiones";
$PASSWORD="123";
$DATABASE="Comisiones";
$H2PDF="../../util/wkhtmltopdf-i386";
//$H2PDF="../../util/wkhtmltopdf-amd64";

$TEXTS=array("presentacion","respuesta");
$PERMISOS=array("Profesor","Director","Decano");

////////////////////////////////////////////////////////////////////////
//GLOBAL VARIABLES
////////////////////////////////////////////////////////////////////////
foreach(array_keys($_GET) as $field){
    $$field=$_GET[$field];
}
foreach(array_keys($_POST) as $field){
    $$field=$_POST[$field];
}

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

function generateSelection($values,$name,$value,$disabled="")
{
  $parts=$values;
  $selection="";
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

////////////////////////////////////////////////////////////////////////
//GLOBAL ACTIONS
////////////////////////////////////////////////////////////////////////

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//DATABASE CONNECTION
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$DB=mysqli_connect("localhost",$USER,$PASSWORD,$DATABASE);
$result=mysqlCmd("select now();",$qout=0,$qlog=0);
$DATE=$result[0];

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CSS
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$style=<<<STYLE
<style>
td{
  vertical-align:top;
}
tr.ayuda{
  font-size:12px;
  font-style:italic;
  color:blue;
  display:none;
}
</style>
STYLE;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//FIELDS OF COMISIONES
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$columns=mysqlCmd("show columns from Comisiones;",$qout=1,$qlog=0);
$ncolumns=count($columns);
$FIELDS_COMISIONES=array();
for($i=0;$i<$ncolumns;$i++){
  $column=$columns[$i];
  array_push($FIELDS_COMISIONES,$column["Field"]);
}
//print_r($FIELDS_COMISIONES);echo "<br/>";

$columns=mysqlCmd("show columns from Profesores;",$qout=1,$qlog=0);
$ncolumns=count($columns);
$FIELDS_PROFESORES=array();
for($i=0;$i<$ncolumns;$i++){
  $column=$columns[$i];
  array_push($FIELDS_PROFESORES,$column["Field"]);
}
//print_r($FIELDS_PROFESORES);echo "<br/>";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CEDULA DECANO
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$out=mysqlCmd("select cedulajefe,institutoid from Institutos",$qout=1);
$DIRECTORS=array();
foreach($out as $instituto){
  $DIRECTORS[$instituto["institutoid"]]=$instituto["cedulajefe"];
}

$RANDOMMODE=generateRandomString(100);
$USERSTRING="usercedula=$usercedula&mode=$RANDOMMODE&userpass=$userpass";
?>
