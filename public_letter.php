<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>DataFeed</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="icon" href="favicon.ico">
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
  <link rel="stylesheet" href="dist/css/custom.css" />
  <link rel="stylesheet" href="dist/css/animate.css" />
  <link rel="stylesheet" href="dist/css/rippler.min.css" />
  <link href="styles/ripple.min.css" rel="stylesheet">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  



</head>
<body class="hold-transition skin-red layout-top-nav">

<?php
include_once("config.php");
require_once("./lib_php/generalFunctions.class.php");
$gf=new generalFunctions();
$id_site=$gf->cleanVar($_GET["site"]);
$tks=$gf->cleanVar($_GET["dmk"]);

if($id_site>0){
    $serv=$gf->dataSet("SELECT T.NOMBRE, T.CIUDAD, T.LOGO, S.ID_SERVICIO, S.FECHA FROM servicio S JOIN sitios T ON S.ID_SITIO=T.ID_SITIO WHERE T.ID_SITIO='$id_site' AND ID_CLIENT='$tks' AND S.ESTADO=0");
    if(count($serv)>0){
        $id_serv=$serv[0]["ID_SERVICIO"];
        $nombre_rest=$serv[0]["NOMBRE"];
        $ciudad_rest=$serv[0]["CIUDAD"];
        $fecha=$serv[0]["FECHA"];
        $logo=$serv[0]["LOGO"];
    }else{
        echo "Restaurante cerrado";
        exit;
    }
}else{
    echo "Restaurante no valido";
    exit;
}

?>
<header class="main-header">
    <nav class="navbar navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <a href="#" class="navbar-brand"><b>Data</b>FEED</a>
          <a href="#" class="navbar-brand"><?php echo $gf->utf8($nombre_rest)?></a>
         
        </div>
      </div>
    </nav>
  </header>
  <div class="content-wrapper">
      <div class="container">
      <section class="content-header">
        <h1>
          Carta Elect&oacute;nica <?php echo $fecha?>
        </h1>
      </section>
      <section class="content">
<?php

$rsOptions=$gf->dataSet("SELECT P.ID_PLATO, PC.ID_RACION, PC.NOMBRE AS RACION, RO.ID_OPCION, RO.NOMBRE  AS OPCION FROM platos AS P JOIN servicio_oferta SO ON SO.ID_PLATO=P.ID_PLATO JOIN platos_composicion PC ON PC.ID_PLATO=P.ID_PLATO JOIN racion_opciones RO ON RO.ID_RACION=PC.ID_RACION WHERE SO.ID_SERVICIO=:servicio AND RO.ESTADO=1 AND P.ESTADO=1 ORDER BY P.ID_PLATO, PC.POSITION",array(":servicio"=>$id_serv));
$lasopciones=array();
foreach($rsOptions as $rwOptions){
    $r_idplato=$rwOptions["ID_PLATO"];
    $r_idracion=$rwOptions["ID_RACION"];
    $r_nmracion=$rwOptions["RACION"];
    $r_idopion=$rwOptions["ID_OPCION"];
    $r_nmopcion=$rwOptions["OPCION"];
    
    $lasopciones[$r_idplato][$r_idracion]["nm"]=$r_nmracion;
    $lasopciones[$r_idplato][$r_idracion]["op"][$r_idopion]=$r_nmopcion;
        
}
$camprice="PRECIO";

$resultChair = $gf->dataSet("SELECT P.ID_PLATO, CAT.ID_CATEGORIA, CAT.NOMBRE AS CATEGORIA, CAT.ICONO, P.NOMBRE AS PLATO, P.$camprice AS PRECIO, P.TIPO_PLATO, P.PRECIO_EDITABLE FROM platos_categorias AS CAT JOIN platos AS P ON(CAT.ID_CATEGORIA=P.ID_CATEGORIA AND P.ID_PLATO IN(SELECT ID_PLATO FROM servicio_oferta WHERE ID_SERVICIO=:servicio)) WHERE CAT.ID_SITIO=:sitio AND CAT.ESTADO='1' AND P.ESTADO='1' GROUP BY P.ID_PLATO ORDER BY CAT.POSITION, P.NOMBRE",array(":servicio"=>$id_serv,":sitio"=>$id_site));

