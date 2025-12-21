<?php 
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"])){
	include_once("../config.php");
	require_once("../lib_php/generalFunctions.class.php");
	require_once("../lib_php/relations.php");
	$gf=new generalFunctions;
	global $relaciones;
	$flag=$gf->cleanVar($_GET["flag"]);
	if($flag=="changepass"){
		$oldpass=$_POST["oldpass"];
		$newpass=$_POST["newpass"];
		$id_usuario=$_POST["id_usuario"];
		$rsUs=mysqli_query($link,"SELECT ID_USUARIO FROM usuarios WHERE ID_USUARIO='$id_usuario' AND PASSWORD=MD5('$oldpass')");
		if($row=mysqli_fetch_array($rsUs)){
			$sentencia="UPDATE usuarios SET PASSWORD=md5('$newpass') WHERE ID_USUARIO='$id_usuario'";
			$res=mysqli_query($link, $sentencia) or die("Error: (" . mysqli_errno($link) . ") " . mysqli_error($link));
			if($res){
				echo "ok";
			}else{
				echo "bad2";
			}
		}else{
			echo "bad1";	
		}
	}elseif($flag=="sdl"){
		$tb=$_POST["tb"];
		$ky=$_POST["ky"];
		$kv=$_POST["kv"];
		
		$QR="DELETE FROM $tb WHERE $ky='$kv'";
		if($gf->dataIn($QR)){
			echo 1;
		}else{
			echo 0;
		}
	}elseif($flag=="conftam"){
		$witables=$_POST["witables"];
		$_SESSION["witables"]=$witables;

	}elseif($flag=="qupcamp"){
		$tbl=$_POST["tabla"];
		$pkey=$relaciones[$tbl]["pkey"];
		$vkey=$_POST["vkey"];
		$cval=$_POST["cval"];
		$val=$_POST["valor"];
		$enc=$_POST["enc"];
		if($enc==1){
			$val=$gf->encriptar($val);
		}
		$ok=$gf->dataIn("UPDATE $tbl SET $cval='$val' WHERE $pkey='$vkey'");
		echo $ok;
	}
	
}
?>