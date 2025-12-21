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
			<div class='box-header'>REPORTE DE FACTURAS POR RANGO DE FECHAS</div>
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
	}elseif($actividad=="go"){
		$desde=$_POST["fechafromencuesta"];
		$hasta=$_POST["fechatoencuesta"];
		if($desde=="" || $hasta==""){
			echo "Selecciona un rango de fechas";
			exit;
		}

		$infoEmpresa=$gf->dataSet("SELECT NIT, NOMBRE, CIUDAD, DIRECCION, TELEFONO, REGIMEN, RESOLUCION_FACTURAS, INIFACT, PROPIETARIO, PREFIJO FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		if(count($infoEmpresa)>0){
			$rwEmpresa=$infoEmpresa[0];
			$empresa_nit=$rwEmpresa["NIT"];
			$empresa_nombre=$rwEmpresa["NOMBRE"];
			$empresa_propietario=$rwEmpresa["PROPIETARIO"];
			$empresa_ciudad=$rwEmpresa["CIUDAD"];
			$empresa_telefono=$rwEmpresa["TELEFONO"];
			$empresa_direccion=$rwEmpresa["DIRECCION"];
			$empresa_regimen=$rwEmpresa["REGIMEN"];
			$empresa_prefijo=$rwEmpresa["PREFIJO"];
			$empresa_facturac=$rwEmpresa["RESOLUCION_FACTURAS"];
			$empresa_inifact=$rwEmpresa["INIFACT"];
			if($empresa_regimen=="C"){
				$regimen="R&Eacute;GIMEN COM&Uacute;N";
			}else{
				$regimen="R&Eacute;GIMEN SIMPLIFICADO";
			}
		}else{
			echo "Error 356";
			exit;
		}

		$resultInt = $gf->dataSet("SELECT F.ID_FACTURA, CL.TIPO_ID, CL.IDENTIFICACION, CL.NOMBRE AS CLIENTE, CL.DIRECCION, CL.TELEFONO, CL.CORREO, F.PREFIJO, F.CONSECUTIVO, PE.ID_PEDIDO, M.NOMBRE AS MESA, M.TIPO, SP.ID_ITEM, S.ID_SILLA,  DATE(F.FECHA) AS FECHA_EMISION, S.OBSERVACION, SUM(SP.CANTIDAD) AS CANTIDAD, PE.DCTO, SP.LISTO, SP.ENTREGADO, P.NOMBRE, P.DESCRIPCION, P.PRECIO,P.PRECIO_DOM, P.ID_PLATO, I.PORCENTAJE, I.INCLUIDO, I.NOMBRE AS IMPUESTO, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS TENDER FROM servicio SE JOIN pedidos PE ON PE.ID_SERVICIO=SE.ID_SERVICIO JOIN usuarios U ON PE.ID_TENDER=U.ID_USUARIO JOIN mesas M ON PE.ID_MESA=M.ID_MESA JOIN sillas AS S ON S.ID_PEDIDO=PE.ID_PEDIDO JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) JOIN facturas F ON F.ID_PEDIDO=PE.ID_PEDIDO LEFT JOIN clientes CL ON CL.ID_CLIENTE=F.ID_CLIENTE  LEFT JOIN impuestos I ON I.ID_IMPUESTO=P.ID_IMPUESTO WHERE PE.CHEF<>'0000-00-00 00:00:00' AND SE.ID_SITIO={$_SESSION["restbus"]} AND SE.FECHA BETWEEN '$desde' AND '$hasta' GROUP BY PE.ID_PEDIDO ORDER BY F.FECHA");
		
		if(count($resultInt)>0){
			foreach($resultInt as $rwChair){
				$id_cliente=$rwChair["IDENTIFICACION"];
				$tipoid=$rwChair["TIPO_ID"];
				$cliente=$rwChair["CLIENTE"];
				$direccion=$rwChair["DIRECCION"];
				$telefono=$rwChair["TELEFONO"];
				$prefijo=$rwChair["PREFIJO"];
				$consecutivo=$rwChair["CONSECUTIVO"];
				$id_pedido=$rwChair["ID_PEDIDO"];
				$id_factura=$rwChair["ID_FACTURA"];
				$fecha_emision=$rwChair["FECHA_EMISION"];
				$tipo=$rwChair["TIPO"];
				$id_item=$rwChair["ID_PLATO"];
				$id_silla=$rwChair["ID_SILLA"];
				$observacion=$rwChair["OBSERVACION"];
				$cantidad=$rwChair["CANTIDAD"];
				$nombre_plato=$rwChair["NOMBRE"];
				$descripcion=$rwChair["DESCRIPCION"];
				$precio=$rwChair["PRECIO"];
				$descuento=$rwChair["DCTO"];
				$impuesto=$rwChair["IMPUESTO"];
				$tender=$rwChair["TENDER"];
				$porcentaje=$rwChair["PORCENTAJE"];
				$incluido=$rwChair["INCLUIDO"];
				$precio_dom=$rwChair["PRECIO_DOM"];
				if($tipo=="D") $precio=$precio_dom;
				if($porcentaje>0){
					$preciobase=(($precio*$cantidad))/(1+($porcentaje/100));
					$IMPUESTO=$preciobase*($porcentaje/100);
				}else{
					$IMPUESTO=0;
					$preciobase=0;
				}

				if(isset($imptable[$id_pedido][$impuesto]["im"])){
					$imptable[$id_pedido][$impuesto]["im"]+=$IMPUESTO;
					$imptable[$id_pedido][$impuesto]["pb"]+=$preciobase;
				}else{
					$imptable[$id_pedido][$impuesto]["im"]=$IMPUESTO;
					$imptable[$id_pedido][$impuesto]["pb"]=$preciobase;
				}
				//$acumprice+=($precio*$cantidad);
				$tars[$impuesto]=$porcentaje;
				$harped[$id_pedido]["id_factura"]=$id_factura;
				$harped[$id_pedido]["id_cliente"]=$id_cliente;
				$harped[$id_pedido]["cliente"]=$cliente;
				$harped[$id_pedido]["tipoid"]=$tipoid;
				$harped[$id_pedido]["fecha"]=$fecha_emision;
				$harped[$id_pedido]["direccion"]=$direccion;
				$harped[$id_pedido]["telefono"]=$telefono;
				$harped[$id_pedido]["descuento"]=$descuento;
				$harped[$id_pedido]["prefijo"]=$prefijo;
				$harped[$id_pedido]["consecutivo"]=$consecutivo;
				$harped[$id_pedido]["tipo"]=$tipo;
				$harped[$id_pedido]["tender"]=$tender;
				$harped[$id_pedido]["observacion"]=$observacion;
				$harped[$id_pedido]["items"][$id_item]=array("p"=>$nombre_plato,"z"=>$precio,"k"=>$cantidad);
			}


			echo $gf->utf8("
			<div class='panel panel-default'>
			<div class='panel-heading'>REPORTE FACTURAS $desde - $hasta <form class='form-inline pull-right' style='margin:0px;padding:0px !important;' method='post' target='_blank' action='Admin/rpt_facturas.php?flag=xls'><input type='hidden' name='fechafromencuesta' value='$desde' /><input type='hidden' name='fechatoencuesta' value='$hasta' /><button type='submit' class='btn btn-minier btn-primary pull-right'><i class='fa fa-file-excel-o'></i> Exportar XLS</button></form></div>
			<div class='panel-body' style='overflow:auto;'>
			<table class='table table-bordered table-striped table-responsive responsive'>
				<thead>
					<tr>
						<td>	PREFIJO</td>
						<td>	CONSECUTIVO</td>
						<td>	FECHA EXPEDICION</td>
						<td>	EMISOR IDENTIFICACION</td>
						<td>	EMISOR RAZON SOCIAL</td>
						<td>	EMISOR NOMBRE</td>
                        <td>	EMISOR DOMICILIO</td>
                        <td>	RECEPTOR IDENTIFICACION</td>
						<td>	RECEPTOR NOMBRE</td>
						<td>	RECEPTOR DOMICILIO</td>
						<td>	RECEPTOR TELEFONO</td>
						<td>	FECHA SERVICIO</td>
						<td>	VALOR FACTURA</td>
						<td>	BASE IMPONIBLE</td>
						<td>	IMPUETO</td>
						<td>	BASE</td>
						<td>	TOTAL</td>
						
					</tr>
				</thead>
				<tbody>
						
			");

			$total=0;
			$total_dcto=0;
			foreach($harped as $idPedido=>$componentes){
				$id_factura=$componentes["id_factura"];
				$id_cliente=$componentes["id_cliente"];
				$cliente=$componentes["cliente"];
				$tipoid=$componentes["tipoid"];
				$direccion=$componentes["direccion"];
				$telefono=$componentes["telefono"];
				$descuento=$componentes["descuento"];
				$prefijo=$componentes["prefijo"];
				$consecutivo=$componentes["consecutivo"];
				$tipo=$componentes["tipo"];
				$fecha=$componentes["fecha"];
				$items = array();
				$subtotal=0;
				$acumprice=0;
				foreach($componentes["items"] as $id_item=>$itm){
					$plato=$itm["p"];
					$precio=$itm["z"];
					$kant=$itm["k"];
					$acumprice+=$precio*$kant;
				}
				$base_imp=0;
				$vr_impuesto=0;
				$tari=0;
				if(isset($imptable[$idPedido])){
					if(count($imptable[$idPedido])>0){
						foreach($imptable[$idPedido] as $impu=>$valinfo){
							$tari=$tars[$impu];
							if($impu!=""){
								$vr_impuesto=$valinfo["im"];
								$base_imp=$valinfo["pb"];
							}
						}
					}
				}

				if($descuento>0){
					$acumprice=$acumprice-$descuento;
				}
			

				echo $gf->utf8("
				<tr>
					<td>	$empresa_prefijo</td>
					<td>	$consecutivo</td>
					<td>	$fecha</td>
					<td>	$empresa_nit</td>
					<td>	$empresa_nombre</td>
					<td>	$empresa_propietario</td>
					<td>	$empresa_direccion, $empresa_ciudad</td>
					<td>	$id_cliente</td>
					<td>	$cliente</td>
					<td>	$direccion</td>
					<td>	$telefono</td>
					<td>	$fecha</td>
					<td>	".number_format($acumprice,0)."</td>
					<td>	".number_format($tari,0)."</td>
					<td>	".number_format($vr_impuesto,0)."</td>
					<td>	".number_format($base_imp,0)."</td>
					<td>	".number_format($acumprice,0)."</td>
				</tr>");
			}
		}
		echo $gf->utf8("</tbody>
		</table>
		</div></div>
		");
	}else{
		$desde=$_POST["fechafromencuesta"];
		$hasta=$_POST["fechatoencuesta"];
		if($desde=="" || $hasta==""){
			echo "Selecciona un rango de fechas";
			exit;
		}

		$infoEmpresa=$gf->dataSet("SELECT NIT, NOMBRE, CIUDAD, DIRECCION, TELEFONO, REGIMEN, RESOLUCION_FACTURAS, INIFACT, PROPIETARIO, PREFIJO FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		if(count($infoEmpresa)>0){
			$rwEmpresa=$infoEmpresa[0];
			$empresa_nit=$rwEmpresa["NIT"];
			$empresa_nombre=$rwEmpresa["NOMBRE"];
			$empresa_propietario=$rwEmpresa["PROPIETARIO"];
			$empresa_ciudad=$rwEmpresa["CIUDAD"];
			$empresa_telefono=$rwEmpresa["TELEFONO"];
			$empresa_direccion=$rwEmpresa["DIRECCION"];
			$empresa_regimen=$rwEmpresa["REGIMEN"];
			$empresa_prefijo=$rwEmpresa["PREFIJO"];
			$empresa_facturac=$rwEmpresa["RESOLUCION_FACTURAS"];
			$empresa_inifact=$rwEmpresa["INIFACT"];
			if($empresa_regimen=="C"){
				$regimen="R&Eacute;GIMEN COM&Uacute;N";
			}else{
				$regimen="R&Eacute;GIMEN SIMPLIFICADO";
			}
		}else{
			echo "Error 356";
			exit;
		}

		$resultInt = $gf->dataSet("SELECT F.ID_FACTURA, CL.TIPO_ID, CL.IDENTIFICACION, CL.NOMBRE AS CLIENTE, CL.DIRECCION, CL.TELEFONO, CL.CORREO, F.PREFIJO, F.CONSECUTIVO, PE.ID_PEDIDO, M.NOMBRE AS MESA, M.TIPO, SP.ID_ITEM, S.ID_SILLA,  DATE(F.FECHA) AS FECHA_EMISION, S.OBSERVACION, SUM(SP.CANTIDAD) AS CANTIDAD, PE.DCTO, SP.LISTO, SP.ENTREGADO, P.NOMBRE, P.DESCRIPCION, P.PRECIO,P.PRECIO_DOM, P.ID_PLATO, I.PORCENTAJE, I.INCLUIDO, I.NOMBRE AS IMPUESTO, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS TENDER FROM servicio SE JOIN pedidos PE ON PE.ID_SERVICIO=SE.ID_SERVICIO JOIN usuarios U ON PE.ID_TENDER=U.ID_USUARIO JOIN mesas M ON PE.ID_MESA=M.ID_MESA JOIN sillas AS S ON S.ID_PEDIDO=PE.ID_PEDIDO JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) JOIN facturas F ON F.ID_PEDIDO=PE.ID_PEDIDO LEFT JOIN clientes CL ON CL.ID_CLIENTE=F.ID_CLIENTE  LEFT JOIN impuestos I ON I.ID_IMPUESTO=P.ID_IMPUESTO WHERE PE.CHEF<>'0000-00-00 00:00:00' AND SE.ID_SITIO={$_SESSION["restbus"]} AND SE.FECHA BETWEEN '$desde' AND '$hasta' GROUP BY PE.ID_PEDIDO ORDER BY F.FECHA");
		
		if(count($resultInt)>0){
			foreach($resultInt as $rwChair){
				$id_cliente=$rwChair["IDENTIFICACION"];
				$tipoid=$rwChair["TIPO_ID"];
				$cliente=$rwChair["CLIENTE"];
				$direccion=$rwChair["DIRECCION"];
				$telefono=$rwChair["TELEFONO"];
				$prefijo=$rwChair["PREFIJO"];
				$consecutivo=$rwChair["CONSECUTIVO"];
				$id_pedido=$rwChair["ID_PEDIDO"];
				$id_factura=$rwChair["ID_FACTURA"];
				$fecha_emision=$rwChair["FECHA_EMISION"];
				$tipo=$rwChair["TIPO"];
				$id_item=$rwChair["ID_PLATO"];
				$id_silla=$rwChair["ID_SILLA"];
				$observacion=$rwChair["OBSERVACION"];
				$cantidad=$rwChair["CANTIDAD"];
				$nombre_plato=$rwChair["NOMBRE"];
				$descripcion=$rwChair["DESCRIPCION"];
				$precio=$rwChair["PRECIO"];
				$descuento=$rwChair["DCTO"];
				$impuesto=$rwChair["IMPUESTO"];
				$tender=$rwChair["TENDER"];
				$porcentaje=$rwChair["PORCENTAJE"];
				$incluido=$rwChair["INCLUIDO"];
				$precio_dom=$rwChair["PRECIO_DOM"];
				if($tipo=="D") $precio=$precio_dom;
				if($porcentaje>0){
					$preciobase=(($precio*$cantidad))/(1+($porcentaje/100));
					$IMPUESTO=$preciobase*($porcentaje/100);
				}else{
					$IMPUESTO=0;
					$preciobase=0;
				}

				if(isset($imptable[$id_pedido][$impuesto]["im"])){
					$imptable[$id_pedido][$impuesto]["im"]+=$IMPUESTO;
					$imptable[$id_pedido][$impuesto]["pb"]+=$preciobase;
				}else{
					$imptable[$id_pedido][$impuesto]["im"]=$IMPUESTO;
					$imptable[$id_pedido][$impuesto]["pb"]=$preciobase;
				}
				//$acumprice+=($precio*$cantidad);
				$tars[$impuesto]=$porcentaje;
				$harped[$id_pedido]["id_factura"]=$id_factura;
				$harped[$id_pedido]["id_cliente"]=$id_cliente;
				$harped[$id_pedido]["cliente"]=$cliente;
				$harped[$id_pedido]["tipoid"]=$tipoid;
				$harped[$id_pedido]["fecha"]=$fecha_emision;
				$harped[$id_pedido]["direccion"]=$direccion;
				$harped[$id_pedido]["telefono"]=$telefono;
				$harped[$id_pedido]["descuento"]=$descuento;
				$harped[$id_pedido]["prefijo"]=$prefijo;
				$harped[$id_pedido]["consecutivo"]=$consecutivo;
				$harped[$id_pedido]["tipo"]=$tipo;
				$harped[$id_pedido]["tender"]=$tender;
				$harped[$id_pedido]["observacion"]=$observacion;
				$harped[$id_pedido]["items"][$id_item]=array("p"=>$nombre_plato,"z"=>$precio,"k"=>$cantidad);
			}
			$report="";

			$report.='
			<h3>REPORTE FACTURAS - '.$empresa_nombre.' '.$empresa_ciudad.' ('.$desde.' - '.$hasta.') </h3>
			<table border="1">
				<thead>
					<tr bgcolor="#CCCCCC">
						<td>	PREFIJO</td>
						<td>	CONSECUTIVO</td>
						<td>	FECHA EXPEDICION</td>
						<td>	EMISOR IDENTIFICACION</td>
						<td>	EMISOR RAZON SOCIAL</td>
						<td>	EMISOR NOMBRE</td>
                        <td>	EMISOR DOMICILIO</td>
                        <td>	RECEPTOR IDENTIFICACION</td>
						<td>	RECEPTOR NOMBRE</td>
						<td>	RECEPTOR DOMICILIO</td>
						<td>	RECEPTOR TELEFONO</td>
						<td>	FECHA SERVICIO</td>
						<td>	VALOR FACTURA</td>
						<td>	BASE IMPONIBLE</td>
						<td>	IMPUETO</td>
						<td>	BASE</td>
						<td>	TOTAL</td>
						
					</tr>
				</thead>
				<tbody>';

			$total=0;
			$total_dcto=0;
			foreach($harped as $idPedido=>$componentes){
				$id_factura=$componentes["id_factura"];
				$id_cliente=$componentes["id_cliente"];
				$cliente=$componentes["cliente"];
				$tipoid=$componentes["tipoid"];
				$direccion=$componentes["direccion"];
				$telefono=$componentes["telefono"];
				$descuento=$componentes["descuento"];
				$prefijo=$componentes["prefijo"];
				$consecutivo=$componentes["consecutivo"];
				$tipo=$componentes["tipo"];
				$fecha=$componentes["fecha"];
				$items = array();
				$subtotal=0;
				$acumprice=0;
				foreach($componentes["items"] as $id_item=>$itm){
					$plato=$itm["p"];
					$precio=$itm["z"];
					$kant=$itm["k"];
					$acumprice+=$precio*$kant;
				}
				$base_imp=0;
				$vr_impuesto=0;
				$tari=0;
				if(isset($imptable[$idPedido])){
					if(count($imptable[$idPedido])>0){
						foreach($imptable[$idPedido] as $impu=>$valinfo){
							$tari=$tars[$impu];
							if($impu!=""){
								$vr_impuesto=$valinfo["im"];
								$base_imp=$valinfo["pb"];
							}
						}
					}
				}

				if($descuento>0){
					$acumprice=$acumprice-$descuento;
				}
			

				$report.='
				<tr>
					<td>	'.$empresa_prefijo.'</td>
					<td>	'.$consecutivo.'</td>
					<td>	'.$fecha.'</td>
					<td>	'.$empresa_nit.'</td>
					<td>	'.$empresa_nombre.'</td>
					<td>	'.$empresa_propietario.'</td>
					<td>	'.$empresa_direccion.', '.$empresa_ciudad.'</td>
					<td>	'.$id_cliente.'</td>
					<td>	'.$cliente.'</td>
					<td>	'.$direccion.'</td>
					<td>	'.$telefono.'</td>
					<td>	'.$fecha.'</td>
					<td>	'.$acumprice.'</td>
					<td>	'.$tari.'</td>
					<td>	'.$vr_impuesto.'</td>
					<td>	'.$base_imp.'</td>
					<td>	'.$acumprice.'</td>
				</tr>';
			}
		}

		$report.='</tbody></table>';
		header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-Disposition: attachment;filename=\"reporteFacturas.xls\"");
		header("Cache-Control: max-age=0");
		echo $report;
		exit;
	}
}else{
	echo "No has iniciado sesion!";
}
?>