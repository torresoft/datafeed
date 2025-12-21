<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="A" || ($_SESSION["restprofile"]=="J" && $_SESSION["restcajerofiscal"]==1))){
	
	$arcols=array(0=>"#FF0000",10=>"#FF0000",20=>"#FF4000",30=>"#FF4000",40=>"#FF8000",50=>"#FFBF00",60=>"#FFFF00",70=>"#BFFF00",80=>"#80FF00",90=>"#40FF00",100=>"#01DF01");
	require_once("../autoload.php");
	$sender=$_SERVER["PHP_SELF"];
	$gf=new generalFunctions;
	$gc=new generalComponents;
	$dataTables=new dsTables;
	$actividad=$gf->cleanVar($_GET["flag"]);
	if($actividad=="start"){
		echo $gf->utf8("
		<div class='box box-default'>
			<div class='box-header'>SALIDA DE PLATOS POR RANGO DE FECHAS</div>
			<div class='box-body form-inline'>
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
		
		
		 <input type='button' class='btn btn-primary' value='Generar'  onclick=\"cargaHTMLvars('showresult','$sender?flag=go','','20000','unival_regus')\" />");
		echo $gf->utf8("<hr /><div id='showresult'></div></div></div>");
	}elseif($actividad=="detalle"){
		$plato=$gf->cleanVar($_GET["plato"]);
		$desde=$gf->cleanVar($_GET["desde"]);
		$hasta=$gf->cleanVar($_GET["hasta"]);
		$rsInventarios=$gf->dataSet("SELECT PC.ID_RACION, PC.NOMBRE AS COMPONENTE, RO.ID_OPCION, RO.NOMBRE, SPC.ID_OPCION AS SELECOP, SP.CANTIDAD FROM racion_opciones RO LEFT JOIN platos_composicion PC ON PC.ID_RACION=RO.ID_RACION LEFT JOIN sillas_platos SP ON SP.ID_PLATO=PC.ID_PLATO JOIN sillas_platos_composicion SPC ON SPC.ID_ITEM=SP.ID_ITEM AND RO.ID_OPCION=SPC.ID_OPCION JOIN sillas S ON S.ID_SILLA=SP.ID_SILLA JOIN pedidos P ON P.ID_PEDIDO=S.ID_PEDIDO JOIN servicio SE ON SE.ID_SERVICIO=P.ID_SERVICIO WHERE SE.ID_SITIO=:sitio AND SE.FECHA BETWEEN '$desde' AND '$hasta' AND SP.ID_PLATO=:plato GROUP BY SPC.ID_REL ORDER BY RO.NOMBRE",array(":sitio"=>$_SESSION["restbus"],":plato"=>$plato));

		if(count($rsInventarios)>0){
			echo $gf->utf8("<table class='table table-bordered'>");
			$optins=array();
			foreach($rsInventarios as $rwInventario){
				$id_com=$rwInventario["ID_RACION"];
				$nm_com=$rwInventario["COMPONENTE"];
				$id_op=$rwInventario["ID_OPCION"];
				$nm_op=$rwInventario["NOMBRE"];
				$selec=$rwInventario["SELECOP"];
				$camti=$rwInventario["CANTIDAD"];
				$optins[$id_com]["nm"]=$nm_com;
				$optins[$id_com]["op"][$id_op]["nm"]=$nm_op;
				if(!isset($optins[$id_com]["op"][$id_op]["cant"])) $optins[$id_com]["op"][$id_op]["cant"]=0;
				if(!isset($optins[$id_com]["op"][$selec]["cant"])) $optins[$id_com]["op"][$selec]["cant"]=0;
				if($id_op==$selec) $optins[$id_com]["op"][$selec]["cant"]+=$camti;
			}
			foreach($optins as $id_com=>$compons){
				$nm_com=$compons["nm"];
				if(count($compons["op"])>0){
					echo $gf->utf8("<tr class='bg-primary'><td colspan='2'>$nm_com</td>");
					foreach($compons["op"] as $id_op=>$opti){
						$nmopt=$opti["nm"];
						$cant=$opti["cant"];
						echo $gf->utf8("<tr><td>$nmopt</td><td>$cant</td></tr>");
					}
					
				}		
				
			}
			echo $gf->utf8("</table>");
		}else{
			echo "No se encuentran componentes diferenciales para este producto";
		}


	}else{
		$desde=$_POST["fechafromencuesta"];
		$hasta=$_POST["fechatoencuesta"];
		if($desde=="" || $hasta==""){
			echo "Selecciona un rango de fechas";
			exit;
		}
        $resultInt=$gf->dataSet("SELECT PLA.ID_PLATO, PLA.NOMBRE, SUM(PL.CANTIDAD) AS CUENTA FROM platos PLA JOIN sillas_platos PL ON PLA.ID_PLATO=PL.ID_PLATO WHERE PL.ID_SILLA IN(SELECT S.ID_SILLA FROM sillas S JOIN pedidos PE ON PE.ID_PEDIDO=S.ID_PEDIDO JOIN servicio SE ON PE.ID_SERVICIO=SE.ID_SERVICIO WHERE SE.FECHA BETWEEN '$desde' AND '$hasta') AND PLA.ID_CATEGORIA IN(SELECT ID_CATEGORIA FROM platos_categorias WHERE ID_SITIO='{$_SESSION["restbus"]}' ORDER BY ID_CATEGORIA) GROUP BY PLA.ID_PLATO ORDER BY CUENTA DESC");

		if(count($resultInt)>0){
			echo $gf->utf8("
			<table class='table table-striped'>
				<thead>
					<tr>
						<td class='bg-success'>	<b>PRODUCTO</b></td>
						<td class='bg-success'>	<b>CANTIDAD</b></td>
	    			</tr>
				</thead>
				<tbody>
						
			");
			$total=0;
			$total_dcto=0;
			foreach($resultInt as $rowInt){
				
				$id=$rowInt["ID_PLATO"];
				$nombre=$rowInt["NOMBRE"];
				$cuenta=$rowInt["CUENTA"];
				if($cuenta>0){
					$click="getDialog('$sender?flag=detalle&plato=$id&desde=$desde&hasta=$hasta')";
					$btn="btn-primary";
				}else{
					$click="msgBox('No hubo ventas en el rango seleccionado')";
					$btn="btn-default";
				}
				echo $gf->utf8("
				<tr>
					<td>	$nombre</td>
					<td>	<button class='btn btn-minier $btn' onclick=\"$click\">$cuenta</button></td>
				</tr>");
			}
		}
		echo $gf->utf8("</tbody>
		
		</table>
		");
	}
}else{
	echo "No has iniciado sesion!";
}
?>