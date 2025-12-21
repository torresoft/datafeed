<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="T" || ($_SESSION["restprofile"]=="J" && $_SESSION["restcajerofiscal"]==1))){
	
	$arcols=array(0=>"#FF0000",10=>"#FF0000",20=>"#FF4000",30=>"#FF4000",40=>"#FF8000",50=>"#FFBF00",60=>"#FFFF00",70=>"#BFFF00",80=>"#80FF00",90=>"#40FF00",100=>"#01DF01");
	require_once("../autoload.php");
	$sender=$_SERVER["PHP_SELF"];
	$gf=new generalFunctions;
	$gc=new generalComponents;
	$dataTables=new dsTables;
	$actividad=$gf->cleanVar($_GET["flag"]);
	if($actividad=="start"){
		$fkf=array("ESTADO"=>2);
		$sons=array();
		$gettabla = $dataTables->armaFilter("servicio","FECHA DESC",0,0,0,"ID_SITIO",$_SESSION["restbus"],$sender,"$sender?flag=level1","level1",0,$fkf,$sons);
		$fila1=$gc->row(array(array("id"=>"level1","extend"=>12,"content"=>"")));
		$panel=$gc->panel("default","REPORTE DE VENTAS POR SERVICIO","",array(),$gettabla."<hr />".$fila1);
		echo $gf->utf8($panel);
	}else{
		$id_servicio=$gf->cleanVar($_GET["key"]);
		if($id_servicio==0 || $id_servicio==""){
			echo "Selecciona un servicio";
			exit;
		}
		$resultInt = $gf->dataSet("SELECT M.ID_MESA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_PEDIDO, P.APERTURA, P.CIERRE, P.PAGO, P.DCTO FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CIERRE<>'0000-00-00 00:00:00') JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND P.ID_SERVICIO='$id_servicio' GROUP BY P.ID_PEDIDO ORDER BY P.ID_PEDIDO");
					
		if(count($resultInt)>0){
			echo $gf->utf8("
			<table class='table table-striped'>
				<thead>
					<tr>
						<td>	MESA</td>
						<td>	TENDER</td>
						<td>	APERTURA</td>
						<td>	CIERRE</td>
						<td>	PAGO</td>
						<td>	DCTO</td>
					</tr>
				</thead>
				<tbody>
						
			");
			$total=0;
			$total_dcto=0;
			$rows="";
			foreach($resultInt as $rowInt){
				$id_mesa=$rowInt["ID_MESA"];
				$nombre=$rowInt["NOMBRE"];
				$tender=$rowInt["TENDER"];
				$id_pedido=$rowInt["ID_PEDIDO"];
				$apertura=$rowInt["APERTURA"];
				$cierre=$rowInt["CIERRE"];
				$pago=$rowInt["PAGO"];
				$dcto=$rowInt["DCTO"];
				$total+=$pago;
				$total_dcto+=$dcto;
				$rows.="
				<tr>
					<td>	$nombre</td>
					<td>	$tender</td>
					<td>	$apertura</td>
					<td>	$cierre</td>
					<td>".number_format($pago,0)."</td>
					<td>".number_format($dcto,0)."</td>
				</tr>";
			}
		}
		echo $gf->utf8("<tr>
		<td colspan='4'>TOTALES</td><td>".number_format($total,0)."</td><td>".number_format($total_dcto,0)."</td></tr>
		</tr>".$rows);
		echo $gf->utf8("</tbody>
		<tfoot>
				<tr>
				<td colspan='4'>TOTALES</td><td>".number_format($total,0)."</td><td>".number_format($total_dcto,0)."</td></tr>
				</tr>
		</tfoot>
		</table>
		");
	}
}else{
	echo "No has iniciado sesion!";
}
?>