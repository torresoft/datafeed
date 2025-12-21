<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && $_SESSION["restprofile"]=="A"){
	
	require_once("../autoload.php");
	$gf=new generalFunctions;
	global $relaciones;
	$dataTables=new dsTables();
	$tabla="mesas";
	$titulo="MESAS DEL ESTABLECIMIENTO";
	$sender=$_SERVER['PHP_SELF'];
	$filterKey="ID_SITIO";
	$filterVal=$_SESSION["restbus"];

	if(isset($_GET["hnd"])){
		$hnd=$gf->cleanVar($_GET["hnd"]);
		$rigu=$_SESSION["UP"][$hnd];
	}else{
		$rigu=array(1,1,1,1,1);
	}
	$actividad=$gf->cleanVar($_GET["flag"]);
	if($actividad=="ver"){
		$sons=array("tabla"=>"pedidos","fk"=>"ID_MESA");
		$gettabla = $dataTables->armaTablaDyRel($tabla,"1",$rigu[1],$rigu[1],$rigu[2],$filterKey,$filterVal,$sender,array(),array(),$sons);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="editar"){
		$Vkey=$gf->cleanVar($_GET["Vkey"]);
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		$fkf["mesas_grupos"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey,$filterVal,$dialogo,"getAux(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')",$fkf);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="nuevo"){
		$dialogo=$gf->cleanVar($_GET["rnd"]);
		$fkf["mesas_grupos"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$gettabla = $dataTables->devuelveTablaNewItemDyRel($tabla,$filterKey,$filterVal,$dialogo,"getAux(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')",$fkf);
		echo $gf->utf8($gettabla);
	
	}else{
		echo "Ninguna solicitud";
	}
	
}else{
	echo "No has iniciado sesion!";
}
?>