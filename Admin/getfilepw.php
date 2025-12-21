<?php
session_start();
include_once("../autoload.php");
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && $_SESSION["restprofile"]=="A"){
	$gf=new generalFunctions;
	$controlto=$gf->cleanVar($_GET["controlto"]);
	$rd=$gf->cleanVar($_GET["rnd"]);
	if(isset($_GET["extfiltro"])){
		$extfiltro=$gf->cleanVar($_GET["extfiltro"]);
	}else{
		$extfiltro="";
	}
	if(isset($_GET["callback"])){
		$callback=$gf->cleanVar($_GET["callback"]);
	}else{
		$callback="";
	}
	$url="Admin/getfilepw.php?dlg=1";
	
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
	if($dir!=""){
		echo $gf->utf8("<div id='carpeta' class='panel panel-default' style='height:390px;'><div id='file-container' class='panel-body' style='height:270px;overflow-x:hidden;overflow-y:auto;'>");
		if(!file_exists("../".$dir)){
			mkdir("../".$dir,0777);
		}
		$directorio=opendir("../".$dir); 
		$i=0;
		$losfiles=array();
		if($extfiltro==""){
			while ($archivo = readdir($directorio)){
				$losfiles[]=$archivo;
			}
		}else{
			$extensiones=explode(",",$extfiltro);
			if(sizeof($extensiones)==0){
				$extensiones=array($extfiltro);	
			}
			while ($archivo = readdir($directorio)){
				$ext=strtolower(end(explode(".",$archivo)));
				if(in_array($ext,$extensiones)){
					$losfiles[]=$archivo;
				}
			}
		}
		sort($losfiles);
		if(sizeof($losfiles)==0){
			echo "<span class='nofile'>No hay archivos en esta ubicaci&oacute;n con el formato especificado</span>";	
		}
		echo $gf->utf8("<ul class='list-group'>");
		foreach($losfiles as $thefile){
			$arex=explode(".",$thefile);
			$fextencion=strtolower(end($arex));
			if(!file_exists("../iconos/$fextencion.png")){
				$fextencion="file";
			}
			if($thefile!="." && $thefile!=".."){
				$i++;
				echo $gf->utf8("<li id='file_$i' class='losfils link list-group-item' ondblclick=\"setFilepw('$controlto','file_$i','$rd','$callback')\" onclick=\"setSelect('losfils',event)\" value='$dir$thefile'><img src='iconos/$fextencion.png' style='width:22px;height:22px;' align='left' />$thefile</li>");
			}
		}
		echo $gf->utf8("</ul></div>");
		echo "<div class='panel-footer' style='position:relative;float:left;width:100%;'>Buscar: <input type='text' id='filtergetfil' onkeyup=\"filtrar('filtergetfil','losfils')\" /></div><div style='width:578px;height:90px;float:left;position:relative;float:left;'><form name='formni' id='formni' action='Admin/files/file_upload.php' method='post' enctype='multipart/form-data'><label class='btn btn-primary' for='archivo'><span class='glyphicon glyphicon-upload'></span> Subir un archivo<input name='archivo' id='archivo' type='file' style='width:200px;display:none;' onchange=\"uploadFile('formni','file-container','archivo','$rd','$controlto','$callback')\" /></label><input type='hidden' name='carpeta' id='lacarpeta' value='$dir' /></form><div id='elifmni' style='display:none;width:0px;height:0px;overflow:hidden;'></div><div id='progress-div'><div id='progress-bar'></div></div></div>";
		closedir($directorio); 
	}else{
		echo $gf->utf8("<div class='panel bg-info' style='width:99%;'>Seleccione la carpeta:<select name='sel_folder' onchange=\"cargaHTMLvars('carpeta','$url&amp;callback=$callback&amp;extfiltro=$extfiltro&amp;controlto=$controlto&amp;&amp;rd=$rd&amp;folder='+this.value)\">
		<option value=''>Seleccione...</option>
		<option value='./archivos/files/'>Archivos</option>
		<option value='./archivos/images/'>Im&aacute;genes</option>
		</select></div>
		<div id='carpeta' style='width:580px;overflow:hidden;height:350px;margin:5px;' class='panel-body'></div>");
	}

}
?>
