<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="A" || ($_SESSION["restprofile"]=="J" && $_SESSION["restcajerofiscal"]==1))){
	
	$arcols=array(0=>"#FF0000",10=>"#FF0000",20=>"#FF4000",30=>"#FF4000",40=>"#FF8000",50=>"#FFBF00",60=>"#FFFF00",70=>"#BFFF00",80=>"#80FF00",90=>"#40FF00",100=>"#01DF01");
	require_once("../autoload.php");
	$sender=$_SERVER["PHP_SELF"];
	$gf=new generalFunctions;
	$gc=new generalComponents;
	$dataTables=new dsTables;
    $actividad=$gf->cleanVar($_GET["flag"]);
	if($actividad=="start"){
		echo $gf->utf8("
		<div class='box box-default'>
			<div class='box-header'>REPORTE DE FLUJO DE CLIENTES POR RANGO DE FECHAS</div>
			<div class='box-body form-inline'>
			Datos Desde: 
			
			<div class='form-group'>
				<div class='input-group date' id='lfechafromencuestaa'>
					<input type='text' class='form-control unival_regus' name='fechafromencuesta' class='fechafromencuesta unival_regus' id='fechafromencuesta' />
					<span class='input-group-addon'>
						<span class='glyphicon glyphicon-calendar'>
						</span>
					</span>
				</div>
				<script type='text/javascript'>
					$(function () {
						$('#lfechafromencuestaa').datetimepicker({
							viewMode: 'months',
							format: 'YYYY-MM-DD',
							locale: 'es'
						});
					});
				</script>
			</div>
			
			
			
			Datos Hasta: <div class='form-group'>
				<div class='input-group date' id='fechatoencuestaa'>
					<input type='text' class='form-control unival_regus' name='fechatoencuesta' class='fechatoencuesta unival_regus' id='fechatoencuesta' />
					<span class='input-group-addon'>
						<span class='glyphicon glyphicon-calendar'>
						</span>
					</span>
				</div>
				<script type='text/javascript'>
					$(function () {
						$('#fechatoencuestaa').datetimepicker({
							viewMode: 'months',
							format: 'YYYY-MM-DD',
							locale: 'es'
						});
					});
				</script>
			</div>
					
		
		 <input type='button' class='btn btn-primary' value='Generar'  onclick=\"cargaHTMLvars('showresult','$sender?flag=go','','20000','unival_regus')\" />");
		echo $gf->utf8("<hr /><div id='showresult'></div></div></div>");
	}else{
		$desde=$_POST["fechafromencuesta"];
		$hasta=$_POST["fechatoencuesta"];
		
		$resultInt = $gf->dataSet("SELECT M.ID_MESA, SE.FECHA, M.NOMBRE, CONCAT(T.NOMBRES,' ',T.APELLIDOS) AS TENDER, P.ID_TENDER, P.ID_PEDIDO,  P.APERTURA, P.PAGO, P.CIERRE, HOUR(P.APERTURA) AS HORA, TIMESTAMPDIFF(MINUTE,P.APERTURA,P.CIERRE) AS MINUTOS FROM mesas AS M JOIN pedidos AS P ON (M.ID_MESA=P.ID_MESA AND P.CIERRE<>'0000-00-00 00:00:00') JOIN servicio SE ON SE.ID_SERVICIO=P.ID_SERVICIO JOIN usuarios T ON T.ID_USUARIO=P.ID_TENDER WHERE M.ID_SITIO='".$_SESSION["restbus"]."' AND SE.FECHA BETWEEN '$desde' AND '$hasta'  GROUP BY P.ID_PEDIDO ORDER BY HOUR(P.APERTURA)");
        $armesas=array();
        $artender=array();
        $mintotal=0;
        $pago_total=0;
        $arhoras=array();
        $nped=0;
		if(count($resultInt)>0){
			foreach($resultInt as $rowInt){
                $nped++;
				$id_mesa=$rowInt["ID_MESA"];
				$id_pedido=$rowInt["ID_PEDIDO"];
                $nombre=$rowInt["NOMBRE"];
                $armesas[$id_mesa]["nm"]=$nombre;
                $armesas[$id_mesa]["pe"][]=$id_pedido;
                
				$id_tender=$rowInt["TENDER"];
                $tender=$rowInt["TENDER"];
                $artender[$id_tender]["nm"]=$tender;
                $artender[$id_tender]["pe"][]=$id_pedido;

                $minutos=$rowInt["MINUTOS"];
                $mintotal+=$minutos;
				$apertura=$rowInt["APERTURA"];
                $cierre=$rowInt["CIERRE"];
                
                $hora=$rowInt["HORA"];
                $arhoras[$hora][$id_pedido]=$apertura;

                $pago=$rowInt["PAGO"];
                $pago_total+=$pago;
            }
            $arrange=array();
            for($n=0;$n<24;$n++){
                $r=$n;
                if($n==0){
                    $r=12;
                }
                $ampm="p.m.";
                if($n<11) $ampm="a.m.";
                $l=$r+1;
                $arrange[$n]="$r $ampm";
            }
            $rpt_horas="<table border='1'><tr><td colspan='2'>AFLUENCIA POR HORAS</td></tr>";
            $data_hours="[";
            $label_hours="[";
            foreach($arhoras as $hora=>$info){
                $lahora=$arrange[$hora];
                $cuenta=count($info);
                
                $data_hours.="{y:\"$lahora\",item1:$cuenta},";
                $label_hours.="'$lahora',";
                $rpt_horas.="<tr><td>$lahora</td><td>$cuenta</td></tr>";
            }
            $rpt_horas.="</table>";
            $data_hours=substr($data_hours,0,-1)."]";
            $label_hours=substr($label_hours,0,-1)."]";

             
            $data_mesa="[";
            $rpt_mesas="<table border='1'><tr><td colspan='2'>AFLUENCIA POR MESA</td></tr>";
            foreach($armesas as $mesa=>$info){
                $lamesa=$armesas[$mesa]["nm"];
                $cuenta=count($armesas[$mesa]["pe"]);
                $rpt_mesas.="<tr><td>$lamesa</td><td>$cuenta</td></tr>";
                $data_mesa.="{label: '$lamesa', value: $cuenta},";
            }
            $rpt_mesas.="</table>";
            $data_mesa=substr($data_mesa,0,-1)."]";

            $data_tender="[";
            $rpt_mesero="<table border='1'><tr><td colspan='2'>AFLUENCIA POR MESERO</td></tr>";
            foreach($artender as $tender=>$info){
                $elmesero=$artender[$tender]["nm"];
                $cuenta=count($artender[$tender]["pe"]);
                $rpt_mesero.="<tr><td>$elmesero</td><td>$cuenta</td></tr>";
                $data_tender.="{label: '$elmesero', value: $cuenta},";
            }
            $data_tender=substr($data_tender,0,-1)."]";
            $rpt_mesero.="</table>";

            $avg_minutos=number_format(($mintotal/$nped),0);
            $avg_cuenta=number_format(($pago_total/$nped),0);

            echo $gf->utf8("
            <div class='row'>
                <div class='col-md-12'>
                    <h3>PROMEDIO CUENTA POR PEDIDO: $ $avg_cuenta
                    <br />PROMEDIO DE PERMANENCIA EN ESTABLECIMIENTO: $avg_minutos Min
                </div>
            </div>
            <div class='row'>
                <div class='col-md-12'><div class='box box-success'><div class='box-header'>AFLUENCIA POR HORA</div><div class='box-body'><div id='chart-horas' style='height:300px;'></div></div></div>
               
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6'><div class='box box-success'><div class='box-header'>AFLUENCIA POR MESA</div><div class='box-body'><div id='graf-mesa' style='height:300px;'></div></div></div></div>

                <div class='col-md-6'><div class='box box-success'><div class='box-header'>ATENCIONES POR MESERO</div><div class='box-body'><div id='graf-tender' style='height:300px;'></div></div></div></div>
            </div>
            
            <script>
            $(document).ready(function () {
                var line = new Morris.Line({
                    element: 'chart-horas',
                    resize: true,
                    data: $data_hours,
                    parseTime:false,
                    xkey: 'y',
                    ykeys: ['item1'],
                    labels: ['Ocurrencias'],
                    lineColors: ['#3c8dbc'],
                    hideHover: 'auto'
                });
                var donut = new Morris.Donut({
                    element: 'graf-mesa',
                    resize: true,
                    colors: ['#3c8dbc', '#f56954', '#00a65a', '#f56988', '#00df5a'],
                    data: $data_mesa,
                    hideHover: 'auto'
                });
                var donut = new Morris.Donut({
                    element: 'graf-tender',
                    resize: true,
                    colors: ['#3c8dbc', '#f56954', '#00a65a', '#f56988', '#00df5a'],
                    data: $data_tender,
                    hideHover: 'auto'
                });
            });
            </script>
            
            ");
		}else{
			echo $gf->utf8("No hay resultados");
		}
		
	}
}else{
	echo "No has iniciado sesion!";
}
?>