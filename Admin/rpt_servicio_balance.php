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

		 <input type='button' class='btn btn-primary' value='Generar'  onclick=\"cargaHTMLvars('showresult','$sender?flag=go','','20000','unival_regus')\" />");
		echo $gf->utf8("<hr /><div id='showresult'></div></div></div>");
	}else{
		$desde=$_POST["fechafromencuesta"];
		$hasta=$_POST["fechatoencuesta"];


        $rsSer=$gf->dataSet("SELECT ID_SERVICIO FROM servicio WHERE ID_SITIO='{$_SESSION["restbus"]}' ORDER BY FECHA");
        $ns=1;
        $arids=array();
        foreach($rsSer as $rwS){
            $ids=$rwS["ID_SERVICIO"];
            $arids[$ids]=$ns;
            $ns++;
        }

        $rsFp=$gf->dataSet("SELECT ID_FP, NOMBRE, CAJA FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}' ORDER BY CAJA DESC");
        $arfp=array();
        $asfp0="";
        $asfp="";
        $asfp2="";
        if(count($rsFp)){
            foreach($rsFp as $rwFp){
                $id_fp=$rwFp["ID_FP"];
                $nm_fp=$rwFp["NOMBRE"];
                $caja=$rwFp["CAJA"];
                $arfp[$id_fp]=array("nm"=>$nm_fp,"ca"=>$caja);
                $asfp0.="C.VAL_$id_fp,";
                $asfp.="SUM(IF(C.ID_FP='$id_fp',C.VALOR,0)) AS VAL_$id_fp,";
                $asfp2.="SUM(IF(P.ID_FP='$id_fp' AND P.ID_SERVICIO=SE.ID_SERVICIO,P.PAGO+P.PROPINA,0)) AS VEN_$id_fp,";
            }
            $asfp=substr($asfp,0,-1);
            $asfp2=substr($asfp2,0,-1);
            $asfp0=substr($asfp0,0,-1);
        }

        $rsAbonos=$gf->dataSet("SELECT S.ID_SERVICIO, A.ID_PEDIDO, A.VALOR, A.ID_FP, P.ID_FP AS FP2 FROM pedidos_abonos A JOIN pedidos P ON P.ID_PEDIDO=A.ID_PEDIDO JOIN servicio S ON S.ID_SERVICIO=P.ID_SERVICIO WHERE S.FECHA BETWEEN '$desde' AND '$hasta' AND S.ID_SITIO='".$_SESSION["restbus"]."' AND A.ID_FP<>P.ID_FP ORDER BY P.ID_PEDIDO");
        $abos=array();
        if(count($rsAbonos)>0){
            foreach($rsAbonos as $rwAbonos){
                $id_servicio=$rwAbonos["ID_SERVICIO"];
                $id_pedido=$rwAbonos["ID_PEDIDO"];
                $vl_abono=$rwAbonos["VALOR"];
                $id_forma=$rwAbonos["ID_FP"];
                $id_forma2=$rwAbonos["FP2"];
                if(!isset($abos[$id_servicio][$id_forma])) $abos[$id_servicio][$id_forma]=0;
                if(!isset($abos[$id_servicio][$id_forma2])) $abos[$id_servicio][$id_forma2]=0;
                $abos[$id_servicio][$id_forma]+=$vl_abono;
                $abos[$id_servicio][$id_forma2]-=$vl_abono;
            }
        }
  
		if($desde=="" || $hasta==""){
			echo "Selecciona un rango de fechas";
			exit;
		}
		$resultInt = $gf->dataSet("SELECT SE.ID_SERVICIO, SE.FECHA, $asfp0, $asfp2, SE.BASE_CAJA, SUM(P.IMPUESTO) AS IMPUESTOS, SUM(P.PROPINA) AS PROPINAS FROM pedidos AS P JOIN servicio SE ON SE.ID_SERVICIO=P.ID_SERVICIO AND P.CIERRE<>'0000-00-00 00:00:00' LEFT JOIN (SELECT ID_SERVICIO, $asfp FROM servicio_cuadre C GROUP BY C.ID_SERVICIO)C ON C.ID_SERVICIO=SE.ID_SERVICIO WHERE SE.ID_SITIO='".$_SESSION["restbus"]."' AND SE.FECHA BETWEEN '$desde' AND '$hasta' GROUP BY SE.ID_SERVICIO ORDER BY SE.FECHA");
        $artot=array();
		if(count($resultInt)>0){
			echo $gf->utf8("
			<div class='box box-danger'>
				<div class='box-header'>BALANCE DESDE $desde HASTA $hasta </div>
				<div class='box-body' style='overflow:auto;'>
			<table class='table table-striped datatables' id='abcdefgache'>
				<thead>
					<tr class='bg-primary'>
						<td>	ID</td>
						<td>	FECHA</td>
						<td>	BASE CAJA</td>
                        ");
                        foreach($arfp as $id_fpp=>$infofp){
                            $nm=$infofp["nm"];
                            $ca=$infofp["ca"];
                            if($ca==1) $nm=$nm." + BASE";
                            echo $gf->utf8("<td>$nm</td><td>$nm (conteo)</td>");
                            $artot[$id_fpp]["c"]=0;
                            $artot[$id_fpp]["v"]=0;
                        }
                        echo $gf->utf8("
                        <td>	TOTAL INGRESO</td>
						<td>	TOTAL CONTEO</td>
						<td>	DESCUADRE</td>
                        <td>	PROPINAS</td>
                        <td>	VENTAS</td>
						<td>	BASE</td>
						<td>	IMPUESTOS</td>
						
					</tr>
				</thead>
				<tbody>
						
			");
			$total=0;
			$totalv=0;
			$totald=0;
			$total_dcto=0;
            $rows="";
            $total_1=0;
            $total_2=0;
            $total_3=0;
            $total_4=0;
            $total_5=0;
            $total_6=0;
            $total_7=0;
            $total_8=0;
			foreach($resultInt as $rowInt){
				$id_servicio=$rowInt["ID_SERVICIO"];
				$servicio=$rowInt["FECHA"];
				$base_caja=$rowInt["BASE_CAJA"];
				$impuestos=$rowInt["IMPUESTOS"];
				$propinas=$rowInt["PROPINAS"];
			
                $idser=$arids[$id_servicio];
				$rows.="
				<tr>
					<td>	$idser</td>
					<td>	$servicio</td>
                    <td>	".number_format($base_caja,0)."</td>";
                    $tove=0;
                    $toco=0;
                    foreach($arfp as $id_fpp=>$infofp){
                        $va_cu=$rowInt["VAL_$id_fpp"];
                        $va_ve=$rowInt["VEN_$id_fpp"];

                        if(isset($abos[$id_servicio][$id_fpp])){
                            $va_ve+=$abos[$id_servicio][$id_fpp];
                        }
                        if($infofp["ca"]==1){
                            $va_ve+=$base_caja;  
                        }
                        $tove+=$va_ve;
                        $toco+=$va_cu;
                        
                        $rows.="<td>".number_format($va_ve,0)."</td><td>".number_format($va_cu,0)."</td>";
                        $artot[$id_fpp]["v"]+=$va_ve;
                        $artot[$id_fpp]["c"]+=$va_cu;
                    }
                    $descuadre=$toco-$tove;
                    $total=$tove-$propinas;
                    $basse=$total-$impuestos;
                    $rows.="<td>	".number_format($tove,0)."</td>
                    <td>	".number_format($toco,0)."</td>
                    <td>	".number_format($descuadre,0)."</td>
                    <td>	".number_format($propinas,0)."</td>
					<td>	".number_format($total,0)."</td>
					<td>	".number_format($basse,0)."</td>
                    <td>	".number_format($impuestos,0)."</td>
                    
					
                </tr>";
                    $total_1+=$base_caja;
                    $total_2+=$tove;
                    $total_3+=$toco;
                    $total_4+=$descuadre;
                    $total_5+=$propinas;
                    $total_6+=$total;
                    $total_7+=$basse;
                    $total_8+=$impuestos;
            }
            $totas="<tr class='bg-warning' style='font-weight:bold;'>
            <td colspan=2>TOTALES</td>
            <td>	".number_format($total_1)."</td>
            ";
            foreach($arfp as $id_fpp=>$infofp){
                $nm=$infofp["nm"];
                $ca=$infofp["ca"];
                if($ca==1) $nm=$nm." + BASE";
                $totas.="<td>".number_format($artot[$id_fpp]["v"])."</td><td>".number_format($artot[$id_fpp]["c"])."</td>";
            }
            $totas.="
            <td>	".number_format($total_2)."</td>
            <td>	".number_format($total_3)."</td>
            <td>	".number_format($total_4)."</td>
            <td>	".number_format($total_5)."</td>
            <td>	".number_format($total_6)."</td>
            <td>	".number_format($total_7)."</td>
            <td>	".number_format($total_8)."</td>
            
            </tr>";

			echo $gf->utf8("
			</tr>".$totas.$rows.$totas);
		echo $gf->utf8("</tbody>
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