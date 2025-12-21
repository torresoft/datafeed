<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && $_SESSION["restprofile"]=="A"){
	
	require_once("../autoload.php");
	$gf=new generalFunctions;
	$gc=new generalComponents;
	global $relaciones;
	$dataTables=new dsTables();
	if(isset($_GET["ent"])){
		$tabla=$gf->cleanVar($_GET["ent"]);
	}else{
		$tabla="impuestos";
	}
	$titulo="CONFIGURACI&Oacute;N DE IMPUESTOS";
	$sender=$_SERVER['PHP_SELF'];
	if(isset($_GET["filterKey"])){
		$filterKey=$gf->cleanVar($_GET["filterKey"]);
		$filterVal=$gf->cleanVar($_GET["filterVal"]);
	}else{
		$filterKey="ID_SITIO";
		$filterVal=$_SESSION["restbus"];
	}

	$rigu=array(1,1,1,1,1);

	$actividad=$gf->cleanVar($_GET["flag"]);
	if($actividad=="start"){
		$fkf=array();
		$sons=array("tabla"=>"platos","fk"=>"ID_IMPUESTO","cond"=>"ID_PLATO>0");
		$gettabla = $dataTables->armaItems("impuestos","NOMBRE",1,1,1,"ID_SITIO",$_SESSION["restbus"],$sender,"$sender?flag=level3","level3",0,$fkf,$sons);
		echo $gf->utf8("
		<div class='row'>
			<div class='col-md-4' id='level2'>".$gettabla."</div>
			<div class='col-md-8' id='level3'></div>
		</div>");

	}elseif($actividad=="applyall2"){
		$key=$gf->cleanVar($_GET["key"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$tk=$gf->cleanVar($_GET["tk"]);
		if($_SESSION["restprofile"]!="A") exit;
		if($tk==$_SESSION["tkall_pro_im"]){
			$ok=$gf->dataIn("UPDATE platos SET ID_IMPUESTO=:impuesto WHERE ID_CATEGORIA IN(SELECT ID_CATEGORIA FROM platos_categorias WHERE ID_SITIO=:sitio)",array(":impuesto"=>$key,":sitio"=>$_SESSION["restbus"]));
			if($ok){
				echo "Proceso realizado <input type='hidden' id='callbackeval' value=\"closeD('$rnd')\" />";
			}else{
				echo "Hubo un error al realizar el proceso.";
			}
		}else{
			echo "Error 854 - No tienes permisos para esta acci&oacute;n";
		}

	}elseif($actividad=="applyall"){
		$key=$gf->cleanVar($_GET["key"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		if($_SESSION["restprofile"]!="A") exit;
		$tk=rand(99,99999);
		$_SESSION["tkall_pro_im"]=$tk;

		echo $gf->utf8("ATENCI&Oacute;N: Se aplicar&aacute; este impuesto a todos los platos, reemplazando cualquier dato asignado previamente, continuar?<hr />
		<button class='btn btn-danger btn-sm' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=applyall2&key=$key&rnd=$rnd&tk=$tk')\">Continuar</button>

		<button class='btn btn-info btn-sm' onclick=\"closeD('$rnd')\">Cancelar</button>

		");
	}elseif($actividad=="level3"){
		$filterVal=$gf->cleanVar($_GET["key"]);
		
		$lCom=$gf->dataSet("SELECT P.ID_PLATO, P.NOMBRE AS PLATO, C.NOMBRE AS CATEGORIA FROM platos_categorias C JOIN platos P ON P.ID_CATEGORIA=C.ID_CATEGORIA WHERE P.ID_IMPUESTO=:impuesto AND C.ID_SITIO=:sitio",array(":impuesto"=>$filterVal,":sitio"=>$_SESSION["restbus"]));
		
		
		echo $gf->utf8("
		<div class='box box-danger'>
			<div class='box-header'>Productos Gravados con este impuesto </div>
			<div class='box-body'>
			<button class='btn btn-warning btn-sm' onclick=\"getDialog('$sender?flag=applyall&key=$filterVal','500','Aplicar\ Impuesto','','','cargaHTMLvars(\'level3\',\'$sender?flag=level3&key=$filterVal\')')\">Aplicar este impuesto a todos los platos</button>
			<table class='table'>
				<thead>
					<tr>
						<th>CATEGORIA</th>
						<th>PRODUCTO</th>
					</tr>
				</thead>
				<tbody>
			");
			foreach($lCom as $rOp){
				
				$id_plato=$rOp["ID_PLATO"];
				$nombre=$rOp["PLATO"];
				$categoria=$rOp["CATEGORIA"];
				echo $gf->utf8("<tr>
									<td>$categoria</td>
									<td>$nombre</td>
								</tr>");
			}
		echo $gf->utf8("
				</tbody>
				</table>
			</div>
		</div>");
		

	}elseif($actividad=="add_imp_plat"){
		$id_imp= $gf->cleanVar($_GET["id_imp"]);
		$id_plat=$gf->cleanVar($_GET["id_plat"]);
		$val=$_POST["checkplaim_$id_plat"];
		if($val==1){
			$sqla="INSERT IGNORE INTO platos_impuestos (ID_IMPUESTO,ID_PLATO) VALUES ('$id_imp','$id_plat')";
		}else{
			$sqla="DELETE FROM platos_impuestos WHERE ID_IMPUESTO='$id_imp' AND ID_PLATO='$id_plat'";
		}
		$oka=$gf->dataIn($sqla);
		if($oka){
			echo 1;
		}else{
			echo 0;
		}

	
	}elseif($actividad=="editar"){
		$Vkey=$gf->cleanVar($_GET["Vkey"]);
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		$fn="getAux(\'$sender?flag=start&ent=$tabla\')";
		$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey,$filterVal,$dialogo,$fn);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="nuevo"){
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		$fn="getAux(\'$sender?flag=start&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')";
		$gettabla = $dataTables->devuelveTablaNewItemDyRel($tabla,$filterKey,$filterVal,$dialogo,"getAux(\'$sender?flag=start&ent=$tabla\')");
		echo $gf->utf8($gettabla);
	
	}else{
		echo "Ninguna solicitud";
	}
	
}else{
	echo "No has iniciado sesion!";
}
?>