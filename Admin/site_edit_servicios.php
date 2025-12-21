<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="T" || ($_SESSION["restcajeroserv"]==1 && $_SESSION["restprofile"]=="J"))){
	
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
	$titulo="ADMINISTRACI&Oacute; DE SERVICIOS";
	$sender=$_SERVER['PHP_SELF'];
	if(isset($_GET["filterKey"])){
		$filterKey=$gf->cleanVar($_GET["filterKey"]);
		$filterVal=$gf->cleanVar($_GET["filterVal"]);
	}else{
		$filterKey="ID_SITIO";
		$filterVal=$_SESSION["restbus"];
	}

	$rigu=array(1,1,1,1,1);

	$actividad=$gf->cleanVar($_GET["flag"]);
	if($actividad=="start"){
		$lCom=$gf->dataSet("SELECT S.ID_SERVICIO, S.FECHA, S.ESTADO, COUNT(P.ID_PLATO) AS PLATOS FROM servicio S LEFT JOIN servicio_oferta P ON S.ID_SERVICIO=P.ID_SERVICIO WHERE S.ID_SITIO=:sitio GROUP BY S.FECHA ORDER BY S.FECHA DESC",array(":sitio"=>$_SESSION["restbus"]));

		echo $gf->utf8("
		<div class='box box-warning'><div class='box-header'>SERVICIO / OFERTA</div>
		<div class='box-body'>
		");
		echo $gf->utf8("
		<div class='row'>
			<div class='col-md-3' id='level2'>
			<div class='box box-danger'>
			<div class='box-header'>SERVICIOS <button class='btn btn-xs btn-success pull-right' title='Crear servicio' onclick=\"getDialog('$sender?flag=nuevo','500','Nuevo\ Servicio')\"><i class='fa fa-plus'></i></button></div>
			<div class='box-body'>
			<ul class='list-group' style='max-height:250px;overflow:auto;'>");
			if(count($lCom)>0){
				foreach($lCom as $rwServ){
					$id_service=$rwServ["ID_SERVICIO"];
					$fecha=$rwServ["FECHA"];
					$estado=$rwServ["ESTADO"];
					$platos=$rwServ["PLATOS"];
					if($estado==0){
						$classe="warning";
						if($_SESSION["restprofile"]=="T"){
							$classe.=" hidden";
						}
						$ico="fa fa-fire";
						$titl="Servicio Activo";
						
						$btns="<button onclick=\"getDialog('$sender?flag=close_serv&Vkey=$id_service','500','Cerrar\ Servicio')\" class='btn btn-xs btn-danger pull-right stoppa' title='Cerrar Servicio'><i class='fa fa-check-square-o'></i></button>";
						$btns.="<button onclick=\"getDialog('$sender?flag=editar&Vkey=$id_service','500','Editar\ Servicio')\" class='btn btn-xs btn-warning pull-right stoppa' title='Editar Servicio'><i class='fa fa-edit'></i></button>";
						if($platos==0){
							$btns.="<button onclick=\"goErase('servicio','ID_SERVICIO','$id_service','theserv_$id_service')\" class='btn btn-xs btn-danger pull-right stoppa' title='Eliminar Servicio'><i class='fa fa-trash'></i></button>";
						}
						$bandera="level3";
						$_SESSION["restservice"]=$id_service;
					}elseif($estado==1){
						$classe="success";
						$ico="fa fa-check";
						$titl="Servicio En Revisi&oacute;n";
						$bandera="cuadre";
						$btns="";
						if($_SESSION["restprofile"]=="T"){
							$classe.=" hidden";
						}
					}else{
						$classe="danger";
						$ico="fa fa-lock";
						$titl="Servicio Cerrado y Cuadrado";
						$bandera="resumen";
						$btns="";
					}
					echo $gf->utf8("<li id='theserv_$id_service' title='$titl' onclick=\"cargaHTMLvars('level3','$sender?flag=$bandera&key=$id_service&st=$estado')\" class='list-group-item list-group-item-$classe link-cnv'>$fecha <i class='$ico pull-right'></i> $btns</li>");
				}
			}
			echo $gf->utf8("
			</ul>
			</div>
			</div>
			</div>
			<div class='col-md-9' id='level3'></div>
		</div>
		</div>
		</div>
		
		");
	}elseif($actividad=="resumen"){
		$id_serv=$gf->cleanVar($_GET["key"]);
		$id_servicio=$gf->cleanVar($_GET["key"]);

		echo $gf->utf8("<h3>RESUMEN CUADRE</h3>");

		$rsServ=$gf->dataSet("SELECT FECHA, ESTADO, BASE_CAJA, OBSERVACION FROM servicio WHERE ID_SERVICIO=:servicio",array(":servicio"=>$id_serv));
		
		if(count($rsServ)>0){
			$rwServ=$rsServ[0];
			$fecha_servicio=$rwServ["FECHA"];
			$estado=$rwServ["ESTADO"];
			$base_caja=$rwServ["BASE_CAJA"];
			$observac=$rwServ["OBSERVACION"];
			$totGasto=0;
		
			$resultInt = $gf->dataSet("SELECT F.ID_FACTURA, F.PREFIJO, F.CONSECUTIVO, M.ID_MESA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, P.APERTURA, P.CIERRE, P.PAGO, P.IMPUESTO, P.DCTO, FP.NOMBRE AS FORMA, FP.CAJA, FP.ID_FP FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA) JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER LEFT JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO LEFT JOIN formas_pago FP ON FP.ID_FP=P.ID_FP WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='$id_serv' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
				
			if(count($resultInt)>0){
				
				$total=0;
				$total_otros=0;
				$total_dcto=0;
				$total_ventas=0;
				$total_cotizado=0;
				$tot_imp_base=0;
				$tot_impuesto=0;
				foreach($resultInt as $rowInt){
					$id_mesa=$rowInt["ID_MESA"];
					$nombre=$rowInt["NOMBRE"];
					$tender=$rowInt["TENDER"];
					$id_pedido=$rowInt["ID_PEDIDO"];
					$id_fp=$rowInt["ID_FP"];
					$apertura=$rowInt["APERTURA"];
					$cierre=$rowInt["CIERRE"];
					$pago=$rowInt["PAGO"];
					$impuesto=$rowInt["IMPUESTO"];
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
						$tot_impuesto+=$impuesto;
					}else{
						$total_cotizado += $sumar;
					} 
				}

			}
			$tot_imp_base=$total_ventas-$tot_impuesto;

			$gtos=$gf->dataSet("SELECT T.NOMBRE, G.ID_GASTO, G.DESCRIPCION, G.FECHA, G.VALOR, G.ID_FP FROM gastos G JOIN gastos_tipos T ON T.ID_TIPO=G.ID_TIPO WHERE G.ID_SERVICIO=:servicio",array(":servicio"=>$id_serv));
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
					$totGasto+=$valor;
				}
			}

			$compras=$gf->dataSet("SELECT C.ID_COMPRA, C.FECHA, C.OBSERVACION, C.FACTURA, P.NOMBRE AS PROVEEDOR, SUM(I.PRECIO*I.CANTIDAD) AS VALOR, C.ID_FP FROM inventario_compras C JOIN proveedores P ON C.ID_PROVEEDOR=P.ID_PROVEEDOR JOIN inventario I ON I.ID_COMPRA=C.ID_COMPRA WHERE C.ID_SERVICIO=:servicio GROUP BY C.ID_COMPRA ORDER BY C.ID_COMPRA",array(":servicio"=>$id_serv));
			
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
					$totGasto+=$valor;
				}
			}
			
			$fppropins=array();
			$tot_propina=0;
			if($_SESSION["restpropina"]>0){
				$resultPropins = $gf->dataSet("SELECT P.ID_FP, SUM(P.PROPINA) AS VALOR FROM pedidos AS P WHERE P.ID_SERVICIO='$id_serv' GROUP BY P.ID_FP ORDER BY P.ID_FP");

				if(count($resultPropins)>0){
					foreach($resultPropins as $rwI){
						$idfpp=$rwI["ID_FP"];
						$valorp=$rwI["VALOR"];
						$fppropins[$idfpp]=$valorp;
						$tot_propina+=$valorp;
					}
				}

			}else{
				$tot_propina=0;
				$tabpro="";
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
		


			$totalreal=$total+$total_otros+$base_caja-$totGasto+$ingre_arcabos;
			$total_vents=$total+$total_otros+$ingre_arcabos;
			$total_caja=$total+$base_caja-$totGasto+$ingre_arcabos;
			

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

			$resultInt = $gf->dataSet("SELECT FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, SUM(P.PAGO) AS VALOR, CU.ID_REL, CU.VALOR AS VALCUADRE FROM formas_pago AS FP LEFT JOIN pedidos AS P ON (FP.ID_FP=P.ID_FP AND P.ID_SERVICIO='$id_servicio') JOIN servicio_cuadre CU ON CU.ID_FP=FP.ID_FP AND CU.ID_SERVICIO='$id_servicio' WHERE FP.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY FP.ID_FP ORDER BY FP.NOMBRE");
			$tab_resumen="<table class='table table-bordered'><tr class='bg-danger'><td>FORMA DE PAGO</td><td>+BASE</td>
			<td>+VENTAS</td>
			<td>+REC. CARTERA</td>
			<td>+AJUSTES</td>
			<td>+PROPINAS</td><td>-GASTOS</td><td>=SUGERIDO</td><td>CONTEO</td><td class='bg-danger'>DIFERENCIA</td></tr>";
			$totfp=0;
			$tot_caja=0;
			$tot_otros=0;
			$tot_v=0;
			$tot_g=0;
			$tot_p=0;
			$tot_d=0;
			$tot_c=0;
			$tot_t=0;
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
					if($caja==1) $valor+=$base_caja+$car;
					$valor+=$aju;
					if($caja==1) $addon="<small>(base+ventas+propinas-gastos con $forma)</small>";
					$addon="";
					if(isset($fpgasto[$fp])){
						$valor-=$fpgasto[$fp];
						$tgast=$fpgasto[$fp];
					}else{
						$tgast=0;
					}
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
						$descu=$valorcu-$valor;
						$tot_c+=$valorcu;
						$tot_t+=$valor;
						$tab_resumen.="<tr><td><i class='fa $icforma'></i> $forma<br />$addon</td><td>".number_format($ba_caa,0)."</td>
						<td>".number_format($tvent,0)."</td>
						<td>".number_format($car,0)."</td>
						<td>".number_format($aju,0)."</td>
						<td>".number_format($tprop,0)."</td><td>".number_format($tgast,0)."</td><td>".number_format($valor,0)."</td><td>".number_format($valorcu,0)."</td><td>".number_format($descu,0)."</td></tr>";
					//}
					$tot_d+=$descu;
					
				}
				
				$tot_caja-=$restar;
				$tot_otros+=$restar;
				
			}
			$tab_resumen.="<tr><td>TOTAL</td><td><b>".number_format($base_caja,0)."</b></td>
			<td><b>".number_format($tot_v,0)."</b></td>
			<td><b>".number_format($tot_car,0)."</b></td>
			<td><b>".number_format($tot_aju,0)."</b></td>
			<td><b>".number_format($tot_p,0)."</b></td><td><b>".number_format($tot_g,0)."</b></td><td><b>".number_format($tot_t,0)."</b></td><td><b>".number_format($tot_c,0)."</b></td><td><b>".number_format($tot_d,0)."</b></td><td></td></tr></table>";

		}else{
			echo "No se encuentra el servicio";
			exit;
		}
		

		echo $gf->utf8("
		FECHA DEL SERVICIO: $fecha_servicio<br />

		$tab_resumen<br />

		<b>SUGERIDO TOTAL:</b> ".number_format($tot_t,0)."<br />
		<b>VALOR CONTEO TOTAL:</b> ".number_format($tot_c,0)."<br />
		<b>DESCUADRE:</b> ".number_format($tot_d,0)."<br />
		<hr /> INFORMACI&Oacute;N TRIBUTARIA<br />
	
		<b>BASE VENTAS:</b> ".number_format($tot_imp_base,0)."<br />
		<b>IMPUESTO VENTAS:</b> ".number_format($tot_impuesto,0)."<br />
		<hr />
		OBSERVACI&Oacute;N:<br />
		$observac<br />


		<a class='btn btn-danger btn-minier btn-xs' href=\"Admin/pdf_z.php?id_servicio=$id_servicio\" target='_blank'>Generar Z</a>

		<a class='btn btn-danger btn-minier btn-xs' href=\"Admin/pdf_x.php?id_servicio=$id_servicio\" target='_blank'>Reporte General</a>

		<hr />

		<a class='btn btn-danger btn-minier btn-xs' href=\"Admin/pdf_z.php?id_servicio=$id_servicio&tm=1\" target='_blank'>Generar Z - Formato 58mm</a>

		<a class='btn btn-danger btn-minier btn-xs' href=\"Admin/pdf_x.php?id_servicio=$id_servicio&tm=1\" target='_blank'>Reporte General - Formato 58mm</a>
		");
		
			
		
	}elseif($actividad=="cambia_fp"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$id_servicio=$gf->cleanVar($_GET["id_serv"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$id_cur= $gf->cleanVar($_GET["cur"]);

		$formas=$gf->dataSet("SELECT ID_FP, NOMBRE, ICONO FROM formas_pago WHERE ID_SITIO=:sitio ORDER BY NOMBRE",array(":sitio"=>$_SESSION["restbus"]));

		$rsAbonos=$gf->dataSet("SELECT A.ID_ABONO, A.ID_FP, A.FECHA, A.VALOR, A.OBSERVACION, U.NOMBRES AS USUARIO FROM pedidos_abonos A JOIN usuarios U ON U.ID_USUARIO=A.ID_USUARIO WHERE A.ID_PEDIDO='$id_pedido'");
		$tot_abonos=0;
		$abos="<table class='table table-bordered'><tr><td>CONCEPTO</td><td>VALOR</td><td>CAMBIAR FORMA DE PAGO</td></tr>";
		if(count($rsAbonos)>0){
			$tot_abonos=0;
			foreach($rsAbonos as $rwAbonos){
				$id_abono=$rwAbonos["ID_ABONO"];
				$fc_abono=$rwAbonos["FECHA"];
				$vl_abono=$rwAbonos["VALOR"];
				$ob_abono=$rwAbonos["OBSERVACION"];
				$us_abono=$rwAbonos["USUARIO"];
				$fp_abono=$rwAbonos["ID_FP"];
				$fsel="<select name='fab_$id_abono' id='fab_$id_abono' class='form-control unival_fp_$id_pedido'>";
				foreach($formas as $fpa){
					$idfp=$fpa["ID_FP"];
					$nombrefp=$fpa["NOMBRE"];
					$checked="";
					if($idfp==$fp_abono) $checked="selected='selected'";
					$fsel.="<option value='$idfp' $checked>$nombrefp</option>";
				}
				$fsel.="</select>";

				$abos.="<tr><td>ANTICIPO $fc_abono ($us_abono): $ob_abono</td><td align='right'>$".number_format($vl_abono,0)."</td><td>$fsel</td></tr>";
				$tot_abonos+=$vl_abono;
			}
		}
		$resta=0;
		$rsPe=$gf->dataSet("SELECT PAGO, ID_FP FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
		if(count($rsPe)>0){
			$pago=$rsPe[0]["PAGO"];
			$id_cur=$rsPe[0]["ID_FP"];
			$resta=$pago-$tot_abonos;
		}
		
		$abos.="<tr><td>CUENTA PRINCIPAL</td><td>$".number_format($resta,0)."</td><td><select name='formapago' id='formapago' class='form-control unival_fp_$id_pedido'>";
		if(count($formas)>0){
			foreach($formas as $fpa){
				$idfp=$fpa["ID_FP"];
				$nombrefp=$fpa["NOMBRE"];
				$checked="";
				if($id_cur==$idfp) $checked="selected='selected'";
				$abos.="<option value='$idfp' $checked>$nombrefp</option>";
			}
		}
		$abos.="</select></td></tr></table>";
		echo $gf->utf8($abos."
		
		<button class='btn btn-sm btn-warning' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=cambia_fp_go&id_ped=$id_pedido&rnd=$rnd&id_serv=$id_servicio','','10000','unival_fp_$id_pedido')\">Cambiar</button>
		
		");
		
	}elseif($actividad=="cambia_fp_go"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$id_servicio=$gf->cleanVar($_GET["id_serv"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$NFP=$_POST["formapago"];

		$rsAbonos=$gf->dataSet("SELECT A.ID_ABONO, A.ID_FP, A.FECHA, A.VALOR, A.OBSERVACION, U.NOMBRES AS USUARIO FROM pedidos_abonos A JOIN usuarios U ON U.ID_USUARIO=A.ID_USUARIO WHERE A.ID_PEDIDO='$id_pedido'");
		if(count($rsAbonos)>0){
			$tot_abonos=0;
			foreach($rsAbonos as $rwAbonos){
				$id_abono=$rwAbonos["ID_ABONO"];
				if(isset($_POST["fab_$id_abono"])){
					$id_fab=$_POST["fab_$id_abono"];
					$gf->dataIn("UPDATE pedidos_abonos SET ID_FP='$id_fab' WHERE ID_ABONO='$id_abono' AND ID_PEDIDO='$id_pedido'");
				}
			}
		}

		$ok=$gf->dataIn("UPDATE pedidos SET ID_FP='$NFP' WHERE ID_PEDIDO='$id_pedido'");
		if($ok){
			echo $gf->utf8("Cambio registrado...
			<input type='hidden' id='callbackeval' value=\"cargaHTMLvars('level3','$sender?flag=cuadre&key=$id_servicio&st=1');closeD('$rnd')\" />
			");
		}
	}elseif($actividad=="cuadre"){
		
		$id_servicio = (isset($_GET["key"])) ? $_GET["key"] : $_SESSION["restservice"];

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
		

			

				$resultInt = $gf->dataSet("SELECT F.ID_FACTURA, F.PREFIJO, F.CONSECUTIVO, M.ID_MESA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, P.APERTURA, P.CIERRE, P.PAGO, P.DCTO, FP.NOMBRE AS FORMA, FP.CAJA, FP.ID_FP, SUM(A.VALOR) AS ABONOS FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA) JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER LEFT JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO LEFT JOIN formas_pago FP ON FP.ID_FP=P.ID_FP LEFT JOIN pedidos_abonos A ON A.ID_PEDIDO=P.ID_PEDIDO AND A.ID_FP<>P.ID_FP WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='$id_servicio' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
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
						$abonos=$rowInt["ABONOS"];
						if($abonos!=0 && $abonos!=""){
							$forma_pago="MIXTA";
						}
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
							<td>	<button class='btn btn-xs btn-warning' onclick=\"getDialog('$sender?flag=cambia_fp&id_serv=$id_servicio&id_ped=$id_pedido&cur=$id_fp','200','Editar')\"><i class='fa fa-edit'></i> $forma_pago</button></td>
						</tr>");
					}

				}
				
				$gtos=$gf->dataSet("SELECT T.NOMBRE, G.ID_GASTO, G.DESCRIPCION, G.FECHA, G.VALOR, G.ID_FP FROM gastos G JOIN gastos_tipos T ON T.ID_TIPO=G.ID_TIPO WHERE G.ID_SERVICIO=:servicio",array(":servicio"=>$id_servicio));
				$fpgasto=array();
				if(count($gtos)>0){
					echo $gf->utf8("
						<tr class='bg-danger'>
							<td colspan='8'>	GASTOS </td>
						</tr>");
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



				$rsCabonos=$gf->dataSet("SELECT C.ID_INGRESO, P.ID_FP, P.NOMBRE, C.ID_FPC, C.FECHA, C.VALOR, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS USUARIO FROM formas_pago P JOIN cartera_ingresos C ON C.ID_FPC=P.ID_FP JOIN usuarios U ON U.ID_USUARIO=C.ID_USUARIO WHERE C.ID_SERVICIO='$id_servicio' GROUP BY C.ID_INGRESO");

				
				if(count($rsCabonos)>0){
					echo $gf->utf8("
					<tr class='bg-danger'><td colspan='8'>RECUPERACI&Oacute;N DE CARTERA</td></tr>
					<tr class='bg-warning'>
						<td colspan='2'><b>FECHA</b></td>
						<td colspan='2'><b>USUARIO QUE REGISTRA</b></td>
						<td colspan='2'><b>CARTERA</b></td>
						<td colspan='2' align='right'><b>VALOR ABONO</b></td>
					</tr>");
					foreach($rsCabonos as $rwCabonos){
						
						$id_ingreso=$rwCabonos["ID_INGRESO"];
						$valfpo=$rwCabonos["VALOR"];
						$fecha=$rwCabonos["FECHA"];
						$formap=$rwCabonos["NOMBRE"];
						$usuario=$rwCabonos["USUARIO"];
						echo $gf->utf8("<tr><td colspan='2'>$fecha</td><td colspan='2'>$usuario</td><td colspan='2'>$formap</td><td colspan='2' align='right'>".number_format($valfpo,0)."</td></tr>");
					} 
				}


				


				$rsCabonos=$gf->dataSet("SELECT C.ID_AJUSTE, P.ID_FP, P.NOMBRE, C.ID_FP, C.FECHA, C.VALOR, C.OBSERVACION, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS USUARIO FROM formas_pago P JOIN ajustes_caja C ON C.ID_FP=P.ID_FP JOIN usuarios U ON U.ID_USUARIO=C.ID_USUARIO WHERE C.ID_SERVICIO='$id_servicio' GROUP BY C.ID_AJUSTE");

				
				if(count($rsCabonos)>0){
					echo $gf->utf8("
					<tr><td colspan='8' class='bg-danger'>AJUSTES CAJA</td></tr>
					<tr class='bg-warning'>
						<td colspan='2'><b>FECHA</b></td>
						<td colspan='2'><b>USUARIO QUE REGISTRA</b></td>
						<td colspan='2'><b>OBSERVACION</b></td>
						<td colspan='2' align='right'><b>VALOR</b></td>
					</tr>");
					foreach($rsCabonos as $rwCabonos){
						
						$id_ajuste=$rwCabonos["ID_AJUSTE"];
						$valfpo=$rwCabonos["VALOR"];
						$fecha=$rwCabonos["FECHA"];
						$observ=$rwCabonos["OBSERVACION"];
						$usuario=$rwCabonos["USUARIO"];
						echo $gf->utf8("<tr><td colspan='2'>$fecha</td><td colspan='2'>$usuario</td><td colspan='2'>$observ</td><td colspan='2' align='right'>".number_format($valfpo,0)."</td></tr>");
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


				$totalreal=$total+$total_otros+$base_caja-$totGasto+$ingre_arcabos;
				$total_vents=$total+$total_otros+$ingre_arcabos;
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
						
						<input type='hidden' class='unival_cuadre' name='total_efectivo' value='$total_caja' />
						<input type='hidden' class='unival_cuadre' name='total_otros' value='$total_otros' />
						<input type='hidden' class='unival_cuadre' name='total_ventas' value='$total_vents' /><input type='hidden' class='unival_cuadre' name='base_caja' value='$base_caja' />
						<input type='hidden' class='unival_cuadre' name='total_gastos' value='$totGasto' />
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
					if($tot_abonos>0){
						echo $gf->utf8("<table class='table table-bordered'><tr><td class='bg-danger' colspan='4'>TABLA DE ANTICIPOS - CUENTAS ABIERTAS</td></tr><tr><td>PEDIDO</td><td>FECHA</td><td>OBSERVACION</td><td>VALOR</td></tr>".$abos."</table><br />TOTAL ABONOS: ".number_format($tot_abonos)."");
					}
				}
				




				$gf->dataIn("INSERT IGNORE INTO servicio_cuadre (ID_SERVICIO,ID_FP,VALOR) SELECT '$id_servicio' AS ID_SERVICIO, ID_FP, '0' AS VALOR FROM formas_pago WHERE ID_SITIO='".$_SESSION["restbus"]."' ORDER BY ID_FP");

				$resultInt = $gf->dataSet("SELECT FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, SUM(P.PAGO) AS VALOR, CU.ID_REL, CU.VALOR AS VALCUADRE FROM formas_pago AS FP LEFT JOIN pedidos AS P ON (FP.ID_FP=P.ID_FP AND P.ID_SERVICIO='$id_servicio') JOIN servicio_cuadre CU ON CU.ID_FP=FP.ID_FP AND CU.ID_SERVICIO='$id_servicio' WHERE FP.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY FP.ID_FP ORDER BY FP.NOMBRE");
				echo $gf->utf8("<table class='table table-bordered'><tr class='bg-danger'><td>FORMA DE PAGO</td><td>+BASE</td>
				<td>+VENTAS</td>
				<td>+REC. CARTERA</td>
				<td>+AJUSTES</td>
				<td>+PROPINAS</td><td>-GASTOS</td><td>=SUGERIDO</td><td>CONTEO</td></tr>");
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
						if($valor==null || $valor=="") $valor=0;
						$id_rel=$rwI["ID_REL"];
						$valorcu=$rwI["VALCUADRE"];
						$caja=$rwI["CAJA"];
						$abis=0;
						if(isset($ab_forma[$fp])){
							$abis=$ab_forma[$fp];
						}
						if($abis==null || $abis=="") $abis=0;
						
						
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
						if($caja==1) $valor+=$base_caja+$car;
						$valor+=$aju;
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


							if(isset($_GET["key"])){


								if($valor>0 && $valorcu==0){
									$gf->dataIn("UPDATE servicio_cuadre SET VALOR='$valor' WHERE ID_REL='$id_rel'");
									//$valorcu=$valor+$car;
								}

								$edit_conteo="<input type='number' step='any' value='$valorcu' class='form-control unival_$id_rel forms_ogp' name='valri_$id_rel' onchange=\"cargaHTMLvars('state_proceso','$sender?flag=upvrel&id_rel=$id_rel','','5000','unival_$id_rel');calculaToto()\" />";
							}else{
								$edit_conteo=number_format($valor,0);
							}

							echo $gf->utf8("<tr><td><i class='fa $icforma'></i> $forma<br />$addon</td>
							<td>".number_format($ba_caa,0)."</td>
							<td>".number_format($tvent,0)."</td>
							<td>".number_format($car,0)."</td>
							<td>".number_format($aju,0)."</td>
							<td>".number_format($tprop,0)."</td><td>".number_format($tgast,0)."</td><td>".number_format($valor,0)."</td><td>$edit_conteo</td></tr>");
						}
						
					}
	
					
				}
				echo $gf->utf8("<tr><td>TOTAL</td><td><b>".number_format($base_caja,0)."</b></td>
				<td><b>".number_format($tot_v,0)."</b></td>
				<td><b>".number_format($tot_car,0)."</b></td>
				<td><b>".number_format($tot_aju,0)."</b></td>
				<td><b>".number_format($tot_p,0)."</b></td><td><b>".number_format($tot_g,0)."</b></td><td></td><td><span id='thetot_met'></span></td></tr>");
				echo $gf->utf8("</table><hr />
				<script>
					function calculaToto(){
						var tost=0;
						$('.forms_ogp').each(function(){
							tost+=parseInt($(this).val());
						});
						$('thetot_met').text(number_format(tost,0,',','.'));
					}
				</script>
				<h2>
				
				<b>TOTAL SUGERIDO CAJA: </b> $".number_format($tot_caja,0)."<br />
				<br />
				<b>TOTAL OTROS M&Eacute;TODOS:</b> $ ".number_format($tot_otros,0)."</h2>
				<hr />
				$tabpro
				");

				
			$pedis=$gf->dataSet("SELECT ID_PEDIDO FROM pedidos WHERE ID_SERVICIO=:servicio AND CIERRE='0000-00-00 00:00:00' AND CHEF<>'0000-00-00 00:00:00'",array(":servicio"=>$id_servicio));
			if(count($pedis)>0){
				$pedss="";
				foreach($pedis as $pedk){
					$pedo=$pedk["ID_PEDIDO"];
					$pedss.="Ped.#".$pedo.",";
				}
				echo $gf->utf8("<div class='alert alert-warning alert-dismissible'>Hay pedidos abiertos en este servicio $pedss los cuales ser&aacute;n asignados al siguiente servicio que se cree</div>");
				

			}
			
			if(isset($_GET["key"])){
				echo $gf->utf8("
				<button class='btn btn-primary pull-right' onclick=\"getDialog('$sender?flag=cuadra_serv&id_serv=$id_servicio','1200','Cuadrar\ Caja','','','','unival_cuadre')\">Cuadrar Caja</button>
				");		
			}
			
			echo $gf->utf8("
			</div>
			</div>
			");
		}else{
			echo "No se encuentra el servicio";
		}
	}elseif($actividad=="upvrel"){
		$id_rel=$gf->cleanVar($_GET["id_rel"]);
		$valor=$_POST["valri_$id_rel"];
		$gf->dataIn("UPDATE servicio_cuadre SET VALOR='$valor' WHERE ID_REL='$id_rel'");

	}elseif($actividad=="cancelpedis_cierre"){
		$id_servicio=$gf->cleanVar($_GET["id_servicio"]);
	}elseif($actividad=="genera_z"){
		$id_servicio=$gf->cleanVar($_GET["id_servicio"]);

		$rnd=$gf->cleanVar($_GET["rnd"]);
		$curConf=$gf->dataSet("SELECT PRINTER_HOST, CLIENT_KEY, CLIENT_SEC FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		$prhs=$curConf[0]["PRINTER_HOST"];
		$client_k=$curConf[0]["CLIENT_KEY"];
		$client_s=$curConf[0]["CLIENT_SEC"];
		echo $gf->utf8("
		<script>
			  $(function () {
				var params={'credentials':{'client':'$client_k','token':'$client_s'},'servicio':'$id_servicio'};
				$.ajax({
					url : 'https://www.torresoft.co:3443/api/rest/get_z/',
					type: 'GET',
					data : params,
					timeout:5000,
					dataType: 'json',
					success: function(data, textStatus, jqXHR)
						{
							console.log(data);
							$('#alertaimpresora').hide();
							if(data.success){
								$.ajax({
									type: 'POST',
									url: '$prhs',
									data: {contents: data.result,rectype:'z'},
									success: function(data, textStatus, jqXHR)
									{
										console.log(data);
										$('#alertaimpresora').hide();
										closeD('$rnd')
									},
									error : function(xhr, textStatus, errorThrown ) {
										$('#alertaimpresora').show();
										console.log('Motor de impresion local fuera' + errorThrown);
									}
								});
							}
						},
					error : function(xhr, textStatus, errorThrown ) {
						$('#alertaimpresora').show();
						console.log('Motor de impresion remoto fuera');
					}
				});
			  });
			</script>
			Imprimiendo...
			
			");
	}elseif($actividad=="genera_y"){
		$id_servicio=$gf->cleanVar($_GET["id_servicio"]);

		$rnd=$gf->cleanVar($_GET["rnd"]);
		$curConf=$gf->dataSet("SELECT PRINTER_HOST, CLIENT_KEY, CLIENT_SEC FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		$prhs=$curConf[0]["PRINTER_HOST"];
		$client_k=$curConf[0]["CLIENT_KEY"];
		$client_s=$curConf[0]["CLIENT_SEC"];
		echo $gf->utf8("
		<script>
				$(function () {
				var params={'credentials':{'client':'$client_k','token':'$client_s'},'servicio':'$id_servicio'};
				$.ajax({
					url : 'https://www.torresoft.co:3443/api/rest/get_y/',
					type: 'GET',
					data : params,
					timeout:5000,
					dataType: 'json',
					success: function(data, textStatus, jqXHR)
						{
							console.log(data);
							$('#alertaimpresora').hide();
							if(data.success){
								$.ajax({
									type: 'POST',
									url: '$prhs',
									data: {contents: data.result,rectype:'z'},
									success: function(data, textStatus, jqXHR)
									{
										console.log(data);
										$('#alertaimpresora').hide();
										closeD('$rnd')
									},
									error : function(xhr, textStatus, errorThrown ) {
										$('#alertaimpresora').show();
										console.log('Motor de impresion local fuera' + errorThrown);
									}
								});
							}
						},
					error : function(xhr, textStatus, errorThrown ) {
						$('#alertaimpresora').show();
						console.log('Motor de impresion remoto fuera');
					}
				});
				});
			</script>
			Imprimiendo...
			
			");
	

	}elseif($actividad=="close_serv_go"){
		$id_serv=$gf->cleanVar($_GET["id_serv"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$pedis=$gf->dataSet("SELECT P.ID_PEDIDO, M.NOMBRE AS MESA FROM pedidos P JOIN mesas M ON M.ID_MESA=P.ID_MESA WHERE P.ID_SERVICIO=:servicio AND P.CIERRE='0000-00-00 00:00:00'",array(":servicio"=>$id_serv));
		if(count($pedis)==0){
			$ok=$gf->dataIn("UPDATE servicio SET ESTADO='1' WHERE ID_SERVICIO=:servicio",array(":servicio"=>$id_serv));
			if($ok){
				echo $gf->utf8("El servicio ha sido cerrado y queda listo para cuadre de caja<br />
				<button onclick=\"getAux('$sender?flag=start');closeD('$rnd')\" class='btn btn-xs btn-success pull-right stoppa'><i class='fa fa-check'></i> Ok!</button>
				");
			}else{
				echo "Error al cerrar el servicio";
			}
		}else{
			echo "El servicio tiene pedidos abiertos";
		}
	}elseif($actividad=="close_serv"){
		$id_serv=$gf->cleanVar($_GET["Vkey"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$konfig=$gf->dataSet("SELECT PERSISTENTE FROM sitios WHERE ID_SITIO='{$_SESSION["restbus"]}'");
		$persiste=$konfig[0]["PERSISTENTE"];
		//if($_SESSION["restanticipos"]==0){
		$pedis0=$gf->dataSet("SELECT ID_PEDIDO, ID_MESA FROM pedidos WHERE ID_SERVICIO=:servicio AND CIERRE='0000-00-00 00:00:00' AND (CHEF='0000-00-00 00:00:00' OR ID_PEDIDO NOT IN(SELECT S.ID_PEDIDO FROM sillas S JOIN sillas_platos P ON P.ID_SILLA=S.ID_SILLA ORDER BY S.ID_PEDIDO))",array(":servicio"=>$id_serv));
		if(count($pedis0)>0){
			foreach($pedis0 as $pdis){
				$idpo=$pdis["ID_PEDIDO"];
				$idme=$pdis["ID_MESA"];
				$gf->log($_SESSION["restbus"],$idme,$idpo,"PEDIDO ELIMINADO AL CIERRE DEL SERVICIO",$_SESSION["restuiduser"]);
			}
			$gf->dataIn("DELETE FROM pedidos WHERE ID_SERVICIO='$id_serv' AND (CHEF='0000-00-00 00:00:00' OR ID_PEDIDO NOT IN(SELECT S.ID_PEDIDO FROM sillas S JOIN sillas_platos P ON P.ID_SILLA=S.ID_SILLA ORDER BY S.ID_PEDIDO))");
		}
		//}
		$pedis=$gf->dataSet("SELECT P.ID_PEDIDO, M.NOMBRE AS MESA FROM pedidos P JOIN mesas M ON M.ID_MESA=P.ID_MESA WHERE P.ID_SERVICIO=:servicio AND P.CIERRE='0000-00-00 00:00:00'",array(":servicio"=>$id_serv));
		if(count($pedis)==0){
			echo $gf->utf8("Se va a cerrar el servicio activo, se inactivar&aacute;n todas las mesas y se enviar&aacute; a cuadre de caja, continuar?<br />
			
			");
		}else{
			if($persiste==0){
				echo $gf->utf8("Hay pedidos abiertos, no se puede cerrar el servicio hasta no finalizar todos los pedidos.<hr />
				<button onclick=\"closeD('$rnd')\" class='btn btn-xs btn-warning pull-right stoppa'><i class='fa fa-remove'></i> Cancelar</button>
				
				"); 
				exit;
			}
			echo $gf->utf8("Hay pedidos abiertos, los pedidos que queden abiertos pasar&aacute;n al pr&oacute;ximo servicio creado.");

			foreach($pedis as $pedk){
				$pedo=$pedk["ID_PEDIDO"];
				$mesa=$pedk["MESA"];
				echo $gf->utf8("<br />Pedido: ".$pedo." ($mesa)");
			}
			
			
		}
		echo $gf->utf8("<br />Deseas continuar? <hr /><button onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=close_serv_go&id_serv=$id_serv&rnd=$rnd')\" class='btn btn-xs btn-danger pull-right stoppa'><i class='fa fa-check'></i> Si</button>
			
		<button onclick=\"closeD('$rnd')\" class='btn btn-xs btn-warning pull-right stoppa'><i class='fa fa-remove'></i> Cancelar</button>");
	}elseif($actividad=="cuadra_serv"){
		$id_serv=$gf->cleanVar($_GET["id_serv"]);
		$id_servicio=$gf->cleanVar($_GET["id_serv"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		echo $gf->utf8("<h3>CONFIRMAR CUADRE DE SERVICIO</h3>");

		$rsServ=$gf->dataSet("SELECT FECHA, ESTADO, BASE_CAJA FROM servicio WHERE ID_SERVICIO=:servicio",array(":servicio"=>$id_serv));
		

			
		$rsCabonos=$gf->dataSet("SELECT ID_FPP, SUM(VALOR) AS VALOR FROM cartera_ingresos WHERE ID_FPP IN(SELECT ID_FP FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}') AND ID_SERVICIO='$id_serv' GROUP BY ID_FPP");
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



		if(count($rsServ)>0){
			$rwServ=$rsServ[0];
			$fecha=$rwServ["FECHA"];
			$estado=$rwServ["ESTADO"];
			$base_caja=$rwServ["BASE_CAJA"];
			$totGasto=0;
		
			$resultInt = $gf->dataSet("SELECT F.ID_FACTURA, F.PREFIJO, F.CONSECUTIVO, M.ID_MESA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, P.APERTURA, P.CIERRE, P.PAGO, P.DCTO, FP.NOMBRE AS FORMA, FP.CAJA, FP.ID_FP FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA) JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER LEFT JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO LEFT JOIN formas_pago FP ON FP.ID_FP=P.ID_FP WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='$id_serv' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
				
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
				}

			}

			$gtos=$gf->dataSet("SELECT T.NOMBRE, G.ID_GASTO, G.DESCRIPCION, G.FECHA, G.VALOR, G.ID_FP FROM gastos G JOIN gastos_tipos T ON T.ID_TIPO=G.ID_TIPO WHERE G.ID_SERVICIO=:servicio",array(":servicio"=>$id_serv));
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
					$totGasto+=$valor;
				}
			}

			$compras=$gf->dataSet("SELECT C.ID_COMPRA, C.FECHA, C.OBSERVACION, C.FACTURA, P.NOMBRE AS PROVEEDOR, SUM(I.PRECIO*I.CANTIDAD) AS VALOR, C.ID_FP FROM inventario_compras C JOIN proveedores P ON C.ID_PROVEEDOR=P.ID_PROVEEDOR JOIN inventario I ON I.ID_COMPRA=C.ID_COMPRA WHERE C.ID_SERVICIO=:servicio GROUP BY C.ID_COMPRA ORDER BY C.ID_COMPRA",array(":servicio"=>$id_serv));
			
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
					$totGasto+=$valor;
				}
			}
			
			$fppropins=array();
			$tot_propina=0;
			if($_SESSION["restpropina"]>0){
				$resultPropins = $gf->dataSet("SELECT P.ID_FP, SUM(P.PROPINA) AS VALOR FROM pedidos AS P WHERE P.ID_SERVICIO='$id_serv' GROUP BY P.ID_FP ORDER BY P.ID_FP");

				if(count($resultPropins)>0){
					foreach($resultPropins as $rwI){
						$idfpp=$rwI["ID_FP"];
						$valorp=$rwI["VALOR"];
						$fppropins[$idfpp]=$valorp;
						$tot_propina+=$valorp;
					}
				}

			}else{
				$tot_propina=0;
				$tabpro="";
			}


			$totalreal=$total+$total_otros+$base_caja-$totGasto;
			$total_vents=$total+$total_otros;
			$total_caja=$total+$base_caja-$totGasto;
			

				
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
			



			$resultInt = $gf->dataSet("SELECT FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, SUM(P.PAGO) AS VALOR, CU.ID_REL, CU.VALOR AS VALCUADRE FROM formas_pago AS FP LEFT JOIN pedidos AS P ON (FP.ID_FP=P.ID_FP AND P.ID_SERVICIO='$id_servicio') JOIN servicio_cuadre CU ON CU.ID_FP=FP.ID_FP AND CU.ID_SERVICIO='$id_servicio' WHERE FP.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY FP.ID_FP ORDER BY FP.NOMBRE");
			echo $gf->utf8("<table class='table table-bordered'><tr class='bg-danger'><td>FORMA DE PAGO</td><td>+BASE</td>
			<td>+VENTAS</td>
			<td>+REC. CARTERA</td>
			<td>+AJUSTES/td>
			<td>+PROPINAS</td><td>-GASTOS</td><td>=SUGERIDO</td><td>CONTEO</td><td class='bg-danger'>DIFERENCIA</td></tr>");
			$totfp=0;
			$tot_caja=0;
			$tot_otros=0;
			$tot_v=0;
			$tot_g=0;
			$tot_p=0;
			$tot_d=0;
			$tot_c=0;
			$tot_t=0;
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
					if($caja==1) $valor+=$base_caja+$car;
					$valor+=$aju;
					if($caja==1) $addon="<small>(base+ventas+propinas-gastos con $forma)</small>";
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
						$descu=$valorcu-$valor;
						$tot_c+=$valorcu;
						$tot_t+=$valor;
						echo $gf->utf8("<tr><td><i class='fa $icforma'></i> $forma<br />$addon</td><td>".number_format($ba_caa,0)."</td>
						<td>".number_format($tvent,0)."</td>
						<td>".number_format($car,0)."</td>
						<td>".number_format($aju,0)."</td>
						<td>".number_format($tprop,0)."</td><td>".number_format($tgast,0)."</td><td>".number_format($valor,0)."</td><td>".number_format($valorcu,0)."</td><td>".number_format($descu,0)."</td></tr>");
						$tot_d+=$descu;
					}
					
					
				}
				
				$tot_caja-=$restar;
				$tot_otros+=$restar;
				
			}
			echo $gf->utf8("<tr><td>TOTAL</td><td><b>".number_format($base_caja,0)."</b></td>
			<td><b>".number_format($tot_v,0)."</b></td>
			<td><b>".number_format($tot_car,0)."</b></td>
			<td><b>".number_format($tot_aju,0)."</b></td>
			<td><b>".number_format($tot_p,0)."</b></td><td><b>".number_format($tot_g,0)."</b></td><td><b>".number_format($tot_t,0)."</b></td><td><b>".number_format($tot_c,0)."</b></td><td><b>".number_format($tot_d,0)."</b></td><td></td></tr>");
			echo $gf->utf8("</table>
			");

		}else{
			echo "No se encuentra el servicio";
			exit;
		}
		$descuas="";
		if($tot_d!=0){
			$descuas="<br />
			<div class='alert alert-warning alert-dismissible'>
                <h4><i class='icon fa fa-warning'></i> Anuncio de Descuadre</h4>
                Hay un descuadre de $ ".number_format($tot_d,0).", desea continuar?, se registrar&aacute; el ajuste en el registro del servicio.
              </div>
			";

		}
		

		echo $gf->utf8("
		Se va a registrar el cuadre del servicio:<br />
		
		
		<b>SUGERIDO TOTAL:</b> ".number_format($tot_t,0)."<br />
		<b>VALOR CONTEO TOTAL:</b> ".number_format($tot_c,0)."<br />
		<b>DESCUADRE:</b> ".number_format($tot_d,0)."<br />
		
		OBSERVACI&Oacute;N:<br />
		<input type='hidden' name='total' id='total' class='unival_cuadre2' value='$tot_c' />
		<input type='hidden' name='ventas' id='ventas' class='unival_cuadre2' value='$tot_v' />
		<input type='hidden' name='descuadre' id='descuadre' class='unival_cuadre2' value='$tot_d' />
		<input type='hidden' name='propinas' id='propinas' class='unival_cuadre2' value='$tot_p' />
		<textarea name='cuadre_observac' class='form-control unival_cuadre2'></textarea>
		$descuas
		<button onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=cuadra_serv_go&id_serv=$id_serv&rnd=$rnd','','5000','unival_cuadre2')\" class='btn btn-xs btn-warning pull-right stoppa'><i class='fa fa-check'></i> continuar!</button>");

	}elseif($actividad=="cuadra_serv_go"){
		$id_serv=$gf->cleanVar($_GET["id_serv"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$total=$_POST["total"];
		$ventas=$_POST["ventas"];
		$descuadre=$_POST["descuadre"];
		$propinas=isset($_POST["propinas"]) ? $_POST["propinas"] : 0;
		$observac=$_POST["cuadre_observac"];
		$ok=$gf->dataIn("UPDATE servicio SET ESTADO='2', TOTAL='$total', VENTAS='$ventas', PROPINAS='$propinas', DESCUADRE='$descuadre', OBSERVACION='$observac', ID_USUARIO='{$_SESSION["restuiduser"]}' WHERE ID_SERVICIO=:servicio",array(":servicio"=>$id_serv));
		if($ok){
			$gf->log($_SESSION["restbus"],0,0,"CUADRE DE SERVICIO $id_serv: $observac",$_SESSION["restuiduser"]);
			echo $gf->utf8("Se ha registrado el cuadre de servicio<br />
			<button onclick=\"getAux('$sender?flag=start');closeD('$rnd')\" class='btn btn-xs btn-success pull-right stoppa'><i class='fa fa-check'></i> Ok!</button>
			");
		}else{
			echo "Error al cerrar el servicio";
		}
	}elseif($actividad=="level3"){
		$filterVal=$gf->cleanVar($_GET["key"]);
		$st= $gf->cleanVar($_GET["st"]);
		$lCom=$gf->dataSet("SELECT C.ID_CATEGORIA, C.NOMBRE AS CATEGORIA, P.ID_PLATO, P.NOMBRE, P.DESCRIPCION, F.ID_REL, GROUP_CONCAT(PC.ID_RACION,'|',RO.ID_OPCION) AS ADDINFO FROM platos_categorias C JOIN platos P ON P.ID_CATEGORIA=C.ID_CATEGORIA LEFT JOIN servicio_oferta F ON F.ID_PLATO=P.ID_PLATO AND F.ID_SERVICIO=:servicio LEFT JOIN platos_composicion PC ON PC.ID_PLATO=P.ID_PLATO LEFT JOIN racion_opciones RO ON RO.ID_RACION=PC.ID_RACION WHERE C.ID_SITIO=:sitio AND P.ESTADO=1 AND C.ESTADO=1 GROUP BY P.ID_PLATO ORDER BY C.NOMBRE, P.NOMBRE",array(":servicio"=>$filterVal,":sitio"=>$_SESSION["restbus"]));

		$konfig=$gf->dataSet("SELECT PERSISTENTE FROM sitios WHERE ID_SITIO='{$_SESSION["restbus"]}'");
		$persiste=$konfig[0]["PERSISTENTE"];
		if($persiste==1){
			$lastService=$gf->dataSet("SELECT ID_SERVICIO FROM servicio WHERE ID_SITIO='{$_SESSION["restbus"]}' AND ESTADO=2 ORDER BY ID_SERVICIO DESC LIMIT 1");
			if(count($lastService)>0){
				$last_sv=$lastService[0]["ID_SERVICIO"];
				$gf->dataIn("UPDATE pedidos SET ID_SERVICIO='{$_SESSION["restservice"]}' WHERE ID_SERVICIO<{$_SESSION["restservice"]} AND CIERRE='0000-00-00 00:00:00' AND ID_SERVICIO IN(SELECT ID_SERVICIO FROM servicio WHERE ID_SITIO='{$_SESSION["restbus"]}')");
			}
		}
		

		echo $gf->utf8("
		<div class='box box-warning'><div class='box-header'>SELECCIONA LOS PLATOS OFERTADOS EN EL SERVICIO <input type='text' class='form-control input-sm' placeholder='Buscar' onkeyup=\"filtrarValores('filtroproser','platsserv')\" id='filtroproser' /> <button class='btn btn-warning btn-xs pull-right btn-primary' onclick=\"getDialog('$sender?flag=include_all&svc=$filterVal')\">Incluir todo</button></div>
		<div class='box-body'>
		<div class='box-group' id='accordion'>

		");
		$arplatos=array();
		foreach($lCom as $rOp){
			$id_rel=$rOp["ID_REL"];
			$id_categoria=$rOp["ID_CATEGORIA"];
			$categoria=$rOp["CATEGORIA"];
			$id_plato=$rOp["ID_PLATO"];
			$plato=$rOp["NOMBRE"];
			$descripcion=$rOp["DESCRIPCION"];
			$addinfo=explode(",",$rOp["ADDINFO"]);
			
			if($addinfo!=""){
				$opts=0;
				$rat=array();
				foreach($addinfo as $grupo){
					if($grupo!=""){
						$subgrupo=explode("|",$grupo);
						$id_ra=$subgrupo[0];
						$id_opt=$subgrupo[1];
						if(isset($rat[$id_ra])){
							$rat[$id_ra]++;
						}else{
							$rat[$id_ra]=1;
						}
					}
				}
				foreach($rat as $nra=>$nop){
					if($nop>1){
						$opts=1;
					}
				}
			}else{
				$opts=0;
			}
			$arplatos[$id_categoria]["nm"]=$categoria;
			$arplatos[$id_categoria]["pl"][$id_plato]["nm"]=$plato;
			$arplatos[$id_categoria]["pl"][$id_plato]["dc"]=$descripcion;
			$arplatos[$id_categoria]["pl"][$id_plato]["is"]=$id_rel;
			$arplatos[$id_categoria]["pl"][$id_plato]["opts"]=$opts;
		}
		foreach($arplatos as $id_cat=>$cate){
			$nm_cat=$cate["nm"];
			echo $gf->utf8("
			<div class='panel box box-primary platsserv'>
			  <div class='box-header with-border'>
				<h4 class='box-title'>
				  <a data-toggle='collapse' data-parent='#accordion' href='#collapse_$id_cat' aria-expanded='false' class='collapsed'>
					$nm_cat
				  </a>
				</h4>
			  </div>
			  <div id='collapse_$id_cat' class='panel-collapse collapse' aria-expanded='false' style='height: 0px;'>
				<div class='box-body'>
					
					<ul class='list-group'>
					<li class='list-group-item list-group-item-warning'><input id='selall_$id_cat' style='width:20px;height:20px;' type='checkbox' onclick=\"imitaCheck('selall_$id_cat','sons_$id_cat')\" class='pull-right' /> <big>Seleccionar Todos</big>
					</li>
					
					");
				foreach($cate["pl"] as $id_pla=>$plats){
					$nm_plat=$plats["nm"];
					$dc_plat=$plats["dc"];
					$isin=$plats["is"];
					$otps=$plats["opts"];
					if($otps>0){
						$btn_opts="<button onclick=\"getDialog('$sender?flag=config_options&id_plato=$id_pla')\" class='btn btn-xs btn-warning pull-left' title='Disponibilidad de opciones'><i class='fa  fa-exclamation-triangle'></i></button>";
					}else{
						$btn_opts="";
					}
					if($st==0){
						if($isin!=""){
							$cheka="checked='checked'";
						}else{
							$cheka="";
						}
						$active_plat="<input type='checkbox' class='pull-right unival_add_plat_$id_pla sons_$id_cat' $cheka value='1' name='chik_$id_pla' style='width:20px;height:20px;'  onclick=\"cargaHTMLvars('state_proceso','$sender?flag=add_plat_service&id_service=$filterVal&id_pla=$id_pla','','5000','unival_add_plat_$id_pla')\" fn=\"cargaHTMLvars('state_proceso','$sender?flag=add_plat_service&id_service=$filterVal&id_pla=$id_pla','','5000','unival_add_plat_$id_pla')\" />";
						
					}else{
						if($isin!=""){
							$cheka="fa fa-check green";
						}else{
							$cheka="fa fa-remove red";
						}
						$active_plat="<i class='$cheka pull-right'></i>";
					}
					echo $gf->utf8("<li class='list-group-item clearfix platsserv'>$btn_opts $nm_plat <small>$dc_plat</small> $active_plat</li>");
				}
			
			echo $gf->utf8("
					</ul>
				</div>
			  </div>
			</div>");
		}
	echo $gf->utf8("
		</div>
		</div>
		</div>");
	
	
	}elseif($actividad=="config_options"){
		$id_plato=$gf->cleanVar($_GET["id_plato"]);

		$rsPlato=$gf->dataSet("SELECT R.ID_RACION, R.NOMBRE, R.DESCRIPCION, COUNT(O.ID_OPCION) AS OPCIONES FROM platos_composicion R LEFT JOIN racion_opciones O ON R.ID_RACION=O.ID_RACION WHERE R.ID_PLATO=:plato GROUP BY R.ID_RACION ORDER BY R.NOMBRE",array(":plato"=>$id_plato));
		if(count($rsPlato)>0){
			foreach($rsPlato as $rwPlato){
				
				$nmPorcion=$rwPlato["NOMBRE"];
				$dePorcion=$rwPlato["DESCRIPCION"];
				$id_racion=$rwPlato["ID_RACION"];
				$opciones=$rwPlato["OPCIONES"];

				if($opciones>1){
					$nOp=$gf->dataSet("SELECT O.ID_OPCION, O.NOMBRE, O.ESTADO FROM racion_opciones O WHERE O.ID_RACION=:racion GROUP BY O.ID_OPCION ORDER BY O.NOMBRE",array(":racion"=>$id_racion));
					echo $gf->utf8("
					<div class='box box-danger'>
					<div class='box-header'>Opciones disponibles para $nmPorcion</div>
					<div class='box-body'>
					<ul class='list-group'>
					");
					foreach($nOp as $rOp){
						$id_opcion=$rOp["ID_OPCION"];
						$nm_opcion=$rOp["NOMBRE"];
						$state=$rOp["ESTADO"];
						if($state==1){
							$chekka="checked='checked'";
						}else{
							$chekka="";
						}
						
						echo $gf->utf8("<li class='list-group-item'>$nm_opcion <input style='width:25px;height:25px;' name='val_opt_$id_opcion' class='univle_$id_opcion pull-right' value='1' type='checkbox' onclick=\"cargaHTMLvars('state_proceso','$sender?flag=add_opt_serv&id_opcion=$id_opcion','','5000','univle_$id_opcion')\" $chekka /></li>");
					}
					echo $gf->utf8("
					</ul>
					</div>
					</div>");
				}
			}
			
		}else{
			echo $gf->utf8("No se encuentra la porci&oacute;n");
		}

	}elseif($actividad=="propins"){
		$id_servicio=$_SESSION["restservice"];			
		if($_SESSION["restpropina"]>0){
			if($id_servicio>0){
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

				echo $gf->utf8($tabpro);
			}else{
				echo "No hay un servicio activo";
			}
		}else{
			echo "No se ha activado la funcionalidad de control de propinas";
		}


	}elseif($actividad=="add_opt_serv"){
		$id_opcion=$gf->cleanVar($_GET["id_opcion"]);
		$val=$_POST["val_opt_$id_opcion"];
		$gf->dataIn("UPDATE racion_opciones SET ESTADO='$val' WHERE ID_OPCION=:opcion",array(":opcion"=>$id_opcion));
	}elseif($actividad=="include_all"){
		$id_service= $gf->cleanVar($_GET["svc"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$sqla="INSERT IGNORE INTO servicio_oferta (ID_SERVICIO,ID_PLATO) SELECT '$id_service' AS ID_SERVICIO, ID_PLATO FROM platos WHERE ID_CATEGORIA IN(SELECT ID_CATEGORIA FROM platos_categorias WHERE ID_SITIO='{$_SESSION["restbus"]}' ORDER BY ID_CATEGORIA) AND ESTADO=1 ORDER BY ID_PLATO";
		$oka=$gf->dataIn($sqla);
		if($oka){
			echo "Se han incluido todos los platos en el servicio<br />
			<button class='btn btn-succes btn-sm' onclick=\"cargaHTMLvars('level3','$sender?flag=level3&key=$id_service&st=0');closeD('$rnd')\">Ok</button>
			";
		}else{
			echo "Hubo un problema al realizar el proceso";
		}

	}elseif($actividad=="add_plat_service"){
		$id_service= $gf->cleanVar($_GET["id_service"]);
		$id_plato=$gf->cleanVar($_GET["id_pla"]);
		$val=$_POST["chik_$id_plato"];
		if($val==1){
			$sqla="INSERT IGNORE INTO servicio_oferta (ID_SERVICIO,ID_PLATO) VALUES ('$id_service','$id_plato')";
		}else{
			$sqla="DELETE FROM servicio_oferta WHERE ID_SERVICIO='$id_service' AND ID_PLATO='$id_plato'";
		}
		
		$oka=$gf->dataIn($sqla);
		if($oka){
			echo 1;
		}else{
			echo 0;
		}

	
	}elseif($actividad=="editar"){
		$Vkey=$gf->cleanVar($_GET["Vkey"]);
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		$fn="getAux(\'$sender?flag=start&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')";
		$gettabla = $dataTables->devuelveTablaEditItemDyRel("servicio",$Vkey,$filterKey,$filterVal,$dialogo,$fn);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="nuevo"){
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		$curServ=$gf->dataSet("SELECT ID_SERVICIO FROM servicio WHERE ESTADO<2 AND ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		if(count($curServ)==0){
			$fn="getAux(\'$sender?flag=start&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')";
			$gettabla = $dataTables->devuelveTablaNewItemDyRel("servicio",$filterKey,$filterVal,$dialogo,$fn);
			echo $gf->utf8($gettabla);
			$gf->log($_SESSION["restbus"],0,0,"AGREGAR SERVICIO",$_SESSION["restuiduser"]);
		}else{
			echo "No se puede crear un nuevo servicio teniendo uno sin cuadre de caja";
			$gf->log($_SESSION["restbus"],0,0,"INTENTO DE ABRIR SERVICIO SIN CERRAR EL ANTERIOR",$_SESSION["restuiduser"]);
		}
	}else{
		echo "Ninguna solicitud";
	}
	
}else{
	echo "No has iniciado sesion!";
}
?>