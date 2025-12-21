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
    $idcock=isset($_GET["id_kit"]) ? $_GET["id_kit"] : 0;
    if($idcock>0){
        $condcock="PL.ID_COCINA='$idcock'";
        $rsFpC=$gf->dataSet("SELECT ID_COCINA, NOMBRE FROM cocinas WHERE ID_COCINA='$idcock'");
        $nm_co=$rsFpC[0]["NOMBRE"];
    }else{
        $nm_co="TODAS LAS COCINAS";
    }
    $out_htm='
    <table cellpadding="3" width="100%"  style="font-size:12px;">
        <tr>
           
            <td width="100%" align="center">
                <h2>'.$empresa_nombre.'</h2>
                <h3>Nit. '.$empresa_nit.' - '.$regimen.'</h3>
                <h3>'.$empresa_ciudad.'</h3>
                <h4>Dir: '.$empresa_direccion.', Tel:'.$empresa_telefono.'</h4>
                <h4>REPORTE: '.$nm_co.'</h4>
            </td>
        </tr>
    
    ';


    $desde= $gf->cleanVar($_GET["desde"]);
    $hasta=$gf->cleanVar($_GET["hasta"]);
    $id_fp=$gf->cleanVar($_GET["id_fp"]);
    if($id_fp==0){
        $condfp="1";
        $titl="";
    }else{
        $condfp="P.ID_FP='$id_fp'";
        $rsFp=$gf->dataSet("SELECT ID_FP, NOMBRE FROM formas_pago WHERE ID_FP='$id_fp'");
        $nm_fp=$rsFp[0]["NOMBRE"];
        $titl="FILTRADO POR FORMA DE PAGO $nm_fp";
    }
    if($desde=="" || $hasta==""){
        echo "Selecciona un rango de fechas";
        exit;
    }
    
    if($idcock>0){
        $condcock="PL.ID_COCINA='$idcock'";
        $rsFpC=$gf->dataSet("SELECT ID_COCINA, NOMBRE FROM cocinas WHERE ID_COCINA='$idcock'");
        $nm_co=$rsFpC[0]["NOMBRE"];
        $titl.=", FILTRADO POR COCINA $nm_co";
    }else{
        $condcock="1";
    }
    $resultInt = $gf->dataSet("SELECT M.ID_MESA, SE.FECHA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, COUNT(S.ID_SILLA) AS SILLAS, P.APERTURA, P.CIERRE, SUM(IF(M.TIPO<>'D',PL.PRECIO*SP.CANTIDAD,PL.PRECIO_DOM*SP.CANTIDAD)) AS PAGO, SUM(P.DCTO) AS DCTO FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CIERRE<>'0000-00-00 00:00:00') JOIN servicio SE ON SE.ID_SERVICIO=P.ID_SERVICIO JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO JOIN sillas_platos SP ON SP.ID_SILLA=S.ID_SILLA JOIN platos PL ON PL.ID_PLATO=SP.ID_PLATO WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND SE.FECHA BETWEEN '$desde' AND '$hasta' AND $condfp AND $condcock GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
    $reporte='';
    if(count($resultInt)>0){
        $reporte.='
        <table border="1" cellpadding="2" width="100%">
            <tr>
                <td>	PED.</td>
                <td>	MESA</td>
                <td>	PAGO</td>
                <td>	DESC</td>
            </tr>';
        $total=0;
        $total_dcto=0;

        $nprod=0;
        foreach($resultInt as $rowInt){
            $id_mesa=$rowInt["ID_MESA"];
            $nombre=$rowInt["NOMBRE"];
            $servicio=$rowInt["FECHA"];
            $tender=$rowInt["TENDER"];
            $id_pedido=$rowInt["ID_PEDIDO"];
            $sillas=$rowInt["SILLAS"];
            $apertura=$rowInt["APERTURA"];
            $cierre=$rowInt["CIERRE"];
            $pago=$rowInt["PAGO"];
            $dcto=$rowInt["DCTO"];
            $total+=$pago;
            $total_dcto+=$dcto;
            $reporte.='
            <tr>
                <td>	'.$id_pedido.'</td>
                <td>	'.$nombre.'</td>
                <td>'.number_format($pago,0).'</td>
                <td>'.number_format($dcto,0).'</td>
            </tr>';
            $nprod++;
        }

        $reporte.='
        <tr>
        <td colspan="2">TOTALES</td><td>'.number_format($total,0).'</td><td>'.number_format($total_dcto,0).'</td></tr>
        </tr>
        </table>
        ';


        $out_htm.='<tr><td>
        <hr />
        <h4>REPORTE DE'.$desde.'  A '.$hasta.'</h4><br />
        
        
        </td></tr>
        <tr><td></td></tr>
        </table>
        '.$reporte;
    }
     

  

    $pageLayout = array(76, 200+($nprod*5));
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