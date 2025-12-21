<?php
session_start();
date_default_timezone_set("America/Bogota");
error_reporting (E_ALL ^ E_NOTICE);
if(isset($_SESSION["restbusstyle"])){
	$tema=$_SESSION["restbusstyle"];
	$namebus=$_SESSION["restbusname"];
}else{
	$tema="cupertino";
	$namebus="(INICIAR)";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="https://www.facebook.com/2008/fbml">
	<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>DATAREST - <?php echo $namebus ?></title>
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.4/themes/<?php echo $tema; ?>/jquery-ui.css" type="text/css" />
	<link rel="shortcut icon" href="misc/dar.ico" />
	<link rel="stylesheet" href="./styles/custom.css" type="text/css" />
	<link rel="stylesheet" href="./styles/jquery.dataTables.css" type="text/css" />
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.2.js" language="javascript"></script>
	<script type="text/javascript" src="http://code.jquery.com/ui/1.10.4/jquery-ui.js" language="javascript"></script>
	<script type="text/javascript" src="./lib/ready.js" language="javascript"></script>
	<script type="text/javascript" src="./lib/visuals.js" language="javascript"></script>
	<script src="lib/ui.datepicker-es.js" type="text/javascript"></script>
    <script src="lib/jquery.timepicker.js" type="text/javascript"></script>
	<script src="lib/jquery.dataTables.min.js" type="text/javascript"></script>
	
	</head>
    <body style='background:url(misc/bg2.jpg);'>
    <?php
	include_once("config.php");
	require_once("./lib_php/generalFunctions.class.php");
	$gf=new generalFunctions;
	if(isset($_SESSION["restuiduser"]) && $gf->isUserAdm($_SESSION["restuiduser"],$_SESSION["tk"]) && $_SESSION["restprofile"]=="M"){
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$browser = substr("$browser", 25, 8);
		if ($browser != "MSIE 6.0"){

			if($_SESSION["restori"]=="v"){
				$wim=40;
				$hem=round((40/$_SESSION["restw"])*$_SESSION["resth"]);
			}else{
				$hem=40;
				$wim=round((40/$_SESSION["resth"])*$_SESSION["restw"]);
			}
			
	?>    
			<div class='retroalimenta'><div id="state_proceso" style="width:1px;height:1px;overflow:hidden;position:fixed;left:-300px;top:-300px;"></div><iframe name="if_proc" id="if_proc" width="0" height="0" frameborder="0" scrolling="no"></iframe></div>  
   	 
     
			<div id='workspace'>
			
			
			</div>
			<!-- cierra workspace -->
  
    <?php
		}else{
	?>
			<div style="margin:auto;width:500px;height:500px; font-family:'Courier New', Courier, monospace; text-align:center;">
				<p>Oye!, tu navegador fue programado el milenio pasado y no cumple con las especificaciones m&iacute;nimas para este sitio web</p>
				<p>Descarga uno de estos navegadores(<a href="http://www.microsoft.com/latam/windows/internet-explorer/">Internet Explorer 7-8-9</a>, <a href="http://es-es.www.mozilla.com/es-ES/">Mozilla Firefox 3-5-6</a>, <a href="http://www.google.com/chrome/">Google Chrome</a>, <a href="http://www.opera.com/">Opera</a>)</p>
				<p>Disculpa las molestias, es para que tengas una mejor experiencia en nuestro sitio</p>
			</div>
    <?php
		}
		
	}else{
	?>
		<div id="getonline" ins='1' style="width:360px;height:300px;position:absolute;bottom:0px;left:200px;display:none;" class="ui-widget ui-corner-all ui-widget-content ui-shadow">
		<div class="ui-widget-header ui-corner-all">INICIO DE SESI&Oacute;N</div>
			<form action="restlogin.php" method="post" style='margin:10px;'>
			<table width="100%" align="center">
			<tr>
				<td align='right'>Usuario</td>
				<td><input type="text" style='width:180px;' name="namerestuser" id="namerestuser" /></td>
			</tr>
			<tr>
				<td align='right'>Contrase&ntilde;a</td>
				<td><input type="password" name="namerestpass" id="namerestpass" style='width:180px;' /></td>
			</tr>
			<tr><td colspan="2" align="center"><hr /><input type="submit" onclick="goOnline()" class="jq" value="Iniciar Sesi&oacute;n" /></td></tr></table></form>
		</div>
    
    <?php
	
	}
	?>
	<div id='wait_bar' style='width:100%;height:100%;position:fixed;left:0px;top:0px;display:none;text-align:center;z-index:9999999999999;' class='ui-semiblack'><img class='ui-widget-content ui-corner-all' id='imgload' src='misc/loader.gif' alt='Cargando...' title='Cargando...' align='left' /><div id='msgload' class='ui-widget ui-semiblack ui-corner-all' style='width:222px;height:22px;font-size:12px !important;'>cargando...</div></div>
</body>
</html>