$arplati=array();
if(count($resultChair)>0){

    $inicat="";
    foreach($resultChair as $rowChair){
        $id_categoria=$rowChair["ID_CATEGORIA"];
        $nombre_categoria=$rowChair["CATEGORIA"];
        $id_plato=$rowChair["ID_PLATO"];
        $icono=$rowChair["ICONO"];
        $nombre_plato=$rowChair["PLATO"];
        $precio=$rowChair["PRECIO"];
        $tpz=$rowChair["TIPO_PLATO"];
        $editprice=$rowChair["PRECIO_EDITABLE"];
        $arplati[$id_categoria]["nm"]=$nombre_categoria;
        $arplati[$id_categoria]["ic"]=$icono;
        $arplati[$id_categoria]["pl"][$id_plato]["nm"]=$nombre_plato;
        $arplati[$id_categoria]["pl"][$id_plato]["pz"]=$precio;
        $arplati[$id_categoria]["pl"][$id_plato]["tp"]=$tpz;
        $arplati[$id_categoria]["pl"][$id_plato]["ed"]=$editprice;
    }

    echo $gf->utf8("
    <div class='row'>
        <div class='col-md-12'>

            <input type='text' class='form-control' placeholder='Buscar producto...' id='tr_pedidos_items' onkeyup=\"filtrarTr('tr_pedidos_items','flt_tr_items');validaBtnErase()\" />
            
            <button id='erasesearch' onclick=\"javascript:$('#tr_pedidos_items').val('');filtrarTr('tr_pedidos_items','flt_tr_items');$('#tr_pedidos_items').focus()\" class='btn btn-sm btn-warning' style='position:fixed;top:60px;left:180px;display:none;'><i class='fa fa-remove'></i>
            </button>
        </div>
    </div>
    <br />
    <div class='row'>
    <div class='col-md-12'>
    <div class='box-group' id='accordeon_items'>");	
    $npl=1;
    $expanded="true";
    $firscat="";
    foreach($arplati as $id_categoria=>$info_cat){
        $nplcat=0;
        $nombre_categoria=str_pad($info_cat["nm"], 30, ' ', STR_PAD_RIGHT);
        $icono=$info_cat["ic"];
        echo $gf->utf8("<div class='panel box box-default flt_tr_items'>
        <div class='box-header'>
        <h4 class='box-title' style='font-size:28px;width:100%;font-weight:bold;'>
        <a data-toggle='collapse' data-parent='#accordeon_items' id='clikat_$id_categoria' href='#collapse_cat_$id_categoria' class='text-center' style='width:100%;' aria-expanded='$expanded'>
            $nombre_categoria <i class='fa  fa-caret-square-o-down pull-right'></i>
        </a>
        
            </h4>
        </div>
        <div id='collapse_cat_$id_categoria' class='panel-collapse collapse non-transition animated fadeIn' aria-expanded='$expanded'>
        <div class='box-body'><ul class='list-group'>");
        $arplt=$info_cat["pl"];
        foreach($arplt as $id_plato=>$info_pla){
            
        
            $nombre_plato=$info_pla["nm"];
            $tipl=$info_pla["tp"];
            $edpz=$info_pla["ed"];
            $precio=number_format($info_pla["pz"],0);
            $precio_nc=$info_pla["pz"];
            if($id_plato!=""){
                if($npl%2>0){
                    $classe="bg-danger";
                    $classe2="bg-grey1";
                }else{
                    $classe="bg-success";
                    $classe2="bg-grey2";
                }

                
                $input_prize="<small class='pull-right blue'>$ $precio</small>";
                
                echo $gf->utf8("<li class='flt_tr_items $classe cattie_$id_categoria list-group-item item-warning clearfix'><b style='color:#405271;'>$nombre_plato </b>$input_prize");
                $nra=0;
                $opciones="";
                if(isset($lasopciones[$id_plato])){
                    foreach($lasopciones[$id_plato] as $id_rac=>$gopcion){
                        $nm_racion=$gopcion["nm"];
                        
                        if(count($gopcion["op"])>1){
                            $opciones.="<br /><b>".$nm_racion.": </b>";
                            foreach($gopcion["op"] as $iop=>$namop){
                                $opciones.="$namop, ";
                            }
                            $opciones=substr($opciones,0,-2);
                            
                        }
                    }
                }
  
                echo $gf->utf8("$opciones</li>");
                
                $npl++;
            }
            $nplcat++;
        }
        echo $gf->utf8("</ul></div></div></div>");
        
    }
    echo $gf->utf8("</div></div></div>

    ");
}else{
    echo $gf->utf8("No se han activado los productos disponibles para el servicio actual");
}


?>
      </section>
      </div>
  </div>
<footer class="main-footer">
    <div class="container">
      <div class="pull-right hidden-xs">
        <b>Version</b> 2.0
      </div>
      <strong>Copyright © 2020 <a href="https://www.torresoft.co">Torresoft SAS</a>.<br /></strong>Derechos reservados.
    </div>
    <!-- /.container -->
  </footer>
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
<script src="lib/ready.js"></script>
</body>
</html>