<?php
ini_set("display_errors",1);
session_start();
date_default_timezone_set("America/Bogota");
	include_once("autoload.php");
	$gf=new generalFunctions;


	$serv=$gf->dataSet("SELECT ID_SERVICIO FROM servicio WHERE ESTADO=0");
	if(count($serv)==0){
		echo $gf->utf8("
		<div class='alert alert-danger alert-dismissible'>
		<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>x</button>
		<h4><i class='icon fa fa-ban'></i> ATENCI&Oacute;N!</h4>
		No hay un servicio activo, contacta el administrador para activar el servicio
		</div>
		");
		exit;
	}else{
		$_SESSION["restservice"]=$serv[0]["ID_SERVICIO"];
	}

	$sender=$_SERVER["PHP_SELF"];
	$arcols=array(0=>"#FF0000",10=>"#FF0000",20=>"#FF4000",30=>"#FF4000",40=>"#FF8000",50=>"#FFBF00",60=>"#FFFF00",70=>"#BFFF00",80=>"#80FF00",90=>"#40FF00",100=>"#01DF01");
	if(isset($_SESSION["restuiduser"]) && $gf->isUserAdm($_SESSION["restuiduser"],$_SESSION["tk"])){
		$flag=$gf->cleanVar($_GET["flag"]);
		$permisos=array();
		$permisos["M"]=2;
		$permisos["J"]=1;
		$permisos["A"]=0;
		$mipermiso=$permisos[$_SESSION["restprofile"]];
		if($_SESSION["restprofile"]=="M" || $_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="J"){
			if($flag=="opentable"){
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				if(isset($_GET["tlv"])){
					$_SESSION["tlv"]=$gf->cleanVar($_GET["tlv"]);
				}
				if(!isset($_SESSION["tlv"])){
					$_SESSION["tlv"]="P";
				}
				$rsSilla=$gf->dataSet("SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido' ORDER BY ID_SILLA");
				if(count($rsSilla)==0){
					$id_silla=$gf->dataInLast("INSERT INTO sillas (ID_PEDIDO) VALUES ('$id_pedido')");
				}else{
					$id_silla=$rsSilla[0]["ID_SILLA"];
				}
				$nch=1;
				$t=$gf->cleanVar($_GET["t"]);
				$resultChair = $gf->dataSet("SELECT S.ID_SILLA, S.ID_PEDIDO, S.COLOR, S.GENDER, PE.ID_MESA, PE.ID_TENDER, PE.CHEF, PE.CAJA, PE.DIRECCION, PE.DESPACHADO, PE.PAGADO, PE.DENOM, S.OBSERVACION, P.ID_ITEM, P.ID_PLATO, P.CANTIDAD, P.PRECIO, P.OBSERVACION AS OBSERPLAT, PL.NOMBRE AS PLATO, P.TIPO_PLATO, P.LISTO, P.ENTREGADO, SUM(P.CANTIDAD) AS PEDIDOS, SUM(P.PRINTED) AS IMPRESOS, M.NOMBRE AS MESA, P.PRINTED, M.TIPO, GROUP_CONCAT(CONCAT(C.NOMBRE,'|',R.ESTADO,'|',RO.NOMBRE,'|',ROP.OPTIS) SEPARATOR '+*+') AS COMPOSITION FROM mesas AS M JOIN pedidos AS PE ON PE.ID_MESA=M.ID_MESA LEFT JOIN sillas AS S ON(PE.ID_PEDIDO=S.ID_PEDIDO) LEFT JOIN sillas_platos AS P ON(S.ID_SILLA=P.ID_SILLA) LEFT JOIN platos AS PL ON(P.ID_PLATO=PL.ID_PLATO)LEFT JOIN platos_composicion C ON P.ID_PLATO=C.ID_PLATO LEFT JOIN sillas_platos_composicion R ON R.ID_ITEM=P.ID_ITEM AND R.ID_RACION=C.ID_RACION LEFT JOIN racion_opciones RO ON R.ID_OPCION=RO.ID_OPCION LEFT JOIN (SELECT ID_RACION,GROUP_CONCAT(ID_OPCION) OPTIS FROM racion_opciones GROUP BY ID_RACION ORDER BY ID_RACION)ROP ON ROP.ID_RACION=C.ID_RACION WHERE S.ID_SILLA='$id_silla' GROUP BY P.ID_ITEM ORDER BY P.ID_ITEM");
				$listos=0;
				$chefed=0;
				$nitem=1;
				$toprint=0;
				$entregados=0;
				$entrega_total=0;
				$peditotal=0;
				if(count($resultChair)>0){
					$rowChair=$resultChair[0];
					$id_chair=$rowChair["ID_SILLA"];
					$observacion=$rowChair["OBSERVACION"];
					$id_pedido=$rowChair["ID_PEDIDO"];
					$gender=$rowChair["GENDER"];
					$id_mesa=$rowChair["ID_MESA"];
					$mesa=$rowChair["MESA"];
					$tipo=$rowChair["TIPO"];
					
					

					$cantpedido=$rowChair["PEDIDOS"] || 0;
					$cantimpreso=$rowChair["IMPRESOS"] || 0;

					$direccion=$rowChair["DIRECCION"];
					$despachado=$rowChair["DESPACHADO"];
					$devuelta=$rowChair["DENOM"];
					$pagado=$rowChair["PAGADO"];
					$id_tender=$rowChair["ID_TENDER"];
					if($listos==""){
						$listos=0;
					}

					$entrega_total+=$entregados;
					$peditotal+=$cantpedido;
					
					if($cantimpreso<$cantpedido && $cantpedido>0){
						$toprint++;
					}
					$percent=100;
					$chef=$rowChair["CHEF"];
					$titca="CANT.";

					echo $gf->utf8("<div id='ch_$id_chair' idch='$id_chair' class='box box-danger'><div class='box-header'> <i class='fa fa-cutlery'></i> <b>PEDIDO $id_pedido - MESA: $mesa</b> 
					");
					$active=1;
					//if($active==1){
					//if($_SESSION["restuiduser"]==$id_tender || $_SESSION["restprofile"]!="M" || $_SESSION["restmancomun"]>=$mipermiso){
						echo $gf->utf8("
						<button style='margin-right:5px;' class='btn btn-sm btn-success pull-right' onclick=\"getDialog('$sender?flag=additemdlg&id_silla=$id_chair&id_mesa=$id_mesa&id_pedido=$id_pedido&nch=$nch&ait=$active&t=$t','500','Adicionar')\"><i class='fa fa-cutlery'></i> Agregar Producto</button>
						");
					//}
					//}
					echo $gf->utf8("
					</div>
					<div class='box-body'>
					<table class='latabla table table-striped' width='100%'><tr><td></td><td>ITEM</td><td>$titca</td><td>OPC</td></tr>");
					$nprod=0;
					$toprint=0;
					$total_pex=0;
					foreach($resultChair as $rowChair){
						$icono="fa-cutlery";
						$id_item=$rowChair["ID_ITEM"];
						$cantidad=$rowChair["CANTIDAD"];
						if($cantidad=="") $cantidad=0;
						$plato=$rowChair["PLATO"];
						$listo=$rowChair["LISTO"];
						$printedd=$rowChair["PRINTED"];
						$entregado=$rowChair["ENTREGADO"];
						$composition=$rowChair["COMPOSITION"];
						$obserplat=$rowChair["OBSERPLAT"];
						$tipoplato=$rowChair["TIPO_PLATO"];
						$prux=$rowChair["PRECIO"];
						if($prux=="") $prux=0;

						$precio = $prux * $cantidad;	
						$total_pex+=$precio;
						if($printedd<$cantidad) $toprint++;
						if($tipoplato==1){
							$tipoplaicon="<i class='fa fa-spoon orange'></i>";
						}elseif($tipoplato==2){
							$tipoplaicon="<i class='fa fa-cutlery red'></i>";
						}else{
							$tipoplaicon="";
						}

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
						if($obserplat!=""){
							$comps.=$obserplat.", ";
						}
						if($comps==""){
							
						}else{
							$comps=substr($comps,0,-2);
						}
						
						if($id_item!=""){
							$nprod++;
							if($chef!="0000-00-00 00:00:00"){
								$cantii="($cantidad)";
							}else{
								$cantii="";
							}
							echo $gf->utf8("<tr class='ui-widget-content ui-corner-all' id='tritem_$id_item'><td><b>$nprod</b>$tipoplaicon</td><td><span style='font-size:17px;'> $plato</span><br/><small>$comps</small></td>
							<td align='center'>");
							if(($_SESSION["restuiduser"]==$id_tender || $_SESSION["restmancomun"]>=$mipermiso) && ($_SESSION["resttendercants"]>=$mipermiso || $printedd==0)){
								echo $gf->utf8("
								<div class='input-group'>
									<span class='input-group-addon btn btn-lg btn-danger mimibutton' onclick=\"downNumber('cant_$id_item');calcPrix()\"><i class='fa fa-arrow-down'></i></span>
									<input type='number' onchange=\"cargaHTMLvars('state_proceso','$sender?flag=edit_cant&item=$id_item&id_pedido=$id_pedido&id_mesa=$id_mesa&t=$tipo&val='+this.value)\" style='font-size:23px;height:40px;text-align:center;min-width:45px;' value='$cantidad' id='cant_$id_item' class='form-control univalunichair cantitis'  it='$id_item' pr='$prux' id='nchairs' name='nchairs'  min='1' max='20' />
									<span class='input-group-addon btn-lg btn btn-success mimibutton' onclick=\"upNumber('cant_$id_item');calcPrix()\"><i class='fa fa-arrow-up'></i></span>
								</div>
								");
							}else{
								echo $gf->utf8($cantidad);
							}
							echo $gf->utf8("
							</td><td>");
							if(($_SESSION["restuiduser"]==$id_tender || $_SESSION["restmancomun"]>=$mipermiso) && ($_SESSION["resttendercants"]>=$mipermiso || $printedd==0)){
								echo $gf->utf8("
								<button class='btn btn-warning' onclick=\"getDialog('$sender?flag=config_plat&id_item=$id_item','600','Editar','','','reloaHash()')\"><i class='fa fa-edit'></i></button>
								");
							}else{
								echo $gf->utf8("
								<button class='btn btn-default disabled'><i class='fa fa-edit'></i></button>
								");
							}
							if(($_SESSION["restuiduser"]==$id_tender || $_SESSION["restmancomun"]>=$mipermiso) && ($_SESSION["resttendercants"]>=$mipermiso || $printedd==0)){
								echo $gf->utf8("<button class='btn btn-danger' onclick=\"getDialog('$sender?flag=del_itemped&id_item=$id_item&id_pedido=$id_pedido&id_mesa=$id_mesa&t=$tipo&printed=$printedd','300','Borrar')\"><i class='fa fa-trash'></i></button>");
							}else{
								echo $gf->utf8("<button class='btn btn-default disabled'><i class='fa fa-trash'></i></button>");
								
							}


							echo $gf->utf8(" <small id='pricex_$id_item'>".@number_format($precio,0)."</small>
							</td></tr>");
						}
					}
					echo $gf->utf8("<tr><td colspan='4' align='center'>Observaciones: <textarea onchange=\"cargaHTMLvars('state_proceso','$sender?flag=up_observa&id_silla=$id_chair','','5000','unival_observa')\" name='obs_$id_chair' class='form-control unival_observa'>$observacion </textarea><br />
					<big><b>TOTAL PEDIDO: </b><b id='totl_ped_calc'>".@number_format($total_pex,0)."</b></big><br />
					");
					
					if(($_SESSION["restuiduser"]==$id_tender || $_SESSION["restmancomun"]>=$mipermiso) && ($_SESSION["resttendercancel"]>=$mipermiso)){
						$btn_cancel="<button class='btn btn-danger margined' onclick=\"getDialog('$sender?flag=cancel_pedido&id_pedido=$id_pedido&id_mesa=$id_mesa','200','Cancelar\ Pedido')\"> <i class='fa fa-trash'></i> Cancelar Pedido</button>";
					}else{
						$btn_cancel="";
					}
					$btn_cobra="";
					$btn_send="";
					if($chef!="0000-00-00 00:00:00" && $toprint==0){
						$icon="fa-fire";
						$classd="bg-orange";
						if($_SESSION["restprofile"]!="M"){

							if($_SESSION["restfastmode"]==1){
								$cobra_fn="getDialog('Admin/site_box.php?flag=fact_pedido1&id_ped=$id_pedido','1200','Facturar','','','cargaHTMLvars(\'contenidos\',\'mviews.php?flag=home\')','unival_dix_$id_pedido')";
							}else{
								$cobra_fn="cargaHTMLvars('contenidos-aux','Admin/site_box.php?flag=opentable&id_mesa=$id_mesa&id_pedido=$id_pedido&callback=ped')";
							}
							$btn_cobra="<button class='btn btn-primary margined' lnk-tsf='#cobrar-$id_pedido' lnk-cont='contenidos-aux' onclick=\"$cobra_fn\"><i class='fa fa-dollar'></i> Cobrar</button>";
							
						}
						$btn_send="<button id='chefin_$id_pedido' class='btn btn-warning margined hidden' onclick=\"getDialog('$sender?flag=confirm_pedido&id_pedido=$id_pedido&tipo=chef&t=$tipo&id_mesa=$id_mesa')\"><i class='fa fa-fire'></i> Enviar al Chef</button>";
					}else{
						if($nprod>0){
							if($_SESSION["restuiduser"]==$id_tender || $_SESSION["restmancomun"]>=$mipermiso){
								if($toprint>0){
									$btn_send="<button id='chefin_$id_pedido' class='btn btn-warning margined' onclick=\"getDialog('$sender?flag=confirm_pedido&id_pedido=$id_pedido&tipo=chef&t=$tipo&id_mesa=$id_mesa')\"><i class='fa fa-fire'></i> Enviar al Chef</button>";
								}else{
									$btn_send="<button id='chefin_$id_pedido' class='btn btn-warning margined hidden' onclick=\"getDialog('$sender?flag=confirm_pedido&id_pedido=$id_pedido&tipo=chef&t=$tipo&id_mesa=$id_mesa')\"><i class='fa fa-fire'></i> Enviar al Chef</button>";
								}
							}else{
								$rsTen=$gf->dataSet("SELECT NOMBRES FROM usuarios WHERE ID_USUARIO='$id_tender' ORDER BY ID_USUARIO");
								$tender=$rsTen[0]["NOMBRES"];
								$btn_send="<span class='badge'>Pedido de $tender</span>";
							}
							
						}
						
					}

					if($tipo=="D"){
						$checki0="";
						$checki1="";
						$checki2="";
						switch($pagado){
							case 0: 
								$checki0="selected='selected'";
								break;
							case 1:
								$checki1="selected='selected'";
								break;
							case 2:
								$checki2="selected='selected'";
								break;
						}
						echo $gf->utf8("
						<div class='row'>
							<div class='col-md-12'>
								<div class='control-group'>
									<label for='nchairs'>DIRECCI&Oacute;N</label>
									<textarea class='form-control unival_observa' onchange=\"cargaHTMLvars('state_proceso','$sender?flag=up_direc&id_ped=$id_pedido','','5000','unival_observa')\" id='direc_$id_pedido' name='direc_$id_pedido'>$direccion</textarea>
									
								</div>
								<div class='control-group'>
									<label for='devuelta'>DV</label>
									<input type='number' style='font-size:30px;height:60px;text-align:center;' class='form-control univalunichair' id='devuelta_$id_pedido' name='devuelta_$id_pedido'  min='0' max='1000000' value='$devuelta' onchange=\"cargaHTMLvars('state_proceso','$sender?flag=up_devuelta&id_ped=$id_pedido','','5000','univalunichair')\" />
								</div>
								
								<div class='control-group'>
									<label for='devuelta'>PG</label>
									<select  style='font-size:30px;height:60px;text-align:center;' class='form-control univalpaga' id='paga_$id_pedido' name='paga_$id_pedido' onchange=\"cargaHTMLvars('state_proceso','$sender?flag=up_paga&id_ped=$id_pedido','','5000','univalpaga')\">
										<option value='0' $checki0>DEBE</option>
										<option value='1' $checki1>PAG&Oacute;</option>
										<option value='2' $checki2>ABON&Oacute;</option>
									</select>
								</div>
								
							</div>
						</div>
						");
					}

					
					echo $gf->utf8("
					
					$btn_send $btn_cobra $btn_cancel
					<button class='btn btn-sm pull-left btn-warning margined' onclick=\"javascript:history.back()\"><i class='fa fa-arrow-left'></i></button>
					
					<button class='btn btn-sm pull-left btn-default margined' onclick=\"getDialog('$sender?flag=rota_pedido&id_pedido=$id_pedido&curmesa=$id_mesa&tipo=$t')\"><i class='fa fa-refresh'></i></button>

					<button class='btn btn-sm pull-left btn-default margined' onclick=\"getDialog('$sender?flag=merge_pedido&id_pedido=$id_pedido&curmesa=$id_mesa&tipo=$t')\"><i class='fa fa-compress'></i></button>
					<button class='btn btn-default btn-sm margined pull-left' onclick=\"getDialog('$sender?flag=printcom&id_mesa=$id_mesa&id_pedido=$id_pedido')\"><i class='fa fa-print'></i></button>
					

					</td></tr></table>
					
					</div>
					
					<script>
						$(function(){
							$(window).scroll(strolin);
							strolinch()
						})
						
						function strolinch(){
							var a=$(window).scrollTop();
							var h=$(window).height();
							$('#elbuttonkr').css('top',(Math.round(a)+h-70)+'px');
						}

						function calcPrix(){
							var total=0;
							$('.cantitis').each(function(){
								var item=$(this).attr('it');
								var precio=parseInt($(this).attr('pr'));
								var cant=parseInt($('#cant_'+item).val());
								total+=parseInt(precio*cant);
							});
							setTimeout(function(){
								$('#totl_ped_calc').text(total);
							},500);
						}
					</script>
					</div>
					");
				}
				
				
	
			}elseif($flag=="observitem_go"){
				$id_item=$gf->cleanVar($_GET["id_item"]);
				$OBSERV=$_POST["obserplato"];
				$gf->dataIn("UPDATE sillas_platos SET OBSERVACION='$OBSERV' WHERE ID_ITEM='$id_item'");

			}elseif($flag=="del_itemped"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$t=$gf->cleanVar($_GET["t"]);
				$id_item=$gf->cleanVar($_GET["id_item"]);
				$printed=$gf->cleanVar($_GET["printed"]);
				if($printed==1 && !isset($_GET["confirm"])){
					echo $gf->utf8("
					Este producto ya est&aacute; comandado, Deseas borrarlo?<hr />
					<button class='btn btn-danger btn-md pull-left' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=del_itemped&id_pedido=$id_pedido&id_mesa=$id_mesa&rnd=$rnd&t=$t&id_item=$id_item&printed=$printed&confirm=1')\">Borrar</button>
					<button class='btn btn-warning btn-md pull-left' onclick=\"closeD('$rnd')\">Cancelar</button>
					");
				}else{

					$itm=$gf->dataSet("SELECT P.NOMBRE AS PLATO FROM sillas_platos SP JOIN platos P ON SP.ID_PLATO=P.ID_PLATO WHERE SP.ID_ITEM='$id_item'");
					if(count($itm)>0){
						$nm=$itm[0]["PLATO"];
					}else{
						$nm=$id_item;
					}

					$ok=$gf->dataIn("DELETE FROM sillas_platos WHERE ID_ITEM='$id_item'");
					$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"ITEM COMANDADO BORRADO: $nm",$_SESSION["restuiduser"]);
					if($ok){
						echo $gf->utf8("
						<script>
						$(function(){
							$('#tritem_$id_item').remove();
							closeD('$rnd');
							sockEmitir('up_mesa',{id_mesa:$id_mesa,id_pedido:$id_pedido,tipo:'$t'});
						});
						</script>
						");
					}
					
				}
				

			}elseif($flag=="merge_pedido"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$id_mesa=$gf->cleanVar($_GET["curmesa"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$curtipo=$gf->cleanVar($_GET["tipo"]);
				
				$rsMesas=$gf->dataSet("SELECT M.ID_MESA, M.NOMBRE, P.ID_PEDIDO FROM mesas M JOIN pedidos P ON P.ID_MESA=M.ID_MESA WHERE M.ID_SITIO='{$_SESSION["restbus"]}' AND M.TIPO<>'D' AND P.ID_SERVICIO='{$_SESSION["restservice"]}' AND P.CIERRE='0000-00-00 00:00:00' AND M.ID_MESA<>'$id_mesa' ORDER BY M.NOMBRE");
		
				if(count($rsMesas)>0){
					echo $gf->utf8("
					Se va a fusionar el pedido No. $id_pedido con el de otra mesa; selecciona la mesa que se va a fusionar en esta mesa<hr />

					<ul class='list-group'>
					");
					foreach($rsMesas as $rwMesas){
						$id_mesak=$rwMesas["ID_MESA"];
						$pedidok=$rwMesas["ID_PEDIDO"];
						$nmesa=$rwMesas["NOMBRE"];
						echo $gf->utf8("<li class='list-group-item item-red clearfix'><label style='width:100%;'>$nmesa (Ped. $pedidok) <input type='radio' name='mesato' value='$pedidok' id='mesato_$id_mesak' class='pull-right unival_mesato' style='width:25px;height:25px;' /></label></li>");
					}
					echo $gf->utf8("</ul><br />
					
					<button class='btn btn-warning btn-sm' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=merge_pedido_go&id_pedido=$id_pedido&curmesa=$id_mesa&rnd=$rnd','','5000','unival_mesato')\">Fusionar</button>");
				}else{
					echo $gf->utf8("
					No hay pedidos en otras mesas
					");
				}
			}elseif($flag=="rota_pedido"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$id_mesa=$gf->cleanVar($_GET["curmesa"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$curtipo=$gf->cleanVar($_GET["tipo"]);
				$rsMesas=$gf->dataSet("SELECT ID_MESA, NOMBRE FROM mesas WHERE ID_SITIO='{$_SESSION["restbus"]}' AND TIPO<>'D' AND ID_MESA NOT IN(SELECT ID_MESA FROM pedidos WHERE ID_SERVICIO='{$_SESSION["restservice"]}' AND CIERRE='0000-00-00 00:00:00') AND ID_MESA<>'$id_mesa' ORDER BY NOMBRE");

				echo $gf->utf8("
				Se va a trasladar el pedido No. $id_pedido a otra mesa; selecciona la mesa destino<hr />

				<ul class='list-group'>
				");
				foreach($rsMesas as $rwMesas){
					$id_mesak=$rwMesas["ID_MESA"];
					$nmesa=$rwMesas["NOMBRE"];
					echo $gf->utf8("<li class='list-group-item item-red clearfix'><label style='width:100%;'>$nmesa <input type='radio' name='mesato' value='$id_mesak' id='mesato_$id_mesak' class='pull-right unival_mesato' style='width:25px;height:25px;' /></label></li>");
				}
				echo $gf->utf8("</ul><br />
				
				<button class='btn btn-warning btn-sm' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=rota_pedido_go&id_pedido=$id_pedido&curmesa=$id_mesa&rnd=$rnd','','5000','unival_mesato')\">Trasladar</button>");
				
			}elseif($flag=="merge_pedido_go"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$id_mesa=$gf->cleanVar($_GET["curmesa"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$id_pedido_traer=$_POST["mesato"];
				if($id_pedido_traer>0){
					if($_SESSION["restchairs"]==1){
						$ok=$gf->dataIn("UPDATE sillas SET ID_PEDIDO='$id_pedido' WHERE ID_PEDIDO='$id_pedido_traer'");
						if($ok){
							$gf->dataIn("DELETE FROM pedidos WHERE ID_PEDIDO='$id_pedido_traer'");
							$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido_traer,"PEDIDO FUSIONADO CON PED No. $id_pedido",$_SESSION["restuiduser"]);
					
							echo $gf->utf8("<input type='hidden' id='callbackeval' value=\"closeD('$rnd');cargaHTMLvars('contenidos-aux','$sender?flag=opentable&id_mesa=$id_mesa&id_pedido=$id_pedido')\" />");
							//echo "$id_mesa -> $id_mesato";
						}else{
							echo $gf->utf8("Error al realizar la fusi&oacute;n");
						}
					}else{
						$chaira=$gf->dataSet("SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido'");

						if(count($chaira)>0){
							$id_sillato=$chaira[0]["ID_SILLA"];
							$ok=$gf->dataIn("UPDATE sillas_platos SET ID_SILLA='$id_sillato' WHERE ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido_traer')");
							if($ok){
								$mesalibre=$gf->dataSet("SELECT ID_MESA FROM pedidos WHERE ID_PEDIDO='$id_pedido_traer'");
								$id_mesalibre=$mesalibre[0]["ID_MESA"];
								$tipi=$gf->dataSet("SELECT TIPO FROM mesas WHERE ID_MESA='$id_mesa'");
								$t=$tipi[0]["TIPO"];
								$gf->dataIn("DELETE FROM sillas WHERE ID_PEDIDO='$id_pedido_traer'");
								$gf->dataIn("DELETE FROM pedidos WHERE ID_PEDIDO='$id_pedido_traer'");
								echo $gf->utf8("
								<script>
								$(function(){
									sockEmitir('up_mesa',{id_mesa:$id_mesa,id_pedido:$id_pedido,tipo:'$t'});
									sockEmitir('liberar',{id_mesa:$id_mesalibre});
								});
								</script>
								<input type='hidden' id='callbackeval' value=\"closeD('$rnd');cargaHTMLvars('contenidos-aux','$sender?flag=opentable&id_mesa=$id_mesa&id_pedido=$id_pedido')\" />");
								//echo "$id_mesa -> $id_mesato";
								$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"PEDIDOS FUSIONADOS $id_pedido y $id_pedido_traer",$_SESSION["restuiduser"]);
							}else{
								echo $gf->utf8("Error al realizar la fusi&oacute;n");
							}
						}else{
							echo $gf->utf8("Error al realizar la fusi&oacute;n");
						}
						
					}
					
				}else{
					echo "Debes seleccionar un pedido para fusionar";
				}

			}elseif($flag=="rota_pedido_go"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$id_mesa=$gf->cleanVar($_GET["curmesa"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$id_mesato=$_POST["mesato"];
				if($id_mesato>0){
					$ok=$gf->dataIn("UPDATE pedidos SET ID_MESA='$id_mesato' WHERE ID_MESA='$id_mesa' AND ID_PEDIDO='$id_pedido'");
					$tipi=$gf->dataSet("SELECT TIPO FROM mesas WHERE ID_MESA='$id_mesato'");
					$t=$tipi[0]["TIPO"];
					$tender=$_SESSION["restuiduser"];
					if($ok){
						echo $gf->utf8("<input type='hidden' id='callbackeval' value=\"closeD('$rnd');loadMask('$sender?flag=opentable&id_mesa=$id_mesato&id_pedido=$id_pedido')\"  lnk-tsf='#mesa-$id_mesato' lnk-cont='contenidos-aux' />
						<script>
						$(function(){
							sockEmitir('ocupar',{id_mesa:$id_mesato,id_pedido:$id_pedido,tender:$tender});
							sockEmitir('up_mesa',{id_mesa:$id_mesato,id_pedido:$id_pedido,tipo:'$t'});
							sockEmitir('liberar',{id_mesa:$id_mesa});
						});
						</script>
						
						");
						//echo "$id_mesa -> $id_mesato";
						$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"PEDIDO TRASLADADO A MESA ID:$id_mesato",$_SESSION["restuiduser"]);
					}else{
						echo "Error al realizar el traslado";
					}
				}else{
					echo "Debes seleccionar una mesa destino";
				}

			}elseif($flag=="pago"){
				if($_SESSION["restprofile"]=="J"){
					
					$id_pedido=$_POST["id_pedido"];
					$dcto=$_POST["dcto"];
					if(mysqli_query($link,"UPDATE pedidos SET PAGO='1', DCTO='$dcto' WHERE ID_PEDIDO='$id_pedido'")){
						echo "1";
					}else{
						echo "0";
					}
					$gf->log($_SESSION["restbus"],0,$id_pedido,"PEDIDO COBRADO CON DESCUENTO $dcto",$_SESSION["restuiduser"]);
				
				}
				
				
			}elseif($flag=="printcom"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				//$ok=$gf->dataIn("UPDATE pedidos SET PRINTED='0' WHERE ID_PEDIDO='$id_pedido'");
				//$ok=$gf->dataIn("UPDATE sillas_platos SET PRINTED='0' WHERE ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido')");
				//if($ok){
				echo $gf->utf8("
				Enviando.....
				<script>
				$(function(){
					closeD('$rnd');
					sockEmitir('comandar',{id_pedido:$id_pedido,parte:'todo'});
				})
				</script>");
				$gf->log($_SESSION["restbus"],0,$id_pedido,"IMPRIMIR COMANDA (COPIA)",$_SESSION["restuiduser"]);
				//}
				
				
			}elseif($flag=="setpedprint"){
				$id_pedido=$_POST["id_pedido"];
				$ok=$gf->dataIn("UPDATE pedidos SET PRINTED='1' WHERE ID_PEDIDO='$id_pedido'");
				$ok=$gf->dataIn("UPDATE sillas_platos SET PRINTED=CANTIDAD WHERE ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido')");
				if($ok){
					echo $gf->utf8("Impreso...");
					$gf->log($_SESSION["restbus"],0,$id_pedido,"MARCADO COMO IMPRESO",$_SESSION["restuiduser"]);
				}
				
			}elseif($flag=="setfacprint"){
				$id_factura=$_POST["id_factura"];
				$ok=$gf->dataIn("UPDATE facturas SET PRINTED=1 WHERE ID_FACTURA='$id_factura'");
				if($ok){
					echo $gf->utf8("Impreso...");
				}

			
			}elseif($flag=="home"){
				

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
				echo $gf->utf8("<div class='row'><div class='col-md-12 flexbox' id='contMesas'>");
				$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.TIPO, M.NOMBRE, M.ID_GRUPO, P.DIRECCION, P.ID_PEDIDO, P.DESPACHADO, TIMESTAMPDIFF(MINUTE,P.APERTURA,NOW()) AS TIMEOPEN, P.APERTURA, COUNT(DISTINCT SI.ID_SILLA) AS SILLAS, COUNT(SP.ID_PLATO) AS TOTPLA, SUM(SP.LISTO) AS LISTO, SUM(SP.ENTREGADO) AS ENTREGADOS, P.CHEF, P.CAJA, P.DENOM, MS.NOMBRES AS TENDER, SUM(SP.PRECIO*SP.CANTIDAD) AS TOTAL_PEDIDO FROM mesas AS M LEFT JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CIERRE='0000-00-00 00:00:00' AND P.ID_SERVICIO='{$_SESSION["restservice"]}') LEFT JOIN sillas AS SI ON (P.ID_PEDIDO=SI.ID_PEDIDO) LEFT JOIN sillas_platos AS SP ON(SI.ID_SILLA=SP.ID_SILLA) LEFT JOIN platos AS PL ON(PL.ID_PLATO=SP.ID_PLATO) LEFT JOIN usuarios MS ON MS.ID_USUARIO=P.ID_TENDER WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND M.ESTADO='1' GROUP BY M.ID_MESA ORDER BY M.POS");
				$_SESSION["tlv"]="P";
				if(count($resultInt)>0){
					foreach($resultInt as $rowInt){
						$id_mesa=$rowInt["ID_MESA"];
						$nombre=$rowInt["NOMBRE"];
						$tipo=$rowInt["TIPO"];
						$def=1;
						$sillas=$rowInt["SILLAS"];
						$id_pedido=$rowInt["ID_PEDIDO"];
						$id_grupo=$rowInt["ID_GRUPO"];
						$chef=$rowInt["CHEF"];
						$denom=$rowInt["DENOM"];
						$direccion=$rowInt["DIRECCION"];
						$dispatch=$rowInt["DESPACHADO"];
						$totped=$rowInt["TOTPLA"];
						$totlisto=$rowInt["LISTO"];
						$totentrega=$rowInt["ENTREGADOS"];
						$total_pedido=$rowInt["TOTAL_PEDIDO"];
						if($total_pedido==""){
							$total_pedido=0;
						}
						$timeopen=$rowInt["TIMEOPEN"];
						$apertura=$rowInt["APERTURA"];
						$timerunit="Min";
						if($timeopen>60){
							$timerunit="Hrs";
							$timeopen=ceil($timeopen/60);
						}
					
						$tender_ar=explode(" ",$rowInt["TENDER"]);
						$tender=$tender_ar[0];
						if($totped>0){
							$perc=ceil(($totlisto/$totped)*100);
						}else{
							$perc=0;
						}
						
				
						if($id_pedido!="" && $tipo!="D"){
							$op=1;
							$classd='bg-red';
						$textcolor='#fff';
							if($chef!="0000-00-00 00:00:00"){
								$icon="fa-fire";
								$classd="bg-orange";
							}
					
							//if($_SESSION["restchairs"]==0){
							$chair="P#".$id_pedido;
							//}else{
								//$chair=$sillas." sillas";
							//}
							$avante=$timeopen." ".$timerunit;
							$onclik="onclick=\"loadMask('$sender?flag=opentable&id_mesa=$id_mesa&id_pedido=$id_pedido&t=$tipo')\" lnk-tsf='#mesa-$id_mesa' lnk-cont='contenidos-aux'";
							
						}else{
							$apertura=0;
							$op=0;
							$classd='bg-gray';
						$textcolor='#333';
							$addons="create_pedido&id_mesa=$id_mesa&t=$tipo";
							if($tipo=="M"){
								$icon="fa-qrcode";
							}elseif($tipo=="E"){
								$icon="fa-umbrella";
							}else{
								$icon="fa-motorcycle";
							}
							$chair="Libre";
							$avante="";
							
							$onclik="onclick=\"getDialog('$sender?flag=$addons','300','Crear\ Pedido')\"";
						}
						$moto="";
						if($tipo=="D"){
							$totals="";
							$elicon="<i class='fa $icon' id='mesa_icon_$id_mesa'></i>";
							$dv="D: $denom";
							$moto="<i class='fa fa-motorcycle' style='font-size:1.2em;'></i>";
						}else{
							$totals="$".@number_format($total_pedido,0);
							$mesan=str_replace("MESA ","",$nombre);
							$mesan=str_replace("mesa ","",$mesan);
							$elicon=$mesan;
							$dv="";
						}
						$progress="
						<div class='progress progress-xs' style='margin-bottom:5px; border-radius:4px; overflow:hidden;'>
							<div class='progress-bar progress-bar-warning' id='mesa_progress_$id_mesa' style='width: $perc%; transition: width 0.4s ease;'></div>
						</div>";
						
						echo $gf->utf8("
						<div class='col-lg-3 col-md-4 col-sm-6 col-xs-12 lasmesas grupi_$id_grupo data-filtrable' id='tbl_$id_mesa' t='$tipo' idm='$id_mesa' $onclik timer='$apertura' style='transition: all 0.3s ease;'>
						  <div class='small-box $classd' id='infoboxa_$id_mesa' style='cursor:pointer; transition: all 0.3s ease; border-radius:8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow:hidden;' onmouseover=\"this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)';\" onmouseout=\"this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';\">
							<div class='inner' style='color:$textcolor !important; padding:10px 12px;'>
							  <h3 style='margin:0; font-size:24px; color:$textcolor !important;'>$elicon</h3>
							  <p style='font-size:12px; margin:3px 0 0 0; color:$textcolor !important;'><strong>$nombre</strong> $moto</p>
							  <div style='margin:5px 0 3px 0; color:$textcolor !important;'>
								<span id='mesa_sillas_$id_mesa' style='font-size:11px;'>$chair</span>
								<span class='pull-right' style='font-size:13px; font-weight:bold;'>$totals</span>
							  </div>
							  $progress
							  <div style='font-size:10px; opacity:0.85; color:$textcolor !important; margin-top:2px;'>
								<i class='fa fa-clock-o'></i> <span id='pg_description_k_$id_mesa'>$avante</span>
								<span class='pull-right tdr' style='font-size:9px;'><i class='fa fa-user'></i> $tender</span>
							  </div>
							</div>
							<div class='icon'>
							  <i class='fa $icon' id='mesa_icon_$id_mesa' style='opacity:0.18; transition: opacity 0.3s ease;'></i>
							</div>
						  </div>
						</div>
						");
					}



					$rsReservation=$gf->dataSet("SELECT R.ID_RESERVA FROM reservas R JOIN usuarios U ON U.ID_USUARIO=R.ID_USUARIO WHERE DATE(R.FECHA)=CURDATE() AND R.ESTADO='0' AND U.ID_SITIO='{$_SESSION["restbus"]}' ORDER BY R.ID_RESERVA");

					if(count($rsReservation)>0){
						$nreser=count($rsReservation);
						echo $gf->utf8("
						
						
						<div class='col-lg-3 col-md-4 col-sm-6 col-xs-12 lasmesas grupi_reservas data-filtrable' tid='reservations' id='tbl_reservas' onclick=\"getDialog('$sender?flag=reservations')\">
						  <div class='small-box bg-aqua shadow link-cnv miniround material-ripple' id='infoboxa_reservas'>
							<span class='' style='font-size:28px;'><i class='fa fa-lock'></i></span>

							<div class='small-box-content'>
							  <h3 style='margin:0; font-size:24px; color:$textcolor !important;'>$elicon</h3><p style='font-size:12px; margin:3px 0 0 0; color:$textcolor !important;'><strong>Reservas  <small class='pull-right' style='font-size:11px;'></small></span>
							  <span class='info-box-number' id='mesa_sillas_reservas'>$nreser</span>

							  <span class='progress-description' id='pg_description_k_reservas'>
								Ver reservas
							  </span>
							</div>
						  </div>
						</div>
						");
					}



					echo $gf->utf8("</div></div>");
				}
				echo $gf->utf8("<input type='hidden' id='homeviewer' />");
			}elseif($flag=="reservations"){
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$rsReservation=$gf->dataSet("SELECT R.ID_RESERVA, R.NOMBRE, TIME(R.FECHA) AS FECHA, M.NOMBRE AS MESA FROM reservas R JOIN usuarios U ON U.ID_USUARIO=R.ID_USUARIO LEFT JOIN mesas M ON M.ID_MESA=R.ID_MESA WHERE DATE(R.FECHA)=CURDATE() AND R.ESTADO='0' AND U.ID_SITIO='{$_SESSION["restbus"]}' ORDER BY R.FECHA");
				echo $gf->utf8("<table class='table table-bordered'>
				<tr>
					<td>ID</td>
					<td>NOMBRE</td>
					<td>HORA</td>
					<td>MESA</td>
					<td></td>
				</tr>
					");
				if(count($rsReservation)>0){
					foreach ($rsReservation as $rwReser) {
						$id_res=$rwReser["ID_RESERVA"];
						$nm_res=$rwReser["NOMBRE"];
						$fe_res=$rwReser["FECHA"];
						$me_res=$rwReser["MESA"];
						if($me_res=="") $me_res="Sin asignar";
						echo $gf->utf8("
						<tr>
							<td>$id_res</td>
							<td>$nm_res</td>
							<td>".$gf->hora_verb($fe_res)."</td>
							<td>$me_res</td>
							<td><button class='btn btn-xs btn-primary' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=gest_reserva&id=$id_res&rnd=$rnd')\"><i class='fa fa-check'></i> Gestionar</button></td>
						</tr>
							");
					}

				}
				echo $gf->utf8("</table><hr />
				
				<button class='btn btn-danger pull-right' onclick=\"closeD('$rnd')\"> <i class='fa fa-check'></i> Cerrar</button>");

			}elseif($flag=="gest_reserva"){
				$id=$gf->cleanVar($_GET["id"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$rsReservation=$gf->dataSet("SELECT R.ID_RESERVA, R.NOMBRE, TIME(R.FECHA) AS FECHA, M.NOMBRE AS MESA FROM reservas R JOIN usuarios U ON U.ID_USUARIO=R.ID_USUARIO LEFT JOIN mesas M ON M.ID_MESA=R.ID_MESA WHERE R.ID_RESERVA='$id' AND U.ID_SITIO='{$_SESSION["restbus"]}' ORDER BY R.FECHA");
			
				if(count($rsReservation)>0){
					$rwReser=$rsReservation[0];
					$id_res=$rwReser["ID_RESERVA"];
					$nm_res=$rwReser["NOMBRE"];
					$fe_res=$rwReser["FECHA"];
					$me_res=$rwReser["MESA"];
					if($me_res=="") $me_res="Sin asignar";
					
					echo $gf->utf8("
					<h3>GESTIONAR RESERVA</h3> <br />
					<h5>$nm_res - $fe_res - Mesa: $me_res</h5><hr />
					<div class='row'>
						<div class='col-md-12 flexbox'>
							<button class='btn btn-success margined' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=cumpleres&id=$id&rnd=$rnd&val=1')\">CUMPLIDA</button>
							<button class='btn btn-danger margined' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=cumpleres&id=$id&rnd=$rnd&val=2')\">NO ASISTI&Oacute;</button>
							<button class='btn btn-warning margined' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=cumpleres&id=$id&rnd=$rnd&val=3')\">CANCEL&Oacute;</button>
						</div>
					</div>
					<hr />
					<button class='btn btn-danger pull-right' onclick=\"closeD('$rnd')\"> <i class='fa fa-remove'></i> Cancelar</button>
						");
				}else{
					echo "Reserva no encontrada";
				}

			}elseif($flag=="cumpleres"){
				$id=$gf->cleanVar($_GET["id"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$val=$gf->cleanVar($_GET["val"]);
				$rsReservation=$gf->dataSet("SELECT R.ID_RESERVA, R.NOMBRE, TIME(R.FECHA) AS FECHA, M.NOMBRE AS MESA, R.ID_MESA FROM reservas R JOIN usuarios U ON U.ID_USUARIO=R.ID_USUARIO LEFT JOIN mesas M ON M.ID_MESA=R.ID_MESA WHERE R.ID_RESERVA='$id' AND U.ID_SITIO='{$_SESSION["restbus"]}' ORDER BY R.FECHA");
			
				if(count($rsReservation)>0){
					$rwReser=$rsReservation[0];
					$id_res=$rwReser["ID_RESERVA"];
					$nm_res=$rwReser["NOMBRE"];
					$fe_res=$rwReser["FECHA"];
					$me_res=$rwReser["ID_MESA"];
					if($val==1){
						echo $gf->utf8("Se va a crear un pedido para la reserva No. $id_res de $nm_res, por favor selecciona la mesa");
						$rsMesas=$gf->dataSet("SELECT ID_MESA, NOMBRE FROM mesas WHERE ID_SITIO='{$_SESSION["restbus"]}' AND ID_MESA NOT IN(SELECT ID_MESA FROM pedidos WHERE CIERRE='0000-00-00' ORDER BY ID_MESA) AND TIPO<>'D' ORDER BY NOMBRE");
						echo $gf->utf8("<hr /><ul class='list-group'>");
						if(count($rsMesas)>0){
							foreach($rsMesas as $rwMesas){
								$id_mesa=$rwMesas["ID_MESA"];
								$nm_mesa=$rwMesas["NOMBRE"];
								$chacka=($id_mesa==$me_res) ? "checked='checked'" : "";
								echo $gf->utf8("<li class='list-group-item'><div class='checkbox'><label><input type='radio' $chacka name='id_mesa' value='$id_mesa' id='radres_$id_mesa' class='unival_cumpleres' required style='width:20px;height:20px;' /> <span style='font-size:20px;'>$nm_mesa</span></label></div></li>");
							}
							
						}
						echo $gf->utf8("</ul><hr />
						<button class='btn btn-warning pull-left' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=create_pedido_go&t=M&id_res=$id_res&rnd=$rnd','','5000','unival_cumpleres')\"> <i class='fa fa-bolt'></i> Crear pedido</button>
						<button class='btn btn-danger pull-right' onclick=\"closeD('$rnd')\"> <i class='fa fa-remove'></i> Cancelar</button>");
					}else{
						$msga=($val==2) ? "INCUMPLIDO" : "CANCELADO";
						$oka=$gf->dataIn("UPDATE reservas SET ESTADO='$val' WHERE ID_RESERVA='$id_res'");
						if($oka){
							echo $gf->utf8("Se ha $msga una reserva<hr />
							<button class='btn btn-danger pull-right' onclick=\"closeD('$rnd')\"> <i class='fa fa-remove'></i> Terminar</button>
							");
						}
					}

				}else{
					echo "Reserva no encontrada";
				}
				
				

			}elseif($flag=="create_pedido"){
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$t=$gf->cleanVar($_GET["t"]);
				$valida=$gf->dataSet("SELECT P.ID_PEDIDO, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER FROM pedidos P JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER WHERE P.ID_MESA='$id_mesa' AND P.CIERRE='0000-00-00 00:00:00' AND P.ID_SERVICIO='{$_SESSION["restservice"]}'");
				if(count($valida)==0 || $t=="D"){
					if($_SESSION["restchairs"] == 1){
						if($t!="D"){
							echo $gf->utf8("
							<div class='box box-default'><div class='box-body flexbox'>
							<h4>Vas a crear un nuevo pedido, ingresa el n&uacute;mero de sillas para continuar...<hr />
							<div class='input-group'>
								<span class='input-group-addon btn btn-app btn-danger' onclick=\"downNumber('nchairs')\"><i class='fa fa-arrow-down'></i></span>
								<input type='number' style='font-size:30px;height:60px;text-align:center;' class='form-control univalunichair' id='nchairs' name='nchairs'  min='1' max='20' value='1' />
								<span class='input-group-addon btn-app btn btn-success' onclick=\"upNumber('nchairs')\"><i class='fa fa-arrow-up'></i></span>
							</div>
							
							</div></div><hr />
							<button class='btn btn-success pull-left' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=create_pedido_go&id_mesa=$id_mesa&rnd=$rnd','','4000','univalunichair')\"> <i class='fa fa-check'></i> Crear Pedido</button>
							
							<button class='btn btn-danger pull-right' onclick=\"closeD('$rnd')\"> <i class='fa fa-trash'></i> Cancelar</button>
							
							");
						}else{
							echo $gf->utf8("
							<div class='box box-default'><div class='box-body flexbox'>
							<h4>Creando domicilio.<hr />
							
							
							</div></div><hr />
							<input type='hidden' id='callbackevaldlg' value=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=create_pedido_go&id_mesa=$id_mesa&rnd=$rnd&t=$t','','4000','univalunichair');closeD('$rnd')\" />
							
							
							");
						}
					}else{
						if($t!="D"){
							
							echo $gf->utf8("
							
							<input type='hidden' id='callbackevaldlg' value=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=create_pedido_go&id_mesa=$id_mesa&rnd=$rnd&t=$t','','4000','univalunichair')\" />
							
							");
						}else{
							
							$rsDom=$gf->dataSet("SELECT ID_PEDIDO FROM pedidos WHERE ID_MESA='$id_mesa' AND CHEF='0000-00-00 00:00:00' AND ID_TENDER='{$_SESSION["restuiduser"]}'");
							if(count($rsDom)==0){
								
								echo $gf->utf8("
								<div class='box box-default'><div class='box-body flexbox'>
								<h4>Creando domicilio.<hr />
								
								
								</div></div><hr />
								<input type='hidden' id='callbackevaldlg' value=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=create_pedido_go&id_mesa=$id_mesa&rnd=$rnd&t=$t','','4000','univalunichair')\" />
								");
							}else{
								$id_pedi=$rsDom[0]["ID_PEDIDO"];
								
								echo $gf->utf8("
								<input type='hidden' id='callbackevaldlg' lnk-tsf='#mesa-$id_mesa' lnk-cont='contenidos-aux' value=\"loadMask('$sender?flag=opentable&id_mesa=$id_mesa&id_pedido=$id_pedi&t=$t');closeD('$rnd')\" />
								
								
								");
								
							}
							
						}
					}
					
				}else{
					$tender=$valida[0]["TENDER"];
					echo $gf->utf8("Hay un pedido activo en esta mesa abierto por $tender, verifica si est&aacute;s en la mesa correcta<br />
					<hr />
					");
				}
				
			}elseif($flag=="create_pedido_go"){
				$id_mesa=(isset($_GET["id_mesa"])) ? $_GET["id_mesa"] : $_POST["id_mesa"];
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$t=$gf->cleanVar($_GET["t"]);
				
				$nchairs= (isset($_POST["nchairs"])) ? $_POST["nchairs"] : 1;
				$devuelta=isset($_POST["devuelta"]) ? $_POST["devuelta"] : 0;
				$ida="callbackeval";
				if($t!="D"){
					$direccion="";
				}else{
					$direccion="";
					$nchairs=1;
				}

				$res=$gf->dataInLast("INSERT INTO pedidos (ID_MESA,ID_SERVICIO,APERTURA,ID_TENDER,DIRECCION,DENOM) VALUES ('$id_mesa','{$_SESSION["restservice"]}',NOW(),'{$_SESSION["restuiduser"]}','$direccion','$devuelta')");
				if($res>0){
					$id_pedido=$res;
					for($i=1;$i<=$nchairs;$i++){
						$gf->dataIn("INSERT INTO sillas (ID_PEDIDO) VALUES ('$id_pedido')");
						$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"PEDIDO CREADO",$_SESSION["restuiduser"]);
					}
					if(isset($_POST["id_mesa"])){
						$id_res= $gf->cleanVar($_GET["id_res"]);
						$gf->dataIn("UPDATE reservas SET ESTADO='1', ID_PEDIDO='$id_pedido' WHERE ID_RESERVA='$id_res'");
						$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"PEDIDO CREADO DESDE RESERVA $id_res",$_SESSION["restuiduser"]);
					}
					$tender=$_SESSION["restuiduser"];
					echo $gf->utf8("
					<script>
						$(function(){
							sockEmitir('ocupar',{id_mesa:$id_mesa,id_pedido:$id_pedido,tender:$tender});
						});
					</script>
					<input type='hidden' id='callbackeval' lnk-tsf='#mesa-$id_mesa' lnk-cont='contenidos-aux' value=\"loadMask('$sender?flag=opentable&id_mesa=$id_mesa&id_pedido=$id_pedido&t=$t');closeD('$rnd')\" />
					");
				}else{
					echo "bad";
				}
			}elseif($flag=="infomesas"){
				$resultInt = $gf->dataSet("SELECT M.ID_MESA, P.ID_PEDIDO, COUNT(DISTINCT SI.ID_SILLA) AS SILLAS, COUNT( SP.ID_ITEM) AS TOTPLA, SUM(SP.LISTO) AS LISTO, P.CHEF, P.CAJA FROM mesas AS M LEFT JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CIERRE='0000-00-00 00:00:00' AND P.ID_SERVICIO='{$_SESSION["restservice"]}') LEFT JOIN sillas AS SI ON (P.ID_PEDIDO=SI.ID_PEDIDO) LEFT JOIN sillas_platos AS SP ON(SI.ID_SILLA=SP.ID_SILLA) LEFT JOIN platos AS PL ON(PL.ID_PLATO=SP.ID_PLATO AND PL.COCINA=1) WHERE M.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY M.ID_MESA");
				
				if(count($resultInt)>0){
					foreach($resultInt as $rowInt){
						$id_mesa=$rowInt["ID_MESA"];
						$sillas=$rowInt["SILLAS"];
						$id_pedido=$rowInt["ID_PEDIDO"];
						$chef=$rowInt["CHEF"];
						$caja=$rowInt["CAJA"];
						$totped=$rowInt["TOTPLA"];
						$totlisto=$rowInt["LISTO"];
						if($totped>0){
							$perc=ceil(($totlisto/$totped)*100);
						}else{
							$perc=0;
						}
						if($id_pedido=="") $id_pedido=0;
						if($id_pedido!="" && $id_pedido>0){
							$icon="fa-hourglass-2";
							$op=1;
							$classd='bg-red';
						$textcolor='#fff';
							if($chef!="0000-00-00 00:00:00"){
								$icon="fa-fire";
								$classd="bg-orange";
							}
							if($caja!="0000-00-00 00:00:00"){
								$icon="fa-dollar";
								$classd="bg-yellow";
							}
							$chair="$sillas sillas";

						}else{
							$icon="fa-qrcode";
							$op=0;
							$classd='bg-gray';
						$textcolor='#333';
							$chair="Libre";
						}
						echo $gf->utf8("$id_mesa|$chair|$id_pedido|$icon|$classd|$perc***");
					}
				}
			}elseif($flag=="comandas"){
				$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.TIPO, M.NOMBRE, M.COLOR, P.ID_PEDIDO, COUNT(S.ID_SILLA) AS SILLAS FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CHEF<>'0000-00-00 00:00:00') LEFT JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.PRINT='1' AND P.PRINTED='0' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
				$nped=0;
				if(count($resultInt)>0){
					$output="{\"data\":[";
					foreach($resultInt as $rowInt){
						$id_mesa=$rowInt["ID_MESA"];
						$nombre=$rowInt["NOMBRE"];
						$id_pedido=$rowInt["ID_PEDIDO"];
						$sillas=$rowInt["SILLAS"];
						$tipo=$rowInt["TIPO"];
						
						$output.="{\"ID_MESA\":\"".$id_mesa."\",\"ID_PEDIDO\":\"".$id_pedido."\",\"SILLAS\":\"".$sillas."\",\"TIPO\":\"".$tipo."\",\"PRUDUCTOS\":[";
						$nsi=0;
						$inisill=0;
						if($id_pedido>0){
							$nped++;
							$resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, SP.CANTIDAD, SP.LISTO, SP.ENTREGADO, P.NOMBRE, P.DESCRIPCION, GROUP_CONCAT(CONCAT(C.NOMBRE,'|',R.ESTADO,'|',RO.NOMBRE,'|',ROP.OPTIS) SEPARATOR '+*+') AS COMPOSITION FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN platos_composicion C ON SP.ID_PLATO=C.ID_PLATO LEFT JOIN sillas_platos_composicion R ON R.ID_ITEM=SP.ID_ITEM AND R.ID_RACION=C.ID_RACION LEFT JOIN racion_opciones RO ON R.ID_OPCION=RO.ID_OPCION LEFT JOIN (SELECT ID_RACION,GROUP_CONCAT(ID_OPCION) OPTIS FROM racion_opciones GROUP BY ID_RACION ORDER BY ID_RACION)ROP ON ROP.ID_RACION=C.ID_RACION WHERE S.ID_PEDIDO='$id_pedido' GROUP BY SP.ID_ITEM ORDER BY S.ID_SILLA");
							$plats="";
							$totpedido=0;
							$totlisto=0;
							if(count($resultChairs)>0){
								foreach($resultChairs as $rwChair){
									$id_item=$rwChair["ID_ITEM"];
									$id_silla=$rwChair["ID_SILLA"];
									$observacion=$rwChair["OBSERVACION"];
									$entregado=$rwChair["ENTREGADO"];
									$cantidad=$rwChair["CANTIDAD"];
									$nombre_plato=$rwChair["NOMBRE"];
									$descripcion=$rwChair["DESCRIPCION"];
									$composition=$rwChair["COMPOSITION"];
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
									if($comps=="") $comps="Completo";
									$listo=$rwChair["LISTO"];
									if($inisill!=$id_silla){
										$nsi++;
									}
									if($observacion!=""){
										$observacion=$observacion;
									}
									
									$totpedido++;
									
									
									if($comps!="Completo"){
										$comps=substr($comps,0,-1);
									}

									$output.="{\"ID_ITEM\":\"".$id_item."\",\"PLATO\":\"".$nombre_plato."\",\"SILLA\":\"".$nsi."\",\"CANTIDAD\":\"".$cantidad."\",\"COMPONENTES\":\"".$comps."\",\"OBSERVACION\":\"".$observacion."\"},";
									$inisill=$id_silla;
								}
							}
							$output=substr($output,0,-1);
							
						}
						
						$output.="]},";
					}
					$output=substr($output,0,-1);
					$output.="]}";
				}else{
					$output="{\"data\":[]}";
				}
				echo $gf->utf8($output);
			}elseif($flag=="print_mesa"){

				$id_pedido= $gf->cleanVar($_GET["idp"]);
				$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.TIPO, M.NOMBRE, M.COLOR, P.ID_PEDIDO, COUNT(S.ID_SILLA) AS SILLAS FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CHEF<>'0000-00-00 00:00:00' AND P.CAJA='0000-00-00 00:00:00') LEFT JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_PEDIDO='$id_pedido' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
				$nped=0;
				if(count($resultInt)>0){
					echo $gf->utf8("<div class='row'>");
					foreach($resultInt as $rowInt){
						$id_mesa=$rowInt["ID_MESA"];
						$nombre=$rowInt["NOMBRE"];
						$id_pedido=$rowInt["ID_PEDIDO"];
						$sillas=$rowInt["SILLAS"];
						$tipo=$rowInt["TIPO"];
						

						$nsi=0;
						$inisill=0;
						if($id_pedido>0){
							$nped++;
							$resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, SP.CANTIDAD, SP.LISTO, SP.ENTREGADO, P.NOMBRE, P.DESCRIPCION, GROUP_CONCAT(CONCAT(C.NOMBRE,'|',R.ESTADO,'|',RO.NOMBRE,'|',ROP.OPTIS) SEPARATOR '+*+') AS COMPOSITION FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN platos_composicion C ON SP.ID_PLATO=C.ID_PLATO LEFT JOIN sillas_platos_composicion R ON R.ID_ITEM=SP.ID_ITEM AND R.ID_RACION=C.ID_RACION LEFT JOIN racion_opciones RO ON R.ID_OPCION=RO.ID_OPCION LEFT JOIN (SELECT ID_RACION,GROUP_CONCAT(ID_OPCION) OPTIS FROM racion_opciones GROUP BY ID_RACION ORDER BY ID_RACION)ROP ON ROP.ID_RACION=C.ID_RACION WHERE S.ID_PEDIDO='$id_pedido' GROUP BY SP.ID_ITEM ORDER BY S.ID_SILLA");
							$plats="";
							$totpedido=0;
							$totlisto=0;
							if(count($resultChairs)>0){
								foreach($resultChairs as $rwChair){
									
									$id_item=$rwChair["ID_ITEM"];
									$id_silla=$rwChair["ID_SILLA"];
									$observacion=$rwChair["OBSERVACION"];
									$entregado=$rwChair["ENTREGADO"];
									$cantidad=$rwChair["CANTIDAD"];
									$nombre_plato=$rwChair["NOMBRE"];
									$descripcion=$rwChair["DESCRIPCION"];
									$composition=$rwChair["COMPOSITION"];
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
									if($comps=="") $comps="Completo";
									$listo=$rwChair["LISTO"];
									if($inisill!=$id_silla){
										$nsi++;
									}
									if($observacion!=""){
										$observacion="<i class='fa fa-user'></i>".$observacion;
									}
									$plats.="
									<li class='list-group-item clearfix'>$nombre_plato";
									$totpedido++;
									if($listo==1){
										$chacka="checked='checked'";
										$totlisto++;
									}else{
										$chacka="";
									}
									if($entregado==0){
										$plats.="
											<input type='checkbox' $chacka id='cook_$id_item' name='cook_$id_item' class='icheck unicook_$id_item pull-right' value='1' style='width:28px !important;height:28px !important;' onclick=\"cargaHTMLvars('state_proceso','$sender?flag=plato_listo&id_item=$id_item&t=$tipo','','5000','unicook_$id_item')\" />";
									}else{
										$plats.="<i class='fa fa-cutlery pull-right'></i>";
									}
									if($comps!="Completo"){
										$comps=substr($comps,0,-1);
									}
									$plats.="(S.$nsi) <span class='pull-left badge bg-blue'>$cantidad</span> <small><b>$comps</b><br />$observacion</small> </li>";
									$inisill=$id_silla;
								}
							}
							if($totpedido>0){
								$perca=ceil(($totlisto/$totpedido)*100);
							}else{
								$perca=0;
							}
							$perdiez=round($perca,-1);
							$color=$arcols[$perdiez];
							echo $gf->utf8("
							<div class='col-lg-3 col-md-4'>
							  <div class='box box-widget widget-user-2 shadow'>
								<div class='widget-user-header bg-grey'>
								
									
								<div class='row'><div class='col-md-8'><h3 class='widget-user-username'>$nombre</h3>
								  <h5 class='widget-user-desc'>Pedido No. $id_pedido  ($sillas sillas)</h5>
								</div><div class='col-lg-3 col-md-4'>
								  <input type='text' class='knob pull-right' value='$perca' data-width='70' data-height='70' data-fgColor='$color'>
								 </div>
								 </div>
								</div>
								<div class='box-footer no-padding'>
									<ul class='list-group'>
										$plats
									</ul>
								</div>
							  </div>
							</div>");
						}
					}
				}
				echo $gf->utf8("</div>
				");
				$gf->dataIn("UPDATE pedidos SET PRINTED='1' WHERE ID_PEDIDO='$id_pedido'");
			}elseif($flag=="add_chair"){
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				if($gf->dataIn("INSERT INTO sillas (ID_PEDIDO) VALUES ('$id_pedido')")){
					echo $gf->utf8("<input type='hidden' id='callbackeval' value=\"cargaHTMLvars('contenidos-aux','mviews.php?flag=opentable&id_mesa=$id_mesa&id_pedido=$id_pedido');closeD('$rnd')\" />");
					$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"SILLA AGREGADA ",$_SESSION["restuiduser"]);
				}else{
					echo "bad";
				}
			}elseif($flag=="del_chair"){
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$rsCursi=$gf->dataSet("SELECT S.ID_SILLA, COUNT(SP.ID_ITEM) AS PLTS FROM sillas S LEFT JOIN sillas_platos SP ON SP.ID_SILLA=S.ID_SILLA WHERE S.ID_PEDIDO=:pedido GROUP BY S.ID_SILLA HAVING PLTS=0 ORDER BY S.ID_SILLA DESC",array(":pedido"=>$id_pedido));
				if(count($rsCursi)>0){
					$id_borra=$rsCursi[0]["ID_SILLA"];
					if($gf->dataIn("DELETE FROM sillas WHERE ID_SILLA='$id_borra' AND ID_PEDIDO='$id_pedido'")){
						echo $gf->utf8("<input type='hidden' id='callbackeval' value=\"cargaHTMLvars('contenidos-aux','mviews.php?flag=opentable&id_mesa=$id_mesa&id_pedido=$id_pedido');closeD('$rnd')\" />");
						$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"SILLA ELIMINADA",$_SESSION["restuiduser"]);
					}else{
						echo "No se pudo borrar la silla";
					}
				}else{
					echo $gf->utf8("<input type='hidden' id='callbackeval' value=\"msgBox('No hay sillas para borrar, las sillas con pedido no se pueden borrar');closeD('$rnd')\" />");
				}
			
			}elseif($flag=="confirm_pedido"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$tipo=$gf->cleanVar($_GET["tipo"]);
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$t=$gf->cleanVar($_GET["t"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				if($tipo=="chef"){
					$msg="El pedido ser&aacute; enviado a preparaci&oacute;n";
				}else{
					if($t!="D"){
						$msg="El pedido ser&aacute; enviado a Caja para cobro, Continuar?";
					}else{
						$msg="El pedido ser&aacute; despachado y enviado a Caja para cobro, Continuar?";
					}
				}
				
				echo $gf->utf8("<h4 class='flexbox'>$msg</h4><br />
				<label for='printcoms' class='flexbox-centro' ><input style='width:23px;height:23px;' id='printcoms' name='printcoms' checked='checked' type='checkbox' class='univilcom_' />Imprimir Comanda</label>
				<br /><br />
				
				<button class='btn btn-danger pull-left' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=confirm_pedido_go&id_pedido=$id_pedido&rnd=$rnd&tipo=$tipo&t=$t&id_mesa=$id_mesa','','4000','univilcom_')\"> <i class='fa fa-check'></i> Confirmar</button>
				
				<button class='btn btn-info pull-right' onclick=\"closeD('$rnd')\"> <i class='fa fa-remove'></i> Cancelar</button>
				");
				
				
			}elseif($flag=="confirm_pedido_go"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$tipo=$gf->cleanVar($_GET["tipo"]);
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$t=$gf->cleanVar($_GET["t"]);
				$comanda_go=$_POST["printcoms"];

				if($tipo=="chef"){
					$camp="CHEF";
					$addon="";
					if($_SESSION["restecomanda"]==0){
						if($comanda_go==1){
							$gf->dataIn("UPDATE pedidos SET PRINTED='0' WHERE ID_PEDIDO='$id_pedido'");
						}else{
							$gf->dataIn("UPDATE pedidos SET PRINTED='1' WHERE ID_PEDIDO='$id_pedido'");
						}
					}else{
						$gf->dataIn("UPDATE pedidos SET PRINTED='1' WHERE ID_PEDIDO='$id_pedido'");
						$gf->dataIn("UPDATE telecomanda SET ESTADO=1, FECHA=NOW() WHERE ID_PEDIDO='$id_pedido' AND ESTADO=0");
					}
					$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"PEDIDO ENVIADO AL CHEF",$_SESSION["restuiduser"]);
				}else{
					$camp="CAJA";
					if($t=="D"){
						$addon=",DESPACHADO=1";
					}else{
						$addon="";
					}
				}
				if($gf->dataIn("UPDATE pedidos SET $camp=NOW()$addon WHERE ID_PEDIDO='$id_pedido'")){
					if($_SESSION["restautogest"]==1){
						//$gf->dataIn("UPDATE sillas_platos SET ENTREGADO=CANTIDAD, FENTREGA=NOW() WHERE ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido' ORDER BY ID_SILLA)");
					}
					if($_SESSION["restecomanda"]==0){
						$gf->dataIn("UPDATE sillas_platos SET LISTO=1 WHERE ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido' ORDER BY ID_SILLA)");
						if($comanda_go==0){
							$gf->dataIn("UPDATE sillas_platos SET PRINTED=CANTIDAD WHERE ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido' ORDER BY ID_SILLA)");
						}
					}else{
						$gf->dataIn("UPDATE sillas_platos SET PRINTED=CANTIDAD WHERE ID_SILLA IN(SELECT ID_SILLA FROM sillas WHERE ID_PEDIDO='$id_pedido' ORDER BY ID_SILLA)");
					}
					
					$comanda=($_SESSION["restecomanda"]==1) ? 0 : 1;

					if($comanda_go==0) $comanda=0;

					if($_SESSION["restprofile"]=="M"){
						$home="#home";
					}else{
						$home="#ver_pedidos_in";
					}

					echo $gf->utf8("
					<script>
					$(function(){
						window.location.hash='$home';
						sockEmitir('enviar_chef',{id_mesa:$id_mesa,id_pedido:$id_pedido,comanda:$comanda});
					});
					</script>
					");
					
					echo $gf->utf8("
					<input type='hidden' id='callbackeval' value=\"closeD('$rnd')\" />
					
					
					");

				}else{
					echo "Hubo un error al enviar el pedido";
				}

			}elseif($flag=="cobrar"){
				$id_pedido=$_POST["id_pedido"];
				$dcto=$_POST["dcto"];
				//$valor=$_POST["valor"];
				if(mysqli_query($link,"UPDATE pedidos SET PAGO='1', DCTO='$dcto' WHERE ID_PEDIDO='$id_pedido'")){
					echo "1";
				}else{
					echo "0";
				}
			
			
			}elseif($flag=="cancel_pedido"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				echo $gf->utf8("<h3>Se va a borrar el pedido, se eliminar&aacute;n todos sus componentes, continuar?<br /><br />
				<button class='btn btn-danger pull-left' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=cancel_pedido_go&id_pedido=$id_pedido&rnd=$rnd&id_mesa=$id_mesa','','4000')\"> <i class='fa fa-check'></i> Si</button>
				
				<button class='btn btn-info pull-right' onclick=\"closeD('$rnd')\"> <i class='fa fa-cancel'></i> No</button>
				
				");
			}elseif($flag=="cancel_pedido_go"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				if($gf->dataIn("DELETE FROM sillas WHERE ID_PEDIDO='$id_pedido'")){
					if($gf->dataIn("DELETE FROM pedidos WHERE ID_PEDIDO='$id_pedido'")){
						$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"PEDIDO CANCELADO",$_SESSION["restuiduser"]);
						echo $gf->utf8("
						<script>
						$(function(){
							sockEmitir('liberar',{id_mesa:$id_mesa});
						});
						</script>
						
						<input type='hidden' id='callbackeval' value=\"javascript:history.back();closeD('$rnd')\" />");
					}else{
						echo "echo Error al borrar el pedido";
					}
				}else{
					echo "echo Error al borrar el pedido";
				}
		
			}elseif($flag=="openchair"){
				$id_silla=$gf->cleanVar($_GET["id_silla"]);
				$nch=$gf->cleanVar($_GET["nch"]);
				$t=$gf->cleanVar($_GET["t"]);
				$resultChair = $gf->dataSet("SELECT S.ID_SILLA, S.ID_PEDIDO, S.COLOR, S.GENDER, PE.ID_MESA, PE.CHEF, PE.CAJA, S.OBSERVACION, P.ID_ITEM, P.ID_PLATO, P.CANTIDAD, P.OBSERVACION AS OBSERPLAT, PL.NOMBRE AS PLATO, P.LISTO, P.ENTREGADO, PC.NOMBRE AS CATEGORIA, PC.ICONO, P.PRINTED, PE.ID_TENDER, P.TIPO_PLATO FROM pedidos AS PE LEFT JOIN sillas AS S ON(PE.ID_PEDIDO=S.ID_PEDIDO) LEFT JOIN sillas_platos AS P ON(S.ID_SILLA=P.ID_SILLA) LEFT JOIN platos AS PL ON(P.ID_PLATO=PL.ID_PLATO) LEFT JOIN platos_categorias AS PC ON(PL.ID_CATEGORIA=PC.ID_CATEGORIA) WHERE S.ID_SILLA='$id_silla'");
				
				if(count($resultChair)>0){
					$rowChair=$resultChair[0];
					$id_chair=$rowChair["ID_SILLA"];
					$observacion=$rowChair["OBSERVACION"];
					$id_pedido=$rowChair["ID_PEDIDO"];
					$color=$rowChair["COLOR"];
					$gender=$rowChair["GENDER"];
					$id_tender=$rowChair["ID_TENDER"];
					
					
					if($gender=="M"){
						$icogender="fa fa-male";
					}else{
						$icogender="fa fa-female";
					}
					$id_mesa=$rowChair["ID_MESA"];
					$pedestado=$rowChair["CHEF"];
					$pedecaja=$rowChair["CAJA"];
					if($pedestado=="0000-00-00 00:00:00"){
						$titca="CANT.";
					}else{
						$titca="ENTREGADO";
					}
					
					if($pedecaja=="0000-00-00 00:00:00"){
						$active=1;
					}else{
						$active=0;
					}
					echo $gf->utf8("<div id='ch_$id_chair' idch='$id_chair' class='box box-danger'><div class='box-header'> <i class='$icogender' style='color:$color;'></i> <b>PEDIDO SILLA $nch</b> <button class='btn btn-warning btn-circle' style='position:fixed;bottom:60px;left:10px;width:45px;height:45px;font-size:20px;border-radius:25px;' onclick=\"javascript:history.back()\" id='elbuttonkr'><i class='fa fa-check'></i></button>
					");
					//if($active==1){
					echo $gf->utf8("
						<button style='margin-right:5px;' class='btn btn-sm btn-success pull-right' onclick=\"getDialog('$sender?flag=additemdlg&id_silla=$id_chair&id_mesa=$id_mesa&id_pedido=$id_pedido&nch=$nch&ait=$active&t=$t','500','Adicionar')\"><i class='fa fa-cutlery'></i> Agregar Producto</button>
						");
					//}
					echo $gf->utf8("
					</div>
					<div class='box-body'>
					<table class='latabla table table-striped' width='100%'><tr ><td></td><td>ITEM</td><td>$titca</td><td>QUITAR</td></tr>");
					foreach($resultChair as $rowChair){
						$icono=$rowChair["ICONO"];
						$id_item=$rowChair["ID_ITEM"];
						$cantidad=$rowChair["CANTIDAD"];
						$plato=$rowChair["PLATO"];
						$listo=$rowChair["LISTO"];
						$entregado=$rowChair["ENTREGADO"];
						$obserplat=$rowChair["OBSERPLAT"];
						$printedd=$rowChair["PRINTED"];
						$tipoplato=$rowChair["TIPO_PLATO"];
						if($tipoplato==1){
							$tipoplaicon="<i class='fa fa-spoon orange'></i>";
						}elseif($tipoplato==2){
							$tipoplaicon="<i class='fa fa-cutlery red'></i>";
						}else{
							$tipoplaicon="";
						}
						$categoria=$rowChair["CATEGORIA"];
						if($id_item!=""){
							$nprod++;
							if($pedestado!="0000-00-00 00:00:00"){
								$cantii="($cantidad)";
							}else{
								$cantii="";
							}
							echo $gf->utf8("<tr class='ui-widget-content ui-corner-all' id='tritem_$id_item'><td><b>$nprod</b> $tipoplaicon</td><td><span style='font-size:17px;'> $plato</span><br/><small>$obserplat</small></td>
							<td align='center'>");
							if((($_SESSION["restuiduser"]==$id_tender || $_SESSION["restmancomun"]>=$mipermiso) && $_SESSION["resttendercants"]>=$mipermiso) || $printedd==0){
								echo $gf->utf8("
								<div class='input-group'>
									<span class='input-group-addon btn btn-lg btn-danger mimibutton' onclick=\"downNumber('cant_$id_item')\"><i class='fa fa-arrow-down'></i></span>
									<input type='number' onchange=\"cargaHTMLvars('state_proceso','$sender?flag=edit_cant&id_pedido=$id_pedido&id_mesa=$id_mesa&t=$t&item=$id_item&val='+this.value)\" style='font-size:23px;height:40px;text-align:center;min-width:45px;' value='$cantidad' id='cant_$id_item' class='form-control univalunichair' id='nchairs' name='nchairs'  min='1' max='20' />
									<span class='input-group-addon btn-lg btn btn-success mimibutton' onclick=\"upNumber('cant_$id_item')\"><i class='fa fa-arrow-up'></i></span>
								</div>
								");
							}else{
								if($listo==1){
									
									if($entregado==1){
										$checka="checked='checked'";
									}else{
										$checka="";
									}
									echo $gf->utf8("
										<input type='checkbox' $checka name='chk_entrega_$id_item' onclick=\"cargaHTMLvars('state_proceso','$sender?flag=entregado&item=$id_item','','5000','unival_gocheck_$id_item')\" value='1' style='width:36px;height:36px;' class='icheck unival_gocheck_$id_item' />
									");
								}else{
									echo $gf->utf8("<i class='fa fa-fire' title='En proceso'></i>");
								}
								echo $gf->utf8($cantii);
								
							}
							echo $gf->utf8("
							</td><td>");
							if(($_SESSION["restuiduser"]==$id_tender || $_SESSION["restmancomun"]>=$mipermiso) && ($_SESSION["resttendercants"]>=$mipermiso || $printedd==0)){
								echo $gf->utf8("
								<button class='btn btn-warning' onclick=\"getDialog('$sender?flag=config_plat&id_item=$id_item','600','Editar','','','reloaHash()')\"><i class='fa fa-edit'></i></button>
								");
							}else{
								echo $gf->utf8("
								<button class='btn btn-default disabled'><i class='fa fa-edit'></i></button>
								");
							}
							if(($_SESSION["restuiduser"]==$id_tender || $_SESSION["restmancomun"]>=$mipermiso) && ($_SESSION["resttendercants"]>=$mipermiso || $printedd==0)){
								echo $gf->utf8("<button class='btn btn-danger' onclick=\"goErase('sillas_platos','ID_ITEM','$id_item','tritem_$id_item','1')\"><i class='fa fa-trash'></i></button>");
							}else{
								echo $gf->utf8("<button class='btn btn-default disabled'><i class='fa fa-trash'></i></button>");
								
							}
							echo $gf->utf8("
							</td></tr>");
						}
					
					}
					echo $gf->utf8("<tr><td colspan='4' align='center'>Observaciones: <textarea onchange=\"cargaHTMLvars('state_proceso','$sender?flag=up_observa&id_silla=$id_chair','','5000','unival_observa')\" name='obs_$id_chair' class='form-control unival_observa'>$observacion</textarea><br />
					<button class='btn btn-sm btn-danger pull-right center' onclick=\"javascript:history.back()\"><i class='fa fa-check'></i> Listo</button>
					
					</td></tr></table>
					
					</div>
					
					<script>
						$(function(){
							$(window).scroll(strolin);
							strolinch()
						})
						
						function strolinch(){
							var a=$(window).scrollTop();
							var h=$(window).height();
							$('#elbuttonkr').css('top',(Math.round(a)+h-70)+'px');
						}
					</script>
					</div>
					");
				}
			

			}elseif($flag=="up_devuelta"){
				$id_ped=$gf->cleanVar($_GET["id_ped"]);
				$val=$_POST["devuelta_$id_ped"];
				$ok=$gf->dataIn("UPDATE pedidos SET DENOM='$val' WHERE ID_PEDIDO='$id_ped'");
			}elseif($flag=="up_paga"){
				$id_ped=$gf->cleanVar($_GET["id_ped"]);
				$val=$_POST["paga_$id_ped"];
				$ok=$gf->dataIn("UPDATE pedidos SET PAGADO='$val' WHERE ID_PEDIDO='$id_ped'");
			}elseif($flag=="up_direc"){
				$id_ped=$gf->cleanVar($_GET["id_ped"]);
				$val=$_POST["direc_$id_ped"];
				$ok=$gf->dataIn("UPDATE pedidos SET DIRECCION='$val' WHERE ID_PEDIDO='$id_ped'");
			}elseif($flag=="up_observa"){
				$id_silla=$gf->cleanVar($_GET["id_silla"]);
				$val=$_POST["obs_$id_silla"];
				$ok=$gf->dataIn("UPDATE sillas SET OBSERVACION='$val' WHERE ID_SILLA='$id_silla'");
			}elseif($flag=="entregado"){
				$item=$gf->cleanVar($_GET["item"]);
				$val=$_POST["chk_entrega_$item"];
				if($val!=1){
					$val=0;
				}else{
					$val="CANTIDAD";
				}
				$ok=$gf->dataIn("UPDATE sillas_platos SET ENTREGADO=$val, FENTREGA=NOW() WHERE ID_ITEM='$item'");
			}elseif($flag=="edit_cant"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$t=$gf->cleanVar($_GET["t"]);
				$item=$gf->cleanVar($_GET["item"]);
				$val=$gf->cleanVar($_GET["val"]);
				$rsCuca=$gf->dataSet("SELECT CANTIDAD FROM sillas_platos WHERE ID_ITEM='$item'");
				$curca=$rsCuca[0]["CANTIDAD"];
				$ok=$gf->dataIn("UPDATE sillas_platos SET CANTIDAD='$val', LISTO=0 WHERE ID_ITEM='$item'");
				if($curca>$val){
					$rsSinco=$gf->dataSet("SELECT ID_MOVIMIENTO, CANTIDAD FROM telecomanda WHERE ID_ITEM='$item' AND ESTADO=0");
					if(count($rsSinco)>0){
						$curIt=$rsSinco[0]["CANTIDAD"];
						$id_mov=$rsSinco[0]["ID_MOVIMIENTO"];
						if($curIt>=2){
							$gf->dataIn("UPDATE telecomanda SET CANTIDAD=CANTIDAD-1 WHERE ID_MOVIMIENTO='$id_mov'");
						}elseif($curIt==1){
							$gf->dataIn("DELETE FROM telecomanda WHERE ID_MOVIMIENTO='$id_mov'");
						}
					}else{
						$gf->dataIn("INSERT INTO telecomanda (ID_ITEM,ID_PEDIDO,FECHA,CANTIDAD,ESTADO) VALUES ('$item','$id_pedido',NOW(),'-1','0')");
					}
				}else{
					$diff=$val-$curca;
					$rsSinco=$gf->dataSet("SELECT ID_MOVIMIENTO, CANTIDAD FROM telecomanda WHERE ID_ITEM='$item' AND ESTADO=0");
					if(count($rsSinco)>0){
						$curIt=$rsSinco[0]["CANTIDAD"];
						$id_mov=$rsSinco[0]["ID_MOVIMIENTO"];
						$gf->dataIn("UPDATE telecomanda SET CANTIDAD=CANTIDAD+$diff WHERE ID_MOVIMIENTO='$id_mov'");
					}else{
						$gf->dataIn("INSERT INTO telecomanda (ID_ITEM,ID_PEDIDO,FECHA,CANTIDAD,ESTADO) VALUES ('$item','$id_pedido',NOW(),'$diff','0')");
					}
				}
				$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"CANTIDAD ITEM No. $item CAMBIADA A $val",$_SESSION["restuiduser"]);
				$ok=$gf->dataIn("UPDATE pedidos SET DESPACHADO='0' WHERE ID_PEDIDO='$id_pedido'");
				echo $gf->utf8("
				<script>
					$(function(){
						sockEmitir('up_mesa',{id_mesa:$id_mesa,id_pedido:$id_pedido,tipo:'$t'});
						$('#chefin_$id_pedido').removeClass('hidden');
						$('#checobra_$id_pedido').addClass('hidden');
					});
				</script>
				");

			}elseif($flag=="config_plat"){
				$id_item=$gf->cleanVar($_GET["id_item"]);
				$id_itemgo=$gf->cleanVar($_GET["id_item"]);
				$tiplats=array("0"=>"","1"=>"ENTRADA","2"=>"PLATO FUERTE","3"=>"NO APLICA");
				$rsOk=$gf->dataSet("SELECT TIPO_PLATO FROM sillas_platos WHERE ID_ITEM='$id_item'");
				$curobserv=$rsOk[0]["OBSERPLAT"];
				$tipo_plato=$rsOk[0]["TIPO_PLATO"];
				$composite=$gf->dataSet("SELECT R.ID_REL,R.ID_ITEM, R.ID_RACION, R.ESTADO, R.ID_OPCION, C.NOMBRE, C.DESCRIPCION, SP.OBSERVACION AS OBSERPLAT, SP.TIPO_PLATO FROM sillas_platos SP JOIN platos_composicion C ON SP.ID_PLATO=C.ID_PLATO JOIN sillas_platos_composicion R ON R.ID_ITEM=SP.ID_ITEM AND R.ID_RACION=C.ID_RACION WHERE SP.ID_ITEM='$id_item' ORDER BY C.NOMBRE");
				echo $gf->utf8("
				<div class='box box-warning'>
					<div class='box-header'>COMPOSICION DEL PLATO</div>
					<div class='box-body'>
					<table class='table table-striped'>");
					if(count($composite)>0){
						echo $gf->utf8("<tr><td>RACI&Oacute;N</td><td>OPCIONES</td><td>SERVIR</td></tr>");
						$id_rel=$composite[0]["ID_ITEM"];
						foreach($composite as $rw){
							$id_item=$rw["ID_REL"];
							$id_racion=$rw["ID_RACION"];
							$estado=$rw["ESTADO"];
							$nombre=$rw["NOMBRE"];
							$curoption=$rw["ID_OPCION"];
							$descripcion=$rw["DESCRIPCION"];
							
							if($estado==1){
								$ckecka="checked='checked'";
							}else{
								$ckecka="";
							}
							$opts=$gf->dataSet("SELECT ID_OPCION, NOMBRE, DESCRIPCION FROM racion_opciones WHERE ID_RACION='$id_racion' AND ESTADO=1 ORDER BY NOMBRE");
							echo $gf->utf8("<tr><td>$nombre</td><td>
							
							");
							if(count($opts)>0){
								if(count($opts)>1){
									echo $gf->utf8("<select id='select_rac_option' onchange=\"cargaHTMLvars('state_proceso','$sender?flag=choption&id_rel=$id_item&val='+this.value)\" class='form-control unival_setopt_$id_racion'>");
									foreach($opts as $rwopt){
										$id_opt=$rwopt["ID_OPCION"];
										$nm_opt=$rwopt["NOMBRE"];
										if($curoption==$id_opt){
											echo $gf->utf8("<option value='$id_opt' selected='selected'>$nm_opt</option>");
										}else{
											echo $gf->utf8("<option value='$id_opt'>$nm_opt</option>");
										}
									}
									echo $gf->utf8("</select>");
								}else{
									$val=$opts[0]["ID_OPCION"];
									$nm_opt=$opts[0]["NOMBRE"];
									$gf->dataIn("UPDATE sillas_platos_composicion SET ID_OPCION='$val' WHERE ID_REL='$id_item'");
									echo $gf->utf8($nm_opt);
								}
							}
							echo $gf->utf8("
							</td><td><input id='composite_$id_item' name='composite_$id_item' type='checkbox' $ckecka class='icheck unival_kkkomposite_$id_item pull-right' onclick=\"cargaHTMLvars('state_proceso','$sender?flag=on_composition&id_rel=$id_item','','5000','unival_kkkomposite_$id_item')\" style='width:36px !important;height:36px !important;' /></td></tr>");
						}
					}
					$clase_1="btn-default";
					$clase_2="btn-default";
					$clase_3="btn-default";
					switch($tipo_plato){
						case 1:
							$clase_1="btn-warning";
							break;
						case 2:
							$clase_2="btn-warning";
							break;
						case 3:
							$clase_3="btn-warning";
							break;
						case 0:
							$clase_3="btn-warning";
							break;
					}
				echo $gf->utf8("
					<tr><td colspan='3' align='center'>
						<button class='btntipoes btn btn-sm $clase_1' onclick=\"cargaHTMLvars('state_proceso','$sender?flag=set_tp&id_rel=$id_itemgo&val=1');setTipoPla(event)\">Entrada</button>
						<button class='btntipoes btn btn-sm $clase_2' onclick=\"cargaHTMLvars('state_proceso','$sender?flag=set_tp&id_rel=$id_itemgo&val=2');setTipoPla(event)\">Plato Fuerte</button>
						<button class='btntipoes btn btn-sm $clase_3' onclick=\"cargaHTMLvars('state_proceso','$sender?flag=set_tp&id_rel=$id_itemgo&val=3');setTipoPla(event)\">N/A</button>
					</td></tr>

					<tr><td colspan='3'>OBSERVACI&Oacute;N ADICIONAL<br />
					<textarea id='observplato' name='obserplato' class='form-control unival_goobs' onchange=\"cargaHTMLvars('state_proceso','$sender?flag=observitem_go&id_item=$id_itemgo','','5000','unival_goobs')\">$curobserv</textarea></td></tr>

					</table>
					<script>
					
						function setTipoPla(e){
							$('.btntipoes').removeClass('btn-warning');
							$('.btntipoes').addClass('btn-default');
							$(e.target).addClass('btn-warning');
						}
					</script>
					</div>
				</div>");
			}elseif($flag=="printpre"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				echo $gf->utf8("
				Enviando.....
				<script>
					$(function(){
						setTimeout(function(){
							closeD('$rnd');
							sockEmitir('precuenta',{id_pedido:$id_pedido});
							
						},2000);
					});
				</script>");
				$gf->log($_SESSION["restbus"],0,$id_pedido,"IMPRIMIR PRECUENTA",$_SESSION["restuiduser"]);
				
			}elseif($flag=="set_tp"){
				$id_rel=$gf->cleanVar($_GET["id_rel"]);
				$val=$gf->cleanVar($_GET["val"]);
				$gf->dataIn("UPDATE sillas_platos SET TIPO_PLATO='$val' WHERE ID_ITEM='$id_rel'");
				echo "UPDATE sillas_platos SET TIPO_PLATO='$val' WHERE ID_ITEM='$id_rel'";
			}elseif($flag=="on_composition"){
				$id_rel=$gf->cleanVar($_GET["id_rel"]);
				$val=$_POST["composite_$id_rel"];
				$gf->dataIn("UPDATE sillas_platos_composicion SET ESTADO='$val' WHERE ID_REL='$id_rel'");
				
			}elseif($flag=="choption"){
				$id_rel=$gf->cleanVar($_GET["id_rel"]);
				$val=$gf->cleanVar($_GET["val"]);
				$gf->dataIn("UPDATE sillas_platos_composicion SET ID_OPCION='$val' WHERE ID_REL='$id_rel'");
			}elseif($flag=="go_in_plats"){
				
				$id_silla=$gf->cleanVar($_GET["id_silla"]);
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$rsExistentes=$gf->dataSet("SELECT P.ID_PLATO FROM sillas_platos SP JOIN platos P ON P.ID_PLATO=SP.ID_PLATO WHERE SP.ID_SILLA='$id_silla' AND P.PRECIO_EDITABLE=1 ORDER BY P.ID_PLATO");
				$arestrict=array();
				if(count($rsExistentes)>0){
					foreach($rsExistentes as $rwExist){
						$iplat=$rwExist["ID_PLATO"];
						$arestrict[]=$iplat;
					}
				}
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$ait=$gf->cleanVar($_GET["ait"]);
				$t=$gf->cleanVar($_GET["t"]);
				$ok=false;
				$defaults=array();
				$estates=array();
				$obsees=array();
				//print_r($_POST);
				//exit;
				foreach($_POST as $key=>$val){
					if($key!="rd" && substr($key,0,4)!="plt_" && substr($key,0,6)=="racci_"){
						$idrac=intval(str_replace("racci_","",$key));
						if($val>0 && $idrac>0){
							$defaults[$idrac]=$val;
						}
					}elseif($key!="rd" && substr($key,0,4)!="plt_" && substr($key,0,5)=="racc_"){
						$idrac=intval(str_replace("racc_","",$key));
						if($val==0 && $idrac>0){
							$estates[$idrac]=$val;
						}
					}elseif($key!="rd" && substr($key,0,4)!="plt_" && substr($key,0,7)=="observ_"){
						$idplatd=intval(str_replace("observ_","",$key));
						if($val!=""){
							$obsees[$idplatd]=$val;
						}
					}
				}
				$platon="";
				//print_r($_POST);
				//exit;
				foreach($_POST as $key=>$val){
					if($key!="rd" && substr($key,0,4)=="plt_" && substr($key,0,6)!="racci_"){
						$plato=intval(str_replace("plt_","",$key));
						if($val>0 && $plato>0){
							$campoprice=($t!="D") ? "PRECIO" : "PRECIO_DOM";
							$ipla=$gf->dataSet("SELECT NOMBRE, TIPO_PLATO, $campoprice AS PRECIO, PRECIO_EDITABLE FROM platos WHERE ID_PLATO='$plato' ORDER BY ID_PLATO");
							$tipo_plx=$ipla[0]["TIPO_PLATO"];
							$precio=$ipla[0]["PRECIO"];
							$platonm=$ipla[0]["NOMBRE"];
							$editable=$ipla[0]["PRECIO_EDITABLE"];
							if($editable==1){
								$precio=$_POST["iput_$plato"];
							}
							if(isset($obsees[$plato])){
								$observacion=$obsees[$plato];
							}else{
								$observacion="";
							
							}
							
							if(!in_array($plato,$arestrict)){
								$tipo_plx=(isset($_POST["rolplato_$plato"])) ? $_POST["rolplato_$plato"] : $tipo_plx;
								$sql="INSERT INTO sillas_platos (ID_SILLA,ID_PLATO,CANTIDAD,TIPO_PLATO,PRECIO,OBSERVACION) VALUES ('$id_silla','$plato','1','$tipo_plx','$precio','$observacion')";

								$okI=$gf->dataInLast($sql);
								
								if($okI>0){
									$composite=$gf->dataIn("INSERT INTO sillas_platos_composicion (ID_ITEM,ID_RACION,ESTADO,ID_OPCION) SELECT '$okI' AS ID_ITEM, R.ID_RACION, '1' AS ESTADO, O.ID_OPCION FROM platos_composicion R LEFT JOIN racion_opciones O ON O.ID_RACION=R.ID_RACION WHERE R.ID_PLATO='$plato' GROUP BY R.ID_RACION");
									if($ait==0 && $cocina==1){
										$gf->dataIn("UPDATE pedidos SET CAJA='0000-00-00 00:00:00' WHERE ID_PEDIDO='$id_pedido'");
									}
									if(count($defaults)>0){
										foreach($defaults as $idra=>$idop){
											$gf->dataIn("UPDATE sillas_platos_composicion SET ID_OPCION='$idop' WHERE ID_ITEM='$okI' AND ID_RACION='$idra'");
										}
									}
									if(count($estates)>0){
										foreach($estates as $idra=>$idop){
											$gf->dataIn("UPDATE sillas_platos_composicion SET ESTADO='0' WHERE ID_ITEM='$okI' AND ID_RACION='$idra'");
										}
									}
									
									$gf->dataIn("UPDATE pedidos SET CHEF='0000-00-00 00:00:00', DESPACHADO='0' WHERE ID_PEDIDO='$id_pedido'");
									
									if($_SESSION["restecomanda"]>0){
										$gf->dataIn("INSERT INTO telecomanda (ID_ITEM,ID_PEDIDO,FECHA,CANTIDAD,ESTADO) VALUES ('$okI','$id_pedido',NOW(),'1','0')");
									}

									$ok=true;
									
								}
								$platon.=$platonm.",";
							}
						}
					}
				}
				if($ok){
					$gf->log($_SESSION["restbus"],$id_mesa,$id_pedido,"PLATOS AGREGADOS: $platon",$_SESSION["restuiduser"]);
					echo $gf->utf8("
					<script>
					$(function(){
						sockEmitir('up_mesa',{id_mesa:$id_mesa,id_pedido:$id_pedido,tipo:'$t'});
					});
					</script>
					<input type='hidden' id='callbackeval' value=\"reloaHash();closeD('$rnd')\" />");
				}else{
					echo $gf->utf8("
					<script>
						$(function(){
							alert('Los productos con precio variable solo se pueden incluir una vez en el pedido');
						})
					</script>
					<input type='hidden' id='callbackeval' value=\"reloaHash();closeD('$rnd')\" />
					
					");
				}
			}elseif($flag=="additemdlg"){
				$id_silla=$gf->cleanVar($_GET["id_silla"]);
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$nch=$gf->cleanVar($_GET["nch"]);
				$ait=$gf->cleanVar($_GET["ait"]);
				$t=$gf->cleanVar($_GET["t"]);
				if(isset($_GET["all"])){
					$cond="1";
					$btnall="<button onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=additemdlg&id_silla=$id_silla&id_pedido=$id_pedido&id_mesa=$id_mesa&rnd=$rnd&nch=$nch&ait=$ait&t=$t')\" class='btn btn-warning btn-xs pull-right'>Solo alta demanda</button>";
				}else{
					$cond="P.HD='1'";
					$btnall="<button onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=additemdlg&id_silla=$id_silla&id_pedido=$id_pedido&id_mesa=$id_mesa&rnd=$rnd&nch=$nch&ait=$ait&all=1&t=$t')\" class='btn btn-success btn-xs pull-right'>Mostrar todo</button>";
				}
				
				$rsOptions=$gf->dataSet("SELECT P.ID_PLATO, PC.ID_RACION, PC.NOMBRE AS RACION, RO.ID_OPCION, RO.NOMBRE  AS OPCION FROM platos AS P JOIN servicio_oferta SO ON SO.ID_PLATO=P.ID_PLATO JOIN platos_composicion PC ON PC.ID_PLATO=P.ID_PLATO JOIN racion_opciones RO ON RO.ID_RACION=PC.ID_RACION WHERE SO.ID_SERVICIO=:servicio AND RO.ESTADO=1 AND P.ESTADO=1 ORDER BY P.ID_PLATO, PC.POSITION",array(":servicio"=>$_SESSION["restservice"]));
				$lasopciones=array();
				foreach($rsOptions as $rwOptions){
					$r_idplato=$rwOptions["ID_PLATO"];
					$r_idracion=$rwOptions["ID_RACION"];
					$r_nmracion=$rwOptions["RACION"];
					$r_idopion=$rwOptions["ID_OPCION"];
					$r_nmopcion=$rwOptions["OPCION"];
					
					$lasopciones[$r_idplato][$r_idracion]["nm"]=$r_nmracion;
					$lasopciones[$r_idplato][$r_idracion]["op"][$r_idopion]=$r_nmopcion;
						
				}
				$camprice=($t=="D") ? "PRECIO_DOM" : "PRECIO";
				
				$resultChair = $gf->dataSet("SELECT P.ID_PLATO, CAT.ID_CATEGORIA, CAT.NOMBRE AS CATEGORIA, CAT.ICONO, P.NOMBRE AS PLATO, P.$camprice AS PRECIO, P.TIPO_PLATO, P.PRECIO_EDITABLE FROM platos_categorias AS CAT JOIN platos AS P ON(CAT.ID_CATEGORIA=P.ID_CATEGORIA AND P.ID_PLATO IN(SELECT ID_PLATO FROM servicio_oferta WHERE ID_SERVICIO=:servicio)) WHERE CAT.ID_SITIO=:sitio AND CAT.ESTADO='1' AND P.ESTADO='1' GROUP BY P.ID_PLATO ORDER BY CAT.POSITION, P.NOMBRE",array(":servicio"=>$_SESSION["restservice"],":sitio"=>$_SESSION["restbus"]));
				
				$arplati=array();
				if(count($resultChair)>0){

					$inicat="";
					foreach($resultChair as $rowChair){
						$id_categoria=$rowChair["ID_CATEGORIA"];
						$nombre_categoria=$rowChair["CATEGORIA"];
						$id_plato=$rowChair["ID_PLATO"];
						$icono=$rowChair["ICONO"];
						$nombre_plato=$rowChair["PLATO"];
						$precio=$rowChair["PRECIO"];
						$tpz=$rowChair["TIPO_PLATO"];
						$editprice=$rowChair["PRECIO_EDITABLE"];
						$arplati[$id_categoria]["nm"]=$nombre_categoria;
						$arplati[$id_categoria]["ic"]=$icono;
						$arplati[$id_categoria]["pl"][$id_plato]["nm"]=$nombre_plato;
						$arplati[$id_categoria]["pl"][$id_plato]["pz"]=$precio;
						$arplati[$id_categoria]["pl"][$id_plato]["tp"]=$tpz;
						$arplati[$id_categoria]["pl"][$id_plato]["ed"]=$editprice;
					}
				
					echo $gf->utf8("
					<div class='row'><div class='col-xs-6'><input style='position:fixed;top:60px;left:15px;z-index:600;' type='text' placeholder='Buscar...' id='tr_pedidos_items' onkeyup=\"filtrarTr('tr_pedidos_items','flt_tr_items');validaBtnErase()\" /><button id='erasesearch' onclick=\"javascript:$('#tr_pedidos_items').val('');filtrarTr('tr_pedidos_items','flt_tr_items');$('#tr_pedidos_items').focus()\" class='btn btn-sm btn-warning' style='position:fixed;top:60px;left:180px;display:none;'><i class='fa fa-remove'></i></button>
					<script>
					function validaBtnErase(){
						var valsearch=$('#tr_pedidos_items').val();
						if(valsearch!=''){
							$('#erasesearch').show();
						}else{
							$('#erasesearch').hide();
						}
					}

					</script></div>
					<div class='col-xs-6'>$btnall</div>
					</div>
			
					<button id='elbuttonk' style='position:fixed;bottom:60px;left:10px;width:45px;height:45px;font-size:20px;border-radius:25px;z-index:600;' class='btn btn-md btn-warning pull-left shadow' onclick=\"cargaHTMLvars('state_proceso','$sender?flag=go_in_plats&id_silla=$id_silla&id_pedido=$id_pedido&rnd=$rnd&id_mesa=$id_mesa&ait=$ait&t=$t','','12000','unival_select_plats')\"  ondblclick=\"cargaHTMLvars('state_proceso','$sender?flag=go_in_plats&id_silla=$id_silla&id_pedido=$id_pedido&rnd=$rnd&id_mesa=$id_mesa&ait=$ait&t=$t','','12000','unival_select_plats')\"><i class='fa fa-check'></i></button> 
					
					<button class='btn btn-danger btn-md pull-right shadow' style='position:fixed;bottom:60px;left:65px;width:45px;height:45px;font-size:20px;border-radius:25px;;z-index:600;' id='elbuttonk2' onclick=\"closeD('$rnd')\"><i class='fa fa-remove'></i></button>
					
					<div class='box-group' id='accordeon_items'>");	
					$npl=1;
					$expanded=($_SESSION["restaccordion"]==1) ? "false" : "true";
					$firscat="";
					foreach($arplati as $id_categoria=>$info_cat){
						$nplcat=0;
						$nombre_categoria=str_pad($info_cat["nm"], 30, ' ', STR_PAD_RIGHT);
						$icono=$info_cat["ic"];
						echo $gf->utf8("<div class='panel box box-default flt_tr_items'>
						<div class='box-header'>
						<h4 class='box-title' style='font-size:28px;width:100%;font-weight:bold;'>
						<a data-toggle='collapse' data-parent='#accordeon_items' id='clikat_$id_categoria' href='#collapse_cat_$id_categoria' class='text-center clickats' style='width:100%;' aria-expanded='$expanded'>
							$nombre_categoria <i class='fa  fa-caret-square-o-down pull-right'></i>
                     	</a>
						
						 </h4>
						</div>
						<div id='collapse_cat_$id_categoria' class='panel-collapse collapse non-transition firstrr' aria-expanded='$expanded'>
						<div class='box-body'><table class='table'>");
						$arplt=$info_cat["pl"];
						foreach($arplt as $id_plato=>$info_pla){
							
						
							$nombre_plato=$info_pla["nm"];
							$tipl=$info_pla["tp"];
							$edpz=$info_pla["ed"];
							$precio=@number_format($info_pla["pz"],0);
							$precio_nc=$info_pla["pz"];
							if($id_plato!=""){
								if($npl%2>0){
									$classe="bg-danger";
									$classe2="bg-grey1";
								}else{
									$classe="bg-success";
									$classe2="bg-grey2";
								}
	
								if($edpz==0){
									$input_prize="<small class='pull-right'>$ $precio</small>";
								}else{
									$input_prize="<small class='pull-right'><div class='input-group'><span class='input-group-addon'>$</span><input type='number' id='iput_$id_plato' name='iput_$id_plato' value='$precio_nc' class='form-control input-sm unival_select_plats' style='width:90px;text-align:right;' /></div></small>";
								}
								echo $gf->utf8("<tr class='flt_tr_items $classe cattie_$id_categoria' style='margin-left:3px;'><td><h4>$nombre_plato $input_prize</h4></td><td width='35'><input type='checkbox' class='icheck unival_select_plats' onclick=\"showak('$id_plato');strolin()\" style='width:36px !important;height:36px !important;' value='$id_plato' name='plt_$id_plato' id='plt_$id_plato' /></td></tr>");
								$nra=0;
								if(isset($lasopciones[$id_plato])){
									foreach($lasopciones[$id_plato] as $id_rac=>$gopcion){
										$nm_racion=$gopcion["nm"];
				
										if(count($gopcion["op"])>1){
											echo $gf->utf8("<tr style='display:none;' class='$classe2 lospingos_$id_plato'><td>$nm_racion <select class='form-control input-sm unival_select_plats' name='racci_$id_rac' id='racci_$id_rac'>");
											foreach($gopcion["op"] as $iop=>$namop){
												echo $gf->utf8("<option value='$iop'>$namop</option>");
											}
											echo $gf->utf8("</select></td><td><input type='checkbox' class='cicheck_$id_plato"."_$id_rac unival_select_plats' style='width:30px !important;height:30px !important;' value='$id_rac' name='racc_$id_rac' id='racc_$id_rac' checked='checked' /></td></tr>");
										}else if($nm_racion!="Composici&oacute;n &uacute;nica"){
											echo $gf->utf8("<tr style='display:none;' class='$classe2 lospingos_$id_plato'><td>$nm_racion</td><td><input type='checkbox' class='cicheck_$id_plato"."_$id_rac unival_select_plats' style='width:30px !important;height:30px !important;' value='$id_rac' name='racc_$id_rac' id='racc_$id_rac' checked='checked' /></td></tr>");
										}
									}
								}
								$checkent="";
								$checkplf="";
								if($tipl==1){
									$checkent="checked='checked'";
								}elseif($tipl==2){
									$checkplf="checked='checked'";
								}
								echo $gf->utf8("<tr style='display:none;' class='$classe2 lospingos_$id_plato'><td colspan='2'><textarea class='form-control unival_select_plats' id='observ_$id_plato' placeholder='Observaciones adicionales' name='observ_$id_plato'></textarea></td></tr>");
								echo $gf->utf8("<tr style='display:none;' class='$classe2 lospingos_$id_plato'><td colspan='2'>
								
								<table><tr><td>
								<input type='radio' class='unival_select_plats' $checkent id='entradad_$id_plato' name='rolplato_$id_plato' style='width:20px;height:20px;' value='1' /><label for='entradad_$id_plato'>Entrada</label>
								</td>
								<td>
								<input type='radio' class='unival_select_plats' value='2' $checkplf id='entradad_$id_plato' name='rolplato_$id_plato' style='width:20px;height:20px;' /><label for='entradad_$id_plato'>Pl.Fuerte</label>
								</td>
								</tr></table>
								</td></tr>");
								
								$npl++;
							}
							$nplcat++;
						}
						echo $gf->utf8("</table></div></div></div>");
						
					}
					echo $gf->utf8("</div>
					
					<script>


						function strolin(){
							var a=$('.modal').scrollTop();
							var h=$(window).height();
							$('#elbuttonk').css('top',(Math.round(a)+h-80)+'px');
							$('#elbuttonk2').css('top',(Math.round(a)+h-80)+'px');
							$('#tr_pedidos_items').css('top',(Math.round(a)+20)+'px');
							$('#erasesearch').css('top',(Math.round(a)+18)+'px');
						}
						function showak(ipl){
							if($('#plt_'+ipl+':checked').length>0){
								$('.lospingos_'+ipl).show();
							}else{
								$('.lospingos_'+ipl).hide();
							}
						}
						$(function(){
							$('.modal').scroll(strolin);
							strolin()
							$('.clickats').on('click', function (e) {
								e.preventDefault();
								setTimeout(function(){
									$('#Modal_$rnd').scrollTo($(e.target),300);
								},100);
							});
						})
						
						
					</script>
					");
				}else{
					echo $gf->utf8("No se han activado los productos disponibles para el servicio actual");
				}
			
			}
			
		}elseif($_SESSION["restprofile"]=="C"){
			
			
			
			if($flag=="plato_listo"){
				$id_item=$gf->cleanVar($_GET["id_item"]);
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$t=$gf->cleanVar($_GET["t"]);
				$valor=$_POST["cook_$id_item"];
				
				$sql="UPDATE sillas_platos SET LISTO='$valor', ENTREGADO=CANTIDAD, FENTREGA=NOW() WHERE ID_ITEM='$id_item'";
				
				if($gf->dataIn($sql)){
					echo $gf->utf8("<script>
					sockEmitir('mark_listo',{id_pedido:$id_pedido,id_item:$id_item,val:$valor});
					validaEntrega('$id_pedido','$id_item','$valor');
					getSound();
					</script>");
				}else{
					echo "Error al realizar la operaci&oacute;n";
				}
			}elseif($flag=="plato_listo_log"){
				$id_item=$gf->cleanVar($_GET["id_item"]);
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$t=$gf->cleanVar($_GET["t"]);
				$fecha=$gf->cleanVar($_GET["fecha"]);
				$valor=$_POST["cook_$id_item"];
				$_SESSION["mvkt_".$id_item."_".$fecha]=$valor;
				echo $gf->utf8("<script>
					getSound();
					</script>");
			}elseif($flag=="despachar"){
				$id_pedido=$gf->cleanVar($_GET["id_pedido"]);
				$id_mesa=$gf->cleanVar($_GET["id_mesa"]);
				$rnd=$gf->cleanVar($_GET["rnd"]);
				$sql="UPDATE pedidos SET DESPACHADO='1' WHERE ID_PEDIDO='$id_pedido'";
				
				if($gf->dataIn($sql)){
					echo $gf->utf8("<script>
					$(function(){
						$('#mesachefnz_$id_mesa').remove();
						closeD('$rnd');
						sockEmitir('up_mesa',{id_mesa:$id_mesa,id_pedido:$id_pedido,tipo:'M'});
					})
					getSound();
					</script>");
				}else{
					echo "Error al realizar la operaci&oacute;n";
				}
				
			}elseif($flag=="home"){
				if(isset($_GET["view"])){
					$_SESSION["chef_view"]= $gf->cleanVar($_GET["view"]);
					
				}else{
					if(!isset($_SESSION["chef_view"])){
						$_SESSION["chef_view"]="log";
					}
				}
				if($_SESSION["chef_view"]=="completo"){
					$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.TIPO, M.NOMBRE, M.COLOR, P.ID_PEDIDO FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CHEF<>'0000-00-00 00:00:00' AND P.CIERRE='0000-00-00 00:00:00') WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='{$_SESSION["restservice"]}' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
				
					if(count($resultInt)>0){
						
						foreach($resultInt as $rowInt){
							$id_mesa=$rowInt["ID_MESA"];
							$nombre=$rowInt["NOMBRE"];
							$color=$rowInt["COLOR"];
							$tipo=$rowInt["TIPO"];
							$id_pedido=$rowInt["ID_PEDIDO"];
							echo $gf->utf8("<div class='box box-warning'><div class='box-header'><strong>$nombre Pedido No. $id_pedido $tipo <input type='hidden' class='chefcounter_' value=\"$id_pedido\" /></strong></div><div class='box-body'>");
							$nsi=0;
							$inisill=0;
							$resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, SP.CANTIDAD, SP.LISTO, SP.ENTREGADO, P.NOMBRE, P.DESCRIPCION, SP.OBSERVACION AS OBSERPLAT, GROUP_CONCAT(CONCAT(C.NOMBRE,'|',R.ESTADO,'|',RO.NOMBRE,'|',ROP.OPTIS) SEPARATOR '+*+') AS COMPOSITION FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN platos_composicion C ON SP.ID_PLATO=C.ID_PLATO LEFT JOIN sillas_platos_composicion R ON R.ID_ITEM=SP.ID_ITEM AND R.ID_RACION=C.ID_RACION LEFT JOIN racion_opciones RO ON R.ID_OPCION=RO.ID_OPCION LEFT JOIN (SELECT ID_RACION,GROUP_CONCAT(ID_OPCION) OPTIS FROM racion_opciones GROUP BY ID_RACION ORDER BY ID_RACION)ROP ON ROP.ID_RACION=C.ID_RACION WHERE S.ID_PEDIDO='$id_pedido' GROUP BY SP.ID_ITEM ORDER BY S.ID_SILLA");
							if(count($resultChairs)>0){
								echo $gf->utf8("
								<table class='table table-striped'>");
								foreach($resultChairs as $rwChair){
									
									$id_item=$rwChair["ID_ITEM"];
									$id_silla=$rwChair["ID_SILLA"];
									$observacion=$rwChair["OBSERVACION"];
									$entregado=$rwChair["ENTREGADO"];
									$cantidad=$rwChair["CANTIDAD"];
									$nombre_plato=$rwChair["NOMBRE"];
									$descripcion=$rwChair["DESCRIPCION"];
									$composition=$rwChair["COMPOSITION"];
									$obserplat=$rwChair["OBSERPLAT"];
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
												$comps.="Sin $nmcom<br />";
											}else{
												if($nopts>1){
													$comps.="$compost<br />";
												}
											}
										}
									}
									if($obserplat!="") $comps.=$obserplat.",";
									if($comps=="") $comps="Completo";
									$listo=$rwChair["LISTO"];
									if($inisill!=$id_silla){
										$nsi++;
									}
									if($observacion!=""){
										$observacion="<i class='fa fa-user'></i>".$observacion;
									}
									echo $gf->utf8("<tr>
									<td width='10%'>Silla $nsi</td>
									<td width='40%'>$nombre_plato</td>
									<td width='30%'>$comps<br />$observacion</td>
									<td width='5%'>$cantidad</td>
									<td width='15%'>");
									//va
									if($listo==1){
										$chacka="checked='checked'";
									}else{
										$chacka="";
									}
									if($entregado==0){
										echo $gf->utf8("
											<input type='checkbox' $chacka id='cook_$id_item'  name='cook_$id_item' class='icheck unicook_$id_item' value='1' style='width:36px !important;height:36px !important;' onclick=\"cargaHTMLvars('state_proceso','$sender?flag=plato_listo&id_item=$id_item&t=$tipo&id_pedido=$id_pedido','','5000','unicook_$id_item')\" />");
									}else{
										echo $gf->utf8("<i class='fa fa-cutlery'></i>");
									}
									echo $gf->utf8("</td>
										</tr>");
									$inisill=$id_silla;
								}
								echo $gf->utf8("</table>");
							}
							echo $gf->utf8("</div></div>");
						}
					}
					
			
				}elseif($_SESSION["chef_view"]=="log"){
					$resultChairs = $gf->dataSet("SELECT KT.ID_MOVIMIENTO, KT.FECHA, M.NOMBRE AS MESA, PE.ID_PEDIDO, SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, KT.CANTIDAD, SP.LISTO, SP.ENTREGADO, SP.OBSERVACION AS OBSERPLAT, P.NOMBRE, P.DESCRIPCION, GROUP_CONCAT(CONCAT(C.NOMBRE,'|',R.ESTADO,'|',RO.NOMBRE,'|',ROP.OPTIS) SEPARATOR '+*+') AS COMPOSITION FROM mesas M JOIN pedidos PE ON PE.ID_MESA=M.ID_MESA JOIN sillas AS S ON S.ID_PEDIDO=PE.ID_PEDIDO JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN telecomanda KT ON KT.ID_ITEM=SP.ID_ITEM JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN platos_composicion C ON SP.ID_PLATO=C.ID_PLATO LEFT JOIN sillas_platos_composicion R ON R.ID_ITEM=SP.ID_ITEM AND R.ID_RACION=C.ID_RACION LEFT JOIN racion_opciones RO ON R.ID_OPCION=RO.ID_OPCION LEFT JOIN (SELECT ID_RACION,GROUP_CONCAT(ID_OPCION) OPTIS FROM racion_opciones GROUP BY ID_RACION ORDER BY ID_RACION)ROP ON ROP.ID_RACION=C.ID_RACION WHERE KT.ESTADO=1 AND KT.SALE=0 AND PE.CHEF<>'0000-00-00 00:00:00' AND PE.CIERRE='0000-00-00 00:00:00' GROUP BY KT.ID_ITEM, KT.FECHA ORDER BY KT.FECHA");
					$plats="";
					$totpedido=0;
					$totlisto=0;
					$faltan=0;
					if(count($resultChairs)>0){
						echo $gf->utf8("<ul class='list-group'>");
						foreach($resultChairs as $rwChair){
							
							$id_item=$rwChair["ID_ITEM"];
							$fecha=$rwChair["FECHA"];
							$id_silla=$rwChair["ID_SILLA"];
							$observacion=$rwChair["OBSERVACION"];
							$entregado=$rwChair["ENTREGADO"];
							$cantidad=$rwChair["CANTIDAD"];
							$nombre_mesa=$rwChair["MESA"];
							$nombre_plato=$rwChair["NOMBRE"];
							$descripcion=$rwChair["DESCRIPCION"];
							$composition=$rwChair["COMPOSITION"];
							$obserplat=$rwChair["OBSERPLAT"];
							$composet=explode("+*+",$composition);
							$comps="";
							if(isset($_SESSION["mvkt_".$id_item."_".$fecha])){
								if($_SESSION["mvkt_".$id_item."_".$fecha]==1){
									$chacka="checked='checked'";
								}else{
									$chacka="";
								}
							}else{
								$chacka="";
							}
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
							if($obserplat!=""){
								$comps.="$obserplat, ";
							}
							if($comps=="") $comps="Completo";
							$listo=$rwChair["LISTO"];

							if($observacion!=""){
								$observacion="<i class='fa fa-user'></i>".$observacion;
							}
							
							$totpedido++;
							$tipoitem="list-group-item-default";
							if($cantidad<0){
								$tipoitem="list-group-item-danger";
							}
							if($comps!="Completo"){
								$comps=substr($comps,0,-1);
							}
							

							$plats.="
							<li class='list-group-item $tipoitem clearfix'><label for='cook_$id_item' style='width:100%;'><table style='width:100%;'><tr><td style='width:70%;'><big><span class='pull-left badge bg-blue' style='font-size:20px !important;margin-right:2px;'>$cantidad</span> $nombre_plato</big><br /> <span>$comps<br />$observacion</span></td><td  style='width:20%;'>M.$nombre_mesa</td><td  style='width:10%;'>
							
							<input type='checkbox' $chacka id='cook_$id_item'  name='cook_$id_item' class='icheck unicook_$id_item pull-right itemsped_$id_pedido' value='1' style='width:28px !important;height:28px !important;' onclick=\"cargaHTMLvars('state_proceso','$sender?flag=plato_listo_log&id_item=$id_item&t=$tipo&id_pedido=$id_pedido&fecha=$fecha','','5000','unicook_$id_item')\" />";
			
						
							$plats.="</td></tr></table> </label></li>";
							
						}
						echo $gf->utf8("$plats</ul>
						
						
						<script>
						$(function(){
							setTimeout(function(){
								$('.wrapper').animate({
									scrollTop: $('.icheck:not(:checked):first').offset().top - 30
								}, 50);
							},100);
						});
						</script>
						
						");
					}
				}elseif($_SESSION["chef_view"]=="pormesa"){
					$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.TIPO, M.NOMBRE, M.COLOR, P.ID_PEDIDO, COUNT(S.ID_SILLA) AS SILLAS, P.DESPACHADO, SP.ENTREGADO, SP.CANTIDAD  FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CHEF<>'0000-00-00 00:00:00' AND P.CIERRE='0000-00-00 00:00:00') LEFT JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO LEFT JOIN sillas_platos SP ON SP.ID_SILLA=S.ID_SILLA AND SP.LISTO=0 WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='{$_SESSION["restservice"]}' GROUP BY P.ID_PEDIDO ORDER BY P.CHEF");
					$nped=0;
					if(count($resultInt)>0){
						echo $gf->utf8("<div class='row'>");
						foreach($resultInt as $rowInt){
							$id_mesa=$rowInt["ID_MESA"];
							$nombre=$rowInt["NOMBRE"];
							$id_pedido=$rowInt["ID_PEDIDO"];
							$sillas=$rowInt["SILLAS"];
							$despachado=$rowInt["DESPACHADO"];
							$tipo=$rowInt["TIPO"];
							

							$nsi=0;
							$inisill=0;
							if($id_pedido>0){
								$nped++;
								$resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, SP.CANTIDAD, SP.LISTO, SP.ENTREGADO, SP.OBSERVACION AS OBSERPLAT, P.NOMBRE, P.DESCRIPCION, GROUP_CONCAT(CONCAT(C.NOMBRE,'|',R.ESTADO,'|',RO.NOMBRE,'|',ROP.OPTIS) SEPARATOR '+*+') AS COMPOSITION FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN platos_composicion C ON SP.ID_PLATO=C.ID_PLATO LEFT JOIN sillas_platos_composicion R ON R.ID_ITEM=SP.ID_ITEM AND R.ID_RACION=C.ID_RACION LEFT JOIN racion_opciones RO ON R.ID_OPCION=RO.ID_OPCION LEFT JOIN (SELECT ID_RACION,GROUP_CONCAT(ID_OPCION) OPTIS FROM racion_opciones GROUP BY ID_RACION ORDER BY ID_RACION)ROP ON ROP.ID_RACION=C.ID_RACION WHERE S.ID_PEDIDO='$id_pedido' GROUP BY SP.ID_ITEM ORDER BY S.ID_SILLA");
								$plats="";
								$totpedido=0;
								$totlisto=0;
								$faltan=0;
								if(count($resultChairs)>0){
									foreach($resultChairs as $rwChair){
										
										$id_item=$rwChair["ID_ITEM"];
										$id_silla=$rwChair["ID_SILLA"];
										$observacion=$rwChair["OBSERVACION"];
										$entregado=$rwChair["ENTREGADO"];
										$cantidad=$rwChair["CANTIDAD"];
										$nombre_plato=$rwChair["NOMBRE"];
										$descripcion=$rwChair["DESCRIPCION"];
										$composition=$rwChair["COMPOSITION"];
										$obserplat=$rwChair["OBSERPLAT"];
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
										if($obserplat!=""){
											$comps.="$obserplat, ";
										}
										if($comps=="") $comps="Completo";
										$listo=$rwChair["LISTO"];
										if($inisill!=$id_silla){
											$nsi++;
										}
										if($observacion!=""){
											$observacion="<i class='fa fa-user'></i>".$observacion;
										}
										
										$totpedido++;
										if($entregado==$cantidad){
											$chacka="checked='checked'";
											$totlisto++;
											$tipoitem="list-group-item-success";
										}else{
											$chacka="";
											$tipoitem="list-group-item-danger";
											$faltan++;
										}




										if($entregado<$cantidad || $despachado==0){
											$plats.="
											<li class='list-group-item $tipoitem clearfix'><big>$nombre_plato</big>";

											if($entregado<$cantidad){
												$cantidad=$cantidad-$entregado;
												$plats.="
													<input type='checkbox' $chacka id='cook_$id_item'  name='cook_$id_item' class='icheck unicook_$id_item pull-right itemsped_$id_pedido' value='1' style='width:28px !important;height:28px !important;' onclick=\"cargaHTMLvars('state_proceso','$sender?flag=plato_listo&id_item=$id_item&t=$tipo&id_pedido=$id_pedido','','5000','unicook_$id_item')\" />";
											}else{
												$plats.="<i class='fa fa-cutlery pull-right'></i>";
											}
											if($comps!="Completo"){
												$comps=substr($comps,0,-1);
											}
											
											$plats.="(S.$nsi) <span class='pull-left badge bg-blue' style='font-size:20px !important;margin-right:2px;'>$cantidad</span> <small><b>$comps</b><br />$observacion</small> </li>";
										}
										
										$inisill=$id_silla;
									}
								}
								if($totpedido>0){
									$perca=ceil(($totlisto/$totpedido)*100);
								}else{
									$perca=0;
								}
								if($faltan>0){
									$displbott="none";
								}else{
									$displbott="";
								}
								$perdiez=round($perca,-1);
								$color=$arcols[$perdiez];
								if($despachado==0 || $faltan>0){
									echo $gf->utf8("
									<div class='col-md-8 col-lg-8' id='mesachefnz_$id_mesa'>
									<div class='box box-widget widget-user-2 shadow'>
										<div class='widget-user-header bg-grey'>
										
											
										<div class='row'><div class='col-md-8'><h3 class='widget-user-username'>$nombre</h3>
										<h5 class='widget-user-desc'>Pedido No. $id_pedido  ($sillas sillas) <input type='hidden' class='chefcounter_' value=\"$id_pedido\" /></h5>
										<button class='btn btn-warning btn-sm' style='display:$displbott;' id='dispach_$id_pedido' onclick=\"getDialog('$sender?flag=despachar&id_pedido=$id_pedido&id_mesa=$id_mesa')\">Despachado</button>
										</div><div class='col-lg-3 col-md-4'>
										<input type='text' class='knob pull-right' value='$perca' data-width='70' data-height='70' data-fgColor='$color'>
										</div>
										</div>
										</div>
										<div class='box-footer no-padding'>
											<ul class='list-group'>
												$plats
											</ul>
										</div>
									</div>
									</div>
									<div class='col-lg-3 col-md-4 col-lg-4'>
									&nbsp;
									</div>
									");
								}
							}
						}
					}
					echo $gf->utf8("</div>
					");
				}
			}
			
		}
		
	}else{
	
		echo "<META HTTP-EQUIV='REFRESH' CONTENT='0;URL=index.php'>";
	}
?>
