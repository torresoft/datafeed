<?php
session_start();
ini_set("memory_limit","512M");
ini_set("display_errors",0);
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="J" || $_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="T")){
	require_once("../autoload.php");
	$gf=new generalFunctions;
	global $relaciones;
	$sender=$_SERVER["PHP_SELF"];
	$dataTables=new dsTables();

    require_once('../lib_php/tcpdf/tcpdf.php');
    require_once('../lib_php/tcpdf/mytcpdf.php');

    $id_servicio=$gf->cleanVar($_GET["id_servicio"]);


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
            $regimen="R&Eacute;GIMEN SIMPLIFICADO";
        }
    }else{
        echo "Error 968";
        exit;
    }
    $out_htm='
    <table cellpadding="3" width="100%"  style="font-size:12px;">
        <tr>
           
            <td width="100%" align="center">
                <h2>'.$empresa_nombre.'</h2>
                <h3>Nit. '.$empresa_nit.' - '.$regimen.'</h3>
                <h3>'.$empresa_ciudad.'</h3>
                <h4>Dir: '.$empresa_direccion.', Tel:'.$empresa_telefono.'</h4>
                <h4>REPORTE DE FACTURACI&Oacute;N POR SERVICIO</h4>
            </td>
        </tr>
    
    ';

    $id_serv=$gf->cleanVar($_GET["id_servicio"]);

    $rsServ=$gf->dataSet("SELECT FECHA, ESTADO, BASE_CAJA, OBSERVACION FROM servicio WHERE ID_SERVICIO=:servicio",array(":servicio"=>$id_serv));
		
    if(count($rsServ)>0){
        $rwServ=$rsServ[0];
        $fecha_servicio=$rwServ["FECHA"];
        $estado=$rwServ["ESTADO"];
        $base_caja=$rwServ["BASE_CAJA"];
        $observac=$rwServ["OBSERVACION"];
        $totGasto=0;
    
        $resultInt = $gf->dataSet("SELECT F.ID_FACTURA, F.PREFIJO, F.CONSECUTIVO, M.ID_MESA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, P.APERTURA, P.CIERRE, P.PAGO, P.DCTO, P.IMPUESTO, P.DCTO, FP.NOMBRE AS FORMA, FP.CAJA, FP.ID_FP FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA) JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO LEFT JOIN formas_pago FP ON FP.ID_FP=P.ID_FP WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='$id_serv' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
        $arpa_gral=array();
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
                $descto=$rowInt["DCTO"];
                $impuesto=$rowInt["IMPUESTO"];

                $arpa_gral[$id_pedido]["p"]=$pago;
                $arpa_gral[$id_pedido]["d"]=$descto;
                $arpa_gral[$id_pedido]["i"]=$impuesto;

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


        $resultNF = $gf->dataSet("SELECT FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, SUM(P.PAGO) AS VALOR, SUM(P.IMPUESTO) AS IMPUESTO, CU.ID_REL, CU.VALOR AS VALCUADRE FROM formas_pago AS FP JOIN pedidos AS P ON (FP.ID_FP=P.ID_FP AND P.ID_SERVICIO='$id_servicio') JOIN servicio_cuadre CU ON CU.ID_FP=FP.ID_FP AND CU.ID_SERVICIO='$id_servicio' WHERE FP.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_PEDIDO NOT IN(SELECT ID_PEDIDO FROM facturas ORDER BY ID_PEDIDO) GROUP BY FP.ID_FP ORDER BY FP.NOMBRE");
        $arNf=array();
        if(count($resultNF)>0){
            foreach($resultNF as $rwNf){
                $id_fpp=$rwNf["ID_FP"];
                $val=$rwNf["VALOR"];
                $arNf[$id_fpp]=$val;
            }
        }


        $resultInt = $gf->dataSet("SELECT FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, SUM(P.PAGO) AS VALOR, SUM(P.IMPUESTO) AS IMPUESTO, CU.ID_REL, CU.VALOR AS VALCUADRE FROM formas_pago AS FP JOIN pedidos AS P ON (FP.ID_FP=P.ID_FP AND P.ID_SERVICIO='$id_servicio') JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO JOIN servicio_cuadre CU ON CU.ID_FP=FP.ID_FP AND CU.ID_SERVICIO='$id_servicio' WHERE FP.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY FP.ID_FP ORDER BY FP.NOMBRE");
        $tab_resumen='<h4>VALORES POR M&Eacute;TODO DE PAGO</h4><br /><table border="1" cellpadding="2"><tr><td><b>M&Eacute;TODO DE PAGO</b></td><td><b>BASE</b></td><td><b>IMPUESTO</b></td><td><b>TOTAL</b></td></tr>';

        $totfp=0;
        $tot_caja=0;
        $tot_otros=0;
        $tot_v=0;
        $tot_g=0;
        $tot_p=0;
        $tot_d=0;
        $tot_c=0;
        $tot_t=0;
        $resumen_lineal='';
        $tot_i=0;
        $tot_b=0;
        $tot_tot=0;
        if(count($resultInt)>0){
            $restar=0;
            foreach($resultInt as $rwI){
                $fp=$rwI["ID_FP"];
                $forma=$rwI["FORMAPAGO"];
                $icforma=$rwI["ICONO"];
                $valor=$rwI["VALOR"];
                $valor_base=$rwI["VALOR"];
                $impuesto=$rwI["IMPUESTO"];
                if($impuesto>0){
                    $base_imp=$valor-$impuesto;
                }else{
                    $base_imp=$valor;
                }
                $id_rel=$rwI["ID_REL"];
                $valorcu=$rwI["VALCUADRE"];
                if(isset($arNf[$fp])){
                    $valorcu-=$arNf[$fp];
                }
                $caja=$rwI["CAJA"];
                $abis=0;
                if(isset($ab_forma[$fp])){
                    $abis=$ab_forma[$fp];
                }
                $tot_tot+=$valor;
                $tot_i+=$impuesto;
                $tot_b+=$base_imp;
                
                $valor+=$abis;
                if(isset($ab_suma[$fp])){
                    $valor+=$ab_suma[$fp];
                }
                if(isset($ab_resta[$fp])){
                    $valor-=$ab_resta[$fp];
                }

                $tvent=$valor;
                $tot_v+=$tvent;
                $totfp+=$valor;
                $addon="<small>(ventas-gastos con $forma)</small>";
                if($caja==1) $valor+=$base_caja;
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

   
                if($caja==1){
                    $tot_caja += $valor;
                }else{
                    $tot_otros += $valor;
                }
                $ba_caa=($caja==1) ? $base_caja : 0;
                $descu=$valorcu-$valor;
                $tot_c+=$valorcu;
                $tot_t+=$valor;
                $tab_resumen.='<tr><td>'.$forma.'</td><td align="right">'.number_format($base_imp,0).'</td><td align="right">'.number_format($impuesto,0).'</td><td align="right">'.number_format($valor_base,0).'</td></tr>';

                $resumen_lineal.=''.$forma.'<br />Base: '.number_format($ba_caa,0).'<br />Ventas:'.number_format($tvent,0).'<br />Propinas:'.number_format($tprop,0).'<br />Gastos:'.number_format($tgast,0).'<br />Sugerido:'.number_format($valor,0).'<br />Conteo:'.number_format($valorcu,0).'<br />Diferencia:'.number_format($descu,0).'<hr />';
                $tot_d+=$descu;
            
                
                
            }
            
            $tot_caja-=$restar;
            $tot_otros+=$restar;
            
        }
        $tab_resumen.='<tr><td>TOTAL</td><td align="right"><b>'.number_format($tot_b,0).'</b></td><td align="right"><b>'.number_format($tot_i,0).'</b></td><td align="right"><b>'.number_format($tot_tot,0).'</b></td></tr></table>';

        $resumen_lineal.='<b>TOTALES</b><br />Base:'.number_format($base_caja,0).'<br />Ventas:'.number_format($tot_v,0).'<br />Propinas:'.number_format($tot_p,0).'<br />Gastos:'.number_format($tot_g,0).'<br />Sugerido:'.number_format($tot_t,0).'<br />Conteo: '.number_format($tot_c,0).'<br />Conteo: '.number_format($tot_d,0).'';

    }else{
        echo "No se encuentra el servicio";
        exit;
    }
    

    $rsFc=$gf->dataSet("SELECT F.PREFIJO, MIN(F.CONSECUTIVO) AS MINIMO, MAX(F.CONSECUTIVO) AS MAXIMO FROM pedidos P JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO WHERE P.ID_SERVICIO=:servicio GROUP BY P.ID_SERVICIO",array(":servicio"=>$id_servicio));
    if(count($rsFc)>0){
        $min=$rsFc[0]["MINIMO"];
        $max=$rsFc[0]["MAXIMO"];
        $pref=$rsFc[0]["PREFIJO"];
    }
    $bycat='';
    $rsPorCat=$gf->dataSet("SELECT F.ID_FACTURA, F.PREFIJO, F.CONSECUTIVO, P.ID_PEDIDO, P.APERTURA, P.CIERRE, P.PAGO, P.IMPUESTO, P.DCTO, SUM(SP.PRECIO*SP.CANTIDAD) AS VALOR, CA.ID_CATEGORIA, CA.NOMBRE AS CATEGORIA FROM pedidos AS P JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO JOIN sillas_platos SP ON SP.ID_SILLA=S.ID_SILLA JOIN platos PL ON PL.ID_PLATO=SP.ID_PLATO JOIN platos_categorias CA ON CA.ID_CATEGORIA=PL.ID_CATEGORIA WHERE P.ID_SERVICIO='$id_serv' GROUP BY CA.ID_CATEGORIA, P.ID_PEDIDO ORDER BY P.ID_PEDIDO, CA.ID_CATEGORIA");
    $pedicats=array();
    $cats=array();
    if(count($rsPorCat)>0){
        foreach($rsPorCat as $rwPcat){
            $id_pedido=$rwPcat["ID_PEDIDO"];
            $id_categoria=$rwPcat["ID_CATEGORIA"];
            $categoria=$rwPcat["CATEGORIA"];
            $valor=$rwPcat["VALOR"];
            $cats[$id_categoria]["nm"]=$categoria;
            $pedicats[$id_pedido][$id_categoria]=$valor;
        }
        
        foreach($pedicats as $id_ped=>$infoped){
            $valpe=0;
            foreach($infoped as $id_cat=>$val){
                $valpe+=$val;
            }
            $val_ofi=$arpa_gral[$id_ped]["p"];

            if($val_ofi<$valpe){
                $dif = $valpe - $val_ofi;
                $perc=$dif/$valpe;
                foreach($infoped as $id_cat=>$val){
                    $real=$val-($val*$perc);
                    $pedicats[$id_ped][$id_cat]=$real;
                }
            }

        }
        
        foreach($pedicats as $id_ped=>$infoped){
            
            foreach($infoped as $id_cat=>$val){
                if(isset($cats[$id_cat]["val"])){
                    $cats[$id_cat]["val"]+=$val;
                }else{
                    $cats[$id_cat]["val"]=$val;
                }
            }
        }
        $ttl=0;
        $bycat='<hr /><br /><h4>VALORES POR DEPARTAMENTO</h4><table border="1" cellpadding="3"><tr><td width="75%"><b>DEPARTAMENTO</b></td><td width="25%"><b>VALOR</b></td></tr>';
        foreach($cats as $id_cat=>$infocat){
            $nm=$infocat["nm"];
            $vl=$infocat["val"];
            $ttl+=$vl;
            $bycat.='<tr><td>'.$nm.'</td><td align="right">'.number_format($vl,0).'</td></tr>';
        }
        $bycat.='<tr><td>TOTAL</td><td align="right">'.number_format($ttl,0).'</td></tr>';
        $bycat.='</table>';
    }

   


    
    
    $idserv=count($gf->dataSet("SELECT ID_SERVICIO FROM servicio WHERE ID_SITIO='{$_SESSION["restbus"]}' AND ID_SERVICIO<$id_servicio"))+1;
    $out_htm.='<tr><td>
    <hr />
    <h4>SERVICIO No. '.$idserv.' - FECHA: '.$fecha_servicio.'</h4><br />
    
    <b>CONSECUTIVOS:</b> De '.$pref.'-'.$min.' a  '.$pref.'-'.$max.'<br />
    <b>BASE CAJA:</b> '.number_format($base_caja,0).'<br />
    <b>BASE VENTAS:</b> '.number_format($tot_imp_base,0).'<br />
    <b>IMPUESTO VENTAS:</b> '.number_format($tot_impuesto,0).'<br />
    <b>TOTAL VENTAS:</b> '.number_format($tot_v,0).'<br />';
    
    /*<hr />
    <b>PROPINAS:</b> '.number_format($tot_p,0).'<br />
    <b>GASTOS CON RECURSOS DEL SERVICIO:</b> '.number_format($tot_g,0).'<br />
    <b>SUGERIDO TOTAL:</b> '.number_format($tot_t,0).'<br />
    <b>TOTAL CONTEO:</b> '.number_format($tot_c,0).'<br />*/
    $out_htm.='<b>DESCUADRE:</b> '.number_format($tot_d,0).'<br /><br />
    '.$tab_resumen.'<br /><hr /><p>&nbsp;</p>'.$bycat.'<br />';
    /*if(isset($_GET["tm"])){
        $out_htm.='
        <br /><br />'.$resumen_lineal;
    }else{
        $out_htm.='
        <br /><br />'.$tab_resumen;
    }*/
    $out_htm.='<br /><br />
    OBSERVACI&Oacute;N:<br /><br /><hr />
    '.$observac.'<br />
    
    </td></tr></table>
    ';
    //echo $gf->utf8($out_htm);
   

    $pageLayout = array(76, 300);
    // create new PDF document
    if(isset($_GET["tm"])){
        $pdf = new myTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageLayout, true, 'UTF-8', false);
    }else{
        $pdf = new myTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    }
        
    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Torresoft');
    $pdf->SetTitle('DataFeed');
    $pdf->SetSubject('Torresoft');
    $pdf->SetKeywords('Reporte Z');
    
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    if(isset($_GET["tm"])){
        $pdf->SetMargins(1,1,1);
    }else{
        $pdf->SetMargins(20,20,20);
    }
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    
    $pdf->SetAutoPageBreak(TRUE, 2);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
        require_once(dirname(__FILE__).'/lang/spa.php');
        $pdf->setLanguageArray($l);
    }

    // ---------------------------------------------------------

    // add a page
    $pdf->setFont('helvetica');
    if(isset($_GET["tm"])){
        $pdf->SetFontSize(8);
    }else{
        $pdf->SetFontSize(10);
    }
    $pdf->AddPage();
    $pdf->writeHTML($gf->utf8($out_htm), true, false, true, false, '');
    $pdf->lastPage();
    $pdf->Output();

}else{
	echo "No has iniciado sesion!";
}
?>