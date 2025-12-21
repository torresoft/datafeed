<?php
session_start();
error_reporting (E_ALL ^ E_NOTICE);
include_once("config.php");
if(isset($_SESSION["restbusstyle"])){
	$tema=$_SESSION["restbusstyle"];
	$namebus=$_SESSION["restbusname"];
}else{
	$tema="cupertino";
	$namebus="(INICIAR)";
}
require_once("lib_php/generalFunctions.class.php");
$gf=new generalFunctions;
if(isset($_SESSION["restuiduser"]) && $gf->isUserAdm($_SESSION["restuiduser"],$_SESSION["tk"]) && ($_SESSION["restprofile"]=="Z")){

$tot_msgs=0;
$msgs=$gf->dataSet("SELECT M.ID_MSG, M.ASUNTO, CONCAT(U.NOMBRES, U.APELLIDOS) AS USUARIO, M.FECHA, COUNT(M.ID_MSG) AS MSGS FROM mensajes M LEFT JOIN usuarios U ON U.ID_USUARIO=M.ID_FROM WHERE M.ID_TO=:usuario AND M.VISTO=0 GROUP BY U.ID_USUARIO ORDER BY M.FECHA DESC",array(":usuario"=>$_SESSION["restuiduser"]));
foreach($msgs as $msg){
  $tot_msgs+=$msg["MSGS"];
}



$clientes=count($gf->dataSet("SELECT ID_SITIO FROM sitios WHERE ESTADO='1'"));
$clientes_mes=count($gf->dataSet("SELECT ID_SITIO FROM sitios WHERE ESTADO='1' AND MONTH(INICIO_FACTURACION)=MONTH(CURDATE()) AND YEAR(INICIO_FACTURACION)=YEAR(CURDATE())"));

$resultCar = $gf->dataSet("SELECT E.ID_SITIO,E.NOMBRE, E.CIUDAD, E.PROPIETARIO, E.TELEFONO, E.ESTADO, E.FECHA_REGISTRO, E.INICIO_FACTURACION, E.PERIODICIDAD, E.INICIO_FACTURACION, tar.VALOR, (TIMESTAMPDIFF(MONTH, E.INICIO_FACTURACION, CURDATE())+1)*tar.VALOR AS DEBE_PAGAR, SUM(P.VALOR) AS PAGADO FROM sitios E JOIN (SELECT ID_SITIO, VALOR FROM sitios_tarifas GROUP BY ID_SITIO ORDER BY FECHA DESC)tar ON tar.ID_SITIO=E.ID_SITIO LEFT JOIN sitios_pagos P ON P.ID_SITIO=E.ID_SITIO WHERE E.ESTADO=1 GROUP BY E.ID_SITIO ORDER BY E.NOMBRE");
$clientesmora="";
$pericobro=0;
if(count($resultCar)>0){
  $total_debe=0;
  $total_pagado=0;
  foreach($resultCar as $rwCar){
    $sitio=$rwCar["NOMBRE"];
    $debe_pagar=$rwCar["DEBE_PAGAR"];
    $pagado=$rwCar["PAGADO"];
    $tarifa=$rwCar["VALOR"];
    $total_debe+=$debe_pagar;
    $cartera=$debe_pagar-$pagado;
    
    if($cartera>0){
      $clientesmora.="$sitio: $ $cartera<br />";
      $pericobro+=floor($cartera/$tarifa);
    }
    $total_cartera+=$cartera;
  }
  if($total_debe>0){
    $cartera=number_format(($total_cartera/$total_debe)*100,1);
  }else{
    $cartera=0;
  }
  
}else{
  $cartera=0;
}


$rsClact=$gf->dataSet("SELECT E.NOMBRE AS SITIO, COUNT(P.ID_PEDIDO) AS PEDI, SUM(P.PAGO) AS PGS FROM sitios E JOIN servicio SE ON SE.ID_SITIO=E.ID_SITIO JOIN pedidos P ON P.ID_SERVICIO=SE.ID_SERVICIO WHERE E.ESTADO=1 AND MONTH(SE.FECHA)=MONTH(CURDATE()) AND YEAR(SE.FECHA)=YEAR(CURDATE()) GROUP BY E.ID_SITIO ORDER BY COUNT(P.ID_PEDIDO) DESC, E.NOMBRE LIMIT 10");
$clactivos="";
if(count($rsClact)>0){
  foreach($rsClact as $rwCla){
    $sitio=$rwCla["SITIO"];
    $pedidos=$rwCla["PEDI"];
    $pgs=$rwCla["PGS"];
    $clactivos.="$sitio: <span title='$pgs'>$pedidos</span> Pedidos<br />";
  }
}



?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>DataFeed - DashBoard</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="icon" href="favicon.ico">
  
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <!-- Morris chart -->
  <link rel="stylesheet" href="bower_components/morris.js/morris.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="bower_components/jvectormap/jquery-jvectormap.css">
  <!-- Date Picker -->
  <link rel="stylesheet" href="bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
  <link rel="stylesheet" href="dist/css/nprogress.css" />
  <link rel="stylesheet" href="dist/css/custom.css" />
  <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/v/bs/dt-1.10.12/r-2.1.0/datatables.min.css"/>
<link rel="stylesheet" href="dist/css/bootstrap-datetimepicker.min.css" />
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="indesh.php" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>D</b>F</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>DF</b>Dashboard</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">

		 <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-print"></i>
			<span class="label label-danger" id='alertaimpresora'><i class='fa fa-warning'></i></span>
			</a>
          </li>
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-comment"></i>
              <span class="label label-danger"><?php echo $gf->utf8($tot_msgs)?></span>
            </a>
            <!-- inner menu: contains the actual data -->
               <ul class="dropdown-menu">
								 <?php 
								 foreach($msgs as $msg_u){
									 $usuario=$msg_u["USUARIO"];
									 $mensajes=$msg_u["MSGS"];
									 $fecha=$msg_u["FECHA"];
									 if($usuario=="") $usuario="Soporte DataFeed";
								 ?>
                  <li>
                    <a href="#">
                      <i class="fa fa-user"></i><b><?php echo $gf->utf8($usuario)?></b>  - <?php echo $gf->utf8($mensajes)?> 
                    </a>
                  </li>
								<?php 
								 }
								?>
								<li>
									<a href="#sys_mensajes" class='link-cnv' lnk="Admin/site_mensajes.php?flag=home"><i class="fa fa-comment"></i> Ir a Mensajes</a>
								</li>

                </ul>
          </li>
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bell-o"></i>
			  
              <span class="label label-warning">0</span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">Notificaciones</li>
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                  <li>
                    <a href="#">
                      <i class="fa fa-users text-aqua"></i> 0 Alertas cartera
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <i class="fa fa-warning text-yellow"></i> 0 Mensajes de clientes
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <i class="fa fa-users text-red"></i> 0 Requerimientos por resolver
                    </a>
                  </li>
                  
                </ul>
              </li>
            </ul>
          </li>
         
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?php echo $_SESSION["restuavatar"];?>" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo $gf->utf8($_SESSION["restuname"])?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="<?php echo $gf->utf8($_SESSION["restuavatar"])?>" class="img-circle" alt="User Image">

                <p>
                  <?php echo $gf->utf8($_SESSION["restuname"])?>
                  <small><?php echo $gf->utf8($_SESSION["restbusname"])?></small>
                </p>
              </li>
              
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a  href="#perfil_usuario" lnk="Admin/site_profile.php?flag=edit_me" class="btn btn-default btn-flat">Perfil</a>
                </div>
                <div class="pull-right">
                  <a href="govlogin.php" class="btn btn-default btn-flat">Cerrar sesi&oacute;n</a>
                </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
          <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="<?php echo $_SESSION["restuavatar"];?>" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?php echo $gf->utf8($_SESSION["restuname"])?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
     

      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">Opciones</li>
			<?php
			if($_SESSION["restprofile"]=="Z"){
			?>
			<li class="active treeview">
				<a href="#">
					<i class="fa fa-gears"></i> <span>Configuraci&oacute;n</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>
				</a>
				<ul class="treeview-menu">
					<li class='link-cnv'>
						<a href="#info_empresa" class='link-cnv' lnk="dash/site_edit_sitios.php?flag=ver">
							<i class="fa fa-bank"></i> Empresas
						</a>
					</li>
          <li class='link-cnv'>
						<a href="#administrar_tarifas" lnk="dash/site_edit_tarifas.php?flag=ver_bus">
						<i class="fa fa-money"></i> Tarifa por empresa</a>
					</li>
					<li class='link-cnv'>
						<a href="#administrar_usuarios" lnk="dash/site_adm_users.php?flag=ver_bus">
						<i class="fa fa-users"></i> Usarios por empresa</a>
					</li>
					<li class='link-cnv'>
						<a href="#administrar_pagos" lnk="dash/site_payments.php?flag=ver_bus">
							<i class="fa fa-dollar"></i> Control de Pagos
						</a>
					</li>
					
					<li class='link-cnv'>
						<a href="#listado_ingredientes" lnk="Admin/site_sellers.php?flag=ver">
							<i class="fa fa-cart-arrow-down"></i> Vendedores
						</a>
					</li>
				</ul>
			</li>
			<li class="treeview">
			  <a href="#">
				<i class="fa fa-money"></i> <span>Estad&iacute;sticas</span>
				<span class="pull-right-container">
				  <i class="fa fa-angle-left"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li class='link-cnv'>
						<a href="#ventas_abiertas" lnk="rpt/afluencia.php?flag=fact_start">
						<i class="fa fa-hourglass-half"></i>
						Afluencia
						</a>
					</li>
					
					<li class='link-cnv'>
						<a href="#ventas_historico" lnk="rpt/monitor.php?flag=fact_history">
						<i class="fa fa-hourglass-half"></i>
						Monitor de negocios
						</a>
					</li>
          
          
          <li class='link-cnv'>
						<a href="#gesto_serv" lnk="dash/site_edit_servicios.php?flag=ver_bus">
						<i class="fa fa-warning"></i>
						Servicios
						</a>
					</li>
				</ul>
			</li>
			
			<li class="treeview">
			  <a href="#">
				<i class="fa fa-motorcycle"></i> <span>Prospectos</span>
				<span class="pull-right-container">
				  <i class="fa fa-angle-left"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li class='link-cnv'>
						<a href="#ver_pedidos_in" lnk="site_prospect.php?flag=home">
						<i class="fa fa-hourglass-half"></i>
						Clientes potenciales
						</a>
					</li>
				</ul>
			</li>
			
			
			
			<?php
			}elseif($_SESSION["restprofile"]=="X"){
			?>

			<!---CUANDO SE CONTRATEN VENDEDORES --->
		
			<?php
			}
			?>
			<li class="treeview">
			  <a href="#">
				<i class="fa  fa-reply"></i> <span>Salir</span>
				<span class="pull-right-container">
				  <i class="fa fa-angle-left pull-right"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li> <a href="govlogin.php?mob=1"> <i class="fa fa-angle-left pull-right"></i> Cerrar sesi&oacute;n</a></li>
				</ul>
			</li>
		
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
      <?php
		if(file_exists($_SESSION["restbuslogo"])){
			echo $gf->utf8("<img src='{$_SESSION["restbuslogo"]}' style='height:30px;' class='img-circle pull-left' />");
		}
	  ?>
		 <?php echo $gf->utf8($_SESSION["restbusname"]);?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Inicio</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content" id='contenidos'>
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?php echo $gf->utf8($clientes)?></h3>

              <p>Total clientes</p>
            </div>
            <div class="icon">
              <i class="ion ion-beer"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">
              <h3><?php echo $gf->utf8($cartera)?><sup style="font-size: 20px">%</sup></h3>
              <p>Cartera</p>
            </div>
            <div class="icon">
              <i class="ion ion-alert-circled"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <h3><?php echo $gf->utf8($clientes_mes)?></h3>
              <p>Ventas este mes</p>
            </div>
            <div class="icon">
              <i class="ion ion-android-playstore"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner">
              <h3><?php echo $gf->utf8($pericobro)?></h3>

              <p>Cobros pendientes</p>
            </div>
            <div class="icon">
              <i class="ion ion-social-usd"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->
      <!-- Main row -->
      <div class="row">
        <!-- Left col -->
        <section class="col-lg-6 col-md-6">
		
           <div class="box box-danger">
            <div class="box-header with-border">
              <h3 class="box-title">Clientes en mora</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body chart-responsive">
              <div class="chart" id="line-chart" style="height: 300px; position: relative;">
              <?php
                echo $gf->utf8($clientesmora);

              ?>
            
            </div>
            </div>
            <!-- /.box-body -->
          </div>
		</section>
		<section class="col-lg-6 col-md-6">
		<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Clientes m&aacute;s activos</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body chart-responsive">
              <div class="chart" id="sales-chart" style="height: 300px;">
              <?php
                echo $gf->utf8($clactivos);
              ?>
            
            </div>
            </div>
            <!-- /.box-body -->
          </div>

        </section>
		
	</div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Version</b> 1
    </div>
    <strong>Copyright &copy; 2018 <a href="https://torresoft.co">Torresoft</a>.</strong> Derechos Reservados.
  </footer>

  <!-- Control Sidebar -->
  
  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- jQuery 3 -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="bower_components/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.7 -->
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Morris.js charts -->
<script src="bower_components/raphael/raphael.min.js"></script>
<script src="bower_components/morris.js/morris.min.js"></script>
<!-- Sparkline -->
<script src="bower_components/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
<!-- jvectormap -->
<script src="plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<!-- jQuery Knob Chart -->
<script src="bower_components/jquery-knob/dist/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="bower_components/moment/min/moment.min.js"></script>
<script src="bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->
<script src="bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>

<!-- Slimscroll -->
<script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script src="dist/js/nprogress.js"></script>
<script src="dist/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="//cdn.datatables.net/v/bs/dt-1.10.12/r-2.1.0/datatables.min.js"></script>
<script type="text/javascript" src="lib/ready.js" language="javascript"></script>
<script type="text/javascript" src="lib/jquery.form.min.js" language="javascript"></script>
<div class='retroalimenta' style="width:1px;height:1px;overflow:hidden;position:fixed;left:-300px;top:-300px;"><div id="state_proceso" style="width:1px;height:1px;overflow:hidden;position:fixed;left:-300px;top:-300px;"></div><iframe name="if_proc" id="if_proc" width="0" height="0" frameborder="0" scrolling="no"></iframe></div>

	
		<?php
		echo $gf->utf8("<script>
		  $(document).ready(function () {
			'use strict';
			var line = new Morris.Line({
			  element: 'line-chart',
			  resize: true,
			  data: $data_sell,
			  xkey: 'y',
			  ykeys: ['item1'],
			  labels: ['Ventas'],
			  lineColors: ['#3c8dbc'],
			  hideHover: 'auto'
			});
			var donut = new Morris.Donut({
			  element: 'sales-chart',
			  resize: true,
			  colors: ['#3c8dbc', '#f56954', '#00a65a', '#f56988', '#00df5a'],
			  data: $data,
			  hideHover: 'auto'
			});
			
		  });
		</script>");
		
		?>

</body>
</html>
<?php
	}else{
		echo "
		<META HTTP-EQUIV='REFRESH' CONTENT='0;URL=login.php'>
		No session";
	}

?>