<?php
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"]) && ($_SESSION["restprofile"]=="J" || $_SESSION["restprofile"]=="A")){
	
	require_once("../autoload.php");
	$gf=new generalFunctions;
	global $relaciones;
	$sender=$_SERVER["PHP_SELF"];
	$dataTables=new dsTables();
    $actividad=$gf->cleanVar($_GET["flag"]);
	if($actividad=="fact_start"){
        $fkf["usuarios"]=array("ID_SITIO"=>$_SESSION["restbus"]);
		$gettabla = $dataTables->armaTablaDyRel("reservas","FECHA DESC",1,0,1,"","",$sender,$fkf);
		echo $gf->utf8($gettabla);
	
	}elseif($actividad=="get_client"){
		$docu=$_POST["doc"];
		$cliente=$gf->dataSet("SELECT * FROM clientes WHERE IDENTIFICACION=:ident AND ID_SITIO=:sitio",array(":ident"=>$docu,":sitio"=>$_SESSION["restbus"]));
		if(count($cliente)>0){
			$rw=$cliente[0];
			$TIPO_ID=$rw["TIPO_ID"];
			$IDENTIFICACION=$rw["IDENTIFICACION"];
			$NOMBRE=$rw["NOMBRE"];
			$DIRECCION=$rw["DIRECCION"];
			$TELEFONO=$rw["TELEFONO"];
			$CORREO=$rw["CORREO"];
			echo $gf->utf8("{\"TIPO_ID\":\"".$TIPO_ID."\",\"IDENTIFICACION\":\"".$IDENTIFICACION."\",\"NOMBRE\":\"".$NOMBRE."\",\"DIRECCION\":\"".$DIRECCION."\",\"TELEFONO\":\"".$TELEFONO."\",\"CORREO\":\"".$CORREO."\"}");
		}else{
			echo "{}";
		}
	}elseif($actividad=="nuevo"){
	
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$id_pedido="reserva";
		echo $gf->utf8("
			<div class='callout callout-default'>
                <h4>CREAR RESERVA</h4>
				<div class='row'>
				<div class='col-md-12'>
				DOCUMENTO: *<input onchange=\"queryClient()\" type='text' name='num_doc' id='num_doc' class='form-control unival_dix_$id_pedido' required />
				<small class='red'>El documento es requerido</small>
				</div>
				<div class='col-md-12'>
				TIPO DOCUMENTO: *<select name='tipo_doc' id='tipo_doc' class='form-control  unival_dix_$id_pedido'><option value='CC'>C&eacute;dula</option><option value='NI'>Nit</option></select>
				</div>
				<div class='col-md-12'>
				NOMBRE: *<input type='text' required name='nombre_cliente' id='nombre_cliente' class='form-control unival_dix_$id_pedido' />
				</div>
				<div class='col-md-12'>
				DIRECCION: <input type='text' name='dir_cliente' id='dir_cliente' class='form-control unival_dix_$id_pedido' />
				</div>
				<div class='col-md-12'>
				TELEFONO: *<input type='text' required name='tel_cliente' id='tel_cliente' class='form-control unival_dix_$id_pedido' />
				</div>
				<div class='col-md-12'>
				CORREO: <input type='text' name='mail_cliente' id='mail_cliente' class='form-control unival_dix_$id_pedido' />
                </div>
                
                </div>
                <div class='row'>
                    <div class='col-md-12'>
                        FECHA RESERVA: <input type='text' name='fecha_reserva' id='fecha_reserva' class='form-control datetimepicker unival_dix_$id_pedido' />
                
                    </div>
                    <script>
                        $(function () {
                            $('#fecha_reserva').datetimepicker({
                                viewMode: 'days',
                                format: 'YYYY-MM-DD HH:mm:[00] A',
                                minDate:'".date("Y-m-d h:i:s")."',
                                stepping:30,
                                collapse: false,
                                sideBySide: true,
                                showClose: true,
                                locale:'es'
                            });
                        });
                    </script>
                </div>


                <div class='row'>
                    <div class='col-md-12'>
                        MESA: <select type='text' name='mesa_reserva' id='mesa_reserva' class='form-control unival_dix_$id_pedido'>
                        ");
                        $rsMesas=$gf->dataSet("SELECT ID_MESA, NOMBRE FROM mesas WHERE ID_SITIO='{$_SESSION["restbus"]}' AND TIPO<>'D' ORDER BY NOMBRE");
                        echo $gf->utf8("<option value='0'>Asignar en la llegada</option>");
                        if(count($rsMesas)>0){
                            foreach($rsMesas as $rwMesas){
                                $id_mes=$rwMesas["ID_MESA"];
                                $nm_mesa=$rwMesas["NOMBRE"];
                                echo $gf->utf8("<option value='$id_mes'>$nm_mesa</option>");
                            }
                        }
                        echo $gf->utf8("
                        </select>
                
                    </div>
                </div>
                <hr />
				<button class='btn btn-danger' onclick=\"cargaHTMLvars('ModalContent_$rnd','$sender?flag=fact_pedido2&id_ped=$id_pedido&rnd=$rnd','','4000','unival_dix_$id_pedido')\">Crear Reserva</button>



			</div>");
		
	}elseif($actividad=="fact_pedido2"){
		$rnd=$gf->cleanVar($_GET["rnd"]);
		$id_pedido=$gf->cleanVar($_GET["id_ped"]);

        $rnd=$gf->cleanVar($_GET["rnd"]);
        $num_doc=$_POST["num_doc"];
        $tipo_doc=$_POST["tipo_doc"];
        $nombre_cliente=$_POST["nombre_cliente"];
        $dir_cliente=$_POST["dir_cliente"];
        $tel_cliente=$_POST["tel_cliente"];
        $mail_cliente=$_POST["mail_cliente"];
        $fecha_reserva=$_POST["fecha_reserva"];
        $mesa_reserva=$_POST["mesa_reserva"];
        if($num_doc!="" && $nombre_cliente!="" && $tel_cliente!="" && $fecha_reserva!=""){
            $cliente=$gf->dataSet("SELECT * FROM clientes WHERE IDENTIFICACION=:ident AND ID_SITIO=:sitio",array(":ident"=>$num_doc,":sitio"=>$_SESSION["restbus"]));
            if(count($cliente)>0){
                $ID_CLIENTE=$cliente[0]["ID_CLIENTE"];
            }else{
                $ID_CLIENTE=$gf->dataInLast("INSERT INTO clientes (TIPO_ID,IDENTIFICACION,NOMBRE,DIRECCION,TELEFONO,CORREO,ID_SITIO) VALUES ('$tipo_doc','$num_doc','$nombre_cliente','$dir_cliente','$tel_cliente','$mail_cliente','{$_SESSION["restbus"]}')");
            }
            if($ID_CLIENTE>0){
                $id_reserva=$gf->dataInLast("INSERT INTO reservas (ID_CLIENTE,CREATED,NOMBRE,FECHA,ID_USUARIO,ID_MESA) VALUES ('$ID_CLIENTE',NOW(),'$nombre_cliente','$fecha_reserva','{$_SESSION["restuiduser"]}','$mesa_reserva')");
                if($id_reserva>0){
                    echo $gf->utf8("
                    <div class='callout callout-warning'>
                        <h4>RESERVA GENERADA No. $id_reserva</h4>

                        <p>INFORMACI&Oacute;N:</p>
                        CLIENTE: <b>$nombre_cliente ($num_doc)</b><br />
                        FECHA RESERVA: ".$gf->fechahora_verb($fecha_reserva)."<br />


                        <hr />

                        <button class='btn btn-default' onclick=\"cargaHTMLvars('contenidos','$sender?flag=fact_start');closeD('$rnd')\">Terminar</button>
                    </div>
                    
                    ");
                    $gf->log($_SESSION["restbus"],0,$id_pedido,"RESERVA CREADA ID:$id_reserva Cliente: $nombre_cliente",$_SESSION["restuiduser"]);
                }else{
                    echo "Error 988: No se pudo crear la reserva";
                }
                
            }else{
                echo "Error 987: No se pudo crear la reserva";
            }
        }else{
            echo "Error 978: Faltan datos requeridos";
        }

	}
}else{
	echo "No has iniciado sesion!";
}
?>