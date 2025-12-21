<?php
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"])){
	
    $titulo="USUARIOS ADMINISTRADORES";
    require_once("../autoload.php");
    global $relaciones;
    $gf=new generalFunctions;
    $dataTables=new dsTables();
    $sender=$_SERVER['PHP_SELF'];
    $hnd=1;
    $tabla="usuarios";

	if(isset($_GET["idU"])){
		$id_user= $gf->cleanVar($_GET["idU"]);
		$rw=0;
	}else{
		$id_user=$_SESSION["restuiduser"];
		$rw=1;
	}
	$campos=$relaciones["usuarios"]["campos"];
	$camposall="";
	foreach($campos as $camp=>$configd){
		$camposall.=$camp.",";
	}
	$camposall.=substr($camposall,0,-1);
	$rs=$gf->dataSet("SELECT $camposall FROM usuarios WHERE ID_USUARIO='$id_user' AND ID_SITIO='{$_SESSION["restbus"]}'");

	if(count($rs)>0){
		$row=$rs[0];
		foreach($campos as $varr=>$cnf){
			$$varr=$row[$varr];
		}
    }
    if($_SESSION["restprofile"]=="M" || $_SESSION["restprofile"]=="C"){
        $folder="public_usr";
    }else{
        $folder="";
    }
    $AVATAR=trim($AVATAR);
    $perfil=$PERFIL;
    if($AVATAR=="" || !file_exists("../".$AVATAR)){
        if($perfil=="A"){
            $perfile="ADMINSITRADOR";
            $AVATAR="misc/default_avatar.png";
        }elseif($perfil=="C"){
            $perfile="CHEF";
            $AVATAR="misc/chef.png";
        }elseif($perfil=="J"){
            $perfile="CAJERO";
            $AVATAR="misc/casher.png";
        }elseif($perfil=="M"){
            $perfile="WAITER";
            $AVATAR="misc/waiter.png";
        }
    }else{
        if($perfil=="A"){
            $perfile="ADMINSITRADOR";
        }elseif($perfil=="C"){
            $perfile="CHEF";
        }elseif($perfil=="J"){
            $perfile="CAJERO";
        }elseif($perfil=="M"){
            $perfile="WAITER";
        }
    }
    

	if($AVATAR=="" || !file_exists("../".$AVATAR)){
		$AVATAR="misc/user_user.png";
	}
	$_SESSION["restuavatar"]=$AVATAR;


?>
<hr />
<div id="user-profile-1" class="user-profile row">
	<div class="col-xs-12 col-sm-3 center">
		<div>
			<span class="profile-picture">
				<img id="atatarok" style='width:200px;' data-toggle="tooltip" title="Click para cambiar imagen" class="editable img-responsive link" <?php if($rw==1) echo "onclick=\"getImage('IMAG_TERC','$folder','srcImg()')\""; ?> alt="<?php echo $gf->utf8($NOMBRES." ".$APELLIDOS);?>" src="readfl.php?filename=<?php echo $AVATAR;?>" />
				<input type='hidden' id='IMAG_TERC' onchange="setQR('usuarios','AVATAR','IMAG_TERC','<?php echo $id_user ?>');" />
			</span>

		</div>

    </div>
		
	<div class="col-xs-12 col-sm-9">
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> NOMBRES </div>

				<div class="profile-info-value">
					<?php
					if($rw==1){
					?>
					<input type='text' class='form-control' id='PNOM_TERC' value="<?php echo $gf->utf8($NOMBRES);?>" onchange="setQR('usuarios','NOMBRES','PNOM_TERC','<?php echo $id_user ?>')" />
					<?php
					}else{
					?>
					<span class="editable" id="username"><?php echo $gf->utf8($NOMBRES);?></span>
					<?php
					}
					?>
				</div>
			</div>
            <br />
			<div class="profile-info-row">
				<div class="profile-info-name"> APELLIDO </div>

				<div class="profile-info-value">
					<?php
					if($rw==1){
					?>
					<input type='text' class='form-control' id='SNOM_TERC' value="<?php echo $gf->utf8($APELLIDOS);?>" onchange="setQR('usuarios','APELLIDOS','SNOM_TERC','<?php echo $id_user ?>')" />
					<?php
					}else{
					?>
					<span class="editable" id="username2"><?php echo $gf->utf8($APELLIDOS);?></span>
					<?php
					}
					?>
				</div>
            </div>
            <br />
			<div class="profile-info-row">
				<div class="profile-info-name"> PERFIL </div>

				<div class="profile-info-value">
					<span class="editable" id="username2"><?php echo $gf->utf8($perfile);?></span>
				</div>
            </div>
            
            <hr />
			<div class="profile-info-row">
				<div class="profile-info-name"> CLAVE </div>

				<div class="profile-info-value">
					
                <?php
				if($id_user==$_SESSION["restuiduser"]){
				?>
				<a class="btn btn-link" onclick="getDialog('Admin/site_adm_users.php?flag=chpass&restuiduser=<?php echo $gf->utf8($id_user);?>','450')">
					<i class="ace-icon fa fa-key bigger-125 blue"></i>
					Cambiar clave
				</a>
				<?php
				}else{
                    echo "***privada***";
                }
				?>
				</div>
            </div>
        </div>

	</div>
</div>

<?php
}
?>