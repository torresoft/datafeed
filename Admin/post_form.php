<?php
session_start();
require_once("../autoload.php");
$gf=new generalFunctions;
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="Z" || $_SESSION["restprofile"]=="J")){
	$ent=$gf->cleanVar($_GET["ent"]);
	$qry=$gf->cleanVar($_GET["qry"]);
	if($qry=="in"){
		$camps="(";
		$vals="(";
		foreach ($_POST as $key => $value) {
			$camps.="$key,";
			$value=str_replace("data:image/png;base64,","|||***png***|||",$value);
			$value=str_replace("data:image/jpg;base64,","|||***jpg***|||",$value);
			$value=str_replace("data:image/jpeg;base64,","|||***jpeg***|||",$value);
			$vals.="'$value',";
		}
		$camps=substr($camps,0,strlen($camps)-1).")";
		$vals=substr($vals,0,strlen($vals)-1).")";
		$query="INSERT INTO $ent $camps VALUES $vals";
	}else{
		$ky=$gf->cleanVar($_GET["ky"]);
		$kv=$gf->cleanVar($_GET["kv"]);
		$camps="";
		foreach ($_POST as $key => $value) {
			$value=str_replace("data:image/png;base64,","|||***png***|||",$value);
			$value=str_replace("data:image/jpg;base64,","|||***jpg***|||",$value);
			$value=str_replace("data:image/jpeg;base64,","|||***jpeg***|||",$value);
			$camps.="$key='$value',";
		}
		$camps=substr($camps,0,strlen($camps)-1);
		$query="UPDATE $ent SET $camps WHERE $ky='$kv'";
	}
	//echo $query;
	$res=$gf->dataIn($query);

	if($res){
		echo "ok";
	}else{
		echo "bad: ".$query;
	}
}else{
	echo "bad: Se ha vencido la sesion";
}
?>
