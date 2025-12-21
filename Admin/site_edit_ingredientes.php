<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && $_SESSION["restprofile"]=="A"){
	
	require_once("../autoload.php");
	$gf=new generalFunctions;
	global $relaciones;
	$dataTables=new dsTables();
	$tabla="ingredientes";
	$titulo="LISTADO DE INGREDIENTES";
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
		$sons=array();
		$sons=array("tabla"=>"inventario","fk"=>"ID_INGREDIENTE");
		$add=array(array("nombre"=>"Platos en uso","icono"=>"fa-tasks","clase"=>"btn-info","funcion"=>"$sender?flag=elementos_uso","contenedor"=>""));
		$gettabla = $dataTables->armaTablaDyRel($tabla,"1",$rigu[1],$rigu[1],$rigu[2],$filterKey,$filterVal,$sender,array(),$add,$sons);
		echo $gf->utf8($gettabla);
		
	}elseif($actividad=="elementos_uso"){
		$id_ingre=$gf->cleanVar($_GET["key"]);
		$ds=$gf->dataSet("SELECT PL.NOMBRE, PC.NOMBRE AS RACION, RO.NOMBRE AS OPCION, RI.CANTIDAD  FROM platos PL JOIN platos_composicion PC ON PC.ID_PLATO=PL.ID_PLATO JOIN `racion_opciones` RO ON RO.ID_RACION=PC.ID_RACION JOIN racion_ingredientes RI ON RI.ID_INGREDIENTE='$id_ingre' AND RI.ID_RACION=RO.ID_OPCION ORDER BY PL.NOMBRE");
		if(count($ds)>0){
			echo $gf->utf8("PLATOS QUE UTILIZAN ESTE INGREDIENTE<hr />
			<table class='table table-bordered'><tr><td>PLATO</td><td>COMPONENTE</td><td>OPCION</td><td>CANTIDAD</td></tr>");
			foreach($ds as $rw){
				$plato=$rw["NOMBRE"];
				$racion=$rw["RACION"];
				$opcion=$rw["OPCION"];
				$cantidad=$rw["CANTIDAD"];
				echo $gf->utf8("<tr><td>$plato</td><td>$racion</td><td>$opcion</td><td>$cantidad</td></tr>");
			}
			echo $gf->utf8("</table>");
		}else{
			echo $gf->utf8("El material no es utilizado en ning&uacute;n producto");
		}
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