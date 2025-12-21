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
			$condfp="P.ID_FP='$id_fp' OR P.ID_PEDIDO IN(SELECT ID_PEDIDO FROM pedidos_abonos WHERE ID_FP='$id_fp')";
			$rsFp=$gf->dataSet("SELECT ID_FP, NOMBRE FROM formas_pago WHERE ID_FP='$id_fp'");
			$nm_fp=$rsFp[0]["NOMBRE"];
			$titl="FILTRADO POR FORMA DE PAGO $nm_fp";
		}
		$arformas=array();
		$rsFp=$gf->dataSet("SELECT ID_FP, NOMBRE FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}' ORDER BY ID_FP");
		$forma_filtro="";
		if(count($rsFp)>0){
			foreach($rsFp as $rwFp){
				$idfp=$rwFp["ID_FP"];
				$nmfp=$rwFp["NOMBRE"];
				$arformas[$idfp]=$nmfp;
			}
			if($id_fp>0){
				$forma_filtro=$arformas[$id_fp];
			}
		}
		
		if($desde=="" || $hasta==""){
			echo "Selecciona un rango de fechas";
			exit;
		}


		$rsAbonos=$gf->dataSet("SELECT A.ID_PEDIDO,A.VALOR, A.ID_FP FROM pedidos_abonos A JOIN pedidos P ON P.ID_PEDIDO=A.ID_PEDIDO JOIN servicio S ON S.ID_SERVICIO=P.ID_SERVICIO WHERE S.FECHA BETWEEN '$desde' AND '$hasta' AND S.ID_SITIO='".$_SESSION["restbus"]."'  ORDER BY P.ID_PEDIDO");
		
		$abos=array();
		if(count($rsAbonos)>0){
			foreach($rsAbonos as $rwAbonos){
				$id_pedido=$rwAbonos["ID_PEDIDO"];
				$vl_abono=$rwAbonos["VALOR"];
				$id_forma=$rwAbonos["ID_FP"];
				$abos[$id_pedido][$id_forma]=$vl_abono;
			}
		}


        $rsIm=$gf->dataSet("SELECT ID_IMPUESTO, NOMBRE, PORCENTAJE FROM impuestos WHERE ID_SITIO='{$_SESSION["restbus"]}'");
        $campi="";
        if(count($rsIm)>0){
            foreach($rsIm as $rwIm){
                $id_imp=$rwIm["ID_IMPUESTO"];
                $nm_imp=$rwIm["NOMBRE"];
                $percen=1+($rwIm["PORCENTAJE"]/100);
                $campi.="SUM(IF(PL.ID_IMPUESTO='$id_imp',(SP.PRECIO-(SP.PRECIO/$percen))*SP.CANTIDAD,0)) AS IMP_$id_imp,";
            }
            $campi=substr($campi,0,-1);
        }else{
            $campi="'0' AS DUMMY";
        }
        $impuesto=0;
        $resultInt = $gf->dataSet("SELECT M.ID_MESA, SE.FECHA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, COUNT(S.ID_SILLA) AS SILLAS, P.APERTURA, P.CIERRE, SUM(IF(M.TIPO<>'D',PL.PRECIO*SP.CANTIDAD,PL.PRECIO_DOM*SP.CANTIDAD)) AS PAGO, P.DCTO, $campi FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CIERRE<>'0000-00-00 00:00:00') JOIN servicio SE ON SE.ID_SERVICIO=P.ID_SERVICIO JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO JOIN sillas_platos SP ON SP.ID_SILLA=S.ID_SILLA JOIN platos PL ON PL.ID_PLATO=SP.ID_PLATO WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND SE.FECHA BETWEEN '$desde' AND '$hasta' AND $condfp GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");

		$reporte="";
		if(count($resultInt)>0){
			if(!isset($_GET["xls"])){
				echo $gf->utf8("<div class='box box-danger'>
				<div class='box-header'>REPORTE VENTAS DESDE $desde HASTA $hasta <b>$titl</b>
				
				<form class='form-inline pull-right' style='margin:0px;padding:0px !important;' method='post' target='_blank' action='Admin/rpt_ventas_rango.php?flag=xls&xls=1'><input type='hidden' name='fechafromencuesta' value='$desde' /><input type='hidden' name='fechatoencuesta' value='$hasta' /><input type='hidden' name='id_fp' value='$id_fp' /><button type='submit' class='btn btn-minier btn-primary pull-right'><i class='fa fa-file-excel-o'></i> Exportar XLS</button></form>
				
				</div>
				<div class='box-body'>");
			}else{
				$reporte.="<p>REPORTE VENTAS DESDE $desde HASTA $hasta <b>$titl</b></p>";
			}
			$reporte.="
			<table class='table table-bordered table-stripped' border='1'>
				<thead>
					<tr class='bg-primary'>
						<td>	SERVICIO</td>
						<td>	APERTURA</td>
						<td>	FORMA DE PAGO</td>
						<td>	PAGO</td>
                        ";
                    if(count($rsIm)>0){
                        foreach($rsIm as $rwIm){
                            $id_imp=$rwIm["ID_IMPUESTO"];
                            $nm_imp=$rwIm["NOMBRE"];
                            $reporte.="<td>".$nm_imp."</td>";
                            $imptots[$id_imp]=0;
                        }
                    }else{
                        $reporte.="<td>IMPUESTO</td>";
                    }
            $reporte.="
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
				$apertura=$rowInt["APERTURA"];
				$cierre=$rowInt["CIERRE"];
				$pago=$rowInt["PAGO"];
				$dcto=$rowInt["DCTO"];
				$fp=$rowInt["ID_FP"];
				$forma=$arformas[$rowInt["ID_FP"]];
				if($fp!=$id_fp && $id_fp>0) $pago=0;
				if(isset($abos[$id_pedido])){
					foreach($abos[$id_pedido] as $fpa=>$valor){
						if($fp!=$id_fp && $fpa==$id_fp){
							$pago+=$valor;
							$forma="MIXTA ($forma_filtro)";
						}elseif($fp==$id_fp && $fpa!=$id_fp){
							$pago-=$valor;
							$forma="MIXTA ($forma_filtro)";
						}
					}
				}

				$total+=$pago;
				$total_dcto+=$dcto;
				if($pago>0){
					if(!isset($_GET["xls"])){
						$rows.="
						<tr>
							<td>	$servicio</td>
							<td>	$apertura</td>
							<td>	$forma</td>
							<td>".number_format($pago,0)."</td>
                            ";
                            if(count($rsIm)>0){
                                foreach($rsIm as $rwIm){
                                    $id_imp=$rwIm["ID_IMPUESTO"];
                                    $impuesto=$rowInt["IMP_$id_imp"];
                                    $imptots[$id_imp]+=$impuesto;
                                    $reporte.="<td>".number_format($impuesto,0)."</td>";
                                }
                            }else{
                                $rows.="<td>0</td>";
                            }

						$rows.="</tr>";
					}else{
						$rows.="
						<tr>
							<td>	$servicio</td>
							<td>	$apertura</td>
							<td>	$forma</td>
							<td>	$pago</td>
                            ";
                            if(count($rsIm)>0){
                                foreach($rsIm as $rwIm){
                                    $id_imp=$rwIm["ID_IMPUESTO"];
                                    $impuesto=$rowInt["IMP_$id_imp"];
                                    $imptots[$id_imp]+=$impuesto;
                                    $reporte.="<td>".$impuesto."</td>";
                                }
                            }else{
                                $rows.="<td>0</td>";
                            }
                            $rows.="
						</tr>";
					}
				}
			}
			if(!isset($_GET["xls"])){
				echo $gf->utf8($reporte."<tr class='bg-danger'>
                <td colspan='3'>TOTALES</td><td><big>".number_format($total,0)."</big></td>
                ");
                if(count($rsIm)>0){
                    foreach($rsIm as $rwIm){
                        $id_imp=$rwIm["ID_IMPUESTO"];
                        $impuesto=$imptots[$id_imp];
                        echo $gf->utf8("<td>".number_format($impuesto,0)."</td>");
                    }
                }else{
                    echo $gf->utf8("<td>0</td>");
                }
                echo $gf->utf8("
				</tr>".$rows."</tbody>
				<tfoot>
						<tr class='bg-danger'>
                        <td colspan='3'>TOTALES</td><td>".number_format($total,0)."</td>
                        ");
                        if(count($rsIm)>0){
                            foreach($rsIm as $rwIm){
                                $id_imp=$rwIm["ID_IMPUESTO"];
                                $impuesto=$imptots[$id_imp];
                                echo $gf->utf8("<td>".number_format($impuesto,0)."</td>");
                            }
                        }else{
                            echo $gf->utf8("<td>0</td>");
                        }
                        echo $gf->utf8("
                        </tr>
				</tfoot>
				</table>
				</div>
				</div>
				");
			}else{
				$reporte.=$rows."</tbody></table>";
				header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
				header("Content-Disposition: attachment;filename=\"reporteVentas.xls\"");
				header("Cache-Control: max-age=0");
				echo $reporte;
				exit;
			}

		}else{
			echo $gf->utf8("No hay resultados");
		}
		
	}
}else{
	echo "No has iniciado sesion!";
}
?>