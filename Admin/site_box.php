<?php
ini_set("display_errors",1);
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="J" || $_SESSION["restprofile"]=="A")){
	
	require_once("../autoload.php");

	$gf=new generalFunctions;
	$actividad=$gf->cleanVar($_GET["flag"]);
	$infoEmpresa=$gf->dataSet("SELECT ANTICIPOS, CL_COMODIN FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
	if(count($infoEmpresa)>0){
		$_SESSION["restanticipos"] = $infoEmpresa[0]["ANTICIPOS"];
		$__CL_COMODIN=$infoEmpresa[0]["CL_COMODIN"];
	}

	$_SESSION["restfastmode"]=0;

	$sv=$gf->dataSet("SELECT ID_SERVICIO FROM servicio WHERE ESTADO<2 ORDER BY ID_SERVICIO");
	if(count($sv)==0){
		echo $gf->utf8("
		<div class='alert alert-danger alert-dismissible'>
		<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>x</button>
		<h4><i class='icon fa fa-ban'></i> ATENCI&Oacute;N!</h4>
		No hay un servicio activo o en proceso de cuadre, contacta el administrador para activar el servicio
		</div>
		");
		exit;
	}

	global $relaciones;
	$sender=$_SERVER["PHP_SELF"];
	$dataTables=new dsTables();
	if($actividad=="fact_start"){
		
		
		$curServ=$gf->dataSet("SELECT ID_SERVICIO, BASE_CAJA FROM servicio WHERE ESTADO=0 AND ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		if(count($curServ)>0){
			$_SESSION["restservice"]=$curServ[0]["ID_SERVICIO"];
			$_SESSION["base_caja"]=$curServ[0]["BASE_CAJA"];
		}else{
			echo $gf->utf8("Hola!, no se encontr&oacute; un servicio activo, contacta al administrador para abrir el servicio de hoy");
			$_SESSION["restservice"]=0;
		}
		

		$resGrupos=$gf->dataSet("SELECT ID_GRUPO, NOMBRE, COLOR FROM mesas_grupos WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));

		if(count($resGrupos)>1){
			echo $gf->utf8("<div class='row'><div class='col-md-12 flexbox'>");
			echo $gf->utf8("<button data-filter='all' class='btn btn-default btn-md btnfiltershome'>TODO</button>");
			foreach($resGrupos as $grup){
				$idgr=$grup["ID_GRUPO"];
				$nombregr=$grup["NOMBRE"];
				echo $gf->utf8("<button data-filter='.grupi_$idgr' class='btn btn-default btn-md btnfiltershome'>$nombregr</button>");
			}
			echo $gf->utf8("</div></div><hr />");
		}
		echo $gf->utf8("<div class='row'><div class='col-md-12 flexbox'>");
		$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.NOMBRE, M.TIPO, P.ID_PEDIDO, P.DIRECCION, P.CHEF, P.CAJA, U.NOMBRES AS TENDER, P.DENOM, P.PAGADO, SUM(SP.PRECIO * SP.CANTIDAD) AS TOTAL FROM mesas AS M RIGHT JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA) JOIN usuarios U ON U.ID_USUARIO=P.ID_TENDER JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO JOIN sillas_platos SP ON SP.ID_SILLA=S.ID_SILLA WHERE P.ID_SERVICIO='{$_SESSION["restservice"]}' AND P.CHEF<>'0000-00-00 00:00:00' AND P.CIERRE='0000-00-00 00:00:00' AND P.PAGO='0' GROUP BY P.ID_PEDIDO ORDER BY M.ID_MESA, P.ID_PEDIDO");
		
		if(count($resultInt)>0){
			foreach($resultInt as $rowInt){
				$acumbase=0;
				$acumprice=0;
				$acumimp=0;
				$id_mesa=$rowInt["ID_MESA"];
				$nombre=$rowInt["NOMBRE"];
				$m_tipo=$rowInt["TIPO"];
				$tipo=$rowInt["TIPO"];
				$perc=100;
				$dispatch=1;
				$direccion=$rowInt["DIRECCION"];
				if(strlen($direccion)>24){
					$direccion_tin=substr($direccion,0,24)."...";
				}else{
					$direccion_tin=$direccion;
				}
				
				$id_pedido=$rowInt["ID_PEDIDO"];
				$caja=$rowInt["CAJA"];
				$chef=$rowInt["CHEF"];
				$tender=$rowInt["TENDER"];
				$denom=$rowInt["DENOM"];
				$payd=$rowInt["PAGADO"];
				$total=$rowInt["TOTAL"];
				if($payd==1){
					$pagado="PAGO";
				}else{
					$pagado="DEBE";
				}
				if($m_tipo=="D"){
					$camprecio="PRECIO_DOM";
				}else{
					$camprecio="PRECIO";
				}
				echo $gf->utf8("<input type='hidden' id='boxviewbox' />");
				$nsi=0;
				$inisill=0;
	
				$icon="fa-hourglass-2";
				$op=1;
				$classd='bg-red';
				if($chef!="0000-00-00 00:00:00"){
					$icon="fa-fire";
					$classd="bg-orange";
				}
				if($caja!="0000-00-00 00:00:00"){
					$icon="fa-dollar";
					$classd="bg-yellow";
				}

				$progress="
				<div class='progress'>
					<div class='progress-bar' id='mesa_progress_$id_mesa' style='width: 100%;'></div>
					</div>";
				$onclik="onclick=\"cargaHTMLvars('contenidos','$sender?flag=opentable&id_mesa=$id_mesa&id_pedido=$id_pedido')\" lnk-tsf='#cobrar-pedido-$id_pedido' lnk-cont='contenidos'";
				if($dispatch==1){
					$moto="<i class='fa fa-motorcycle' style='color:black;'></i>";
				}else{
					$moto="";
				}
				
				if($tipo=="D"){
				
					$elicon="<i class='fa fa-motorcycle' id='mesa_icon_$id_mesa'></i>";
					$dv="D:$denom $pagado";
				}else{
		
					$mesan=str_replace("MESA ","",$nombre);
					$elicon="<i class='fa fa-qrcode' id='mesa_icon_$id_mesa'></i>";
					$dv="";
				}
				
				
				echo $gf->utf8("
					
					
					<div class='col-md-4 col-sm-6 col-xs-12 lasmesas data-filtrable' id='tbl_$id_mesa' idm='$id_mesa' $onclik>
						<div class='info-box $classd shadow link-cnv miniround material-ripple' id='infoboxa_$id_mesa'>
						<span class='info-box-icon' style='font-size:1.1em;line-height: 15px;padding-top:25px;'>$elicon<br> $nombre</span>

						<div class='info-box-content'>
							<span class='info-box-text'>P.$id_pedido $nombre <small class='pull-right' style='font-size:11px;'>$tender</small></span>
							<span class='info-box-number' id='mesa_sillas_$id_mesa'>$nsi <small class='pull-right'>$".number_format($total,0)."</small></span>

							$progress
							<span class='progress-description' id='pg_description_k_$id_mesa'>
							<span class='ellip' title='$direccion'>$direccion_tin</span> <small class='pull-right'>$dv</small>
							</span>
						</div>
						</div>
					</div>
					");
				
				
				
				
			}
			echo $gf->utf8("</div></div>");
		}else{
			$rsMesa=$gf->dataSet("SELECT M.ID_MESA, P.ID_PEDIDO, P.CHEF FROM mesas M LEFT JOIN pedidos P ON M.ID_MESA=P.ID_MESA AND P.ID_SERVICIO='{$_SESSION["restservice"]}' AND P.CIERRE='0000-00-00 00:00:00' WHERE M.ID_SITIO='{$_SESSION["restbus"]}' AND M.TIPO<>'D'");
			$mesas_libres=0;
			$mesas_chef=0;
			$mesas_activas=0;
			if(count($rsMesa)>0){
				foreach($rsMesa as $rwMesa){
					$id_mesa=$rwMesa["ID_MESA"];
					$pedido=$rwMesa["ID_PEDIDO"];
					$chef=$rwMesa["CHEF"];
					if($pedido>0){
						if($chef=="0000-00-00 00:00:00"){
							$mesas_activas++;
						}else{
							$mesas_chef++;
						}
					}else{
						$mesas_libres++;
					}
				}
			}
			echo $gf->utf8("
			<input type='hidden' id='boxviewbox' />
			<div class='col-md-4'>
			  <div class='box box-widget widget-user'>
				<div class='widget-user-header bg-aqua-active'>
				  <h3 class='widget-user-username'>{$_SESSION["restbusname"]}</h3>
				  <h5 class='widget-user-desc'>Estado del servicio</h5>
				</div>
				<div class='widget-user-image'>
				  <img class='img-circle' src='{$_SESSION["restbuslogo"]}' alt='User Avatar'>
				</div>
				<div class='box-footer'>
				  <div class='row'>
					<div class='col-sm-4 border-right'>
					  <div class='description-block'>
						<h5 class='description-header'>$mesas_activas</h5>
						<span class='description-text'>EN PEDIDO</span>
					  </div>
					</div>
					<div class='col-sm-4 border-right'>
					  <div class='description-block'>
						<h5 class='description-header'>$mesas_chef</h5>
						<span class='description-text'>EN COCINA</span>
					  </div>
					</div>
					<div class='col-sm-4 border-right'>
					  <div class='description-block'>
						<h5 class='description-header'>$mesas_libres</h5>
						<span class='description-text'>LIBRES</span>
					  </div>
					</div>
				  </div>
				</div>
			  </div>
			</div>
			
			
			");
		}




	}elseif($actividad=="pedi_start"){

			
		$curServ=$gf->dataSet("SELECT ID_SERVICIO, BASE_CAJA FROM servicio WHERE ESTADO=0 AND ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		if(count($curServ)>0){
			$_SESSION["restservice"]=$curServ[0]["ID_SERVICIO"];
			$_SESSION["base_caja"]=$curServ[0]["BASE_CAJA"];
		}else{
			echo $gf->utf8("Hola!, no se encontr&oacute; un servicio activo, contacta al administrador para abrir el servicio de hoy");
		}
		

		$resGrupos=$gf->dataSet("SELECT ID_GRUPO, NOMBRE, COLOR FROM mesas_grupos WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));

		if(count($resGrupos)>1){
			echo $gf->utf8("<div class='row'><div class='col-md-12 flexbox'>");
			echo $gf->utf8("<button data-filter='all' class='btn btn-default btn-md btnfiltershome'>TODO</button>");
			foreach($resGrupos as $grup){
				$idgr=$grup["ID_GRUPO"];
				$nombregr=$grup["NOMBRE"];
				echo $gf->utf8("<button data-filter='.grupi_$idgr' class='btn btn-default btn-md btnfiltershome'>$nombregr</button>");
			}
			echo $gf->utf8("</div></div><hr />");
		}
		echo $gf->utf8("<div class='row'><div class='col-md-12 flexbox'>");
		$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.NOMBRE, M.TIPO, P.ID_PEDIDO, P.DIRECCION, P.CHEF, P.CAJA, U.NOMBRES AS TENDER, P.DENOM, P.PAGADO FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CHEF<>'0000-00-00 00:00:00' AND P.CIERRE='0000-00-00 00:00:00' AND P.PAGO='0') JOIN usuarios U ON U.ID_USUARIO=P.ID_TENDER WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='{$_SESSION["restservice"]}'  GROUP BY P.ID_PEDIDO ORDER BY M.ID_MESA, P.ID_PEDIDO");
		
		if(count($resultInt)>0){
			foreach($resultInt as $rowInt){
				$acumbase=0;
				$acumprice=0;
				$acumimp=0;
				$id_mesa=$rowInt["ID_MESA"];
				$nombre=$rowInt["NOMBRE"];
				$m_tipo=$rowInt["TIPO"];
				$tipo=$rowInt["TIPO"];
				$perc=100;
				$dispatch=1;
				$direccion=$rowInt["DIRECCION"];
				$id_pedido=$rowInt["ID_PEDIDO"];
				$caja=$rowInt["CAJA"];
				$chef=$rowInt["CHEF"];
				$tender=$rowInt["TENDER"];
				$denom=$rowInt["DENOM"];
				$payd=$rowInt["PAGADO"];
				if($payd==1){
					$pagado="PAGO";
				}else{
					$pagado="DEBE";
				}
				if($m_tipo=="D"){
					$camprecio="PRECIO_DOM";
				}else{
					$camprecio="PRECIO";
				}
				echo $gf->utf8("<input type='hidden' id='boxviewbox' />");
				$nsi=0;
				$inisill=0;
				$resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, SP.CANTIDAD, SP.LISTO, P.NOMBRE, P.DESCRIPCION, P.$camprecio AS PRECIO, SUM(P.$camprecio/(1+(IM.PORCENTAJE/100))) AS IMPUESTO FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN impuestos IM ON IM.ID_IMPUESTO=P.ID_IMPUESTO WHERE S.ID_PEDIDO='$id_pedido' GROUP BY SP.ID_ITEM ORDER BY S.ID_SILLA");
				if(count($resultChairs)>0){
					
					$bklass="ui-semiwhite";
					foreach($resultChairs as $rwChair){
						
						$id_item=$rwChair["ID_ITEM"];
						$id_silla=$rwChair["ID_SILLA"];
						$observacion=$rwChair["OBSERVACION"];
						$cantidad=$rwChair["CANTIDAD"];
						$nombre_plato=$rwChair["NOMBRE"];
						$descripcion=$rwChair["DESCRIPCION"];
						$listo=$rwChair["LISTO"];
						$precio=$rwChair["PRECIO"];
						$impuestos=$rwChair["IMPUESTO"];
						if($impuestos=="") $impuestos=0;
						
						if($_SESSION["restbusstyle"]==1){
							$precio_base=$precio-$impuestos;
						}else{
							$precio_base=$precio;
						}
						$acumbase+=($precio_base*$cantidad);
						$acumimp+=($impuestos*$cantidad);
						$precio_total=$precio_base+$impuestos;
						$acumprice+=($precio_total*$cantidad);
						if($id_silla!=$inisill){
							$nsi++;
							$inisill=$id_silla;
						}

						$inisill=$id_silla;
					}
					$sillas=$nsi;
					
				
					$icon="fa-hourglass-2";
					$op=1;
					$classd='bg-red';
					if($chef!="0000-00-00 00:00:00"){
						$icon="fa-fire";
						$classd="bg-orange";
					}
					if($caja!="0000-00-00 00:00:00"){
						$icon="fa-dollar";
						$classd="bg-yellow";
					}
					if($tipo!="D"){
						$chair="$sillas sillas";
						$avante="$perc% Avance del pedido";
					}else{
						$chair="";
						$avante="";
					}
					
					$progress="
					<div class='progress'>
						<div class='progress-bar' id='mesa_progress_$id_mesa' style='width: 100%;'></div>
					  </div>";
					$onclik="onclick=\"cargaHTMLvars('contenidos','mviews.php?flag=opentable&id_mesa=$id_mesa&id_pedido=$id_pedido')\" lnk-tsf='#proceso-pedido-$id_pedido' lnk-cont='contenidos'";
					if($dispatch==1){
						$moto="<i class='fa fa-motorcycle' style='color:black;'></i>";
					}else{
						$moto="";
					}
					
					if($tipo=="D"){
					
						$elicon="<i class='fa fa-motorcycle' id='mesa_icon_$id_mesa'></i>";
						$dv="D:$denom $pagado";
					}else{
			
						$mesan=str_replace("MESA ","",$nombre);
						$elicon="<i class='fa fa-qrcode' id='mesa_icon_$id_mesa'></i>";
						$dv="";
					}
					
					
					echo $gf->utf8("
						
						
						<div class='col-md-4 col-sm-6 col-xs-12 lasmesas data-filtrable' id='tbl_$id_mesa' idm='$id_mesa' $onclik>
						  <div class='info-box $classd shadow link-cnv miniround material-ripple' id='infoboxa_$id_mesa'>
							<span class='info-box-icon' style='font-size:1.1em;line-height: 15px;padding-top:25px;'>$elicon<br>$nombre</span>
							<div class='info-box-content'>
							  <span class='info-box-text'>P.$id_pedido $nombre <small class='pull-right' style='font-size:11px;'>$tender</small></span>
							  <span class='info-box-number' id='mesa_sillas_$id_mesa'>$nsi <small class='pull-right'>$".number_format($acumprice,0)."</small></span>

							 $progress
							  <span class='progress-description' id='pg_description_k_$id_mesa'>
								$direccion <small class='pull-right'>$dv</small>
							  </span>
							</div>
						  </div>
						</div>
						");
					
					
					
				}
			}
			echo $gf->utf8("</div></div>");
		}else{
			$rsMesa=$gf->dataSet("SELECT M.ID_MESA, P.ID_PEDIDO, P.CHEF FROM mesas M LEFT JOIN pedidos P ON M.ID_MESA=P.ID_MESA AND P.ID_SERVICIO='{$_SESSION["restservice"]}' AND P.CIERRE='0000-00-00 00:00:00' WHERE M.ID_SITIO='{$_SESSION["restbus"]}'");
			$mesas_libres=0;
			$mesas_chef=0;
			$mesas_activas=0;
			if(count($rsMesa)>0){
				foreach($rsMesa as $rwMesa){
					$id_mesa=$rwMesa["ID_MESA"];
					$pedido=$rwMesa["ID_PEDIDO"];
					$chef=$rwMesa["CHEF"];
					if($pedido>0){
						if($chef=="0000-00-00 00:00:00"){
							$mesas_activas++;
						}else{
							$mesas_chef++;
						}
					}else{
						$mesas_libres++;
					}
				}
			}
			echo $gf->utf8("
			<input type='hidden' id='boxviewbox' />
			<div class='col-md-4'>
			  <div class='box box-widget widget-user'>
				<div class='widget-user-header bg-aqua-active'>
				  <h3 class='widget-user-username'>{$_SESSION["restbusname"]}</h3>
				  <h5 class='widget-user-desc'>Estado del servicio</h5>
				</div>
				<div class='widget-user-image'>
				  <img class='img-circle' src='{$_SESSION["restbuslogo"]}' alt='User Avatar'>
				</div>
				<div class='box-footer'>
				  <div class='row'>
					<div class='col-sm-4 border-right'>
					  <div class='description-block'>
						<h5 class='description-header'>$mesas_activas</h5>
						<span class='description-text'>EN PEDIDO</span>
					  </div>
					</div>
					<div class='col-sm-4 border-right'>
					  <div class='description-block'>
						<h5 class='description-header'>$mesas_chef</h5>
						<span class='description-text'>EN COCINA</span>
					  </div>
					</div>
					<div class='col-sm-4 border-right'>
					  <div class='description-block'>
						<h5 class='description-header'>$mesas_libres</h5>
						<span class='description-text'>LIBRES</span>
					  </div>
					</div>
				  </div>
				</div>
			  </div>
			</div>
			
			
			");
		}


	}elseif($actividad=="opentable"){
		$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
		$id_pedido=$gf->cleanVar($_GET["id_pedido"]);

		$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.NOMBRE, M.TIPO, P.ID_PEDIDO, P.DIRECCION, P.CHEF, P.CAJA, U.NOMBRES AS TENDER FROM mesas AS M RIGHT JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CHEF<>'0000-00-00 00:00:00' AND P.CIERRE='0000-00-00 00:00:00') LEFT JOIN usuarios U ON U.ID_USUARIO=P.ID_TENDER WHERE P.ID_SERVICIO='{$_SESSION["restservice"]}' AND P.ID_PEDIDO='$id_pedido'  GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
		
		
		
		if(count($resultInt)>0){
			foreach($resultInt as $rowInt){
				$acumbase=0;
				$acumprice=0;
				$acumimp=0;
				$id_mesa=$rowInt["ID_MESA"];
				$nombre=$rowInt["NOMBRE"];
				$m_tipo=$rowInt["TIPO"];
				$tipo=$rowInt["TIPO"];
				$perc=100;
				$dispatch=1;
				$direccion=$rowInt["DIRECCION"];
				$id_pedido=$rowInt["ID_PEDIDO"];
				$caja=$rowInt["CAJA"];
				$chef=$rowInt["CHEF"];
				$tender=$rowInt["TENDER"];
				
				if($m_tipo=="D"){
					$camprecio="PRECIO_DOM";
					//$modify="<button class='btn btn-xs btn-warning'	onclick=\"cargaHTMLvars('contenidos','mviews.php?flag=opentable&id_mesa=$id_mesa&id_pedido=$id_pedido')\" lnk-tsf='#proceso-pedido-$id_pedido' lnk-cont='contenidos'><i class='fa fa-edit'></i></button>";
					$modify="<button class='btn btn-xs btn-warning'	onclick=\"localPedido('$id_pedido')\" lnk-tsf='#proceso-pedido-$id_pedido' lnk-cont='df-pedido'><i class='fa fa-edit'></i></button>";
				}else{
					$camprecio="PRECIO";
					$modify="";
				}


				echo $gf->utf8("<div class='box box-danger'><div class='box-header'>$nombre - ID $id_pedido  $direccion <button class='btn btn-xs btn-danger pull-right' onclick=\"javascript:history.back()\"><i class='fa fa-remove'></i></button> $modify</div><div class='box-body'><table class='table table-bordered'>");
				$nsi=0;
				$inisill=0;
				$resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, SP.CANTIDAD, SP.LISTO, P.NOMBRE, P.DESCRIPCION, SP.PRECIO AS PRECIO, (SP.PRECIO/(1+(I.PORCENTAJE/100))) AS PREBASE FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN impuestos I ON P.ID_IMPUESTO=I.ID_IMPUESTO WHERE S.ID_PEDIDO='$id_pedido' GROUP BY SP.ID_ITEM ORDER BY S.ID_SILLA");
				if(count($resultChairs)>0){
					
					$bklass="ui-semiwhite";
					$tot_desc=0;
					foreach($resultChairs as $rwChair){
						
						$id_item=$rwChair["ID_ITEM"];
						$id_silla=$rwChair["ID_SILLA"];
						$observacion=$rwChair["OBSERVACION"];
						$cantidad=$rwChair["CANTIDAD"];
						$nombre_plato=$rwChair["NOMBRE"];
						$descripcion=$rwChair["DESCRIPCION"];
						$listo=$rwChair["LISTO"];
						$precio=$rwChair["PRECIO"];
						$precio_base=$rwChair["PREBASE"];
						if($precio_base=="") $precio_base=0;
						
						$rsDesc=$gf->dataSet("SELECT SUM(PC.BAJA_PRECIO) AS DESCUENTA FROM  sillas_platos_composicion SPC LEFT JOIN platos_composicion AS PC ON PC.ID_RACION=SPC.ID_RACION AND PC.BAJA_PRECIO>0 AND SPC.ESTADO='0' WHERE SPC.ID_ITEM='$id_item' GROUP BY SPC.ID_ITEM");
						if(count($rsDesc)>0){
							$desi = $rsDesc[0]["DESCUENTA"];
							if($desi=="") $desi=0;
							$descuenta= $desi * $cantidad;
						}else{
							$descuenta=0;
						}
						$tot_desc+=$descuenta;

						if($precio_base==0){
							$precio_base=$precio;
							$impuestos=0;
						}else{
							$impuestos=$precio-$precio_base;
						}
							
						
						$acumbase+=($precio_base*$cantidad);
						$acumimp+=($impuestos*$cantidad);
						$precio_total=$precio_base+$impuestos;
						$acumprice+=($precio_total*$cantidad);
						if($id_silla!=$inisill){
							$nsi++;
							$inisill=$id_silla;
						}
						echo $gf->utf8("<tr>
						<td width='10%'>Silla $nsi</td>
						<td width='40%'>$nombre_plato</td>
						<td width='30%'>$descripcion</td>
						<td width='5%' align='right'>$cantidad</td>
						<td width='15%' align='right'>".number_format(($precio_base*$cantidad),0)."</td>
						</tr>");
						$inisill=$id_silla;
					}
					$sillas=$nsi;
				
					if(isset($_GET["callback"])){
						$callback="cargaHTMLvars(\'contenidos\',\'mviews.php?flag=home\')";
					}else{
						$callback="goBack()";
					}
					
					
					
					echo $gf->utf8("<tr><td colspan='4' align='right'>TOTAL</td><td align='right'>".number_format($acumbase,0)."</td></tr>");
					echo $gf->utf8("<tr><td colspan='4' align='right'>IMPUESTO</td><td align='right'>".number_format($acumimp,0)."</td></tr>");
					echo $gf->utf8("<tr><td colspan='4' align='right'>TOTAL</td><td align='right'>".number_format($acumprice,0)."</td></tr>");
					echo $gf->utf8("<tr><td colspan='4' align='right'>DESCUENTO POR COMPONENTES</td><td align='right'>".number_format($tot_desc,0)."
					
					<input type='hidden' id='discunt_$id_pedido' name='discont_$id_pedido' class='form-control unival_dix_$id_pedido' value='0' onchange=\"calcCaja('$id_pedido')\" />
					<input type='hidden' id='discuntcpt_$id_pedido' name='discuntcpt_$id_pedido' class='form-control unival_dix_$id_pedido' value='$tot_desc' />
					<input type='hidden' id='tot_ped_cax_$id_pedido' name='tot_ped_cax_$id_pedido' class='unival_dix_$id_pedido' value='$acumprice' />

					<input type='hidden' id='tot_ped_bax_$id_pedido' name='tot_ped_bax_$id_pedido' class='unival_dix_$id_pedido' value='$acumbase' />
					</td></tr>");
					
					echo $gf->utf8("<tr><td colspan='5' align='right'><h4 id='receip_cax_$id_pedido'>$".number_format($acumprice-$tot_desc,0)."</h4></td></tr>");

					if($_SESSION["restanticipos"]==1){

						$rsAbonos=$gf->dataSet("SELECT A.ID_ABONO, A.FECHA, A.VALOR, A.OBSERVACION, U.NOMBRES AS USUARIO FROM pedidos_abonos A JOIN usuarios U ON U.ID_USUARIO=A.ID_USUARIO WHERE A.ID_PEDIDO='$id_pedido'");
						$tot_abonos=0;
						$abos="";
						if(count($rsAbonos)>0){
							$tot_abonos=0;
							foreach($rsAbonos as $rwAbonos){
								$id_abono=$rwAbonos["ID_ABONO"];
								$fc_abono=$rwAbonos["FECHA"];
								$vl_abono=$rwAbonos["VALOR"];
								$ob_abono=$rwAbonos["OBSERVACION"];
								$us_abono=$rwAbonos["USUARIO"];
								//$abos.="<tr><td colspan='4' align='right'>$fc_abono ($us_abono): $ob_abono</td><td align='right'>$".number_format($vl_abono,0)."</td></tr>";
								$tot_abonos+=$vl_abono;
							}
						}
						$abos="<tr><td colspan='4' class='bg-warning' align='right'><button class='btn btn-xs btn-warning' onclick=\"getDialog('$sender?flag=add_abono&id_pedido=$id_pedido','500','Agregar\ Abono','','','reloaHash()')\" title='Agregar anticipo'><i class='fa fa-plus'></i></button> ANTICIPOS </td><td align='right'><button class='btn btn-sm btn-warning pull-right' onclick=\"getDialog('$sender?flag=ver_abonos&id_pedido=$id_pedido','600','Abonos\ Cuenta','','','reloaHash()')\">$ ".number_format($tot_abonos)."</button></td></tr>";
						echo $gf->utf8($abos);
						if($tot_abonos>0){
							$total_gral=$acumprice-$tot_desc-$tot_abonos;
							echo $gf->utf8("<tr><td colspan='4' align='right'><h4>DEBE</h4></td><td align='right'>".number_format($total_gral,0)."</td></tr>");
						}
						
					}
					//echo $gf->utf8("<tr><td colspan='5'><hr /></td></tr>");


					echo $gf->utf8("<tr><td colspan='5' align='center'>
					
					<button  class='btn btn-default pull-left' onclick=\"getDialog('$sender?flag=printcom&id_pedido=$id_pedido','200','Imprimir')\"><i class='fa fa-print'></i> Imprimir comanda</button>

					<button  class='btn btn-default pull-left' onclick=\"getDialog('$sender?flag=printpre&id_pedido=$id_pedido&id_mesa=$id_mesa','200','Imprimir')\"><i class='fa fa-print'></i> Precuenta</button>
					
					<!--<button  class='btn btn-default pull-left' onclick=\"getDialog('$sender?flag=print_precuenta&id_pedido=$id_pedido','200','Imprimir')\"><i class='fa fa-print'></i> Precuenta</button>-->

					

					<button  class='btn btn-warning' onclick=\"getDialog('$sender?flag=fact_pedido1&id_ped=$id_pedido','1200','Facturar','','','$callback','unival_dix_$id_pedido')\"><i class='fa fa-dollar'></i> Facturar</button>
					
					<button  class='btn btn-danger' onclick=\"getDialog('$sender?flag=fact_pedido_part1_v2&id_ped=$id_pedido','1200','Facturar','','','$callback','unival_dix_$id_pedido')\"><i class='fa fa-dollar'></i> Dividir y Facturar</button>

					</td></tr>");
					echo $gf->utf8("</table>");
					
					
					
				}
				echo $gf->utf8("</div></div>");
			}
		}else{
			echo "<div class='alert alert-warning'>No se encuentra el pedido, verifique que haya sido enviado</div>";
		}
	}elseif($actividad=="ver_abonos"){
		$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
		$rsAbonos=$gf->dataSet("SELECT A.ID_ABONO, A.FECHA, A.VALOR, A.OBSERVACION, A.REFERENCIA, U.NOMBRES AS USUARIO FROM pedidos_abonos A JOIN usuarios U ON U.ID_USUARIO=A.ID_USUARIO WHERE A.ID_PEDIDO='$id_pedido'");
		$tot_abonos=0;
		$abos="<table class='table table-bordered'><thead><tr class='bg-warning'><td>ID</td><td>FECHA</td><td>REGISTRADO POR</td><td>OBSERVACI&Oacute;N</td><td>VAUCHER O REF</td><td>VALOR</td></tr></thead><tbody>";
		if(count($rsAbonos)>0){
			$tot_abonos=0;
			foreach($rsAbonos as $rwAbonos){
				$id_abono=$rwAbonos["ID_ABONO"];
				$fc_abono=$rwAbonos["FECHA"];
				$vl_abono=$rwAbonos["VALOR"];
				$ob_abono=$rwAbonos["OBSERVACION"];
				$us_abono=$rwAbonos["USUARIO"];
				$ref_abono=$rwAbonos["REFERENCIA"];
				$btn_del="";
				if($_SESSION["restprofile"]=="A"){
					$btn_del="<button class='btn btn-minier btn-danger' onclick=\"goErase('pedidos_abonos','ID_ABONO','$id_abono','trabono_$id_abono','1')\"><i class='fa fa-trash'></i></button>";
				}
				$abos.="<tr id='trabono_$id_abono'><td>$id_abono $btn_del</td><td>$fc_abono</td><td>$us_abono</td><td>$ob_abono</td><td>$ref_abono</td><td align='right'>$".number_format($vl_abono,0)."</td></tr>";
				$tot_abonos+=$vl_abono;
			}
		}
		$abos.="<tr><td colspan='5' class='bg-warning' align='right'>TOTAL</td><td align='right' class='bg-danger'>$ ".number_format($tot_abonos,0)."</td></tr></tbody></table><br />
		<button class='btn btn-minier btn-info' onclick=\"getDialog('$sender?flag=print_abono&id_pedido=$id_pedido','300','Imprimir')\"><i class='fa fa-print'></i> Imprimir comprobante de anticipos</button>
		
		";
		echo $gf->utf8($abos);
	}elseif($actividad=="fact_pedido_part1"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		echo $gf->utf8("Se va a dividir esta cuenta para generar varias facturas, ingresa el n&uacute;mero de subdivisiones");
		if($_SESSION["restchairs"]==1){
			$ssill=$gf->dataSet("SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido'");
			$ndef=count($ssill);
			if($ndef==1) $ndef=2;
		}else{
			$ndef=2;
		}
		echo $gf->utf8("
			<div class='input-group'>
				<span class='input-group-addon btn btn-app btn-danger' onclick=\"downNumber('nchairs')\"><i class='fa fa-arrow-down'></i></span>
				<input type='number' style='font-size:30px;height:60px;text-align:center;' class='form-control univalunichair' id='nchairs' name='nchairs'  min='2' value='$ndef' max='5' />
				<span class='input-group-addon btn-app btn btn-success' onclick=\"upNumber('nchairs')\"><i class='fa fa-arrow-up'></i></span>
			</div>

			<hr />

			<button  class='btn btn-warning' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=fact_pedido_part2&id_ped=$id_pedido&rnd=$rnd','','5000','univalunichair')\"><i class='fa fa-dollar'></i> Dividir y Facturar</button>


		");
		
	}elseif($actividad=="fact_pedido_part1_v2"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		//$nsub=$_POST["nchairs"];

		$formas=$gf->dataSet("SELECT ID_FP, NOMBRE, ICONO FROM formas_pago WHERE ID_SITIO=:sitio ORDER BY POSICION, NOMBRE",array(":sitio"=>$_SESSION["restbus"]));
		$formas_select="";
		if(count($formas)>0){
			foreach($formas as $fpa){
				$idfp=$fpa["ID_FP"];
				$nombrefp=$fpa["NOMBRE"];
				$formas_select.="<option value='$idfp'>$nombrefp</option>";
			}
		}



		$rsPed=$gf->dataSet("SELECT ID_ITEM, ID_SILLA, ID_PLATO, CANTIDAD, FENTREGA, PRECIO FROM sillas_platos WHERE ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO=:pedido ORDER BY ID_SILLA) AND CANTIDAD>1 ORDER BY ID_ITEM",array(":pedido"=>$id_pedido));
		if(count($rsPed)>0){
			foreach($rsPed as $rwPed){
				$id_item=$rwPed["ID_ITEM"];
				$id_silla=$rwPed["ID_SILLA"];
				$id_plato=$rwPed["ID_PLATO"];
				$cantidad=$rwPed["CANTIDAD"];
				$fentrega=$rwPed["FENTREGA"];
				$precio=$rwPed["PRECIO"];
				for($s=2;$s<=$cantidad;$s++){
					$id_newitem=$gf->dataInLast("INSERT INTO sillas_platos (ID_SILLA,ID_PLATO,CANTIDAD,ENTREGADO,FENTREGA,PRINTED,PRECIO) VALUES (:silla,:plato,:cantidad,:entregado,:fentrega,:printed,:precio)",array(":silla"=>$id_silla,":plato"=>$id_plato,":cantidad"=>1,":entregado"=>1,":fentrega"=>$fentrega,":printed"=>1,":precio"=>$precio));
					$gf->dataIn("INSERT INTO sillas_platos_composicion (ID_ITEM,ID_RACION,ESTADO,ID_OPCION) SELECT '$id_newitem' AS ID_ITEM,ID_RACION,ESTADO,ID_OPCION FROM sillas_platos_composicion WHERE ID_ITEM='$id_item'");
				}
				$gf->dataIn("UPDATE sillas_platos SET CANTIDAD=1 WHERE ID_ITEM=:item",array(":item"=>$id_item));
			}
		}

		echo $gf->utf8("
			<div class='row'>
				<div class='col-md-3' style='max-height:230px;overflow:auto;'>
					<ul class='list-group' id='grupoinicial_pro'>
					");
					$rsPed=$gf->dataSet("SELECT I.ID_ITEM, I.ID_SILLA, I.ID_PLATO, I.CANTIDAD, I.FENTREGA, P.NOMBRE AS PLATO, P.PRECIO FROM sillas_platos I JOIN platos P ON P.ID_PLATO=I.ID_PLATO WHERE I.ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO=:pedido ORDER BY ID_SILLA) ORDER BY I.ID_ITEM",array(":pedido"=>$id_pedido));
					if(count($rsPed)>0){
						foreach($rsPed as $rwPed){
							$id_item=$rwPed["ID_ITEM"];
							$plato=$rwPed["PLATO"];
							$plato_pz=$rwPed["PRECIO"];
							echo $gf->utf8("<li idi='$id_item' class='list-group-item platos_to_divide clearfix'><i class='fa fa-arrows'></i><input type='hidden' id='pareto_$id_item' name='item_$id_item' class='unival_dix_$id_pedido' value='0' /> $plato<small class='pull-right prz'>$plato_pz</small></li>");
						}
					}
					echo $gf->utf8("
					</ul>
				</div>
				<div class='col-md-9' id='facturados_n'>
				<div class='row'>
					");
					if($_SESSION["restpropina"]>0){
						echo $gf->utf8("<input type='hidden' id='propini' value='{$_SESSION["restpropina"]}' />");
					}
					$n=1;
						echo $gf->utf8("

						<div class='col-md-12'>
							<div class='box box-widget widget-user-2'>
								<div class='widget-user-header bg-yellow'>
									<div class='widget-user-image'>
										<img class='img-circle' src='misc/user_normal.png' alt='Cliente'>
										<input type='hidden' id='id_cliente_$n' value='' name='id_cliente_$n' class='unival_dix_$id_pedido' />
									
									</div>
									<h3 class='widget-user-username' id='cliente_$n'>Cliente $n</h3>
									<h5 class='widget-user-desc' id='cliente_d_$n'><button class='btn btn-default btn-xs' onclick=\"getDialog('$sender?flag=getadd_cliente&n=$n')\">Asignar cliente</button></h5>
									<h5 class='widget-user-desc'><span id='totalprz_$n'>0</span> <span class='pull-right'>Pago: <select name='fpa_$n' id='fpa_$n' class='form-control input-sm unival_dix_$id_pedido' style='width:100px;height:25px;font-size:12px;'>$formas_select</select></span></h5>
								</div>
								<div class='box-footer no-padding'>
									<ul n='$n' id='ulaula_$n' class='nav nav-stacked container_platon' data-empty-message='Arrastra productos aquí' style='width:100%;min-height:70px;'>
									<li class='empty'>Arrastra productos aqu&iacute;</li>
									");
									echo $gf->utf8("
									<li class='list-group-item'><input type='checkbox' onclick=\"calcPropi('$n','container_platon','platos_to_divide')\" style='width:20px;height:20px;' name='incluserv_$n' id='incluserv_$n' class='unival_dix_$id_pedido' /> Incluir propina: <input type='number' onchange=\"calcPrize('container_platon','platos_to_divide')\" class='form-control input-sm pull-right unival_dix_$id_pedido' step='any' style='width:120px;' id='propini_$n' name='propini_$n' /></li>");

									echo $gf->utf8("
									</ul>
								</div>
							</div>
						</div>
					");
		
						echo $gf->utf8("
					</div>
					<button id='nextbtn_pedi_facpart' style='display:none;' class='btn btn-danger pull-right' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=fact_pedido_part3_v2&id_ped=$id_pedido&rnd=$rnd','','4000','unival_dix_$id_pedido')\">Facturar</button>
				</div>
				
			</div>
			
			
			<script>
					$(function(){
						dragaDrop2('platos_to_divide','container_platon');
					});
			</script>
			");
		}elseif($actividad=="fact_pedido_part3_v2"){
			$id_pedido=$gf->cleanVar($_GET["id_ped"]);
			$rsM=$gf->dataSet("SELECT ID_MESA FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
			$id_mesa=$rsM[0]["ID_MESA"];

			$rnd=$gf->cleanVar($_GET["rnd"]);
			$n=1;
			$clientes=array();
			$items=array();
			$niz=array();
			$propas=array();
			foreach($_POST as $key=>$val){
				if(substr($key,0,4)=="item"){
					$id_item=str_replace("item_","",$key);
					if(isset($items[$val])){
						$items[$val][]=$id_item;
					}else{
						$items[$val]=array();
						$items[$val][]=$id_item;
					}
					
				}elseif(substr($key,0,9)=="incluserv"){
					$n_i=str_replace("incluserv_","",$key);
					$niz[$n_i]=$val;
					$propas[$n_i]=$_POST["propini_$n_i"];
				}
			}
	
			for($z=1;$z<=$n;$z++){
				$id_cliente=$_POST["id_cliente_".$z];
				$fpa=$_POST["fpa_".$z];
				$clientes[$z]["id"]=$id_cliente;
				$clientes[$z]["it"]=$items[$z];
				$clientes[$z]["fp"]=$fpa;
				if(isset($niz[$z])){
					$clientes[$z]["iz"]=$niz[$z];
					$clientes[$z]["pp"]=$propas[$z];
				}else{
					$clientes[$z]["iz"]=0;
					$clientes[$z]["pp"]=0;
				}
			}
			$z=1;
			$id_newped=$gf->dataInLast("INSERT INTO pedidos (ID_SERVICIO,ID_MESA,ID_TENDER,APERTURA,CHEF,CAJA,CIERRE,PRINTED) SELECT ID_SERVICIO,ID_MESA,ID_TENDER,APERTURA,CHEF,CAJA,CIERRE,'1' AS PRINTED FROM pedidos WHERE ID_PEDIDO=:pedido",array(":pedido"=>$id_pedido));
			if($id_newped>0){
				$itemss=$clientes[$z]["it"];
				$id_sil=$gf->dataInLast("INSERT INTO sillas (ID_PEDIDO,GENDER) VALUES ('$id_newped','M')");
				foreach($itemss as $itm){
					$gf->dataIn("UPDATE sillas_platos SET ID_SILLA='$id_sil' WHERE ID_ITEM='$itm'");
				}
				$pedidos[$z]=array("p"=>$id_newped,"c"=>$clientes[$z]["id"],"f"=>$clientes[$z]["fp"],"pp"=>$clientes[$z]["iz"],"pr"=>$clientes[$z]["pp"]);
			}else{
				echo "Fallo en la reorganizacion de las facturas";
			}
			
			
			$infoEmpresa=$gf->dataSet("SELECT NIT, NOMBRE, CIUDAD, DIRECCION, TELEFONO, REGIMEN, RESOLUCION_FACTURAS, PREFIJO, INIFACT FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
			$rwEmpresa=$infoEmpresa[0];
			$empresa_inifact=$rwEmpresa["INIFACT"];
			$empresa_prefijo=$rwEmpresa["PREFIJO"];
			$pedsfact="";
			foreach($pedidos as $indx=>$infoped){
				$ID_CLIENTE=$infoped["c"];
				$idped=$infoped["p"];
				$pedsfact.="$idped,";
				$fpgo=$infoped["f"];
				$pp=$infoped["pp"];
				$prp=$infoped["pr"];


				



				$rsPed=$gf->dataSet("SELECT I.ID_ITEM, I.ID_SILLA, I.ID_PLATO, I.CANTIDAD, I.FENTREGA, P.NOMBRE AS PLATO, P.PRECIO, (P.PRECIO/(1+(IM.PORCENTAJE/100))) AS BASE FROM sillas_platos I JOIN platos P ON P.ID_PLATO=I.ID_PLATO LEFT JOIN impuestos IM ON IM.ID_IMPUESTO=P.ID_IMPUESTO WHERE I.ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO=:pedido ORDER BY ID_SILLA) ORDER BY I.ID_ITEM",array(":pedido"=>$idped));
				$filas="";
				$toti=0;
				$totimp=0;
				if(count($rsPed)>0){
					foreach($rsPed as $rwPed){
						$id_item=$rwPed["ID_ITEM"];
						$plato=$rwPed["PLATO"];
						$plato_pz=$rwPed["PRECIO"];
						$plato_base=$rwPed["BASE"];
						if($plato_base=="") $plato_base=$plato_pz;
						$cantidad=$rwPed["CANTIDAD"];
						$impu=$plato_pz-$plato_base;
						$totimp+=($impu*$cantidad);
						$filas.="<li idi='$id_item' class='list-group-item platos_to_divide clearfix'> $plato<small class='pull-right prz'>$plato_pz</small></li>";
						$toti+=$plato_pz;
					}
				}
				if($pp==1 && $_SESSION["restpropina"]>0){
					$propina=$prp;
				}else{
					$propina=0;
				}
				if($propina>0){
					$filas.="<li idi='$id_item' class='list-group-item platos_to_divide clearfix'> Servicio (propina)<small class='pull-right prz'>$propina</small></li>";
				}
				$cobrado=$toti;
				$descuento=0;
				$consec=$gf->dataSet("SELECT CONSECUTIVO FROM facturas WHERE ID_PEDIDO IN(SELECT P.ID_PEDIDO FROM pedidos P JOIN servicio S ON S.ID_SERVICIO=P.ID_SERVICIO WHERE S.ID_SITIO=:sitio ORDER BY P.ID_PEDIDO) ORDER BY CONSECUTIVO DESC LIMIT 1",array(":sitio"=>$_SESSION["restbus"]));
				//print_r($consec);
				if(count($consec)>0){
					$consecutivo=$consec[0]["CONSECUTIVO"];
					$consecutivo+=1;
				}else{
					$consecutivo=$empresa_inifact;
				}




				$rsPP=$gf->dataSet("SELECT PESO_PUNTO FROM sitios WHERE ID_SITIO='{$_SESSION["restbus"]}'");
				$peso_punto=$rsPP[0]["PESO_PUNTO"];

				if($peso_punto>0){
					$puntos=floor($cobrado/$peso_punto);
				}else{
					$puntos=0;
				}
				
				$id_fact=$gf->dataInLast("INSERT INTO facturas (PREFIJO,CONSECUTIVO,FECHA,ID_CLIENTE,ID_PEDIDO,PRINTED,PUNTOS) VALUES ('$empresa_prefijo','$consecutivo',CURDATE(),'$ID_CLIENTE','$idped','1','$puntos')");
				if($id_fact>0){
					$ok=$gf->dataIn("UPDATE pedidos SET PAGO='$cobrado', IMPUESTO='$totimp', DCTO='$descuento', PROPINA='$propina', CIERRE=NOW(), ID_FP='$fpgo', ID_CAJERO='{$_SESSION["restuiduser"]}' WHERE ID_PEDIDO='$idped'");
					if($ok){
						$clet=$gf->dataSet("SELECT NOMBRE FROM clientes WHERE ID_CLIENTE='$ID_CLIENTE'");
						if(count($clet)>0){
							$nm_clte=$clet[0]["NOMBRE"];
						}else{
							$nm_clte="Cliente an&oacute;nimo";
						}
						
						echo $gf->utf8("
						<div class='row'>
						<div class='col-md-2'></div>
						<div class='col-md-8'>
							<div class='box box-widget widget-user-2'>
								<div class='widget-user-header bg-yellow'>
									<div class='widget-user-image'>
										<img class='img-circle' src='misc/user_normal.png' alt='Cliente'>
									</div>
									<h3 class='widget-user-username' id='cliente_$n'>Fact No. $consecutivo <button class='btn btn-primary pull-right btn-xs' onclick=\"getDialog('$sender?flag=fact_pedido4&id_ped=$idped&rnd=$rnd')\"><i class='fa fa-print'></i> Imprimir</button>
									<a class='btn btn-info btn-xs pull-right' target='_blank' href=\"Admin/pdf_fact.php?id_ped=$idped\">Pdf</a>
									</h3>
									<h5 class='widget-user-desc' id='cliente_d_$n'>$nm_clte</h5>
								</div>
								<div class='box-footer no-padding'>
									<ul n='$n' class='nav nav-stacked container_platon'>
									$filas
									<li class='list-group-item list-group-item-danger'>TOTAL $ ".number_format($cobrado+$propina,0)."</li>
									</ul>
								</div>
								
							</div>
						</div>
						<div class='col-md-2'></div>
						</div>
						<script>
						$(function(){
							sockEmitir('liberar',{id_mesa:$id_mesa});
						});
						</script>
						");
	
					}
				}else{
					echo "Error 988: No se pudo crear la factura";
				}
	
			}
			$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"PEDIDO CORTADO Y FACTURADO, NUEVO PEDIDO $id_newped",$_SESSION["restuiduser"]);
			$rsNP=$gf->dataSet("SELECT SP.ID_PLATO FROM sillas S JOIN sillas_platos SP ON SP.ID_SILLA=S.ID_SILLA WHERE S.ID_PEDIDO='$id_pedido'");
			if(count($rsNP)>0){
				echo $gf->utf8("
				<hr />
				<div class='row'>
				<div class='col-md-12'>
				<button class='btn btn-primary pull-right' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=fact_pedido_part1_v2&rnd=$rnd&id_ped=$id_pedido')\">Continuar facturaci&oacute;n</button></div>
				</div>
				");
			}else{
				$oka=$gf->dataIn("DELETE FROM sillas WHERE ID_PEDIDO='$id_pedido'");
				$oka=$gf->dataIn("DELETE FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
				echo $gf->utf8("
				<hr />
				<div class='row'>
				<div class='col-md-12'>
				<button class='btn btn-primary pull-right' onclick=\"closeD('$rnd')\">Terminar</button>
				</div>
				</div>
						");
			}
			
		

	}elseif($actividad=="fact_pedido_part2"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$nsub=$_POST["nchairs"];

		$formas=$gf->dataSet("SELECT ID_FP, NOMBRE, ICONO FROM formas_pago WHERE ID_SITIO=:sitio ORDER BY POSICION, NOMBRE",array(":sitio"=>$_SESSION["restbus"]));
		$formas_select="";
		if(count($formas)>0){
			foreach($formas as $fpa){
				$idfp=$fpa["ID_FP"];
				$nombrefp=$fpa["NOMBRE"];
				$formas_select.="<option value='$idfp'>$nombrefp</option>";
			}
		}



		$rsPed=$gf->dataSet("SELECT ID_ITEM, ID_SILLA, ID_PLATO, CANTIDAD, FENTREGA, PRECIO FROM sillas_platos WHERE ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO=:pedido ORDER BY ID_SILLA) AND CANTIDAD>1 ORDER BY ID_ITEM",array(":pedido"=>$id_pedido));
		if(count($rsPed)>0){
			foreach($rsPed as $rwPed){
				$id_item=$rwPed["ID_ITEM"];
				$id_silla=$rwPed["ID_SILLA"];
				$id_plato=$rwPed["ID_PLATO"];
				$cantidad=$rwPed["CANTIDAD"];
				$fentrega=$rwPed["FENTREGA"];
				$precio=$rwPed["PRECIO"];
				for($s=2;$s<=$cantidad;$s++){
					$id_newitem=$gf->dataInLast("INSERT INTO sillas_platos (ID_SILLA,ID_PLATO,CANTIDAD,ENTREGADO,FENTREGA,PRINTED,PRECIO) VALUES (:silla,:plato,:cantidad,:entregado,:fentrega,:printed,:precio)",array(":silla"=>$id_silla,":plato"=>$id_plato,":cantidad"=>1,":entregado"=>1,":fentrega"=>$fentrega,":printed"=>1,":precio"=>$precio));
					$gf->dataIn("INSERT INTO sillas_platos_composicion (ID_ITEM,ID_RACION,ESTADO,ID_OPCION) SELECT '$id_newitem' AS ID_ITEM,ID_RACION,ESTADO,ID_OPCION FROM sillas_platos_composicion WHERE ID_ITEM='$id_item'");
				}
				$gf->dataIn("UPDATE sillas_platos SET CANTIDAD=1 WHERE ID_ITEM=:item",array(":item"=>$id_item));
			}
		}

		echo $gf->utf8("
			<div class='row'>
				<div class='col-md-3'>
					<ul class='list-group' id='grupoinicial_pro'>
					");
					$rsPed=$gf->dataSet("SELECT I.ID_ITEM, I.ID_SILLA, I.ID_PLATO, I.CANTIDAD, I.FENTREGA, P.NOMBRE AS PLATO, P.PRECIO FROM sillas_platos I JOIN platos P ON P.ID_PLATO=I.ID_PLATO WHERE I.ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO=:pedido ORDER BY ID_SILLA) ORDER BY I.ID_ITEM",array(":pedido"=>$id_pedido));
					if(count($rsPed)>0){
						foreach($rsPed as $rwPed){
							$id_item=$rwPed["ID_ITEM"];
							$plato=$rwPed["PLATO"];
							$plato_pz=$rwPed["PRECIO"];
							echo $gf->utf8("<li idi='$id_item' class='list-group-item platos_to_divide clearfix'><i class='fa fa-arrows'></i><input type='hidden' id='pareto_$id_item' name='item_$id_item' class='unival_dix_$id_pedido' value='0' /> $plato<small class='pull-right prz'>$plato_pz</small></li>");
						}
					}
					echo $gf->utf8("
					</ul>
				</div>
				<div class='col-md-9'>
				<div class='row'>
					");
					if($_SESSION["restpropina"]>0){
						echo $gf->utf8("<input type='hidden' id='propini' value='{$_SESSION["restpropina"]}' />");
					}

					for($n=1;$n<=$nsub;$n++){
						echo $gf->utf8("

						<div class='col-md-6'>
							<div class='box box-widget widget-user-2'>
								<div class='widget-user-header bg-yellow'>
									<div class='widget-user-image'>
										<img class='img-circle' src='misc/user_normal.png' alt='Cliente'>
										<input type='hidden' id='id_cliente_$n' value='' name='id_cliente_$n' class='unival_dix_$id_pedido' />
									
									</div>
									<h3 class='widget-user-username' id='cliente_$n'>Cliente $n</h3>
									<h5 class='widget-user-desc' id='cliente_d_$n'><button class='btn btn-default btn-xs' onclick=\"getDialog('$sender?flag=getadd_cliente&n=$n')\">Asignar cliente</button></h5>
									<h5 class='widget-user-desc'><span id='totalprz_$n'>0</span> <span class='pull-right'>Pago: <select name='fpa_$n' id='fpa_$n' class='form-control input-sm unival_dix_$id_pedido' style='width:100px;height:25px;font-size:12px;'>$formas_select</select></span></h5>
								</div>
								<div class='box-footer no-padding'>
									<ul n='$n' id='ulaula_$n' class='nav nav-stacked container_platon' data-empty-message='Arrastra productos aquí' style='width:100%;min-height:70px;'>
									<li class='empty'>Arrastra productos aqu&iacute;</li>
									");
									echo $gf->utf8("
									<li class='list-group-item'><input type='checkbox' onclick=\"calcPropi('$n','container_platon','platos_to_divide')\" style='width:20px;height:20px;' name='incluserv_$n' id='incluserv_$n' class='unival_dix_$id_pedido' /> Incluir propina: <input type='number' onchange=\"calcPrize('container_platon','platos_to_divide')\" class='form-control input-sm pull-right unival_dix_$id_pedido' step='any' style='width:120px;' id='propini_$n' name='propini_$n' /></li>");

									echo $gf->utf8("
									</ul>
								</div>
							</div>
						</div>
					");
					}
				
						echo $gf->utf8("
					</div>
				</div>
			</div>
			<div class='row'>
				<div class='col-md-12'>
					<button id='nextbtn_pedi_facpart' style='display:none;' class='btn btn-danger pull-right' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=fact_pedido_part3&id_ped=$id_pedido&rnd=$rnd&n=$nsub','','4000','unival_dix_$id_pedido')\">Facturar</button>
				</div>
			</div>
			
			<script>
					$(function(){
						dragaDrop('platos_to_divide','container_platon');
					});
			</script>
			");
		
		

	}elseif($actividad=="fact_pedido_part3"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rsM=$gf->dataSet("SELECT ID_MESA FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
		$id_mesa=$rsM[0]["ID_MESA"];
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$n=$gf->cleanVar($_GET["n"]);
		$clientes=array();
		$items=array();
		$niz=array();
		$propas=array();
		foreach($_POST as $key=>$val){
			if(substr($key,0,4)=="item"){
				$id_item=str_replace("item_","",$key);
				if(isset($items[$val])){
					$items[$val][]=$id_item;
				}else{
					$items[$val]=array();
					$items[$val][]=$id_item;
				}
				
			}elseif(substr($key,0,9)=="incluserv"){
				$n_i=str_replace("incluserv_","",$key);
				$niz[$n_i]=$val;
				$propas[$n_i]=$_POST["propini_$n_i"];
			}
		}

		for($z=1;$z<=$n;$z++){
			$id_cliente=$_POST["id_cliente_".$z];
			$fpa=$_POST["fpa_".$z];
			$clientes[$z]["id"]=$id_cliente;
			$clientes[$z]["it"]=$items[$z];
			$clientes[$z]["fp"]=$fpa;
			if(isset($niz[$z])){
				$clientes[$z]["iz"]=$niz[$z];
				$clientes[$z]["pp"]=$propas[$z];
			}else{
				$clientes[$z]["iz"]=0;
				$clientes[$z]["pp"]=0;
			}
		}
		$pedidos[1]=array("p"=>$id_pedido,"c"=>$clientes[1]["id"],"f"=>$clientes[1]["fp"],"pp"=>$clientes[1]["iz"],"pr"=>$clientes[1]["pp"]);
		for($z=2;$z<=$n;$z++){
			$id_newped=$gf->dataInLast("INSERT INTO pedidos (ID_SERVICIO,ID_MESA,ID_TENDER,APERTURA,CHEF,CAJA,CIERRE,PRINTED) SELECT ID_SERVICIO,ID_MESA,ID_TENDER,APERTURA,CHEF,CAJA,CIERRE,'1' AS PRINTED FROM pedidos WHERE ID_PEDIDO=:pedido",array(":pedido"=>$id_pedido));
			if($id_newped>0){
				$itemss=$clientes[$z]["it"];
				$id_sil=$gf->dataInLast("INSERT INTO sillas (ID_PEDIDO,GENDER) VALUES ('$id_newped','M')");
				foreach($itemss as $itm){
					$gf->dataIn("UPDATE sillas_platos SET ID_SILLA='$id_sil' WHERE ID_ITEM='$itm'");
				}
				$pedidos[$z]=array("p"=>$id_newped,"c"=>$clientes[$z]["id"],"f"=>$clientes[$z]["fp"],"pp"=>$clientes[$z]["iz"],"pr"=>$clientes[$z]["pp"]);
			}else{
				echo "Fallo en la reorganizacion de las facturas";
			}
		}
		
		$infoEmpresa=$gf->dataSet("SELECT NIT, NOMBRE, CIUDAD, DIRECCION, TELEFONO, REGIMEN, RESOLUCION_FACTURAS, PREFIJO, INIFACT FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		$rwEmpresa=$infoEmpresa[0];
		$empresa_inifact=$rwEmpresa["INIFACT"];
		$empresa_prefijo=$rwEmpresa["PREFIJO"];
		$pedsfact="";
		foreach($pedidos as $indx=>$infoped){
			$ID_CLIENTE=$infoped["c"];
			$idped=$infoped["p"];
			$pedsfact.="$idped,";
			$fpgo=$infoped["f"];
			$pp=$infoped["pp"];
			$prp=$infoped["pr"];
			$rsPed=$gf->dataSet("SELECT I.ID_ITEM, I.ID_SILLA, I.ID_PLATO, I.CANTIDAD, I.FENTREGA, P.NOMBRE AS PLATO, P.PRECIO, (P.PRECIO/(1+(IM.PORCENTAJE/100))) AS BASE FROM sillas_platos I JOIN platos P ON P.ID_PLATO=I.ID_PLATO LEFT JOIN impuestos AS IM ON IM.ID_IMPUESTO=P.ID_IMPUESTO WHERE I.ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO=:pedido ORDER BY ID_SILLA) ORDER BY I.ID_ITEM",array(":pedido"=>$idped));
			$filas="";
			$toti=0;
			$totimp=0;
			if(count($rsPed)>0){
				foreach($rsPed as $rwPed){
					$id_item=$rwPed["ID_ITEM"];
					$plato=$rwPed["PLATO"];
					$plato_pz=$rwPed["PRECIO"];
					$base=$rwPed["BASE"];
					$cant=$rwPed["CANTIDAD"];
					$impuesto=($plato_pz-$base)*$cant;
					$totimp+=$impuesto;
					$filas.="<li idi='$id_item' class='list-group-item platos_to_divide clearfix'> $plato<small class='pull-right prz'>$plato_pz</small></li>";
					$toti+=$plato_pz;
				}
			}
			if($pp==1 && $_SESSION["restpropina"]>0){
				$propina=$prp;
			}else{
				$propina=0;
			}
			if($propina>0){
				$filas.="<li idi='$id_item' class='list-group-item platos_to_divide clearfix'> Servicio (propina)<small class='pull-right prz'>$propina</small></li>";
			}
			$cobrado=$toti;
			$descuento=0;
			$consec=$gf->dataSet("SELECT CONSECUTIVO FROM facturas WHERE ID_PEDIDO IN(SELECT P.ID_PEDIDO FROM pedidos P JOIN servicio S ON S.ID_SERVICIO=P.ID_SERVICIO WHERE S.ID_SITIO=:sitio ORDER BY P.ID_PEDIDO) ORDER BY CONSECUTIVO DESC LIMIT 1",array(":sitio"=>$_SESSION["restbus"]));
			//print_r($consec);
			if(count($consec)>0){
				$consecutivo=$consec[0]["CONSECUTIVO"];
				$consecutivo+=1;
			}else{
				$consecutivo=$empresa_inifact;
			}


			$rsPP=$gf->dataSet("SELECT PESO_PUNTO FROM sitios WHERE ID_SITIO='{$_SESSION["restbus"]}'");
			$peso_punto=$rsPP[0]["PESO_PUNTO"];

			if($peso_punto>0){
				$puntos=floor($cobrado/$peso_punto);
			}else{
				$puntos=0;
			}


			$id_fact=$gf->dataInLast("INSERT INTO facturas (PREFIJO,CONSECUTIVO,FECHA,ID_CLIENTE,ID_PEDIDO,PRINTED,PUNTOS) VALUES ('$empresa_prefijo','$consecutivo',CURDATE(),'$ID_CLIENTE','$idped','1','$puntos')");
			if($id_fact>0){
				$ok=$gf->dataIn("UPDATE pedidos SET PAGO='$cobrado', IMPUESTO='$totimp', DCTO='$descuento', PROPINA='$propina', CIERRE=NOW(), ID_FP='$fpgo', ID_CAJERO='{$_SESSION["restuiduser"]}' WHERE ID_PEDIDO='$idped'");
				if($ok){
					$clet=$gf->dataSet("SELECT NOMBRE FROM clientes WHERE ID_CLIENTE='$ID_CLIENTE'");
					if(count($clet)>0){
						$nm_clte=$clet[0]["NOMBRE"];
					}else{
						$nm_clte="Cliente an&oacute;nimo";
					}
					
					echo $gf->utf8("
					<div class='col-md-6'>
						<div class='box box-widget widget-user-2'>
							<div class='widget-user-header bg-yellow'>
								<div class='widget-user-image'>
									<img class='img-circle' src='misc/user_normal.png' alt='Cliente'>
								</div>
								<h3 class='widget-user-username' id='cliente_$n'>Fact No. $consecutivo <button class='btn btn-primary pull-right btn-xs' onclick=\"getDialog('$sender?flag=fact_pedido4&id_ped=$idped&rnd=$rnd')\"><i class='fa fa-print'></i> Imprimir</button>
								<a class='btn btn-info btn-xs pull-right' target='_blank' href=\"Admin/pdf_fact.php?id_ped=$idped\">Pdf</a>
								</h3>
								<h5 class='widget-user-desc' id='cliente_d_$n'>$nm_clte</h5>
							</div>
							<div class='box-footer no-padding'>
								<ul n='$n' class='nav nav-stacked container_platon'>
								$filas
								<li class='list-group-item list-group-item-danger'>TOTAL $ ".number_format($cobrado+$propina,0)."</li>
								</ul>
							</div>
							
						</div>
					</div>
					<script>
					$(function(){
						sockEmitir('liberar',{id_mesa:$id_mesa});
					});
					</script>
					");

				}
			}else{
				echo "Error 988: No se pudo crear la factura";
			}

		}
		$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"PEDIDO CORTADO Y FACTURADO, PEDIDOS RESULTANTES: $pedsfact",$_SESSION["restuiduser"]);
		echo $gf->utf8("
			<hr />
			<button class='btn btn-primary pull-right' onclick=\"closeD('$rnd')\">Terminar</button>
					");
	}elseif($actividad=="getadd_cliente"){
		$n=$gf->cleanVar($_GET["n"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);

		$rsCity=$gf->dataSet("SELECT COD, NOMBRE FROM ciudades ORDER BY NOMBRE");
		$selectcities="";
		if(count($rsCity)>0){
			foreach ($rsCity as $rwCity) {
				$dane=$rwCity["COD"];
				$nmcity=$rwCity["NOMBRE"];
				$selectcities.="<option value='$dane'>$nmcity</option>";
			}
		}
		echo $gf->utf8("
		Identificaci&oacute;n:
		<input onchange=\"queryClient('$n')\" type='text' name='num_doc' id='num_doc_$n' class='form-control unival_dix_x' required /><br />
		Tipo: <select name='tipo_doc' id='tipo_doc_$n' class='form-control  unival_dix_x'><option value='CC'>C&eacute;dula</option><option value='NI'>Nit</option></select>
		Nombre: <input type='text' required name='nombre_cliente' id='nombre_cliente_$n' class='form-control unival_dix_x' /><br />
		Direcci&oacute;n: <input type='text' name='dir_cliente' id='dir_cliente_$n' class='form-control unival_dix_x' /><br />
		Tel&eacute;fono: <input type='text' name='tel_cliente' id='tel_cliente_$n' class='form-control unival_dix_x' /><br />
		Correo: <input type='text' name='mail_cliente' id='mail_cliente_$n' class='form-control unival_dix_x' /><br />
		Ciudad: <select style='width:100%' type='text' name='city_cliente' id='city_cliente_$n' class='form-control unival_dix_x chosen'>
		$selectcities
		</select><br />
		<br />
		<button class='btn btn-danger' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=addset_cliente&n=$n&rnd=$rnd','','4000','unival_dix_x')\">Asignar</button>
		
		");
	}elseif($actividad=="addset_cliente"){
		$n=$gf->cleanVar($_GET["n"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$num_doc=trim($_POST["num_doc"]);
		$tipo_doc=$_POST["tipo_doc"];
		$nombre_cliente=$_POST["nombre_cliente"];
		$dir_cliente=$_POST["dir_cliente"];
		$tel_cliente=$_POST["tel_cliente"];
		$mail_cliente=$_POST["mail_cliente"];
		$city_cliente=$_POST["city_cliente"];
		if($num_doc>0){
			$cliente=$gf->dataSet("SELECT * FROM clientes WHERE IDENTIFICACION=:ident AND ID_SITIO=:sitio",array(":ident"=>$num_doc,":sitio"=>$_SESSION["restbus"]));
			if(count($cliente)>0){
				$ID_CLIENTE=$cliente[0]["ID_CLIENTE"];
				$nombre_c=$cliente[0]["NOMBRE"];
				$dir_c=$cliente[0]["DIRECCION"];
				$tel_c=$cliente[0]["TELEFONO"];
				echo $gf->utf8("<script>
				$(function(){
					$('#cliente_$n').text('$num_doc: $nombre_c');
					$('#cliente_d_$n').text('Dir: $dir_c Tel: $tel_c');
					$('#id_cliente_$n').val('$ID_CLIENTE');
					
					closeD('$rnd');
				});
				</script>");
			}else{
				$ID_CLIENTE=$gf->dataInLast("INSERT INTO clientes (TIPO_ID,IDENTIFICACION,NOMBRE,DIRECCION,TELEFONO,CORREO,CIUDAD,ID_SITIO) VALUES ('$tipo_doc','$num_doc','$nombre_cliente','$dir_cliente','$tel_cliente','$mail_cliente','$city_cliente','{$_SESSION["restbus"]}')");
				if($ID_CLIENTE>0){
					echo $gf->utf8("<script>
						$(function(){
							$('#cliente_$n').text('$num_doc: $nombre_cliente');
							$('#cliente_d_$n').text('Dir: $dir_cliente Tel: $tel_cliente');
							$('#id_cliente_$n').val('$ID_CLIENTE');
							closeD('$rnd');
						});
						</script>");
				}else{
					echo "Error al crear el cliente";
				}
			}
		}else{
			echo $gf->utf8("Los datos son obligatorios");
		}
		


	}elseif($actividad=="printcom"){
		$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		//$ok=$gf->dataIn("UPDATE pedidos SET PRINTED='0' WHERE ID_PEDIDO='$id_pedido'");
		//$ok=$gf->dataIn("UPDATE sillas_platos SET PRINTED='0' WHERE ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido')");

		echo $gf->utf8("
		Enviando.....
		<script>
			$(function(){
				setTimeout(function(){
					closeD('$rnd');
					sockEmitir('comandar',{id_pedido:'$id_pedido',parte:'todo'});
					
				},2000);
			});
		</script>");
		$gf->log($_SESSION["restbus"],0,$id_pedido,"IMPRIMIR COMANDA",$_SESSION["restuiduser"]);

	}elseif($actividad=="printpre"){
		$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
		$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		echo $gf->utf8("
		Enviando.....
		<script>
			$(function(){
				setTimeout(function(){
					closeD('$rnd');
					sockEmitir('precuenta',{id_pedido:'$id_pedido',id_mesa:$id_mesa});
					
				},2000);
			});
		</script>");
		$gf->dataIn("UPDATE pedidos SET T_PRECUENTA=NOW(), U_PRECUENTA='{$_SESSION["restuiduser"]}' WHERE ID_PEDIDO='$id_pedido'");
		$gf->log($_SESSION["restbus"],0,$id_pedido,"IMPRIMIR PRECUENTA",$_SESSION["restuiduser"]);
	}elseif($actividad=="fchistory_home"){
		$lCom=$gf->dataSet("SELECT S.ID_SERVICIO, S.FECHA, S.ESTADO, COUNT(P.ID_PLATO) AS PLATOS FROM servicio S LEFT JOIN servicio_oferta P ON S.ID_SERVICIO=P.ID_SERVICIO WHERE S.ID_SITIO=:sitio AND S.ESTADO>0 GROUP BY S.FECHA ORDER BY S.FECHA DESC",array(":sitio"=>$_SESSION["restbus"]));

		echo $gf->utf8("
		<div class='box box-warning'><div class='box-header'>HISTORIAL DE VENTA POR SERVICIOS</div>
		<div class='box-body'>
		");
		echo $gf->utf8("
		<div class='row'>
			<div class='col-md-3' id='level2'>
			<div class='box box-danger'>
			<div class='box-header'>SERVICIOS CERRADOS</div>
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
					$bandera="fact_history";
					$btns="";
					
					echo $gf->utf8("<li id='theserv_$id_service' title='$titl' onclick=\"cargaHTMLvars('level3','$sender?flag=$bandera&key=$id_service&st=$estado')\" lnk-tsf='#history-$id_service' lnk-cont='level3' class='list-group-item list-group-item-$classe link-cnv'>$fecha <i class='$ico pull-right'></i> $btns</li>");
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
	

	}elseif($actividad=="fact_history"){
		if(isset($_GET["key"])){
			$id_serv = $_GET["key"];
		}else{
			$id_serv = 0;
		}

		$formas=$gf->dataSet("SELECT ID_FP, NOMBRE, ICONO FROM formas_pago WHERE ID_SITIO=:sitio ORDER BY POSICION, NOMBRE",array(":sitio"=>$_SESSION["restbus"]));
		$arformas=array();
		if(count($formas)>0){
			foreach($formas as $fpa){
				$idfp=$fpa["ID_FP"];
				$nombrefp=$fpa["NOMBRE"];
				$arformas[$idfp]=$nombrefp;
			}
		}



		if($id_serv==0){
			$curServ=$gf->dataSet("SELECT ID_SERVICIO, BASE_CAJA FROM servicio WHERE ESTADO=0 AND ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
			if(count($curServ)>0){
				$_SESSION["restservice"]=$curServ[0]["ID_SERVICIO"];
				$_SESSION["base_caja"]=$curServ[0]["BASE_CAJA"];
				$id_serv=$curServ[0]["ID_SERVICIO"];
			}else{
				echo $gf->utf8("Hola!, no se encontr&oacute; un servicio activo, contacta al administrador para abrir el servicio de hoy");
				exit;
			}
		}

		$rsAbonos=$gf->dataSet("SELECT ID_ABONO, ID_PEDIDO, VALOR, ID_FP FROM pedidos_abonos WHERE ID_PEDIDO IN(SELECT ID_PEDIDO FROM pedidos WHERE ID_SERVICIO='{$_SESSION["restservice"]}') AND ID_FP IN(SELECT ID_FP FROM formas_pago WHERE CAJA=1 ORDER BY ID_FP) ORDER BY ID_PEDIDO");

		$arAnti=array();
		if(count($rsAbonos)>0){
			foreach($rsAbonos as $rwA){
				$idab=$rwA["ID_ABONO"];
				$idpea=$rwA["ID_PEDIDO"];
				$idfpa=$rwA["ID_FP"];
				$abvl=$rwA["ID_ABONO"];
				if(!isset($arAnti[$idpea][$idfpa])) $arAnti[$idpea][$idfpa]=0;
				$arAnti[$idpea][$idfpa]+=$abvl;

			}
		}

		$knz=$gf->dataSet("SELECT KNZ, SIIGO_USER FROM sitios WHERE ID_SITIO='{$_SESSION["restbus"]}'");
		$knzval=$knz[0]["KNZ"];
		$fel_user=$knz[0]["SIIGO_USER"];
		$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.NOMBRE, M.TIPO, P.ID_PEDIDO,P.PROPINA,F.PREFIJO, F.CONSECUTIVO, F.ID_FACTURA, F.FEL,F.FEL_NOMBRE, F.FEL_CUFE, F.FEL_ESTADO, F.FEL_CONSECUTIVO, F.ESTADO, C.NOMBRE AS CLIENTE, DATE(P.APERTURA) AS FECHA, P.PAGO, P.PAGO<P.ORIG_PAGO AS PDX, FP.NOMBRE AS FORMAPAGO, P.ID_FP, GROUP_CONCAT(CONCAT(PA.ID_FP,'*',PA.VALOR)) AS ABOX FROM mesas AS M RIGHT JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CIERRE<>'0000-00-00 00:00:00') LEFT JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO LEFT JOIN clientes C ON C.ID_CLIENTE=F.ID_CLIENTE LEFT JOIN formas_pago FP ON FP.ID_FP=P.ID_FP LEFT JOIN pedidos_abonos PA ON PA.ID_PEDIDO=P.ID_PEDIDO WHERE P.ID_SERVICIO='$id_serv' AND P.CIERRE<>'0000-00-00 00:00:00' GROUP BY P.ID_PEDIDO ORDER BY F.CONSECUTIVO DESC, P.ID_PEDIDO DESC");
		
		$reporte="<table class='table table-bordered'><thead><tr><td>FACT No.</td><td>MESA</td><td>CLIENTE</td><td>FECHA</td><td>Forma pago</td><td>VAL</td><td>Propina</td><td>OBS</td></tr></thead><tbody>";
		$totalventas=0;
		
		if(count($resultInt)>0){
			foreach($resultInt as $rowInt){
				$acumbase=0;
				$acumprice=0;
				$acumimp=0;
				$abonos=0;
				$id_mesa=$rowInt["ID_MESA"];
				$nombre=$rowInt["NOMBRE"];
				$id_pedido=$rowInt["ID_PEDIDO"];
				$id_fp=$rowInt["ID_FP"];
				$id_factura=$rowInt["ID_FACTURA"];
				$consecutivo=$rowInt["CONSECUTIVO"];
				$formapago = $rowInt["FORMAPAGO"];
				$propina = $rowInt["PROPINA"];
				$pdx = $rowInt["PDX"];
				$pago = $rowInt["PAGO"];
				$f_estado = $rowInt["ESTADO"];
				$fel = $rowInt["FEL"];
				$fel_nombre = $rowInt["FEL_NOMBRE"];
				$fel_estado = $rowInt["FEL_ESTADO"];
				$fel_cufe = $rowInt["FEL_CUFE"];
				$factus=$consecutivo;
				$abox=explode(",",$rowInt["ABOX"]);
				$add_desc="";
				$oriforma=$formapago;
				if($_SESSION["restprofile"]=="A"){
					$rofp=0;
				}else{
					$rofp=1;
				}
				foreach($abox as $abx){
					if($abx!=""){
						$infoab=explode("*",$abx);
						$idfx=$infoab[0];
						$valfx=$infoab[1];
						$abonos+=$valfx;
						if($idfx!="" && $idfx!=$id_fp){
							$formapago="MIXTA";
							//$rofp=1;
						}
						$fo=$arformas[$idfx];
						$add_desc.=$valfx.":".$fo;
					}
					
				}
				
				$vtn="";
				if($consecutivo==""){
					$consecutivo="Cot.";
					$flagi="print_comanda_out";
					$flags="edit_fact";
					$vtn="<button class='btn btn-danger btn-xs' target='_blank' onclick=\"getDialog('$sender?flag=facturar_cot&id_ped=$id_pedido','600','Facturar','','','reloaHash()')\"><i class='fa fa-check'></i> Facturar</button><small><br />ORDEN:$id_pedido</small>";
				}else{
					$flagi="fact_pedido4";
					$flags="edit_fact";
					if($id_factura>0){
						if($f_estado==1){
							$vtn="<button class='btn btn-danger btn-xs' target='_blank' onclick=\"getDialog('$sender?flag=anula_factura&id_fact=$id_factura','200','Anular','','','reloaHash()')\"><i class='fa fa-warning'></i> Anular</button>";
							if($fel==1 && $fel_user!=""){
								if($fel_estado=="Accepted"){
									$vtn.="<button onclick=\"getDialog('$sender?flag=fel_info&id_fac=$id_factura','500','Factura\ Electronica')\" title='Factura enviada' class='btn btn-success btn-xs' target='_blank'><i class='fa fa-check'></i> Dian OK</button>";
								
								}elseif($fel_estado=="Draft"){
									$vtn.="<button onclick=\"getDialog('$sender?flag=fel_info&id_fac=$id_factura','500','Factura\ Electronica')\" title='Factura enviada' class='btn btn-info btn-xs' target='_blank'><i class='fa fa-clock-o'></i> En Operador</button>";
								}else{
									$vtn.="<button onclick=\"getDialog('$sender?flag=fel_info&id_fac=$id_factura','500','Factura\ Electronica')\" title='Factura con errores' class='btn btn-danger btn-xs' target='_blank'><i class='fa fa-warning'></i> Dian Errores</button>";
								}
							}else{
								if($fel_user!=""){
									$vtn.="<button  onclick=\"getDialog('$sender?flag=fel_send&id_ped=$id_pedido','500','Factura\ Electronica')\"  title='Factura no enviada' class='btn btn-warning btn-xs' target='_blank'><i class='fa fa-warning'></i>Enviar a DIAN</button>
									
									";
							
								}
								
							}
						}else{
							$vtn="<small class='red'> Anulada</small>";
						}
					}
					if($knzval==1 ){
						$vtn.="<button class='btn btn-danger btn-xs' target='_blank' onclick=\"getDialog('$sender?flag=edit_conz&id_ped=$id_pedido','200','Editar','','','reloaHash()')\"><i class='fa fa-edit'></i> $consecutivo</button><small><br />ORDEN:$id_pedido</small>";
					}else{
						$vtn.="<small><br />CONS: ".$consecutivo."<br />ORDEN: $id_pedido</small>";
					}
					
					
				
				}
				if($_SESSION["restprofile"]!="A"){
					$vtn=$consecutivo;
				}
				if(isset($_GET["xls"])){
					if($consecutivo>0){
						$vtn=$consecutivo;
						if($f_estado==0){
							$vtn.=" (ANULADA)";
						}
					}else{
						$vtn="Cotiz";
					}
				
				}
				$cliente=$rowInt["CLIENTE"];
				$m_tipo=$rowInt["TIPO"];
				$fecha=$rowInt["FECHA"];



				if($f_estado==1 || $f_estado==""){
					$totalventas+=$pago;
					$lastpago=$pago-$abonos;
				}
				
				$add_desc.=" ".$lastpago.":".$oriforma;
				$btprop="";
				if(!isset($_GET["xls"])){
					$val = number_format($pago,0);
					$prop = number_format($propina,0);
					if($propina>0 && $_SESSION["restprofile"]=="A"){
						$btprop="<button class='btn btn-minier btn-danger' title='Editar propina' onclick=\"getDialog('$sender?flag=edit_propins&id_pedido=$id_pedido','300','Editar\ Propina','','','reloaHash()')\"><i class='fa fa-edit'></i></button>";
					}
					if($abonos>0){
						$descpago="<br /><small>".$add_desc."</small>";
					}else{
						$descpago="";
					}
				}else{
					$val = $pago;
					$prop=$propina;
					$descpago="";
				}
				$icopdx="<i class='fa fa-hand-o-right green'></i>";
				if($pdx==1){
					$icopdx="<i class='fa fa-thumbs-o-up orange'></i>";
				}


				$reporte.="
				<tr>
					<td>$vtn
					
					</td><td>$nombre</td><td>$cliente</td><td>$fecha</td><td><button class='btn btn-xs btn-warning' onclick=\"getDialog('Admin/site_edit_servicios.php?flag=cambia_fp&id_serv=$id_serv&id_ped=$id_pedido&cur=$id_fp&ro=$rofp','200','Editar')\"><i class='fa fa-edit'></i> $formapago</button></td><td>".$val." $descpago</td><td>".$prop." $btprop</td><td>";
					if(!isset($_GET["xls"])){
					$reporte.="<button class='btn btn-xs btn-primary' onclick=\"getDialog('$sender?flag=$flagi&id_ped=$id_pedido')\">Imprimir</button>
					";
					}
					if($factus!=""){
						if(!isset($_GET["xls"])){
							$reporte.="
							<a class='btn btn-info btn-xs' target='_blank' href=\"Admin/pdf_fact.php?id_ped=$id_pedido\">Pdf</a>
							";
						}
					}
				
				if($_SESSION["restprofile"]=="A"){
					if(!isset($_GET["xls"])){
						$reporte.="
						<button class='btn btn-warning btn-xs' target='_blank' onclick=\"getDialog('$sender?	flag=$flags&id_ped=$id_pedido','600','Editar\ Factura','','','reloaHash()')\"><i class='fa fa-edit'></i></button>
						";
					}
				}
				if($_SESSION["restprofile"]=="A"){
					if(!isset($_GET["xls"])){
						if($fel_estado=="" || $fel_estado=="0"){
							$reporte.="
							<button class='btn btn-danger btn-xs' target='_blank' onclick=\"getDialog('$sender?	flag=del_ped&id_ped=$id_pedido&id_fact=".$rowInt["CONSECUTIVO"]."','600','Borrar\ Factura','','','reloaHash()')\"><i class='fa fa-trash'></i></button>

							<button class='btn btn-warning btn-xs' alt='Modificar cliente' title='Modificar Cliente' target='_blank' onclick=\"getDialog('$sender?flag=change_client&id_ped=$id_pedido','300','Editar\ Cliente','','','reloaHash()')\"><i class='fa fa-user'></i></button>

							";
						}
						
					}
				}
				if(isset($_GET["xls"])){
					if($abonos>0) $reporte.=$add_desc;
				}
				$reporte.=" ".$icopdx."
				</td></tr>";
			}
		}
		$reporte.="</tbody></table><br />";
		if(isset($_GET["xls"])){
			header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
			header("Content-Disposition: attachment;filename=\"reporteFacturas.xls\"");
			header("Cache-Control: max-age=0");
			echo $reporte;
			exit;
		}else{
			echo $gf->utf8("
			<h4>Total:".number_format($totalventas,0)."</h4>$reporte<br /><h4>Total:".number_format($totalventas,0)."</h4>
			<a href='$sender?flag=fact_history&key=$id_serv&xls=1' target='_blank' class='btn btn-primary'>Exportar <i class='fa fa-share'></i></a>
			
			
			");
		}
		
	}elseif($actividad=="change_client"){
		$id_ped=$gf->cleanVar($_GET["id_ped"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$curcli=$gf->dataSet("SELECT ID_CLIENTE FROM facturas WHERE ID_PEDIDO='$id_ped' ORDER BY ID_PEDIDO");
		if(count($curcli)>0){
			$cur_cliente=$curcli[0]["ID_CLIENTE"];
		}
		$rscl = $gf->dataSet("SELECT CL.ID_CLIENTE, CL.IDENTIFICACION, CL.NOMBRE FROM sitios S JOIN clientes CL ON CL.ID_SITIO=S.ID_SITIO WHERE S.ID_SITIO='{$_SESSION["restbus"]}' ORDER BY CL.NOMBRE");

		echo $gf->utf8("Selecciona el cliente para cambiar en la factura<br />
		<select id='cliente_fact' style='width:100%;' name='cliente_fact' class='form-control unival_changeclient chosen'>
		");
		if(count($rscl)>0){
			foreach ($rscl as $rwcl) {
				$id_cli=$rwcl["ID_CLIENTE"];
				$iden=$rwcl["IDENTIFICACION"];
				$namecli=$rwcl["NOMBRE"];
				$sels="";
				if($__CL_COMODIN==$id_cli) $namecli.=" [COMODIN] ";
				if($id_cli==$cur_cliente) $sels="selected='selected'";
				if($namecli!=""){
					echo $gf->utf8("<option value='$id_cli' $sels>$namecli ($iden)</option>");
				}
				
			}
		}
		echo $gf->utf8("</select><hr />
		<button class='btn btn-danger btn-sm' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=changeclient_go&id_ped=$id_ped&rnd=$rnd','','5000','unival_changeclient')\">Cambiar cliente</button>
		");
		
	}elseif($actividad=="changeclient_go"){
		$id_ped=$gf->cleanVar($_GET["id_ped"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$cliente_fact=$_POST["cliente_fact"];

		$gf->dataIn("UPDATE pedidos SET ID_CLIENTE='$cliente_fact' WHERE ID_PEDIDO='$id_ped'");
		$gf->dataIn("UPDATE facturas SET ID_CLIENTE='$cliente_fact' WHERE ID_PEDIDO='$id_ped'");
		echo $gf->utf8("Se ha cambiado el cliente a la factura
		<input id='callbackeval' type='hidden' value=\"closeD('$rnd')\" /> 
		
		");

	}elseif($actividad=="fel_info"){
		$id_fac=$gf->cleanVar($_GET["id_fac"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);

		$resultInt = $gf->dataSet("SELECT F.CONSECUTIVO, DATE(F.FECHA) AS FECHA, CL.IDENTIFICACION, CL.NOMBRE AS CLIENTE, CL.DIRECCION, CL.TELEFONO, CL.CORREO, CL.TIPO_ID, M.ID_MESA, M.TIPO, M.NOMBRE, P.ID_PEDIDO, P.DCTO, CL.HOMO_FEL, FP.CAJA, F.FEL, F.FEL_CUFE, F.FEL_NOMBRE, F.FEL_ESTADO, F.FEL_RESPUESTA FROM mesas AS M JOIN pedidos AS P ON M.ID_MESA=P.ID_MESA JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO JOIN clientes CL ON CL.ID_CLIENTE=F.ID_CLIENTE JOIN formas_pago FP ON FP.ID_FP=P.ID_FP WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND F.ID_FACTURA='$id_fac' ORDER BY P.ID_PEDIDO");
		
		if(count($resultInt)>0){
	
			$rowInt=$resultInt[0];

			$id_pedido=$rowInt["ID_PEDIDO"];
			$f_consecutivo=$rowInt["CONSECUTIVO"];
			$f_consecutivo=str_pad($f_consecutivo, 6, '0', STR_PAD_LEFT);
			$f_fecha=$rowInt["FECHA"];
			$f_cliente=$rowInt["CLIENTE"];
			$fel_estado=$rowInt["FEL_ESTADO"];
			$fel=$rowInt["FEL"];
			$fel_nombre=$rowInt["FEL_NOMBRE"];
			$fel_cufe=$rowInt["FEL_CUFE"];
			$fel_respuesta=$rowInt["FEL_RESPUESTA"];
		
			if($fel==1){
				$tipo="Factura Electronica";
				if($fel_estado=="Accepted"){
					$estado_factura_el="Aceptada por la DIAN";
				}elseif($fel_estado=="Draft"){
					$estado_factura_el="Recibida por operador, sin enviar a la DIAN";
				}elseif($fel_estado=="Rejected"){
					$estado_factura_el="Rechazada por la DIAN";
				}

			}else{
				$tipo="Sin factura electronica";
				
			}


			echo $gf->utf8("
			FACTURA $f_cliente<br />
			FECHA: $f_fecha<br />
			ID: $fel_nombre<br />
			CUFE: $fel_cufe<br />
			ESTADO: $estado_factura_el<br />	
			<hr />
			<button class='btn btn-warning btn-sm pull-left' onclick=\"closeD('$rnd')\">Salir <i class='fa fa-check'></i></button>

			
			");



		}else{
			echo "No se encuentra la factura";
		}
	}elseif($actividad=="edit_propins"){
		$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		if($id_pedido!=""){
			$rss = $gf->dataSet("SELECT PROPINA FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
			if(count($rss)>0){
				$curpro=$rss[0]["PROPINA"];
				echo $gf->utf8("Editar el valor de la propina:<br /><input type='number' value='$curpro' class='form-control univalfacticpro' name='PROPI' id='PROPI' onchange=\"cargaHTMLvars('state_proceso','$sender?flag=edit_propins_go&id_pedido=$id_pedido&rnd=$rnd','','5000','univalfacticpro')\" /><br />
				<button class='btn btn-warning btn-sm pull-left' onclick=\"closeD('$rnd')\">Terminar <i class='fa fa-check'></i></button>
				");
			}else{
				echo $gf->utf8("Error: no se encuentra el pedido");
			}
		}
	}elseif($actividad=="edit_propins_go"){
		$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
		$newcons=$_POST["PROPI"];

		$ok=$gf->dataIn("UPDATE pedidos SET PROPINA='$newcons' WHERE ID_PEDIDO='$id_pedido'");
		if($ok){
			$gf->log($_SESSION["restbus"],0,0,"PROPINA EDITADA $id_pedido Nuevo valor: $newcons",$_SESSION["restuiduser"]);
			echo "ok";
		}
	}elseif($actividad=="anula_factura_go"){
		$id_factura=$gf->cleanVar($_GET["id_fact"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$gf->dataIn("UPDATE facturas SET ESTADO=0 WHERE ID_FACTURA='$id_factura'");
		$gf->log($_SESSION["restbus"],0,0,"FACTURA ANULADA $id_factura",$_SESSION["restuiduser"]);
		echo $gf->utf8("<div class='alert alert-warning alert-dismissible' style='font-size:16px;'>Proceso finalizado</div><br />

		
		<button class='btn btn-warning btn-sm pull-left' onclick=\"closeD('$rnd')\">Terminar <i class='fa fa-check'></i></button>
		");

	}elseif($actividad=="anula_factura"){
		$id_factura=$gf->cleanVar($_GET["id_fact"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		echo $gf->utf8("<div class='alert alert-warning alert-dismissible' style='font-size:16px;'>Se va a anular la factura generada para este pedido, el pedido no ser&aacute; borrado</div><hr />
		<button class='btn btn-primary btn-sm pull-left' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=anula_factura_go&id_fact=$id_factura&rnd=$rnd')\">Continuar <i class='fa fa-check'></i></button>
		<button class='btn btn-danger btn-sm pull-right' onclick=\"closeD('$rnd')\">Cancelar <i class='fa fa-remove'></i></button>
		");



	}elseif($actividad=="edit_conz"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);

		$facti=$gf->dataSet("SELECT ID_FACTURA, CONSECUTIVO, PREFIJO FROM facturas WHERE ID_PEDIDO='$id_pedido' AND ESTADO=1");
		$id_fact=$facti[0]["ID_FACTURA"];
		$consecutivo=$facti[0]["CONSECUTIVO"];

		echo $gf->utf8("Consecutivo:<input type='number' value='$consecutivo' class='form-control univalfacti' name='FACT' id='FACT' onchange=\"cargaHTMLvars('state_proceso','$sender?flag=upfact&id_fact=$id_fact','','5000','univalfacti')\" />");

	}elseif($actividad=="upfact"){
		$id_fact=$gf->cleanVar($_GET["id_fact"]);
		$newcons=$_POST["FACT"];

		$ok=$gf->dataIn("UPDATE facturas SET CONSECUTIVO='$newcons' WHERE ID_FACTURA='$id_fact'");
		if($ok){
			$gf->log($_SESSION["restbus"],0,0,"CONSECUTIVO EDITADO $id_fact - No. $newcons",$_SESSION["restuiduser"]);
			echo "ok";
		}

	}elseif($actividad=="del_ped"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$id_fact=$gf->cleanVar($_GET["id_fact"]);
		$rsFac=$gf->dataSet("SELECT ID_FACTURA FROM facturas WHERE ID_PEDIDO='$id_pedido'");
		if(count($rsFac)>0){
			echo $gf->utf8("Se ha solicitado la eliminaci&oacute;n de una cuenta facturada<br />
			Tienes la posibilidad de eliminar solo la factura y conservar la cotizaci&oacute;n o eliminar todo<br />

			<big class='red'>Este proceso es irreversible, qu&eacute; deseas hacer?</big><hr />

			<button class='btn btn-warning' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=del_ped2&id_ped=$id_pedido&id_fact=$id_fact&rnd=$rnd&all=0')\">Eliminar solo factura</button>
			<button class='btn btn-danger' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=del_ped2&id_ped=$id_pedido&id_fact=$id_fact&rnd=$rnd&all=1')\">Eliminar toda la cuenta</button>
			<button class='btn btn-success pull-right' onclick=\"closeD('$rnd')\">Cancelar</button>

			");
		}else{
			echo $gf->utf8("Se ha solicitado la eliminaci&oacute;n de una cuenta cotizada<br />
			<br />

			<big class='red'>Este proceso es irreversible, qu&eacute; deseas hacer?</big><hr />

			<button class='btn btn-danger' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=del_ped2&id_ped=$id_pedido&id_fact=$id_fact&rnd=$rnd&all=1')\">Eliminar la cuenta</button>
			<button class='btn btn-success pull-right' onclick=\"closeD('$rnd')\">Cancelar</button>

			");
		}
	
	}elseif($actividad=="del_ped2"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$id_fact=$gf->cleanVar($_GET["id_fact"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$all=$gf->cleanVar($_GET["all"]);
		$formaPago = $gf->dataSet("SELECT fp.NOMBRE, pedidos.PAGO FROM `pedidos` JOIN formas_pago fp ON fp.ID_FP=pedidos.ID_FP WHERE pedidos.ID_PEDIDO='$id_pedido'");
		$consecutivo = $gf->dataSet("SELECT CONSECUTIVO FROM facturas WHERE ID_PEDIDO='$id_pedido'");
		if($all==1){
			$gf->dataIn("DELETE FROM facturas WHERE ID_PEDIDO='$id_pedido'");
			$gf->dataIn("DELETE FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
			$gf->dataIn("DELETE FROM sillas WHERE ID_PEDIDO='$id_pedido'");
			$gf->dataIn("DELETE FROM sillas_platos WHERE ID_SILLA NOT IN(SELECT ID_SILLA FROM sillas ORDER BY ID_SILLA)");
			$gf->log($_SESSION["restbus"],0,$id_pedido,"PEDIDO ELIMINADO POST-COBRO",$_SESSION["restuiduser"]);
			echo $gf->utf8("Proceso terminado");
		}else{

			$consex=$gf->dataSet("SELECT CONSECUTIVO FROM facturas WHERE ID_PEDIDO='$id_pedido'");
			if(count($consex)>0){
				$consecu=$consex[0]["CONSECUTIVO"];
			}else{
				$consecu=0;
			}
			$gf->dataIn("DELETE FROM facturas WHERE ID_PEDIDO='$id_pedido'");
			$gf->dataIn("UPDATE pedidos SET PRINT='$consecu' WHERE ID_PEDIDO='$id_pedido'");
			echo $gf->utf8("Proceso terminado");
			$gf->log($_SESSION["restbus"],0,$id_pedido,"FACTURA ELIMINADA POST-COBRO",$_SESSION["restuiduser"]);
		}

		$sort = $gf->dataSet('SELECT SORT FROM `sitios` WHERE ID_SITIO='.$_SESSION["restbus"]);

		if($sort[0]["SORT"]){
			$gf->dataIn("UPDATE facturas SET CONSECUTIVO=(CONSECUTIVO - 1) WHERE CONSECUTIVO > $id_fact");
		}

		echo $gf->utf8("
		<button classs='btn btn-success pull-right' onclick=\"closeD('$rnd')\">Terminar</button>
		");
		
		$notificacion = $gf->dataSet('SELECT SMS_NOTIFICACION FROM `sitios` WHERE ID_SITIO='.$_SESSION["restbus"]);
		if($notificacion[0]['SMS_NOTIFICACION']==5 || $notificacion[0]['SMS_NOTIFICACION']==4){
			$cantidad = $gf->dataSet('SELECT SMS FROM `sitios` WHERE ID_SITIO='.$_SESSION["restbus"]);
			if($cantidad[0]['SMS'] > 1){
				$newCantidad = intval($cantidad[0]['SMS']) - 1;
				$ok=$gf->dataIn("UPDATE sitios SET SMS='$newCantidad' WHERE ID_SITIO=".$_SESSION["restbus"]);
				if($ok){
					$celular = $_SESSION["restcelular"];
					$mensaje = 'Datafeed '.$_SESSION["restbusname"].": ELIMINACION DE Fac #".$consecutivo[0]["CONSECUTIVO"]." por $".number_format($formaPago[0]['PAGO'])." ".$formaPago[0]['NOMBRE']." por '".$gf->limitar_cadena($_SESSION["restuname"], 15, "...")."'. ".date('Y-m-d h:i A');
					echo $gf->utf8('
					<script>
						$(function(){
							enviarSms("'.$celular.'", "'.$mensaje.'");
						});
					</script>
					');
				}else{
					echo "<script>console.log('Error al enviar SMS');</script>";
				}
			}elseif($cantidad[0]['SMS']==1){
				$newCantidad = intval($cantidad[0]['SMS']) - 1;
				$ok=$gf->dataIn("UPDATE sitios SET SMS='$newCantidad' WHERE ID_SITIO=".$_SESSION["restbus"]);
				if($ok){
					$celular = $_SESSION["restcelular"];
					$mensaje = 'Datafeed '.$_SESSION["restbusname"].": ELIMINACION DE Fac #".$consecutivo[0]["CONSECUTIVO"]." por $".number_format($formaPago[0]['PAGO'])." ".$formaPago[0]['NOMBRE']." por '".$gf->limitar_cadena($_SESSION["restuname"], 15, "...")."'. ".date('Y-m-d h:i A');
					$mensaje2 = 'Datafeed '.$_SESSION["restbusname"].': ¿Quieres que te sigamos informando en tiempo real sobre tu negocio? se ha agotado tu paquete de mensajes por favor contacta a soporte.';
					echo $gf->utf8('
						<script>
						$(function(){
							enviarSms("'.$celular.'", "'.$mensaje.'");
							enviarSms("'.$celular.'", "'.$mensaje2.'");
						});
						</script>
					');
				}else{
					echo "<script>console.log('Error al enviar SMS');</script>";
				}
			}
		}

	}elseif($actividad=="facturar_cot"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);

		$rsCity=$gf->dataSet("SELECT COD, NOMBRE FROM ciudades ORDER BY NOMBRE");
		$selectcities="";
		if(count($rsCity)>0){
			foreach ($rsCity as $rwCity) {
				$dane=$rwCity["COD"];
				$nmcity=$rwCity["NOMBRE"];
				$selectcities.="<option value='$dane'>$nmcity</option>";
			}
		}
		$rsPed=$gf->dataSet("SELECT PAGO, ID_FP, PRINT FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
		$cobrado=$rsPed[0]["PAGO"];
		$consecprev=$rsPed[0]["PRINT"];
		echo $gf->utf8("<div class='alert alert-warning alert-dismissible' style='font-size:16px;'>Se va a generar una factura con base en la cotizaci&oacute;n No. $id_pedido, a continuaci&oacute;n selecciona los datos del cliente</div>");

		echo $gf->utf8("
			<div class='callout callout-default'>
         <h4>INFORMACI&Oacute;N DEL CLIENTE</h4>
				<div class='row'>
				<div class='col-md-4'>
				DOCUMENTO: <input onchange=\"queryClient()\" placeholder='El documento es requerido' type='text' name='num_doc' id='num_doc' class='form-control unival_dix_$id_pedido' />
					</div>
				<div class='col-md-4'>
				TIPO DOCUMENTO: <select name='tipo_doc' id='tipo_doc' class='form-control  unival_dix_$id_pedido'><option value='CC'>C&eacute;dula</option><option value='NI'>Nit</option></select>
				</div>
				<div class='col-md-4'>
				NOMBRE: <input type='text' name='nombre_cliente' id='nombre_cliente' class='form-control unival_dix_$id_pedido' />
				</div>
				<div class='col-md-4'>
				DIRECCION: <input type='text' name='dir_cliente' id='dir_cliente' class='form-control unival_dix_$id_pedido' />
				</div>
				<div class='col-md-4'>
				CIUDAD: <select name='city_cliente' id='city_cliente' class='form-control unival_dix_$id_pedido'>
				$selectcities
				</select>
				</div>
				<div class='col-md-4'>
				TELEFONO: <input type='text' name='tel_cliente' id='tel_cliente' class='form-control unival_dix_$id_pedido' />
				</div>
				<div class='col-md-4'>
				CORREO: <input type='text' name='mail_cliente' id='mail_cliente' class='form-control unival_dix_$id_pedido' />
				</div>

				</div>

				<hr />
				<div class='row'><div class='col-md-6'>
				<ul class='list-group'>
				<li class='list-group-item'>TOTAL PEDIDO: <span class='pull-right' style='font-size:16px;'>".number_format($cobrado)."</span></li>
				</ul>
				
				<hr />
				<button class='btn btn-danger' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=fact_pedido2_post&id_ped=$id_pedido&rnd=$rnd&consec=$consecprev','','4000','unival_dix_$id_pedido')\">Facturar</button>



			</div>

			");


	}elseif($actividad=="edit_fact"){
		if($_SESSION["restprofile"]=="A"){
			$id_pedido=$gf->cleanVar($_GET["id_ped"]);
			$rnd=$gf->cleanVar($_GET["rnd"]);
			
	
			$resultChair = $gf->dataSet("SELECT PE.PAGO, PE.ID_FP, PE.DCTO, S.ID_SILLA, S.ID_PEDIDO, S.COLOR, S.GENDER, PE.ID_MESA, PE.ID_TENDER, PE.CHEF, PE.CAJA, PE.DIRECCION, PE.DESPACHADO, PE.PAGADO, PE.DENOM, S.OBSERVACION, P.ID_ITEM, P.ID_PLATO, P.CANTIDAD, PL.NOMBRE AS PLATO, P.LISTO, P.ENTREGADO, COUNT(P.ID_PLATO) AS PEDIDOS, SUM(P.LISTO) AS LISTOS, SUM(P.ENTREGADO) AS ENTREGA, SUM(P.PRINTED) AS COMANDAR, M.NOMBRE AS MESA, P.PRINTED, M.TIPO, GROUP_CONCAT(CONCAT(C.NOMBRE,'|',R.ESTADO,'|',RO.NOMBRE,'|',ROP.OPTIS) SEPARATOR '+*+') AS COMPOSITION FROM mesas AS M JOIN pedidos AS PE ON PE.ID_MESA=M.ID_MESA LEFT JOIN sillas AS S ON(PE.ID_PEDIDO=S.ID_PEDIDO) LEFT JOIN sillas_platos AS P ON(S.ID_SILLA=P.ID_SILLA) LEFT JOIN platos AS PL ON(P.ID_PLATO=PL.ID_PLATO)LEFT JOIN platos_composicion C ON P.ID_PLATO=C.ID_PLATO LEFT JOIN sillas_platos_composicion R ON R.ID_ITEM=P.ID_ITEM AND R.ID_RACION=C.ID_RACION LEFT JOIN racion_opciones RO ON R.ID_OPCION=RO.ID_OPCION LEFT JOIN (SELECT ID_RACION,GROUP_CONCAT(ID_OPCION) OPTIS FROM racion_opciones GROUP BY ID_RACION ORDER BY ID_RACION)ROP ON ROP.ID_RACION=C.ID_RACION WHERE PE.ID_PEDIDO='$id_pedido' GROUP BY P.ID_ITEM ORDER BY P.ID_ITEM");
	
			if(count($resultChair)>0){
				$rowChair=$resultChair[0];
				$id_chair=$rowChair["ID_SILLA"];
				$observacion=$rowChair["OBSERVACION"];
				$id_pedido=$rowChair["ID_PEDIDO"];
				$cobrado=$rowChair["PAGO"];
				$descuento=$rowChair["DCTO"];
				$mesa=$rowChair["MESA"];
				$id_fp=$rowChair["ID_FP"];
				$id_mesa=$rowChair["ID_MESA"];
				$tipo=$rowChair["TIPO"];
				$direccion=$rowChair["DIRECCION"];
				$pagado=$rowChair["PAGADO"];

				echo $gf->utf8("
				<div id='ch_$id_chair' idch='$id_chair' class='box box-danger'>
					<div class='box-header'><b>MODIFICAR CUENTA No. $id_pedido - MESA: $mesa</b></div>
					<div class='box-body'>
					<table class='latabla table table-striped' width='100%'><tr><td></td><td>ITEM</td><td>CANT</td><td>OPC</td></tr>");
				$nprod=0;
				foreach($resultChair as $rowChair){
					$id_item=$rowChair["ID_ITEM"];
					$cantidad=$rowChair["CANTIDAD"];
					$plato=$rowChair["PLATO"];
					$listo=$rowChair["LISTO"];
					$printedd=$rowChair["PRINTED"];
					$entregado=$rowChair["ENTREGADO"];
					$composition=$rowChair["COMPOSITION"];
					$composet=explode("+*+",$composition);
					$comps="";
					foreach($composet as $subcomp){
						if($subcomp!=""){
							$subcm=explode("|",$subcomp);
							$nmcom=$subcm[0];
							$stcom=$subcm[1];
							$compost=$subcm[2];
							$naaa=$subcm[3];
							$nopts_ar=explode(",",$subcm[3]);
							$nopts=count($nopts_ar);
							if($stcom==0){
								$comps.="Sin $nmcom, ";
							}else{
								if($nopts>1){
									$comps.="$compost, ";
								}
							}
						}
					}
					if($comps==""){
						
					}else{
						$comps=substr($comps,0,-2);
					}
					
					if($id_item!=""){
						$nprod++;
						$cantii="";
						
						echo $gf->utf8("<tr id='tritem_$id_item'><td><b>$nprod</b></td><td><span style='font-size:17px;'> $plato</span><br/><small>$comps</small></td>
						<td align='center'>
						<div class='input-group'>
							<span class='input-group-addon btn btn-lg btn-danger mimibutton' onclick=\"downNumber('cant_$id_item')\"><i class='fa fa-arrow-down'></i></span>
							<input type='number' onchange=\"cargaHTMLvars('state_proceso','mviews.php?flag=edit_cant&item=$id_item&id_pedido=$id_pedido&id_mesa=$id_mesa&t=$tipo&val='+this.value)\" style='font-size:23px;height:40px;text-align:center;min-width:45px;' value='$cantidad' id='cant_$id_item' class='form-control univalunichair' id='nchairs' name='nchairs'  min='1' max='20' />
							<span class='input-group-addon btn-lg btn btn-success mimibutton' onclick=\"upNumber('cant_$id_item')\"><i class='fa fa-arrow-up'></i></span>
						</div>
						</td><td>
						<button class='btn btn-warning' onclick=\"getDialog('mviews.php?flag=config_plat&id_item=$id_item','600','Editar','','')\"><i class='fa fa-edit'></i></button>
						<button class='btn btn-danger' onclick=\"getDialog('mviews.php?flag=del_itemped&id_item=$id_item&id_pedido=$id_pedido&id_mesa=$id_mesa&t=$tipo&printed=$printedd','300','Borrar')\"><i class='fa fa-trash'></i></button>
						</td></tr>");
					}
				}

				echo $gf->utf8("
				<tfoot>
					<tr><td colspan='4'>
						<button class='btn btn-sm btn-primary' onclick=\"getDialog('mviews.php?flag=additemdlg&id_silla=$id_chair&id_mesa=$id_mesa&id_pedido=$id_pedido&nch=1&ait=0&t=$tipo','600','Agregar','','','cargaHTMLvars(\'ModalContent_$rnd\',\'$sender?flag=edit_fact&id_ped=$id_pedido&rnd=$rnd\')')\"><i class='fa fa-plus'></i> Agregar producto</button>
					</td></tr>
				</tfoot>
				</table>
				</div>
			</div>
				");
			}


			echo $gf->utf8("<div class='row'>
			<div class='col-md-6'>
			INFORMACI&Oacute;N DE LA CUENTA:<br />



			TOTAL PEDIDO: ".number_format($cobrado+$descuento,0)."<br />
			DESCUENTO: ".number_format($descuento,0)."<br />
			PAGADO: ".number_format($cobrado,0)."<br /><hr />
			</div>
			<div class='col-md-6'>
			MODIFICAR DATOS
			<div class='control-group'>
				<label for='DESCUENTO'>
					DESCUENTO
				</label>
				<input type='number' id='DESCUENTO' name='DESCUENTO' value='$descuento' class='form-control unival_dix_$id_pedido' />
			</div>

			<div class='control-group'>
				<label for='ID_FP'>
					CAMBIAR FORMA DE PAGO
				</label>
				");
				$formas=$gf->dataSet("SELECT ID_FP, NOMBRE, ICONO FROM formas_pago WHERE ID_SITIO=:sitio ORDER BY POSICION, NOMBRE",array(":sitio"=>$_SESSION["restbus"]));
				echo $gf->utf8("
				<select name='ID_FP' id='ID_FP' class='form-control unival_dix_$id_pedido'>");
				if(count($formas)>0){
					foreach($formas as $fpa){
						$idfp=$fpa["ID_FP"];
						$nombrefp=$fpa["NOMBRE"];
						$checkin="";
						if($id_fp==$idfp) $checkin="selected='selected'";
						echo $gf->utf8("<option value='$idfp' $checkin>$nombrefp</option>");
					}
				}
				echo $gf->utf8("
				</select>
			</div>
			</div>
			</div>
			<hr />
			<div class='row'>
				<div class='col-md-12'>
						<button class='btn btn-warning pull-left' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=edit_fact2&id_pedido=$id_pedido&t=$tipo&rnd=$rnd','','10000','unival_dix_$id_pedido')\">Modificar</button>
						<button class='btn btn-danger pull-right' onclick=\"closeD('$rnd')\">Cancelar</button>
				</div>
			</div>
			");
	
		}else{
			echo $gf->utf8("<div class='alert alert-warning alert-dismissible'>Operaci&oacute;n reservada solo para Administradores del establecimiento</div>");
		}
		


	}elseif($actividad=="edit_fact2"){

		$rnd=$gf->cleanVar($_GET["rnd"]);
		$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
		$descuento=$_POST["DESCUENTO"];
		$id_fp=$_POST["ID_FP"];
		$t=$gf->cleanVar($_GET["t"]);

		$camprecio = ($t=="D") ? "PRECIO_DOM" : "PRECIO";

		$acumprice=0;
		$acumbase=0;
		$acumimp=0;
		$resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, SP.CANTIDAD, SP.LISTO, P.NOMBRE, P.DESCRIPCION, SP.PRECIO AS PRECIO, IF(IM.PORCENTAJE>0,(SP.PRECIO/(1+(IM.PORCENTAJE/100))),SP.PRECIO) AS PREBASE FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN impuestos IM ON IM.ID_IMPUESTO=P.ID_IMPUESTO WHERE S.ID_PEDIDO='$id_pedido' GROUP BY SP.ID_ITEM ORDER BY S.ID_SILLA");
		if(count($resultChairs)>0){
			foreach($resultChairs as $rwChair){
				$id_item=$rwChair["ID_ITEM"];
				$id_silla=$rwChair["ID_SILLA"];
				$cantidad=$rwChair["CANTIDAD"];
				$precio=$rwChair["PRECIO"];
				$precio_base=$rwChair["PREBASE"];
				$impuestos=$precio-$precio_base;
				$acumbase+=($precio_base*$cantidad);
				$acumimp+=($impuestos*$cantidad);
				$precio_total=$precio_base+$impuestos;
				$acumprice+=($precio_total*$cantidad);
				
			}
			$formas=$gf->dataSet("SELECT ID_FP, NOMBRE, ICONO FROM formas_pago WHERE ID_FP=:fp ORDER BY POSICION, NOMBRE",array(":fp"=>$id_fp));
			$forma_pago=$formas[0]["NOMBRE"];
			echo $gf->utf8("La nueva informaci&oacute;n del pedido es la siguiente:<br />
				TOTAL PEDIDO:  ".number_format($acumprice,0)."<br />
				DESCUENTO: ".number_format($descuento,0)."<br />
				TOTAL PAGADO: ".number_format($acumprice-$descuento,0)."<br />
				FORMA DE PAGO: $forma_pago<hr />
				<input type='hidden' id='PAGO' name='PAGO' value='$acumprice' class='unival_dix_$id_pedido' />
				<input type='hidden' id='DCTO' name='DCTO' value='$descuento' class='unival_dix_$id_pedido' />
				<input type='hidden' id='ID_FP' name='ID_FP' value='$id_fp' class='unival_dix_$id_pedido' />
				<input type='hidden' id='IMPUESTO' name='IMPUESTO' value='$acumimp' class='unival_dix_$id_pedido' />
				<button class='btn btn-warning pull-left' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=edit_fact3&id_pedido=$id_pedido&t=$t&rnd=$rnd','','10000','unival_dix_$id_pedido')\">Modificar</button>
				<button class='btn btn-danger pull-right' onclick=\"closeD('$rnd')\">Cancelar</button>

			");

		}

	}elseif($actividad=="edit_fact3"){
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
		$pago=$_POST["PAGO"];
		$descuento=$_POST["DCTO"];
		$impuesto=$_POST["IMPUESTO"];
		$pago-=$descuento;
		$id_fp=$_POST["ID_FP"];
		$ok = $gf->dataIn("UPDATE pedidos SET PAGO='$pago', IMPUESTO='$impuesto', ID_FP='$id_fp', DCTO='$descuento' WHERE ID_PEDIDO='$id_pedido'");
		if($ok){
			echo $gf->utf8("Proceso terminado<br />");
		}else{
			echo $gf->utf8("Hubo un error al realizar la modificaci&oacute;n de los datos<br />");
		}
		echo $gf->utf8("<hr />
		<button class='btn btn-warning pull-right' onclick=\"closeD('$rnd')\">Terminar</button>
		");
		$gf->log($_SESSION["restbus"],0,$id_pedido,"FACTURA MODIFICADA PAGO: $pago, DCTO: $descuento, ID FORMA PAGO: $id_fp",$_SESSION["restuiduser"]);

	}elseif($actividad=="print_comanda_out"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);

		//$oka=$gf->dataIn("UPDATE pedidos SET PRINTED='0' WHERE ID_PEDIDO = '$id_pedido'");
		//$oka=$gf->dataIn("UPDATE sillas_platos SET PRINTED='0' WHERE ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido')");
		//if($ok){
		echo $gf->utf8("
		Enviando.....
		<script>
			$(function(){
				setTimeout(function(){
					closeD('$rnd');
					sockEmitir('comandar',{id_pedido:'$id_pedido',parte:'todo'});
					
				},2000);
			});
		</script>");
		$gf->log($_SESSION["restbus"],0,$id_pedido,"IMPRIMIR COMANDA",$_SESSION["restuiduser"]);
	//}
		
	}elseif($actividad=="get_client"){
		$docu=$_POST["doc"];
		$rsCf = $gf->dataSet("SELECT PUNTO_PESO, PESO_PUNTO FROM sitios WHERE ID_SITIO='{$_SESSION["restbus"]}'");
		$punto_peso= $rsCf[0]["PUNTO_PESO"];
		$peso_punto= $rsCf[0]["PESO_PUNTO"];
		$cliente=$gf->dataSet("SELECT C.TIPO_ID, C.ID_CLIENTE, C.IDENTIFICACION, C.NOMBRE, C.TELEFONO, C.DIRECCION, C.CORREO, SUM(F.PUNTOS) AS PUNTOS, C.CIUDAD FROM clientes C LEFT JOIN facturas F ON F.ID_CLIENTE=C.ID_CLIENTE AND F.ESTADO=1 WHERE C.IDENTIFICACION=:ident AND C.ID_SITIO=:sitio GROUP BY C.ID_CLIENTE ORDER BY C.NOMBRE",array(":ident"=>$docu,":sitio"=>$_SESSION["restbus"]));
		if(count($cliente)>0){
			$rw=$cliente[0];

			$id_cliente=$rw["ID_CLIENTE"];

			$rsSali=$gf->dataSet("SELECT ID_CLIENTE, SUM(PUNTOS) AS SALIDAS FROM puntos_salida WHERE ID_CLIENTE='$id_cliente' GROUP BY ID_CLIENTE ORDER BY ID_CLIENTE");
			if(count($rsSali)>0){
				$SALIDAS=$rsSali[0]["SALIDAS"];
			}else{
				$SALIDAS=0;
			}

			$TIPO_ID=$rw["TIPO_ID"];
			$IDENTIFICACION=$rw["IDENTIFICACION"];
			$NOMBRE=$rw["NOMBRE"];
			$DIRECCION=$rw["DIRECCION"];
			$TELEFONO=$rw["TELEFONO"];
			$CORREO=$rw["CORREO"];
			$PUNTOS=$rw["PUNTOS"];
			$CIUDAD=$rw["CIUDAD"];
			$DISPONIBLES=$PUNTOS-$SALIDAS;
			$BILLEGAS=$DISPONIBLES*$punto_peso;
			echo $gf->utf8("{\"TIPO_ID\":\"".$TIPO_ID."\",\"IDENTIFICACION\":\"".$IDENTIFICACION."\",\"NOMBRE\":\"".$NOMBRE."\",\"DIRECCION\":\"".$DIRECCION."\",\"CIUDAD\":\"".$CIUDAD."\",\"TELEFONO\":\"".$TELEFONO."\",\"CORREO\":\"".$CORREO."\",\"PUNTOS\":\"".$DISPONIBLES."\",\"BILLEGAS\":\"".$BILLEGAS."\"}");
		}else{
			echo "{}";
		}
	}elseif($actividad=="fact_pedido1"){

		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$id_cliente=0;

		$rsCity=$gf->dataSet("SELECT COD, NOMBRE FROM ciudades ORDER BY NOMBRE");
		$selectcities="";
		if(count($rsCity)>0){
			foreach ($rsCity as $rwCity) {
				$dane=$rwCity["COD"];
				$nmcity=$rwCity["NOMBRE"];
				$selectcities.="<option value='$dane'>$nmcity</option>";
			}
		}
		

		if($_SESSION["restfastmode"]==1){
			$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.NOMBRE, M.TIPO, P.ID_CLIENTE, P.ID_PEDIDO, P.DIRECCION, P.CHEF, P.CAJA, U.NOMBRES AS TENDER FROM mesas AS M RIGHT JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CHEF<>'0000-00-00 00:00:00' AND P.CIERRE='0000-00-00 00:00:00' AND P.PAGO='0') JOIN usuarios U ON U.ID_USUARIO=P.ID_TENDER WHERE P.ID_SERVICIO='{$_SESSION["restservice"]}' AND P.ID_PEDIDO='$id_pedido'  GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
			
			if(count($resultInt)>0){
				foreach($resultInt as $rowInt){
					$acumbase=0;
					$acumprice=0;
					$acumimp=0;
					$id_mesa=$rowInt["ID_MESA"];
					$id_cliente=$rowInt["ID_CLIENTE"];
					$nombre=$rowInt["NOMBRE"];
					$m_tipo=$rowInt["TIPO"];
					$tipo=$rowInt["TIPO"];
					$perc=100;
					$dispatch=1;
					$direccion=$rowInt["DIRECCION"];
					$id_pedido=$rowInt["ID_PEDIDO"];
					$caja=$rowInt["CAJA"];
					$chef=$rowInt["CHEF"];
					$tender=$rowInt["TENDER"];
					
					$nsi=0;
					$inisill=0;
					$resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, SP.CANTIDAD, SP.LISTO, P.NOMBRE, P.DESCRIPCION, SP.PRECIO AS PRECIO, IF(I.PORCENTAJE>0,(SP.PRECIO/(1+(I.PORCENTAJE/100))),SP.PRECIO) AS PREBASE FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN impuestos I ON P.ID_IMPUESTO=I.ID_IMPUESTO WHERE S.ID_PEDIDO='$id_pedido' GROUP BY SP.ID_ITEM ORDER BY S.ID_SILLA");
					if(count($resultChairs)>0){
						
						$bklass="ui-semiwhite";
						$tot_desc=0;
						foreach($resultChairs as $rwChair){
							
							$id_item=$rwChair["ID_ITEM"];
							$id_silla=$rwChair["ID_SILLA"];
							$observacion=$rwChair["OBSERVACION"];
							$cantidad=$rwChair["CANTIDAD"];
							$nombre_plato=$rwChair["NOMBRE"];
							$descripcion=$rwChair["DESCRIPCION"];
							$listo=$rwChair["LISTO"];
							$precio=$rwChair["PRECIO"];
							$precio_base=$rwChair["PREBASE"];
							
							$rsDesc=$gf->dataSet("SELECT SUM(PC.BAJA_PRECIO) AS DESCUENTA FROM  sillas_platos_composicion SPC LEFT JOIN platos_composicion AS PC ON PC.ID_RACION=SPC.ID_RACION AND PC.BAJA_PRECIO>0 AND SPC.ESTADO='0' WHERE SPC.ID_ITEM='$id_item' GROUP BY SPC.ID_ITEM");
							if(count($rsDesc)>0){
								$descuenta=$rsDesc[0]["DESCUENTA"]*$cantidad;
							}
							$tot_desc+=$descuenta;

							if($precio_base==0){
								$precio_base=$precio;
								$impuestos=0;
							}else{
								$impuestos=$precio-$precio_base;
							}
								
							
							$acumbase+=($precio_base*$cantidad);
							$acumimp+=($impuestos*$cantidad);
							$precio_total=$precio_base+$impuestos;
							$acumprice+=($precio_total*$cantidad);
							if($id_silla!=$inisill){
								$nsi++;
								$inisill=$id_silla;
							}
							$inisill=$id_silla;
						}
						$sillas=$nsi;

						if($_SESSION["restanticipos"]==1){

							$rsAbonos=$gf->dataSet("SELECT A.ID_ABONO, A.FECHA, A.VALOR, A.OBSERVACION, U.NOMBRES AS USUARIO FROM pedidos_abonos A JOIN usuarios U ON U.ID_USUARIO=A.ID_USUARIO WHERE A.ID_PEDIDO='$id_pedido'");
							$tot_abonos=0;
							$abos="";
							if(count($rsAbonos)>0){
								$tot_abonos=0;
								foreach($rsAbonos as $rwAbonos){
									$id_abono=$rwAbonos["ID_ABONO"];
									$fc_abono=$rwAbonos["FECHA"];
									$vl_abono=$rwAbonos["VALOR"];
									$ob_abono=$rwAbonos["OBSERVACION"];
									$us_abono=$rwAbonos["USUARIO"];
									$tot_abonos+=$vl_abono;
								}
							}
							if($tot_abonos>0){
								$total_gral=$acumprice-$tot_desc-$tot_abonos;
							}
							
						}
						
						
						
					}
				}
			}

			$descuento=0;
			$descuento_cpt=$tot_desc;
			$total=$acumprice;
			$total_base=$acumbase;

		}else{
			$descuento=$_POST["discont_$id_pedido"];
			$descuento_cpt=$_POST["discuntcpt_$id_pedido"];
			$total=$_POST["tot_ped_cax_$id_pedido"];
			$total_base=$_POST["tot_ped_bax_$id_pedido"];
			$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.NOMBRE, M.TIPO, P.ID_CLIENTE, P.ID_PEDIDO, P.DIRECCION, P.CHEF, P.CAJA, U.NOMBRES AS TENDER FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CHEF<>'0000-00-00 00:00:00' AND P.CIERRE='0000-00-00 00:00:00' AND P.PAGO='0') JOIN usuarios U ON U.ID_USUARIO=P.ID_TENDER WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='{$_SESSION["restservice"]}' AND P.ID_PEDIDO='$id_pedido'  GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
			
			if(count($resultInt)>0){
				foreach($resultInt as $rowInt){
					$acumbase=0;
					$acumprice=0;
					$acumimp=0;
					$id_mesa=$rowInt["ID_MESA"];
					$id_cliente=$rowInt["ID_CLIENTE"];
					$nombre=$rowInt["NOMBRE"];
					$m_tipo=$rowInt["TIPO"];
					$tipo=$rowInt["TIPO"];
					$perc=100;
					$dispatch=1;
					$direccion=$rowInt["DIRECCION"];
					$id_pedido=$rowInt["ID_PEDIDO"];
					$caja=$rowInt["CAJA"];
					$chef=$rowInt["CHEF"];
					$tender=$rowInt["TENDER"];
				}
			}
		}
		
		//--------------SF  validaFps()

		$formas=$gf->dataSet("SELECT ID_FP, NOMBRE, ICONO, CAJA, CREDITO FROM formas_pago WHERE ID_SITIO=:sitio ORDER BY POSICION, NOMBRE",array(":sitio"=>$_SESSION["restbus"]));
		$formas_select="<div class='form-group'>
		<label for='formapago'>FORMA DE PAGO</label><select onchange=\"validaFp('$id_pedido');validaFps()\" name='formapago' id='formapago' class='form-control unival_dix_$id_pedido pull-right' style='font-size:18px;width:150px;'>";
		if(count($formas)>0){
			foreach($formas as $fpa){
				$idfp=$fpa["ID_FP"];
				$nombrefp=$fpa["NOMBRE"];
				$cajax=$fpa["CAJA"];
				$credx=$fpa["CREDITO"];
				$formas_select.="<option value='$idfp' c='$cajax' cr='$credx'>$nombrefp</option>";
			}
		}
		$formas_select.="</select>
		<script>
		$(function(){
			validaFp('$id_pedido');
		})
		</script>
		</div>";
		

		////-------------------------------------------------SF


		$subformas=$gf->dataSet("SELECT ID_SUB, ID_FORMA, NOMBRE, ML FROM formas_pago_sub WHERE ID_FORMA IN(SELECT ID_FP FROM formas_pago WHERE ID_SITIO=:sitio) ORDER BY ID_FORMA, NOMBRE",array(":sitio"=>$_SESSION["restbus"]));
		$subformas_array=array();
		$subformas_select="<div class='form-group' id='subformaxcontrol' style='display:none;'>
		<label for='subformapago'>SUB-FORMA</label><select name='subformapago' id='subformapago' class='form-control unival_dix_$id_pedido pull-right' style='font-size:18px;width:150px;'><option value='0' idf='0'>N/A</option>";
		if(count($subformas)>0){
			foreach($subformas as $spa){
				$idsf=$spa["ID_SUB"];
				$id_forma=$spa["ID_FORMA"];
				$nombresfp=$spa["NOMBRE"];
				$ml=$spa["ML"];
				$subformas_array[$id_forma][$idsf]=array("nm"=>$nombresfp,"ml"=>$ml);
				$subformas_select.="<option value='$idsf' idf='$id_forma' ml='$ml'>$nombresfp</option>";
			}
		}
		$subformas_select.="</select></div>
		<script>
			function validaFps(){
				var formapx=$('#formapago').val();
				if($('#subformapago > option[idf='+formapx+']').length > 0){
					$('#subformapago > option').hide()
					$('#subformapago > option[idf='+formapx+']').show();
					$('#subformaxcontrol').show();
				}else{
					$('#subformapago').val(0);
					$('#subformaxcontrol').hide();
				}
			}
			
			
			
		</script>
		<script src='addon.js'></script>
		";
		$formas_select.="<br />".$subformas_select;
		////-------------------------------------------------SF



		if($descuento=="") $descuento=0;
		$cobrado=$total-$descuento-$descuento_cpt;

		if($_SESSION["restpropina"]>0){
			$propina=ceil($cobrado*($_SESSION["restpropina"]/100));
			$propins="
			<li class='list-group-item list-group-item-warning'>
			<label for='propins'>
			<input checked='checked' style='width:16px;height:16px;' type='checkbox' name='propins' class='unival_dix_$id_pedido' id='propins' onclick=\"calcTotPropina()\" /> INCLUIR PROPINA:</label>
			<span class='pull-right'>

			<input type='number' onchange=\"calcTotPropina()\" id='propin_in' name='propin_in' value='".$propina."' class='form-control unival_dix_$id_pedido' style='text-align:right;font-size:18px;width:150px;' />
			</span>
				
			</li>";
		}else{
			$propina=0;
			$propins="";
		}


		$rsAbonos=$gf->dataSet("SELECT A.ID_ABONO, A.FECHA, A.VALOR, A.OBSERVACION, U.NOMBRES AS USUARIO FROM pedidos_abonos A JOIN usuarios U ON U.ID_USUARIO=A.ID_USUARIO WHERE A.ID_PEDIDO='$id_pedido'");
		$tot_abonos=0;
		$abos="";
		if(count($rsAbonos)>0){
			$tot_abonos=0;
			foreach($rsAbonos as $rwAbonos){
				$id_abono=$rwAbonos["ID_ABONO"];
				$fc_abono=$rwAbonos["FECHA"];
				$vl_abono=$rwAbonos["VALOR"];
				$ob_abono=$rwAbonos["OBSERVACION"];
				$us_abono=$rwAbonos["USUARIO"];
				$tot_abonos+=$vl_abono;
			}
		}


		if($id_cliente>0){
			$infoCliente=$gf->dataSet("SELECT TIPO_ID, IDENTIFICACION, NOMBRE, DIRECCION, TELEFONO FROM clientes WHERE ID_CLIENTE='$id_cliente'");
			if(count($infoCliente)>0){
				$cliente_id=$infoCliente[0]["IDENTIFICACION"];
				$cliente_nombre=$infoCliente[0]["NOMBRE"];
				$cliente_direccion=$infoCliente[0]["DIRECCION"];
				$cliente_telefono=$infoCliente[0]["TELEFONO"];
			}else{
				$cliente_id="";
				$cliente_nombre="";
				$cliente_direccion="";
				$cliente_telefono="";
			}
		}else{
			$cliente_id="";
			$cliente_nombre="";
			$cliente_direccion="";
			$cliente_telefono="";
		}

		$reqva=$gf->dataSet("SELECT S.REQ_VAUCHER, S.BTN_COBRAR FROM sitios S WHERE S.ID_SITIO='{$_SESSION["restbus"]}'");
		$btn_cobrar=$reqva[0]["BTN_COBRAR"];
		$req_vaucher=($reqva[0]["REQ_VAUCHER"]==1) ? "required" : "";
		$req_vaucher_ast=($reqva[0]["REQ_VAUCHER"]==1) ? "*" : "";
		$requi_vaucher=($reqva[0]["REQ_VAUCHER"]==1) ? "<input type='hidden' id='requivaucher' />" : "";
		if($id_cliente==0){
		$cmdin = $gf->dataSet("SELECT CL.ID_CLIENTE, CL.IDENTIFICACION FROM sitios S JOIN clientes CL ON CL.ID_CLIENTE=S.CL_COMODIN WHERE S.ID_SITIO='{$_SESSION["restbus"]}'");
			if(count($cmdin)>0){
				$id_comodo=$cmdin[0]["ID_CLIENTE"];
				$iden_comodo=$cmdin[0]["IDENTIFICACION"];
			}else{
				$id_comodo=0;
				$iden_comodo="";
			}
		}else{
			$cmdin = $gf->dataSet("SELECT IDENTIFICACION FROM clientes WHERE ID_CLIENTE='$id_cliente'");
			$iden_comodo=$cmdin[0]["IDENTIFICACION"];
			$id_comodo=$id_cliente;

		}
		if($cliente_id==""){
			$cliente_id=$iden_comodo;
			$mascall="queryClient();";
		}else{
			$mascall="";
		}
		$stotal=$cobrado+$propina;
		echo $gf->utf8("
		
		$requi_vaucher
		<input type='hidden' id='id_comodin' value='$id_comodo' />
		<input type='hidden' id='ident_comodin' value='$iden_comodo' />
			<div class='callout callout-default'>
                
				<div class='row'>
				<div class='col-md-4 bg-warning'>
				<h4>INFORMACI&Oacute;N DEL CLIENTE</h4>
				DOCUMENTO: <input onchange=\"queryClient()\" type='text' name='num_doc' id='num_doc' class='form-control unival_dix_$id_pedido' value='$cliente_id' placeholder='El documento es requerido' />
				<br />
				<input type='hidden' id='id_cliente' name='id_cliente' value='$id_cliente' class='unival_dix_$id_pedido' />
				TIPO DOCUMENTO: <select name='tipo_doc' id='tipo_doc' class='form-control  unival_dix_$id_pedido'><option value='CC' selected='selected'>C&eacute;dula</option><option value='NI'>Nit</option></select>
				<br />
				NOMBRE: <input type='text' value='$cliente_nombre' name='nombre_cliente' id='nombre_cliente' class='form-control unival_dix_$id_pedido' />
				<br />
				DIRECCION: <input type='text' value='$cliente_direccion' name='dir_cliente' id='dir_cliente' class='form-control unival_dix_$id_pedido' />
				<br />

				CIUDAD: <div class='row'><div class='col-md-12'>
				<select name='city_cliente' id='city_cliente' class='form-control unival_dix_$id_pedido chosen'  style='width: 100%'>
				$selectcities
				</select>
				</div></div>
				<br />
				TELEFONO: <input type='text' value='$cliente_telefono' name='tel_cliente' id='tel_cliente' class='form-control unival_dix_$id_pedido' />
				CORREO: <input type='text' name='mail_cliente' id='mail_cliente' class='form-control unival_dix_$id_pedido' />

				<br />
				<span id='punta' class='hidden'>PUNTOS: </span>
				<br />
				<label class='hidden' id='reeedime' for='redime'>
					<input type='checkbox' name='redime' id='redime' class='unival_dix_$id_pedido' /> Redimir
				</label>
				<input type='hidden' name='puntos' id='puntos' class='unival_dix_$id_pedido' />
				<input type='hidden' name='billegas' id='billegas' class='unival_dix_$id_pedido' />
	


				</div>
				<div class='col-md-8'>
				<h4>PEDIDO</h4>
				<input type='hidden' id='labase' value='$cobrado' />
				
				<ul class='list-group'>
				<li class='list-group-item'>TOTAL PEDIDO: <big class='pull-right'><b>".number_format($total-$descuento_cpt)."</b></big></li>
				
				<li class='list-group-item clearfix'>DESCUENTO: <input type='number' id='discunt_cuentas' name='discont_$id_pedido' value='$descuento' onchange='calcTotPropina()' class='form-control unival_dix_$id_pedido pull-right'  style='text-align:right;font-size:18px;width:150px;' /></li>

			
				

				$propins
				<li class='list-group-item list-group-item-warning clearfix'>$formas_select</li>
				<li class='list-group-item'>TOTAL INGRESO: <big style='font-weight:bold;' class='pull-right'><span id='calctotopro'>".number_format($cobrado+$propina)."</span></big>
				<input type='hidden' value='".$stotal."' id='totall_$id_pedido' /> 
				<input type='hidden' class='unival_dix_$id_pedido' value='".$total_base."' name='totalbaseini_$id_pedido' id='totalbaseini_$id_pedido' /> 
				<input type='hidden' class='unival_dix_$id_pedido' value='".$descuento_cpt."' name='discont_cpt_$id_pedido' id='discont_cpt_$id_pedido' /> 
				
			
					</li>

					");

					if($tot_abonos>0){
						$adeudado=$cobrado+$propina-$tot_abonos;
						$adeuda=$cobrado-$tot_abonos;
						echo $gf->utf8("<li class='list-group-item'>ANTICIPOS (informativo): <big class='pull-right'>".number_format($tot_abonos)."</big>
						<input type='hidden'  value='".$adeuda."' id='addeu_$id_pedido' /> 
						<input type='hidden'  value='".$adeudado."' id='caltots_abonos_hidden' /> 
						</li>");
						echo $gf->utf8("<li class='list-group-item'>DEBE: <big class='pull-right red'>".number_format($total-$descuento_cpt-$tot_abonos)."</big></li>
						
						<li class='list-group-item'>TOTAL CON PROPINA: <big style='font-weight:bold;' class='pull-right'><span id='caltots_abonos'>".number_format($adeudado)."</span></big></li>
						
						");
					}
	
	
					echo $gf->utf8("


				
				<li class='list-group-item list-group-item-warning licredis hidden clearfix'>VAUCHER(REF) :$req_vaucher_ast <input type='text' id='referencia_$id_pedido' name='referencia_$id_pedido' class='form-control unival_dix_$id_pedido pull-right' $req_vaucher style='width:150px;text-align:right;' /></li>

				<li class='list-group-item list-group-item-warning licajas clearfix'>EFECTIVO: <input type='number' id='efecty_$id_pedido' name='efecty_$id_pedido' onkeyup=\"calcCambio()\" class='form-control unival_dix_$id_pedido pull-right' style='width:150px;text-align:right;' /></li>

				<li class='list-group-item licajas'>CAMBIO: <big style='font-weight:bold;' class='pull-right'><span id='calccambio'>0</span></big></li>
				


				</ul>
				
				<input type='hidden' id='tot_ped_cax_$id_pedido' name='tot_ped_cax_$id_pedido' class='unival_dix_$id_pedido' value='$total' />
				<br />
				
				<hr />
				<button class='btn btn-danger' onclick=\"getDialog('$sender?flag=fact_pedido2&id_ped=$id_pedido&rnd2=$rnd','500','Confirmar','','','','unival_dix_$id_pedido')\">Facturar</button>
				");
				if($btn_cobrar==1){
					echo $gf->utf8("
					<button  class='btn btn-primary' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=cobra_pedido_go&id_ped=$id_pedido&rnd=$rnd','','5000','unival_dix_$id_pedido')\"><i class='fa fa-dollar'></i> Cobrar</button>");
				}
				echo $gf->utf8("
			</div>
			</div>
			
			<script>
			function calcTotPropina(){
				var peditot=parseInt($('#labase').val());
				var descuento=parseInt($('#discunt_cuentas').val());
				if(descuento=='' || descuento==undefined){descuento=0}
				if(descuento>peditot){
					alert('No es posible realizar un descuento que supere el valor de la cuenta');
					$('#discunt_cuentas').val(peditot);
					descuento=peditot;
				}
				peditot=peditot-descuento;
				
				var proptot=$('#propin_in').val();
				var prrr=0;
				if($('#propins:checked').length>0){
					var totalprz=parseInt(parseInt(peditot)+parseInt(proptot));
					$('#calctotopro').text(number_format(totalprz,0,'.',','));
					$('#totall_$id_pedido').val(totalprz);
					prrr=parseInt(proptot);
				}else{
					$('#calctotopro').text(number_format(peditot,0,'.',','));
					$('#totall_$id_pedido').val(peditot);
					prrr=0;
				}
				var addeu=parseInt($('#addeu_$id_pedido').val());
				var xxe=parseInt(addeu+prrr-descuento);
				$('#caltots_abonos').text(number_format(xxe,0,'.',','));
				$('#caltots_abonos_hidden').val(xxe);
				calcCambio();
				
			}


			function calcCambio(){
				if($('#caltots_abonos').length==0){
					var total=$('#totall_$id_pedido').val();
				}else{
					var total=$('#caltots_abonos_hidden').val();
				}
				
				var efectivo=$('#efecty_$id_pedido').val();
				if(efectivo==''){
					efectivo=total;
				}
				
				var cambio=parseInt(efectivo-total);
				$('#calccambio').text(number_format(cambio,0,'.',','));
			}

				$(function(){ 
					$mascall	
				});
				</script>
			
			");
		
	}elseif($actividad=="fact_pedido2"){
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$rnd2=$gf->cleanVar($_GET["rnd2"]);
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rsM=$gf->dataSet("SELECT ID_MESA FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
		$id_mesa=$rsM[0]["ID_MESA"];

		$forma=$_POST["formapago"];

		$rnd=$gf->cleanVar($_GET["rnd"]);
		$descuento=$_POST["discont_$id_pedido"];
		$efectivo=$_POST["efecty_$id_pedido"];
		$referencia=$_POST["referencia_$id_pedido"];
		$total=$_POST["tot_ped_cax_$id_pedido"];
		$total_base=$_POST["totalbaseini_$id_pedido"];
		$descuento_cpt=$_POST["discont_cpt_$id_pedido"];

		$REDIMIR=$_POST["redime"];
		$PUNTOS=$_POST["puntos"];
		$BILLEGAS=$_POST["billegas"];

		$impuesto=$total-$total_base;


		$total_descuento=$descuento+$descuento_cpt;
		if($REDIMIR==1){
			$total_descuento+=$BILLEGAS;
		}
		if($total_descuento>0){
			$total_new=$total-$total_descuento;
			$total_base=($total_base/$total)*$total_new;
			$impuesto=$total_new-$total_base;
			$total=$total_new;
		}
		

		$tot_abonos=0;
		if($_SESSION["restanticipos"]==1){
			$rsAbonos=$gf->dataSet("SELECT A.ID_ABONO, A.FECHA, A.VALOR, A.OBSERVACION, U.NOMBRES AS USUARIO FROM pedidos_abonos A JOIN usuarios U ON U.ID_USUARIO=A.ID_USUARIO WHERE A.ID_PEDIDO='$id_pedido'");
			$abos="";
			if(count($rsAbonos)>0){
				foreach($rsAbonos as $rwAbonos){
					$id_abono=$rwAbonos["ID_ABONO"];
					$fc_abono=$rwAbonos["FECHA"];
					$vl_abono=$rwAbonos["VALOR"];
					$ob_abono=$rwAbonos["OBSERVACION"];
					$us_abono=$rwAbonos["USUARIO"];
					$tot_abonos+=$vl_abono;
				}
			}
		}


		
		
		$id_cliente=$_POST["id_cliente"];
		
		$num_doc=trim($_POST["num_doc"]);
		$tipo_doc=$_POST["tipo_doc"];
		$nombre_cliente=$_POST["nombre_cliente"];
		$dir_cliente=$_POST["dir_cliente"];
		$tel_cliente=$_POST["tel_cliente"];
		$mail_cliente=$_POST["mail_cliente"];
		$city_cliente=$_POST["city_cliente"];
		
		

		$propins=isset($_POST["propins"]) ? $_POST["propins"] : 0;
		
		if($propins==1 && $_SESSION["restpropina"]>0){
			$propina=$_POST["propin_in"];
			$propinsh="<tr><td>SERVICIO (PROPINA)</td><td>$ ".number_format($propina,0)."</td></tr>";
		}else{
			$propina=0;
			$propinsh="";
		}

		if($descuento=="") $descuento=0;
		
		//$cobrado=$total-$descuento;
		if($efectivo<($total+$propina-$tot_abonos)) $efectivo=$total+$propina-$tot_abonos;
		$cambio=$efectivo-($total+$propina-$tot_abonos);
		
		
		$infoEmpresa=$gf->dataSet("SELECT NIT, NOMBRE, CIUDAD, DIRECCION, TELEFONO, REGIMEN, RESOLUCION_FACTURAS, PREFIJO, INIFACT FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		if(count($infoEmpresa)>0){
			$rwEmpresa=$infoEmpresa[0];
			$empresa_inifact=$rwEmpresa["INIFACT"];
			$empresa_prefijo=$rwEmpresa["PREFIJO"];
			
			$consec=$gf->dataSet("SELECT CONSECUTIVO FROM facturas WHERE ID_PEDIDO IN(SELECT P.ID_PEDIDO FROM pedidos P JOIN servicio S ON S.ID_SERVICIO=P.ID_SERVICIO WHERE S.ID_SITIO=:sitio ORDER BY P.ID_PEDIDO) ORDER BY CONSECUTIVO DESC LIMIT 1",array(":sitio"=>$_SESSION["restbus"]));
			//print_r($consec);
			if(count($consec)>0){
				$consecutivo=$consec[0]["CONSECUTIVO"];
				$consecutivo+=1;
			}else{
				$consecutivo=$empresa_inifact;
			}

			if($nombre_cliente=="") $nombre_cliente="No definido";
			echo $gf->utf8("
			<div class='box box-warning'>
				<div class='box-body'>
				<h4>
				<table class='table table-bordered'>
				<tr><td>CLIENTE</td><td>$nombre_cliente $num_doc</td></tr>
				<tr><td>TOTAL PEDIDO</td><td> <b>".number_format($total+$total_descuento)."</b></td></tr>
				<tr><td>BASE</td><td><b>".number_format($total_base)."</b></td></tr>
				<tr><td>IMPUESTO</td><td> <b>".number_format($impuesto)."</b></td></tr>
				<tr><td>DESCUENTO</td><td> <b>".number_format($total_descuento)."</b></td></tr>
				$propinsh
				<tr><td>TOTAL CUENTA</td><td> <b style='color:#008B00;'>".number_format($total+$propina)."</b></td></tr>

				");
				if($tot_abonos>0){
					echo $gf->utf8("<tr><td>ANTICIPOS</td><td> <b style='color:#008B00;'>".number_format($tot_abonos)."</b></td></tr>");
					echo $gf->utf8("<tr><td>DEUDA</td><td> <b style='color:#008B00;'>".number_format($total+$propina-$tot_abonos)."</b></td></tr>");

				}
				echo $gf->utf8("
				<tr><td>EFECTIVO</td><td> <b>".number_format($efectivo)."</b></td></tr>
				<tr><td>CAMBIO</td><td> <b style='color:#DE0000;'>".number_format($cambio)."</b></td></tr>");
				if($referencia!=""){
				echo $gf->utf8("
				<tr><td>REF</td><td> <b style='color:#DE0000;'>$referencia</b></td></tr>
				");
				}
				echo $gf->utf8("
				</table>
				</h4>
				Salida de factura
				<div class='flexbox' style='width:100%;'>
				<label for='medio_factura1' style='margin:5px;' class='flexbox-centro'><input checked='checked' type='radio' name='medio_factura' id='medio_factura1' value='1' style='width:22px;height:22px;' class='unival_dix_$id_pedido'>Imprimir</label>
				<label for='medio_factura2'  style='margin:5px;' class='flexbox-centro'><input type='radio' style='width:22px;height:22px;' class='unival_dix_$id_pedido' name='medio_factura' id='medio_factura2' value='2'>PDF</label>
				<label for='medio_factura3'  style='margin:5px;' class='flexbox-centro'><input type='radio'  style='width:22px;height:22px;' class='unival_dix_$id_pedido' name='medio_factura' id='medio_factura3' value='0'>Ninguna</label>
				</div>
				<br />
				<button class='btn btn-danger' onclick=\"cargaHTMLvars('ModalContent_$rnd2','$sender?flag=fact_pedido3&id_ped=$id_pedido&rnd=$rnd2&redimir=$REDIMIR&billegas=$BILLEGAS&puntos=$PUNTOS','','10000','unival_dix_$id_pedido');closeD('$rnd')\">Facturar</button>

				<button class='btn btn-warning pull-right' onclick=\"closeD('$rnd')\">Cancelar</button>
				</div>
			</div>
			");
			
		}
		if($_SESSION["restfastmode"]==1){
			echo $gf->utf8("<input type='hidden' id='callbackevaldlg' value=\"cargaHTMLvars('ModalContent_$rnd2','$sender?flag=fact_pedido3&id_ped=$id_pedido&rnd=$rnd2&redimir=$REDIMIR&billegas=$BILLEGAS&puntos=$PUNTOS','','10000','unival_dix_$id_pedido');closeD('$rnd')\" />");
		}
		

	}elseif($actividad=="fact_pedido3"){
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rsM=$gf->dataSet("SELECT ID_MESA, ID_CLIENTE FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
		$id_mesa=$rsM[0]["ID_MESA"];
		$id_cliente=$rsM[0]["ID_CLIENTE"];
		$isfact=$gf->dataSet("SELECT ID_FACTURA, CONSECUTIVO FROM facturas WHERE ID_PEDIDO='$id_pedido'");

		$forma=$_POST["formapago"];
		$subforma=$_POST["subformapago"];  //-----------------------------SF




		$medio=$_POST["medio_factura"];


		if(count($isfact)==0){
			$rnd=$gf->cleanVar($_GET["rnd"]);
			$descuento=$_POST["discont_$id_pedido"];
			$efectivo=$_POST["efecty_$id_pedido"];
			$referencia=$_POST["referencia_$id_pedido"];
			$total=$_POST["tot_ped_cax_$id_pedido"];
			$total_base=$_POST["totalbaseini_$id_pedido"];
			$descuento_cpt=$_POST["discont_cpt_$id_pedido"];

			$impuesto=$total-$total_base;

			$REDIMIR=isset($_GET["redimir"]) ? $_GET["redimir"] : 0;
			$PUNTOS=isset($_GET["puntos"]) ? $_GET["puntos"] : 0;
			$BILLEGAS=isset($_GET["billegas"]) ? $_GET["billegas"] : 0;
			$total_descuento=$descuento+$descuento_cpt;
			if($REDIMIR==1){
				$total_descuento+=$BILLEGAS;
			}
			if($total_descuento>0){
				$total_new=$total-$total_descuento;
				$total_base=($total_base/$total)*$total_new;
				$impuesto=$total_new-$total_base;
				$total=$total_new;
			}
			
			

			$tot_abonos=0;
			if($_SESSION["restanticipos"]==1){
				$rsAbonos=$gf->dataSet("SELECT A.ID_ABONO, A.FECHA, A.VALOR, A.OBSERVACION, U.NOMBRES AS USUARIO FROM pedidos_abonos A JOIN usuarios U ON U.ID_USUARIO=A.ID_USUARIO WHERE A.ID_PEDIDO='$id_pedido'");
				$abos="";
				if(count($rsAbonos)>0){
					foreach($rsAbonos as $rwAbonos){
						$id_abono=$rwAbonos["ID_ABONO"];
						$fc_abono=$rwAbonos["FECHA"];
						$vl_abono=$rwAbonos["VALOR"];
						$ob_abono=$rwAbonos["OBSERVACION"];
						$us_abono=$rwAbonos["USUARIO"];
						$tot_abonos+=$vl_abono;
					}
				}
			}


			if($efectivo=="") $efectivo=$total;
			$cambio=$efectivo-$total;
			if($efectivo<($total-$tot_abonos)) $efectivo=$total;
			$num_doc=trim($_POST["num_doc"]);
			$tipo_doc=$_POST["tipo_doc"];
			$nombre_cliente=$_POST["nombre_cliente"];
			$dir_cliente=$_POST["dir_cliente"];
			$tel_cliente=$_POST["tel_cliente"];
			$mail_cliente=$_POST["mail_cliente"];
			$city_cliente=$_POST["city_cliente"];
			

			$propins=isset($_POST["propins"]) ? $_POST["propins"] : 0;
			
			if($propins==1 && $_SESSION["restpropina"]>0){
				$propina=$_POST["propin_in"];
				$propinsh="SERVICIO (PROPINA): $ ".number_format($propina,0)."<br />";
			}else{
				$propina=0;
				$propinsh="";
			}

			
			

			//if($descuento=="") $descuento=0;
			if($id_cliente==0){
				$cobrado=$total-$total_descuento;
				$cliente=$gf->dataSet("SELECT ID_CLIENTE, PUNTOS FROM clientes WHERE IDENTIFICACION=:ident AND ID_SITIO=:sitio",array(":ident"=>$num_doc,":sitio"=>$_SESSION["restbus"]));
				if(count($cliente)>0){
					$ID_CLIENTE=$cliente[0]["ID_CLIENTE"];
					$PUNTOS_ACUMULADOS=$cliente[0]["PUNTOS"];
				}else{
					$ID_CLIENTE=$gf->dataInLast("INSERT INTO clientes (TIPO_ID,IDENTIFICACION,NOMBRE,DIRECCION,CIUDAD,TELEFONO,CORREO,ID_SITIO) VALUES ('$tipo_doc','$num_doc','$nombre_cliente','$dir_cliente','$city_cliente','$tel_cliente','$mail_cliente','{$_SESSION["restbus"]}')");
					$PUNTOS_ACUMULADOS=0;
				}
			}else{
				$ID_CLIENTE=$id_cliente;
				if(trim($nombre_cliente!="") && trim($dir_cliente!="") && trim($tel_cliente!="") && trim($mail_cliente!="") && trim($city_cliente!="")){
					$gf->dataIn("UPDATE clientes SET NOMBRE='$nombre_cliente',DIRECCION='$dir_cliente', TELEFONO='$tel_cliente',CORREO='$mail_cliente',CIUDAD='$city_cliente' WHERE ID_CLIENTE='$ID_CLIENTE'");
				}
				
			}
			if($ID_CLIENTE>0){
				if(trim($nombre_cliente!="") && trim($dir_cliente!="") && trim($tel_cliente!="") && trim($mail_cliente!="") && trim($city_cliente!="")){
					$gf->dataIn("UPDATE clientes SET NOMBRE='$nombre_cliente',DIRECCION='$dir_cliente', TELEFONO='$tel_cliente',CORREO='$mail_cliente',CIUDAD='$city_cliente' WHERE ID_CLIENTE='$ID_CLIENTE'");
				}
				$gf->log($_SESSION["restbus"],0,$id_pedido,"PEDIDO FACTURADO",$_SESSION["restuiduser"]);
				$infoEmpresa=$gf->dataSet("SELECT NIT, NOMBRE, CIUDAD, DIRECCION, TELEFONO, REGIMEN, RESOLUCION_FACTURAS, PREFIJO, INIFACT FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
				if(count($infoEmpresa)>0){
					$rwEmpresa=$infoEmpresa[0];
					$empresa_inifact=$rwEmpresa["INIFACT"];
					$empresa_prefijo=$rwEmpresa["PREFIJO"];
					
					$consec=$gf->dataSet("SELECT CONSECUTIVO FROM facturas WHERE ID_PEDIDO IN(SELECT P.ID_PEDIDO FROM pedidos P JOIN servicio S ON S.ID_SERVICIO=P.ID_SERVICIO WHERE S.ID_SITIO=:sitio ORDER BY P.ID_PEDIDO) ORDER BY CONSECUTIVO DESC LIMIT 1",array(":sitio"=>$_SESSION["restbus"]));
					//print_r($consec);
					if(count($consec)>0){
						$consecutivo=$consec[0]["CONSECUTIVO"];
						$consecutivo+=1;
					}else{
						$consecutivo=$empresa_inifact;
					}

					if($medio==1){
						$callback="cargaHTMLvars('ModalContent_$rnd','$sender?flag=fact_pedido4&id_ped=$id_pedido&rnd=$rnd','','5000','unival_dix_$id_pedido')";
					}elseif($medio==2){
						$callback="var redirectWindow = window.open('Admin/pdf_fact.php?id_ped=$id_pedido', '_blank');closeD('$rnd')";
					}else{
						$callback="closeD('$rnd')";
					}
					
				


					$id_fact=$gf->dataInLast("INSERT INTO facturas (PREFIJO,CONSECUTIVO,FECHA,ID_CLIENTE,ID_PEDIDO,PRINTED) VALUES ('$empresa_prefijo','$consecutivo',CURDATE(),'$ID_CLIENTE','$id_pedido','1')");
					if($id_fact>0){
						//---------------SF  ID_FPS='$subforma'
						$ok=$gf->dataIn("UPDATE pedidos SET PAGO='$total', IMPUESTO='$impuesto', DCTO='$total_descuento', PROPINA='$propina', CIERRE=NOW(), ID_FP='$forma', ID_FPS='$subforma', DENOM='$efectivo', REFERENCIA='$referencia', ID_CAJERO='{$_SESSION["restuiduser"]}' WHERE ID_PEDIDO='$id_pedido'");
						
					

						if($ok){
							echo $gf->utf8("
							
							<div class='callout callout-warning'>
								<h4>FACTURA GENERADA No. $consecutivo</h4>
	
								<p>INFORMACI&Oacute;N:</p>
								TOTAL PEDIDO: <b>".number_format($total)."</b><br />
								BASE: <b>".number_format($total_base)."</b><br />
								IMPUESTO: <b>".number_format($impuesto)."</b><br />
								DESCUENTO: ".number_format($total_descuento)."<br />
								$propinsh
								TOTAL INGRESADO A CAJA: ".number_format($total+$propina)."<br />
								EFECTIVO: ".number_format($efectivo)."<br />
								CAMBIO: ".number_format($cambio)."<br />
								<hr />
								

								</div>
								<script>
								$(function(){
									sockEmitir('liberar',{id_mesa:$id_mesa});
									$callback;
								});
								</script>
							
							");
							
							/*
							<a class='btn btn-primary' href=\"$sender?flag=factura_pedido&id_ped=$id_pedido\" target='_blank'>Imprimir</a>
							*/
						}
					}else{
						echo "Error 988: No se pudo crear la factura";
					}
				}
			}else{
				echo "Error 987: No se pudo crear la factura";
			}
		}else{
			$id_factura=$isfact[0]["ID_FACTURA"];
			$consec=$isfact[0]["CONSECUTIVO"];

			$descuento=$_POST["discont_$id_pedido"];
			$referencia=$_POST["referencia_$id_pedido"];
			$total=$_POST["tot_ped_cax_$id_pedido"];
			$num_doc=trim($_POST["num_doc"]);
			$tipo_doc=$_POST["tipo_doc"];
			$nombre_cliente=$_POST["nombre_cliente"];
			$dir_cliente=$_POST["dir_cliente"];
			$tel_cliente=$_POST["tel_cliente"];
			$mail_cliente=$_POST["mail_cliente"];
			$city_cliente=$_POST["city_cliente"];
			if($descuento=="") $descuento=0;
			$cobrado=$total-$descuento;
			$callback="reloaHash()";
			echo $gf->utf8("
			<div class='callout callout-warning'>
			<h4>FACTURA GENERADA No. $consec</h4>

			<p>INFORMACI&Oacute;N:</p>
			TOTAL PEDIDO: ".number_format($total)."<br />
			DESCUENTO: ".number_format($descuento)."<br />
			TOTAL INGRESADO A CAJA: ".number_format($cobrado)."<br />
			<hr />
			<button class='btn btn-primary' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=fact_pedido4&id_ped=$id_pedido&rnd=$rnd','','5000','unival_dix_$id_pedido')\">Imprimir</button>

			<a class='btn btn-info pull-right' target='_blank' href=\"Admin/pdf_fact.php?id_ped=$id_pedido\">Pdf</a>

			<script>
			$(function(){
				sockEmitir('liberar',{id_mesa:$id_mesa});
				$callback;
			});
			</script>
			
			");
			$callback="";
		}
		
	}elseif($actividad=="fact_pedido2_post"){
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rsM=$gf->dataSet("SELECT ID_MESA, PAGO FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
		$id_mesa=$rsM[0]["ID_MESA"];
		$cobrado=$rsM[0]["PAGO"];
		$consec=$gf->cleanVar($_GET["consec"]);
		$isfact=$gf->dataSet("SELECT ID_FACTURA, CONSECUTIVO FROM facturas WHERE ID_PEDIDO='$id_pedido'");

		if(count($isfact)==0){
			$rnd=$gf->cleanVar($_GET["rnd"]);
			$num_doc=trim($_POST["num_doc"]);
			$tipo_doc=$_POST["tipo_doc"];
			$nombre_cliente=$_POST["nombre_cliente"];
			$dir_cliente=$_POST["dir_cliente"];
			$tel_cliente=$_POST["tel_cliente"];
			$mail_cliente=$_POST["mail_cliente"];
			$city_cliente=$_POST["city_cliente"];
	
			$cliente=$gf->dataSet("SELECT * FROM clientes WHERE IDENTIFICACION=:ident AND ID_SITIO=:sitio",array(":ident"=>$num_doc,":sitio"=>$_SESSION["restbus"]));
			if(count($cliente)>0){
				$ID_CLIENTE=$cliente[0]["ID_CLIENTE"];
			}else{
				$ID_CLIENTE=$gf->dataInLast("INSERT INTO clientes (TIPO_ID,IDENTIFICACION,NOMBRE,DIRECCION,CIUDAD,TELEFONO,CORREO,ID_SITIO) VALUES ('$tipo_doc','$num_doc','$nombre_cliente','$dir_cliente','$city_cliente','$tel_cliente','$mail_cliente','{$_SESSION["restbus"]}')");
			}
			if($ID_CLIENTE>0){
				$infoEmpresa=$gf->dataSet("SELECT NIT, NOMBRE, CIUDAD, DIRECCION, TELEFONO, REGIMEN, RESOLUCION_FACTURAS, PREFIJO, INIFACT FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
				if(count($infoEmpresa)>0){
					$rwEmpresa=$infoEmpresa[0];
					$empresa_inifact=$rwEmpresa["INIFACT"];
					$empresa_prefijo=$rwEmpresa["PREFIJO"];
					if($consec==0){
						$consec=$gf->dataSet("SELECT CONSECUTIVO FROM facturas WHERE ID_CLIENTE IN(SELECT ID_CLIENTE FROM clientes WHERE ID_SITIO=:sitio ORDER BY ID_CLIENTE) ORDER BY CONSECUTIVO DESC LIMIT 1",array(":sitio"=>$_SESSION["restbus"]));
						//print_r($consec);
						if(count($consec)>0){
							$consecutivo=$consec[0]["CONSECUTIVO"];
							$consecutivo+=1;
						}else{
							$consecutivo=$empresa_inifact;
						}
					}else{
						$consecutivo=$consec;
					}

					$rsPP=$gf->dataSet("SELECT PESO_PUNTO FROM sitios WHERE ID_SITIO='{$_SESSION["restbus"]}'");
					$peso_punto=$rsPP[0]["PESO_PUNTO"];
	
					if($peso_punto>0){
						$puntos=floor($cobrado/$peso_punto);
					}else{
						$puntos=0;
					}

					$id_fact=$gf->dataInLast("INSERT INTO facturas (PREFIJO,CONSECUTIVO,FECHA,ID_CLIENTE,ID_PEDIDO,PRINTED,PUNTOS) VALUES ('$empresa_prefijo','$consecutivo',CURDATE(),'$ID_CLIENTE','$id_pedido','1','$puntos')");
					if($id_fact>0){
				
						echo $gf->utf8("
						<div class='callout callout-warning'>
							<h4>FACTURA GENERADA No. $consecutivo</h4>

							<p>INFORMACI&Oacute;N:</p>
							TOTAL PEDIDO: <b>".number_format($cobrado)."</b><br />
							<hr />
							<button class='btn btn-primary' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=fact_pedido3&id_ped=$id_pedido&rnd=$rnd','','5000','unival_dix_$id_pedido')\">Imprimir</button>

							<a class='btn btn-info pull-right' target='_blank' href=\"Admin/pdf_fact.php?id_ped=$id_pedido\">Pdf</a>

							</div>
							
						");
						$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"COTIZACION FACTURADA",$_SESSION["restuiduser"]);
					}else{
						echo "Error 988: No se pudo crear la factura";
					}
				}
			}else{
				echo "Error 987: No se pudo crear la factura";
			}
		}else{
			$id_factura=$isfact[0]["ID_FACTURA"];
			$consec=$isfact[0]["CONSECUTIVO"];

			$num_doc=trim($_POST["num_doc"]);
			$tipo_doc=$_POST["tipo_doc"];
			$nombre_cliente=$_POST["nombre_cliente"];
			$dir_cliente=$_POST["dir_cliente"];
			$tel_cliente=$_POST["tel_cliente"];
			$mail_cliente=$_POST["mail_cliente"];
		

			echo $gf->utf8("
			<div class='callout callout-warning'>
			<h4>LA FACTURA YA EXISTE CON No. $consec</h4>

			<p>INFORMACI&Oacute;N:</p>
			TOTAL PEDIDO: ".number_format($cobrado)."<br />
			<hr />
			<button class='btn btn-primary' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=fact_pedido3&id_ped=$id_pedido&rnd=$rnd','','5000','unival_dix_$id_pedido')\">Imprimir</button>

			<a class='btn btn-info pull-right' target='_blank' href=\"Admin/pdf_fact.php?id_ped=$id_pedido\">Pdf</a>
			
			");
		}
	}elseif($actividad=="fact_pedido4"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$gf->dataIn("UPDATE facturas SET PRINTED='0' WHERE ID_PEDIDO='$id_pedido'");
		$rsM=$gf->dataSet("SELECT ID_MESA FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
		$id_mesa=$rsM[0]["ID_MESA"];

		$rsF=$gf->dataSet("SELECT ID_FACTURA FROM facturas WHERE ID_PEDIDO='$id_pedido'");
		$id_factura=$rsF[0]["ID_FACTURA"];
		$gf->log($_SESSION["restbus"],0,$id_pedido,"IMPRIMIR FACTURA",$_SESSION["restuiduser"]);
		echo $gf->utf8("
		Factura enviada a la impresora...
		<script>
			$(function(){
				setTimeout(function(){
					sockEmitir('factura',{id_factura:$id_factura});
					closeD('$rnd');
				},2000);
			});
		</script>");
	
	}elseif($actividad=="print_precuenta"){
		$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$curConf=$gf->dataSet("SELECT PRINTER_HOST, CLIENT_KEY, CLIENT_SEC FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		$prhs=$curConf[0]["PRINTER_HOST"];
		$client_k=$curConf[0]["CLIENT_KEY"];
		$client_s=$curConf[0]["CLIENT_SEC"];
		echo $gf->utf8("
		<script>
			  $(function () {
				var params={'credentials':{'client':'$client_k','token':'$client_s'},'pedido':'$id_pedido'};
				$.ajax({
					url : 'https://www.torresoft.co:3443/api/rest/get_ped/',
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
									data: {contents: data.result,rectype:'pref'},
									success: function(data, textStatus, jqXHR)
									{
										$('#alertaimpresora').hide();
										closeD('$rnd')
									},
									error : function(xhr, textStatus, errorThrown ) {
										$('#alertaimpresora').show();
										console.log('Error al imprimir precuenta');
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
		

	}elseif($actividad=="add_abono"){
		$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
		$dialogo=$gf->cleanVar($_GET["rnd"]);
		$fkf["formas_pago"]=array("ID_SITIO"=>$_SESSION["restbus"],"CREDITO"=>0);
		$gettabla = $dataTables->devuelveTablaNewItemDyRel("pedidos_abonos","ID_PEDIDO",$id_pedido,$dialogo,"cargaHTMLvars(\'ModalContent_$dialogo\',\'$sender?flag=print_abono&id_pedido=$id_pedido&rnd=$dialogo\')",$fkf);
		echo $gf->utf8($gettabla);
	}elseif($actividad=="print_abono"){
		$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
		$dialogo=$gf->cleanVar($_GET["rnd"]);
		echo $gf->utf8("
		<script>
		$(function(){
			sockEmitir('abono',{id_pedido:'$id_pedido'});
			closeD('$dialogo');
		});
		</script>
		");

	}elseif($actividad=="cobra_pedido"){
		
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$descuento=$_POST["discont_$id_pedido"];
		$total=$_POST["tot_ped_cax_$id_pedido"];
		if($descuento=="") $descuento=0;
		$cobrado=$total-$descuento;
		$formas=$gf->dataSet("SELECT ID_FP, NOMBRE, ICONO FROM formas_pago WHERE ID_SITIO=:sitio ORDER BY POSICION, NOMBRE",array(":sitio"=>$_SESSION["restbus"]));
		$formas_select="<div class='form-group'>
		<label for='formapago'>Forma de pago</label><select name='formapago' id='formapago' class='form-control unival_dix_$id_pedido'>";
		if(count($formas)>0){
			foreach($formas as $fpa){
				$idfp=$fpa["ID_FP"];
				$nombrefp=$fpa["NOMBRE"];
				$formas_select.="<option value='$idfp'>$nombrefp</option>";
			}
		}
		$formas_select.="</select></div>";

		if($_SESSION["restpropina"]>0){
			$propina=ceil($cobrado*($_SESSION["restpropina"]/100));
			$propins="<li class='list-group-item'><table class='table'><tr><td><input style='width:16px;height:16px;' checked='checked' type='checkbox' name='propins' class='unival_dix_$id_pedido' id='propins' onclick=\"calcTotPropina()\" />INCLUIR PROPINA</td><td>
			<div class='input-group pull-right'>
			<div class='input-group-addon'>$</div><input type='number' id='propin_in' onchange=\"calcTotPropina()\" name='propin_in' value='".$propina."' class='form-control input-sm unival_dix_$id_pedido' style='text-align:right;' /></div>
			</td></tr></table>
			</li>";
		}else{
			$propina=0;
			$propins="";
		}
		echo $gf->utf8("
			<div class='callout callout-default'>
			<input type='hidden' id='lapropina' value='$propina' />
			<input type='hidden' id='labase' value='$cobrado' />
                <h4>CONFIRMA LOS DATOS</h4>

								<p>INFORMACI&Oacute;N:</p>
				<ul class='list-group'>
				<li class='list-group-item'>TOTAL PEDIDO: <span class='pull-right'>".number_format($total)."</span></li>
				<li class='list-group-item'>DESCUENTO: <span class='pull-right'>".number_format($descuento)."</span></li>
				<li class='list-group-item'>$formas_select</li>
				
				$propins
				
				<li class='list-group-item'>TOTAL INGRESADO A CAJA: <big class='pull-right' style='font-weight:bold;'><span id='calctotopro'>".number_format($cobrado+$propina)."</span></big></li>
				
				</ul>
				
				<input type='hidden' id='discunt_$id_pedido' name='discont_$id_pedido' value='$descuento' class='unival_dix_$id_pedido' />
				<input type='hidden' id='tot_ped_cax_$id_pedido' name='tot_ped_cax_$id_pedido' class='unival_dix_$id_pedido' value='$total' />

				<hr />
				<button class='btn btn-danger' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=cobra_pedido_go&id_ped=$id_pedido&rnd=$rnd','','4000','unival_dix_$id_pedido')\">Cobrar</button>

							</div>
							<script>
						function calcTotPropina(){
							var peditot=$('#labase').val();
							var proptot=$('#propin_in').val();
							if($('#propins:checked').length>0){
								var totalprz=parseInt(parseInt(peditot)+parseInt(proptot));
								$('#calctotopro').text(number_format(totalprz,0,'.',','));
							}else{
								$('#calctotopro').text(number_format(peditot,0,'.',','));
							}
						}
							</script>
			
			");
	}elseif($actividad=="cobra_pedido_go"){
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		$rsM=$gf->dataSet("SELECT ID_MESA FROM pedidos WHERE ID_PEDIDO='$id_pedido'");
		$id_mesa=$rsM[0]["ID_MESA"];
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$descuento=$_POST["discont_$id_pedido"];
		$total=$_POST["tot_ped_cax_$id_pedido"];
		$efectivo=$_POST["efecty_$id_pedido"];
		
		$forma_pago=$_POST["formapago"];
		$propins=isset($_POST["propins"]) ? $_POST["propins"] : 0;
		$cobrado=$total-$descuento;
		if($efectivo=="") $efectivo = $cobrado + $propins;
		
		if($propins==1 && $_SESSION["restpropina"]>0){
			$propina=$_POST["propin_in"];
			$propinsh="SERVICIO (PROPINA): $ ".number_format($propina,0)."<br />";
		}else{
			$propina=0;
			$propinsh="";
		}
		$cambio=$efectivo-($cobrado+$propina);
		if($cambio<0){
			$cambio=0;
		}


		$rsPed=$gf->dataSet("SELECT I.ID_ITEM, I.ID_SILLA, I.ID_PLATO, I.CANTIDAD, I.FENTREGA, P.NOMBRE AS PLATO, P.PRECIO, (P.PRECIO/(1+(IM.PORCENTAJE/100))) AS BASE FROM sillas_platos I JOIN platos P ON P.ID_PLATO=I.ID_PLATO LEFT JOIN impuestos AS IM ON IM.ID_IMPUESTO=P.ID_IMPUESTO WHERE I.ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO=:pedido ORDER BY ID_SILLA) ORDER BY I.ID_ITEM",array(":pedido"=>$id_pedido));

		$totimp=0;
		if(count($rsPed)>0){
			foreach($rsPed as $rwPed){
				$id_item=$rwPed["ID_ITEM"];
				$plato=$rwPed["PLATO"];
				$plato_pz=$rwPed["PRECIO"];
				$base=$rwPed["BASE"];
				if($base=="") $base=$plato_pz;
				$cant=$rwPed["CANTIDAD"];
				$impuesto=($plato_pz-$base)*$cant;
				$totimp+=$impuesto;
			}
		}


		$ok=$gf->dataIn("UPDATE pedidos SET PAGO='$cobrado', DCTO='$descuento', IMPUESTO='$totimp', PROPINA='$propina', CIERRE=NOW(), ID_FP='$forma_pago', ID_CAJERO='{$_SESSION["restuiduser"]}' WHERE ID_PEDIDO='$id_pedido'");
		if($ok){
			echo $gf->utf8("
			<div class='callout callout-success'>
                <h4>COBRO REALIZADO</h4>

                <p>INFORMACI&Oacute;N:</p>
				TOTAL PEDIDO: ".number_format($total)."<br />
				DESCUENTO: ".number_format($descuento)."<br />
				$propinsh
				TOTAL INGRESADO A CAJA: ".number_format($cobrado+$propina)."<br />
				EFECTIVO: ".number_format($efectivo)."<br />
				CAMBIO: ".number_format($cambio)."<br />
				<hr />
				<button class='btn btn-default' onclick=\"closeD('$rnd')\">Regresar</button>
              </div>
			  <script>
			  $(function(){
				  sockEmitir('liberar',{id_mesa:$id_mesa});
			  });
			  </script>
			");
			$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"PEDIDO COBRADO: COBRO: $cobrado, PROPINA:$propina, DESCUENTO: $descuento",$_SESSION["restuiduser"]);
			
			//Add
			$curConf=$gf->dataSet("SELECT PRINTER_HOST, CLIENT_KEY, CLIENT_SEC FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
			$prhs=$curConf[0]["PRINTER_HOST"];
			echo $gf->utf8("
			<script>
			  $(function () {
				
				$.ajax({
					type: 'POST',
					url: '$prhs',
					data: {rectype:'opencash'},
					success: function(data, textStatus, jqXHR)
					{
						$('#alertaimpresora').hide();
					},
					error : function(xhr, textStatus, errorThrown ) {
						$('#alertaimpresora').show();
						console.log('Motor de impresion local fuera' + errorThrown);
					}
				});		
			  });
			</script>
			
			");
			//Add
		}
		
	}elseif($actividad=="start_ajustes"){
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
                    $bandera="ajuste_caja_ver";
                    $btns="";
					
					echo $gf->utf8("<li id='theserv_$id_service' title='$titl' onclick=\"cargaHTMLvars('level3','$sender?flag=$bandera&key=$id_service&st=$estado&ro=1')\" class='list-group-item list-group-item-$classe link-cnv'>$fecha <i class='$ico pull-right'></i> $btns</li>");
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
	
	}elseif($actividad=="ajuste_caja_ver"){
		$servicio=isset($_GET["key"]) ? $_GET["key"] : $_SESSION["restservice"];
		$ro=isset($_GET["ro"]) ? $_GET["ro"] : 0;
		
		$gtos=$gf->dataSet("SELECT P.ID_AJUSTE, P.FECHA, P.OBSERVACION, P.VALOR, FP.NOMBRE AS FORMA_PAGO, G.ID_GASTO, G.DESCRIPCION FROM ajustes_caja P LEFT JOIN gastos G ON P.ID_GASTO=G.ID_GASTO LEFT JOIN formas_pago FP ON FP.ID_FP=P.ID_FP WHERE P.ID_SERVICIO=:servicio GROUP BY P.ID_AJUSTE ORDER BY P.FECHA DESC",array(":servicio"=>$servicio));
		echo $gf->utf8("
		<div class='box box-warning'>
			<div class='box-header'>
				AJUSTES DE CAJA SERVICIO: $servicio
			</div>
			<div class='box-body'>
			<table class='table table-bordered'>
				<tr>
					<td>FECHA</td>
					<td>VALOR</td>
					<td>OBSERVACION</td>
					<td>FORMA DE PAGO</td>
					<td>APLICADO A GASTO</td>
					<td>...</td>
				</tr>
			
			");
        if(count($gtos)>0){
            foreach($gtos as $gto){
                $id_ajuste=$gto["ID_AJUSTE"];
                $forma=$gto["FORMA_PAGO"];
                $observacion=$gto["OBSERVACION"];
                $fecha=$gto["FECHA"];
                $valor=$gto["VALOR"];
                $gasto=$gto["DESCRIPCION"];
				
				echo $gf->utf8("<tr id='trajuste_$id_ajuste'>
				<td>$fecha</td>
				<td>$valor</td>
				<td>$observacion</td>
				<td>$forma</td>
				<td>$gasto</td>
				<td>");
				if($ro==0){
				echo $gf->utf8("
					<button class='btn btn-xs btn-danger' onclick=\"goErase('ajustes_caja','ID_AJUSTE','$id_ajuste','trajuste_$id_ajuste','1')\"><i class='fa fa-remove'></i></button>");
				}
					echo $gf->utf8("
				</td>
			</tr>");

			}
		}
	
		echo $gf->utf8("
			</table><hr />");
			if($ro==0){
				echo $gf->utf8("
				<button class='btn btn-primary btn-xs' onclick=\"getDialog('$sender?flag=ajuste_caja')\">Agregar ajuste</button>");
			}
			echo $gf->utf8("
			<button class='btn btn-warning btn-xs' onclick=\"loader('$sender?flag=estado_caja')\">Salir</button>
			</div></div>");

	}elseif($actividad=="ajuste_caja"){
		$rnd=$gf->cleanVar($_GET["rnd"]);
		echo $gf->utf8("
		Los ajustes de caja o ajustes de servicio, son ingresos que pueden generarse por reintegros de dinero de gastos previos u otros conceptos.
		<hr />Seleccione el gasto asociado al reintegro y el valor ingresado, adem&aacute;s seleccione el m&eacute;todo de pago:<br />
		");


		$gtos=$gf->dataSet("SELECT T.NOMBRE, G.ID_GASTO, G.DESCRIPCION, G.FECHA, G.VALOR, SUM(P.VALOR) AS ABONOS FROM gastos G JOIN gastos_tipos T ON T.ID_TIPO=G.ID_TIPO LEFT JOIN ajustes_caja P ON P.ID_GASTO=G.ID_GASTO WHERE G.ID_SITIO=:sitio GROUP BY G.ID_GASTO ORDER BY G.FECHA DESC",array(":sitio"=>$_SESSION["restbus"]));
		echo $gf->utf8("
		Gasto previo:
		<select class='form-control unival_regus_aju' name='id_gto' id='id_gto'>
			<option value='0'>Ninguno</option>
			");
        if(count($gtos)>0){
            foreach($gtos as $gto){
                $id_gto=$gto["ID_GASTO"];
                $tipo=$gto["NOMBRE"];
                $descripcion=$gto["DESCRIPCION"];
                $fecha=$gto["FECHA"];
                $valor=$gto["VALOR"];
				$abonos=$gto["ABONOS"];
				$saldo=$valor-$abonos;
				echo $gf->utf8("<option value='$id_gto'>$fecha: $tipo - $descripcion ($valor, saldo:$saldo)</option>");

			}
		}
		echo $gf->utf8("
		</select><br />
		Forma de pago:
		<select class='form-control unival_regus_aju' name='id_fp' id='id_fp' required>
			");
			$rsFp=$gf->dataSet("SELECT ID_FP, NOMBRE FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}' AND CREDITO=0 ORDER BY POSICION");
			if(count($rsFp)>0){
				foreach($rsFp as $rwFp){
					$id_fp=$rwFp["ID_FP"];
					$nm_fp=$rwFp["NOMBRE"];
					echo $gf->utf8("<option value='$id_fp'>$nm_fp</option>");
				}
			}
		echo $gf->utf8("
		</select><br />
		Valor Ajuste:
		<input type='number' class='form-control unival_regus_aju' id='valor_ajuste' name='valor_ajuste' required /><br />
		Observaciones:
		<textarea class='form-control unival_regus_aju' id='observaciones' name='observaciones'></textarea> 
		<hr />

		<button class='btn btn-sm btn-success pull-right' title='Ingresar ajuste' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=ajuste_caja_go&rnd=$rnd','','','unival_regus_aju')\"><i class='fa fa-plus'></i> Ingresar ajuste</button>
		");

		
	}elseif($actividad=="ajuste_caja_go"){
		$id_fp=$_POST["id_fp"];
		$id_gto=$_POST["id_gto"];
		$val=$_POST["valor_ajuste"];
		$observacion=$_POST["observaciones"];
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$id_servicio=$_SESSION["restservice"];
		$id_tender=$_SESSION["restuiduser"];
		$id_cajero=$_SESSION["restuiduser"];
		$id_ped=$gf->dataInLast("INSERT INTO ajustes_caja (ID_FP,VALOR,ID_USUARIO,ID_SERVICIO,OBSERVACION,FECHA,ID_GASTO) VALUES ('$id_fp','$val','$id_cajero','$id_servicio','$observacion',NOW(),'$id_gto')");
		if($id_ped>0){
			echo "Registro ingresado
			<input type='hidden' id='callbackeval' value=\"loader('$sender?flag=ajuste_caja_ver')\" />
			";
		}

	}elseif($actividad=="estado_caja"){


		$rsAbonos=$gf->dataSet("SELECT SUM(VALOR) AS ANTICIPOS FROM pedidos_abonos WHERE ID_PEDIDO IN(SELECT ID_PEDIDO FROM pedidos WHERE ID_SERVICIO='{$_SESSION["restservice"]}') AND ID_FP IN(SELECT ID_FP FROM formas_pago WHERE CAJA=1 ORDER BY ID_FP) ORDER BY ID_PEDIDO");

		if(count($rsAbonos)>0){
			$tot_anticipos=$rsAbonos[0]["ANTICIPOS"];
		}else{
			$tot_anticipos=0;
		}
		

		$resultInt = $gf->dataSet("SELECT SUM(P.PAGO) AS PAGO, S.BASE_CAJA, F.ESTADO FROM servicio S JOIN pedidos AS P ON P.ID_SERVICIO=S.ID_SERVICIO JOIN formas_pago FP ON FP.ID_FP=P.ID_FP LEFT JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO WHERE P.ID_SERVICIO='{$_SESSION["restservice"]}' AND FP.CREDITO=0 AND P.CIERRE<>'0000-00-00 00:00:00' AND (F.ESTADO=1 OR ISNULL(F.ESTADO))  GROUP BY S.ID_SERVICIO");
		
		$compras=$gf->dataSet("SELECT C.ID_COMPRA, C.FECHA, C.OBSERVACION, C.FACTURA, P.NOMBRE AS PROVEEDOR, SUM(I.PRECIO*I.CANTIDAD) AS VALOR FROM inventario_compras C JOIN proveedores P ON C.ID_PROVEEDOR=P.ID_PROVEEDOR JOIN inventario I ON I.ID_COMPRA=C.ID_COMPRA WHERE C.ID_SERVICIO=:servicio GROUP BY C.ID_COMPRA ORDER BY C.ID_COMPRA",array(":servicio"=>$_SESSION["restservice"]));
		$totCompra=0;
		$totGasto=0;
		if(count($compras)>0){
				foreach($compras as $compra){
						$fecha=$compra["FECHA"];
						$valor=$compra["VALOR"];
						$totCompra+=$valor;
				}
		}

		
		
		$gtos=$gf->dataSet("SELECT T.NOMBRE, G.ID_GASTO, G.DESCRIPCION, G.FECHA, G.VALOR FROM gastos G JOIN gastos_tipos T ON T.ID_TIPO=G.ID_TIPO WHERE G.ID_SERVICIO=:servicio",array(":servicio"=>$_SESSION["restservice"]));
		if(count($gtos)>0){
			foreach($gtos as $gto){
				$fecha=$gto["FECHA"];
				$valor=$gto["VALOR"];
				$totGasto+=$valor;
			}
		}
		


		$rsCabonos=$gf->dataSet("SELECT ID_FPP, SUM(VALOR) AS VALOR FROM cartera_ingresos WHERE ID_FPP IN(SELECT ID_FP FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}') AND ID_SERVICIO='{$_SESSION["restservice"]}' GROUP BY ID_FPP");
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

			 
		$rsAjustes=$gf->dataSet("SELECT ID_FP, SUM(VALOR) AS VALOR FROM ajustes_caja WHERE ID_FP IN(SELECT ID_FP FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}') AND ID_SERVICIO='{$_SESSION["restservice"]}' GROUP BY ID_FP");
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



		if(count($resultInt)>0){
			$rowInt=$resultInt[0];
			$pggo=$rowInt["PAGO"];
			$ingresos=$rowInt["PAGO"]+$tot_anticipos+$ingre_arcabos+$ajustes;
			$base=$rowInt["BASE_CAJA"];
			$total=$ingresos+$base-$totCompra-$totGasto;
			echo $gf->utf8("
			<span class='hidden' style='display:none'>$pggo+$tot_anticipos+$ingre_arcabos+$ajustes</span>
			<div class='col-md-4'>
				<div class='box box-widget widget-user-2'>
					<div class='widget-user-header bg-yellow'>
						<div class='widget-user-image'>
						<img class='img-circle' src='misc/casher.png' alt='User Avatar'>
						</div>
						<h3 class='widget-user-username'>ESTADO DE CAJA</h3>
						<h5 class='widget-user-desc'>Servicio</h5>
					</div>
					<div class='box-footer no-padding'>
						<ul class='nav nav-stacked'>
						<li><a href='#estado_caja'>Base <span class='pull-right badge bg-blue' style='color:black!important;background-color:white!important;'>".number_format($base,0)."</span></a></li>
						<li><a href='#estado_caja'>Ingreso <span class='pull-right badge bg-aqua' style='color:black!important;background-color:white!important;'>".number_format($ingresos,0)."</span></a></li>
						<li><a href='#estado_caja'>Compras caja <span class='pull-right badge bg-aqua' style='color:black!important;background-color:white!important;'>".number_format($totCompra,0)."</span></a></li>
						<li><a href='#estado_caja'>Gastos caja <span class='pull-right badge bg-aqua' style='color:black!important;background-color:white!important;'>".number_format($totGasto,0)."</span></a></li>
						<li><a href='#estado_caja'>Total <span class='pull-right badge bg-green' style='color:black!important;background-color:white!important;'>".number_format($total,0)."</span></a></li>
						</ul>
						<button class='btn btn-xs btn-warning pull-right' onclick=\"loader('$sender?flag=ajuste_caja_ver')\">Ajustes (ingreso)</button>
					</div>
				</div>
			</div>
			<hr />
			<div class='col-md-12'>
			");



			$id_servicio=$_SESSION["restservice"];
			$id_serv=$_SESSION["restservice"];

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
				<div class='box box-warning'><div class='box-header'>DETALLES</div>
				<div class='box-body'>
				
				");

					$resultInt = $gf->dataSet("SELECT F.ID_FACTURA, F.PREFIJO, F.CONSECUTIVO, M.ID_MESA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, P.APERTURA, P.CIERRE, P.PAGO, P.DCTO, FP.NOMBRE AS FORMA, FP.CAJA, FP.ID_FP, F.ESTADO FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA) JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER LEFT JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO LEFT JOIN formas_pago FP ON FP.ID_FP=P.ID_FP WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='$id_servicio' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
							
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

							$festado=$rowInt["ESTADO"];
							if($festado==1){
								$pago=$rowInt["PAGO"];
								$dcto=$rowInt["DCTO"];
								//$abonos=$rowInt["ABONOS"];
							}else{
								$pago=0;
								$dcto=0;
								//$abonos=0;
							}
							
				
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

					




					$gf->dataIn("INSERT IGNORE INTO servicio_cuadre (ID_SERVICIO,ID_FP,VALOR) SELECT '$id_servicio' AS ID_SERVICIO, ID_FP, '0' AS VALOR FROM formas_pago WHERE ID_SITIO='".$_SESSION["restbus"]."' ORDER BY ID_FP");

					$resultInt = $gf->dataSet("SELECT FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, SUM(P.PAGO) AS VALOR, CU.ID_REL, CU.VALOR AS VALCUADRE FROM formas_pago AS FP JOIN pedidos AS P ON (FP.ID_FP=P.ID_FP AND P.ID_SERVICIO='$id_servicio') JOIN servicio_cuadre CU ON CU.ID_FP=FP.ID_FP AND CU.ID_SERVICIO='$id_servicio' LEFT JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO WHERE FP.ID_SITIO='".$_SESSION["restbus"]."'  AND (F.ESTADO=1 OR ISNULL(F.ESTADO)) GROUP BY FP.ID_FP ORDER BY FP.NOMBRE");
					echo $gf->utf8("<table class='table table-bordered'><tr class='bg-danger'><td>FORMA DE PAGO</td><td>+BASE</td>
					<td>+VENTAS</td>
					<td>+REC. CARTERA</td>
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
								<td>".number_format($tprop,0)."</td><td>".number_format($tgast,0)."</td><td>".number_format($valor,0)."</td></tr>");
							}
							
						}
						
						$tot_caja-=$restar;
						$tot_otros+=$restar;
						
					}
					echo $gf->utf8("<tr><td>TOTAL</td><td><b>".number_format($base_caja,0)."</b></td>
					<td><b>".number_format($tot_v,0)."</b></td>
					<td><b>".number_format($tot_car,0)."</b></td>
					<td><b>".number_format($tot_aju,0)."</b></td>
					<td><b>".number_format($tot_p,0)."</b></td><td><b>".number_format($tot_g,0)."</b></td></tr>");
					echo $gf->utf8("</table><hr />
					
					<hr />
					$tabpro
					");

					
				echo $gf->utf8("
				</div>
				</div>
				</div>
				");
			}else{
				echo "No se encuentra el servicio";
			}


			echo $gf->utf8("
			</div>
		 
			");






		}else{
			echo $gf->utf8("<div class='callout callout-warning'>
								<h4>No hay informaci&oacute;n</h4>

								<p>No se han registrado pedidos, o bien, no hay un servicio abierto.</p>
							  </div>");
		}
	}elseif($actividad=="fel_test"){

		require_once("../lib_php/felSiigo.class.php");

		//INFO INTERFAZ

		$FEL=new felSiigo();
		$url_auth = "https://api.siigo.com/auth";
		$username = "siigoapi@pruebas.com";
		$access_key="OWE1OGNkY2QtZGY4ZC00Nzg1LThlZGYtNmExMzUzMmE4Yzc1OlJ2cTYudDk0IUI=";

		$token=$FEL->auth($username,$access_key);
		echo $token;

	}elseif($actividad=="fel_send"){
		require_once("../lib_php/felSiigo.class.php");

		//INFO INTERFAZ

		$FEL=new felSiigo();


		
		$username = "siigoapi@pruebas.com";
		$access_key="OWE1OGNkY2QtZGY4ZC00Nzg1LThlZGYtNmExMzUzMmE4Yzc1OlJ2cTYudDk0IUI=";
		
		//INFO EMPRESA
		$infoEmpresa=$gf->dataSet("SELECT NIT, NOMBRE, CIUDAD, DIRECCION, TELEFONO, REGIMEN, RESOLUCION_FACTURAS, INIFACT, LOGO, HEADER_FACT, FOOTER_FACT, SIIGO_USER, SIIGO_ACCESSKEY, CL_COMODIN, CODE_PROPINA, CODE_FEL, CODE_EMPL,FEL_COD_CAJA,FEL_COD_NOCAJA FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		if(count($infoEmpresa)>0){
			$rwEmpresa=$infoEmpresa[0];
			$empresa_nit=$rwEmpresa["NIT"];
			$username = $rwEmpresa["SIIGO_USER"];
			$access_key = $rwEmpresa["SIIGO_ACCESSKEY"];

			$empresa_nit=$rwEmpresa["NIT"];
			$CL_COMODIN=$rwEmpresa["CL_COMODIN"];
			$empresa_nombre=$rwEmpresa["NOMBRE"];
			$empresa_ciudad=$rwEmpresa["CIUDAD"];
			$empresa_telefono=$rwEmpresa["TELEFONO"];
			$empresa_direccion=$rwEmpresa["DIRECCION"];
			$empresa_regimen=$rwEmpresa["REGIMEN"];
			$empresa_facturac=$rwEmpresa["RESOLUCION_FACTURAS"];
			$empresa_inifact=$rwEmpresa["INIFACT"];
			$empresa_logo=$rwEmpresa["LOGO"];
			$fact_header=$rwEmpresa["HEADER_FACT"];
			$fact_footer=$rwEmpresa["FOOTER_FACT"];
			$code_propina=$rwEmpresa["CODE_PROPINA"];
			$code_fel=$rwEmpresa["CODE_FEL"];
			$code_empl=$rwEmpresa["CODE_EMPL"];
			$code_caja=$rwEmpresa["FEL_COD_CAJA"];
			$code_nocaja=$rwEmpresa["FEL_COD_NOCAJA"];
		
		}else{
			echo "Error 968";
			exit;
		}


		$id_pedido=$gf->cleanVar($_GET["id_ped"]);


		$rsAbonos=$gf->dataSet("SELECT A.ID_ABONO, A.FECHA, A.VALOR, A.ID_FP, FP.FEL_CODE FROM pedidos_abonos A JOIN formas_pago FP ON FP.ID_FP=A.ID_FP WHERE A.ID_PEDIDO='$id_pedido'");
		$tot_abonos=0;
		$abos="";
		$abonix=array();
		if(count($rsAbonos)>0){
			foreach($rsAbonos as $rwAbonos){
				$id_abono=$rwAbonos["ID_ABONO"];
				$id_forma=$rwAbonos["ID_FP"];
				$fel_code=$rwAbonos["FEL_CODE"];
				$vl_abono=$rwAbonos["VALOR"];
				if(!isset($abonix[$id_forma])) $abonix[$id_forma]=array("fel_code"=>$fel_code,"val"=>0);
				$abonix[$id_forma]["val"]+=$vl_abono;
				$tot_abonos+=$vl_abono;
			}
		}



		
		$resultInt = $gf->dataSet("SELECT F.CONSECUTIVO, DATE(F.FECHA) AS FECHA, CL.ID_CLIENTE, CL.IDENTIFICACION, CL.NOMBRE AS CLIENTE, CL.DIRECCION, CL.CIUDAD, CL.TELEFONO, CL.CORREO, CL.TIPO_ID, M.ID_MESA, M.TIPO, M.NOMBRE, P.ID_PEDIDO, P.DCTO, CL.HOMO_FEL, FP.CAJA, P.PROPINA, FP.FEL_CODE FROM mesas AS M JOIN pedidos AS P ON M.ID_MESA=P.ID_MESA JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO JOIN clientes CL ON CL.ID_CLIENTE=F.ID_CLIENTE JOIN formas_pago FP ON FP.ID_FP=P.ID_FP WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_PEDIDO='$id_pedido' ORDER BY P.ID_PEDIDO");
		
		if(count($resultInt)>0){
			$token=$FEL->auth($username,$access_key);
			if($token!=""){
				$rowInt=$resultInt[0];
				$acumbase=0;
				$acumprice=0;
				$acumimp=0;
				$id_mesa=$rowInt["ID_MESA"];
				$nombre=$gf->utf8($rowInt["NOMBRE"]);
				$id_pedido=$rowInt["ID_PEDIDO"];
				$f_consecutivo=$rowInt["CONSECUTIVO"];
				$f_consecutivo=str_pad($f_consecutivo, 6, '0', STR_PAD_LEFT);
				$f_fecha=$rowInt["FECHA"];
				$f_cliente=$gf->utf8($rowInt["CLIENTE"]);
				
				$f_mail=$rowInt["CORREO"];
				$f_identifica=$rowInt["IDENTIFICACION"];
				$f_tipoid=$rowInt["TIPO_ID"];
				$m_tipo=$rowInt["TIPO"];
				$f_direccion=$rowInt["DIRECCION"];
				$f_telefono=$rowInt["TELEFONO"];
				$f_ciudad=$rowInt["CIUDAD"];
				
				if($f_ciudad=="") $f_ciudad="66001";
				$f_departamento=substr($f_ciudad,0,2);
				
				$descuento=$rowInt["DCTO"];
				$propina=$rowInt["PROPINA"];
				$es_caja=$rowInt["CAJA"];
				$fp_fel_code=$rowInt["FEL_CODE"];



				if($fp_fel_code==""){
					if($es_caja==1){
						$ID_FORMA=$code_caja; // TODO: CAMBIAR EN PRODUCCION
					}else{
						$ID_FORMA=$code_nocaja;// TODO: CAMBIAR EN PRODUCCION
					}
				}else{
					$ID_FORMA = $fp_fel_code;
				}
				$HOMO_FEL=$rowInt["HOMO_FEL"];
				$ID_CLIENTE=$rowInt["ID_CLIENTE"];

				if($f_tipoid=="CC"){
					$TIPO_IDENTIFICACION="13";
				}else{
					$TIPO_IDENTIFICACION="31";
				}
				if($CL_COMODIN==$ID_CLIENTE){
					echo $gf->utf8("Error de validaci&oacute;n:  No es posible hacer facturas electronicas a nombre del cliente comodin");
					exit;
				}

				if(trim($f_identifica)==""){
					echo $gf->utf8("Error de validaci&oacute;n:  El campo identificaci&oacute;n del cliente es requerido");
					exit;
				}
				if(trim($f_direccion)==""){
					echo $gf->utf8("Error de validaci&oacute;n:  El campo direcci&oacute;n del cliente es requerido");
					exit;
				}
				if(trim($f_cliente)==""){
					echo $gf->utf8("Error de validaci&oacute;n:  Falta o est&aacute; incompleto el nombre del cliente");
					exit;
				}

				if(trim($f_mail)==""){
					echo $gf->utf8("Error de validaci&oacute;n:  Es necesario especificar el correo del cliente para la difusi&oacute;n del documento");
					exit;
				}

				
				// if($HOMO_FEL!=""){
				// 	$INVOICE_CLIENT=$HOMO_FEL;
				// }else{
					$NEW_CLIENT=array();
					$NEW_CLIENT["type"] = "Customer";
					$NEW_CLIENT["person_type"] = "Person";
					$NEW_CLIENT["id_type"] = $TIPO_IDENTIFICACION;
					$NEW_CLIENT["identification"] = trim($f_identifica);
					$NEW_CLIENT["check_digit"] = $FEL->calcularDV(trim($f_identifica));
					$f_cliente=preg_replace('/(?:\s\s+|\n|\t)/', ' ', $f_cliente);
					$C_NOMBRES=explode(" ",$f_cliente);
					if(count($C_NOMBRES)>3){
						$F_NOMBRE=$C_NOMBRES[0]." ".$C_NOMBRES[1];
						$L_NOMBRE=$C_NOMBRES[2]." ".$C_NOMBRES[3];
					}elseif(count($C_NOMBRES)==3){
						$F_NOMBRE=$C_NOMBRES[0]." ".$C_NOMBRES[1];
						$L_NOMBRE=$C_NOMBRES[2];
					}elseif(count($C_NOMBRES)==2){
						$F_NOMBRE=$C_NOMBRES[0];
						$L_NOMBRE=$C_NOMBRES[1];
					}else{
						$F_NOMBRE=$f_cliente;
						$L_NOMBRE=$f_cliente;
					}

					$NEW_CLIENT["name"] = [$F_NOMBRE,$L_NOMBRE];
					$NEW_CLIENT["commercial_name"] = $gf->utf8($f_cliente);
					$NEW_CLIENT["branch_office"] = 0;
					$NEW_CLIENT["active"] = true;
					$NEW_CLIENT["vat_responsible"] = false;
					//$NEW_CLIENT["fiscal_responsibilities"] =["code"=>"R-99-PN"];
					$NEW_CLIENT["address"]=array(
						"address"=>$gf->utf8($f_direccion),
						"city"=>array(
							"country_code"=>"Co",
							"state_code"=>$f_departamento,
							"city_code"=>$f_ciudad
						),
					  	"postal_code"=>$f_ciudad
					);
					$NEW_CLIENT["phones"]=array(
						array(
							"indicative"=>"57",
							"number"=>substr($f_telefono,0,10),
							"extension"=>""
						)
					);
					$NEW_CLIENT["contacts"]=array(
						array(
							"first_name"=>$F_NOMBRE,
							"last_name"=>$L_NOMBRE,
							"email"=>$f_mail,
							"phone" => array(
							  "indicative"=>"57",
							  "number"=>substr($f_telefono,0,10),
							  "extension"=>""
							)
						)
					);
					$NEW_CLIENT["comments"] = "Cliente creado desde Datafeed";

					//$INVOICE_CLIENT = $FEL->creaCliente($NEW_CLIENT,$token);
				//}


				if($m_tipo=="D"){
					$camprecio="PRECIO_DOM";
				}else{
					$camprecio="PRECIO";
				}
				
				$resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, SUM(SP.CANTIDAD) AS CANTIDAD, IM.ID_IMPUESTO, IM.NOMBRE AS IMPUESTONM, IM.PORCENTAJE, SP.LISTO, P.NOMBRE, P.DESCRIPCION, P.ID_PLATO, SP.PRECIO AS PRECIO, (SP.PRECIO/(1+(IM.PORCENTAJE/100))) AS BASE, P.HOMOPLATO FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN impuestos IM ON IM.ID_IMPUESTO=P.ID_IMPUESTO WHERE S.ID_PEDIDO='$id_pedido' GROUP BY SP.ID_ITEM ORDER BY S.ID_SILLA");
				$nprod=0;
				$arimp=array();
				$INVOICE_ITEMS=array();
				if(count($resultChairs)>0){
					foreach($resultChairs as $rwChair){
						$id_item=$rwChair["ID_ITEM"];
						$id_plato=$rwChair["ID_PLATO"];
						$id_silla=$rwChair["ID_SILLA"];
						$observacion=$rwChair["OBSERVACION"];
						$cantidad=$rwChair["CANTIDAD"];
						$nombre_plato=strtoupper($rwChair["NOMBRE"]);
						$descripcion=$rwChair["DESCRIPCION"];
						$listo=$rwChair["LISTO"];
						$precio=$rwChair["PRECIO"];
						$porcentaje=$rwChair["PORCENTAJE"];
						$base=$rwChair["BASE"];
						if($base==0){
							$base=$precio;
							$impuesto=0;
						}else{
							$impuesto=$precio-$base;

						}
						$id_impuesto=$rwChair["ID_IMPUESTO"];
						$nm_impuesto=$rwChair["IMPUESTONM"];

						$precio_base=$base*$cantidad;
						$impuestos=$impuesto*$cantidad;
						//impoconsumo 13171
						//IVA 19    13156
						// IVA 5%  13157
						$ID_TAX="";
						if($porcentaje==19){
							$ID_TAX="13156";
						}elseif($porcentaje==8){
							$ID_TAX="13171";
						}elseif($porcentaje==5){
							$ID_TAX="13157";
						}
						$TAXES=array();
						if($ID_TAX!=""){
							$TAXES[]=array("id"=>$ID_TAX);
						}
						$arimp[$id_impuesto]["nm"]=$nm_impuesto;
						$arimp[$id_impuesto]["pc"]=$porcentaje;
						if(!isset($arimp[$id_impuesto]["vl"])) $arimp[$id_impuesto]["vl"]=0;
						$arimp[$id_impuesto]["vl"]+=$impuestos;

						$acumbase+=$precio_base;
						$acumimp+=$impuestos;
						$precio_total=$precio_base+$impuestos;
						$acumprice+=$precio_total;
						

						$CD_PRO=$rwChair["HOMOPLATO"];
						$NM_PRO=$gf->utf8($nombre_plato);
						if($base>0){
							$INVOICE_ITEMS[]=array(
								"code"=>$CD_PRO,
								"description" => $NM_PRO,
								"quantity" => intval($cantidad),
								"price" => $base,
								"discount" => 0,
								"taxes"=>$TAXES
							);
						}
						$nprod++;
					}
				}

				if($propina>0){
					$INVOICE_ITEMS[]=array(
						"code"=>$code_propina,
						"description" => 'PROPINA',
						"quantity" => 1,
						"price" => $propina,
						"discount" => 0,
						"taxes"=>array()
				  );
				}


				$payments=array(array(
					"id" => $ID_FORMA,
					"value" => ($acumprice + $propina) - $tot_abonos,
					"due_date" => date("Y-m-d")
				));
				if(count($abonix)>0){
					foreach ($abonix as $id_forma=>$infoab) {
						$felco=$infoab["fel_code"];
						$valab=$infoab["val"];
						if($valab>0){
							$payments[]=array(
								"id" => $felco,
								"value" => $valab,
								"due_date" => date("Y-m-d")
							);

						}
					}
				}




				$INVOICE_PAYMENTS = $payments;
			
				$INVOICE = array(
					"document"=>array(
					  "id"=>$code_fel
					),
					"date" => date("Y-m-d"),
					"customer" => $NEW_CLIENT,
					
					"seller"=>$code_empl,
					"observations" => "Reemplaza factura POS No. ".$f_consecutivo,
					"items" => $INVOICE_ITEMS,
					"payments" => $INVOICE_PAYMENTS
				);
				// echo "<pre>";
				// print_r($INVOICE);
				// echo "</pre>";
				// exit;
				$FACTURA = $FEL->sendInvoice($INVOICE,$token);
				if($FACTURA!=""){
					if($FACTURA["id"]){
						$factura_id=$FACTURA["id"];
						$stamp=$FACTURA["stamp"];
						$mail=$FACTURA["mail"];
						
						$consecutivo=$FACTURA["number"];
						$nombre_fac=$FACTURA["name"];
						

						$estado=$stamp["status"];
						if($estado=="Accepted"){
							$cufe=$stamp["cufe"];
							$errors=$stamp["errors"];
						}elseif($estado=="Draft"){
							$cufe="Enviada a SIIGO, Sin enviar a DIAN";
							$errors="Enviada a SIIGO, Sin enviar a DIAN";
						}
						$sent=$mail["status"];
						$factura_completa=json_encode($FACTURA);
						$gf->dataIn("UPDATE facturas SET FEL=1, FEL_CONSECUTIVO='$consecutivo', FEL_CUFE='$cufe', FEL_NOMBRE='$nombre_fac', FEL_ESTADO='$estado', FEL_RESPUESTA='$factura_completa' WHERE ID_PEDIDO='$id_pedido'");

						echo "
						ID ASIGNADO: $factura_id<br />
						ESTADO DIAN: $estado<br />
						CUFE: $cufe<br />
						CORREO CLIENTE: $sent
						";
					}else{
						echo "Fall&oacute; el proceso de env&iacute;o al operador tecnol&oacute;gico<br />Detalles de respuesta: <hr />";
						print_r($FACTURA);
						echo "<hr />";
						echo "<pre>";
						print_r($INVOICE);
						echo "</pre>";
						
					}
				}else{
					echo "Fall&oacute; el proceso de env&iacute;o al operador tecnol&oacute;gico<br />Detalles de respuesta: <hr />";
					print_r($FACTURA);
					echo "<hr />";
					echo "<pre>";
					print_r($INVOICE);
					echo "</pre>";
					
				}

	
			}else{
				echo $gf->utf8("No se pudo autenticar al servicio de facturaci&oacute;n electr&oacute;nica");
			}
		}else{
			echo $gf->utf8("ERROR 408:  No se encuentra la factura");
		}


	}elseif($actividad=="factura_pedido"){
		
		
		require_once('../lib_php/tcpdf/tcpdf.php');
		require_once('../lib_php/tcpdf/mytcpdf.php');
		
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);
		
		$infoEmpresa=$gf->dataSet("SELECT NIT, NOMBRE, CIUDAD, DIRECCION, TELEFONO, REGIMEN, RESOLUCION_FACTURAS, INIFACT FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
		if(count($infoEmpresa)>0){
			$rwEmpresa=$infoEmpresa[0];
			$empresa_nit=$rwEmpresa["NIT"];
			$empresa_nombre=$rwEmpresa["NOMBRE"];
			$empresa_ciudad=$rwEmpresa["CIUDAD"];
			$empresa_telefono=$rwEmpresa["TELEFONO"];
			$empresa_direccion=$rwEmpresa["DIRECCION"];
			$empresa_regimen=$rwEmpresa["REGIMEN"];
			$empresa_facturac=$rwEmpresa["RESOLUCION_FACTURAS"];
			$empresa_inifact=$rwEmpresa["INIFACT"];
			if($empresa_regimen=="C"){
				$regimen="R&Eacute;GIMEN COM&Uacute;N";
			}else{
				$regimen="NO RESPONSABLE DE IMPUESTO AL CONSUMO";
			}
		}else{
			echo "Error 968";
			exit;
		}
		$out_htm='';
		$resultInt = $gf->dataSet("SELECT F.CONSECUTIVO, DATE(F.FECHA) AS FECHA, CL.IDENTIFICACION, CL.NOMBRE AS CLIENTE, CL.DIRECCION, CL.TELEFONO, CL.TIPO_ID, M.ID_MESA, M.TIPO, M.NOMBRE, P.ID_PEDIDO, P.DCTO FROM mesas AS M RIGHT JOIN pedidos AS P ON M.ID_MESA=P.ID_MESA JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO JOIN clientes CL ON CL.ID_CLIENTE=F.ID_CLIENTE WHERE P.ID_PEDIDO='$id_pedido' ORDER BY P.ID_PEDIDO");
		$out_htm='
		<table cellpadding="3" width="100%">
			<tr>
				<td align="center" width="30%">
					<img src="../'.$_SESSION["restbuslogo"].'" align="left" style="width:50px;" />
				</td>
				<td width="70%">
					<h3>'.$empresa_nombre.'</h3>
					<h5>Nit'.$empresa_nit.' - '.$regimen.'</h5>
					<h5>'.$empresa_ciudad.'</h5>
					<h6>Dir: '.$empresa_direccion.', Tel:'.$empresa_telefono.'</h6>
				</td>
			</tr>
		
		';
		
		if(count($resultInt)>0){
			
			foreach($resultInt as $rowInt){
				$acumbase=0;
				$acumprice=0;
				$acumimp=0;
				$id_mesa=$rowInt["ID_MESA"];
				$nombre=$rowInt["NOMBRE"];
				$id_pedido=$rowInt["ID_PEDIDO"];
				$f_consecutivo=$rowInt["CONSECUTIVO"];
				$f_consecutivo=str_pad($f_consecutivo, 6, '0', STR_PAD_LEFT);
				$f_fecha=$rowInt["FECHA"];
				$f_cliente=$rowInt["CLIENTE"];
				$f_identifica=$rowInt["IDENTIFICACION"];
				$f_tipoid=$rowInt["TIPO_ID"];
				$m_tipo=$rowInt["TIPO"];
				$f_direccion=$rowInt["DIRECCION"];
				$f_telefono=$rowInt["TELEFONO"];
				$out_htm.='
			<tr>
				<td align="right" colspan="2"><hr /></td>
			</tr>
			<tr>
				<td align="right" colspan="2">FACT: '.$f_consecutivo.'  -  FECHA: '.$f_fecha.'</td>
			</tr>
			<tr>
				<td colspan="2">
					<table style="font-size:12px;" border="1" width="100%" cellpadding="3">
						<tr><td colspan="4" align="center">INFORMACI&Oacute;N DEL CLIENTE</td></tr>
						<tr><td bgcolor="#CCC">IDENTIFICACI&Oacute;N</td><td>'.$f_tipoid.'-'.$f_identifica.'</td><td bgcolor="#CCC">NOMBRE</td><td>'.$f_cliente.'</td></tr>
						<tr><td bgcolor="#CCC">DIRECCI&Oacute;N</td><td>'.$f_direccion.'</td><td bgcolor="#CCC">TEL&Eacute;FONO</td><td>'.$f_telefono.'</td></tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				';
				$nsi=0;
				$inisill=0;
				
				if($m_tipo=="D"){
					$camprecio="PRECIO_DOM";
				}else{
					$camprecio="PRECIO";
				}
				
				$resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, SUM(SP.CANTIDAD) AS CANTIDAD, SP.LISTO, P.NOMBRE, P.DESCRIPCION, P.$camprecio AS PRECIO, SUM((SP.PRECIO*IM.PORCENTAJE)/100) AS IMPUESTO FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN impuestos IM ON IM.ID_IMPUESTO=P.ID_IMPUESTO WHERE S.ID_PEDIDO='$id_pedido' GROUP BY P.ID_PLATO ORDER BY S.ID_SILLA");
				if(count($resultChairs)>0){
					$out_htm.='<table style="font-size:10px;" border="1" width="100%" cellpadding="2">
					<tr style="font-weight:bold;"><td width="50%">PRODUCTO</td><td width="10%">CANT.</td><td width="20%">VR UNIT</td><td width="20%">SUBTOTAL</td></tr>';
					$bklass="ui-semiwhite";
					foreach($resultChairs as $rwChair){
						
						$id_item=$rwChair["ID_ITEM"];
						$id_silla=$rwChair["ID_SILLA"];
						$observacion=$rwChair["OBSERVACION"];
						$cantidad=$rwChair["CANTIDAD"];
						$nombre_plato=$rwChair["NOMBRE"];
						$descripcion=$rwChair["DESCRIPCION"];
						$listo=$rwChair["LISTO"];
						$precio=$rwChair["PRECIO"];
						$impuestos=$rwChair["IMPUESTO"];
						if($_SESSION["restbusstyle"]==1){
							$precio_base=$precio-$impuestos;
						}else{
							$precio_base=$precio;
						}
						$acumbase+=($precio_base*$cantidad);
						$acumimp+=($impuestos*$cantidad);
						$precio_total=$precio_base+$impuestos;
						$acumprice+=($precio_total*$cantidad);
						if($id_silla!=$inisill){
							$nsi++;
							$inisill=$id_silla;
						}
						$out_htm.='<tr>
						<td>'.$nombre_plato.'</td>
						<td align="right">'.$cantidad.'</td>
						<td align="right">'.number_format($precio_base,0).'</td>
						<td align="right">'.number_format(($precio_base*$cantidad),0).'</td>
						</tr>';
						$inisill=$id_silla;
					}
					$out_htm.='<tr><td colspan="3" align="right"><b>TOTAL</b></td><td align="right"><b>'.number_format($acumbase,0).'</b></td></tr>';
					$out_htm.='<tr><td colspan="3" align="right"><b>IMPUESTO</b></td><td align="right"><b>'.number_format($acumimp,0).'</b></td></tr>';
					$out_htm.='<tr><td colspan="3" align="right"><b>TOTAL</b></td><td align="right"><b>'.number_format($acumprice,0).'</b></td></tr>';
				
					$out_htm.='</table>';
				}
			}
		}
		$out_htm.='</td></tr></table>';
		//echo $out_htm;
		$pageLayout = array(140, 216);
		$pdf = new myTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageLayout, true, 'UTF-8', false);

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Torresoft');
		$pdf->SetTitle('Factura - DataFEED');
		$pdf->SetSubject('Torresoft');
		$pdf->SetKeywords('Factura Generada');
		
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//$pdf->SetMargins($model_margin_left,$model_margin_top,$model_margin_right);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
			require_once(dirname(__FILE__).'/lang/spa.php');
			$pdf->setLanguageArray($l);
		}
		$pdf->setFont('helvetica');
		$pdf->SetFontSize(10);
		$pdf->AddPage();
		$pdf->writeHTML($gf->utf8($out_htm), true, false, true, false, '');
		$pdf->lastPage();
		$pdf->Output();

	}
}else{
	echo "No has iniciado sesion!";
}
?>