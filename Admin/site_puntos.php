<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && $_SESSION["restprofile"]=="A"){
	
	require_once("../autoload.php");
	$gf=new generalFunctions;
	global $relaciones;
	$dataTables=new dsTables();
	$tabla="cartera_ingresos";
	$titulo="INGRESOS RECUPERACION CARTERA";
	$sender=$_SERVER['PHP_SELF'];
	$filterKey="ID_SITIO";
	$filterVal=$_SESSION["restbus"];

	if(isset($_GET["hnd"])){
		$hnd=$gf->cleanVar($_GET["hnd"]);
		$rigu=$_SESSION["UP"][$hnd];
	}else{
		$rigu=array(1,1,1,1,1);
	}
	$actividad=$gf->cleanVar($_GET["flag"]);
	if($actividad=="ver"){
        echo $gf->utf8("
		<div class='panel panel-default'>
			<div class='panel-heading'>PUNTOS POR CLIENTE</div>
			<div class='panel-body'>
                <table class='table table-bordered table-stripped datatables' id='taoooooble_clientes_pts'>
                    <thead>
                        <tr>
                            <th>IDENTIFICACI&Oacute;N</th>
                            <th>CLIENTE</th>
                            <th>TELEFONO</th>
                            <th>PUNTOS HISTORICO</th>
                            <th>REDIMIDOS</th>
                            <th>PUNTOS DISPONIBLES</th>
                            <th>EQUIVALENCIA</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
						");
                    $rsCf = $gf->dataSet("SELECT PUNTO_PESO, PESO_PUNTO FROM sitios WHERE ID_SITIO='{$_SESSION["restbus"]}'");
                    $punto_peso= $rsCf[0]["PUNTO_PESO"];
                    $peso_punto= $rsCf[0]["PESO_PUNTO"];

                    $rsSali=$gf->dataSet("SELECT ID_CLIENTE, SUM(PUNTOS) AS SALIDAS FROM puntos_salida WHERE ID_CLIENTE IN(SELECT ID_CLIENTE FROM clientes WHERE ID_SITIO='{$_SESSION["restbus"]}') GROUP BY ID_CLIENTE ORDER BY ID_CLIENTE");
                    $arsal=array();
                    if(count($rsSali)>0){
                        foreach($rsSali as $rwSali){
                            $id_cliente=$rwSali["ID_CLIENTE"];
                            $puntos=$rwSali["SALIDAS"];
                            $arsal[$id_cliente]=$puntos;
                        }
                    }

                    $rso=$gf->dataSet("SELECT C.ID_CLIENTE, C.IDENTIFICACION, C.NOMBRE, C.TELEFONO, SUM(F.PUNTOS) AS PUNTOS FROM clientes C JOIN facturas F ON F.ID_CLIENTE=C.ID_CLIENTE WHERE C.NOMBRE<>'' AND C.ID_SITIO='{$_SESSION["restbus"]}' GROUP BY C.ID_CLIENTE ORDER BY C.NOMBRE");
                    if(count($rso)>0){
                        foreach($rso as $rwo){
                            $id_cliente=$rwo["ID_CLIENTE"];
                            $identificacion=$rwo["IDENTIFICACION"];
                            $nombre=$rwo["NOMBRE"];
                            $telefono=$rwo["TELEFONO"];
                            $puntos=$rwo["PUNTOS"];
                            $salidas=isset($arsal[$id_cliente]) ? $arsal[$id_cliente] : 0;
                            if($salidas=="") $salidas=0;
                            $disponibles=$puntos-$salidas;
                            $equivalencia=$disponibles*$punto_peso;
                            echo $gf->utf8("
                            <tr>
                                <td>$identificacion</td>
                                <td>$nombre</td>
                                <td>$telefono</td>
                                <td>$puntos</td>
                                <td>$salidas</td>
                                <td>$disponibles</td>
                                <td>$ ".number_format($equivalencia,0)."</td>
                                <td><button class='btn btn-xs btn-warning' onclick=\"getDialog('$sender?flag=estado&id_cliente=$id_cliente')\">Detalle</button></td>
                            </tr>
                            ");
                        }
                    }


						echo $gf->utf8("
					</tbody>
                </table>
			</div>
		</div>");




	}elseif($actividad=="estado"){
        
        $id_cliente=$gf->cleanVar($_GET["id_cliente"]);

		$resultInt=$gf->dataSet("SELECT 'IN' AS OPERACION, ID_FACTURA AS ID, FECHA, CONSECUTIVO, PUNTOS, '0' AS VALOR FROM facturas WHERE ID_CLIENTE='$id_cliente' AND PUNTOS > 0 UNION SELECT 'OUT' AS OPERACION, ID_SALIDA AS ID, FECHA, ID_PEDIDO AS CONSECUTIVO, PUNTOS, VALOR FROM puntos_salida WHERE ID_CLIENTE='$id_cliente' ORDER BY FECHA");
		
        echo $gf->utf8("
        <div class='box box-danger'>
            <div class='box-header'>GESTI&Oacute;N DE CUENTAS DE CARTERA</div>
            <div class='box-body'>
        <table class='table table-bordered'>
        <tr class='bg-danger'>
            <td>OPERACI&Oacute;N</td>
            <td>ID</td>
            <td>FECHA</td>
            <td>PUNTOS</td>
            <td>VALOR</td>
        </tr>");
        
        $tot_a=0;
        $tot_d=0;
        $tot_s=0;
        
        if(count($resultInt)>0){
            foreach($resultInt as $rwI){
                $OP=$rwI["OPERACION"];
                $ID=$rwI["ID"];
                $CONSECUTIVO=$rwI["CONSECUTIVO"];
                $FECHA=$rwI["FECHA"];
                $PUNTOS=$rwI["PUNTOS"];
                $VALOR=$rwI["VALOR"];
                $OPERACION="REDIMIR";
                $ELEMENTO="PEDIDO";
                if($OP=="IN") $OPERACION="COMPRAR";
                if($OP=="IN") $ELEMENTO="FACTURA";

                echo $gf->utf8("<tr><td>$OPERACION</td><td>$ELEMENTO #$CONSECUTIVO</td><td>$FECHA</td><td align='right'>".number_format($PUNTOS,0)."</td><td align='right'>$ ".number_format($VALOR,0)."</td></tr>");
            
                
            }

            
        }
        echo $gf->utf8("</table></div></div>
        ");

   
	}else{
		echo "Ninguna solicitud";
	}
	
}else{
	echo "No has iniciado sesion!";
}
?>