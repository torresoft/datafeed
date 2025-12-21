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
			<div class='box-header'>REPORTE DE VENTAS POR RANGO DE FECHAS</div>
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
			<select class='form-control unival_regus' style='width:200px;' name='id_fp' id='id_fp'>
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
		
		 <input type='button' class='btn btn-primary' value='Generar'  onclick=\"cargaHTMLvars('showresult','$sender?flag=go','','20000','unival_regus')\" />");
		echo $gf->utf8("<hr /><div id='showresult'></div></div></div>");
	}else{
		$desde=$_POST["fechafromencuesta"];
		$hasta=$_POST["fechatoencuesta"];
		$id_fp=$_POST["id_fp"];
		if($id_fp==0){
			$condfp="1";
			$titl="";
		}else{
			$condfp="P.ID_FP='$id_fp'";
			$rsFp=$gf->dataSet("SELECT ID_FP, NOMBRE FROM formas_pago WHERE ID_FP='$id_fp'");
			$nm_fp=$rsFp[0]["NOMBRE"];
			$titl="FILTRADO POR FORMA DE PAGO $nm_fp";
		}
		if($desde=="" || $hasta==""){
			echo "Selecciona un rango de fechas";
			exit;
        }
        $idcock=isset($_POST["lacocina"]) ? $_POST["lacocina"] : 0;
		if($idcock>0){
            $condcock="PL.ID_COCINA='$idcock'";
            $rsFpC=$gf->dataSet("SELECT ID_COCINA, NOMBRE FROM cocinas WHERE ID_COCINA='$idcock'");
			$nm_co=$rsFpC[0]["NOMBRE"];
			$titl.=", FILTRADO POR COCINA $nm_co";
		}else{
            $condcock="1";
		}
		$resultInt = $gf->dataSet("SELECT M.ID_MESA, SE.FECHA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, COUNT(S.ID_SILLA) AS SILLAS, P.APERTURA, P.CIERRE, SUM(IF(M.TIPO<>'D',PL.PRECIO*SP.CANTIDAD,PL.PRECIO_DOM*SP.CANTIDAD)) AS PAGO, P.DCTO FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CIERRE<>'0000-00-00 00:00:00') JOIN servicio SE ON SE.ID_SERVICIO=P.ID_SERVICIO JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO JOIN sillas_platos SP ON SP.ID_SILLA=S.ID_SILLA JOIN platos PL ON PL.ID_PLATO=SP.ID_PLATO WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND SE.FECHA BETWEEN '$desde' AND '$hasta' AND $condfp AND $condcock GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
		$reporte="";
		if(count($resultInt)>0){
			$reporte.="
			<div class='box box-danger'>
				<div class='box-header'>REPORTE VENTAS DESDE $desde HASTA $hasta <b>$titl</b></div>
				<div class='box-body'>
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
						
			";
			$total=0;
			$total_dcto=0;
			$rows="";
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
				$rows.="
				<tr>
					<td>	$servicio</td>
					<td>	$nombre</td>
					<td>	$tender</td>
					<td>	$sillas</td>
					<td>	$apertura</td>
					<td>	$cierre</td>
					<td>".number_format($pago-$dcto,0)."</td>
					<td>".number_format($dcto,0)."</td>
				</tr>";
			}

			echo $gf->utf8("<a class='btn btn-xs btn-warning' href='Admin/pdf_x_kit.php?id_kit=$idcock&desde=$desde&hasta=$hasta&id_fp=$id_fp&tm=58'>Reporte 58mm</a>".$reporte."<tr>
			<td colspan='6'>TOTALES</td><td><big>".number_format($total,0)."</big></td><td><big>".number_format($total_dcto,0)."</big></td></tr>
			</tr>".$rows."</tbody>
			<tfoot>
					<tr>
					<td colspan='6'>TOTALES</td><td>".number_format($total,0)."</td><td>".number_format($total_dcto,0)."</td></tr>
					</tr>
			</tfoot>
			</table>
			</div>
			</div>
			");
		}else{
			echo $gf->utf8("No hay resultados");
		}
		
	}
}else{
	echo "No has iniciado sesion!";
}
?>