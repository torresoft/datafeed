<?php
date_default_timezone_set("America/Bogota");
error_reporting (E_ALL ^ E_NOTICE);
session_start();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>DataFeed Iniciando...</title>
  <!-- Tell the browser to be responsive to screen width -->
  
    <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="./bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="./bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="./bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="./dist/css/AdminLTE.min.css">
  
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="stylesheet" href="./styles/custom.css" type="text/css" />
   <!-- jQuery 3 -->
	<script src="bower_components/jquery/dist/jquery.min.js"></script>
	<!-- jQuery UI 1.11.4 -->
	<script src="bower_components/jquery-ui/jquery-ui.min.js"></script>
	<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
</head><body style="background:url(../misc/bg.jpg) no-repeat right bottom fixed;">

<div class='ui-widget ui-widget-content' style='width:320px;height:350px;margin-left:auto;margin-right:auto;margin-top:100px;background:#FFF;'><table align='center' width='100%'><tr><td align='center'><img src='./misc/logo.png' style="width:50%" /></td></tr><tr><td align='center'><img src='load.gif' align='center' /><hr />
<?php
	include_once("./config.php");
	require_once("./lib_php/generalFunctions.class.php");
	$gf=new generalFunctions;
	if(isset($_POST["namerestuser"])){
		//echo "<p align='center'><span class='msjcampo' align='center'><img src='wait.gif' align='absmiddle' />Autenticando...</span></p>";
		$usuario_origin=$_POST["namerestuser"];
		$usuario=$_POST["namerestuser"];
		$clave=$_POST["namerestpass"];
		$aruser=explode("@",$usuario);
		
		if($usuario!="" && $clave!="" && count($aruser)>1){
			$sha_pass=hash('sha512',$clave);
			$usuario=$aruser[0];
			$clientid=$aruser[1];
			if($clientid!="datafeed"){
				$result = $gf->dataSet("SELECT U.ID_USUARIO, E.ID_SITIO, E.LOGO, E.NOMBRE AS EMPRESA, E.PROPINAS, E.AUTOGEST,E.ANTICIPOS, E.IMPUESTO_INCLUIDO, E.SYS_CHAIRS, E.ECOMANDA, E.MANCOMUN, E.TENDER_CANCEL, E.TENDER_CANTS, E.CAJERO_SERV, E.CAJERO_FISCAL, E.SYS_ACCORDION, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS NOMBRE, U.CORREO, U.PERFIL, U.AVATAR, U.WITABLES FROM usuarios AS U, sitios AS E WHERE U.ESTADO='1' AND U.ID_SITIO=E.ID_SITIO AND U.CORREO=:correo AND E.ID_CLIENT=:clientid AND U.PASSWORD=:keyss AND U.PASSWORD<>'' AND E.ESTADO=1",array(":correo"=>$usuario,"clientid"=>$clientid,":keyss"=>$sha_pass)); 
				$numreg=count($result);
				if($numreg>0){
					$row=$result[0];
					$nombre=$row["NOMBRE"];
					$id_user=$row["ID_USUARIO"];
					$id_empresa=$row["ID_SITIO"];
					$empresa=$row["EMPRESA"];
					$correo=$row["CORREO"];
					$estilo=$row["IMPUESTO_INCLUIDO"];
					$witables=$row["WITABLES"];
					$perfil=$row["PERFIL"];
					$autogest=$row["AUTOGEST"];
					$ecomanda=$row["ECOMANDA"];
					$syschairs=$row["SYS_CHAIRS"];
					$tendercants=$row["TENDER_CANTS"];
					$tendercancel=$row["TENDER_CANCEL"];
					$restaccordion=$row["SYS_ACCORDION"];
					$cajero_serv=$row["CAJERO_SERV"];
					$propinas=$row["PROPINAS"];
					$cajero_fiscal=$row["CAJERO_FISCAL"];
					$avatar=$row["AVATAR"];
					$mancomun=$row["MANCOMUN"];
					$anticipos=$row["ANTICIPOS"];
					if($avatar=="" || !file_exists($avatar)){
						if($perfil=="A"){
							$avatar="misc/default_avatar.png";
						}elseif($perfil=="D"){
							$avatar="misc/default_avatar.png";
						}elseif($perfil=="C"){
							$avatar="misc/chef.png";
						}elseif($perfil=="J"){
							$avatar="misc/casher.png";
						}elseif($perfil=="M"){
							$avatar="misc/waiter.png";
						}
					}
					$logo=$row["LOGO"];
					$fg=date("Ymd").rand(1111,999999);
					$tok=$gf->enY($clave).$fg;
					$_SESSION["tk"]=$tok;
					//if($perfil=="M"){
					setcookie('sespersist_dfco', $tok, time() + 365 * 24 * 60 * 60);
					setcookie('sespersist_dfius', $id_user, time() + 365 * 24 * 60 * 60);
					setcookie('sespersist_dfbus', $id_empresa, time() + 365 * 24 * 60 * 60);
				//	}
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
					$_SESSION["restanticipos"]=$anticipos;
					$_SESSION["resttendercancel"]=$tendercancel;
					$_SESSION["resttendercants"]=$tendercants;
					$_SESSION["restchairs"]=$syschairs;
					$_SESSION["restaccordion"]=$restaccordion;
					$_SESSION["restpropina"]=$propinas;
					$_SESSION["restcajeroserv"]=$cajero_serv;
					$_SESSION["restmancomun"]=$mancomun;
					$_SESSION["restcajerofiscal"]=$cajero_fiscal;
					$_SESSION["restorigin"]=$usuario_origin;
					$_SESSION["restfastmode"]=0;
			
					
					$_SESSION["restprofile"]=$perfil;
					$gf->dataIn("UPDATE usuarios SET TOKEN='$tok' WHERE ID_USUARIO='$id_user'");
					$gf->log($_SESSION["restbus"],0,0,"INICIO DE SESION",$_SESSION["restuiduser"]);
					if($perfil=="A" || $perfil=="D"){
						if($perfil=="A"){
							$perfile="ADMINSITRADOR";
						}else{
							$perfile="SUPER USUARIO (DUE&Ntilde;O)";
						}
						
						$callback="indexadm.php'";
					}elseif($perfil=="C"){
						$perfile="CHEF";
						$callback="indexchef.php";
					}elseif($perfil=="J"){
						$perfile="CAJERO";
						$callback="indexadm.php";
					}elseif($perfil=="M"){
						$perfile="WAITER";
						$callback="index.php";
					}elseif($perfil=="T"){
						$perfile="CONTADOR";
						$callback="indexadm.php";
					}else{
						unset($_SESSION["restuname"]);
						unset($_SESSION["restumail"]);
						unset($_SESSION["restutipo"]);
						unset($_SESSION["restuavatar"]);
						unset($_SESSION["restuiduser"]);
						unset($_SESSION["tk"]);
						unset($_SESSION["restorigin"]);
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
						unset($_SESSION["restautogest"]);
						unset($_SESSION["restecomanda"]);
						unset($_SESSION["resttendercancel"]);
						unset($_SESSION["resttendercants"]);
						unset($_SESSION["restcajeroserv"]);
						unset($_SESSION["restchairs"]);
						unset($_SESSION["restaccordion"]);
						unset($_SESSION["restcajerofiscal"]);
						unset($_SESSION["restpropina"]);
						unset($_SESSION["resth"]);
						unset($_SESSION["witables"]);
						unset($_SESSION["restmancomun"]);
						unset($_COOKIE["sespersist_dfco"]);
						unset($_COOKIE["sespersist_dfius"]);
						unset($_COOKIE["sespersist_dfbus"]);
						setcookie("sespersist_dfco","",time()-3600);
						setcookie("sespersist_dfius","",time()-3600);
						setcookie("sespersist_dfbus","",time()-3600);
						$callback="login.php";
						
					}
					$_SESSION["restuavatar"]=$avatar;
					echo $gf->utf8("
					<input type='hidden' id='username' value='$usuario_origin' />
					<input type='hidden' id='usertoken' value='$tok' />
					<table align='center' style='font-size:11px;'><tr><td align='center'>
					<img src='$logo' width='30' height='30' align='center' /></td><td>
					$empresa</td></tr>
					<tr><td>
					<img src='$avatar' width='35' height='35' align='left' /></td><td>
					<b>$nombre</b><br />
					$perfile
					<input type='hidden' id='callbacker' value='$callback' />
					</td></tr></table>");
					
					
				}else{
				
					echo "<p align='center'><span class='msjcampo' align='center'><img src='wait.gif' align='absmiddle' />Los datos de acceso no coinciden...</span></p><input type='hidden' id='callbacker' value='login.php' /></p>";
				
				}
			}else{
				$result = $gf->dataSet("SELECT U.ID_USUARIO, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS NOMBRE, U.CORREO, U.PERFIL, U.WITABLES FROM usuarios AS U WHERE U.ESTADO='1' AND U.CORREO=:correo AND U.PASSWORD=:keyss AND U.PASSWORD<>'' AND U.PERFIL='Z' AND U.ID_SITIO=0",array(":correo"=>$usuario."@".$clientid,":keyss"=>$sha_pass));
			
				$numreg=count($result);
				if($numreg>0){
					$row=$result[0];
					$nombre=$row["NOMBRE"];
					$id_user=$row["ID_USUARIO"];
					$empresa="DATAFEED";
					$correo=$row["CORREO"];
					$estilo=0;
					$witables=0;
					$perfil=$row["PERFIL"];
					
					$_SESSION["restuname"]=$nombre;
					$_SESSION["restumail"]=$correo;
					
					$_SESSION["restuiduser"]=$id_user;
					$_SESSION["restbus"]=0;
					$_SESSION["restbusname"]=$empresa;
					$_SESSION["restuavatar"]="misc/logo.png";
					$_SESSION["restbuslogo"]="misc/logo.png";
					$_SESSION["witables"]=0;
					$_SESSION["restbusstyle"]=0;
					$_SESSION["restautogest"]=0;
					$_SESSION["restecomanda"]=0;
					$_SESSION["restchairs"]=0;
					$_SESSION["restaccordion"]=0;
					$_SESSION["resttendercancel"]=0;
					$_SESSION["resttendercants"]=0;
					$_SESSION["restpropina"]=0;
					$_SESSION["restcajeroserv"]=0;
					$_SESSION["restmancomun"]=0;
					$_SESSION["restcajerofiscal"]=0;
					$_SESSION["restprofile"]="Z";
					$_SESSION["restorigin"]="K";
					$_SESSION["tk"]="ADMUSERDATAFEEEDAK";
					echo $gf->utf8("$nombre<br />Iniciando...<input type='hidden' id='callbacker' value='indesh.php' />");
				}else{
					echo "<p align='center'><span class='msjcampo' align='center'><img src='wait.gif' align='absmiddle' />Los datos de acceso no coinciden...</span></p><input type='hidden' id='callbacker' value='login.php' /></p>";
				}
			}
		}else{
			echo "<p align='center'><span class='msjcampo' align='center'><img src='wait.gif' align='absmiddle' />Los datos de acceso no coinciden...</span></p><input type='hidden' id='callbacker' value='login.php' /></p>";
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
		unset($_SESSION["restchairs"]);
		unset($_SESSION["restaccordion"]);
		unset($_SESSION["resttendercancel"]);
		unset($_SESSION["resttendercants"]);
		unset($_SESSION["restcajeroserv"]);
		unset($_SESSION["restcajerofiscal"]);
		unset($_SESSION["resth"]);
		unset($_COOKIE["sespersist_dfco"]);
		unset($_COOKIE["sespersist_dfius"]);
		unset($_COOKIE["sespersist_dfbus"]);
		setcookie("sespersist_dfco","",time()-3600);
		setcookie("sespersist_dfius","",time()-3600);
		setcookie("sespersist_dfbus","",time()-3600);
		echo "Cerrando sesi&oacute;n...<input type='hidden' id='callbacker' value='login.php' />";
	}
	
	

?>
</td></tr></table></div>
<!-- jQuery 3 -->
<script src="./bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="./bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

</body>
<?php
if(isset($_POST["namerestuser"])){
?>
<script>
	$(document).ready(function(){
		//let server="https://www.torresoft.co:3443";
		//let server="http://192.168.1.65:3000";
		let server="http://192.168.43.40:3000/datafeed";
		let endpoint="/user/auth";

		let username=$("#username").val();
		let usertoken=$("#usertoken").val();
		let callb=$("#callbacker").val();
		let params={username:username,password:usertoken};
		$.ajax({
			url : server+endpoint,
			type: "POST",
			data : params,
			timeout:5000,
			headers:{},
			success: function(data, textStatus, jqXHR)
				{
				
					if(data.status=="goin"){
						localStorage.setItem("sockets",1);
						localStorage.setItem("rs_token",data.token);
						localStorage.setItem("rs_resudi",data.id_user);
						localStorage.setItem("rs_ssubdi",data.id_bus);
					}else{
						localStorage.setItem("sockets",0);
					}
					location.href=callb;
				},
				error: function (jqXHR, textStatus, errorThrown)
				{
					localStorage.setItem("sockets",0);
					location.href=callb;
					console.log(textStatus+":"+errorThrown);
				}
		});
	})
	
</script>
<?php
}else{
?>
<script>
	$(document).ready(function(){
		let callb=$("#callbacker").val();
		location.href=callb;
	})
</script>
<?php
}
?>
</html>