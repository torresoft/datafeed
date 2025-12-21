<?php
session_start();
ini_set("memory_limit","512M");
ini_set("display_errors",0);
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="J" || $_SESSION["restprofile"]=="A")){
	require_once("../autoload.php");
	$gf=new generalFunctions;
	global $relaciones;
	$sender=$_SERVER["PHP_SELF"];
	$dataTables=new dsTables();

    require_once('../lib_php/tcpdf/tcpdf.php');
    require_once('../lib_php/tcpdf/mytcpdf.php');
    
    $id_pedido=$gf->cleanVar($_GET["id_ped"]);
    
    $infoEmpresa=$gf->dataSet("SELECT NIT, NOMBRE, CIUDAD, DIRECCION, TELEFONO, REGIMEN, RESOLUCION_FACTURAS, INIFACT, LOGO, HEADER_FACT, FOOTER_FACT FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
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
        $empresa_logo=$rwEmpresa["LOGO"];
        $fact_header=$rwEmpresa["HEADER_FACT"];
        $fact_footer=$rwEmpresa["FOOTER_FACT"];
        
        if($empresa_logo!="" && file_exists("../".$empresa_logo)){
            $logo='<img src="../'.$empresa_logo.'" align="center" />';
            $empresa_nombre="";
        }else{
            $logo="";
        }
        if($empresa_regimen=="C"){
            $regimen="R&Eacute;GIMEN COM&Uacute;N";
        }else{
            $regimen="R&Eacute;GIMEN SIMPLIFICADO";
        }
    }else{
        echo "Error 968";
        exit;
    }
    $out_htm='';
    $resultInt = $gf->dataSet("SELECT F.CONSECUTIVO, DATE(F.FECHA) AS FECHA, CL.IDENTIFICACION, CL.NOMBRE AS CLIENTE, CL.DIRECCION, CL.TELEFONO, CL.TIPO_ID, M.ID_MESA, M.TIPO, M.NOMBRE, P.ID_PEDIDO, P.DCTO FROM mesas AS M JOIN pedidos AS P ON M.ID_MESA=P.ID_MESA JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO JOIN clientes CL ON CL.ID_CLIENTE=F.ID_CLIENTE WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_PEDIDO='$id_pedido' ORDER BY P.ID_PEDIDO");
    $out_htm='
    <table cellpadding="3" width="100%"  style="font-size:12px;">
        <tr>
           
            <td width="30%" align="center">
                '.$logo.'
            </td>
            <td width="70%" align="center">
                <h2>'.$empresa_nombre.'</h2>
                <h3>Nit. '.$empresa_nit.' - '.$regimen.'</h3>
                <h3>'.$empresa_ciudad.'</h3>
                <h4>Dir: '.$empresa_direccion.', Tel:'.$empresa_telefono.'</h4>
            </td>
        </tr>
        <tr>
            <td width="100%" align="center" colspan="2">
            '.$fact_header.'
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
            $descuento=$rowInt["DCTO"];
            $out_htm.='
        <tr>
            <td align="right" colspan="2"><hr /></td>
        </tr>
        <tr>
            <td colspan="2">
                <table style="font-size:13px;font-weight:bold;" border="1" width="100%" cellpadding="3">
                    <tr><td colspan="4" align="center">FACT: '.$f_consecutivo.'  -  FECHA: '.$f_fecha.'</td></tr>
                    <tr><td bgcolor="#CCC" style="width:25%;">IDENTIF.</td><td style="width:75%;">'.$f_tipoid.'-'.$f_identifica.'</td></tr><tr><td bgcolor="#CCC" style="width:25%;">NOMBRE</td><td style="width:75%;">'.$f_cliente.'</td></tr>
                    <tr><td bgcolor="#CCC">DIR.</td><td>'.$f_direccion.'</td></tr><tr><td bgcolor="#CCC">TEL.</td><td>'.$f_telefono.'</td></tr>
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
            
            $resultChairs = $gf->dataSet("SELECT SP.ID_ITEM, S.ID_SILLA, S.OBSERVACION, SUM(SP.CANTIDAD) AS CANTIDAD, IM.ID_IMPUESTO, IM.NOMBRE AS IMPUESTONM, IM.PORCENTAJE, SP.LISTO, P.NOMBRE, P.DESCRIPCION, SP.PRECIO AS PRECIO, (SP.PRECIO/(1+(IM.PORCENTAJE/100))) AS BASE FROM sillas AS S JOIN sillas_platos AS SP ON (S.ID_SILLA=SP.ID_SILLA) JOIN platos AS P ON (SP.ID_PLATO=P.ID_PLATO) LEFT JOIN impuestos IM ON IM.ID_IMPUESTO=P.ID_IMPUESTO WHERE S.ID_PEDIDO='$id_pedido' GROUP BY P.ID_PLATO ORDER BY S.ID_SILLA");
            $nprod=0;
            $arimp=array();
            if(count($resultChairs)>0){
                $out_htm.='<table style="font-size:10px;font-weight:bold;" border="1" width="100%" cellpadding="2">
                <tr style="font-weight:bold;"><td width="50%">PRODUCTO</td><td width="10%">CA.</td><td width="20%">VR UNIT</td><td width="20%">SUBTOT</td></tr>';
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
                    
                    $arimp[$id_impuesto]["nm"]=$nm_impuesto;
                    $arimp[$id_impuesto]["pc"]=$porcentaje;
                    if(!isset($arimp[$id_impuesto]["vl"])) $arimp[$id_impuesto]["vl"]=0;
                    $arimp[$id_impuesto]["vl"]+=$impuestos;

                    $acumbase+=$precio_base;
                    $acumimp+=$impuestos;
                    $precio_total=$precio_base+$impuestos;
                    $acumprice+=$precio_total;
                    if($id_silla!=$inisill){
                        $nsi++;
                        $inisill=$id_silla;
                    }
                    $out_htm.='<tr>
                    <td>'.$nombre_plato.'</td>
                    <td align="right">'.$cantidad.'</td>
                    <td align="right">'.number_format($base,0).'</td>
                    <td align="right">'.number_format(($precio_base),0).'</td>
                    </tr>';
                    $inisill=$id_silla;
                    $nprod++;
                }

                

                if($descuento>0){
                    $total_new=$acumprice-$descuento;
                    $total_base=($acumbase/$acumprice)*$total_new;
                    $acumimp=$total_new-$total_base;
                    $acumprice=$total_new;
                    $acumbase=$total_base;
                }

                

                $out_htm.='<tr><td colspan="3" align="right"><b>TOTAL</b></td><td align="right"><b>'.number_format($acumbase,0).'</b></td></tr>';
                foreach($arimp as $idim=>$infoimp){
                    $nmim=$infoimp["nm"];
                    $vlim=$infoimp["vl"];
                    $pcim=$infoimp["pc"];
                    $out_htm.='<tr><td colspan="3" align="right"><b>'.$nmim.' ('.$pcim.'%)</b></td><td align="right"><b>'.number_format($vlim,0).'</b></td></tr>';
                }
                $out_htm.='<tr><td colspan="3" align="right"><b>TOTAL IMPUESTOS</b></td><td align="right"><b>'.number_format($acumimp,0).'</b></td></tr>';

                $out_htm.='<tr><td colspan="3" align="right"><b>DESCUENTO</b></td><td align="right"><b>'.number_format($descuento,0).'</b></td></tr>';
                $out_htm.='<tr><td colspan="3" align="right"><b>TOTAL</b></td><td align="right"><b>'.number_format($acumbase+$acumimp,0).'</b></td></tr>';
            
                $out_htm.='</table>';
            }
        }
		
        $out_htm.='</td></tr>
        
        <tr>
            <td width="100%" align="center" colspan="2">
            '.$fact_footer.'
            </td>
        </tr>
        
        </table>';
		//echo $out_htm;
		$pageLayout = array(76, 150+($nprod*8));
        // create new PDF document
        $pdf = new myTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Torresoft');
        $pdf->SetTitle('DataFeed');
        $pdf->SetSubject('Torresoft');
        $pdf->SetKeywords('Factura Generada');
        
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->SetMargins(10,10,10);
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