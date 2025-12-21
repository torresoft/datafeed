<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>.:DataFeed:.</title>
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

	<link rel="icon" href="./favicon.ico" type="image/x-icon" />
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
<link rel="stylesheet" href="./styles/fuentes.css">

</head>
<body class="hold-transition login-page" style="background-image:url('./dist/img/login-bg.jpg') !important;background-size:cover;background-position:center;background-attachment:fixed;">
<button id="back-to-top" class="btn btn-danger btn-circle slide" style="position:fixed;left:5px;bottom:5px;display:none;width:30px;height:30px;border-radius:25px;z-index:999;font-size:20px;padding:2px;"><i class='fa fa-arrow-up'></i></button>
<div class="login-box">
  <div class="login-logo text-center">
    <a href="./index.php"><img src="misc/logo.png" style="width:50%;background-color:rgba(0,0,0,0);border-radius:0;" class="img-responsive center-block img-circle" />
	</a>
	<span class="text-center center-block" style='font-size:12px;'><b>Torresoft</b></span>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body" style="background-color:rgba(255,255,255,0.8);-webkit-box-shadow: 2px 8px 48px 6px rgba(0,0,0,0.29);
-moz-box-shadow: 2px 8px 48px 6px rgba(0,0,0,0.29);
box-shadow: 2px 8px 48px 6px rgba(0,0,0,0.29);border-radius:10px;">
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
setcookie("sespersist_dfco","",time()-3600);
setcookie("sespersist_dfius","",time()-3600);
setcookie("sespersist_dfbus","",time()-3600);
?>
</body>
</html>
