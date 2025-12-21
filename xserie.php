<?php
date_default_timezone_set("America/Bogota");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>.:DataFeed:. Serial...</title>
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

<link rel="stylesheet" href="styles/fuentes.css" />

</head>
<body class="hold-transition login-page" style="background-image:url('./dist/img/login-bg.jpg') !important;background-size:cover;background-position:center;background-attachment:fixed;">
<?php
if(!isset($_POST["xserie"])){
    $id=isset($_GET["id"]) ? $_GET["id"] : 0;
    if($id>0){
      $cond="ID_SITIO='$id'";
    }else{
      $cond="1";
    }
    include_once("config.php");
    require_once("./lib_php/generalFunctions.class.php");
    $gf=new generalFunctions;
    $resultxx = $gf->dataSet("SELECT ID_SITIO, ID_CLIENT, NOMBRE AS EMPRESA, AUTOGEST, IMPUESTO_INCLUIDO, TENDER_CANCEL, CAJERO_SERV, CAJERO_FISCAL, MANCOMUN, TENDER_CANTS, ANTICIPOS, PRINTCONFIRM, MODO_RAPIDO FROM sitios WHERE $cond",array()); 
    $numreg3=count($resultxx);
    if($numreg3>0){
        $row22=$resultxx[0];
        $ID_EMPRESA=$row22["ID_SITIO"];
        $EMPRESA=$row22["EMPRESA"];
        $CLIENTE=$row22["ID_CLIENT"];
        $serie=$gf->xifmaxY($CLIENTE,$ID_EMPRESA);
    }
?>
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
    <p class="login-box-msg">SE REQUIERE UN SERIAL</p>

    <form action="./xserie.php?id=<?php echo $id ?>" method="post">
      <div class="form-group has-feedback">
        <input type="text" name="xserie" class="form-control" placeholder="XXXX-XXXX-XXXX-XXXX" required>
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      
      <div class="row">
 
        <!-- /.col -->
        <div class="col-xs-12">
          <button type="submit" class="btn btn-danger btn-block btn-flat">Validar</button>
        </div>
        <!-- /.col -->
      </div>
    </form>

    
    <!-- /.social-auth-links -->
<hr />
    
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
<?php
}else{
    $DADO=$_POST["xserie"];
    $id=isset($_GET["id"]) ? $_GET["id"] : 0;
    if($id>0){
      $cond="ID_SITIO='$id'";
    }else{
      $cond="1";
    }
    echo "<h1>Validando.......</h1><br />";
    include_once("config.php");
    require_once("./lib_php/generalFunctions.class.php");
    $gf=new generalFunctions;
    $resultxx = $gf->dataSet("SELECT ID_SITIO, ID_CLIENT, NOMBRE AS EMPRESA, AUTOGEST, IMPUESTO_INCLUIDO, TENDER_CANCEL, CAJERO_SERV, CAJERO_FISCAL, MANCOMUN, TENDER_CANTS, ANTICIPOS, PRINTCONFIRM, MODO_RAPIDO FROM sitios WHERE $cond",array()); 
    $numreg3=count($resultxx);
    if($numreg3>0){
        $row22=$resultxx[0];
        $ID_EMPRESA=$row22["ID_SITIO"];
        $EMPRESA=$row22["EMPRESA"];
        $CLIENTE=$row22["ID_CLIENT"];
        $serie=$gf->xifmaxY($CLIENTE,$ID_EMPRESA);
        if($DADO==$serie){
            echo "<h1>Producto validado.......</h1><br />
            <META HTTP-EQUIV='REFRESH' CONTENT='2;URL=login.php'>";
            $gf->dataIn("UPDATE sitios SET GPS='$DADO' WHERE ID_SITIO='$ID_EMPRESA'");
        }else{
            echo "<h1>No se puede validar el serial del producto.......$DADO -> </h1><br /><a href='login.php'>REGRESAR</a>";
        }
    }else{
        echo "<h1>No se encuentra una instalacion disponible.......</h1><br /><META HTTP-EQUIV='REFRESH' CONTENT='2;URL=login.php'>";
    }
}
?>
</body>
</html>
