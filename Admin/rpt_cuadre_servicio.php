<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="T" || ($_SESSION["restprofile"]=="J" && $_SESSION["restcajerofiscal"]==1))){
	
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
	$actividad=$gf->cleanVar($_GET["flag"]);
	$titulo="CUADRE POR SERVICIOS";
	$sender=$_SERVER['PHP_SELF'];
	if(isset($_GET["filterKey"])){
		$filterKey=$gf->cleanVar($_GET["filterKey"]);
		$filterVal=$gf->cleanVar($_GET["filterVal"]);
	}else{
		$filterKey="ID_SITIO";
		$filterVal=$_SESSION["restbus"];
	}

	$rigu=array(1,1,1,1,1);

	
	if($actividad=="start"){
		$lCom=$gf->dataSet("SELECT S.ID_SERVICIO, S.FECHA, S.ESTADO, COUNT(P.ID_PLATO) AS PLATOS FROM servicio S LEFT JOIN servicio_oferta P ON S.ID_SERVICIO=P.ID_SERVICIO WHERE S.ID_SITIO=:sitio AND S.ESTADO>1 GROUP BY S.FECHA ORDER BY S.FECHA DESC",array(":sitio"=>$_SESSION["restbus"]));

		echo $gf->utf8("
		<div class='box box-warning'><div class='box-header'>SERVICIOS</div>
		<div class='box-body'>
		");
		echo $gf->utf8("
		<div class='row'>
			<div class='col-md-4' id='level2'>
			<div class='box box-danger'>
			<div class='box-header'>SERVICIOS</div>
			<div class='box-body'>
			<ul class='list-group' style='max-height:250px;overflow:auto;'>");
			if(count($lCom)>0){
				foreach($lCom as $rwServ){
					$id_service=$rwServ["ID_SERVICIO"];
					$fecha=$rwServ["FECHA"];
					$estado=$rwServ["ESTADO"];
					$platos=$rwServ["PLATOS"];
					
                    $classe="danger";
                    $ico="fa fa-lock";
                    $titl="Servicio Cerrado y Cuadrado";
                    $bandera="cuadre";
                    $btns="";
					
					echo $gf->utf8("<li id='theserv_$id_service' title='$titl' onclick=\"cargaHTMLvars('level3','$sender?flag=$bandera&key=$id_service&st=$estado')\" class='list-group-item list-group-item-$classe link-cnv'>$fecha <i class='$ico pull-right'></i> $btns</li>");
				}
			}
			echo $gf->utf8("
			</ul>
			</div>
			</div>
			</div>
			<div class='col-md-8' id='level3'></div>
		</div>
		</div>
		</div>
		
		");
	
		
	}elseif($actividad=="cuadre"){
		$id_servicio=$gf->cleanVar($_GET["key"]);
		$id_serv=$gf->cleanVar($_GET["key"]);

		if($id_servicio==0){
			echo "Selecciona un servicio";
			exit;
		}
		$rnd=(isset($_GET["rnd"])) ? $_GET["rnd"] : "0";
		$rsServ=$gf->dataSet("SELECT FECHA, ESTADO, BASE_CAJA FROM servicio WHERE ID_SERVICIO=:servicio",array(":servicio"=>$id_servicio));
		
		if(count($rsServ)>0){
			$rwServ=$rsServ[0];
			$fecha=$rwServ["FECHA"];
			$estado=$rwServ["ESTADO"];
			$base_caja=$rwServ["BASE_CAJA"];
			$totGasto=0;
			echo $gf->utf8("
			<div class='box box-warning'><div class='box-header'>CUADRE DE CAJA</div>
			<div class='box-body'>
			
			");

				$resultInt = $gf->dataSet("SELECT F.ID_FACTURA, F.PREFIJO, F.CONSECUTIVO, M.ID_MESA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, P.APERTURA, P.CIERRE, P.PAGO, P.DCTO, FP.NOMBRE AS FORMA, FP.CAJA, FP.ID_FP FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA) JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER LEFT JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO LEFT JOIN formas_pago FP ON FP.ID_FP=P.ID_FP WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='$id_servicio' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
				echo $gf->utf8("
					<table class='table table-striped'>
						<thead>
							<tr>
								<td>	TIPO</td>
								<td>	MESA</td>
								<td>	TENDER</td>
								<td>	ID PEDIDO</td>
								<td>	APERTURA</td>
								<td>	CIERRE</td>
								<td>	PAGO</td>
								<td>	MEDIO</td>
							</tr>
						</thead>
						<tbody style='max-height:200px;overflow:auto;'>
								
					");			
				if(count($resultInt)>0){
					
					$total=0;
					$total_otros=0;
					$total_dcto=0;
					$total_ventas=0;
					$total_cotizado=0;
					foreach($resultInt as $rowInt){
						$id_mesa=$rowInt["ID_MESA"];
						$nombre=$rowInt["NOMBRE"];
						$tender=$rowInt["TENDER"];
						$id_pedido=$rowInt["ID_PEDIDO"];
						$id_fp=$rowInt["ID_FP"];
						$apertura=$rowInt["APERTURA"];
						$cierre=$rowInt["CIERRE"];
						$pago=$rowInt["PAGO"];
						$dcto=$rowInt["DCTO"];
						$fact=$rowInt["ID_FACTURA"];
						$forma_pago=$rowInt["FORMA"];
						$escaja=$rowInt["CAJA"];
						$fact_verb="C";
						
						if($escaja==1){
							$total+=$pago;
						}else{
							$total_otros+=$pago;
						}
						$total_dcto+=$dcto;
						$sumar=$pago;
						if($fact!=""){
							$fact_verb="V";
							$total_ventas += $sumar;
						}else{
							$total_cotizado += $sumar;
						} 
						echo $gf->utf8("
						<tr class='bg-success'>
							<td>	$fact_verb</td>
							<td>	$nombre</td>
							<td>	$tender</td>
							<td>	$id_pedido</td>
							<td>	$apertura</td>
							<td>	$cierre</td>
							<td align='right'>".number_format($sumar,0)."</td>
							<td>$forma_pago</td>
						</tr>");
					}

				}
				echo $gf->utf8("
						<tr class='bg-danger'>
							<td colspan='8'>	GASTOS </td>
						</tr>");
				$gtos=$gf->dataSet("SELECT T.NOMBRE, G.ID_GASTO, G.DESCRIPCION, G.FECHA, G.VALOR, G.ID_FP FROM gastos G JOIN gastos_tipos T ON T.ID_TIPO=G.ID_TIPO WHERE G.ID_SERVICIO=:servicio",array(":servicio"=>$id_servicio));
				$fpgasto=array();
				if(count($gtos)>0){
					foreach($gtos as $gto){
						$id_gto=$gto["ID_GASTO"];
						$tipo=$gto["NOMBRE"];
						$descripcion=$gto["DESCRIPCION"];
						$fecha=$gto["FECHA"];
						$valor=$gto["VALOR"];
						$idfpa=$gto["ID_FP"];
						if(!isset($fpgasto[$idfpa])){
							$fpgasto[$idfpa]=$valor;
						}else{
							$fpgasto[$idfpa]+=$valor;
						}
						echo $gf->utf8("
						<tr class='bg-warning'>
							<td>GASTO</td>
							<td>	$id_gto</td>
							<td>	$tipo</td>
							<td colspan='3'>	$descripcion</td>
							<td>	$fecha</td>
							<td align='right'>".number_format($valor,0)."</td>
						</tr>");
						$totGasto+=$valor;

					}
				}

				$compras=$gf->dataSet("SELECT C.ID_COMPRA, C.FECHA, C.OBSERVACION, C.FACTURA, P.NOMBRE AS PROVEEDOR, SUM(I.PRECIO*I.CANTIDAD) AS VALOR, C.ID_FP FROM inventario_compras C JOIN proveedores P ON C.ID_PROVEEDOR=P.ID_PROVEEDOR JOIN inventario I ON I.ID_COMPRA=C.ID_COMPRA WHERE C.ID_SERVICIO=:servicio GROUP BY C.ID_COMPRA ORDER BY C.ID_COMPRA",array(":servicio"=>$id_servicio));
				
				if(count($compras)>0){
					foreach($compras as $compra){
						$id_compra=$compra["FACTURA"];
						$proveedor=$compra["PROVEEDOR"];
						$descripcion=$compra["OBSERVACION"];
						$fecha=$compra["FECHA"];
						$valor=$compra["VALOR"];
						$idfpa=$compra["ID_FP"];
						if(!isset($fpgasto[$idfpa])){
							$fpgasto[$idfpa]=$valor;
						}else{
							$fpgasto[$idfpa]+=$valor;
						}
						echo $gf->utf8("
						<tr class='bg-warning'>
							<td>INVENT.</td>
							<td>	$id_compra</td>
							<td>	$proveedor</td>
							<td colspan='3'>	$descripcion</td>
							<td>	$fecha</td>
							<td align='right'>".number_format($valor,0)."</td>
						</tr>");
						$totGasto+=$valor;
					}
				}
				
				$fppropins=array();
				if($_SESSION["restpropina"]>0){
					$resultPropins = $gf->dataSet("SELECT CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS NOMBRE, SUM(P.PROPINA) AS VALOR FROM usuarios AS U JOIN pedidos AS P ON (U.ID_USUARIO=P.ID_TENDER) WHERE U.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='$id_servicio' GROUP BY U.ID_USUARIO ORDER BY U.NOMBRES,U.APELLIDOS");
					$tabpro="<table class='table table-bordered'><tr><td class='bg-danger' colspan='2'>TABLA DE PROPINAS</td>";
					$nus=0;
					$tot_propina=0;
					if(count($resultPropins)>0){
						foreach($resultPropins as $rwI){
							$nombretender=$rwI["NOMBRE"];
							
							$valorp=$rwI["VALOR"];
							$tot_propina+=$valorp;
							$tabpro.="<tr><td> $nombretender</td><td>".number_format($valorp,0)."</td></tr>";
							$nus++;
						}
					}
					$promedio=$tot_propina/$nus;
					$tabpro.="<tr class='bg-success'><td>TOTAL</td><td>".number_format($tot_propina,0)."</td></tr>";
					$tabpro.="<tr class='bg-success'><td> PROMEDIO:</td><td> ".number_format($promedio,0)."</td></tr>";
					$tabpro.="</table><hr />";


					$resultPropins = $gf->dataSet("SELECT P.ID_FP, SUM(P.PROPINA) AS VALOR FROM pedidos AS P WHERE P.ID_SERVICIO='$id_servicio' GROUP BY P.ID_FP ORDER BY P.ID_FP");

					if(count($resultPropins)>0){
						foreach($resultPropins as $rwI){
							$idfpp=$rwI["ID_FP"];
							$valorp=$rwI["VALOR"];
							$fppropins[$idfpp]=$valorp;
						}
					}


				}else{
					$tot_propina=0;
					$tabpro="";
				}


				$totalreal=$total+$total_otros+$base_caja-$totGasto;
				$total_vents=$total+$total_otros;
				$total_caja=$total+$base_caja-$totGasto;
				echo $gf->utf8("</tbody>
				<tfoot>
						<tr>
						<td colspan='7' align='right'>TOTAL VENTAS</td><td align='right'>".number_format($total_ventas,0)."
						
						</td>
						</tr>
						<tr>
						<td colspan='7' align='right'>VENTAS SIN FACTURA</td><td align='right'>".number_format($total_cotizado,0)."</td>
						</tr>
						<tr>
						<tr>
						<td colspan='7' align='right'><b>TOTAL VENTAS</b></td><td align='right'><b>".number_format($total_cotizado+$total_ventas,0)."</b></td>
						</tr>
						<tr>
						<td colspan='7' align='right'>BASE CAJA</td><td align='right'>".number_format($base_caja,0)."</td>
						</tr>
						<tr>
						<td colspan='7' align='right'>GASTOS CON RECURSOS DEL SERVICIO = (GASTOS + COMPRAS DE INVENTARIO)</td><td align='right'>".number_format($totGasto,0)."</td>
						
				
						<tr>
						<td colspan='7' align='right'></td><td align='right'><input type='hidden' class='form-control unival_cuadre' name='total_real' value='$total_caja' />
		
						</td>
						</tr>
				</tfoot>
				</table>
				");
				



				
				if($_SESSION["restanticipos"]==1){
                    $rsAbonos=$gf->dataSet("SELECT P.ID_PEDIDO, A.ID_ABONO, A.FECHA, A.VALOR, A.OBSERVACION, U.NOMBRES AS USUARIO, A.ID_FP, P.ID_FP AS FP2, P.CIERRE<>'0000-00-00 00:00:00' AS CERRADO, FP.CAJA, A.ID_FP<>P.ID_FP AS SUMA FROM pedidos_abonos A JOIN usuarios U ON U.ID_USUARIO=A.ID_USUARIO JOIN pedidos P ON P.ID_PEDIDO=A.ID_PEDIDO JOIN formas_pago FP ON A.ID_FP=FP.ID_FP WHERE P.ID_SERVICIO='$id_servicio' ORDER BY P.ID_PEDIDO");
                    $tot_abonos=0;
                    $abos="";
                    $ab_forma=array();
                    $ab_resta=array();
                    $ab_suma=array();
                    if(count($rsAbonos)>0){
                        $tot_abonos=0;
                        foreach($rsAbonos as $rwAbonos){
                            $id_pedido=$rwAbonos["ID_PEDIDO"];
                            $id_abono=$rwAbonos["ID_ABONO"];
                            $fc_abono=$rwAbonos["FECHA"];
                            $vl_abono=$rwAbonos["VALOR"];
                            $ob_abono=$rwAbonos["OBSERVACION"];
                            $us_abono=$rwAbonos["USUARIO"];
                            $id_forma=$rwAbonos["ID_FP"];
                            $fp_resta=$rwAbonos["FP2"];
                            $cerrado=$rwAbonos["CERRADO"];
                            $caja=$rwAbonos["CAJA"];
                            $suma=$rwAbonos["SUMA"];
                            
                            if($cerrado==0){
								if(isset($ab_forma[$id_forma])){
									$ab_forma[$id_forma]+=$vl_abono;
								}else{
									$ab_forma[$id_forma]=$vl_abono;
								}
								$abos.="<tr><td>$id_pedido</td><td>$fc_abono </td><td>($us_abono): $ob_abono</td></td><td align='right'>$".number_format($vl_abono,0)."</td></tr>";
								$tot_abonos+=$vl_abono;
							}else{
								if($suma==1){
									if(isset($ab_resta[$fp_resta])){
										$ab_resta[$fp_resta]+=$vl_abono;
									}else{
										$ab_resta[$fp_resta]=$vl_abono;
									}
									if(isset($ab_suma[$id_forma])){
										$ab_suma[$id_forma]+=$vl_abono;
									}else{
										$ab_suma[$id_forma]=$vl_abono;
									}
									

								}
								
							}
                        }
                    }
                
                }



				
				$rsCabonos=$gf->dataSet("SELECT ID_FPP, SUM(VALOR) AS VALOR FROM cartera_ingresos WHERE ID_FPP IN(SELECT ID_FP FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}') AND ID_SERVICIO='$id_servicio' GROUP BY ID_FPP");
				$arcabos=array();
				$ingre_arcabos=0;
				if(count($rsCabonos)>0){
					foreach($rsCabonos as $rwCabonos){
						$idfpo=$rwCabonos["ID_FPP"];
						$valfpo=$rwCabonos["VALOR"];
						$arcabos[$idfpo]=$valfpo;
						$ingre_arcabos+=$valfpo;
					} 
				}

				 
				$rsAjustes=$gf->dataSet("SELECT ID_FP, SUM(VALOR) AS VALOR FROM ajustes_caja WHERE ID_FP IN(SELECT ID_FP FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}') AND ID_SERVICIO='$id_servicio' GROUP BY ID_FP");
				$arajuste=array();
				$ajustes=0;
				if(count($rsAjustes)>0){
					foreach($rsAjustes as $rwAjuste){
						$idfpo=$rwAjuste["ID_FP"];
						$valfpo=$rwAjuste["VALOR"];
						$arajuste[$idfpo]=$valfpo;
						$ajustes+=$valfpo;
					} 
				}
		
		


				$gf->dataIn("INSERT IGNORE INTO servicio_cuadre (ID_SERVICIO,ID_FP,VALOR) SELECT '$id_servicio' AS ID_SERVICIO, ID_FP, '0' AS VALOR FROM formas_pago WHERE ID_SITIO='".$_SESSION["restbus"]."' ORDER BY ID_FP");

				$resultInt = $gf->dataSet("SELECT FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, SUM(P.PAGO) AS VALOR, CU.ID_REL, CU.VALOR AS VALCUADRE FROM formas_pago AS FP JOIN pedidos AS P ON (FP.ID_FP=P.ID_FP AND P.ID_SERVICIO='$id_servicio') JOIN servicio_cuadre CU ON CU.ID_FP=FP.ID_FP AND CU.ID_SERVICIO='$id_servicio' WHERE FP.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY FP.ID_FP ORDER BY FP.NOMBRE");
				echo $gf->utf8("
				<table class='table table-bordered'>
					<tr class='bg-danger'>
						<td>FORMA DE PAGO</td>
						<td>+BASE</td>
						<td>+VENTAS</td>
						<td>+REC. CARTERA</td>
						<td>+AJUSTES</td>
						<td>+PROPINAS</td>
						<td>-GASTOS</td>
						<td>=SUGERIDO</td>
						<td>CONTEO</td>
					</tr>");
				$totfp=0;
				$tot_caja=0;
				$tot_otros=0;
				$tot_v=0;
				$tot_g=0;
				$tot_p=0;
				$tot_car=0;
				$tot_aju=0;
				
				if(count($resultInt)>0){
					$restar=0;
					foreach($resultInt as $rwI){
						$fp=$rwI["ID_FP"];
						$forma=$rwI["FORMAPAGO"];
						$icforma=$rwI["ICONO"];
						$valor=$rwI["VALOR"];
						$id_rel=$rwI["ID_REL"];
						$valorcu=$rwI["VALCUADRE"];
						$caja=$rwI["CAJA"];
						$abis=0;
						if(isset($ab_forma[$fp])){
							$abis=$ab_forma[$fp];
						}
						
						
						$valor+=$abis;
						if(isset($ab_suma[$fp])){
							$valor+=$ab_suma[$fp];
						}
						if(isset($ab_resta[$fp])){
							$valor-=$ab_resta[$fp];
						}
						$car=0;
						if(isset($arcabos[$fp])){
							$car=$arcabos[$fp];
							$tot_car+=$car;
						}
						$aju=0;
						if(isset($arajuste[$fp])){
							$aju=$arcabos[$fp];
							$tot_aju+=$aju;
						}
						$tvent=$valor;
						$tot_v+=$tvent;
						$totfp+=$valor;
						$addon="<small>(ventas-gastos con $forma)</small>";
						if($caja==1) $valor+=$base_caja+$car+$aju;
						if($caja==1) $addon="<small>(base+ventas+propinas-gastos con $forma)</small>";;
						if(isset($fpgasto[$fp])){
							$valor-=$fpgasto[$fp];
							$tgast=$fpgasto[$fp];
						}else{
							$tgast=0;
						}
						$addon="";
						$tot_g+=$tgast;
						if(isset($fppropins[$fp])){
							$valor+=$fppropins[$fp];
							$tprop=$fppropins[$fp];
						}else{
							$tprop=0;
						}
						$tot_p+=$tprop;

						if($valor>0){
							if($caja==1){
								$tot_caja += $valor;
							}else{
								$tot_otros += $valor;
							}
							$ba_caa=($caja==1) ? $base_caja : 0;
							echo $gf->utf8("<tr><td><i class='fa $icforma'></i> $forma<br />$addon</td><td>".number_format($ba_caa,0)."</td>
							<td>".number_format($tvent,0)."</td>
							<td>".number_format($car,0)."</td>
							<td>".number_format($aju,0)."</td>
							<td>".number_format($tprop,0)."</td><td>".number_format($tgast,0)."</td><td>".number_format($valor,0)."</td><td>".number_format($valorcu,0)."</td></tr>");
						}
						
					}
					
					$tot_caja-=$restar;
					$tot_otros+=$restar;
					
				}
				echo $gf->utf8("<tr><td>TOTAL</td><td><b>".number_format($base_caja,0)."</b></td>
				<td><b>".number_format($tot_v,0)."</b></td>
				<td><b>".number_format($tot_car,0)."</b></td>
				<td><b>".number_format($tot_aju,0)."</b></td>
				<td><b>".number_format($tot_p,0)."</b></td><td><b>".number_format($tot_g,0)."</b></td><td><span id='thetot_met'></span></td></tr>");
				echo $gf->utf8("</table><hr />
				
				<hr />
				$tabpro
				");

				
			echo $gf->utf8("
			</div>
			</div>
			");
		}else{
			echo "No se encuentra el servicio";
		}

	}elseif($actividad=="cuadre_x_boxa"){

        $rsUser=$gf->dataSet("SELECT ID_USUARIO, CONCAT(NOMBRES,' ',APELLIDOS) AS CAJERO FROM usuarios WHERE (PERFIL='A' OR PERFIL='J') AND ID_SITIO='{$_SESSION["restbus"]}' ORDER BY CAJERO");
        echo $gf->utf8("
		<div class='box box-default'>
			<div class='box-header'>REPORTE SERVICIO POR CAJERO</div>
			<div class='box-body form-inline'>
			Selecciona un cajero: 
			
            <div class='form-group'><select name='cajero' id='cajero' class='form-control unival_regiusa'><option value='0'>Selecciona...</option>");
                if(count($rsUser)>0){
                    foreach($rsUser as $rwUser){
                        $id_u=$rwUser["ID_USUARIO"];
                        $nm_u=$rwUser["CAJERO"];
                        echo $gf->utf8("<option value='$id_u'>$nm_u</option>");
                    }
                }
                
            echo $gf->utf8("
            </select>
			</div>
			
			
		 <input type='button' class='btn btn-primary' value='Generar'  onclick=\"cargaHTMLvars('showresult','$sender?flag=cuadre_x_box','','20000','unival_regiusa')\" />");
		echo $gf->utf8("<hr /><div id='showresult'></div></div></div>");

    }elseif($actividad=="cuadre_x_box"){

        $id_servicio=$_SESSION["restservice"];
        if(isset($_POST["cajero"])){
            $id_cajero=$_POST["cajero"];
        }else{
            $id_cajero=$_SESSION["restuiduser"];
        }

		$id_serv=$_SESSION["restservice"];

		if($id_servicio==0){
			echo "Selecciona un servicio";
			exit;
		}


		$rsUser=$gf->dataSet("SELECT CONCAT(NOMBRES,' ',APELLIDOS) AS CAJERO FROM usuarios WHERE ID_USUARIO='$id_cajero' ORDER BY ID_USUARIO");
        $curuser=$rsUser[0]["CAJERO"];

		$rnd=(isset($_GET["rnd"])) ? $_GET["rnd"] : "0";
		$rsServ=$gf->dataSet("SELECT FECHA, ESTADO, BASE_CAJA FROM servicio WHERE ID_SERVICIO=:servicio",array(":servicio"=>$id_servicio));
		
		if(count($rsServ)>0){
			$rwServ=$rsServ[0];
			$fecha=$rwServ["FECHA"];
			$estado=$rwServ["ESTADO"];
			$base_caja=$rwServ["BASE_CAJA"];
			$totGasto=0;
			echo $gf->utf8("
			<div class='box box-warning'><div class='box-header'>PREVISUALIZACI&Oacute;N DE CUADRE POR CAJERO: $curuser</div>
			<div class='box-body'>
			
			");

				$resultInt = $gf->dataSet("SELECT F.ID_FACTURA, F.PREFIJO, F.CONSECUTIVO, M.ID_MESA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, P.APERTURA, P.CIERRE, P.PAGO, P.DCTO, FP.NOMBRE AS FORMA, FP.CAJA, FP.ID_FP FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA) JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER LEFT JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO LEFT JOIN formas_pago FP ON FP.ID_FP=P.ID_FP WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='$id_servicio' AND P.ID_CAJERO='$id_cajero' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
				echo $gf->utf8("
					<table class='table table-striped'>
						<thead>
							<tr>
								<td>	TIPO</td>
								<td>	MESA</td>
								<td>	TENDER</td>
								<td>	ID PEDIDO</td>
								<td>	APERTURA</td>
								<td>	CIERRE</td>
								<td>	PAGO</td>
								<td>	MEDIO</td>
							</tr>
						</thead>
						<tbody style='max-height:200px;overflow:auto;'>
								
					");			
				if(count($resultInt)>0){
					
					$total=0;
					$total_otros=0;
					$total_dcto=0;
					$total_ventas=0;
					$total_cotizado=0;
					foreach($resultInt as $rowInt){
						$id_mesa=$rowInt["ID_MESA"];
						$nombre=$rowInt["NOMBRE"];
						$tender=$rowInt["TENDER"];
						$id_pedido=$rowInt["ID_PEDIDO"];
						$id_fp=$rowInt["ID_FP"];
						$apertura=$rowInt["APERTURA"];
						$cierre=$rowInt["CIERRE"];
						$pago=$rowInt["PAGO"];
						$dcto=$rowInt["DCTO"];
						$fact=$rowInt["ID_FACTURA"];
						$forma_pago=$rowInt["FORMA"];
						$escaja=$rowInt["CAJA"];
						$fact_verb="C";
						
						if($escaja==1){
							$total+=$pago;
						}else{
							$total_otros+=$pago;
						}
						$total_dcto+=$dcto;
						$sumar=$pago;
						if($fact!=""){
							$fact_verb="V";
							$total_ventas += $sumar;
						}else{
							$total_cotizado += $sumar;
						} 
						echo $gf->utf8("
						<tr class='bg-success'>
							<td>	$fact_verb</td>
							<td>	$nombre</td>
							<td>	$tender</td>
							<td>	$id_pedido</td>
							<td>	$apertura</td>
							<td>	$cierre</td>
							<td align='right'>".number_format($sumar,0)."</td>
							<td>$forma_pago</td>
						</tr>");
					}

				}
				echo $gf->utf8("
						<tr class='bg-danger'>
							<td colspan='8'>	GASTOS </td>
						</tr>");
				$gtos=$gf->dataSet("SELECT T.NOMBRE, G.ID_GASTO, G.DESCRIPCION, G.FECHA, G.VALOR, G.ID_FP FROM gastos G JOIN gastos_tipos T ON T.ID_TIPO=G.ID_TIPO WHERE G.ID_SERVICIO=:servicio AND G.ID_USUARIO='$id_cajero'",array(":servicio"=>$id_servicio));
				$fpgasto=array();
				if(count($gtos)>0){
					foreach($gtos as $gto){
						$id_gto=$gto["ID_GASTO"];
						$tipo=$gto["NOMBRE"];
						$descripcion=$gto["DESCRIPCION"];
						$fecha=$gto["FECHA"];
						$valor=$gto["VALOR"];
						$idfpa=$gto["ID_FP"];
						if(!isset($fpgasto[$idfpa])){
							$fpgasto[$idfpa]=$valor;
						}else{
							$fpgasto[$idfpa]+=$valor;
						}
						echo $gf->utf8("
						<tr class='bg-warning'>
							<td>GASTO</td>
							<td>	$id_gto</td>
							<td>	$tipo</td>
							<td colspan='3'>	$descripcion</td>
							<td>	$fecha</td>
							<td align='right'>".number_format($valor,0)."</td>
						</tr>");
						$totGasto+=$valor;

					}
				}

				$compras=$gf->dataSet("SELECT C.ID_COMPRA, C.FECHA, C.OBSERVACION, C.FACTURA, P.NOMBRE AS PROVEEDOR, SUM(I.PRECIO*I.CANTIDAD) AS VALOR, C.ID_FP FROM inventario_compras C JOIN proveedores P ON C.ID_PROVEEDOR=P.ID_PROVEEDOR JOIN inventario I ON I.ID_COMPRA=C.ID_COMPRA WHERE C.ID_SERVICIO=:servicio AND C.ID_USUARIO='$id_cajero' GROUP BY C.ID_COMPRA ORDER BY C.ID_COMPRA",array(":servicio"=>$id_servicio));
				
				if(count($compras)>0){
					foreach($compras as $compra){
						$id_compra=$compra["FACTURA"];
						$proveedor=$compra["PROVEEDOR"];
						$descripcion=$compra["OBSERVACION"];
						$fecha=$compra["FECHA"];
						$valor=$compra["VALOR"];
						$idfpa=$compra["ID_FP"];
						if(!isset($fpgasto[$idfpa])){
							$fpgasto[$idfpa]=$valor;
						}else{
							$fpgasto[$idfpa]+=$valor;
						}
						echo $gf->utf8("
						<tr class='bg-warning'>
							<td>INVENT.</td>
							<td>	$id_compra</td>
							<td>	$proveedor</td>
							<td colspan='3'>	$descripcion</td>
							<td>	$fecha</td>
							<td align='right'>".number_format($valor,0)."</td>
						</tr>");
						$totGasto+=$valor;
					}
				}
				
				$fppropins=array();
				if($_SESSION["restpropina"]>0){
					$resultPropins = $gf->dataSet("SELECT CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS NOMBRE, SUM(P.PROPINA) AS VALOR FROM usuarios AS U JOIN pedidos AS P ON (U.ID_USUARIO=P.ID_TENDER) WHERE U.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='$id_servicio' AND P.ID_CAJERO='$id_cajero' GROUP BY U.ID_USUARIO ORDER BY U.NOMBRES,U.APELLIDOS");
					$tabpro="<table class='table table-bordered'><tr><td class='bg-danger' colspan='2'>TABLA DE PROPINAS</td>";
					$nus=0;
					$tot_propina=0;
					if(count($resultPropins)>0){
						foreach($resultPropins as $rwI){
							$nombretender=$rwI["NOMBRE"];
							
							$valorp=$rwI["VALOR"];
							$tot_propina+=$valorp;
							$tabpro.="<tr><td> $nombretender</td><td>".number_format($valorp,0)."</td></tr>";
							$nus++;
						}
					}
					$promedio=$tot_propina/$nus;
					$tabpro.="<tr class='bg-success'><td>TOTAL</td><td>".number_format($tot_propina,0)."</td></tr>";
					$tabpro.="<tr class='bg-success'><td> PROMEDIO:</td><td> ".number_format($promedio,0)."</td></tr>";
					$tabpro.="</table><hr />";


					$resultPropins = $gf->dataSet("SELECT P.ID_FP, SUM(P.PROPINA) AS VALOR FROM pedidos AS P WHERE P.ID_SERVICIO='$id_servicio' AND P.ID_CAJERO='$id_cajero' GROUP BY P.ID_FP ORDER BY P.ID_FP");

					if(count($resultPropins)>0){
						foreach($resultPropins as $rwI){
							$idfpp=$rwI["ID_FP"];
							$valorp=$rwI["VALOR"];
							$fppropins[$idfpp]=$valorp;
						}
					}


				}else{
					$tot_propina=0;
					$tabpro="";
				}


				$totalreal=$total+$total_otros+$base_caja-$totGasto;
				$total_vents=$total+$total_otros;
				$total_caja=$total+$base_caja-$totGasto;
				echo $gf->utf8("</tbody>
				<tfoot>
						<tr>
						<td colspan='7' align='right'>TOTAL VENTAS</td><td align='right'>".number_format($total_ventas,0)."
						
						</td>
						</tr>
						<tr>
						<td colspan='7' align='right'>VENTAS SIN FACTURA</td><td align='right'>".number_format($total_cotizado,0)."</td>
						</tr>
						<tr>
						<tr>
						<td colspan='7' align='right'><b>TOTAL VENTAS</b></td><td align='right'><b>".number_format($total_cotizado+$total_ventas,0)."</b></td>
						</tr>
						<tr>
						<td colspan='7' align='right'>BASE CAJA</td><td align='right'>".number_format($base_caja,0)."</td>
						</tr>
						<tr>
						<td colspan='7' align='right'>GASTOS CON RECURSOS DEL SERVICIO = (GASTOS + COMPRAS DE INVENTARIO)</td><td align='right'>".number_format($totGasto,0)."</td>
						
				
						<tr>
						<td colspan='7' align='right'></td><td align='right'><input type='hidden' class='form-control unival_cuadre' name='total_real' value='$total_caja' />
		
						</td>
						</tr>
				</tfoot>
				</table>
				");
				



				
				if($_SESSION["restanticipos"]==1){
                    $rsAbonos=$gf->dataSet("SELECT P.ID_PEDIDO, A.ID_ABONO, A.FECHA, A.VALOR, A.OBSERVACION, U.NOMBRES AS USUARIO, A.ID_FP, P.ID_FP AS FP2, P.CIERRE<>'0000-00-00 00:00:00' AS CERRADO, FP.CAJA, A.ID_FP<>P.ID_FP AS SUMA FROM pedidos_abonos A JOIN usuarios U ON U.ID_USUARIO=A.ID_USUARIO JOIN pedidos P ON P.ID_PEDIDO=A.ID_PEDIDO JOIN formas_pago FP ON A.ID_FP=FP.ID_FP WHERE P.ID_SERVICIO='$id_servicio' AND P.ID_CAJERO='$id_cajero' ORDER BY P.ID_PEDIDO");
                    $tot_abonos=0;
                    $abos="";
                    $ab_forma=array();
                    $ab_resta=array();
                    $ab_suma=array();
                    if(count($rsAbonos)>0){
                        $tot_abonos=0;
                        foreach($rsAbonos as $rwAbonos){
                            $id_pedido=$rwAbonos["ID_PEDIDO"];
                            $id_abono=$rwAbonos["ID_ABONO"];
                            $fc_abono=$rwAbonos["FECHA"];
                            $vl_abono=$rwAbonos["VALOR"];
                            $ob_abono=$rwAbonos["OBSERVACION"];
                            $us_abono=$rwAbonos["USUARIO"];
                            $id_forma=$rwAbonos["ID_FP"];
                            $fp_resta=$rwAbonos["FP2"];
                            $cerrado=$rwAbonos["CERRADO"];
                            $caja=$rwAbonos["CAJA"];
                            $suma=$rwAbonos["SUMA"];
                            
                            if($cerrado==0){
								if(isset($ab_forma[$id_forma])){
									$ab_forma[$id_forma]+=$vl_abono;
								}else{
									$ab_forma[$id_forma]=$vl_abono;
								}
								$abos.="<tr><td>$id_pedido</td><td>$fc_abono </td><td>($us_abono): $ob_abono</td></td><td align='right'>$".number_format($vl_abono,0)."</td></tr>";
								$tot_abonos+=$vl_abono;
							}else{
								if($suma==1){
									if(isset($ab_resta[$fp_resta])){
										$ab_resta[$fp_resta]+=$vl_abono;
									}else{
										$ab_resta[$fp_resta]=$vl_abono;
									}
									if(isset($ab_suma[$id_forma])){
										$ab_suma[$id_forma]+=$vl_abono;
									}else{
										$ab_suma[$id_forma]=$vl_abono;
									}
									

								}
								
							}
                        }
                    }
                
                }


				
				$rsCabonos=$gf->dataSet("SELECT ID_FPP, SUM(VALOR) AS VALOR FROM cartera_ingresos WHERE ID_FPP IN(SELECT ID_FP FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}') AND ID_SERVICIO='$id_servicio' GROUP BY ID_FPP");
				$arcabos=array();
				$ingre_arcabos=0;
				if(count($rsCabonos)>0){
					foreach($rsCabonos as $rwCabonos){
						$idfpo=$rwCabonos["ID_FPP"];
						$valfpo=$rwCabonos["VALOR"];
						$arcabos[$idfpo]=$valfpo;
						$ingre_arcabos+=$valfpo;
					} 
				}
		

					 
				$rsAjustes=$gf->dataSet("SELECT ID_FP, SUM(VALOR) AS VALOR FROM ajustes_caja WHERE ID_FP IN(SELECT ID_FP FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}') AND ID_SERVICIO='$id_serv' GROUP BY ID_FP");
				$arajuste=array();
				$ajustes=0;
				if(count($rsAjustes)>0){
					foreach($rsAjustes as $rwAjuste){
						$idfpo=$rwAjuste["ID_FP"];
						$valfpo=$rwAjuste["VALOR"];
						$arajuste[$idfpo]=$valfpo;
						$ajustes+=$valfpo;
					} 
				}
		



				$gf->dataIn("INSERT IGNORE INTO servicio_cuadre (ID_SERVICIO,ID_FP,VALOR) SELECT '$id_servicio' AS ID_SERVICIO, ID_FP, '0' AS VALOR FROM formas_pago WHERE ID_SITIO='".$_SESSION["restbus"]."' ORDER BY ID_FP");

				$resultInt = $gf->dataSet("SELECT FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, SUM(P.PAGO) AS VALOR, CU.ID_REL, CU.VALOR AS VALCUADRE FROM formas_pago AS FP JOIN pedidos AS P ON (FP.ID_FP=P.ID_FP AND P.ID_SERVICIO='$id_servicio') JOIN servicio_cuadre CU ON CU.ID_FP=FP.ID_FP AND CU.ID_SERVICIO='$id_servicio' WHERE FP.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY FP.ID_FP ORDER BY FP.NOMBRE");
				echo $gf->utf8("<table class='table table-bordered'><tr class='bg-danger'><td>FORMA DE PAGO</td><td>+BASE</td>
				<td>+VENTAS</td>
				<td>+REC.CARTERA</td>
				<td>+AJUSTES</td>
				<td>+PROPINAS</td><td>-GASTOS</td><td>=SUGERIDO</td></tr>");
				$totfp=0;
				$tot_caja=0;
				$tot_otros=0;
				$tot_v=0;
				$tot_g=0;
				$tot_p=0;
				$tot_car=0;
				$tot_aju=0;
				
				if(count($resultInt)>0){
					$restar=0;
					foreach($resultInt as $rwI){
						$fp=$rwI["ID_FP"];
						$forma=$rwI["FORMAPAGO"];
						$icforma=$rwI["ICONO"];
						$valor=$rwI["VALOR"];
						$id_rel=$rwI["ID_REL"];
						$valorcu=$rwI["VALCUADRE"];
						$caja=$rwI["CAJA"];
						$abis=0;
						if(isset($ab_forma[$fp])){
							$abis=$ab_forma[$fp];
						}
						
						
						$valor+=$abis;
						if(isset($ab_suma[$fp])){
							$valor+=$ab_suma[$fp];
						}
						if(isset($ab_resta[$fp])){
							$valor-=$ab_resta[$fp];
						}
						$car=0;
						if(isset($arcabos[$fp])){
							$car=$arcabos[$fp];
							$tot_car+=$car;
						}
						$aju=0;
						if(isset($arajuste[$fp])){
							$aju=$arajuste[$fp];
							$tot_aju+=$aju;
						}

						$tvent=$valor;
						$tot_v+=$tvent;
						$totfp+=$valor;
						$addon="<small>(ventas-gastos con $forma)</small>";
						if($caja==1) $valor+=$base_caja+$car+$aju;
						if($caja==1) $addon="<small>(base+ventas+propinas-gastos con $forma)</small>";;
						if(isset($fpgasto[$fp])){
							$valor-=$fpgasto[$fp];
							$tgast=$fpgasto[$fp];
						}else{
							$tgast=0;
						}
						$addon="";
						$tot_g+=$tgast;
						if(isset($fppropins[$fp])){
							$valor+=$fppropins[$fp];
							$tprop=$fppropins[$fp];
						}else{
							$tprop=0;
						}
						$tot_p+=$tprop;

						//if($valor>0){
							if($caja==1){
								$tot_caja += $valor;
							}else{
								$tot_otros += $valor;
							}
							$ba_caa=($caja==1) ? $base_caja : 0;
							echo $gf->utf8("<tr>
							<td><i class='fa $icforma'></i> $forma<br />$addon</td><td>".number_format($ba_caa,0)."</td>
							<td>".number_format($tvent,0)."</td>
							<td>".number_format($car,0)."</td>
							<td>".number_format($aju,0)."</td>
							<td>".number_format($tprop,0)."</td><td>".number_format($tgast,0)."</td><td>".number_format($valor,0)."</td></tr>");
						//}
						
					}
					
					$tot_caja-=$restar;
					$tot_otros+=$restar;
					
				}
				echo $gf->utf8("<tr><td>TOTAL</td><td><b>".number_format($base_caja,0)."</b></td>
				<td><b>".number_format($tot_v,0)."</b></td>
				<td><b>".number_format($tot_car,0)."</b></td>
				<td><b>".number_format($tot_aju,0)."</b></td>
				<td><b>".number_format($tot_p,0)."</b></td><td><b>".number_format($tot_g,0)."</b></td><td><span id='thetot_met'></span></td></tr>");
				echo $gf->utf8("</table><hr />
				
				<hr />
				$tabpro
				");

				
			echo $gf->utf8("
			</div>
			</div>
			");
		}else{
			echo "No se encuentra el servicio";
		}




		


	}else{
		echo "Ninguna solicitud";
	}
	
}else{
	echo "No has iniciado sesion!";
}

?>