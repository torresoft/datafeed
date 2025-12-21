<?php
session_start();
  	require_once("../autoload.php");
	global $relaciones;
	$gf=new generalFunctions;
	$dataTables=new dsTables();
	if(!empty($_SESSION["usuarioadm"])){
		$resultSITE = mysql_query("SELECT ID_SITIO, ID_STYLE, NOMBRE, URL, DIRECCION, GPS, ICON, PBX, FAX, CORREO, CIUDAD, FONDO, ANCHO, PATH_HEADER, DOMINIO, CORREO_ADMINISTRADOR,CUSTOM,ADMINISTRADOR,SHADOW,BARRA,LAST_UPDATE, REGISTRO_SIMPLE, FB_ID, FB_SECRET, MENU_TYPE, MENU_IMG_W FROM site_info", $link) or die("A MySQL error has occurred.<br />Error: (" . mysql_errno() . ") " . mysql_error()); 
		//EXISTE EL SITIO?
		if($rowSITE = mysql_fetch_array($resultSITE)){ 
			$nombre=$rowSITE["NOMBRE"];
			$url=$rowSITE["URL"];
			$icon=$rowSITE["ICON"];
			$direccion=$rowSITE["DIRECCION"];
			$pbx=$rowSITE["PBX"];
			$fax=$rowSITE["FAX"];
			$correo=$rowSITE["CORREO"];
			$ciudad=$rowSITE["CIUDAD"];
			$dominio=$rowSITE["DOMINIO"];
			$fondo=$rowSITE["FONDO"];
			$ancho=$rowSITE["ANCHO"];
			$id_estilo=$rowSITE["ID_STYLE"];
			$header=$rowSITE["PATH_HEADER"];
			$correo_admin=$rowSITE["CORREO_ADMINISTRADOR"];
			$administrador=$rowSITE["ADMINISTRADOR"];
			$sombra=$rowSITE["SHADOW"];
			$barra=$rowSITE["BARRA"];
			$last_update=$rowSITE["LAST_UPDATE"];
			$gps=$rowSITE["GPS"];
			$custom=$rowSITE["CUSTOM"];
			$simple_reg=$rowSITE["REGISTRO_SIMPLE"];
			$fbid=$rowSITE["FB_ID"];
			$fbsecret=$rowSITE["FB_SECRET"];
			$menu_type=$rowSITE["MENU_TYPE"];
			$menu_img_w=$rowSITE["MENU_IMG_W"];
			
			
			
			echo $gf->utf8("<table class='ui-semiblack' border='1'><tr><td colspan='2' class='ui-widget-header ui-corner-all'>CONFIGURACI&Oacute;N DEL SITIO</td></tr><tr><td valign='top'>");
			echo $gf->utf8("<table><tr><td colspan='2' class='ui-widget-header ui-corner-all'>INFORMACI&Oacute;N BASE</td></tr>");
			
			echo $gf->utf8("<tr><td>Nombre del sitio (Raz&oacute;n Social)</td><td><input type='text' style='width:300px;' name='NOMBRE' id='nombre' value='$nombre' tipo='former_theinfo' /></td></tr>");
			echo $gf->utf8("<tr><td>URL(Sitio web):</td><td><input type='text' style='width:250px;' name='URL' id='url' value='$url' tipo='former_theinfo' /></td></tr>");
			echo $gf->utf8("<tr><td>Direcci&oacute;n f&iacute;sica:</td><td><input type='text' style='width:250px;' name='DIRECCION' id='direccion' value='$direccion' tipo='former_theinfo' /></td></tr>");
			echo $gf->utf8("<tr><td>Ciudad</td><td><input type='text' style='width:250px;' name='CIUDAD' id='ciudad' value='$ciudad' tipo='former_theinfo' /></td></tr>");
			echo $gf->utf8("<tr><td>GPS(Coordenadas)</td><td><input type='text' style='width:250px;' name='GPS' id='gps' value='$gps' tipo='former_theinfo' /><img id='imgetmap' src='misc/marker_icon.png' class='link' alt='Buscar las coordenadas con Gmaps' onclick=\"addPick('gps','imgetmap')\" /> </td></tr>");
			echo $gf->utf8("<tr><td>Tel&eacute;fonos</td><td><input type='text' style='width:200px;' name='PBX' id='pbx' value='$pbx' tipo='former_theinfo' /></td></tr>");
			echo $gf->utf8("<tr><td>Fax</td><td><input type='text' style='width:200px;' name='FAX' id='fax' value='$fax' tipo='former_theinfo' /></td></tr>");
			echo $gf->utf8("<tr><td>Correo Oficial</td><td><input type='text' style='width:250px;' name='CORREO' id='correo' value='$correo' tipo='former_theinfo' /></td></tr>");
			echo $gf->utf8("<tr><td>Administrador del sitio</td><td><input type='text' style='width:300px;' name='ADMINISTRADOR' id='administrador' value='$administrador' tipo='former_theinfo' /></td></tr>");
			echo $gf->utf8("<tr><td>Correo del administrador</td><td><input type='text' style='width:250px;' name='CORREO_ADMINISTRADOR' id='correo_administrador' value='$correo_admin' tipo='former_theinfo' /></td></tr>");
			echo $gf->utf8("<tr><td>Dominio (EJ: nombresitio.com)</td><td><input type='text' style='width:250px;' name='DOMINIO' id='dominio' value='$dominio' tipo='former_theinfo' /></td></tr>");
			if($simple_reg==1){
				echo $gf->utf8("<tr><td>Gesti&oacute;n de usuarios externos</td><td><select style='width:200px;' tipo='former_theinfo' name='REGISTRO_SIMPLE' id='registro_simple' alt='El m&eacute;todo simple no requiere que los usuarios confirmen su correo electr&oacute;nico y en el registro se le solicitan menos datos'><option value='0'>Avanzado</option><option value='1' selected='selected'>Simple</option></select></td></tr>");
			}else{
				echo $gf->utf8("<tr><td>Gesti&oacute;n de usuarios externos</td><td><select style='width:200px;' tipo='former_theinfo' name='REGISTRO_SIMPLE' id='registro_simple' alt='El m&eacute;todo simple no requiere que los usuarios confirmen su correo electr&oacute;nico y en el registro se le solicitan menos datos'><option value='0' selected='selected'>Avanzado</option><option value='1'>Simple</option></select></td></tr>");
			}
			
			echo $gf->utf8("<tr><td>INFORMACI&Oacute;N FACEBOOK APP</td><td><table><tr><td>ID:</td><td><input type='text' style='width:200px;' name='FB_ID' id='fb_id' value='$fbid' tipo='former_theinfo' alt='Si no desea integrar la aplicaci&oacute;n con facebook deje estos campos en blanco' /></td></tr><tr><td> SECRET:</td><td><input type='text' style='width:200px;' name='FB_SECRET' id='fb_secret' value='$fbsecret' tipo='former_theinfo' /></td></tr></table></td></tr>");
			
			echo $gf->utf8("</table>");
			echo $gf->utf8("</td><td valign='top'>");

			echo $gf->utf8("<table><tr><td colspan='2' class='ui-widget-header ui-corner-all'>APARIENCIA DEL SITIO</td></tr>");
			echo $gf->utf8("<tr><td>Tema</td><td>");
			echo $gf->utf8("<select tipo='former_theinfo' name='ID_STYLE' id='id_style' alt='Si quiere previsualizar los temas antes de asignarlos, haga clic en el men&uacute; [Configuraci&oacute;n - Administrar temas visuales]'>");
			echo $gf->utf8("<option value=''>Sin estilo...</option>");
			$resultMN = mysql_query("SELECT ID_STYLE, NOMBRE FROM estilos", $link) or die("Error: (" . mysql_errno() . ") " . mysql_error()); 
			if($rowMN = mysql_fetch_array($resultMN)){ 
				do{
					$id_style=$rowMN["ID_STYLE"];
					$nombre_estilo=$rowMN["NOMBRE"];
					if($id_estilo==$id_style){
						echo $gf->utf8("<option value='$id_style' selected='selected'>$nombre_estilo</option>");
					}else{
						echo $gf->utf8("<option value='$id_style'>$nombre_estilo</option>");
					}
				}while($rowMN=mysql_fetch_array($resultMN));
			}
			echo $gf->utf8("</select>");
			echo $gf->utf8("</td></tr>");
			echo $gf->utf8("<tr><td>Ancho del sitio en el navegador (pixeles)</td><td><input type='number' style='width:100px;' name='ANCHO' id='ancho' value='$ancho' tipo='former_theinfo' /></td></tr>");
			if($custom==0){
				echo $gf->utf8("<tr><td>Organizar en</td><td><select name='CUSTOM' id='custom' tipo='former_theinfo'><option value='0' selected='selected'>Columnas (recomendado)</option><option value='1'>Cuadros fijos</option></select></td></tr>");
			}else{
				echo $gf->utf8("<tr><td>Organizar en</td><td><select name='CUSTOM' id='custom' tipo='former_theinfo'><option value='0'>Columnas (recomendado)</option><option value='1' selected='selected'>Cuadros fijos</option></select></td></tr>");
			}
			if($sombra=="V"){
				echo $gf->utf8("<tr><td>Sombra</td><td><select name='SHADOW' id='shadow' tipo='former_theinfo'><option value='V' selected='selected'>Vertical</option><option value='H'>Horizontal</option><option value='S'>Sin sombra</option></select></td></tr>");
			}elseif($sombra=="H"){
				echo $gf->utf8("<tr><td>Sombra</td><td><select name='SHADOW' id='shadow' tipo='former_theinfo'><option value='V'>Vertical</option><option value='H' selected='selected'>Horizontal</option><option value='S'>Sin sombra</option></select></td></tr>");
			}else{
				echo $gf->utf8("<tr><td>Sombra</td><td><select name='SHADOW' id='shadow' tipo='former_theinfo'><option value='V'>Vertical</option><option value='H'>Horizontal</option><option value='S' selected='selected'>Sin sombra</option></select></td></tr>");
			}
			
			echo $gf->utf8("<tr><td>Color barra de men&uacute;</td><td><input type='text' name='BARRA' id='barra' onclick=\"pickColor('barra')\" style='background:#$barra;' value='$barra' tipo='former_theinfo' /></td></tr>");
			
			echo $gf->utf8("<tr><td>Imagen de fondo del sitio(JPG,PNG)</td><td><input type='text' style='width:250px;' name='FONDO' id='fondo' value='$fondo' tipo='former_theinfo' /><img class='link' src='misc/browse.gif' onclick=\"getImage('fondo','')\" /></td></tr>");
			
			echo $gf->utf8("<tr><td>&Iacute;cono del sitio</td><td><input type='text' style='width:250px;' name='ICON' id='icono' value='$icon' tipo='former_theinfo' /><img class='link' src='misc/browse.gif' onclick=\"getImage('icono','')\" /></td></tr>");
			
			echo $gf->utf8("<tr><td>Encabezado (Imagen o Componente Flash)</td><td><input type='text' style='width:250px;' name='PATH_HEADER' id='path_header' value='$header' tipo='former_theinfo' /><img class='link' src='misc/browse.gif' onclick=\"getImage('path_header','')\" /></td></tr>");
			echo $gf->utf8("<tr><td>Fecha &Uacute;ltima actualizaci&oacute;n</td><td><input type='text' name='LAST_UPDATE' id='last_update' value='$last_update' onmousedown=\"addCalendario('last_update')\" onmouseup=\"addCalendario('last_update')\" tipo='former_theinfo' /></td></tr>");
			
			if($menu_type==1){
				echo $gf->utf8("<tr><td>Sombra</td><td><select name='MENU_TYPE' id='menutipe' tipo='former_theinfo'><option value='1' selected='selected'>Lista Normal</option><option value='2'>Botones grandes</option></select></td></tr>");
			}else{
				echo $gf->utf8("<tr><td>Tipo de men&uacute;</td><td><select name='MENU_TYPE' id='menutipe' tipo='former_theinfo'><option value='1'>Lista Normal</option><option value='2' selected='selected'>Botones grandes</option></select></td></tr>");
			}
			echo $gf->utf8("<tr><td>Tama�o &iacute;cono men&uacute; (PIXELES)</td><td><input type='number' style='width:250px;' name='MENU_IMG_W' id='imgwmenu' value='$menu_img_w' tipo='former_theinfo' /></td></tr>");
			
			echo $gf->utf8("</table>");
			

			
			echo $gf->utf8("</td></tr><tr><td colspan='2'>");
			
			echo $gf->utf8("<input type='button' value='Guardar' class='jq' onclick=\"goUpdate('theinfo','theinfo','site_info','1','1','','','0','null','0','refreshadm()')\" /><input type='button' value='Cancelar' class='jq' onclick=\"refreshadm()\" />");
			
			
			echo $gf->utf8("</td></tr></table>");
			
		
		}
	}else{
		echo $gf->utf8("No haz iniciado sesion");
	}
?>
