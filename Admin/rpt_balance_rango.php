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
			<div class='box-header'>BALANCE GENERAL POR RANGO DE FECHAS</div>
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
			<!--
			Por cocina:
			<select name='lacocina'  id='lacocina' class='form-control unival_regus'>
				<option value='0'>Todas</option>
				");
				$rsCocinas=$gf->dataSet("SELECT ID_COCINA, NOMBRE FROM cocinas WHERE ID_SITIO='{$_SESSION["restbus"]}' ORDER BY NOMBRE");
				if(count($rsCocinas)>0){
					foreach($rsCocinas as $rwCoc){
						$idcock=$rwCoc["ID_COCINA"];
						$nmcock=$rwCoc["NOMBRE"];
						echo $gf->utf8("<option value='$idcock'>$nmcock</option>");
					}
				}
				echo $gf->utf8("
			</select>
			-->
		 <input type='button' class='btn btn-primary' value='Generar'  onclick=\"cargaHTMLvars('showresult','$sender?flag=go','','20000','unival_regus')\" />");
		echo $gf->utf8("<hr /><div id='showresult'></div></div></div>");
	}else{
		$desde=$_POST["fechafromencuesta"];
		$hasta=$_POST["fechatoencuesta"];
		if($desde=="" || $hasta==""){
			echo "Selecciona un rango de fechas";
			exit;
		}
		$idcock=isset($_POST["lacocina"]) ? $_POST["lacocina"] : 0;
		if($idcock>0){

		}else{

		}
		$resultInt = $gf->dataSet("SELECT M.ID_MESA, SE.FECHA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, COUNT(S.ID_SILLA) AS SILLAS, P.APERTURA, P.CIERRE, P.PAGO, P.DCTO FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CAJA<>'0000-00-00 00:00:00') JOIN servicio SE ON SE.ID_SERVICIO=P.ID_SERVICIO JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND SE.FECHA BETWEEN '$desde' AND '$hasta' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
					
		if(count($resultInt)>0){
			echo $gf->utf8("
			<table class='table table-striped'>
				<thead>
					<tr>
						<td>	SERVICIO</td>
						<td>	MESA</td>
						<td>	TENDER</td>
						<td>	SILLAS</td>
						<td>	APERTURA</td>
						<td>	CIERRE</td>
						<td>	PAGO</td>
						<td>	DCTO</td>
					</tr>
				</thead>
				<tbody>
						
			");
			$total=0;
			$total_dcto=0;
			foreach($resultInt as $rowInt){
				$id_mesa=$rowInt["ID_MESA"];
				$nombre=$rowInt["NOMBRE"];
				$servicio=$rowInt["FECHA"];
				$tender=$rowInt["TENDER"];
				$id_pedido=$rowInt["ID_PEDIDO"];
				$sillas=$rowInt["SILLAS"];
				$apertura=$rowInt["APERTURA"];
				$cierre=$rowInt["CIERRE"];
				$pago=$rowInt["PAGO"];
				$dcto=$rowInt["DCTO"];
				$total+=$pago;
				$total_dcto+=$dcto;
				echo $gf->utf8("
				<tr>
					<td>	$servicio</td>
					<td>	$nombre</td>
					<td>	$tender</td>
					<td>	$sillas</td>
					<td>	$apertura</td>
					<td>	$cierre</td>
					<td>".number_format($pago,0)."</td>
					<td>".number_format($dcto,0)."</td>
				</tr>");
			}
		}
		echo $gf->utf8("</tbody>
		<tfoot>
				<tr>
				<td colspan='6'>TOTALES</td><td>".number_format($total,0)."</td><td>".number_format($total_dcto,0)."</td></tr>
				</tr>
		</tfoot>
		</table>
		");
	}
}else{
	echo "No has iniciado sesion!";
}
?>