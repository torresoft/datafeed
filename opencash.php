<?php
session_start();
	require_once("./config.php");
	require_once("./lib_php/generalFunctions.class.php");

	$gf=new generalFunctions;

   
    $curConf=$gf->dataSet("SELECT PRINTER_HOST, CLIENT_KEY, CLIENT_SEC FROM sitios WHERE ID_SITIO=:sitio",array(":sitio"=>$_SESSION["restbus"]));
    $prhs=$curConf[0]["PRINTER_HOST"];
    echo $gf->utf8("
    <script>
        $(function () {
        
        $.ajax({
            type: 'POST',
            url: '$prhs',
            data: {rectype:'opencash'},
            success: function(data, textStatus, jqXHR)
            {
                $('#alertaimpresora').hide();
            },
            error : function(xhr, textStatus, errorThrown ) {
                $('#alertaimpresora').show();
                console.log('Motor de impresion local fuera' + errorThrown);
            }
        });		
        });
    </script>
    
    ");

			
?>