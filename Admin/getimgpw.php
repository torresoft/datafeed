<?php
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="A" || (isset($_GET["folder"])))){
	include_once("../autoload.php");
	$gf=new generalFunctions;
	$controlto=$gf->cleanVar($_GET["controlto"]);
	$rd=$gf->cleanVar($_GET["rnd"]);
	$url="Admin/getimgpw.php";
	
	if(!isset($_POST["folder"])){
		$fld="private";
	}else{
		$fld=$_POST["folder"];
	}
	if(isset($_GET["folder"]) && $_GET["folder"]!="undefined" && $_GET["folder"]!=""){
		$fld= $gf->cleanVar($_GET["folder"]);
	}
	if($fld=="private"){
		if (!file_exists("../../hostfile/")) {
			mkdir("../../hostfile/",0777);
		}
		if (!file_exists("../../hostfile/users/")) {
			mkdir("../../hostfile/users/",0777);
		}
		if (!file_exists("../../hostfile/users/us_{$_SESSION["restuiduser"]}/")) {
			mkdir("../../hostfile/users/us_{$_SESSION["restuiduser"]}/",0777);
		}
		$dir="../hostfile/users/us_{$_SESSION["restuiduser"]}/";
	}else{
		if($fld=="public_usr"){
			if (!file_exists("../f64selif4/")) {
				mkdir("../f64selif4/",0777);
			}
			$dir="f64selif4/us_{$_SESSION["restuiduser"]}/";
			if (!file_exists("../f64selif4/us_{$_SESSION["restuiduser"]}/")) {
				mkdir("../f64selif4/us_{$_SESSION["restuiduser"]}/",0777);
			}
			$dir="f64selif4/us_{$_SESSION["restuiduser"]}/";
		}elseif($fld=="public"){
			$dir="f64selif4/";
		}
	}
	if(isset($_GET["callback"])){
		$callback=$gf->cleanVar($_GET["callback"]);
	}else{
		$callback="";
	}

	
	if($dir!=""){
		echo $gf->utf8("<div id='carpeta' class='panel panel-default'><div class='panel-body'><div id='image-container' class='row' style='height:270px;verflow-x:hidden;overflow-y:scroll;'>");
		if(!file_exists("../".$dir)){
			mkdir("../".$dir,0777);
		}
		$directorio=opendir("../".$dir); 
		$i=0;
		$losfiles=array();
		while ($archivo = readdir($directorio)){
			$losfiles[]=$archivo;
		}
		sort($losfiles);
		//echo $gf->utf8("<ul class='list-group'>");
		foreach($losfiles as $thefile){
			$laex=explode(".",$thefile);
			$fextencion=strtolower(end($laex));
			if($fextencion=="png" || $fextencion=="jpg" || $fextencion=="gif"){
				$i++;
				echo $gf->utf8("<div class='col-lg-2 col-md-3 col-sm-6 col-xs-6 lasims' style='height:120px;'><div ondblclick=\"setImagepw('$controlto','imgn_$i','$rd','$callback')\" style='background-image:url(\"readfl.php?filename=$dir$thefile\");height:100px;background-position:center center;background-repeat:no-repeat;background-size:100%;background-attachment:local;overflow:hidden;position:relative;' id='imgn_$i' class='panel panel-default link' value='$dir$thefile'><span style='background:rgba(255,255,255,0.7);position:absolute;bottom:0px;left:0px;width:100%;height;16px;overflow:hidden;font-size:9px;'>$thefile</span></div></div>");
			}
		}
		
		echo $gf->utf8("</div></div>");
		echo "<div class='panel-footer' style='position:relative;float:left;width:100%;'>Buscar: <input type='text' id='filtergetimg' onkeyup=\"filtrar('filtergetimg','lasims')\" /></div><div><form name='formni' id='formni' action='Admin/files/file_upload.php' method='post' enctype='multipart/form-data'><label class='btn btn-primary' for='archivo'><span class='glyphicon glyphicon-upload'></span>Subir un archivo<input name='archivo' id='archivo' type='file' style='width:200px;display:none;' onchange=\"uploadImage('formni','image-container','archivo','$rd','$controlto','$callback')\" /></label><input type='hidden' name='carpeta' id='lacarpeta' value='$dir' /></form><div id='elifmni' style='display:none;width:0px;height:0px;overflow:hidden;'></div><div id='progress-div'><div id='progress-bar'></div></div></div>";
		
		closedir($directorio); 
	}else{
		echo $gf->utf8("<div class='panel bg-info' style='width:99%;position:relative;float:left;'>Seleccione la carpeta:<select name='sel_folder' onchange=\"cargaHTMLvars('carpeta','$url?callback=$callback&amp;controlto=$controlto&amp;rd=$rd&amp;folder='+this.value)\">
		<option value=''>Seleccione...</option>
		<option value='./archivos/files/'>Archivos</option>
		<option value='./archivos/images/'>Imagenes</option>
		<option value='./menu_icons/'>Iconos</option>
		</select></div>
		<div id='carpeta' style='width:580px;overflow:hidden;height:350px;margin:5px;' class='panel-body'></div>");
	}
}
?>
