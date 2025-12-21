<?php 
session_start();
	if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"])){
		
		$titulo="USUARIOS ADMINISTRADORES";
		require_once("../autoload.php");	
		global $relaciones;
		$gf=new generalFunctions;
		$dataTables=new dsTables();
		$actividad=$gf->cleanVar($_GET["flag"]);
		$sender=$_SERVER['PHP_SELF'];
		$hnd=1;
		$tabla="usuarios";
		$rigu=array(1,1,1,1);
		if($actividad=="ver"){
			if($_SESSION["restprofile"]!="A") exit;
			$shapas=hash('sha512','1234567');
			$gf->dataIn("UPDATE usuarios SET PASSWORD=:defpass WHERE PASSWORD=:nulo",array(":defpass"=>$shapas,":nulo"=>""));
	
			$bnf="";
			$cond=" AND 1";
			
			$bnf2="<button class='btn btn-xs btn-success pull-right' onclick=\"getDialog('$sender?flag=nuevo&hnd=$hnd')\"><span class='glyphicon glyphicon-plus-sign'></span>Nuevo usuario</button>";
			$cond1="1 ";
			
			$resultInt = $gf->dataSet("SELECT U.ID_USUARIO, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS NOMBRE, U.PERFIL, U.CORREO, U.ESTADO, E.ID_CLIENT FROM usuarios U JOIN sitios E ON U.ID_SITIO=E.ID_SITIO WHERE E.ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
	
			echo $gf->utf8("<div class='panel panel-default'><div class='panel-heading'>USUARIOS DEL SISTEMA $bnf $bnf2 </div><div class='panel-body'><input type='text' class='form-control' id='schus' onkeyup=\"filtrarValores('schus','thus')\" placeholder='Buscar Usuario' /><br /><div class='row'>");

			if($resultInt!=false && count($resultInt)>0){
				foreach($resultInt as $rowInt){
					$id_usuario=$rowInt["ID_USUARIO"];
					$nombre=$rowInt["NOMBRE"];
					$lgn=$rowInt["CORREO"];
					
					$estado=$rowInt["ESTADO"];
					$perfil=$rowInt["PERFIL"];
					$clientid=$rowInt["ID_CLIENT"];
					$lgn.="@".$clientid;
					$avatar="";
					$tocado=1;
				
					$hasboss="";
					
					if($perfil=="A"){
						$avatar="<img class='img-circle' src='misc/default_avatar.png' style='width:48px;' />";
						$perfile="ADMINISTRADOR";
					}elseif($perfil=="C"){
						$avatar="<img class='img-circle' src='misc/chef.png' style='width:48px;' />";
						$perfile="CHEF";
					}elseif($perfil=="M"){
						$avatar="<img class='img-circle' src='misc/waiter.png' style='width:48px;' />";
						$perfile="WAITER";
					}elseif($perfil=="J"){
						$avatar="<img class='img-circle' src='misc/casher.png' style='width:48px;' />";
						$perfile="CAJERO";
					}elseif($perfil=="T"){
						$avatar="<img class='img-circle' src='misc/default_avatar.png' style='width:48px;' />";
						$perfile="CONTADOR";
					}
				
					if($estado==1){
						$pnel="default";
					}else{
						$pnel="danger";
					}
					
					echo $gf->utf8("<div class='col-lg-4 col-md-6 col-sm-6 col-xs-12 filtrable thus'><div id='elm_user_$id_usuario' class='panel panel-$pnel' style='height:150px;'><div class='panel-heading'>$perfile");
					if($rigu[1]==1){
						echo $gf->utf8("<button class='btn btn-xs btn-warning pull-right' id='edit_$id_usuario' alt='Editar informaci&oacute;n'  title='Editar informaci&oacute;n' onclick=\"getDialog('$sender?flag=editar&amp;Vkey=$id_usuario&hnd=$hnd')\"><i class='fa fa-edit'></i></button>");
					}


					if($rigu[2]==1){
						if($tocado==0){
							echo $gf->utf8("<button class='btn btn-xs btn-info pull-right' id='del_$id_usuario' alt='Borrar Usuario' title='Borrar Usuario' onclick=\"goErase('usuarios','ID_USUARIO','$id_usuario','elm_user_$id_usuario','1')\"><i class='link fa fa-remove'></i></button>");
						}
					}
					if($rigu[1]==1 && $lgn!="datafeed@".$clientid){
						echo $gf->utf8("<button class='btn btn-xs btn-info pull-right' id='del_$id_usuario' alt='Resetear Clave' title='Resetear Clave' onclick=\"getDialog('$sender?flag=resetkey&id_user=$id_usuario&hnd=$hnd','400')\" ><i class='link fa fa-unlock'></i></button>");
					}
					
			
					echo $gf->utf8("</div><div class='panel-body'><table class='table'><tr><td>$avatar</td><td>$nombre<br />$lgn</td></tr><tr><td align='right'></td></tr></table></div></div></div>");
					
				}
				
				

			}
			echo $gf->utf8("</div></div></div>");
		
		}elseif($actividad=="resetkey"){
			if($_SESSION["restprofile"]!="A") exit;
			$id_usuario=	$gf->cleanVar($_GET["id_user"]);
			$shapas=hash('sha512','1234567');
			$resultInt = $gf->dataSet("SELECT ID_USUARIO, CONCAT(NOMBRES,' ',APELLIDOS) AS NOMBRE FROM usuarios WHERE ID_USUARIO=:usuario",array(":usuario"=>$id_usuario));
			if($resultInt!=false && count($resultInt)>0){
				$rowInt=$resultInt[0];
				$nombre=$rowInt["NOMBRE"];
				if($gf->dataIn("UPDATE usuarios SET PASSWORD=:shapas WHERE ID_USUARIO=:usuario",array(":shapas"=>$shapas,":usuario"=>$id_usuario))){
					echo "La clave fue reseteada para el usuario $nombre <br />Nueva clave: 1234567<br />C&aacute;mbiela en cuanto pueda!";
					$gf->log($_SESSION["restbus"],0,0,"CLAVE RESETEADA ID_USUARIO:$id_usuario",$_SESSION["restuiduser"]);
				}else{
					echo "No se pudo realizar el cambio";
				}
			}else{
				echo "Violacion";
			}

		}elseif($actividad=="editar"){
			if($_SESSION["restprofile"]!="A") exit;
			$dialogo=$gf->cleanVar($_GET["rnd"]);
			$Vkey=$gf->cleanVar($_GET["Vkey"]);
			$filterKey="ID_SITIO";
			$filterVal="null";
			$fkf=array();
			$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey,$filterVal,$dialogo,"loader(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal&hnd=$hnd\')",$fkf);
			echo $gf->utf8($gettabla);
		}elseif($actividad=="edit_me"){
			if($_SESSION["restprofile"]!="A") exit;
			$dialogo="null";
			$Vkey=$_SESSION["restuiduser"];
			$filterKey="ID_SITIO";
			$filterVal="null";
			$fkf=array();
			$gettabla = $dataTables->devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey,$filterVal,$dialogo,"loader(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal&hnd=$hnd\')",$fkf);
			echo $gf->utf8($gettabla);
		}elseif($actividad=="nuevo"){
			if($_SESSION["restprofile"]!="A") exit;
			$dialogo=$gf->cleanVar($_GET["rnd"]);
			$filterKey="ID_SITIO";
			$filterVal=$_SESSION["restbus"];
			$plusk=array();
			$fkf=array();
			$gettabla = $dataTables->devuelveTablaNewItemDyRel($tabla,$filterKey,$filterVal,$dialogo,"loader(\'$sender?flag=ver&ent=$tabla&filterKey=$filterKey&filterVal=$filterVal&hnd=$hnd\')",$fkf,false,$plusk);
			echo $gf->utf8($gettabla);
		}elseif($actividad=="chpass2"){
			$id_usuario=$_SESSION["restuiduser"];
			$clave_old=$_POST["oldpass"];
			$clave_new1=$_POST["newpass1"];
			$clave_new2=$_POST["newpass2"];
			$sha_old=hash('sha512',$clave_old);
			$sha_new=hash('sha512',$clave_new1);
			
			$dsus=$gf->dataSet("SELECT ID_USUARIO FROM usuarios WHERE ID_USUARIO=:usua AND PASSWORD=:passold",array(":usua"=>$id_usuario,":passold"=>$sha_old));
			if(count($dsus)>0){
				$ok=$gf->dataIn("UPDATE usuarios SET PASSWORD=:newpass WHERE ID_USUARIO=:usua",array(":usua"=>$id_usuario,":newpass"=>$sha_new));
				if($ok){
					echo "Se ha cambiado la clave para ".$_SESSION["restuname"];
				}else{
					echo "No se pudo realizar el cambio, intente nuevamente";
				}
			}else{
				echo "Los datos no coinciden, verifique que la clave anterior sea correcta y la nueva clave sea igual en los dos campos solicitados";
			}
			$gf->log($_SESSION["restbus"],0,0,"CAMBIO DE CLAVE",$_SESSION["restuiduser"]);
		}elseif($actividad=="chpass"){
			$dialogo=$gf->cleanVar($_GET["rnd"]);
			$id_usuario= $gf->cleanVar($_GET["restuiduser"]);

			echo $gf->utf8("CAMBIAR CLAVE: ".$_SESSION["restuname"]."
			<div class='control-group'><label for='oldpass'>Clave antigua</label>
			<input class='form-control unv_newpass' type='password' id='oldpass' name='oldpass' />
			</div>
			<div class='control-group'><label for='newpass1'>Nueva clave</label>
			<input class='form-control unv_newpass' type='password' id='newpass1' name='newpass1' />
			</div>
			<div class='control-group'><label for='newpass2'>Confirmar nueva clave</label>
			<input class='form-control unv_newpass' type='password' id='newpass2' name='newpass2' /></div>
			
			<hr /><input type='button' class='btn btn-primary' value='Cambiar Clave' onclick=\"cargaHTMLvars('ModalContent_$dialogo','$sender?flag=chpass2','','20000','unv_newpass')\" /> <input type='button' class='btn btn-warning' value='Cancelar' onclick=\"closeD('$dialogo')\" /></form>");


		}else{
			echo "Ninguna solicitud";
		}
	
	}else{
		echo "No has iniciado sesion!";
	}
?>
