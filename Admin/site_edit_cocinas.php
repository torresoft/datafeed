<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && $_SESSION["restprofile"]=="A"){
	
	require_once("../autoload.php");
	$gf=new generalFunctions;
	global $relaciones;
	$dataTables=new dsTables();
	$tabla="cocinas";
	$titulo="COCINAS DEL ESTABLECIMIENTO";
	$sender=$_SERVER['PHP_SELF'];
	$filterKey="ID_SITIO";
	$filterVal=$_SESSION["restbus"];
	$actividad=$gf->cleanVar($_GET["flag"]);
	if(isset($_GET["hnd"])){
		$hnd=$gf->cleanVar($_GET["hnd"]);
		$rigu=$_SESSION["UP"][$hnd];
	}else{
		$rigu=array(1,1,1,1,1);
	}
	
	if($actividad=="ver"){
		$sons=array("tabla"=>"platos","fk"=>"ID_COCINA","cond"=>"ID_COCINA<>'0'");
		$gettabla = $dataTables->armaTablaDyRel($tabla,"1",$rigu[1],$rigu[1],$rigu[2],$filterKey,$filterVal,$sender,array(),array(),$sons);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="editar"){
		$Vkey=$gf->cleanVar($_GET["Vkey"]);
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey,$filterVal,$dialogo,"getAux(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')");
		echo $gf->utf8($gettabla);
	}elseif($actividad=="nuevo"){
		$dialogo=$gf->cleanVar($_GET["rnd"]);
		$gettabla = $dataTables->devuelveTablaNewItemDyRel($tabla,$filterKey,$filterVal,$dialogo,"getAux(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')");
		echo $gf->utf8($gettabla);
	
	}else{
		echo "Ninguna solicitud";
	}
	
}else{
	echo "No has iniciado sesion!";
}
?>