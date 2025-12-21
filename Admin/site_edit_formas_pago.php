<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && $_SESSION["restprofile"]=="A"){

	require_once("../autoload.php");
	$gf=new generalFunctions;
	global $relaciones;
	$dataTables=new dsTables();
	$tabla="formas_pago";
	$titulo="FORMAS DE PAGO";
	$sender=$_SERVER['PHP_SELF'];
	$filterKey="ID_SITIO";
	$filterVal=$_SESSION["restbus"];

	if(isset($_GET["hnd"])){
		$rigu=array(1,1,1,1,1);
	}else{
		$rigu=array(1,1,1,1,1);
	}
	$actividad=$gf->cleanVar($_GET["flag"]);
	if($actividad=="ver"){
		$sons=array("tabla"=>"pedidos","fk"=>"ID_FP","ID_FP<>0");
		$gettabla = $dataTables->armaItems($tabla,"POSICION",$rigu[1],$rigu[1],$rigu[2],"ID_SITIO",$_SESSION["restbus"],$sender,"","",0,array(),$sons);
		echo $gf->utf8("
		<div class='panel panel-default'>
			<div class='panel-heading'>M&Eacute;TODOS DE PAGO</div>
			<div class='panel-body'>
				<div class='row'>
					<div class='col-md-3' id='level1'>".$gettabla."</div>
					<div class='col-md-9' id='level2'>
						ELEGIR EL M&Eacute;TODO DE PAGO DE LA CAJA<hr />

						");

						$rso=$gf->dataSet("SELECT ID_FP, NOMBRE, CAJA FROM formas_pago WHERE CREDITO=0 AND ID_SITIO='{$_SESSION["restbus"]}'");
						if(count($rso)>0){
							foreach($rso as $rwo){
								$id_fp=$rwo["ID_FP"];
								$nm_fp=$rwo["NOMBRE"];
								$bx=$rwo["CAJA"];
								$checka="";
								if($bx==1){
									$checka="checked='checked'";
								}
								echo $gf->utf8("<label for='fpaa_$id_fp'><input type='radio' name='forma_caja' class='univool' id='fc_$id_fp' value='$id_fp' $checka onclick=\"cargaHTMLvars('state_proceso','$sender?flag=setcaja','','5000','univool')\" />$nm_fp</label><br />");
							}
						}


						echo $gf->utf8("
					
					</div>
				</div>
			</div>
		</div>");




	}elseif($actividad=="setcaja"){
		$id_fp=$_POST["forma_caja"];
		$gf->dataIn("UPDATE formas_pago SET CAJA='0' WHERE ID_SITIO='{$_SESSION["restbus"]}'");
		$gf->dataIn("UPDATE formas_pago SET CAJA='1' WHERE ID_FP='$id_fp'");
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