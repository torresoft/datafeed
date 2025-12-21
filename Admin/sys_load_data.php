<?php 
session_start();
require_once("../autoload.php");
  	if(!isset($_SESSION["userrol"]) || !isset($_SESSION["userfullname"]) || !isset($_SESSION["userimg"]) || !isset($_SESSION["usermail"])){
	}else{
		$flag=$gf->cleanVar($_GET["flag"]);
		if($flag=="start"){
			$estructura="CLAVE|VALOR";
			echo $gf->utf8("<div class='ui-widget ui-corner-all'><table align='center'><tr><td colspan='2'>");
			$resu=mysql_query("show tables",$link);
			if($rw=mysql_fetch_array($resu)){
				echo $gf->utf8("Seleccione la tabla: <select name='selectable' id='selectable' onchange=\"selectCampsLoad()\" >");
				do{
					$tabla=$rw[0];
					echo $gf->utf8("<option value='$tabla'>$tabla</option>");
				}while($rw=mysql_fetch_array($resu));
				echo $gf->utf8("</select>");
			}	 
			echo $gf->utf8("</td><td>Clave:<div id='camp_id'></div></td><td>Dato:<div id='camp_val'></td></tr><tr><td colspan='3'><input type='hidden' id='filetype' value='csv' ext='csv,txt' /> Archivo: <input type='text' id='thefilexml' style='width:400px;' /><img class='link' src='misc/browse.gif' align='absmiddle' alt='Seleccionar el archivo o subirlo al servidor' id='imgloadfiles' onclick=\"getFile('thefilexml','filetype','./tmp_xml/')\" /><br /><div id='filestruct'>
							
								ESTRUCTURA:  CLAVE | DATO      <br />IMPORTANTE: No coloque columna de encabezado
							 
							 </div></td><td><input type='button' class='jq' id='botonprevfile' value='Consultar' onclick=\"viewCSV('$estructura')\" alt='Consultar y previsualizar el contenido del archivo' /></td></tr>
							 <tr><td colspan='3'><div id='load_records' class='ui-widget-content ui-corner-all' style='width:650px;height:250px;overflow:auto;'></div><div id='explain' style='width:650px;height:60px;float:left;'><table width='100%'><tr><td colspan='2'><input type='button' class='jq' id='loadbutton' onclick=\"loadData()\" style='display:none;' value='Cargar a la base de datos' /><div style='height:16px;overflow:hidden;' id='progressload' style='display:none;'></div></td></tr><tr><td><div style='height:40px;width:40px;'</td><td align='right'></td></tr></table></div></td></tr>
							 </table></div>");
		}elseif($flag=="selectcamp"){
			$tabla= $gf->cleanVar($_GET["tabla"]);
			$campo=$gf->cleanVar($_GET["campo"]);
			$resu=mysql_query("describe $tabla",$link);
			if($rw=mysql_fetch_array($resu)){
				echo $gf->utf8("<select name='$campo' id='$campo' >");
				do{
					$camp=$rw[0];
					echo $gf->utf8("<option value='$camp'>$camp</option>");
				}while($rw=mysql_fetch_array($resu));
				echo $gf->utf8("</select>");
			}
		}elseif($flag=="nuevo"){
			$id_periodo= $gf->cleanVar($_GET["id_periodo"]);
			$dialogo=$gf->cleanVar($_GET["rnd"]);
			echo $gf->utf8("<table align='center'><tr><td align='center'>NOMBRE DEL INDUCTOR: <input style='width:250px;' type='text' tipo='former_newind' name='NOMBRE' id='nombre_inductor' /><input type='hidden' tipo='former_newind' name='ID_PERIODO' value='$id_periodo' id='periodo_inductor' /><br /><input type='button' class='jq' value='Crear Inductor' onclick=\"goForm('newind','newind','sys_inductores','null','null','$dialogo','null','0','cargaHTMLvars(\'sp-desarrollo\',\'Admin/sys_adm_inductores.php?flag=ini&amp;filterValue=$id_periodo\',\'\',\'setSelectInductores()\')','0')\" /></td></tr></table>");
			
		}elseif($flag=="import"){
			$id_periodoto=$gf->cleanVar($_GET["id_periodoto"]);
			$dialogo=$gf->cleanVar($_GET["rnd"]);
			echo $gf->utf8("<table align='center'><tr><td align='center'>PERIODO PARA IMPORTAR VALORES DE N&Oacute;MINA: ");
			$resu=mysql_query("SELECT ID_PERIODO, NOMBRE, MONTH, YEAR FROM sys_periodos WHERE ID_PERIODO<>'$id_periodoto' AND ID_PERIODO IN(SELECT ID_PERIODO FROM sys_valores_nomina)",$link);
			if($rw=mysql_fetch_array($resu)){
				echo $gf->utf8("<select name='selectperiodoimport' id='selectperiodoimport' >");
				do{
					$id_periodothis=$rw["ID_PERIODO"];
					$anio=$rw["YEAR"];
					$mes=$rw["MONTH"];
					$namePeriodo=$rw["NOMBRE"];
					echo $gf->utf8("<option value='$id_periodothis'>$namePeriodo</option>");
				}while($rw=mysql_fetch_array($resu));
				echo $gf->utf8("</select>");
			}
			echo $gf->utf8("<br /><input type='button' class='jq' value='Importar N&oacute;mina' onclick=\"copyNomina('$id_periodoto','$dialogo')\" /></td></tr></table>");
		}elseif($flag=="importdata"){
			
		}
	}
?>