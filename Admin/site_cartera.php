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
	$actividad=$gf->cleanVar($_GET["flag"]);
	if(isset($_GET["hnd"])){
		$hnd=$gf->cleanVar($_GET["hnd"]);
		$rigu=$_SESSION["UP"][$hnd];
	}else{
		$rigu=array(1,1,1,1,1);
	}
	
	

	if($actividad=="estado"){
		

		$rsCabonos=$gf->dataSet("SELECT ID_FPC, SUM(VALOR) AS VALOR FROM cartera_ingresos WHERE ID_FPC IN(SELECT ID_FP FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}') GROUP BY ID_FPC");
		$arcabos=array();
		if(count($rsCabonos)>0){
			foreach($rsCabonos as $rwCabonos){
				$idfpo=$rwCabonos["ID_FPC"];
				$valfpo=$rwCabonos["VALOR"];
				$arcabos[$idfpo]=$valfpo;
			} 
		}

		$rsCabonosClt=$gf->dataSet("SELECT ID_CLIENTE, ID_FPC, SUM(VALOR) AS VALOR FROM cartera_ingresos WHERE ID_FPC IN(SELECT ID_FP FROM formas_pago WHERE ID_SITIO='{$_SESSION["restbus"]}') GROUP BY ID_FPC, ID_CLIENTE");
		$arcabos_clt=array();
		if(count($rsCabonosClt)>0){
			foreach($rsCabonosClt as $rwCabonos){
				$id_cliente=$rwCabonos["ID_CLIENTE"];
				$idfpo=$rwCabonos["ID_FPC"];
				$valfpo=$rwCabonos["VALOR"];
				$arcabos_clt[$idfpo][$id_cliente]=$valfpo;
			} 
		}

        $resultInt = $gf->dataSet("SELECT FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, SUM(P.PAGO) AS VALOR FROM formas_pago AS FP JOIN pedidos AS P ON FP.ID_FP=P.ID_FP WHERE FP.CREDITO=1 AND FP.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY FP.ID_FP ORDER BY FP.NOMBRE");
        echo $gf->utf8("
        <div class='box box-danger'>
            <div class='box-header'>GESTI&Oacute;N DE CUENTAS DE CARTERA</div>
            <div class='box-body'>
        <table class='table table-bordered'><tr class='bg-danger'><td>M&Eacute;TODO DE CARTERA</td><td>TOTAL DEUDA</td><td>ABONOS</td><td>SALDO</td></tr>");
        
        $tot_a=0;
        $tot_d=0;
        $tot_s=0;
        
        if(count($resultInt)>0){
            foreach($resultInt as $rwI){
                $fp=$rwI["ID_FP"];
                $forma=$rwI["FORMAPAGO"];
                $icforma=$rwI["ICONO"];
                $valor=$rwI["VALOR"];
                $valorcu=isset($arcabos[$fp]) ? $arcabos[$fp] : 0;
                $saldo=$valor-$valorcu;
                $tot_d+=$valor;
                $tot_a+=$valorcu;
                $tot_s+=$saldo;
                

				echo $gf->utf8("
				<tr>
					<td><i class='fa $icforma'></i> $forma</td>
					<td align='right'><button class='btn-xs btn btn-info' onclick=\"getDialog('$sender?flag=describe_val&id_fp=$fp','1200','Pedidos','','','loader(\'$sender?flag=estado\')')\">".number_format($valor,0)."</button></td>
					<td align='right'><button class='btn-xs btn btn-info' onclick=\"getDialog('$sender?flag=describe_abos&id_fp=$fp&s=$saldo','1200','Pedidos','','','loader(\'$sender?flag=estado\')')\">".number_format($valorcu,0)."</button></td>
					<td align='right'>".number_format($saldo,0)."</td>
				</tr>");
            
                
            }

            
        }
        echo $gf->utf8("<tr><td>TOTAL</td><td align='right'><b>".number_format($tot_d,0)."</b></td><td align='right'><b>".number_format($tot_a,0)."</b></td><td align='right'><b>".number_format($tot_s,0)."</b></td></tr>");
        echo $gf->utf8("</table></div></div>
        ");


		
        $resultInt = $gf->dataSet("SELECT F.ID_CLIENTE, CONCAT(CL.IDENTIFICACION,' ',CL.NOMBRE) AS CLIENTE, FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, SUM(P.PAGO) AS VALOR FROM formas_pago AS FP JOIN pedidos AS P ON FP.ID_FP=P.ID_FP JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO JOIN clientes CL ON CL.ID_CLIENTE=F.ID_CLIENTE WHERE FP.CREDITO=1 AND F.ID_CLIENTE>0 AND CL.NOMBRE<>'' AND FP.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY FP.ID_FP, CL.ID_CLIENTE ORDER BY FP.NOMBRE, CL.NOMBRE");
        echo $gf->utf8("
        <h3>CARTERA CLIENTES</h3>
        <table class='table table-bordered'><tr class='bg-danger'><td>CLIENTE</td><td>TOTAL DEUDA</td><td>ABONOS</td><td>SALDO</td></tr>");
        
        $tot_a=0;
        $tot_d=0;
        $tot_s=0;
        $curfp=0;
        if(count($resultInt)>0){
            foreach($resultInt as $rwI){
                $fp=$rwI["ID_FP"];
                $forma=$rwI["FORMAPAGO"];
                $id_cliente=$rwI["ID_CLIENTE"];
                $cliente=$rwI["CLIENTE"];
                $icforma=$rwI["ICONO"];
                $valor=$rwI["VALOR"];
                $valorcu=isset($arcabos_clt[$fp][$id_cliente]) ? $arcabos_clt[$fp][$id_cliente] : 0;
                $saldo=$valor-$valorcu;
                $tot_d+=$valor;
                $tot_a+=$valorcu;
                $tot_s+=$saldo;
                if($curfp!=$fp){
					echo $gf->utf8("<tr class='bg-warning'><td colspan='4'><i class='fa $icforma'></i> $forma</td></tr>");
					$curfp=$fp;
				}

				echo $gf->utf8("
				<tr>
					<td>$cliente</td>
					<td align='right'><button class='btn-xs btn btn-info' onclick=\"getDialog('$sender?flag=describe_val&id_fp=$fp&id_cliente=$id_cliente','1200','Pedidos','','','loader(\'$sender?flag=estado\')')\">".number_format($valor,0)."</button></td>
					<td align='right'><button class='btn-xs btn btn-info' onclick=\"getDialog('$sender?flag=describe_abos&id_fp=$fp&s=$saldo&id_cliente=$id_cliente','1200','Pedidos','','','loader(\'$sender?flag=estado\')')\">".number_format($valorcu,0)."</button></td>
					<td align='right'>".number_format($saldo,0)."</td>
				</tr>");
            
                
            }

            
        }
        echo $gf->utf8("<tr><td>TOTAL</td><td align='right'><b>".number_format($tot_d,0)."</b></td><td align='right'><b>".number_format($tot_a,0)."</b></td><td align='right'><b>".number_format($tot_s,0)."</b></td></tr>");
        echo $gf->utf8("</table></div></div>
        ");
        

	}elseif($actividad=="describe_val"){
		$id_fp=$gf->cleanVar($_GET["id_fp"]);
		if(!isset($_GET["id_cliente"])){
			$resultInt = $gf->dataSet("SELECT FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, P.ID_PEDIDO, P.APERTURA, P.PAGO FROM formas_pago AS FP JOIN pedidos AS P ON FP.ID_FP=P.ID_FP WHERE FP.ID_FP='$id_fp' AND FP.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY P.ID_PEDIDO ORDER BY P.APERTURA");
			$addon="";
		}else{
			$id_cliente=$gf->cleanVar($_GET["id_cliente"]);
			$resultInt = $gf->dataSet("SELECT FP.ID_FP, FP.NOMBRE AS FORMAPAGO, FP.CAJA, FP.ICONO, P.ID_PEDIDO, P.APERTURA, P.PAGO FROM formas_pago AS FP JOIN pedidos AS P ON FP.ID_FP=P.ID_FP JOIN facturas F ON F.ID_PEDIDO=P.ID_PEDIDO WHERE FP.ID_FP='$id_fp' AND F.ID_CLIENTE='$id_cliente' AND FP.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY P.ID_PEDIDO ORDER BY P.APERTURA");
			$clt=$gf->dataSet("SELECT NOMBRE FROM clientes WHERE ID_CLIENTE='$id_cliente'");
			$cliente=$clt[0]["NOMBRE"];
			$addon=": $cliente";
		}
		
        $ttl=0;
        if(count($resultInt)>0){
			$forma=$resultInt[0]["FORMAPAGO"];
			echo $gf->utf8("
			<div class='box box-danger'>
				<div class='box-header'>DESCRIPCI&Oacute;N DE $forma$addon</div>
				<div class='box-body'>
					<table class='table table-bordered'><tr class='bg-danger'><td>ID PEDIDO</td><td>FECHA</td><td>VALOR PEDIDO</td></tr>");
            foreach($resultInt as $rwI){
                $pedido=$rwI["ID_PEDIDO"];
                $fecha=$rwI["APERTURA"];
				$pago=$rwI["PAGO"];
				$ttl+=$pago;
				echo $gf->utf8("<tr><td>$pedido</td><td>$fecha</td><td>".number_format($pago,0)."</td></tr>");

			}
			echo $gf->utf8("<tr><td colspan='2'>TOTAL</td><td><b>".number_format($ttl,0)."</b></td></tr>");
			echo $gf->utf8("
					</table>
				</div>
			</div>");
		}else{
			echo "No se encontraron pedidos";
		}

		
	}elseif($actividad=="describe_abos"){
		$id_fp=$gf->cleanVar($_GET["id_fp"]);
		$saldo=$gf->cleanVar($_GET["s"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		if(!isset($_GET["id_cliente"])){
			$rsCabonos=$gf->dataSet("SELECT C.ID_INGRESO, P.ID_FP, P.NOMBRE, C.ID_FPC, C.FECHA, C.VALOR, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS USUARIO FROM formas_pago P JOIN cartera_ingresos C ON C.ID_FPC=P.ID_FP JOIN usuarios U ON U.ID_USUARIO=C.ID_USUARIO WHERE C.ID_FPC='$id_fp' GROUP BY C.ID_INGRESO");
			$addon="";
			$addvars="";
		}else{
			$id_cliente=$gf->cleanVar($_GET["id_cliente"]);
			$rsCabonos=$gf->dataSet("SELECT C.ID_INGRESO, P.ID_FP, P.NOMBRE, C.ID_FPC, C.FECHA, C.VALOR, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS USUARIO FROM formas_pago P JOIN cartera_ingresos C ON C.ID_FPC=P.ID_FP JOIN usuarios U ON U.ID_USUARIO=C.ID_USUARIO WHERE C.ID_FPC='$id_fp' AND C.ID_CLIENTE='$id_cliente' GROUP BY C.ID_INGRESO");
			$clt=$gf->dataSet("SELECT NOMBRE FROM clientes WHERE ID_CLIENTE='$id_cliente'");
			$cliente=$clt[0]["NOMBRE"];
			$addon=": $cliente";
			$addvars="&id_cliente=$id_cliente";
		}
		$rsNm=$gf->dataSet("SELECT NOMBRE FROM formas_pago WHERE ID_FP='$id_fp'");
		if(count($rsNm)>0){
			$forma=$rsNm[0]["NOMBRE"];
		}
		echo $gf->utf8("
		<div class='box box-danger'>
			<div class='box-header'>REGISTRO DE ABONOS $forma$addon</div>
			<div class='box-body'>
				<table class='table table-bordered'><tr class='bg-danger'><td>FECHA</td><td>USUARIO QUE REGISTRA</td><td>VALOR ABONO</td><td></td></tr>");
		if(count($rsCabonos)>0){
			foreach($rsCabonos as $rwCabonos){
				
				$id_ingreso=$rwCabonos["ID_INGRESO"];
				$valfpo=$rwCabonos["VALOR"];
				$fecha=$rwCabonos["FECHA"];
				$usuario=$rwCabonos["USUARIO"];
				echo $gf->utf8("<tr id='tr_abo_$id_ingreso'><td>$fecha</td><td>$usuario</td><td>".number_format($valfpo,0)."</td><td><button class='btn btn-xs btn-danger' onclick=\"if(confirm('Se dispone a borrar un registro, continuar?')){cargaHTMLvars('state_proceso','$sender?flag=deli&id=$id_ingreso&v=$valfpo&fp=$id_fp')}\"><i class='fa fa-trash'></i></button></tr>");
			} 
		}else{
			echo $gf->utf8("<tr><td colspan='3'>A&uacute;n no se han registrado abonos a esta cartera</td></tr>");
		}
		echo $gf->utf8("
				</table><hr />
				<button onclick=\"getDialog('$sender?flag=nuevo&filter=$id_fp&s=$saldo$addvars','300','Registrar\ Pago','','','cargaHTMLvars(\'ModalContent_$rnd\',\'$sender?flag=describe_abos&id_fp=$id_fp&rnd=$rnd&s=$saldo\')')\" class='btn btn-xs btn-primary pull-left' title='Registrar pago'><i class='fa fa-plus'></i> Registrar abono</button>
			</div>
		</div>");
	}elseif($actividad=="deli"){
		$id=$gf->cleanVar($_GET["id"]);
		$fp=$gf->cleanVar($_GET["fp"]);
		$v=$gf->cleanVar($_GET["v"]);
		$rsNm=$gf->dataSet("SELECT NOMBRE FROM formas_pago WHERE ID_FP='$fp'");
		if(count($rsNm)>0){
			$forma=$rsNm[0]["NOMBRE"];
		}
		$ok=$gf->dataIn("DELETE FROM cartera_ingresos WHERE ID_INGRESO=:ingreso",array(":ingreso"=>$id));
		if($ok){
			$gf->log($_SESSION["restbus"],0,0,"INGRESO DE CARTERA BORRADO DE $forma por $v",$_SESSION["restuiduser"]);
			echo "
			<script>
			$(function(){
				$('#tr_abo_$id').remove();
			})
			</script>";
		}else{
			echo "
			<script>
			$(function(){
				alert('Error al borrar registro');
			})
			</script>";
		}
	}elseif($actividad=="editar"){
		$Vkey=$gf->cleanVar($_GET["Vkey"]);
		if(isset($_GET["rnd"])){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
		}else{
			$dialogo="";
		}
		$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey,$filterVal,$dialogo,"getAux(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')");
		echo $gf->utf8($gettabla);
	}elseif($actividad=="nuevo_go"){
		$fpc= $gf->cleanVar($_GET["fpc"]);
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$fpp=$_POST["fpago"];
		$val=$_POST["valor"];
		$id_cliente=$_POST["cliente"];
		$id_serv=$_SESSION["restservice"];
		$ok = $gf->dataIn("INSERT INTO cartera_ingresos (ID_SERVICIO,ID_FPC,ID_FPP,VALOR,ID_USUARIO,FECHA,ID_CLIENTE) VALUES ('$id_serv','$fpc','$fpp','$val','{$_SESSION["restuiduser"]}',NOW(),'$id_cliente')");
		if($ok){
			echo $gf->utf8("<input type='hidden' id='callbackeval' value=\"closeD('$rnd')\" />");
		}else{
			echo "Error al ingresar los datos";
		}
	}elseif($actividad=="nuevo"){
		$dialogo=$gf->cleanVar($_GET["rnd"]);
        $filter= $gf->cleanVar($_GET["filter"]);
		$s=$gf->cleanVar($_GET["s"]);
		$id_cliente=0;
		if(isset($_GET["id_cliente"])){
			$id_cliente=$gf->cleanVar($_GET["id_cliente"]);
		}
		echo $gf->utf8("
		<input type='hidden' name='cliente' id='cliente' class='univl_abbbs' value='$id_cliente' /> 
		<div class='control-group'>
		<label for='fpago'>FORMA DE PAGO (INGRESO)</label>
        <select name='fpago' id='fpago' class='form-control univl_abbbs'>");
        $rso=$gf->dataSet("SELECT ID_FP, NOMBRE, CAJA FROM formas_pago WHERE CREDITO=0 AND ID_SITIO='{$_SESSION["restbus"]}'");
        if(count($rso)>0){
            foreach($rso as $rwo){
                $id_fp=$rwo["ID_FP"];
                $nm_fp=$rwo["NOMBRE"];

                echo $gf->utf8("<option value='$id_fp'>$nm_fp</option>");
            }
        }
        echo $gf->utf8("</select>
        </div>
		<div class='control-group'>
			<label for='valor'>VALOR (INGRESO)</label>
       	 	<input type='number' max='$s' class='form-control univl_abbbs' step='any' name='valor' id='valor' />
        </div>
        <hr />
        <button class='btn btn-primary btn-sm' onclick=\"cargaHTMLvars('ModalContent_$dialogo','$sender?flag=nuevo_go&fpc=$filter&rnd=$dialogo','','','univl_abbbs')\">Registrar Ingreso</button>
        ");
	
	}else{
		echo "Ninguna solicitud";
	}
	
}else{
	echo "No has iniciado sesion!";
}
?>