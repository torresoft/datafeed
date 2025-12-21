<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && $_SESSION["restprofile"]=="A"){
	
	$arcols=array(0=>"#FF0000",10=>"#FF0000",20=>"#FF4000",30=>"#FF4000",40=>"#FF8000",50=>"#FFBF00",60=>"#FFFF00",70=>"#BFFF00",80=>"#80FF00",90=>"#40FF00",100=>"#01DF01");
	require_once("../autoload.php");
	$sender=$_SERVER["PHP_SELF"];
	$gf=new generalFunctions;
	$actividad=$gf->cleanVar($_GET["flag"]);
	if($_SESSION["restservice"]>0){
		$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.NOMBRE, M.COLOR, P.ID_PEDIDO, COUNT(S.ID_SILLA) AS SILLAS FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CHEF<>'0000-00-00 00:00:00' AND P.CAJA='0000-00-00 00:00:00') LEFT JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='{$_SESSION["restservice"]}' ORDER BY P.ID_PEDIDO");
		$nped=0;
		if(count($resultInt)>0){
			echo $gf->utf8("<div class='row'>");
			foreach($resultInt as $rowInt){
				$id_mesa=$rowInt["ID_MESA"];
				$nombre=$rowInt["NOMBRE"];
				$id_pedido=$rowInt["ID_PEDIDO"];
				$sillas=$rowInt["SILLAS"];
				
				if($id_pedido>0){
					$nped++;
					$nsi=0;
					$inisill=0;
					$resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, SP.CANTIDAD, SP.LISTO, SP.ENTREGADO, P.NOMBRE, P.DESCRIPCION, GROUP_CONCAT(CONCAT(C.NOMBRE,'|',R.ESTADO) SEPARATOR '+*+') AS COMPOSITION FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN platos_composicion C ON SP.ID_PLATO=C.ID_PLATO LEFT JOIN sillas_platos_composicion R ON R.ID_ITEM=SP.ID_ITEM AND R.ID_RACION=C.ID_RACION WHERE S.ID_PEDIDO='$id_pedido' GROUP BY SP.ID_ITEM ORDER BY S.ID_SILLA");
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
									if($stcom==0){
										$comps.="Sin $nmcom<br />";
									}
								}
							}
							if($comps=="") $comps="Plato Completo";
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
									<i class='fa fa-hourglass-half pull-right'></i>";
							}else{
								$plats.="<i class='fa fa-cutlery pull-right'></i>";
							}
							$plats.="(S.$nsi)<span class='pull-left badge bg-blue'>$cantidad</span> <small>$comps<br />$observacion</small> </li>";
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
					<div class='col-md-4'>
					  <div class='box box-widget widget-user-2 shadow'>
						<div class='widget-user-header bg-grey'>
						
							
						<div class='row'><div class='col-md-8'><h3 class='widget-user-username'>$nombre</h3>
						  <h5 class='widget-user-desc'>Pedido No. $id_pedido  ($sillas sillas)</h5>
						</div><div class='col-md-4'>
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
		if($nped==0){
			echo $gf->utf8("Sin pedidos activos");
		}
	}else{
		echo "No hay un servicio abierto";
	}
}else{
	echo "No has iniciado sesion!";
}
?>