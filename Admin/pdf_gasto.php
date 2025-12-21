<?php
session_start();
ini_set("memory_limit","512M");
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="J" || $_SESSION["restprofile"]=="A")){
	require_once("../autoload.php");
	$gf=new generalFunctions;
	global $relaciones;
	$sender=$_SERVER["PHP_SELF"];
	$dataTables=new dsTables();

    require_once('../lib_php/tcpdf/tcpdf.php');
    require_once('../lib_php/tcpdf/mytcpdf.php');

    $id_gto= $gf->cleanVar($_GET["id_gto"]);

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
    $out_htm='';
    $resultInt = $gf->dataSet("SELECT F.ID_GASTO, F.DESCRIPCION, DATE(F.FECHA) AS FECHA, F.VALOR, T.NOMBRE AS TIPO FROM gastos AS F JOIN gastos_tipos AS T ON T.ID_TIPO=F.ID_TIPO LEFT JOIN usuarios U ON F.ID_USUARIO=U.ID_USUARIO WHERE F.ID_GASTO='$id_gto' AND F.ID_SITIO='".$_SESSION["restbus"]."' ORDER BY F.ID_GASTO");
    $out_htm='
    <table cellpadding="3" width="100%"  style="font-size:12px;">
        <tr>
           
            <td width="100%" align="center">
                <h2>'.$empresa_nombre.'</h2>
                <h3>Nit. '.$empresa_nit.' - '.$regimen.'</h3>
                <h3>'.$empresa_ciudad.'</h3>
                <h4>Dir: '.$empresa_direccion.', Tel:'.$empresa_telefono.'</h4>
            </td>
        </tr>
        <tr>
            <td width="100%" align="center">
               COMPROBANTE DE GASTO
            </td>
        </tr>
    
    ';
    
    if(count($resultInt)>0){
        foreach($resultInt as $rowInt){
            $acumbase=0;
            $acumprice=0;
            $acumimp=0;
            $ID_GASTO=$rowInt["ID_GASTO"];
            $DESCRIPCION=$rowInt["DESCRIPCION"];
            $FECHA=$rowInt["FECHA"];
            $VALOR=$rowInt["VALOR"];
            $TIPO=$rowInt["TIPO"];
            $out_htm.='
       
        <tr>
            <td>
                <table style="font-size:11px;font-weight:bold;" border="1" width="100%" cellpadding="3">
                    <tr>
                        <td bgcolor="#CCC">ID: </td><td>'.$ID_GASTO.'</td><td bgcolor="#CCC">FECHA:</td><td>'.$FECHA.'</td>
                    </tr>
                    <tr>
                        <td bgcolor="#CCC" colspan="2">TIPO GASTO</td><td colspan="2">'.$TIPO.'</td>
                    </tr>
                    <tr>
                        <td bgcolor="#CCC" colspan="2">DESCRIPCION</td><td colspan="2">'.$DESCRIPCION.'</td>
                    </tr>
                    
                    <tr><td bgcolor="#CCC" colspan="2">VALOR.</td><td colspan="2">'.number_format($VALOR,0).'</td></tr>
                </table>
            </td>
        </tr>
    
            ';
            
            
        }
		
        $out_htm.='</table><br /><br /><br /><br /><br /><br />
        <hr />
        Firma Beneficiario';
		//echo $out_htm;
		$pageLayout = array(76, 158);
        // create new PDF document
        $pdf = new myTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageLayout, true, 'UTF-8', false);
            
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Torresoft');
        $pdf->SetTitle('DataFeed');
        $pdf->SetSubject('Torresoft');
        $pdf->SetKeywords('Factura Generada');
        
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->SetMargins(1,1,1);
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