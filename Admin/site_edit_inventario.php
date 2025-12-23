<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="J")){
	
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
	$actividad=$gf->cleanVar($_GET["flag"]);
	$rigu=array(1,1,1,1,1);

	
	if($actividad=="start"){
		$fkf=array();
		
		$sons=array("tabla"=>"inventario","fk"=>"ID_COMPRA","cond"=>"ID_COMPRA<>'0'");
		$gettabla = $dataTables->armaItems("inventario_compras","FECHA DESC",1,1,0,"ID_SITIO",$_SESSION["restbus"],$sender,"$sender?flag=level3","level3",0,$fkf,$sons);
		echo $gf->utf8("
		<div class='row'>
			<div class='col-md-4' id='level2'>".$gettabla."</div>
			<div class='col-md-8' id='level3'></div>
		</div>");
		
	}elseif($actividad=="bajas_manuales"){
		$fkf["inventario_bajas_motivos"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$fkf["ingredientes"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$sons=array();
		$gettabla = $dataTables->armaTablaDyRel("inventario_bajas","1",$rigu[1],$rigu[1],$rigu[2],"","",$sender,$fkf);
		echo $gf->utf8($gettabla);

	}elseif($actividad=="bajas_start"){
		echo $gf->utf8("
		<div class='box box-default'>
			<div class='box-header'>REPORTE DE BAJAS POR RANGO DE FECHAS (CONSUMOS)</div>
			<div class='box-body'>
			Datos Desde: 
			
			<div class='form-group'>
				<div class='input-group date' id='lfechafromencuestaa'>
					<input type='text' class='form-control unival_regus' name='fechafromencuesta' class='fechafromencuesta unival_regus' id='fechafromencuesta' />
					<span class='input-group-addon'>
						<span class='glyphicon glyphicon-calendar'>
						</span>
					</span>
				</div>
				<script type='text/javascript'>
					$(function () {
						$('#lfechafromencuestaa').datetimepicker({
							viewMode: 'months',
							format: 'YYYY-MM-DD',
							locale: 'es'
						});
					});
				</script>
			</div>
			
			
			
			Datos Hasta: <div class='form-group'>
				<div class='input-group date' id='fechatoencuestaa'>
					<input type='text' class='form-control unival_regus' name='fechatoencuesta' class='fechatoencuesta unival_regus' id='fechatoencuesta' />
					<span class='input-group-addon'>
						<span class='glyphicon glyphicon-calendar'>
						</span>
					</span>
				</div>
				<script type='text/javascript'>
					$(function () {
						$('#fechatoencuestaa').datetimepicker({
							viewMode: 'months',
							format: 'YYYY-MM-DD',
							locale: 'es'
						});
					});
				</script>
			</div>
		
		
		 <input type='button' class='btn btn-primary' value='Generar'  onclick=\"cargaHTMLvars('showresult','$sender?flag=go_consumos','','20000','unival_regus')\" />");
		echo $gf->utf8("<hr /><div id='showresult'></div></div></div>");
	}elseif($actividad=="go_consumos"){

		$desde=$_POST["fechafromencuesta"];
		$hasta=$_POST["fechatoencuesta"];
		if($desde=="" || $hasta==""){
			echo "Selecciona un rango de fechas";
			exit;
		}

		$rsInventarios=$gf->dataSet("SELECT C.ID_REL, I.ID_INGREDIENTE, I.NOMBRE, I.DESCRIPCION, I.UNIDAD_MEDIDA, I.UNIDADES_PRESENTACION, C.ID_COMPRA, SUM(C.CANTIDAD) AS PRES_INGRESO, IFNULL(BAJA.PRES_SALIDA,0) AS PRES_SALIDA, I.MINIMA, IFNULL(IB.CANTBAJA,0) AS BAJAS_MANUALES FROM ingredientes I LEFT JOIN inventario C ON C.ID_INGREDIENTE=I.ID_INGREDIENTE LEFT JOIN (SELECT I.ID_INGREDIENTE, SUM(RI.CANTIDAD*SP.CANTIDAD) AS PRES_SALIDA FROM ingredientes I JOIN racion_ingredientes RI ON I.ID_INGREDIENTE=RI.ID_INGREDIENTE JOIN racion_opciones RO ON RO.ID_OPCION=RI.ID_RACION JOIN platos_composicion PC ON PC.ID_RACION=RO.ID_RACION JOIN sillas_platos SP ON SP.ID_PLATO=PC.ID_PLATO JOIN sillas_platos_composicion SPC ON SPC.ID_ITEM=SP.ID_ITEM AND RO.ID_OPCION=SPC.ID_OPCION AND SPC.ID_RACION=PC.ID_RACION AND SPC.ESTADO=1 JOIN sillas S ON S.ID_SILLA=SP.ID_SILLA JOIN pedidos P ON P.ID_PEDIDO=S.ID_PEDIDO JOIN servicio SE ON SE.ID_SERVICIO=P.ID_SERVICIO WHERE SE.ID_SITIO=:sitio AND SE.FECHA BETWEEN '$desde' AND '$hasta' GROUP BY I.ID_INGREDIENTE ORDER BY I.ID_INGREDIENTE)BAJA ON BAJA.ID_INGREDIENTE=I.ID_INGREDIENTE LEFT JOIN (SELECT ID_INGREDIENTE, SUM(CANTIDAD) AS CANTBAJA FROM inventario_bajas WHERE FECHA BETWEEN '$desde' AND '$hasta' GROUP BY ID_INGREDIENTE ORDER BY ID_INGREDIENTE)IB ON I.ID_INGREDIENTE=IB.ID_INGREDIENTE  WHERE I.ID_SITIO=:sitio GROUP BY I.ID_INGREDIENTE ORDER BY I.NOMBRE",array(":sitio"=>$_SESSION["restbus"]));
		
		echo $gf->utf8("
		<div class='box box-danger'>
			<div class='box-header'>CONSUMOS DESDE $desde HASTA $hasta</div>
			<div class='box-body'>
			<table class='table'>
				<thead>
					<tr>
						<th>PRODUCTO</th>
						<th>DESCRIPCION</th>
						<th>PRESENTACI&Oacute;N</th>
						<th>UNIDADES-CONSUMO</th>
						<th>UNIDADES-BAJA-MANUAL</th>
						<th>TOTAL PRESENTACIONES SALIDA</th>
					</tr>
				</thead>
				<tbody>
			");
			foreach($rsInventarios as $rOp){
				$id_rel=$rOp["ID_REL"];
				$id_ing=$rOp["ID_INGREDIENTE"];
				$nombre=$rOp["NOMBRE"];
				$descripcion=$rOp["DESCRIPCION"];
				$unidad_medida=$rOp["UNIDAD_MEDIDA"];
				$uma=$relaciones["ingredientes"]["campos"]["UNIDAD_MEDIDA"]["arraycont"][$unidad_medida];
				$up=$rOp["UNIDADES_PRESENTACION"];
				$ingreso_presentaciones=$rOp["PRES_INGRESO"];
				$salida_presentaciones=$rOp["PRES_SALIDA"];
				$bajas_manuales=$rOp["BAJAS_MANUALES"];
				$minima=$rOp["MINIMA"];
				
				// Validar valores y evitar división por cero
				$salida_presentaciones = $salida_presentaciones ?: 0;
				$bajas_manuales = $bajas_manuales ?: 0;
				$up = $up > 0 ? $up : 1;
				
				$existencia=$ingreso_presentaciones-$salida_presentaciones;
				$totalpres=($salida_presentaciones+$bajas_manuales)/$up;
				if($existencia<=$minima){
					$estado="Comprar!";
					$st_bg="bg-danger";
					$gf->dataIn("UPDATE ingredientes SET ALERTA=1 WHERE ID_INGREDIENTE='$id_ing'");
				}else{
					$estado="Ok";
					$st_bg="bg-success";
				}
				$btn_del=$gc->button("btn-danger","goErase('inventario','ID_REL','$id_rel','latrei_$id_rel',1)","fa fa-remove","Borrar",false);
			
				echo $gf->utf8("<tr id='latrei_$id_rel'>
									<td>$nombre</td>
									<td>$descripcion</td>
									<td>$up $uma</td>	
									<td>".number_format($salida_presentaciones,0)." $uma</td>
									<td>".number_format($bajas_manuales,0)." $uma</td>
									<td>".number_format($totalpres,2)."</td>
								</tr>");
			}
		echo $gf->utf8("
			</ul>
			</div>
			</div>");



	}elseif($actividad=="estado_inventario"){
		$rsInventarios=$gf->dataSet("SELECT C.ID_REL, I.ID_INGREDIENTE, I.NOMBRE, I.DESCRIPCION, I.UNIDAD_MEDIDA, I.UNIDADES_PRESENTACION, C.ID_COMPRA, SUM(C.CANTIDAD) AS PRES_INGRESO, IFNULL(IB.CANTBAJA,0) AS BAJAMANUAL, IFNULL(BAJA.PRES_SALIDA,0) AS PRES_SALIDA, I.MINIMA FROM ingredientes I LEFT JOIN inventario C ON C.ID_INGREDIENTE=I.ID_INGREDIENTE LEFT JOIN (SELECT I.ID_INGREDIENTE, SUM(RI.CANTIDAD*SP.CANTIDAD) AS PRES_SALIDA FROM ingredientes I JOIN racion_ingredientes RI ON I.ID_INGREDIENTE=RI.ID_INGREDIENTE JOIN racion_opciones RO ON RO.ID_OPCION=RI.ID_RACION JOIN platos_composicion PC ON PC.ID_RACION=RO.ID_RACION JOIN sillas_platos SP ON SP.ID_PLATO=PC.ID_PLATO JOIN sillas_platos_composicion SPC ON SPC.ID_ITEM=SP.ID_ITEM AND RO.ID_OPCION=SPC.ID_OPCION AND SPC.ID_RACION=PC.ID_RACION AND SPC.ESTADO=1 JOIN sillas S ON S.ID_SILLA=SP.ID_SILLA JOIN pedidos P ON P.ID_PEDIDO=S.ID_PEDIDO JOIN servicio SE ON SE.ID_SERVICIO=P.ID_SERVICIO WHERE SE.ID_SITIO=:sitio AND P.CIERRE<>'0000-00-00 00:00:00' GROUP BY I.ID_INGREDIENTE ORDER BY I.ID_INGREDIENTE)BAJA ON BAJA.ID_INGREDIENTE=I.ID_INGREDIENTE LEFT JOIN (SELECT ID_INGREDIENTE, SUM(CANTIDAD) AS CANTBAJA FROM inventario_bajas WHERE 1 GROUP BY ID_INGREDIENTE ORDER BY ID_INGREDIENTE)IB ON I.ID_INGREDIENTE=IB.ID_INGREDIENTE WHERE I.ID_SITIO=:sitio GROUP BY I.ID_INGREDIENTE ORDER BY I.NOMBRE",array(":sitio"=>$_SESSION["restbus"]));
		
		echo $gf->utf8("
		<div class='box box-danger'>
			<div class='box-header'>ESTADO DEL INVENTARIO <button class='btn btn-minier btn-xs btn-success pull-right' onclick=\"reloaHash()\"><i class='fa fa-refresh'></i> Actualizar</button></div>
			<div class='box-body'>
			<table class='table table-bordered datatables' id='dte_invent_state_table'>
				<thead>
					<tr>
						<th>PRODUCTO</th>
						<th>DESCRIPCION</th>
						<th>PRESENTACI&Oacute;N</th>
						<th>INGRESO-PRESENTACIONES</th>
						<th>SALIDA-UNIDADES</th>
						<th>BAJAS-UNIDADES</th>
						<th>SALIDA-PRESENTACIONES</th>
						<th>EXISTENCIA</th>
						<th>ESTADO</th>
					</tr>
				</thead>
				<tbody>
			");
			foreach($rsInventarios as $rOp){
				$id_rel=$rOp["ID_REL"];
				$id_ing=$rOp["ID_INGREDIENTE"];
				$nombre=$rOp["NOMBRE"];
				$descripcion=$rOp["DESCRIPCION"];
				$unidad_medida=$rOp["UNIDAD_MEDIDA"];
				$uma=$relaciones["ingredientes"]["campos"]["UNIDAD_MEDIDA"]["arraycont"][$unidad_medida];
				$up=$rOp["UNIDADES_PRESENTACION"];
				$ingreso_presentaciones=$rOp["PRES_INGRESO"];
				$salida_unidades=$rOp["PRES_SALIDA"];
				$bajas_manuales=$rOp["BAJAMANUAL"];
				
				// Validar valores NULL y evitar división por cero
				$ingreso_presentaciones = $ingreso_presentaciones ?: 0;
				$salida_unidades = $salida_unidades ?: 0;
				$bajas_manuales = $bajas_manuales ?: 0;
				$up = $up > 0 ? $up : 1; // Evitar división por cero
				
				$salida_presentaciones = ($salida_unidades + $bajas_manuales) / $up;
				$minima = $rOp["MINIMA"] ?: 0;
				$existencia = $ingreso_presentaciones - $salida_presentaciones;
				
				if($existencia <= $minima){
					$estado = "Comprar!";
					$st_bg = "bg-danger";
					$gf->dataIn("UPDATE ingredientes SET ALERTA=1 WHERE ID_INGREDIENTE='$id_ing'");
				}else{
					$estado = "Ok";
					$st_bg = "bg-success";
				}
			
				echo $gf->utf8("<tr id='latrei_$id_rel'>
									<td>$nombre</td>
									<td>$descripcion</td>
									<td>$up $uma</td>
									<td>".number_format($ingreso_presentaciones,1)."</td>
									<td>".number_format($salida_unidades,1)." $uma</td>
									<td>".number_format($bajas_manuales,1)." $uma</td>
									<td>".number_format($salida_presentaciones,2)."</td>
									<td><strong>".number_format($existencia,2)."</strong></td>
									<td class='$st_bg'><strong>$estado</strong></td>
								</tr>");
			}
		echo $gf->utf8("
			</tbody>
			</table>
			</div>
		</div>");
		
	}elseif($actividad=="level3"){
		$filterVal=$gf->cleanVar($_GET["key"]);
		$lCom=$gf->dataSet("SELECT FECHA, FACTURA FROM inventario_compras WHERE ID_COMPRA=:compra",array(":compra"=>$filterVal));
		$nmFac=$lCom[0]["FECHA"]." - ".$lCom[0]["FACTURA"];
		$rsInventarios=$gf->dataSet("SELECT C.ID_REL, I.NOMBRE, I.DESCRIPCION, I.UNIDAD_MEDIDA, I.UNIDADES_PRESENTACION, C.ID_COMPRA, C.CANTIDAD, C.PRECIO, I.ID_INGREDIENTE FROM ingredientes I JOIN inventario C ON C.ID_INGREDIENTE=I.ID_INGREDIENTE WHERE C.ID_COMPRA=:compra GROUP BY I.ID_INGREDIENTE ORDER BY I.NOMBRE",array(":compra"=>$filterVal));
		echo $gf->utf8("
		<div class='box box-danger'>
			<div class='box-header'>Productos de la compra $nmFac ".$gc->button("btn-success","getDialog('$sender?flag=add_productos_compra&id_compra=$filterVal','500','Agregar\ Productos')","fa fa-plus","Agregar Productos",false)."</div>
			<div class='box-body'>
			<table class='table'>
				<thead>
					<tr>
						<th>PRODUCTO</th>
						<th>DESCRIPCION</th>
						<th>PRESENTACI&Oacute;N</th>
						<th>CANTIDAD</th>
						<th>VR PRESENTACI&Oacute;N</th>
						<th>SUBTOTAL</th>
						<th>OPTS</th>
					</tr>
				</thead>
				<tbody>
			");
			foreach($rsInventarios as $rOp){
				$id_rel=$rOp["ID_REL"];
				$nombre=$rOp["NOMBRE"];
				$descripcion=$rOp["DESCRIPCION"];
				$unidad_medida=$rOp["UNIDAD_MEDIDA"];
				$uma=$relaciones["ingredientes"]["campos"]["UNIDAD_MEDIDA"]["arraycont"][$unidad_medida];
				$up=$rOp["UNIDADES_PRESENTACION"];
				$cantidad=$rOp["CANTIDAD"];
				$vr_unitario=$rOp["PRECIO"];
				$id_ingrediente=$rOp["ID_INGREDIENTE"];
				$subtotal=$cantidad*$vr_unitario;
				
				$btn_del=$gc->button("btn-danger","goErase('inventario','ID_REL','$id_rel','latrei_$id_rel',1)","fa fa-remove","Borrar",false);
				$btn_edit=$gc->button("btn-warning","getDialog('$sender?flag=edit_inv&Vkey=$id_rel&ingrediente=$id_ingrediente&compra=$filterVal')","fa fa-edit","Editar",false);
				
				echo $gf->utf8("<tr id='latrei_$id_rel'>
									<td>$nombre</td>
									<td>$descripcion</td>
									<td>$up $uma</td>
									<td>$cantidad</td>
									<td>".number_format($vr_unitario,0)."</td>
									<td>".number_format($subtotal,0)." </td>
									<td>$btn_del $btn_edit</td>
								</tr>");
			}
		echo $gf->utf8("
			</ul>
			</div>
		</div>");
		

	}elseif($actividad=="add_productos_compra_go"){
		$id_compra=$gf->cleanVar($_GET["id_compra"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$sqla="INSERT IGNORE INTO inventario (ID_COMPRA,ID_INGREDIENTE,CANTIDAD,PRECIO) VALUES ";
		$valid=0;
		foreach($_POST as $key=>$val){
			
			if(substr($key,0,5)=="prro_"){
				if($val>0){
					$id_inga=str_replace("prro_","",$key);
					$id_inga=str_replace("prro_","",$key);
					$prize=$_POST["prre_$id_inga"];
					if($id_inga!=""){
						$sqla.="('$id_compra','$id_inga','$val','$prize'),";
						$valid++;
					}
				}
			}
		}
		if($valid>0){
			$sqla=substr($sqla,0,-1);
			$oka=$gf->dataIn($sqla);
			if($oka){
				echo $gf->utf8("
				<script>
				$(function(){
					cargaHTMLvars('level3','$sender?flag=level3&key=$id_compra');
					closeD('$rnd');
				})
				</script>
				");
			}else{
				echo "Error al insertar";
			}
		}else{
			echo "No se ingresaron cantidades";
		}
	}elseif($actividad=="add_productos_compra"){
		$id_compra=$gf->cleanVar($_GET["id_compra"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$rsIng=$gf->dataSet("SELECT ID_INGREDIENTE, NOMBRE, UNIDAD_MEDIDA, UNIDADES_PRESENTACION, COSTO_PRESENTACION FROM ingredientes WHERE ID_SITIO=:sitio AND ID_INGREDIENTE NOT IN(SELECT ID_INGREDIENTE FROM inventario WHERE ID_COMPRA=:compra)",array(":sitio"=>$_SESSION["restbus"],":compra"=>$id_compra));
		
		echo $gf->utf8("
		<div class='row'>
			<div class='col-md-12'>
				<div class='panel panel-warning'>
					<div class='panel-heading'>
						LISTADO DE INGREDIENTES <input style='width:150px;height:22px;' type='text' class='form-control input-sm pull-right' placeholder='Buscar...' id='sel_ingredlist' onkeyup=\"filtrar('sel_ingredlist','lista_ingredientes_k')\" />
					</div>
					<div class='panel-body'>
						<table class='table'>
							<thead>
								<tr>
									<th>PRODUCTO</th>
									<th>CANTIDAD</th>
									<th>PRECIO</th>
								</tr>
							</thead>
							<tbody>
						");
						foreach($rsIng as $rwIng){
							$id_ing=$rwIng["ID_INGREDIENTE"];
							$nm_ing=$rwIng["NOMBRE"];
							$un_ing=$rwIng["UNIDAD_MEDIDA"];
							$un_pre=$rwIng["UNIDADES_PRESENTACION"];
							$precio=$rwIng["COSTO_PRESENTACION"];
							$uma=$relaciones["ingredientes"]["campos"]["UNIDAD_MEDIDA"]["arraycont"][$un_ing];
							echo $gf->utf8("<tr class='lista_ingredientes_k'><td> $nm_ing<br /><small class='bolder'>($un_pre $uma)</small></td><td><input type='number' name='prro_$id_ing' step='any' class='form-control pull-right unival_addprocom' style='width:100px;' /></td><td><input type='number' name='prre_$id_ing' step='any' class='form-control pull-right unival_addprocom' style='width:120px;' value='$precio' /></td></tr>");
						}
		echo $gf->utf8("
						</tbody>
						</table>
					</div>
				</div>
			</div>
			
		</div>
		<hr />
		<button class='btn btn-sm btn-success pull-right' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=add_productos_compra_go&id_compra=$id_compra&rnd=$rnd','','10000','unival_addprocom')\"><i class='fa fa-check'></i> Agregar</button>
			");
		
	
	}elseif($actividad=="editar"){
		$Vkey=$gf->cleanVar($_GET["Vkey"]);
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		$fkf["servicio"]=array("ID_SITIO"=>$_SESSION["restbus"],"ESTADO"=>0);
		$fn="getAux(\'$sender?flag=start&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')";
		$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey,$filterVal,$dialogo,$fn,$fkf);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="edit_inv"){
		$Vkey=$gf->cleanVar($_GET["Vkey"]);
		$id_compra= $gf->cleanVar($_GET["compra"]);
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		$filterVal=$gf->cleanVar($_GET["ingrediente"]);
		$fkf["ingredientes"]=array("ID_INGREDIENTE"=>$filterVal);
		$fn="cargaHTMLvars(\'level3\',\'$sender?flag=level3&key=$id_compra\')";
		$gettabla = $dataTables->devuelveTablaEditItemDyRel("inventario",$Vkey,"ID_COMPRA",$id_compra,$dialogo,$fn,$fkf);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="nuevo"){
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		$fn="reloaHash()";
		$fkf["servicio"]=array("ID_SITIO"=>$_SESSION["restbus"],"ESTADO"=>0);
		$fkf["inventario_bajas_motivos"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$fkf["ingredientes"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$fkf["proveedores"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$fkf["formas_pago"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$gettabla = $dataTables->devuelveTablaNewItemDyRel($tabla,$filterKey,$filterVal,$dialogo,$fn,$fkf);
		echo $gf->utf8($gettabla);
	
	}else{
		echo "Ninguna solicitud";
	}
	
}else{
	echo "No has iniciado sesion!";
}
?>