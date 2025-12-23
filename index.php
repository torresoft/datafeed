<?php
session_start();
date_default_timezone_set("America/Bogota");
error_reporting (E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);
if(isset($_SESSION["restbusstyle"])){
	$tema=$_SESSION["restbusstyle"];
	$namebus=$_SESSION["restbusname"];
}else{
	$tema="cupertino";
	$namebus="(INICIAR)";
}
include_once("./autoload.php");
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
		$result = $gf->dataSet("SELECT U.ID_USUARIO, E.ID_SITIO, E.ECOMANDA, E.PROPINAS, E.SYS_CHAIRS, E.LOGO, E.NOMBRE AS EMPRESA, E.AUTOGEST, E.IMPUESTO_INCLUIDO, E.TENDER_CANTS, E.TENDER_CANCEL, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS NOMBRE, U.CORREO, U.PERFIL, E.ID_CLIENT, U.WITABLES, E.CAJERO_SERV, E.CAJERO_FISCAL, E.MANCOMUN FROM usuarios AS U, sitios AS E WHERE U.ESTADO='1' AND U.ID_SITIO=E.ID_SITIO AND U.ID_USUARIO=:IUS AND U.TOKEN=:TKN AND U.PASSWORD<>'' AND U.ID_SITIO=:SITE AND E.ESTADO=1",array(":IUS"=>$ius,":TKN"=>$tkn,":SITE"=>$bus)); 
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
			$tendercants=$row["TENDER_CANTS"];
			$sys_chairs=$row["SYS_CHAIRS"];
			$propinas=$row["PROPINAS"];
			$cajero_serv=$row["CAJERO_SERV"];
			$cajero_fiscal=$row["CAJERO_FISCAL"];
			$mancomun=$row["MANCOMUN"];
			
			

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
      $_SESSION["resttendercancel"]=$tendercancel;
      $_SESSION["resttendercants"]=$tendercants;
			$_SESSION["restpropina"]=$propinas;
			$_SESSION["restmancomun"]=$mancomun;
			$_SESSION["restorigin"]=$correo."@".$cliente;
			$_SESSION["restcajerofiscal"]=$cajero_fiscal;
			$_SESSION["tk"]=$tkn;
      if($perfil!="M"){
        header("Location:indexadm.php");
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
      unset($_SESSION["resttendercancel"]);
      unset($_SESSION["resttendercants"]);
      unset($_SESSION["restmancomun"]);
      unset($_SESSION["restcajerofiscal"]);
			unset($_SESSION["resth"]);
			unset($_SESSION["restorigin"]);
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
if($_SESSION["restprofile"]!="M"){
	header("Location:indexadm.php");
}


$serv=$gf->dataSet("SELECT ID_SERVICIO FROM servicio WHERE ESTADO=0");
if(count($serv)==0){
//if(!isset($_SESSION["restservice"]) || $_SESSION["restservice"]==0){
  echo $gf->utf8("
  <div class='alert alert-danger alert-dismissible'>
  <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>x</button>
  <h4><i class='icon fa fa-ban'></i> ATENCI&Oacute;N!</h4>
  No hay un servicio activo, contacta el administrador para activar el servicio
  </div>
  ");
  exit;
}else{
  $_SESSION["restservice"]=$serv[0]["ID_SERVICIO"];
}




?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>RestoFlow | Sistema de Gestión</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta name="description" content="Sistema de gestión para restaurantes RestoFlow">
  <meta name="theme-color" content="#f39c12">
  
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="icon" href="favicon.png" type="image/png">
  <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
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
  <link rel="stylesheet" href="dist/css/colorpick.css">
  <link rel="stylesheet" href="dist/css/custom.css?v=2.0" />
  
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="styles/fuentes.css">
  
  
 


</head>
<body class="hold-transition skin-yellow layout-top-nav">
<div class="wrapper">
<input type='hidden' id='username' value='<?php echo $gf->utf8($_SESSION["restorigin"]);?>' />
<input type='hidden' id='usertoken' value='<?php echo $gf->utf8($_SESSION["tk"]);?>' />
 <header class="main-header">
    <nav class="navbar navbar-static-top">
      <div class="container">
        <div class="navbar-header pull-left">
          <a href="index.php" class="navbar-brand" style="font-weight: 600; letter-spacing: 0.5px;">
            <img src="./dist/img/logorest.png" alt="RestoFlow Logo" style="height:30px; display:inline-block; margin-right:10px; margin-top:-5px;">
          </a>
        </div>

       
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu navbar-right">
          <ul class="nav navbar-nav">

            <!-- User Account Menu -->
            <li class="dropdown user user-menu">
              <!-- Menu Toggle Button -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="transition: all 0.3s ease;">
                <!-- The user image in the navbar-->
                <img src="<?php echo $gf->utf8($_SESSION["restuavatar"])?>" class="user-image" alt="User Image" style="border: 2px solid rgba(255,255,255,0.3);">
                <!-- hidden-xs hides the username on small devices so only the image appears. -->
                <span class="hidden-xs" style="font-weight: 500;"><?php echo $gf->utf8($_SESSION["restuname"])?></span>
              </a>
              <ul class="dropdown-menu">
                <!-- The user image in the menu -->
                <li class="user-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                  <img src="<?php echo $gf->utf8($_SESSION["restuavatar"])?>" class="img-circle" alt="User Image" style="border: 3px solid rgba(255,255,255,0.5);">

                  <p style="text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                    <?php echo $gf->utf8($_SESSION["restuname"])?>
                    <small style="opacity: 0.9;">Mesero</small>
                  </p>
                </li>
               
                <li class="user-footer">
                  <div class="pull-left">
                  <a  href="#perfil_usuario" lnk="Admin/site_profile.php?flag=edit_me" class="btn btn-default btn-flat">
                    <i class="fa fa-user"></i> Perfil
                  </a>
                  </div>
                  <div class="pull-right">
                    <a href="govlogin.php" class="btn btn-danger btn-flat">
                      <i class="fa fa-sign-out"></i> Salir
                    </a>
                  </div>
                </li>
              </ul>
            </li>
          </ul>
        </div>
        <!-- /.navbar-custom-menu -->
      </div>
      <!-- /.container-fluid -->
    </nav>
  </header>
    
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) 
    <section class="content-header">
      <h1>
       <i class='fa  fa-check-circle-o'></i>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Inicio</li>
      </ol>
    </section>
-->
    <!-- Main content -->
    <section class="content" id='contenidos'>
	<div class='row flexbox-centro'>
     <?php
  
	
	$curServ=$gf->dataSet("SELECT ID_SERVICIO FROM servicio WHERE ESTADO=0 AND ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
	if(count($curServ)>0){
		$_SESSION["restservice"]=$curServ[0]["ID_SERVICIO"];
		echo $gf->utf8("<input type='hidden' id='callbackeval' lnk-tsf='#home' lnk-cont='contenidos' value=\"getAux('mviews.php?flag=home')\" />");
	}else{
		echo $gf->utf8("Hola!, no se encontr&oacute; un servicio activo, contacta al administrador para abrir el servicio de hoy");
	}
	?>
	</div>
    </section>
    <section class="content hidden" id='contenidos-aux'>

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Versión</b> 2.0 
      <i class="fa fa-heart text-danger" style="margin: 0 5px;"></i>
    </div>
    <strong>
      <i class="fa fa-copyright"></i> <?php echo date('Y'); ?> RestoFlow
    </strong>
  </footer>
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
<!-- Bootstrap WYSIHTML5 -->
<script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Slimscroll -->
<script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/colorpick.js"></script>
<script src="dist/js/demo.js"></script>
<script src="dist/js/nprogress.js"></script>
<script type="text/javascript" src="./lib/ready.js?v=2" language="javascript"></script>
<script type="text/javascript" src="./lib/tender.js?v=2" language="javascript"></script>
<script type="text/javascript" src="./lib/jquery.form.min.js" language="javascript"></script>
<div class='retroalimenta' style="width:1px;height:1px;overflow:hidden;position:fixed;left:-300px;top:-300px;"><div id="state_proceso" style="width:1px;height:1px;overflow:hidden;position:fixed;left:-300px;top:-300px;"></div><iframe name="if_proc" id="if_proc" width="0" height="0" frameborder="0" scrolling="no"></iframe></div>

<script src="<?php echo SOCKET_SVR?>"></script>
<script>
  <?php
  echo "var server = '".SERVER_URL."';";
  ?>
</script>
<script src="lib/socket.js"></script>
<script>
window.addEventListener("focus", function() { 
   //cargaHTMLvars('contenidos','mviews.php?flag=home');
  location.reload();

});
</script>
<div id="divix"></div>
</body>
</html>