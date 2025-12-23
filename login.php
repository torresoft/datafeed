<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>.:RestoFlow:.</title>
  <meta name="apple-mobile-web-app-title" content="La Virginia Digital">
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="./bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="./bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="./bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="./dist/css/AdminLTE.min.css">
  <!-- iCheck -->

	<link rel="icon" href="./favicon.png" type="image/png" />
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
<link rel="stylesheet" href="./styles/fuentes.css">
<style>
/* Estilos de Login con tema Fuego */
@keyframes fireFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

@keyframes fireGlowPulse {
    0%, 100% { box-shadow: 0 0 20px rgba(255,152,0,0.3), 0 0 40px rgba(255,87,34,0.2), 0 10px 60px rgba(0,0,0,0.3); }
    50% { box-shadow: 0 0 30px rgba(255,152,0,0.5), 0 0 60px rgba(255,87,34,0.4), 0 15px 80px rgba(0,0,0,0.4); }
}

body.login-page {
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%) !important;
    position: relative;
    overflow-x: hidden;
}

body.login-page::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('./dist/img/login-bg.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    opacity: 0.08;
    z-index: 0;
}

.login-box {
    position: relative;
    z-index: 1;
    animation: fireFloat 6s ease-in-out infinite;
}

.login-logo {
    margin-bottom: 25px;
}

.login-logo img {
    filter: brightness(1.1) drop-shadow(0 4px 12px rgba(0,0,0,0.8)) drop-shadow(0 0 20px rgba(255,255,255,0.3));
    transition: all 0.3s ease;
}

.login-logo img:hover {
    filter: brightness(1.15) drop-shadow(0 6px 15px rgba(0,0,0,0.9)) drop-shadow(0 0 30px rgba(255,152,0,0.4));
    transform: scale(1.05);
}

.login-logo span {
    color: #fff !important;
    text-shadow: 0 0 10px rgba(255,152,0,0.8), 0 2px 4px rgba(0,0,0,0.8);
    font-size: 14px !important;
    letter-spacing: 2px;
}

.login-box-body {
    background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,248,240,0.95) 100%) !important;
    border-radius: 15px !important;
    border: 2px solid rgba(255,152,0,0.3);
    animation: fireGlowPulse 4s ease-in-out infinite;
    backdrop-filter: blur(10px);
}

.login-box-msg {
    font-size: 16px;
    font-weight: 600;
    color: #ff5722;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.form-control {
    border: 2px solid rgba(255,152,0,0.3);
    border-radius: 8px;
    padding: 12px 15px 12px 40px;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.9);
}

.form-control:focus {
    border-color: #ff5722;
    box-shadow: 0 0 0 0.2rem rgba(255,87,34,0.25), 0 0 15px rgba(255,152,0,0.3);
    background: #fff;
}

.form-control-feedback {
    color: #ff9800;
    font-size: 18px;
}

.btn-danger {
    background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%) !important;
    border: none !important;
    border-radius: 8px;
    padding: 12px;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 4px 15px rgba(255,87,34,0.4);
    transition: all 0.3s ease;
    color: #fff !important;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.btn-danger:hover {
    background: linear-gradient(135deg, #ff5722 0%, #f44336 100%) !important;
    box-shadow: 0 6px 20px rgba(244,67,54,0.5);
    transform: translateY(-2px);
}

.btn-danger:active {
    transform: translateY(0px);
}

.btn-default {
    background: rgba(255,255,255,0.8);
    border: 1px solid rgba(255,152,0,0.3);
    color: #ff5722;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.btn-default:hover {
    background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
    border-color: #ff5722;
    color: #fff !important;
    box-shadow: 0 3px 10px rgba(255,87,34,0.3);
}

#back-to-top {
    background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%) !important;
    border: none !important;
    box-shadow: 0 4px 10px rgba(255,87,34,0.4) !important;
}

#back-to-top:hover {
    background: linear-gradient(135deg, #ff5722 0%, #f44336 100%) !important;
    box-shadow: 0 6px 15px rgba(244,67,54,0.5) !important;
}

hr {
    border-color: rgba(255,152,0,0.2);
}
</style>

</head>
<body class="hold-transition login-page">
<button id="back-to-top" class="btn btn-danger btn-circle slide" style="position:fixed;left:5px;bottom:5px;display:none;width:30px;height:30px;border-radius:25px;z-index:999;font-size:20px;padding:2px;"><i class='fa fa-arrow-up'></i></button>
<div class="login-box">
  <div class="login-logo text-center">
    <a href="./index.php"><img src="dist/img/logorest.png" style="width:80%;background-color:rgba(0,0,0,0);border-radius:0;" class="img-responsive center-block img-circle" />
	</a>
	<span class="text-center center-block"><b>Torresoft</b></span>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">Inicia sesi&oacute;n en el sistema</p>

    <form action="./govlogin.php" method="post">
      <div class="form-group has-feedback">
        <input type="text" name="namerestuser" class="form-control" placeholder="usuario@restaurante" required>
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      
      <div class="form-group has-feedback">
        <input type="password" name="namerestpass" class="form-control" placeholder="Contraseña" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
 
        <!-- /.col -->
        <div class="col-xs-12">
          <button type="submit" class="btn btn-danger btn-block btn-flat">Ingresar</button>
        </div>
        <!-- /.col -->
      </div>
    </form>

    
    <!-- /.social-auth-links -->
<hr />
    <button class="btn btn-default btn-xs pull-left" onclick="getDialog('recpass.php?a=2')">Olvid&eacute; mi contraseña</button>  
<br /><hr />
	

  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->


<!-- jQuery 3 -->
<script src="./bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="./bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script>
localStorage.clear();
</script>
<?php
@setcookie("sespersist_dfco","",time()-3600);
@setcookie("sespersist_dfius","",time()-3600);
@setcookie("sespersist_dfbus","",time()-3600);
?>
</body>
</html>
