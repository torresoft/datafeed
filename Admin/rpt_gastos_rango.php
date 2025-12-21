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
			<div class='box-header'>REPORTE DE GASTOS POR RANGO DE FECHAS</div>
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
		if($desde=="" || $hasta==""){
			echo "Selecciona un rango de fechas";
			exit;
        }
        $id_fp=$_POST["id_fp"];
		if($id_fp==0){
			$condfp="1";
			$titl="";
		}else{
			$condfp="G.ID_FP='$id_fp'";
			$rsFp=$gf->dataSet("SELECT ID_FP, NOMBRE FROM formas_pago WHERE ID_FP='$id_fp'");
			$nm_fp=$rsFp[0]["NOMBRE"];
			$titl="FILTRADO POR FORMA DE PAGO $nm_fp";
		}
		$idcock=isset($_POST["lacocina"]) ? $_POST["lacocina"] : 0;
		if($idcock>0){
            $condcock="G.ID_COCINA='$idcock'";
            $rsFpC=$gf->dataSet("SELECT ID_COCINA, NOMBRE FROM cocinas WHERE ID_COCINA='$idcock'");
			$nm_co=$rsFpC[0]["NOMBRE"];
			$titl.=", FILTRADO POR COCINA $nm_co";
		}else{
            $condcock="1";
		}
        $totGasto=0;
        $gtos=$gf->dataSet("SELECT T.NOMBRE, G.ID_GASTO, G.DESCRIPCION, G.FECHA, G.VALOR FROM gastos G JOIN gastos_tipos T ON T.ID_TIPO=G.ID_TIPO WHERE G.FECHA BETWEEN '$desde' AND '$hasta' AND $condfp AND $condcock AND G.ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
        if(count($gtos)>0){
			echo $gf->utf8("
			REPORTE DE GASTOS DE $desde A $hasta $titl
			<table class='table table-striped'>
				<thead>
					<tr>
						<td>	ID. GASTO</td>
						<td>	TIPO</td>
						<td>	DESCRIPCION</td>
						<td>	FECHA</td>
						<td>	VALOR</td>
					</tr>
				</thead>
				<tbody>
						
            ");
            foreach($gtos as $gto){
                $id_gto=$gto["ID_GASTO"];
                $tipo=$gto["NOMBRE"];
                $descripcion=$gto["DESCRIPCION"];
                $fecha=$gto["FECHA"];
                $valor=$gto["VALOR"];
                echo $gf->utf8("
                <tr class='bg-warning'>
                    <td>	$id_gto</td>
                    <td>	$tipo</td>
                    <td colspan='2'>	$descripcion</td>
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