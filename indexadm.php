<?php
session_start();
ini_set("display_errors",0);
date_default_timezone_set("America/Bogota");
include_once("config.php");
if(isset($_SESSION["restbusstyle"])){
	$tema=$_SESSION["restbusstyle"];
	$namebus=$_SESSION["restbusname"];
}else{
	$tema="cupertino";
	$namebus="(INICIAR)";
}
require_once("./lib_php/generalFunctions.class.php");
$gf=new generalFunctions;

if(isset($_GET["tk"])){
	$tok=$gf->cleanVar($_GET["tk"]);
	$id_user=$gf->cleanVar($_GET["u"]);
	$id_empresa=$gf->cleanVar($_GET["b"]);
	setcookie('sespersist_dfco', $tok, time() + 365 * 24 * 60 * 60);
	setcookie('sespersist_dfius', $id_user, time() + 365 * 24 * 60 * 60);
	setcookie('sespersist_dfbus', $id_empresa, time() + 365 * 24 * 60 * 60);
}

if(!isset($_SESSION["restuiduser"])){
	if(isset($_COOKIE["sespersist_dfco"]) && isset($_COOKIE["sespersist_dfius"])){
		$tkn=$_COOKIE["sespersist_dfco"];
		$ius=$_COOKIE["sespersist_dfius"];
		$bus=$_COOKIE["sespersist_dfbus"];
		$result = $gf->dataSet("SELECT U.ID_USUARIO, E.ID_SITIO, E.ECOMANDA, E.MANCOMUN, E.PROPINAS, E.ANTICIPOS, E.SYS_CHAIRS, E.LOGO, E.NOMBRE AS EMPRESA, E.AUTOGEST, E.IMPUESTO_INCLUIDO, E.TENDER_CANCEL, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS NOMBRE, U.CORREO, E.ID_CLIENT, U.PERFIL, U.WITABLES, E.CAJERO_SERV, E.CAJERO_FISCAL FROM usuarios AS U, sitios AS E WHERE U.ESTADO='1' AND U.ID_SITIO=E.ID_SITIO AND U.ID_USUARIO=:IUS AND U.TOKEN=:TKN AND U.PASSWORD<>'' AND U.ID_SITIO=:SITE AND E.ESTADO=1",array(":IUS"=>$ius,":TKN"=>$tkn,":SITE"=>$bus)); 
		$numreg=count($result);
		if($numreg>0){
			$row=$result[0];
			$nombre=$row["NOMBRE"];
			$id_user=$row["ID_USUARIO"];
			$id_empresa=$row["ID_SITIO"];
			$empresa=$row["EMPRESA"];
			$correo=$row["CORREO"];
			$cliente=$row["ID_CLIENT"];
			$estilo=$row["IMPUESTO_INCLUIDO"];
			$witables=$row["WITABLES"];
			$perfil=$row["PERFIL"];
			$autogest=$row["AUTOGEST"];
			$ecomanda=$row["ECOMANDA"];
			$tendercancel=$row["TENDER_CANCEL"];
			$sys_chairs=$row["SYS_CHAIRS"];
			$propinas=$row["PROPINAS"];
			$cajero_serv=$row["CAJERO_SERV"];
			$cajero_fiscal=$row["CAJERO_FISCAL"];
			$mancomun=$row["MANCOMUN"];
			$anticipos=$row["ANTICIPOS"];
			
			

			$avatar="misc/default_avatar.png";
			$logo=$row["LOGO"];
			$_SESSION["restuname"]=$nombre;
			$_SESSION["restumail"]=$usuario;
			
			$_SESSION["restuiduser"]=$id_user;
			$_SESSION["restbus"]=$id_empresa;
			$_SESSION["restbusname"]=$empresa;
			$_SESSION["restbuslogo"]=$logo;
			$_SESSION["witables"]=$witables;
			$_SESSION["restbusstyle"]=$estilo;
			$_SESSION["restautogest"]=$autogest;
			$_SESSION["restecomanda"]=$ecomanda;
			$_SESSION["restuavatar"]=$avatar;
			$_SESSION["restchairs"]=$sys_chairs;
			$_SESSION["restprofile"]=$perfil;
			$_SESSION["restcajeroserv"]=$cajero_serv;
			$_SESSION["restcajerofiscal"]=$cajero_fiscal;
			$_SESSION["resttendercancel"]=$tendercancel;
			$_SESSION["restpropina"]=$propinas;
			$_SESSION["restanticipos"]=$anticipos;
			$_SESSION["restmancomun"]=$mancomun;
			$_SESSION["restorigin"]=$correo."@".$cliente;
			$_SESSION["tk"]=$tkn;
			if($perfil=="M"){
        header("Location:index.php");
      }
		}else{
			unset($_SESSION["restuname"]);
			unset($_SESSION["restumail"]);
			unset($_SESSION["restutipo"]);
			unset($_SESSION["restuavatar"]);
			unset($_SESSION["restautogest"]);
			unset($_SESSION["restecomanda"]);
			unset($_SESSION["restuiduser"]);
			unset($_SESSION["tk"]);
			unset($_SESSION["restbus"]);
			unset($_SESSION["restofk"]);
			unset($_SESSION["restofkname"]);
			unset($_SESSION["restbusname"]);
			unset($_SESSION["restbuslogo"]);
			unset($_SESSION["resthca"]);
			unset($_SESSION["resthcv"]);
			unset($_SESSION["restsudo"]);
			unset($_SESSION["restwindu"]);
			unset($_SESSION["restbushc"]);
			unset($_SESSION["restdarusgml"]);
			unset($_SESSION["restbuslogo"]);
			unset($_SESSION["restbusstyle"]);
			unset($_SESSION["restori"]);
			unset($_SESSION["restprofile"]);
			unset($_SESSION["restcajeroserv"]);
			unset($_SESSION["restcajerofiscal"]);
			unset($_SESSION["restmancomun"]);
			unset($_SESSION["restanticipos"]);
			unset($_SESSION["restorigin"]);
			unset($_SESSION["resth"]);
			unset($_COOKIE["sespersist_dfco"]);
			unset($_COOKIE["sespersist_dfius"]);
			unset($_COOKIE["sespersist_dfbus"]);
			header("Location:login.php");
			exit;
		}
	}else{
		header("Location:login.php");
		exit;
	}
}

