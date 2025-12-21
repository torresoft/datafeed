<?php 
session_start();
  	if(isset($_SESSION["govuname"]) && isset($_SESSION["govutipo"]) && $_SESSION["govutipo"]=="A"){
		
		require_once("../autoload.php");
		$gf=new generalFunctions;
		global $relaciones;
		$dataTables=new dsTables();
		$titulo="ADMINISTRACI�N LISTADO DE PRODUCTOS";
		$tabla="productos";
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
			$gettabla = $dataTables->armaTablaDyRel($tabla,$Pkey,$Ftablas,$Fkeys,$Fnames,$sender,$titulo,$Ncamps,$Ntypes,$Nalias,"1",1,1,1,"null","null");
			echo $gf->utf8("<div>".$gettabla."</div>");
		}elseif($actividad=="editar"){
			$Vkey=$gf->cleanVar($_GET["Vkey"]);
			$rnd_table=$gf->cleanVar($_GET["editelement"]);
			$dialogo=$gf->cleanVar($_GET["rnd"]);
			$Pkey=$relaciones[$tabla]["clave_primaria"];
			$Ftablas=$relaciones[$tabla]["tablas_foraneas"];
			$Fkeys=$relaciones[$tabla]["llaves_foraneas"];
			$Fnames=$relaciones[$tabla]["nombres_foraneos"];
			$Ncamps=$relaciones[$tabla]["campos"];
			$Nalias=$relaciones[$tabla]["alias"];
			$Ntypes=$relaciones[$tabla]["tipos"];
			$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Pkey,$Vkey,$Ftablas,$Fkeys,$Fnames,$sender,$titulo,$Ncamps,$Ntypes,$Nalias,"null","null",array(),"","null",$dialogo,0,"loader(\'$sender?flag=ver\')");
			echo $gf->utf8($gettabla);
		}elseif($actividad=="nuevo"){
			$rnd_table=$gf->cleanVar($_GET["addelement"]);
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