<?php 
session_start();
  	if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="A" || $_SESSION["restprofile"]=="J" || $_SESSION["restprofile"]=="Z")){
		
		require_once("../autoload.php");
		$gf=new generalFunctions;
		global $relaciones;
		$actividad=$gf->cleanVar($_GET["flag"]);
		$titulo="MENSAJES";
		$tabla="sitios";
		$sender=$_SERVER['PHP_SELF'];
		if($actividad=="home"){
			$tot_msgs=0;
			$msgs=$gf->dataSet("SELECT M.ID_MSG, M.ASUNTO, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS USUARIO, M.FECHA, M.VISTO FROM mensajes M LEFT JOIN usuarios U ON U.ID_USUARIO=M.ID_FROM WHERE M.ID_TO=:usuario AND M.VISTO=0 GROUP BY M.ID_MSG ORDER BY M.FECHA DESC",array(":usuario"=>$_SESSION["restuiduser"]));
			
			$tot_msgs=count($msgs);
			
			$pg = (isset($_GET["pg"])) ? $_GET["pg"] : 1;
			$ini=$pg>1 ? ($pg-1)*10 : 0;
			$sf=$pg>1 ? ($pg-1)*10 : 1;
			$st=$sf + 10;
			$next=$pg+1;
			$prev=($pg==1) ? 1: $pg-1;
			if(isset($_GET["sent"])){
				$camp="ID_FROM";
				$camp2="ID_TO";
			}else{
				$camp="ID_TO";
				$camp2="ID_FROM";
			}
			$allmsgs=$gf->dataSet("SELECT M.ID_MSG, M.ASUNTO, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS USUARIO, M.FECHA, M.VISTO FROM mensajes M LEFT JOIN usuarios U ON U.ID_USUARIO=M.$camp2 WHERE M.$camp=:usuario GROUP BY M.ID_MSG ORDER BY M.FECHA DESC LIMIT $ini, 10",array(":usuario"=>$_SESSION["restuiduser"]));

			$nmes=count($allmsgs);
			if($nmes<10){
				$next--;
				$st=$sf + $nmes - 1;
			}
			?>

			<section class="content-header">
			  <h1>
				Mensajes
				<small><?=$tot_msgs?> nuevos</small>
			  </h1>
			</section>

			<!-- Main content -->
			<section class="content">
			  <div class="row">
				<div class="col-md-3">
				  <a href="javascript:getDialog('<?php echo "$sender?flag=redactar"; ?>')" class="btn btn-primary btn-block margin-bottom">Redactar</a>

				  <div class="box box-solid">
					
					<div class="box-body no-padding">
					  <ul class="nav nav-pills nav-stacked">
						<?php
						if(isset($_GET["sent"])){
							$varsent="&sent=1";
							$activesent="active";
							$activeinbox="";
							$title="Enviados";
						}else{
							$varsent="";
							$activesent="";
							$activeinbox="active";
							$title="Bandeja de Entrada";
						}
						?>
						<li class="<?=$activeinbox?>"><a href="javascript:getAux('<?=$sender?>?flag=home')"><i class="fa fa-inbox"></i> Entrada</a></li>
						<li class="<?=$activesent?>"><a href="javascript:getAux('<?=$sender?>?flag=home&sent=1')"><i class="fa fa-envelope-o"></i> Enviados</a></li>

					  </ul>
					</div>
					<!-- /.box-body -->
				  </div>
				</div>
				<!-- /.col -->
				<div class="col-md-9">
				  <div class="box box-primary">
					<div class="box-header with-border">
					  <h3 class="box-title"><?=$title?></h3>

					  <div class="box-tools pull-right">
						<div class="has-feedback">
						  <input type="text" class="form-control input-sm" placeholder="Buscar Mensaje">
						  <span class="glyphicon glyphicon-search form-control-feedback"></span>
						</div>
					  </div>
					  <!-- /.box-tools -->
					</div>
					<!-- /.box-header -->
					<div class="box-body no-padding">
					  <div class="mailbox-controls">
						<!-- Check all button -->
				
						<!-- /.btn-group -->
						<button type="button" class="btn btn-default btn-sm" onclick="getAux('<?=$sender?>?flag=home<?=$varsent?>')"><i class="fa fa-refresh"></i></button>
						<div class="pull-right">
							<?php echo $sf; ?>-<?php echo $st; ?>
						  <div class="btn-group">
							<button type="button" class="btn btn-default btn-sm" onclick="getAux('<?=$sender?>?flag=home&pg=<?=$prev?><?=$varsent?>')"><i class="fa fa-chevron-left"></i></button>
							<button type="button" class="btn btn-default btn-sm" onclick="getAux('<?=$sender?>?flag=home&pg=<?=$next?><?=$varsent?>')"><i class="fa fa-chevron-right"></i></button>
						  </div>
						  <!-- /.btn-group -->
						</div>
						<!-- /.pull-right -->
					  </div>
					  <div class="table-responsive mailbox-messages">
						<table class="table table-hover table-striped">
						  <tbody>
							<?php
							if(count($allmsgs)==0){
								echo $gf->utf8("<tr><td>No tienes mensajes en la bandeja de entrada</td></tr>");
							}
							$nmes=0;
							foreach($allmsgs as $curmess){
								$id_mess=$curmess["ID_MSG"];
								$remite=$curmess["USUARIO"];
								$asunto=$curmess["ASUNTO"];
								$fecha=$curmess["FECHA"];
								$visto=$curmess["VISTO"];
								$nmes++;
								if($remite=="") $remite="Soporte DataFeed";
							?>
						  <tr>
							<td class="mailbox-star"><a href="javascript:getDialog('<?php echo $gf->utf8("$sender?flag=leer&id_mess=$id_mess");?>','1200','Mensaje')"><i class="fa fa-star text-yellow"></i></a></td>
							<td class="mailbox-name"><a href="javascript:getDialog('<?php echo $gf->utf8("$sender?flag=leer&id_mess=$id_mess");?>','1200','Mensaje')"><?=$remite?></a></td>
							<td class="mailbox-subject"><?=$asunto?></td>
							<td class="mailbox-date"><?=$fecha?></td>
						  </tr>
							<?php
							}
							?>
						  </tbody>
						</table>
						<!-- /.table -->
					  </div>
					  <!-- /.mail-box-messages -->
					</div>
					<!-- /.box-body -->
					<div class="box-footer no-padding">
					  <div class="mailbox-controls">
					
						<button type="button" class="btn btn-default btn-sm" onclick="getAux('<?=$sender?>?flag=home<?=$varsent?>')"><i class="fa fa-refresh"></i></button>
						<div class="pull-right">
						
						  <div class="btn-group">
							<button type="button" class="btn btn-default btn-sm" onclick="getAux('<?=$sender?>?flag=home&pg=<?=$prev?><?=$varsent?>')"><i class="fa fa-chevron-left"></i></button>
							<button type="button" class="btn btn-default btn-sm" onclick="getAux('<?=$sender?>?flag=home&pg=<?=$next?><?=$varsent?>')"><i class="fa fa-chevron-right"></i></button>
						  </div>
						  <!-- /.btn-group -->
						</div>
						<!-- /.pull-right -->
					  </div>
					</div>
				  </div>
				  <!-- /. box -->
				</div>
				<!-- /.col -->
			  </div>
			  <!-- /.row -->
			</section>

			
			<?php
		}elseif($actividad=="leer"){

			$rnd=$gf->cleanVar($_GET["rnd"]);
			$id_mess=$gf->cleanVar($_GET["id_mess"]);
			$allmsgs=$gf->dataSet("SELECT M.ID_MSG, M.ASUNTO, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS USUARIO, M.FECHA, M.VISTO, M.CONTENIDO FROM mensajes M LEFT JOIN usuarios U ON U.ID_USUARIO=M.ID_FROM WHERE (M.ID_TO=:usuario OR M.ID_FROM=:usuario) AND M.ID_MSG=:mensaje GROUP BY M.ID_MSG ORDER BY M.ID_MSG",array(":usuario"=>$_SESSION["restuiduser"],":mensaje"=>$id_mess));
			if(count($allmsgs)==0){
				echo "No se encontró el mensaje";
				exit;
			}
			$rowmess=$allmsgs[0];
			$asunto=$rowmess["ASUNTO"];
			$fecha=$rowmess["FECHA"];
			$remite=$rowmess["USUARIO"];
			$contenido=$rowmess["CONTENIDO"];
			$gf->dataIn("UPDATE mensajes SET VISTO=1 WHERE ID_MSG=:mensaje",array(":mensaje"=>$id_mess));
			?>
			<div class="col-md-12">
			  <div class="box box-primary">
				<div class="box-header with-border">
				  <h3 class="box-title">Leer Mensaje</h3>

				  
				</div>
				<!-- /.box-header -->
				<div class="box-body no-padding">
				  <div class="mailbox-read-info">
					<h3>Asunto: <?=$asunto?></h3>
					<h5>Remitente: <?=$remite?>
					  <span class="mailbox-read-time pull-right">Fecha: <?=$fecha?></span></h5>
				  </div>
				 
				  <div class="mailbox-read-message">

					<?=$contenido?>
				  </div>
				  <!-- /.mailbox-read-message -->
				</div>
	
				<!-- /.box-footer -->
				<div class="box-footer">
				  <div class="pull-right">
					<button type="button" class="btn btn-default" onclick="cargaHTMLvars('ModalContent_<?=$rnd?>','<?=$sender?>?flag=redactar&rnd=<?=$rnd?>&ref=<?=$id_mess?>')"><i class="fa fa-reply"></i> Responder</button>
				  </div>
					
				</div>
				<!-- /.box-footer -->
			  </div>
			  <!-- /. box -->
			</div>
			<!-- /.col -->

			<?php
		}elseif($actividad=="redactar"){
			$rnd=$gf->cleanVar($_GET["rnd"]);
			$curasunto="";
			$condires="1";
			$curcontent="";
			if(isset($_GET["ref"])){
				$ref=$gf->cleanVar($_GET["ref"]);
				echo $gf->utf8("<inpu type='hidden' value='$ref' name='id_ref' id='id_ref' class='univalnewmess' />");
				$curas=$gf->dataSet("SELECT ASUNTO,ID_FROM,CONTENIDO FROM mensajes WHERE ID_MSG=:mensaje",array(":mensaje"=>$ref));
				if(count($curas)>0){
					$curasunto="Re: " . $curas[0]["ASUNTO"];
					$curto=$curas[0]["ID_FROM"];
					$curcon=$curas[0]["CONTENIDO"];
					$condires="ID_USUARIO='$curto'";
					$curcontent="Mensaje Original: ".$curcon."------------------------------------------\n\n";
				}
			}
			?>
			<div class="col-md-12">
			  <div class="box box-primary">
				<div class="box-header with-border">
				  <h3 class="box-title">Redactar Nuevo Mensaje</h3>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
				  <div class="form-group">
					<select name='msgto' id='msgto' class='form-control univalnewmess'>
					<?php
					$rols=$relaciones["usuarios"]["campos"]["PERFIL"]["arraycont"];
					$rols["Z"]="Soporte DataFeed";
					if($_SESSION["restprofile"]=="Z"){
						$condisitio="1";
					}else{
						$condisitio="(U.ID_SITIO={$_SESSION["restbus"]} OR U.ID_SITIO=0)";
					}
					$users=$gf->dataSet("SELECT U.ID_USUARIO, CONCAT(U.NOMBRES,' ',U.APELLIDOS) AS USUARIO, U.ID_SITIO, U.PERFIL, S.NOMBRE AS SITIO FROM usuarios U LEFT JOIN sitios S ON U.ID_SITIO=S.ID_SITIO WHERE $condisitio AND U.PERFIL<>'M' AND $condires ORDER BY U.NOMBRES, U.APELLIDOS");
					foreach($users as $user){
						$id_us=$user["ID_USUARIO"];
						$nm_us=$user["USUARIO"];
						$perfil=$user["PERFIL"];
						$sitio=$user["SITIO"];

						$rol=$rols[$perfil];

						echo $gf->utf8("<option value='$id_us'>$nm_us ($rol/$sitio)</option>");
					}
					?>
					</select>
				  </div>
				  <div class="form-group">
					<input class="form-control univalnewmess" name="asunto" id="asunto" placeholder="Asunto:" value="<?=$curasunto?>">
				  </div>
				  <div class="form-group">
						<textarea name="contenido" id="contenido" class="form-control univalnewmess" style="height: 200px"><?=$curcontent?></textarea>
				  </div>
				</div>
				<!-- /.box-body -->
				<div class="box-footer">
				  <div class="pull-right">
					<button type="submit" class="btn btn-primary" onclick="cargaHTMLvars('ModalContent_<?=$rnd?>','<?=$sender?>?flag=sendmess&rnd=<?=$rnd?>','','5000','univalnewmess')"><i class="fa fa-envelope-o"></i> Enviar</button>
				  </div>
				  <button type="reset" class="btn btn-default"><i class="fa fa-times"></i> Descartar</button>
				</div>
				<!-- /.box-footer -->
			  </div>
			  <!-- /. box -->
			</div>

				
			<?php
		}elseif($actividad=="sendmess"){
			$id_from=$_SESSION["restuiduser"];
			$id_to=$_POST["msgto"];
			$asunto=$gf->utf8($_POST["asunto"]);
			$contenido=$gf->utf8($_POST["contenido"]);
			$rnd=$gf->cleanVar($_GET["rnd"]);
			$ref=isset($_POST["id_ref"]) ? $_POST["id_ref"] : "0";
			$ok=$gf->dataIn("INSERT INTO mensajes (ID_FROM,ID_TO,ASUNTO,CONTENIDO,FECHA,ID_REF) VALUES (:id_from,:id_to,:asunto,:contenido,NOW(),:ref)",array(":id_from"=>$id_from,":id_to"=>$id_to,":asunto"=>$asunto,":contenido"=>$contenido,":ref"=>$ref));
			if($ok){
				echo $gf->utf8("Mensaje enviado!");
			}else{
				echo $gf->utf8("Hubo un problema al enviar el mensaje");
			}
			echo $gf->utf8("<hr /><button class='btn btn-success btn-sm' onclick=\"closeD('$rnd')\">Terminar</button>");
		}else{
			echo "Ninguna solicitud";
		}
		
	}else{
		echo "No has iniciado sesion!";
	}
?>