if($_SESSION["restprofile"]=="Z" || $_SESSION["restprofile"]==""){
    header("Location:govlogin.php");
		exit;
}
if($_SESSION["restprofile"]=="M"){
	header("Location:index.php");
}


if(isset($_SESSION["restuiduser"]) && $gf->isUserAdm($_SESSION["restuiduser"],$_SESSION["tk"]) && ($_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="J" || $_SESSION["restprofile"]=="T")){
	
	$curConf=$gf->dataSet("SELECT PRINTER_HOST, CLIENT_KEY, CLIENT_SEC FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
	$prhs=$curConf[0]["PRINTER_HOST"];
	$client_k=$curConf[0]["CLIENT_KEY"];
	$client_s=$curConf[0]["CLIENT_SEC"];
	$curServ=$gf->dataSet("SELECT ID_SERVICIO, FECHA FROM servicio WHERE ESTADO=0 AND ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
	if(count($curServ)>0){
		$_SESSION["restservice"]=$curServ[0]["ID_SERVICIO"];
		$feser=$curServ[0]["FECHA"];
		
		$noti_nmesas=count($gf->dataSet("SELECT ID_MESA FROM mesas WHERE ID_SITIO='{$_SESSION["restbus"]}'"));
		$noti_nordenes=count($gf->dataSet("SELECT ID_PEDIDO FROM pedidos WHERE ID_SERVICIO='{$_SESSION["restservice"]}'"));
		$noti_promedio=$gf->dataSet("SELECT AVG(TIMESTAMPDIFF(MINUTE,P.APERTURA,PL.FENTREGA)) AS PROME  FROM pedidos P JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO JOIN sillas_platos PL ON PL.ID_SILLA=S.ID_SILLA WHERE P.ID_SERVICIO='{$_SESSION["restservice"]}'");
		if(count($noti_promedio)>0){
			// $noti_promedio_entrega=$noti_promedio[0]["PROME"];
			// if($noti_promedio_entrega<60){
			// 	$noti_promedio_entrega=number_format($noti_promedio_entrega,0)."Min";
			// }elseif($noti_promedio_entrega<(60*24)){
			// 	$noti_promedio_entrega=number_format(($noti_promedio_entrega/60))."Hr";
			// }else{
			// 	$noti_promedio_entrega=number_format(($noti_promedio_entrega/60/24))."D&iacute;as";
			// }
		}
		
		$noti_mesas_activas=count($gf->dataSet("SELECT ID_MESA FROM mesas WHERE ID_MESA IN(SELECT ID_MESA FROM pedidos WHERE ID_SERVICIO='{$_SESSION["restservice"]}' AND CAJA='0000-00-00 00:00:00' AND CIERRE='0000-00-00 00:00:00') AND ID_SITIO='{$_SESSION["restbus"]}'"));
		
		$noti_mesas_por_cobrar=count($gf->dataSet("SELECT ID_MESA FROM mesas WHERE ID_MESA IN(SELECT ID_MESA FROM pedidos WHERE ID_SERVICIO='{$_SESSION["restservice"]}' AND CAJA<>'0000-00-00 00:00:00' AND CIERRE='0000-00-00 00:00:00') AND ID_SITIO='{$_SESSION["restbus"]}'"));
		
		$noti_mesas_esperando=count($gf->dataSet("SELECT ID_MESA FROM mesas WHERE ID_MESA IN(SELECT ID_MESA FROM pedidos WHERE ID_SERVICIO='{$_SESSION["restservice"]}' AND CHEF<>'0000-00-00 00:00:00' AND CAJA='0000-00-00 00:00:00') AND ID_SITIO='{$_SESSION["restbus"]}'"));
		
		
		
		$noti_ocupacion=ceil(($noti_mesas_activas/$noti_nmesas)*100);
		$noti_nlibres=$noti_nmesas-$noti_mesas_activas;
		
		$best_products=$gf->dataSet("SELECT PLA.NOMBRE, COUNT(PL.ID_PLATO) AS CUENTA FROM pedidos P JOIN sillas S ON S.ID_PEDIDO=P.ID_PEDIDO JOIN sillas_platos PL ON PL.ID_SILLA=S.ID_SILLA JOIN platos PLA ON PLA.ID_PLATO=PL.ID_PLATO WHERE P.ID_SERVICIO='{$_SESSION["restservice"]}' GROUP BY PLA.ID_PLATO ORDER BY CUENTA DESC LIMIT 5");
		
		if(count($best_products)>0){
			$data="[";
			foreach($best_products as $bproduc){
				$namepro=$bproduc["NOMBRE"];
				$ocurren=$bproduc["CUENTA"];
				if($cuenta=="") $cuenta=0;
				$data.="{label: '$namepro', value: $ocurren},";
			}
			$data=substr($data,0,-1)."]";
		}
		
		if($data==""){
			$data="[]";
		}
		
		
	}else{
		$data="[]";
		$noti_mesas_activas=0;
		$noti_mesas_esperando=0;
		$noti_mesas_por_cobrar=0;
		$noti_nlibres=0;
		$noti_promedio_entrega=0;
		$noti_ocupacion=0;
		$noti_nordenes=0;
		unset($_SESSION["restservice"]);
	}
	$tot_msgs=0;
	$msgs=$gf->dataSet("SELECT M.ID_MSG, M.ASUNTO, CONCAT(U.NOMBRES, U.APELLIDOS) AS USUARIO, M.FECHA, COUNT(M.ID_MSG) AS MSGS FROM mensajes M LEFT JOIN usuarios U ON U.ID_USUARIO=M.ID_FROM WHERE M.ID_TO=:usuario AND M.VISTO=0 GROUP BY U.ID_USUARIO ORDER BY M.FECHA DESC",array(":usuario"=>$_SESSION["restuiduser"]));
	foreach($msgs as $msg){
		$tot_msgs+=$msg["MSGS"];
	}

	
	$noti_ingredientes=count($gf->dataSet("SELECT ID_INGREDIENTE FROM ingredientes WHERE ALERTA=1 AND ID_SITIO='{$_SESSION["restbus"]}'"));
		
	$noti_sin_ingredientes=count($gf->dataSet("SELECT P.ID_PLATO FROM platos_categorias PC JOIN platos P ON P.ID_CATEGORIA=PC.ID_CATEGORIA WHERE P.ID_PLATO NOT IN(SELECT C.ID_PLATO FROM  platos_composicion C JOIN racion_opciones RO ON RO.ID_RACION=C.ID_RACION JOIN racion_ingredientes RI ON RI.ID_RACION=RO.ID_OPCION JOIN ingredientes I ON I.ID_INGREDIENTE=RI.ID_INGREDIENTE WHERE 1 GROUP BY C.ID_PLATO ORDER BY C.ID_PLATO) AND PC.ID_SITIO=:sitio AND P.COCINA='1'",array(":sitio"=>$_SESSION["restbus"])));

	
	$last_services = $gf->dataSet("SELECT S.FECHA, SUM(P.PAGO) AS CUENTA FROM servicio AS S JOIN pedidos AS P ON (S.ID_SERVICIO=P.ID_SERVICIO AND P.CIERRE<>'0000-00-00 00:00:00') WHERE S.ID_SITIO='".$_SESSION["restbus"]."' GROUP BY S.ID_SERVICIO ORDER BY S.FECHA DESC LIMIT 6");
	if(count($last_services)>0){
		$data_sell="[";
		foreach($last_services as $bproduc){
			$namepro=$bproduc["FECHA"];
			$ocurren=$bproduc["CUENTA"];
			if($ocurren=="") $ocurren=0;
			$data_sell.="{y:\"$namepro\",item1:$ocurren},";
		}
		$data_sell=substr($data_sell,0,-1)."]";
	}else{
		$data_sell="[]";
	}
	
	
	
	
	
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>RestoFlow</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="icon" href="favicon.png" type="image/png">
  
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
  <link rel="stylesheet" type="text/css" href="dist/css/datatables.min.css"/>
	<link rel="stylesheet" href="dist/css/bootstrap-datetimepicker.min.css" />
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="styles/fuentes.css">
</head>
<body class="hold-transition skin-yellow sidebar-mini">
<input type='hidden' id='username' value='<?php echo $gf->utf8($_SESSION["restorigin"]);?>' />
<input type='hidden' id='usertoken' value='<?php echo $gf->utf8($_SESSION["tk"]);?>' />
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="indexadm.php" class="logo">
      <img src="./dist/img/logorest.png" alt="RestoFlow Logo" style="height:50px; display:inline-block; margin-right:10px; margin-top:-5px;">
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
            <a href="./indexadm.php"><i class="fa fa-plug"></i>
				<span class="label label-danger" id='alertaimpresora'><i class='fa fa-warning'></i></span>
			</a>
          </li>
		
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bell-o"></i>
			  <?php 
			  $noti_tot=$noti_mesas_activas+$noti_mesas_por_cobrar+$noti_mesas_esperando;
			  ?>
              <span class="label label-warning"><?php echo $gf->utf8($noti_tot)?></span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">Notificaciones</li>
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                  <li>
                    <a href="#">
                      <i class="fa fa-users text-aqua"></i> <?php echo $gf->utf8($noti_mesas_activas)?> Mesas activas
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <i class="fa fa-warning text-yellow"></i> <?php echo $gf->utf8($noti_mesas_por_cobrar)?> Mesas por cobrar
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <i class="fa fa-users text-red"></i> <?php echo $gf->utf8($noti_mesas_esperando)?> Mesas esperando
                    </a>
                  </li>
                  
                </ul>
              </li>
            </ul>
          </li>
          <!-- Tasks: style can be found in dropdown.less -->
		  <?php 
		  $noti_tota=$noti_ingredientes+$noti_sin_ingredientes;
		  ?>
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-flag-o"></i>
              <span class="label label-danger"><?php echo $gf->utf8($noti_tota)?></span>
            </a>
            <!-- inner menu: contains the actual data -->
               <ul class="dropdown-menu">
                  <li>
                    <a href="#">
                      <i class="fa fa-users text-aqua"></i> <?php echo $gf->utf8($noti_ingredientes)?> Ingredientes por comprar
                    </a>
                  </li>
                  <li>
                    <a href="#platos_sin_ingredientes" class='link-cnv' lnk="Admin/site_edit_platos_composicion.php?flag=sin_config">
                      <i class="fa fa-warning text-yellow"></i>  <?php echo $gf->utf8($noti_sin_ingredientes)?> Platos sin ingredientes
                    </a>
                  </li>
                 
                  
                </ul>
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

          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img style="width:21px;height:21px;" src="<?php echo $_SESSION["restuavatar"];?>" class="user-image" alt="User Image">
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
			if($_SESSION["restprofile"]=="T"){
			?>
			<li class="treeview">
				<a href="#">
					<i class="fa fa-gears"></i> <span>Contabilidad</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>
				</a>
				<ul class="treeview-menu">
					<li class='link-cnv'>
						<a href='#admin_servicios' lnk="Admin/site_edit_servicios.php?flag=start"><i class="fa fa-calendar-check-o"></i> Historial de servicios</a>
					</li>
					<!--
					<li class='link-cnv'><a href="#reporte-cuadres" lnk="Admin/rpt_cuadre_servicio.php?flag=start">
						<i class="fa fa-search"></i>Cuadres de caja</a>
					</li>
					<li class='link-cnv'><a href="#reporte-ventas" lnk="Admin/rpt_ventas_servicio.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Ventas por servicio Individual</a>
					</li>
					<li class='link-cnv'><a href="#reporte-ventas-serv-grp" lnk="Admin/rpt_ventas_servicio_grp.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Ventas por servicios</a>
					</li>
					<li class='link-cnv'><a href="#reporte-ventas-rango" lnk="Admin/rpt_ventas_rango.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Ventas por rango de fechas</a>
					</li>-->
				</ul>
			</li>

			<?php
			}
			if($_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="D"){
			?>
			<li class="treeview">
				<a href="#">
					<i class="fa fa-gears"></i> <span>Configuraci&oacute;n</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>
				</a>
				<ul class="treeview-menu">
					<?php
					if($_SESSION["restprofile"]=="A"){
					?>
					<li class='link-cnv'>
						
						<a href="#info_empresa" class='link-cnv' lnk="Admin/site_edit_info.php?flag=editar">
							<i class="fa fa-bank"></i> Info Empresa
						</a>
					</li>
					<?php
					}
					?>
					<li class='link-cnv'>
						<a href="#administrar_usuarios" lnk="Admin/site_adm_users.php?flag=ver">
						<i class="fa fa-user"></i> Usuarios</a>
					</li>
					<li class='link-cnv'>
						<a href="#administrar_clientes" lnk="Admin/site_edit_clientes.php?flag=ver">
						<i class="fa fa-users"></i> Clientes</a>
					</li>
					<li class='link-cnv'>
						<a href="#administrar_grupos_mesas" lnk="Admin/site_edit_mesas_grupos.php?flag=ver">
							<i class="fa fa-cubes"></i> Grupos de Mesas
						</a>
					</li>
					<li class='link-cnv'>
						<a href="#administrar_mesas" lnk="Admin/site_edit_mesas.php?flag=ver">
							<i class="fa fa-cube"></i> Mesas
						</a>
					</li>

					<li class='link-cnv'>
						<a href="#administrar_cocinas" lnk="Admin/site_edit_cocinas.php?flag=ver">
							<i class="fa fa-fire"></i> Cocinas
						</a>
					</li>
					
					<li class='link-cnv'>
						<a href="#listado_ingredientes" lnk="Admin/site_edit_ingredientes.php?flag=ver">
							<i class="fa fa-cart-arrow-down"></i> Lista Ingredientes
						</a>
					</li>

					<li class='link-cnv'>
						<a href="#formas_pago" lnk="Admin/site_edit_formas_pago.php?flag=ver">
							<i class="fa fa-dollar"></i> Formas de pago
						</a>
					</li>

					<li class='link-cnv'>
						<a href="#tipos_gastos" lnk="Admin/site_edit_tipos_gastos.php?flag=ver">
							<i class="fa fa-asterisk"></i> Tipos de gastos
						</a>
					</li>

					<li class='link-cnv'>
						<a href="#tipos_motivos_bajas" lnk="Admin/site_edit_motivos_bajas.php?flag=ver">
							<i class="fa fa-list"></i> Motivos bajas manuales
						</a>
					</li>
		
					<li class='link-cnv'>
						<a href="#categorias_platos" lnk="Admin/site_edit_platos.php?flag=ver">
							<i class="fa fa-cutlery"></i> Categor&iacute;as y Platos
						</a>
					</li>
					
					<li class='link-cnv'>
						<a href="#composicion_platos" lnk="Admin/site_edit_platos_composicion.php?flag=ver">
							<i class="fa fa-cubes"></i> Composici&oacute;n de Platos
						</a>
					</li>
					
					<li class='link-cnv'>
						<a href="#config_impuestos" lnk="Admin/site_edit_impuestos.php?flag=start">
							<i class="fa fa-eyedropper"></i> Impuestos por plato
						</a>
					</li>
					
					<li class='link-cnv'>
						<a href="#operation_log" lnk="Admin/site_log.php?flag=start">
							<i class="fa fa-puzzle-piece"></i> Registro de eventos
						</a>
					</li>
					
				</ul>
			</li>
			<li class="treeview">
			  <a href="#">
				<i class="fa fa-money"></i> <span>Facturaci&oacute;n</span>
				<span class="pull-right-container">
				  <i class="fa fa-angle-left"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li class='link-cnv'>
						<a href="#ventas_abiertas" lnk="Admin/site_box.php?flag=fact_start">
						<i class="fa fa-hourglass-half"></i>
						Ventas abiertas
						</a>
					</li>
					
					<li class='link-cnv'>
						<a href="#ventas_historico" lnk="Admin/site_box.php?flag=fact_history">
						<i class="fa fa-hourglass-half"></i>
						Historial de ventas
						</a>
					</li>
					
					<li class='link-cnv'>
						<a href="#estado_caja" lnk="Admin/site_box.php?flag=estado_caja">
						<i class="fa fa-dollar"></i>
						Estado Caja
						</a>
					</li>

					<li class='link-cnv'>
						<a href="#estado_cartera" lnk="Admin/site_cartera.php?flag=estado">
						<i class="fa fa-folder-open"></i>
						Cartera
						</a>
					</li>

					<li class='link-cnv'>
						<a href="#puntos_cliente" lnk="Admin/site_puntos.php?flag=ver">
						<i class="fa fa-diamond"></i>
						Puntos
						</a>
					</li>


					<li class='link-cnv'>
						<a href='#estado_propinas' lnk="Admin/site_edit_servicios.php?flag=propins"><i class="fa fa-money"></i> Estado de propinas</a>
					</li>
					
					<li class='link-cnv'><a href="#reporte-cuadres-preview" lnk="Admin/site_edit_servicios.php?flag=cuadre">
						<i class="fa fa-search"></i>Previsualizaci&oacute;n de cierre</a>
					</li>

					<li class='link-cnv'><a href="#reporte-cuadres-x-box" lnk="Admin/rpt_cuadre_servicio.php?flag=cuadre_x_boxa">
						<i class="fa fa-sitemap"></i>Facturaci&oacute;n por Cajero</a>
					</li>
					
					
				</ul>
			</li>
			
			<li class="treeview">
			  <a href="#">
				<i class="fa fa-motorcycle"></i> <span>Pedidos/Domicilios</span>
				<span class="pull-right-container">
				  <i class="fa fa-angle-left"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li class='link-cnv'>
						<a href="#ver_pedidos_in" lnk="mviews.php?flag=home">
						<i class="fa fa-hourglass-half"></i>
						Ingresar pedido
						</a>
					</li>
					<li class='link-cnv'>
						<a href="#ver_pedidos_en_proceso" lnk="Admin/site_box.php?flag=pedi_start">
						<i class="fa fa-cutlery"></i>
						Pedidos abiertos
						</a>
					</li>

					<li class='link-cnv'>
						<a href="#ver_reservas" lnk="Admin/site_reservas.php?flag=fact_start">
						<i class="fa fa-lock"></i>
						Reservas
						</a>
					</li>


				</ul>
			</li>
			
			
			
			<?php
			}elseif($_SESSION["restprofile"]=="J"){
			?>
			<li class="treeview">
			  <a href="#">
				<i class="fa fa-money"></i> <span>Facturaci&oacute;n</span>
				<span class="pull-right-container">
				  <i class="fa fa-angle-left"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li class='link-cnv'>
						<a href="#ventas_abiertas" lnk="Admin/site_box.php?flag=fact_start">
						<i class="fa fa-hourglass-half"></i>
						Ventas abiertas
						</a>
					</li>

					<?php
					

					if($_SESSION["restcajerofiscal"]==2){

					?>
					<li class='link-cnv'>
						<a href="#estado_caja" lnk="Admin/site_box.php?flag=estado_caja">
						<i class="fa fa-dollar"></i>
						Estado Caja
						</a>
					</li>
					
					<?php
					}

					if($_SESSION["restcajerofiscal"]==1){

					?>

					<li class='link-cnv'>
						<a href="#estado_caja" lnk="Admin/site_box.php?flag=estado_caja">
						<i class="fa fa-dollar"></i>
						Estado Caja
						</a>
					</li>
					
					<li class='link-cnv'>
						<a href="#ventas_historico" lnk="Admin/site_box.php?flag=fact_history">
						<i class="fa fa-hourglass-half"></i>
						Historial de ventas
						</a>
					</li>
					
				
					<li class='link-cnv'>
						<a href='#estado_propinas' lnk="Admin/site_edit_servicios.php?flag=propins"><i class="fa fa-money"></i> Estado de propinas</a>
					</li>
					<li class='link-cnv'><a href="#reporte-cuadres-x-box" lnk="Admin/rpt_cuadre_servicio.php?flag=cuadre_x_box">
						<i class="fa fa-search"></i>Reporte Cajero</a>
					</li>
					<li class='link-cnv'><a href="#reporte-cuadres-preview" lnk="Admin/site_edit_servicios.php?flag=cuadre">
						<i class="fa fa-search"></i>Previsualizaci&oacute;n de cierre</a>
					</li>
						<?php
					}
					?>
					
					
				</ul>
			</li>
			<li class="treeview">
			  <a href="#">
				<i class="fa fa-motorcycle"></i> <span>Pedidos/Domicilios</span>
				<span class="pull-right-container">
				  <i class="fa fa-angle-left"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li class='link-cnv'>
						<a href="#ver_pedidos_in" lnk="mviews.php?flag=home">
						<i class="fa fa-hourglass-half"></i>
						Ingresar pedido
						</a>
					</li>
					<li class='link-cnv'>
						<a href="#ver_reservas" lnk="Admin/site_reservas.php?flag=fact_start">
						<i class="fa fa-lock"></i>
						Reservas
						</a>
					</li>

				</ul>
			</li>
			
			<?php
			}
			if($_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="D" || ($_SESSION["restprofile"]=="J" && $_SESSION["restcajeroserv"]==1)){
			?>
			<li class="treeview">
			  <a href="#">
				<i class="fa fa-hand-pointer-o"></i> <span>Servicio</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-left"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li class='link-cnv'>
						<a href='#admin_servicios' lnk="Admin/site_edit_servicios.php?flag=start"><i class="fa fa-calendar-check-o"></i> Apertura/Cierre</a>
					</li>
					<li class='link-cnv'>
						<a href='#pedidos_abiertos' lnk="Admin/site_pedidos_abiertos.php?flag=start"><i class="fa fa-bell-o"></i> Pedidos abiertos</a>
					</li>
					
				</ul>
			</li>
		
			<li class="treeview">
			  <a href="#">
				<i class="fa fa-battery-2"></i> <span>Inventarios</span>
				<span class="pull-right-container">
				  <i class="fa fa-angle-left"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li class='link-cnv'><a href="#inv_proveedores" lnk="Admin/site_edit_proveedores.php?flag=ver">
						<i class="fa fa-truck"></i>Proveedores</a>
					</li>
					<li class='link-cnv'><a href="#inv_compras" lnk="Admin/site_edit_inventario.php?flag=start">
						<i class="fa  fa-cart-plus"></i>Compras</a>
					</li>
					<li class='link-cnv'><a href="#inv_bajasmanuales" lnk="Admin/site_edit_inventario.php?flag=bajas_manuales">
						<i class="fa fa-cart-arrow-down"></i>Bajas Manuales</a>
					</li>
					<li class='link-cnv'><a href="#estado_inventario" lnk="Admin/site_edit_inventario.php?flag=estado_inventario">
						<i class="fa fa-exclamation-triangle"></i>Estado Inventario</a>
					</li>
					<li class='link-cnv'><a href="#bajas_rango" lnk="Admin/site_edit_inventario.php?flag=bajas_start">
						<i class="fa fa-exclamation-triangle"></i>Bajas por rango</a>
					</li>
				</ul>
			</li>

			<li class="treeview">
			  <a href="#">
				<i class="fa fa-reply-all"></i> <span>Gastos</span>
				<span class="pull-right-container">
				  <i class="fa fa-angle-left"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li class='link-cnv'><a href="#registrar-gastos" lnk="Admin/gastos_registrar.php?flag=ver">
						<i class="fa fa-pencil"></i>Registrar Gasto</a>
					</li>
					<li class='link-cnv'><a href="#gastos-servicio" lnk="Admin/gastos_registrar.php?flag=serv_start">
						<i class="fa fa-calendar"></i>Gastos por servicio</a>
					</li>
				</ul>
			</li>
			<?php
			}elseif($_SESSION["restprofile"]=="J"){
			?>

			<li class="treeview">
			  <a href="#">
				<i class="fa fa-battery-2"></i> <span>Inventarios</span>
				<span class="pull-right-container">
				  <i class="fa fa-angle-left"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li class='link-cnv'><a href="#inv_compras" lnk="Admin/site_edit_inventario.php?flag=start">
						<i class="fa  fa-cart-plus"></i>Compras</a>
					</li>
				</ul>
			</li>


			<?php
			}
			if($_SESSION["restprofile"]=="A" || $_SESSION["restcajerofiscal"]==1){
			?>
			<li class="treeview">
			  <a href="#">
				<i class="fa fa-file-excel-o"></i> <span>Reportes</span>
				<span class="pull-right-container">
				  <i class="fa fa-angle-left"></i>
				</span>
			  </a>
				<ul class="treeview-menu">
					<li class='link-cnv'><a href="#reporte-cuadres" lnk="Admin/rpt_cuadre_servicio.php?flag=start">
						<i class="fa fa-search"></i>Cuadres de caja</a>
					</li>
					
					<li class='link-cnv'><a href="#historial-cuadres" lnk="Admin/site_box.php?flag=fchistory_home">
						<i class="fa fa-warning"></i>Historial Ventas</a>
					</li>
					<li class='link-cnv'><a href="#reporte-ventas" lnk="Admin/rpt_ventas_servicio.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Ventas por servicio Individual</a>
					</li>
					<li class='link-cnv'><a href="#reporte-ventas-serv-grp" lnk="Admin/rpt_ventas_servicio_grp.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Ventas por servicios</a>
					</li>
					<li class='link-cnv'><a href="#reporte-ventas-rango" lnk="Admin/rpt_ventas_rango.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Ventas por rango de fechas</a>
					</li>
					<li class='link-cnv'><a href="#reporte-ventas-rango-tax" lnk="Admin/rpt_ventas_rango_v2.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Impuestos por rango de fechas</a>
					</li>

					<li class='link-cnv'><a href="#reporte-compras" lnk="Admin/rpt_compras_rango.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Compras Inventario</a>
					</li>

					<li class='link-cnv'><a href="#reporte-cocina" lnk="Admin/rpt_ventas_cocina.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Ventas por cocina</a>
					</li>

					<li class='link-cnv'><a href="#reporte-gastos" lnk="Admin/rpt_gastos_rango.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Gastos</a>
					</li>
					<li class='link-cnv'><a href="#reporte-balance" lnk="Admin/rpt_balance_rango.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Balance</a>
					</li>
					<li class='link-cnv'><a href="#reporte-balance-serv" lnk="Admin/rpt_servicio_balance.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Balance Servicios</a>
					</li>
					
					<li class='link-cnv'><a href="#reporte-platos-rango" lnk="Admin/rpt_platos_rango.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Ocurrencias por producto</a>
					</li>

					<li class='link-cnv'><a href="#reporte-concurrencia-cliente" lnk="Admin/rpt_comportamiento.php?flag=start">
						<i class="fa fa-pie-chart"></i>Comportamiento del cliente</a>
					</li>

					<li class='link-cnv'><a href="#reporte-facts-rango" lnk="Admin/rpt_facturas.php?flag=start">
						<i class="fa fa-file-excel-o"></i>Facturas operador</a>
					</li>
				</ul>
			</li>
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
			echo $gf->utf8("<img src='{$_SESSION["restbuslogo"]}' style='height:30px;' class='img-circle pull-left' onclick=\"cargaHTMLvars('state_proceso','opencash.php?x=1')\" />");
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
		<section class="content hidden" id='contenidos-aux'>

		</section>
    <section class="content" id='contenidos'>
      <!-- Small boxes (Stat box) -->

			<?php
			if($feser!=date("Y-m-d")){
			?>
			<div class="alert alert-warning alert-dismissible">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				<h4><i class="icon fa fa-warning"></i> Atenci&oacute;n!</h4>
				La fecha del servicio activo no corresponde con la fecha actual...
			</div>
			<?php
			}
			?>
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?php echo $gf->utf8($noti_nordenes)?></h3>

              <p>Ordenes Hoy</p>
            </div>
            <div class="icon">
              <i class="ion ion-android-restaurant"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <h3><?php echo $gf->utf8($noti_ocupacion)?><sup style="font-size: 20px">%</sup></h3>
              <p>Ocupaci&oacute;n</p>
            </div>
            <div class="icon">
              <i class="ion ion-battery-low"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">
              <h3><?php echo $gf->utf8($noti_nlibres)?></h3>

              <p>Mesas Libres</p>
            </div>
            <div class="icon">
              <i class="ion ion-radio-waves"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner">
              <h3><?php echo $gf->utf8($noti_promedio_entrega)?></h3>

              <p>Promedio Demora</p>
            </div>
            <div class="icon">
              <i class="ion ion-android-stopwatch"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->
      <!-- Main row -->
      <div class="row">
        <!-- Left col -->
				<?php
				if($_SESSION["restprofile"]=="A" || ($_SESSION["restprofile"]=="J" && $_SESSION["restcajerofiscal"]==1)){
				?>
        <section class="col-lg-6 col-md-6">
		
           <div class="box box-danger">
            <div class="box-header with-border">
              <h3 class="box-title">Ventas &uacute;ltimos servicios</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body chart-responsive">
              <div class="chart" id="line-chart" style="height: 300px; position: relative;"></div>
            </div>
            <!-- /.box-body -->
          </div>
				</section>
				<?php
				}
				?>
				<section class="col-lg-6 col-md-6">
					<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Platos Estrella</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body chart-responsive">
              <div class="chart" id="sales-chart" style="height: 300px;"></div>
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
      <b>Version</b> 1.LC
    </div>
    <strong>Copyright &copy; 2020 <a href="https://torresoft.co">Torresoft</a>.</strong> Derechos Reservados.
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

<script src="./lib/jqueryui.min.js"></script>

<!-- Slimscroll -->
<script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script src="dist/js/nprogress.js"></script>
<script src="dist/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./dist/js/datatables.min.js"></script>
<script type="text/javascript" src="./lib/ready.js?v=3" language="javascript"></script>
<script type="text/javascript" src="./lib/jquery.form.min.js" language="javascript"></script>
<div class='retroalimenta' style="width:1px;height:1px;overflow:hidden;position:fixed;left:-300px;top:-300px;"><div id="state_proceso" style="width:1px;height:1px;overflow:hidden;position:fixed;left:-300px;top:-300px;"></div><iframe name="if_proc" id="if_proc" width="0" height="0" frameborder="0" scrolling="no"></iframe></div>
<div class='hidden'>
<div class='hidden'>
<div class='hidden'>
<div class='hidden'>
<div class='hidden'>
<?php
	$result = $gf->dataSet("SELECT ID_USUARIO, NOMBRES, APELLIDOS, PERFIL FROM usuarios WHERE ID_SITIO=:SITE",array(":SITE"=>$_SESSION["restbus"])); 
	$numreg=count($result);
	if($numreg>0){
		foreach($result as $rwa){
			$idu=$rwa["ID_USUARIO"];
			$nm=$rwa["NOMBRES"];
			$apl=$rwa["APELLIDOS"];
			$pf=$rwa["PERFIL"];
			echo $gf->utf8("<input type='hidden' id='_uau_$idu' value='$nm' apl='$apl' pf='$pf' />");
		}
	}
?>
</div>
</div>
</div>
</div>
</div>	
<script src="<?php echo SOCKET_SVR?>"></script>
<script>
  <?php
  echo "var server = '".SERVER_URL."';";
  ?>
</script>
<script src="lib/socket.js?v=120"></script>
<input type='hidden' id='prhs' value='<?php echo $prhs ?>' />
<input type='hidden' id='clprp' value='<?php echo $_SESSION["restpropina"] ?>' />
<?php
echo $gf->utf8("<script>
	$(document).ready(function () {
	'use strict';
	var donut = new Morris.Donut({
		element: 'sales-chart',
		resize: true,
		colors: ['#3c8dbc', '#f56954', '#00a65a', '#f56988', '#00df5a'],
		data: $data,
		hideHover: 'auto'
	});
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