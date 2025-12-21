<?php
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="J" || $_SESSION["restprofile"]=="A")){
	
	require_once("../autoload.php");
	$gf=new generalFunctions;
    $actividad=$gf->cleanVar($_GET["flag"]);
	global $relaciones;
	$sender=$_SERVER["PHP_SELF"];
	$dataTables=new dsTables();
	if($actividad=="start"){
        if(!isset($_GET["pg"])){
            $pg=1;
        }else{
            $pg= $gf->cleanVar($_GET["pg"]);
        }
		if($pg==1){
            $limit="50";
        }else{
            $ini=($pg-1)*50;
            $limit="$ini,50";
        }
        if(isset($_POST["search"])){
            $buscar=$_POST["search"];
            $condsearch="(L.ID_PEDIDO LIKE '%$buscar%' OR M.NOMBRE LIKE '%$buscar%')";
        }else{
            $condsearch="1";
        }
        $rsLog=$gf->dataSet("SELECT
                                L.FECHA,
                                CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS USUARIO,
                                U.PERFIL,
                                M.NOMBRE AS MESA,
                                L.ID_PEDIDO,
                                L.OPERACION
                            FROM
                                log L
                                INNER JOIN usuarios U ON U.ID_USUARIO = L.ID_USUARIO
                                LEFT JOIN mesas M ON M.ID_MESA = L.ID_MESA
                                LEFT JOIN pedidos P ON P.ID_PEDIDO = L.ID_PEDIDO
                            WHERE L.ID_SITIO='{$_SESSION["restbus"]}' AND $condsearch ORDER BY L.ID_REG DESC LIMIT $limit");
        echo $gf->utf8("
        <input type='text' class='form-control unival_search' name='search' placeholder='Buscar' onkeyup=\"testIntro(event,'cargaHTMLvars(\'contenidos\',\'$sender?flag=start&buscar=1\',\'\',\'5000\',\'unival_search\')')\" />
        <table class='table table-bordered table-striped'><tr><td class='bg-primary'>FECHA</td><td class='bg-primary'>USUARIO</td><td class='bg-primary'>OPERACI&Oacute;N</td><td class='bg-primary'>PEDIDO</td><td class='bg-primary'>MESA</td></td>");
        if(count($rsLog)>0){
            
            $records=0;
            foreach($rsLog as $rwLog){
                $fecha=$rwLog["FECHA"];
                $usuario=$rwLog["USUARIO"];
                $operacion=$rwLog["OPERACION"];
                $pedido=$rwLog["ID_PEDIDO"];
                $mesa=$rwLog["MESA"];
                if($pedido=="0") $pedido="";
                echo $gf->utf8("<tr><td>$fecha</td><td>$usuario</td><td>$operacion</td><td>$pedido</td><td>$mesa</td></td>");
                $records++;
            }
           

            
        }else{
            echo $gf->utf8("<tr><td colspan='5'>No hay registros</td></td>");
        }
         echo $gf->utf8("</table>");
        if($records==50){
            $next=$pg+1;
        }else{
            $next=$pg;
        }
        
        if($pg>1){
            $prev=$pg-1;
        }else{
            $prev=1;
        }
        if(!isset($_POST["search"])){
            echo $gf->utf8("<button class='btn btn-warning pull-left' onclick=\"cargaHTMLvars('contenidos','$sender?flag=start&pg=$prev')\">< Anterior</button>");
            echo $gf->utf8("<button class='btn btn-warning pull-right' onclick=\"cargaHTMLvars('contenidos','$sender?flag=start&pg=$next')\">Siguiente></button>");
        }else{
            echo $gf->utf8("<button class='btn btn-warning pull-left' onclick=\"cargaHTMLvars('contenidos','$sender?flag=start&pg=1')\">< Regresar</button>");
        }
		

	}
}else{
	echo "No has iniciado sesion!";
}
?>