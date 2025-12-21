<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="T" || ($_SESSION["restprofile"]=="J" && $_SESSION["restcajerofiscal"]==1))){
	
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
			<div class='box-header'>REPORTE DE VENTAS POR SERVICIO ENTRE  FECHAS</div>
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

			Forma de pago:
				<select class='form-control unival_regus' style='width:120px;' name='id_fp' id='id_fp'>
					<option value='0'>Todos</option>
					");
					$rsFp=$gf->dataSet("SELECT ID_FP, NOMBRE FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}'");
					if(count($rsFp)>0){
						foreach($rsFp as $rwFp){
							$id_fp=$rwFp["ID_FP"];
							$nm_fp=$rwFp["NOMBRE"];
							echo $gf->utf8("<option value='$id_fp'>$nm_fp</option>");
						}
					}
				echo $gf->utf8("
				</select>
		<br />
				Cajero:
				<select class='form-control unival_regus' style='width:120px;' name='id_cajero' id='id_cajero'>
					<option value='0'>Todos</option>
					");
					$rsFpu=$gf->dataSet("SELECT ID_USUARIO, CONCAT(NOMBRES,' ',APELLIDOS) AS USUARIO FROM usuarios WHERE ID_SITIO='{$_SESSION["restbus"]}' AND PERFIL<>'M'");
					if(count($rsFpu)>0){
						foreach($rsFpu as $rwFpu){
							$id_us=$rwFpu["ID_USUARIO"];
							$nm_us=$rwFpu["USUARIO"];
							echo $gf->utf8("<option value='$id_us'>$nm_us</option>");
						}
					}
				echo $gf->utf8("
				</select>
		
		 <input type='button' class='btn btn-primary' value='Generar'  onclick=\"cargaHTMLvars('showresult','$sender?flag=go','','20000','unival_regus')\" />");
		echo $gf->utf8("<hr /><div id='showresult'></div></div></div>");
	}else{
		$desde=$_POST["fechafromencuesta"];
		$hasta=$_POST["fechatoencuesta"];
		$id_fp=$_POST["id_fp"];
		
		$id_cajero=$_POST["id_cajero"];
		if($id_cajero==0){
			$condcajero="1";
			$titl2="";
		}else{
			$condcajero="P.ID_CAJERO='$id_cajero'";
			$rsFpU=$gf->dataSet("SELECT ID_USUARIO, CONCAT(NOMBRES,' ',APELLIDOS) AS USUARIO FROM usuarios WHERE ID_USUARIO='$id_cajero'");
			$nm_us=$rsFpU[0]["USUARIO"];
			$titl2=", CAJERO: $nm_us";
		}
		if($id_fp==0){
			$condfp="1";
			$titl="";
			$caja=1;
			$abos=array();
			$condse="1";
		}else{
			$condfp="P.ID_FP='$id_fp'";
			$condse="C.ID_FP='$id_fp'";
			$rsFp=$gf->dataSet("SELECT ID_FP, NOMBRE, CAJA FROM formas_pago WHERE ID_FP='$id_fp'");
			$nm_fp=$rsFp[0]["NOMBRE"];
			$caja=$rsFp[0]["CAJA"];
			$titl=", FILTRADO POR FORMA DE PAGO $nm_fp";
			$rsAbonos=$gf->dataSet("SELECT S.ID_SERVICIO, A.ID_PEDIDO,A.VALOR, A.ID_FP FROM pedidos_abonos A JOIN pedidos P ON P.ID_PEDIDO=A.ID_PEDIDO JOIN servicio S ON S.ID_SERVICIO=P.ID_SERVICIO WHERE S.FECHA BETWEEN '$desde' AND '$hasta' AND $condcajero AND S.ID_SITIO='".$_SESSION["restbus"]."' AND A.ID_FP<>'$id_fp' AND P.ID_FP='$id_fp' ORDER BY P.ID_PEDIDO");
			$abos=array();
			if(count($rsAbonos)>0){
				foreach($rsAbonos as $rwAbonos){
					$id_servicio=$rwAbonos["ID_SERVICIO"];
					$vl_abono=$rwAbonos["VALOR"];
					$id_forma=$rwAbonos["ID_FP"];
					if(!isset($abos[$id_servicio])) $abos[$id_servicio]=0;
					$abos[$id_servicio]+=$vl_abono;
				}
			}
		}


		if($desde=="" || $hasta==""){
			echo "Selecciona un rango de fechas";
			exit;
		}
		$resultInt = $gf->dataSet("SELECT SE.ID_SERVICIO, SE.FECHA, SUM(P.PAGO+P.PROPINA) AS VENTAS, SUM(P.DCTO) AS DESCUENTOS, SUM(C.VALOR) AS VENTAS1, SE.BASE_CAJA FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CIERRE<>'0000-00-00 00:00:00') JOIN servicio SE ON SE.ID_SERVICIO=P.ID_SERVICIO JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER  LEFT JOIN servicio_cuadre C ON C.ID_SERVICIO=SE.ID_SERVICIO AND $condse WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND SE.FECHA BETWEEN '$desde' AND '$hasta' AND $condfp AND $condcajero GROUP BY SE.ID_SERVICIO ORDER BY SE.FECHA");
					
		if(count($resultInt)>0){
			echo $gf->utf8("
			<div class='box box-danger'>
				<div class='box-header'>REPORTE VENTAS DESDE $desde HASTA $hasta <b>$titl</b> <b>$titl2</b></div>
				<div class='box-body'>
			<table class='table table-striped'>
				<thead>
					<tr>
						<td>	SERVICIO</td>
						<td>	FECHA</td>
						<td>	BASE CAJA</td>
						<td>	VENTAS EN CUADRE</td>
						<td>	DESCUADRE</td>
						<td>	VENTAS OPERACI&Oacute;N + PROPINAS</td>
						<td>	DESCUENTOS</td>
					</tr>
				</thead>
				<tbody>
						
			");
			$total=0;
			$totalv=0;
			$totald=0;
			$total_dcto=0;
			$rows="";
			foreach($resultInt as $rowInt){
				$id_servicio=$rowInt["ID_SERVICIO"];
				$servicio=$rowInt["FECHA"];
				$pago=$rowInt["VENTAS"];
				$vtas=$rowInt["VENTAS1"];
				$dcto=$rowInt["DESCUENTOS"];
				$base_caja=$rowInt["BASE_CAJA"];
				if($caja==0) $base_caja=0;
				
				if(isset($abos[$id_servicio])){
					$pago-=$abos[$id_servicio];
				}
				$descuadre=$vtas-$pago;
				$total+=$pago;
				$totalv+=$vtas;
				$totald+=$descuadre;
				$total_dcto+=$dcto;
				$rows.="
				<tr>
					<td>	$id_servicio</td>
					<td>	$servicio</td>
					<td>	".number_format($base_caja,0)."</td>
					<td>	".number_format($vtas,0)."</td>
					<td>	".number_format($descuadre,0)."</td>
					<td>".number_format($pago,0)."</td>
					<td>".number_format($dcto,0)."</td>
				</tr>";
			}
			echo $gf->utf8("<tr>
			<td colspan='3'>TOTALES</td><td><b>".number_format($totalv,0)."</b></td><td>".number_format($totald,0)."</td><td><b>".number_format($total,0)."</b></td><td>".number_format($total_dcto,0)."</td></tr>
			</tr>".$rows);
		echo $gf->utf8("</tbody>
		<tfoot>
				<tr>
				<td colspan='3'>TOTALES</td><td><b>".number_format($totalv,0)."</b></td><td>".number_format($totald,0)."</td><td><b>".number_format($total,0)."</b></td><td>".number_format($total_dcto,0)."</td></tr>
				</tr>
		</tfoot>
		</table>
		</div>

		");

		}else{
			echo "No hay resultados";
		}
		
	}
}else{
	echo "No has iniciado sesion!";
}
?>