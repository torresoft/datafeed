<?php
	$relaciones[][]=array();
	

	$relaciones["sitios"]["pkey"]='ID_SITIO';
	$relaciones["sitios"]["pname"]='NOMBRE';
	$relaciones["sitios"]["alias"]='MIEMBROS';
	$relaciones["sitios"]["campos"]=array(
		'ID_SITIO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NIT'=>array("type"=>"text","obl"=>true,"alias"=>"NIT"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'CIUDAD'=>array("type"=>"text","obl"=>true,"alias"=>"CIUDAD"),
		'DIRECCION'=>array("type"=>"text","obl"=>true,"alias"=>"DIRECCION"),
		'TELEFONO'=>array("type"=>"text","obl"=>true,"alias"=>"TELEFONO"),
		'PROPIETARIO'=>array("type"=>"text","obl"=>true,"alias"=>"PROPIETARIO"),
		'REGIMEN'=>array("type"=>"array","obl"=>true,"alias"=>"REGIMEN TRIBUTARIO","arraycont"=>array('S'=>'SIMPLIFICADO','C'=>'COMUN')),
		'IMPUESTO_INCLUIDO'=>array("type"=>"array","obl"=>true,"alias"=>"MANEJO DE IMPUESTOS","arraycont"=>array('1'=>'INCLUIDO EN EL PRECIO FINAL','0'=>'SUMAR AL PRECIO FINAL')),
		'AUTOGEST'=>array("type"=>"array","obl"=>true,"alias"=>"ENTREGAS AUTOMATIZADAS","arraycont"=>array('1'=>'SI (AUTO)','0'=>'NO (MESERO MARCA LA ENTREGA)')),
		'PRINTER_HOST'=>array("type"=>"text","obl"=>true,"alias"=>"RUTA DE IMPRESION (ej:http://192.168.0.50/server/"),
		'INIFACT'=>array("type"=>"number","min"=>0,"max"=>999999999999999,"obl"=>true,"alias"=>"INICIAR FACTURAS EN:"),
		'LOGO'=>array("type"=>"image","obl"=>true,"alias"=>"LOGO"),
		'SYS_CHAIRS'=>array("type"=>"array","obl"=>true,"alias"=>"METODOLOGIA DE PEDIDO","arraycont"=>array('1'=>'POR SILLAS','0'=>'POR MESAS')),
		'ECOMANDA'=>array("type"=>"array","obl"=>true,"alias"=>"TIPO DE COMANDA","arraycont"=>array('1'=>'COMANDA ELECTRONICA (MONITOR EN COCINA)','0'=>'IMPRESORA DE COMANDAS')),
		'TENDER_CANCEL'=>array("type"=>"array","obl"=>true,"alias"=>"PERMISO CANCELAR PEDIDOS","arraycont"=>array('1'=>'TODOS PUEDEN CANCELAR PEDIDOS','0'=>'LOS MESEROS NO PUEDEN CANCELAR PEDIDOS')),
		'TENDER_CANTS'=>array("type"=>"array","obl"=>true,"alias"=>"PERMISO PARA BAJAR CANTIDADES","arraycont"=>array('1'=>'MESERO Y CAJERO','0'=>'SOLO ADMINISTRADOR')),
		'CAJERO_SERV'=>array("type"=>"array","obl"=>true,"alias"=>"CAJERO PUEDE ADMINISTRAR SERVICIOS","arraycont"=>array('1'=>'SI','0'=>'NO')),
		'SYS_ACCORDION'=>array("type"=>"array","obl"=>true,"alias"=>"LISTADO DE PLATOS EN PEDIDO","arraycont"=>array('0'=>'MOSTRAR PLATOS Y CATEGORIAS','1'=>'MOSTRAR CATEGORIAS Y CLIC PARA LOS PLATOS')),
		'IMPRESORA'=>array("type"=>"text","obl"=>true,"alias"=>"IMPRESORA DE CAJA"),
		'PROPINAS'=>array("type"=>"array","obl"=>true,"alias"=>"SERVICIO (PROPINAS)","arraycont"=>array('0'=>'SIN PROPINAS','5'=>'5%','10'=>"10%",'15'=>'15%','20'=>'20%','25'=>'25%')),
		'ID_CLIENT'=>array("type"=>"text","obl"=>true,"alias"=>"DOMAINKEY"),
		'CLIENT_KEY'=>array("type"=>"text","obl"=>true,"alias"=>"K.PUB"),
		'CLIENT_SEC'=>array("type"=>"text","obl"=>true,"alias"=>"K.SEC"),
		'FECHA_REGISTRO'=>array("type"=>"date","obl"=>true,"alias"=>"REGISTRO"),
        'INICIO_FACTURACION'=>array("type"=>"date","obl"=>true,"alias"=>"INICIO COBRO"),
        'KNZ'=>array("type"=>"array","obl"=>true,"alias"=>"CONSECUTIVO DEL SISTEMA","arraycont"=>array("0"=>"AUTO (SISTEMA)","1"=>"DEL CLIENTE")),
        'PERIODICIDAD'=>array("type"=>"array","obl"=>true,"alias"=>"PERIODOS DE COBRO","arraycont"=>array('1'=>'MENSUAL','2'=>'BIMENSUAL','3'=>'TRIMESTRAL','6'=>'SEMESTRAL','12'=>'ANUAL')),
        'ESTADO'=>array("type"=>"array","obl"=>true,"alias"=>"ESTADO CLIENTE","arraycont"=>array('1'=>'ACTIVO','0'=>'INACTIVO'))
	);


?>