<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="A" || ($_SESSION["restcajeroserv"]==1 && $_SESSION["restprofile"]=="J"))){
	require_once("../autoload.php");
	$gf=new generalFunctions;
	$gc=new generalComponents;
	global $relaciones;
	$dataTables=new dsTables();
	$actividad=$gf->cleanVar($_GET["flag"]);
	if(isset($_GET["ent"])){
		$tabla=$gf->cleanVar($_GET["ent"]);
	}else{
		$tabla="gastos";
	}
	$titulo="REGISTRO DE GASTOS";
	$sender=$_SERVER['PHP_SELF'];
	if(isset($_GET["filterKey"])){
		$filterKey=$gf->cleanVar($_GET["filterKey"]);
		$filterVal=$gf->cleanVar($_GET["filterVal"]);
	}else{
		$filterKey="ID_SITIO";
		$filterVal=$_SESSION["restbus"];
	}

	$rigu=array(1,1,1,1,1);

	
	if($actividad=="ver"){
		// $fkf["gastos_tipos"]=array("ID_SITIO"=>$_SESSION["restbus"]);

		// $extrabuttons[]=array("nombre"=>'Imprimir','icono'=>'fa-print','clase'=>'btn-info','funcion'=>"$sender?flag=gt_print","contenedor"=>"");
		// $gettabla = $dataTables->armaTablaDyRel($tabla,"1",$rigu[1],$rigu[1],$rigu[2],$filterKey,$filterVal,$sender,$fkf,$extrabuttons);
		// echo $gf->utf8($gettabla);

		if(isset($_GET["pg"])){
			$page= $gf->cleanVar($_GET["pg"]);
		}else{
			$page=1;
		}
		if(isset($_POST["search"])){
			$search=$_POST["search"];
		}else{
			$search="";
		}
		$COND=1;
		
		$extrafn[]=array("nombre"=>'Imprimir','icono'=>'fa-print','clase'=>'btn-info','funcion'=>"$sender?flag=gt_print","contenedor"=>"");

		if($search!=""){
			$siri=trim($search);
			$no_result="No hay registros con $siri";
		}else{
			$no_result="No hay registros de gastos";
		}
		
		$gettabla = $dataTables->armaTablaPaginate($tabla,"1",$rigu[1],$rigu[1],$rigu[2],$filterKey,$filterVal,$sender,array(),$extrafn,10,$page,$search,"ver",$no_result,array("VALOR"));
		echo $gf->utf8($gettabla);




	}elseif($actividad=="gt_print"){
		$key=$gf->cleanVar($_GET["key"]);
		echo $gf->utf8("
		<div class='embed-responsive embed-responsive-4by3'>
		<iframe class='embed-responsive-item' src='Admin/pdf_gasto.php?id_gto=$key' height='300' frameborder='no'></iframe>
		</div>");

	}elseif($actividad=="editar"){
		$Vkey=$gf->cleanVar($_GET["Vkey"]);
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		$fkf["gastos_tipos"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$fkf["servicio"]=array("ID_SITIO"=>$_SESSION["restbus"],"ESTADO"=>0);
		$fkf["cocinas"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$fkf["formas_pago"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey,$filterVal,$dialogo,"getAux(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')",$fkf);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="nuevo"){
		$dialogo=$gf->cleanVar($_GET["rnd"]);
		$fkf["gastos_tipos"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$fkf["servicio"]=array("ID_SITIO"=>$_SESSION["restbus"],"ESTADO"=>0);
		$fkf["cocinas"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$fkf["formas_pago"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$gettabla = $dataTables->devuelveTablaNewItemDyRel($tabla,$filterKey,$filterVal,$dialogo,"getAux(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')",$fkf);
		echo $gf->utf8($gettabla);
	
	}elseif($actividad=="serv_start"){
		$fkf=array();
		$sons=array("tabla"=>"inventario","fk"=>"ID_COMPRA","cond"=>"ID_COMPRA<>'0'");
		$gettabla = $dataTables->armaItems("servicio","FECHA DESC",0,0,0,"ID_SITIO",$_SESSION["restbus"],$sender,"$sender?flag=serv_go","level3",0,$fkf,$sons);
		echo $gf->utf8("
		<div class='row'>
			<div class='col-md-4' id='level2'>".$gettabla."</div>
			<div class='col-md-8' id='level3'></div>
		</div>");
	}elseif($actividad=="serv_go"){
		$id_servicio=$gf->cleanVar($_GET["key"]);
		$gettabla = $dataTables->armaTablaDyRel($tabla,"1",0,0,0,"ID_SERVICIO",$id_servicio,$sender);
		echo $gf->utf8($gettabla);
	
	}else{
		echo "Ninguna solicitud";
	}
	
}else{
	echo "No has iniciado sesion!";
}
?>