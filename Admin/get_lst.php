<?php
session_start();
	if(isset($_SESSION["restuiduser"])){
        require_once("../autoload.php");
        $gf=new generalFunctions;
        global $relaciones;
		$tabla=$gf->cleanVar($_GET["tabla"]);
		$key=$gf->cleanVar($_GET["key"]);
		$cmp=$gf->cleanVar($_GET["cmp"]);
		$arreglo=explode("|",$gf->cleanVar($_GET["order"]));
		$ind=0;
		foreach($arreglo as $indice=>$valor){
			$ind=$indice+1;
			$sentencia2="UPDATE $tabla SET $cmp='$ind' WHERE $key='$valor'";
			$gf->dataIn($sentencia2);
		}
	}
?>
