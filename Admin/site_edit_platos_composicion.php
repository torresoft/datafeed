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
		$gettabla = $dataTables->armaFilter("platos_categorias","POSITION",$rigu[1],$rigu[1],$rigu[2],"ID_SITIO",$_SESSION["restbus"],$sender,"$sender?flag=level1","level1",0,$fkf,$sons);
		$fila1=$gc->row(array(array("id"=>"level1","extend"=>12,"content"=>"")));
		$panel=$gc->panel("default","COMPOSICI&Oacute;N DE PLATOS","",array(),$gettabla."<hr />".$fila1);
		echo $gf->utf8($panel);
	}elseif($actividad=="level1"){
		$fkf=array();
		$sons=array("tabla"=>"platos","fk"=>"ID_CATEGORIA","cond"=>"ID_CATEGORIA<>'0'");
		$filterVal=$gf->cleanVar($_GET["key"]);
		$gettabla = $dataTables->armaItems("platos","1",0,0,0,"ID_CATEGORIA",$filterVal,$sender,"$sender?flag=level3","level3",0,$fkf,$sons,"1");
		echo $gf->utf8("
		<div class='row'>
			<div class='col-md-3' id='level2'>".$gettabla."</div>
			<div class='col-md-9' id='level3'></div>
		</div>");
	}elseif($actividad=="sin_config"){
		$fkf=array();
		$sons=array("tabla"=>"platos","fk"=>"ID_CATEGORIA","cond"=>"COCINA='1'");
		$gettabla = $dataTables->armaItems("platos","1",0,0,0,"ID_SITIO",$_SESSION["restbus"],$sender,"$sender?flag=level3","level3",0,$fkf,$sons,"ID_PLATO NOT IN(SELECT PC.ID_PLATO FROM platos_composicion PC JOIN racion_opciones RO ON PC.ID_RACION=RO.ID_RACION JOIN racion_ingredientes RI ON RI.ID_RACION=RO.ID_OPCION GROUP BY PC.ID_PLATO ORDER BY PC.ID_PLATO) AND COCINA='1'");
		echo $gf->utf8("
		<div class='row'>
			<div class='col-md-3' id='level2'>".$gettabla."</div>
			<div class='col-md-9' id='level3'></div>
		</div>");

	}elseif($actividad=="level3"){

		$filterVal=$gf->cleanVar($_GET["key"]);
		$rsPlato=$gf->dataSet("SELECT NOMBRE, DESCRIPCION FROM platos WHERE ID_PLATO=:plato",array(":plato"=>$filterVal));
		if(count($rsPlato)>0){
			$nmPlato=$rsPlato[0]["NOMBRE"];
			$dePlato=$rsPlato[0]["DESCRIPCION"];
			
			$rsComponentes=$gf->dataSet("SELECT ID_RACION, NOMBRE, DESCRIPCION, ID_RACION IN(SELECT DISTINCT ID_RACION FROM sillas_platos_composicion ORDER BY ID_RACION) AS VENDIDO FROM platos_composicion WHERE ID_PLATO=:plato ORDER BY POSITION",array(":plato"=>$filterVal));
			
			$btn_del=array(
				"class"=>"btn-danger",
				"action"=>"goErase('platos_composicion','ID_RACION','[KEY]','li_ID_RACION[KEY]',1)",
				"icon"=>"fa fa-remove",
				"caption"=>"Borrar",
				"condition"=>"VENDIDO",
				"show_caption"=>false);
			$btn_edit=array(
				"class"=>"btn-warning",
				"action"=>"getDialog('$sender?flag=edit_component&id_plato=$filterVal&id_racion=[KEY]','500','Editar\ Componente')",
				"icon"=>"fa fa-edit",
				"caption"=>"Editar",
				"show_caption"=>false);
			
			$ul=$gc->itemlist($rsComponentes,"ID_RACION","NOMBRE","cargaHTMLvars('level5','$sender?flag=racion_opciones&id_plato=$filterVal&id_racion=[KEY]')",array($btn_del,$btn_edit),false,true,"platos_composicion","ID_RACION","POSITION","VENDIDO");
			$btn_add=array(
				"class"=>"btn-primary",
				"action"=>"getDialog('$sender?flag=add_component&id_plato=$filterVal','500','Agregar\ Componente')",
				"icon"=>"fa fa-plus",
				"caption"=>"Agregar Componente",
				"show_caption"=>false);
			$btn_unic=array(
				"class"=>"btn-warning",
				"action"=>"getDialog('$sender?flag=add_component_unic&id_plato=$filterVal','500','Agregar\ Componente')",
				"icon"=>"fa fa-coffee",
				"caption"=>"Composici&oacute;n &Uacute;nica",
				"show_caption"=>false);
			$btn_copy=array(
				"class"=>"btn-success",
				"action"=>"getDialog('$sender?flag=copy_composite&id_pl=$filterVal','500','Copiar\ Componentes')",
				"icon"=>"fa fa-copy",
				"caption"=>"Copiar composicion",
				"show_caption"=>false);

			$panel=$gc->panel("danger","Composici&oacute;n de $nmPlato","",array($btn_add,$btn_unic,$btn_copy),$ul);
			$fila=$gc->row(array(array("id"=>"level4","extend"=>5,"content"=>$panel),array("id"=>"level5","extend"=>7,"content"=>"")));
			echo $gf->utf8($fila);
		}else{
			echo "No se encuentra el plato";
		}
		
		
	}elseif($actividad=="add_component_unic"){
		$id_plato=$gf->cleanVar($_GET["id_plato"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$ok=$gf->dataIn("INSERT IGNORE INTO platos_composicion (ID_PLATO,NOMBRE,DESCRIPCION) VALUES (:id_plato,:nombre,:descripcion)",array(":id_plato"=>$id_plato,":nombre"=>"Composici&oacute;n &uacute;nica",":descripcion"=>"El plato no se divide en componentes"));
		if($ok){
			echo "Ok!";
			$fn="cargaHTMLvars('level3','$sender?flag=level3&ent=platos_composicion&key=$id_plato')";
			echo $gf->utf8("<input type='hidden' id='callbackevaldlg' value=\"$fn;closeD('$rnd')\" />");
		}
	}elseif($actividad=="racion_opciones"){
		$id_racion=$gf->cleanVar($_GET["id_racion"]);

		$rsPlato=$gf->dataSet("SELECT ID_PLATO, NOMBRE, DESCRIPCION FROM platos_composicion WHERE ID_RACION=:racion",array(":racion"=>$id_racion));
		if(count($rsPlato)>0){
			$nmPorcion=$rsPlato[0]["NOMBRE"];
			$dePorcion=$rsPlato[0]["DESCRIPCION"];
			$id_plato=$rsPlato[0]["ID_PLATO"];
		
			
			$nOp=$gf->dataSet("SELECT ID_OPCION, NOMBRE FROM racion_opciones WHERE ID_RACION=:racion",array(":racion"=>$id_racion));
			if(count($nOp)==0){
				$gf->dataIn("INSERT INTO racion_opciones (ID_RACION,NOMBRE,DESCRIPCION) VALUES ('$id_racion','Única Opción','Por defecto')");
			}

			$nOp=$gf->dataSet("SELECT O.ID_OPCION, O.NOMBRE, COUNT(R.ID_REL) AS INGRE FROM racion_opciones O LEFT JOIN racion_ingredientes R ON O.ID_OPCION=R.ID_RACION WHERE O.ID_RACION=:racion GROUP BY O.ID_OPCION ORDER BY O.NOMBRE",array(":racion"=>$id_racion));
			echo $gf->utf8("
			<div class='box box-danger'>
				<div class='box-header'>Opciones de porci&oacute;n $nmPorcion ".$gc->button("btn-success","getDialog('$sender?flag=add_opcion&id_racion=$id_racion','500','Agregar\ Opci&oacute;n')","fa fa-plus","Agregar Opci&oacute;n de Porci&oacute;n",false)."</div>
				<div class='box-body'>
				<ul class='list-group'>
				");
				foreach($nOp as $rOp){
					$id_opcion=$rOp["ID_OPCION"];
					$nm_opcion=$rOp["NOMBRE"];
					$ingre=$rOp["INGRE"];
					
					if($ingre==0){
						$btn_ingre="<button onclick=\"getDialog('$sender?flag=opcion_ingredientes&id_racion=$id_racion&id_opcion=$id_opcion','2000','Ingredientes','','','cargaHTMLvars(\'level5\',\'$sender?flag=racion_opciones&id_plato=$id_plato&id_racion=$id_racion\')')\" class='btn btn-danger btn-xs pull-right' title='Sin ingredientes'><i class='fa  fa-exclamation-triangle'></i> $ingre</button>";
					}else{
						$btn_ingre="<button onclick=\"getDialog('$sender?flag=opcion_ingredientes&id_racion=$id_racion&id_opcion=$id_opcion','2000','Ingredientes','','','cargaHTMLvars(\'level5\',\'$sender?flag=racion_opciones&id_plato=$id_plato&id_racion=$id_racion\')')\" class='btn btn-success btn-xs pull-right' title='Con ingredientes'><i class='fa fa-flask'></i> $ingre</button>";
					}
					$btn_del=$gc->button("btn-danger","goErase('racion_opciones','ID_OPCION','$id_opcion','li_ID_OPCION$id_opcion',1)","fa fa-remove","Borrar",false);
					$btn_edit=$gc->button("btn-warning","getDialog('$sender?flag=edit_opcion&id_racion=$id_racion&id_opcion=$id_opcion','500','Editar\ Opcion')","fa fa-edit","Editar",false);
					echo $gf->utf8("<li class='list-group-item'>$nm_opcion $btn_del $btn_edit $btn_ingre</li>");
				}
			echo $gf->utf8("
				</ul>
				</div>
			</div>");
			
		}else{
			echo $gf->utf8("No se encuentra la porci&oacute;n");
		}
	}elseif($actividad=="opcion_ingredientes"){
		$id_opcion=$gf->cleanVar($_GET["id_opcion"]);
		$id_racion=$gf->cleanVar($_GET["id_racion"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$rsIng=$gf->dataSet("SELECT ID_INGREDIENTE, NOMBRE, UNIDAD_MEDIDA FROM ingredientes WHERE ID_SITIO=:sitio AND ID_INGREDIENTE NOT IN(SELECT ID_INGREDIENTE FROM racion_ingredientes WHERE ID_RACION=:opcion)",array(":sitio"=>$_SESSION["restbus"],":opcion"=>$id_opcion));
		
		echo $gf->utf8("
		<div class='row'>
			<div class='col-md-6'>
				<div class='panel panel-warning'>
					<div class='panel-heading'>
						LISTADO DE INGREDIENTES <input style='width:150px;height:22px;' type='text' class='form-control input-sm pull-right' placeholder='Buscar...' id='sel_ingredlist' onkeyup=\"filtrarValores('sel_ingredlist','lista_ingredientes_k')\" />
					</div>
					<div class='panel-body'>
						<ul class='list-group' style='overflow-y:auto;height:200px;' id='lalistaingredientes'>
						");
						foreach($rsIng as $rwIng){
							$id_ing=$rwIng["ID_INGREDIENTE"];
							$nm_ing=$rwIng["NOMBRE"];
							$un_ing=$rwIng["UNIDAD_MEDIDA"];
							$uma=$relaciones["ingredientes"]["campos"]["UNIDAD_MEDIDA"]["arraycont"][$un_ing];
							echo $gf->utf8("<li class='list-group-item lista_ingredientes_k clearfix' id='listingiexistents_$id_ing'> $nm_ing <small>($uma) </small><button class='btn btn-xs btn-warning pull-right' onclick=\"getDialog('$sender?flag=add_ing_por&id_ing=$id_ing&id_opcion=$id_opcion')\"><i class='fa fa-arrow-right'></i></button></li>");
						}
		echo $gf->utf8("
						</ul>
					</div>
				</div>
			</div>
			<div class='col-md-6'>
				<div class='panel panel-success'>
					<div class='panel-heading'>
						INGREDIENTES SELECCIONADOS
					</div>
					<div class='panel-body' id='selected_ingredients'>
						<input type='hidden' id='callbackevaldlg' value=\"cargaHTMLvars('selected_ingredients','$sender?flag=select_ingred&id_opcion=$id_opcion&id_racion=$id_racion')\" />
					</div>
				</div>
			</div>
		</div>
		<hr />
		<button class='btn btn-sm btn-success pull-right' onclick=\"closeD('$rnd')\"><i class='fa fa-check'></i> Terminar</button>
			");
		
	}elseif($actividad=="copy_composite_go"){
		$id_from=$gf->cleanVar($_GET["id_from"]);
		$id_to=$gf->cleanVar($_GET["id_to"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		
		$id_racion=$gf->cleanVar($_GET["id_racion"]);

		$rsPlato=$gf->dataSet("SELECT ID_RACION, NOMBRE, DESCRIPCION FROM platos_composicion WHERE ID_PLATO=:id_from",array(":id_from"=>$id_from));
		$comps=0;
		if(count($rsPlato)>0){
			foreach($rsPlato as $rwPlato){
				$idRacion=$rwPlato["ID_RACION"];
				$nmRacion=$rwPlato["NOMBRE"];
				$deRacion=$rwPlato["DESCRIPCION"];
				$id_racion_nueva=$gf->dataInLast("INSERT INTO platos_composicion (NOMBRE,DESCRIPCION,ID_PLATO) VALUES ('$nmRacion','$deRacion','$id_to')");
				$comps++;
				if($id_racion_nueva>0){
					$gf->dataIn("INSERT INTO racion_opciones (ID_RACION,NOMBRE,DESCRIPCION) SELECT '$id_racion_nueva' AS ID_RACION, NOMBRE, DESCRIPCION FROM racion_opciones WHERE ID_RACION='$idRacion' ORDER BY ID_OPCION");
				}
			}
			
			echo $gf->utf8("Se copiaron $comps componentes al plato, con sus respectivas opciones");
		}else{
			echo $gf->utf8("No se encuentra la porci&oacute;n");
		}

		
	}elseif($actividad=="copy_composite"){
		$id_plato_destino=$gf->cleanVar($_GET["id_pl"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$resultChair = $gf->dataSet("SELECT P.ID_PLATO, CAT.ID_CATEGORIA, CAT.NOMBRE AS CATEGORIA, CAT.ICONO, P.NOMBRE AS PLATO FROM platos_categorias AS CAT JOIN platos AS P ON(CAT.ID_CATEGORIA=P.ID_CATEGORIA AND CAT.ESTADO='1' AND P.ESTADO='1' AND CAT.ID_SITIO='{$_SESSION["restbus"]}' AND P.ID_PLATO IN(SELECT ID_PLATO FROM platos_composicion ORDER BY ID_PLATO)) WHERE CAT.ID_SITIO='{$_SESSION["restbus"]}' ORDER BY CAT.POSITION, P.NOMBRE");
				
		$inicat="";
		
		if(count($resultChair)>0){
			echo $gf->utf8("<small>Selecciona el plato del cual quieres copiar la composici&oacute;n</small><br /><table class='table table-striped'>");	
			foreach($resultChair as $rowChair){
				$id_categoria=$rowChair["ID_CATEGORIA"];
				$nombre_categoria=$rowChair["CATEGORIA"];
				$id_plato=$rowChair["ID_PLATO"];
				$icono=$rowChair["ICONO"];
				$nombre_plato=$rowChair["PLATO"];
				if($inicat!=$id_categoria){
					echo $gf->utf8("<tr class='bg-warning'><td colspan='2' align='center'><h3><img src='$icono' style='width:30px;align:left;' /> $nombre_categoria</h3></td></tr>");
				}
				if($id_plato!=""){
					echo $gf->utf8("<tr><td><h4>$nombre_plato</h4></td><td width='35'><button class='btn btn-xs btn-warning pull-right' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=copy_composite_go&id_from=$id_plato&id_to=$id_plato_destino&rnd=$rnd')\">Copiar</button></td></tr>");
				}
				$inicat=$id_categoria;
			}
			echo $gf->utf8("</table><br /> <button class='btn btn-danger btn-md pull-right' onclick=\"closeD('$rnd')\">Cancelar</button>

			");
		}
	
		
		
	}elseif($actividad=="select_ingred"){
		$id_opcion=$gf->cleanVar($_GET["id_opcion"]);
		//$id_racion=$gf->cleanVar($_GET["id_racion"]);
		$rsIng2=$gf->dataSet("SELECT I.ID_INGREDIENTE, I.NOMBRE, I.UNIDAD_MEDIDA, C.CANTIDAD, C.ID_REL FROM ingredientes I JOIN racion_ingredientes C ON C.ID_INGREDIENTE=I.ID_INGREDIENTE WHERE I.ID_SITIO=:sitio AND C.ID_RACION=:opcion",array(":sitio"=>$_SESSION["restbus"],":opcion"=>$id_opcion));
		echo $gf->utf8("
		<ul class='list-group' style='overflow-y:auto;height:200px;'>");
		foreach($rsIng2 as $rwIng2){
			$id_rel=$rwIng2["ID_REL"];
			$id_ing=$rwIng2["ID_INGREDIENTE"];
			$nm_ing=$rwIng2["NOMBRE"];
			$un_ing=$rwIng2["UNIDAD_MEDIDA"];
			$uma=$relaciones["ingredientes"]["campos"]["UNIDAD_MEDIDA"]["arraycont"][$un_ing];
			$ca_ing=$rwIng2["CANTIDAD"];
			echo $gf->utf8("<li class='list-group-item lista_ingredientes_k' id='listingiselected_$id_ing'> $nm_ing ($ca_ing $uma) <button class='btn btn-xs btn-danger pull-right' onclick=\"getDialog('$sender?flag=del_ing_por_go&id_rel=$id_rel&id_ing=$id_ing&id_opcion=$id_opcion','200','Borrar\ ingrediente')\"><i class='fa fa-remove'></i></button></li>");
		}
		echo $gf->utf8("
		</ul>");
	}elseif($actividad=="del_ing_por_go"){
		$id_rel=$gf->cleanVar($_GET["id_rel"]);
		$id_ing=$gf->cleanVar($_GET["id_ing"]);
		$id_opcion=$gf->cleanVar($_GET["id_opcion"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$ok=$gf->dataIn("DELETE FROM racion_ingredientes WHERE ID_REL=:rela",array(":rela"=>$id_rel));
		if($ok){
			$rsIng2=$gf->dataSet("SELECT NOMBRE, UNIDAD_MEDIDA FROM ingredientes  WHERE ID_INGREDIENTE=:ingrediente",array(":ingrediente"=>$id_ing));
			$nm_ing=$rsIng2[0]["NOMBRE"];
			$uma=$relaciones["ingredientes"]["campos"]["UNIDAD_MEDIDA"]["arraycont"][$rsIng2[0]["UNIDAD_MEDIDA"]];
			echo $gf->utf8("
			<script>
			$(function(){
				$('#lalistaingredientes').append('<li class=\"list-group-item lista_ingredientes_k clearfix\" id=\"listingiexistents_$id_ing\"> $nm_ing <small>($uma) </small><button class=\"btn btn-xs btn-warning pull-right\" onclick=\"getDialog(\'$sender?flag=add_ing_por&id_ing=$id_ing&id_opcion=$id_opcion\')\"><i class=\"fa fa-arrow-right\"></i></button></li>');
				closeD('$rnd');
				remDm('listingiselected_$id_ing')
			});
			</script>
			");
		}
		
	}elseif($actividad=="add_ing_por_go"){
		$id_opcion=$gf->cleanVar($_GET["id_opcion"]);
		$id_ing=$gf->cleanVar($_GET["id_ing"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$canti=$_POST["add_ingepor_val"];
		if($canti>0){
			$ok=$gf->dataIn("INSERT IGNORE INTO racion_ingredientes (ID_RACION,ID_INGREDIENTE,CANTIDAD) VALUES (:id_opcion,:id_ing,:canti)",array(":id_opcion"=>$id_opcion,":id_ing"=>$id_ing,":canti"=>$canti));
			if($ok){
				echo $gf->utf8("<input type='hidden' id='callbackeval' value=\"cargaHTMLvars('selected_ingredients','$sender?flag=select_ingred&id_opcion=$id_opcion&id_racion=$id_racion');closeD('$rnd');remDm('listingiexistents_$id_ing')\" />");
			}else{
				echo $gf->utf8("Hubo un error al adicionar el ingrediente");
			}
		}else{
			echo $gf->utf8("Por favor digita una cantidad v&aacute;lida");
		}
	}elseif($actividad=="add_ing_por"){
		$id_opcion=$gf->cleanVar($_GET["id_opcion"]);
		$id_ing=$gf->cleanVar($_GET["id_ing"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		echo $gf->utf8("Cantidad: <input type='number' class='form-control unival_adding' required onkeyup=\"testIntr(event,'cargaHTMLvars(\'ModalContent_$rnd\',\'$sender?flag=add_ing_por_go&id_opcion=$id_opcion&id_ing=$id_ing&rnd=$rnd\',\'\',\'15000\',\'unival_adding\')')\" step='any' id='add_ingepor_val' name='add_ingepor_val' /><br />
		<button class='btn btn-warning btn-sm' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=add_ing_por_go&id_opcion=$id_opcion&id_ing=$id_ing&rnd=$rnd','','15000','unival_adding')\">Agregar</button>
		");
	}elseif($actividad=="add_component"){
		$dialogo=$gf->cleanVar($_GET["rnd"]);
		$id_plato=$gf->cleanVar($_GET["id_plato"]);
		$fn="cargaHTMLvars(\'level3\',\'$sender?flag=level3&ent=$tabla&key=$id_plato\')";
		
		$gettabla = $dataTables->devuelveTablaNewItemDyRel("platos_composicion","ID_PLATO",$id_plato,$dialogo,$fn);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="add_opcion"){
		$dialogo=$gf->cleanVar($_GET["rnd"]);
		$id_racion=$gf->cleanVar($_GET["id_racion"]);
		$fn="cargaHTMLvars(\'level5\',\'$sender?flag=racion_opciones&ent=$tabla&id_racion=$id_racion\')";
		
		$gettabla = $dataTables->devuelveTablaNewItemDyRel("racion_opciones","ID_RACION",$id_racion,$dialogo,$fn);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="edit_opcion"){
		$dialogo=$gf->cleanVar($_GET["rnd"]);
		$id_racion=$gf->cleanVar($_GET["id_racion"]);
		$id_opcion=$gf->cleanVar($_GET["id_opcion"]);
		$fn="cargaHTMLvars(\'level5\',\'$sender?flag=racion_opciones&ent=$tabla&id_racion=$id_racion\')";
		$gettabla = $dataTables->devuelveTablaEditItemDyRel("racion_opciones",$id_opcion,"ID_RACION",$id_racion,$dialogo,$fn);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="edit_component"){
		$dialogo=$gf->cleanVar($_GET["rnd"]);
		$id_plato=$gf->cleanVar($_GET["id_plato"]);
		$id_racion=$gf->cleanVar($_GET["id_racion"]);
		$fn="cargaHTMLvars(\'level3\',\'$sender?flag=level3&ent=$tabla&key=$id_plato\')";
		$gettabla = $dataTables->devuelveTablaEditItemDyRel("platos_composicion",$id_racion,"ID_PLATO",$id_plato,$dialogo,$fn);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="editar"){
		$Vkey=$gf->cleanVar($_GET["Vkey"]);
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		if($tabla=="categorias_platos"){
			$fn="getAux(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')";
		}else{
			$fn="cargaHTMLvars(\'level2\',\'$sender?flag=level2&ent=$tabla&key=$filterVal\')";
		}
		$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey,$filterVal,$dialogo,$fn);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="nuevo"){
		$dialogo=$gf->cleanVar($_GET["rnd"]);
		if($tabla=="categorias_platos"){
			$fn="getAux(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')";
		}else{
			$fn="cargaHTMLvars(\'level2\',\'$sender?flag=level2&ent=$tabla&key=$filterVal\')";
		}
		$gettabla = $dataTables->devuelveTablaNewItemDyRel($tabla,$filterKey,$filterVal,$dialogo,"getAux(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')");
		echo $gf->utf8($gettabla);
	
	}else{
		echo "Ninguna solicitud";
	}
	
}else{
	echo "No has iniciado sesion!";
}
?>