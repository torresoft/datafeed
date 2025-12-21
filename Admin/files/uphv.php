<?php session_start();
if(!isset($_SESSION["userrol"]) || !isset($_SESSION["userfullname"]) || !isset($_SESSION["userimg"]) || !isset($_SESSION["usermail"])){
	echo "Sin sesi&oacute;n";
}else{
	include_once("../../config.php");
	$control="archivohv";
  	$tamano = $_FILES[$control]['size'];
    $tipo = $_FILES[$control]['type'];
    $archivo = $_FILES[$control]['name'];
	$temporal = $_FILES[$control]['tmp_name'];
	$id_integrante=$_POST["id_integrante"];
	$tipom=in_array("application",explode("/",$tipo));
	$tipopdf=in_array("pdf",explode("/",$tipo));
	if($tipom && $tipopdf){
		$subio=sube_Archivo($tamano,$tipo,$temporal,$id_integrante);
		if($subio==1){
			mysql_query("UPDATE base_integrantes SET HV='1' WHERE ID_INTEGRANTE='$id_integrante'",$link);
			echo "OK";
		}else{
			echo "Error subiendo el archivo";
		}
	}else{
		echo "Formato no válido";
	}
}	
function sube_Archivo($tamano,$tipo,$temporal,$id_integrante){
   	$destino =  "../../archivos/HVSM_".$id_integrante.".pdf";
	if (copy($temporal,$destino)) {
		return 1;
	} else {
		return 0;
	}
}

	
	
?>
