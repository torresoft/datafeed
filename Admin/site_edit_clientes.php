<?php 
session_start();
	if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && $_SESSION["restprofile"]=="A"){
		require_once("../autoload.php");
		global $relaciones;
		$dataTables=new dsTables();
		$gf=new generalFunctions;
		$titulo="ADMINISTRACI&Oacute;N DE CLIENTES";
		$tabla="clientes";
		$sender=$_SERVER['PHP_SELF'];
		$filterKey="ID_SITIO";
		$filterVal=$_SESSION["restbus"];
		$actividad=$gf->cleanVar($_GET["flag"]);
		if($actividad=="ver"){
			$gettabla = $dataTables->armaTablaDyRel($tabla,"1",1,1,0,$filterKey,$filterVal,$sender);
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