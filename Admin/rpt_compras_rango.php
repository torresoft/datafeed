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
			<div class='box-header'>REPORTE DE COMPRAS DE INVENTARIO POR RANGO DE FECHAS</div>
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
		
		
		 <input type='button' class='btn btn-primary' value='Generar'  onclick=\"cargaHTMLvars('showresult','$sender?flag=go','','20000','unival_regus')\" />");
		echo $gf->utf8("<hr /><div id='showresult'></div></div></div>");
	}else{
		$desde=$_POST["fechafromencuesta"];
		$hasta=$_POST["fechatoencuesta"];
		if($desde=="" || $hasta==""){
			echo "Selecciona un rango de fechas";
			exit;
        }
		$id_fp=$_POST["id_fp"];
		if($id_fp==0){
			$condfp="1";
			$titl="";
		}else{
			$condfp="C.ID_FP='$id_fp'";
			$rsFp=$gf->dataSet("SELECT ID_FP, NOMBRE FROM formas_pago WHERE ID_FP='$id_fp'");
			$nm_fp=$rsFp[0]["NOMBRE"];
			$titl="FILTRADO POR FORMA DE PAGO $nm_fp";
		}



        $compras=$gf->dataSet("SELECT C.ID_COMPRA, C.FECHA, C.OBSERVACION, C.FACTURA, P.NOMBRE AS PROVEEDOR, SUM(I.PRECIO*I.CANTIDAD) AS VALOR FROM inventario_compras C JOIN proveedores P ON C.ID_PROVEEDOR=P.ID_PROVEEDOR JOIN inventario I ON I.ID_COMPRA=C.ID_COMPRA WHERE C.FECHA BETWEEN '$desde' AND '$hasta' AND $condfp AND C.ID_SITIO=:sitio GROUP BY C.ID_COMPRA ORDER BY C.ID_COMPRA",array(":sitio"=>$_SESSION["restbus"]));
        if(count($compras)>0){
			echo $gf->utf8("
			REPORTE DE COMPRAS DE $desde A $hasta $titl
			<table class='table table-striped'>
				<thead>
					<tr>
						<td>	ID. COMPRA</td>
						<td>	PROVEEDOR</td>
						<td>	FACTURA</td>
						<td>	DESCRIPCION</td>
						<td>	FECHA</td>
						<td>	VALOR</td>
					</tr>
				</thead>
				<tbody>
						
            ");
            $totGasto=0;
            foreach($compras as $compra){
                $id_compra=$compra["ID_COMPRA"];
                $factura=$compra["FACTURA"];
                $proveedor=$compra["PROVEEDOR"];
                $descripcion=$compra["OBSERVACION"];
                $fecha=$compra["FECHA"];
                $valor=$compra["VALOR"];
                echo $gf->utf8("
                <tr class='bg-warning'>
                    <td>	$id_compra</td>
                    <td>	$proveedor</td>
                    <td>	$factura</td>
                    <td>	$descripcion</td>
                    <td>	$fecha</td>
                    <td align='right'>".number_format($valor,0)."</td>
                </tr>");
                $totGasto+=$valor;
            }
            echo $gf->utf8("</tbody>
            <tfoot>
                    <tr>
                    <td colspan='5'>TOTAL</td><td align='right'><big>".number_format($totGasto,0)."</big></td></tr>
                    </tr>
            </tfoot>
            </table>
            ");
        }

		
	}
}else{
	echo "No has iniciado sesion!";
}
?>