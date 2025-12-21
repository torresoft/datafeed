<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && $_SESSION["restprofile"]=="A"){
	
	require_once("../autoload.php");
	$gf=new generalFunctions;
	global $relaciones;
	$dataTables=new dsTables();
	if(isset($_GET["ent"])){
		$tabla=$gf->cleanVar($_GET["ent"]);
	}else{
		$tabla="platos";
	}
	$titulo="MESAS DEL ESTABLECIMIENTO";
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
	if($actividad=="ver"){
		$fkf=array();
		$sons=array("tabla"=>"platos","fk"=>"ID_CATEGORIA","cond"=>"ID_CATEGORIA<>'0'");
		
		$gettabla = $dataTables->armaItems("platos_categorias","POSITION",$rigu[1],$rigu[1],$rigu[2],"ID_SITIO",$_SESSION["restbus"],$sender,"$sender?flag=level2","level2",0,$fkf,$sons);
		echo $gf->utf8("
		<div class='panel panel-default'>
			<div class='panel-heading'>CATEGOR&Iacute;AS Y PLATOS</div>
			<div class='panel-body'>
				<div class='row'>
					<div class='col-md-3' id='level1'>".$gettabla."</div>
					<div class='col-md-9' id='level2'></div>
				</div>
			</div>
		</div>");
		if($_SESSION["restumail"]=="datafeed"){
			echo $gf->utf8("<hr /><button class='btn btn-xs btn-success' onclick=\"getDialog('$sender?flag=import_platos')\">Importar</button>");
		}

	}elseif($actividad=="import_platos"){
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$rsTari=$gf->dataSet("SELECT ID_SITIO, NOMBRE FROM sitios WHERE ID_SITIO<>:sitio ORDER BY NOMBRE",array(":sitio"=>$_SESSION["restbus"]));
		echo $gf->utf8("<div class='panel panel-default'><div class='panel-heading'>IMPORTAR TODOS LOS PLATOS Y CONFIGURACIONES DE: </div><div class='panel-body'>
		
		");

		if(count($rsTari)>0){
			echo $gf->utf8("<select class='unival_go_copy form-control' name='origen' id='origen'>");
			foreach($rsTari as $rowInt){
				$id_sitio=$rowInt["ID_SITIO"];
				$nombre=$rowInt["NOMBRE"];
				echo $gf->utf8("<option value='$id_sitio'>$nombre</option>");
				
			}
		   echo $gf->utf8("</select>");
			

		}
		echo $gf->utf8("<hr />
		<button class='btn btn-success btn-xs' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=import_platos_go&rnd=$rnd','','20000','unival_go_copy')\">Copiar todo</button>
		</div></div>");

	}elseif($actividad=="import_platos_go"){
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$origen=$_POST["origen"];
		$destino=$_SESSION["restbus"];
		
		$id_impuesto=0;

		$rsOptions=$gf->dataSet("SELECT P.ID_PLATO, CAT.ID_CATEGORIA, CAT.NOMBRE AS CATEGORIA, CAT.ICONO, CAT.POSITION AS POS_CAT, CAT.ESTADO AS EST_CAT, P.NOMBRE AS PLATO, P.DESCRIPCION AS DESC_PLATO, P.PRECIO, P.PRECIO_DOM, P.ESTADO, P.COCINA, P.ID_COCINA, P.ID_IMPUESTO, P.TIPO_PLATO, P.PRECIO_EDITABLE, PC.ID_RACION, PC.NOMBRE AS RACION, PC.DESCRIPCION AS DESC_RACION, RO.ID_OPCION, RO.NOMBRE  AS OPCION, RO.DESCRIPCION AS OP_DESCRIPCION, RO.ESTADO AS OP_ESTADO FROM platos_categorias CAT JOIN platos AS P ON CAT.ID_CATEGORIA=P.ID_CATEGORIA LEFT JOIN platos_composicion PC ON PC.ID_PLATO=P.ID_PLATO LEFT JOIN racion_opciones RO ON RO.ID_RACION=PC.ID_RACION WHERE CAT.ID_SITIO=:sitio ORDER BY CAT.ID_CATEGORIA, P.ID_PLATO",array(":sitio"=>$origen));
		$lasopciones=array();
		foreach($rsOptions as $rwOptions){
			$id_cat=$rwOptions["ID_CATEGORIA"];
			$nm_cat=$rwOptions["CATEGORIA"];
			$ic_cat=$rwOptions["ICONO"];
			$pos_cat=$rwOptions["POS_CAT"];
			$est_cat=$rwOptions["EST_CAT"];
			$r_idplato=$rwOptions["ID_PLATO"];
			$nm_plato=$rwOptions["PLATO"];
			$desc_plato=$rwOptions["DESC_PLATO"];
			$estado_plato=$rwOptions["ESTADO"];
			$cocina_plato=$rwOptions["COCINA"];
			$id_cocina_plato=$rwOptions["ID_COCINA"];
			$tipo_plato=$rwOptions["TIPO_PLATO"];
			$editable=$rwOptions["PRECIO_EDITABLE"];
			$precio=$rwOptions["PRECIO"];
			$precio_dom=$rwOptions["PRECIO_DOM"];
			
			$r_idracion=$rwOptions["ID_RACION"];
			$r_nmracion=$rwOptions["RACION"];
			$desc_racion=$rwOptions["DESC_RACION"];

			$r_idopion=$rwOptions["ID_OPCION"];
			$r_nmopcion=$rwOptions["OPCION"];
			$desc_opcion=$rwOptions["OP_DESCRIPCION"];
			$est_opcion=$rwOptions["OP_ESTADO"];
			
			$todo[$id_cat]["nm"]=$nm_cat;
			$todo[$id_cat]["ic"]=$ic_cat;
			$todo[$id_cat]["pos"]=$pos_cat;
			$todo[$id_cat]["est"]=$est_cat;
			$todo[$id_cat]["platos"][$r_idplato]["nm"]=$nm_plato;
			$todo[$id_cat]["platos"][$r_idplato]["desc"]=$desc_plato;
			$todo[$id_cat]["platos"][$r_idplato]["est"]=$estado_plato;
			$todo[$id_cat]["platos"][$r_idplato]["coc"]=$cocina_plato;
			$todo[$id_cat]["platos"][$r_idplato]["id_coc"]=$id_cocina_plato;
			$todo[$id_cat]["platos"][$r_idplato]["tipo"]=$tipo_plato;
			$todo[$id_cat]["platos"][$r_idplato]["edit"]=$editable;
			$todo[$id_cat]["platos"][$r_idplato]["precio"]=$precio;
			$todo[$id_cat]["platos"][$r_idplato]["precio_dom"]=$precio_dom;
			if($r_idracion!=""){
				$todo[$id_cat]["platos"][$r_idplato]["compo"][$r_idracion]["nm"]=$r_nmracion;
				$todo[$id_cat]["platos"][$r_idplato]["compo"][$r_idracion]["desc"]=$desc_racion;
				if($r_idopion!=""){
					$todo[$id_cat]["platos"][$r_idplato]["compo"][$r_idracion]["op"][$r_idopion]["nm"]=$r_nmopcion;
					$todo[$id_cat]["platos"][$r_idplato]["compo"][$r_idracion]["op"][$r_idopion]["desc"]=$desc_opcion;
					$todo[$id_cat]["platos"][$r_idplato]["compo"][$r_idracion]["op"][$r_idopion]["est"]=$est_opcion;
				}
				
			}
			
		}

		foreach($todo as $id_cat=>$infocat){
			$nm_cat=$gf->utf8($infocat["nm"]);
			$icono_cat=$infocat["ic"];
			$pos_cat=$infocat["pos"];
			$est_cat=$infocat["est"];
			$new_cat=$gf->dataInLast("INSERT INTO platos_categorias (NOMBRE,ICONO,POSITION,ESTADO,ID_SITIO) VALUES ('$nm_cat','$icono_cat','$pos_cat','$est_cat','$destino')");
			if($new_cat>0){
				$platos=$infocat["platos"];
				foreach($platos as $id_plato=>$infoplato){
					$nm_plato=$gf->utf8($infoplato["nm"]);
					$desc_plato=$infoplato["desc"];
					$estado_plato=$infoplato["est"];
					$cocina_plato=$infoplato["coc"];
					$id_cocina_plato=$infoplato["id_coc"];
					$tipo_plato=$infoplato["tipo"];
					$editable=$infoplato["edit"];
					$precio=$infoplato["precio"];
					$precio_dom=$infoplato["precio_dom"];
					$new_plato=$gf->dataInLast("INSERT INTO platos (ID_CATEGORIA,NOMBRE,DESCRIPCION,PRECIO,PRECIO_DOM,ESTADO,ID_SITIO,COCINA,ID_COCINA,TIPO_PLATO,ID_IMPUESTO,PRECIO_EDITABLE) VALUES ('$new_cat','$nm_plato','$desc_plato','$precio','$precio_dom','1','$destino','$cocina_plato','$id_cocina_plato','$tipo_plato','0','$editable')");
					if($new_plato>0){
						if(isset($infoplato["compo"])){
							$composicion=$infoplato["compo"];
							foreach($composicion as $id_racion=>$inforacion){
								$nm_racion=$gf->utf8($inforacion["nm"]);
								$desc_racion=$inforacion["desc"];
								$new_racion=$gf->dataInLast("INSERT INTO platos_composicion (ID_PLATO,NOMBRE,DESCRIPCION) VALUES ('$new_plato','$nm_racion','$desc_racion')");
								if(isset($inforacion["op"])){
									$opts=$inforacion["op"];
									if(count($opts)>0){
										foreach($opts as $ido=>$infop){
											if($ido!=""){
												$nmo=$infop["nm"];
												$descop=$infop["desc"];
												$estaop=$infop["est"];
												$gf->dataIn("INSERT INTO racion_opciones (ID_RACION,NOMBRE,DESCRIPCION,ESTADO) VALUES ('$new_racion','$nmo','$descop','$estaop')");
											}
										}
									}
								}
								
							}
						}
					}
				}
				
			}
		}

	}elseif($actividad=="level2"){
		$filterKey="ID_CATEGORIA";
		$filterVal=$gf->cleanVar($_GET["key"]);
		$gettabla = $dataTables->armaTablaDyRel("platos","1",$rigu[1],$rigu[1],$rigu[2],$filterKey,$filterVal,$sender);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="editar"){
		$Vkey=$gf->cleanVar($_GET["Vkey"]);
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		if($tabla=="platos_categorias"){
			$fn="reloaHash()";
		}else{
			$fn="cargaHTMLvars(\'level2\',\'$sender?flag=level2&ent=$tabla&key=$filterVal\')";
		}
		$fkf["cocinas"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$fkf["impuestos"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey,$filterVal,$dialogo,$fn,$fkf);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="nuevo"){
		$dialogo=$gf->cleanVar($_GET["rnd"]);
		if($tabla=="platos_categorias"){
			$fn="reloaHash()";
		}else{
			$fn="cargaHTMLvars(\'level2\',\'$sender?flag=level2&ent=$tabla&key=$filterVal\')";
		}
		$fkf["cocinas"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$fkf["impuestos"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$gettabla = $dataTables->devuelveTablaNewItemDyRel($tabla,$filterKey,$filterVal,$dialogo,$fn,$fkf);
		echo $gf->utf8($gettabla);
	
	}else{
		echo "Ninguna solicitud";
	}
	
}else{
	echo "No has iniciado sesion!";
}
?>