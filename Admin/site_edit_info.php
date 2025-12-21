<?php 
session_start();
  	if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && $_SESSION["restprofile"]=="A"){
		require_once("../autoload.php");
		$gf=new generalFunctions;
		global $relaciones;
		$dataTables=new dsTables();
		$titulo="ADMINISTRACI&Oacute;N DE DATOS DE LA EMPRESA";
		$tabla="sitios";
		$sender=$_SERVER['PHP_SELF'];
		$actividad=$gf->cleanVar($_GET["flag"]);
		if($actividad=="ver"){
			$Pkey=$relaciones[$tabla]["clave_primaria"];
			$Ftablas=$relaciones[$tabla]["tablas_foraneas"];
			$Fkeys=$relaciones[$tabla]["llaves_foraneas"];
			$Fnames=$relaciones[$tabla]["nombres_foraneos"];
			$Ncamps=$relaciones[$tabla]["campos"];
			$Nalias=$relaciones[$tabla]["alias"];
			$Ntypes=$relaciones[$tabla]["tipos"];
			$gettabla = $dataTables->armaTablaDyRel($tabla,$Pkey,$Ftablas,$Fkeys,$Fnames,$sender,$titulo,$Ncamps,$Ntypes,$Nalias,"1",0,1,0,"null","null");
			echo $gf->utf8("<div>".$gettabla."</div>");
		}elseif($actividad=="editar"){
			$Vkey=$_SESSION["restbus"];
			if(isset($_GET["filterKey"])){
				$filterKey=$gf->cleanVar($_GET["filterKey"]);
				$filterVal=$gf->cleanVar($_GET["filterVal"]);
			}else{
				$filterKey="";
				$filterVal="";
			}
			if(isset($_GET["rnd"])){
				$dialogo=$gf->cleanVar($_GET["rnd"]);
			}else{
				$dialogo="";
			}
			$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey,$filterVal,$dialogo,"getAux(\'$sender?flag=editar&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal\')");
			echo $gf->utf8($gettabla);
		}elseif($actividad=="nuevo"){
			$rnd_table= $gf->cleanVar($_GET["addelement"]);
			$dialogo=$gf->cleanVar($_GET["rnd"]);
			$Pkey=$relaciones[$tabla]["clave_primaria"];
			$Ftablas=$relaciones[$tabla]["tablas_foraneas"];
			$Fkeys=$relaciones[$tabla]["llaves_foraneas"];
			$Fnames=$relaciones[$tabla]["nombres_foraneos"];
			$Ncamps=$relaciones[$tabla]["campos"];
			$Nalias=$relaciones[$tabla]["alias"];
			$Ntypes=$relaciones[$tabla]["tipos"];
			$gettabla = $dataTables->devuelveTablaNewItemDyRel($tabla,$Pkey,$Ftablas,$Fkeys,$Fnames,$sender,$titulo,$Ncamps,$Ntypes,$Nalias,"null","null",array(),"","",$rnd_table,$dialogo,0,0,"loader(\'$sender?flag=ver\')");
			echo $gf->utf8($gettabla);
		
		}else{
			echo "Ninguna solicitud";
		}
		
	}else{
		echo "No has iniciado sesion!";
	}
?>