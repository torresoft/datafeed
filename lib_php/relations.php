<?php
	$relaciones[][]=array();
	$relaciones["usuarios"]["pkey"]='ID_USUARIO';
	$relaciones["usuarios"]["pname"]='NOMBRE';
	$relaciones["usuarios"]["alias"]='USUARIOS DEL SISTEMA';
	$relaciones["usuarios"]["campos"]=array(
		'ID_USUARIO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRES'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRES"),
		'APELLIDOS'=>array("type"=>"text","obl"=>true,"alias"=>"APELLIDOS"),
		'CORREO'=>array("type"=>"text","obl"=>true,"alias"=>"CORREO"),
		'AVATAR'=>array("type"=>"image","obl"=>false,"alias"=>"AVATAR"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"MIEMBRO"),
		'PERFIL'=>array("type"=>"array","obl"=>true,"alias"=>"PERFIL","arraycont"=>array("A"=>"ADMINISTRADOR","C"=>"CHEF","J"=>"CAJERO","M"=>"TENDER","T"=>"CONTADOR")),
		'ESTADO'=>array("type"=>"boolean","obl"=>true,"alias"=>"USUARIO ACTIVO")
	);
	
	$relaciones["platos_categorias"]["pkey"]='ID_CATEGORIA';
	$relaciones["platos_categorias"]["pname"]='NOMBRE';
	$relaciones["platos_categorias"]["alias"]='CATEGORIAS DE PLATOS';
	$relaciones["platos_categorias"]["campos"]=array(
		'ID_CATEGORIA'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'POSITION'=>array("type"=>"text","obl"=>true,"alias"=>"POSICI&Oacute;N"),
		'ICONO'=>array("type"=>"image","obl"=>false,"alias"=>"ICONO"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"MIEMBRO")
	);

	$relaciones["platos"]["pkey"]='ID_PLATO';
	$relaciones["platos"]["pname"]='NOMBRE';
	$relaciones["platos"]["alias"]='PLATOS';
	$relaciones["platos"]["campos"]=array(
		'ID_PLATO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'DESCRIPCION'=>array("type"=>"textarea","obl"=>false,"alias"=>"DESCRIPCION"),
		'HOMOPLATO'=>array("type"=>"text","obl"=>false,"alias"=>"CODIGO HOMOLOGACION FAC ELEC"),
		"ID_CATEGORIA"=>array("type"=>"rel","table"=>"platos_categorias","name"=>"NOMBRE","fk"=>"ID_CATEGORIA","obl"=>true,"alias"=>"CATEGORIA"),
		'COCINA'=>array("type"=>"array","obl"=>true,"alias"=>"ORIGEN","arraycont"=>array('1'=>'PLATO (SE PREPARA)','0'=>'PRODUCTO EXTERNO (NO SE PREPARA)')),
		"ID_COCINA"=>array("type"=>"rel","table"=>"cocinas","name"=>"NOMBRE","fk"=>"ID_COCINA","obl"=>true,"alias"=>"COCINA EN QUE SE PREPARA"),
		"ID_IMPUESTO"=>array("type"=>"rel","table"=>"impuestos","name"=>"NOMBRE","fk"=>"ID_IMPUESTO","obl"=>false,"alias"=>"IMPUESTO A APLICAR"),
		'PRECIO'=>array("type"=>"number","obl"=>true,"alias"=>"PRECIO"),
		'PRECIO_DOM'=>array("type"=>"number","obl"=>true,"alias"=>"PRECIO A DOMICILIO"),
		'PRECIO_EDITABLE'=>array("type"=>"boolean","obl"=>true,"alias"=>"PRECIO EDITABLE"),
		'TIPO_PLATO'=>array("type"=>"array","obl"=>true,"alias"=>"TIPO DE PLATO POR DEFECTO","arraycont"=>array('1'=>'ENTRADA','2'=>'PLATO FUERTE','3'=>'NO APLICA')),
		'ESTADO'=>array("type"=>"boolean","obl"=>true,"alias"=>"ACTIVO")
	);

	$relaciones["mesas"]["pkey"]='ID_MESA';
	$relaciones["mesas"]["pname"]='NOMBRE';
	$relaciones["mesas"]["alias"]='MESAS';
	$relaciones["mesas"]["campos"]=array(
		'ID_MESA'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'TIPO'=>array("type"=>"array","obl"=>true,"alias"=>"ESQUEMA","arraycont"=>array('M'=>'MESA EN INTERIOR','E'=>'MESA EXTERIOR','D'=>'DOMICILIO')),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"MIEMBRO"),
		"ID_GRUPO"=>array("type"=>"rel","table"=>"mesas_grupos","name"=>"NOMBRE","fk"=>"ID_GRUPO","obl"=>true,"alias"=>"GRUPO"),
		'ESTADO'=>array("type"=>"boolean","obl"=>true,"alias"=>"MESA ACTIVA")
	);

	$relaciones["cocinas"]["pkey"]='ID_COCINA';
	$relaciones["cocinas"]["pname"]='NOMBRE';
	$relaciones["cocinas"]["alias"]='COCINAS';
	$relaciones["cocinas"]["campos"]=array(
		'ID_COCINA'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'IMPRESORA'=>array("type"=>"text","obl"=>true,"alias"=>"IMPRESORA"),
		'INTERFAZ'=>array("type"=>"array","obl"=>true,"alias"=>"INTERFAZ","arraycont"=>array('usb'=>'LOCAL USB','ethernet'=>'EN RED','file'=>'A ARCHIVO')),
		'PUERTO'=>array("type"=>"text","obl"=>true,"alias"=>"PUERTO"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"MIEMBRO")
	);


	$relaciones["mesas_grupos"]["pkey"]='ID_GRUPO';
	$relaciones["mesas_grupos"]["pname"]='NOMBRE';
	$relaciones["mesas_grupos"]["alias"]='GRUPOS DE MESAS (ZONAS)';
	$relaciones["mesas_grupos"]["campos"]=array(
		'ID_GRUPO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'COLOR'=>array("type"=>"color","obl"=>false,"alias"=>"COLOR"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"MIEMBRO")
	);

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
		'PRINTER_HOST'=>array("type"=>"text","obl"=>true,"alias"=>"RUTA DE IMPRESION (ej:http://192.168.0.50/server/"),
		'PREFIJO'=>array("type"=>"text","obl"=>false,"alias"=>"PREFIJO FACTURAS"),
		'INIFACT'=>array("type"=>"number","min"=>0,"max"=>999999999999999,"obl"=>true,"alias"=>"INICIAR FACTURAS EN:"),
		'LOGO'=>array("type"=>"image","obl"=>true,"alias"=>"LOGO"),
		'TENDER_CANCEL'=>array("type"=>"array","obl"=>true,"alias"=>"PUEDE CANCELAR PEDIDOS","arraycont"=>array('2'=>'CUALQUIER USUARIO','1'=>'ADMINISTRADOR Y CAJERO','0'=>'SOLO ADMINISTRADOR')),
		'TENDER_CANTS'=>array("type"=>"array","obl"=>true,"alias"=>"PUEDE PARA BAJAR CANTIDADES COMANDADAS","arraycont"=>array('2'=>'CUALQUIER USUARIO','1'=>'ADMINISTRADOR Y CAJERO','0'=>'SOLO ADMINISTRADOR')),
		'MANCOMUN'=>array("type"=>"array","obl"=>true,"alias"=>"PUEDE GESTIONAR UN PEDIDO (Este permiso tiene efecto dependiendo de los permisos anteriores)","arraycont"=>array('3'=>'CUALQUIER USUARIO','2'=>'QUIEN LO ABRE, ADMINISTRADOR Y CAJERO','1'=>'QUIEN LO ABRE Y EL ADMINISTRADOR','0'=>'SOLO QUIEN LO ABRE')),
		'CAJERO_SERV'=>array("type"=>"array","obl"=>true,"alias"=>"CAJERO PUEDE ADMINISTRAR SERVICIOS","arraycont"=>array('1'=>'SI','0'=>'NO')),
		'CAJERO_FISCAL'=>array("type"=>"array","obl"=>true,"alias"=>"CAJERO PUEDE VER INFORMACI&Oacute;N FINANCIERA","arraycont"=>array('1'=>'TODO','0'=>'NADA','2'=>'SOLO ESTADO DE CAJA')),
		'SYS_ACCORDION'=>array("type"=>"array","obl"=>true,"alias"=>"LISTADO DE PLATOS EN PEDIDO","arraycont"=>array('0'=>'MOSTRAR PLATOS Y CATEGORIAS','1'=>'MOSTRAR CATEGORIAS Y CLIC PARA LOS PLATOS')),
		'IMPRESORA'=>array("type"=>"text","obl"=>true,"alias"=>"IMPRESORA DE CAJA"),
		'PROPINAS'=>array("type"=>"array","obl"=>true,"alias"=>"SERVICIO (PROPINAS)","arraycont"=>array('0'=>'SIN PROPINAS','5'=>'5%','10'=>"10%",'15'=>'15%','20'=>'20%','25'=>'25%')),
		"CL_COMODIN"=>array("type"=>"rel","table"=>"clientes","name"=>"NOMBRE","fk"=>"ID_CLIENTE","obl"=>false,"alias"=>"CONSUMIDOR FINAL")
	);


	$relaciones["ingredientes"]["pkey"]='ID_INGREDIENTE';
	$relaciones["ingredientes"]["pname"]='NOMBRE';
	$relaciones["ingredientes"]["alias"]='LISTADO DE INGREDIENTES';
	$relaciones["ingredientes"]["campos"]=array(
		'ID_INGREDIENTE'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'DESCRIPCION'=>array("type"=>"text","obl"=>false,"alias"=>"DESCRIPCION"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"MIEMBRO"),
		'UNIDAD_MEDIDA'=>array("type"=>"array","obl"=>true,"alias"=>"UNIDAD DE MEDIDA","arraycont"=>array('1'=>'Gramos','2'=>'Mililitros','5'=>'Onzas','3'=>'Centimetros','4'=>'Unidades')),
		'UNIDADES_PRESENTACION'=>array("type"=>"number","obl"=>true,"alias"=>"UNIDADES POR PRESENTACION"),
		'COSTO_PRESENTACION'=>array("type"=>"number","obl"=>true,"alias"=>"COSTO POR PRESENTACION"),
		'MINIMA'=>array("type"=>"number","obl"=>true,"alias"=>"UNIDADES M&Iacute;NIMAS")
	);
	
	
	$relaciones["pedidos"]["pkey"]='ID_PEDIDO';
	$relaciones["pedidos"]["pname"]='ID_PEDIDO';
	$relaciones["pedidos"]["alias"]='PEDIDO';
	$relaciones["pedidos"]["campos"]=array(
		'ID_PEDIDO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'APERTURA'=>array("type"=>"datetime","obl"=>true,"alias"=>"APERTURA"),
		"ID_SERVICIO"=>array("type"=>"rel","table"=>"servicio","name"=>"FECHA","fk"=>"ID_SERVICIO","obl"=>true,"alias"=>"MIEMBRO")
	);

	$relaciones["pedidos_abonos"]["pkey"]='ID_ABONO';
	$relaciones["pedidos_abonos"]["pname"]='VALOR';
	$relaciones["pedidos_abonos"]["alias"]='ABONOS PEDIDO';
	$relaciones["pedidos_abonos"]["campos"]=array(
		'ID_ABONO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'VALOR'=>array("type"=>"number","obl"=>true,"alias"=>"VALOR"),
		'OBSERVACION'=>array("type"=>"text","obl"=>false,"alias"=>"OBSERVACION"),
		'ID_USUARIO'=>array("type"=>"curuser","obl"=>true,"alias"=>"REGISTRA"),
		'FECHA'=>array("type"=>"now","obl"=>true,"alias"=>"FECHA DE REGISTRO"),
		"ID_PEDIDO"=>array("type"=>"rel","table"=>"pedidos","name"=>"ID_PEDIDO","fk"=>"ID_PEDIDO","obl"=>true,"alias"=>"PEDIDO"),
		"ID_FP"=>array("type"=>"rel","table"=>"formas_pago","name"=>"NOMBRE","fk"=>"ID_FP","obl"=>true,"alias"=>"FORMA DE PAGO")
	);

	$relaciones["platos_composicion"]["pkey"]='ID_RACION';
	$relaciones["platos_composicion"]["pname"]='NOMBRE';
	$relaciones["platos_composicion"]["alias"]='COMPOSICION DEL PLATO';
	$relaciones["platos_composicion"]["campos"]=array(
		'ID_RACION'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'DESCRIPCION'=>array("type"=>"text","obl"=>false,"alias"=>"DESCRIPCION"),
		'BAJA_PRECIO'=>array("type"=>"number","obl"=>true,"alias"=>"BAJA EL PRECIO"),
		"ID_PLATO"=>array("type"=>"rel","table"=>"platos","name"=>"NOMBRE","fk"=>"ID_PLATO","obl"=>true,"alias"=>"PLATO")
	);
	
	
	

	$relaciones["racion_opciones"]["pkey"]='ID_OPCION';
	$relaciones["racion_opciones"]["pname"]='NOMBRE';
	$relaciones["racion_opciones"]["alias"]='OPCIONES DE PORCION';
	$relaciones["racion_opciones"]["campos"]=array(
		'ID_OPCION'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'DESCRIPCION'=>array("type"=>"text","obl"=>false,"alias"=>"DESCRIPCION"),
		"ID_RACION"=>array("type"=>"rel","table"=>"platos_composicion","name"=>"NOMBRE","fk"=>"ID_RACION","obl"=>true,"alias"=>"PORCION")
	);
	
		

	$relaciones["proveedores"]["pkey"]='ID_PROVEEDOR';
	$relaciones["proveedores"]["pname"]='NOMBRE';
	$relaciones["proveedores"]["alias"]='PROVEEDORES';
	$relaciones["proveedores"]["campos"]=array(
		'ID_PROVEEDOR'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NIT'=>array("type"=>"text","obl"=>true,"alias"=>"NIT O IDENTIFICACION"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'DIRECCION'=>array("type"=>"text","obl"=>true,"alias"=>"DIRECCION"),
		'TELEFONO'=>array("type"=>"text","obl"=>true,"alias"=>"TELEFONO"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"MIEMBRO")
	);
	
	
	
	$relaciones["inventario_compras"]["pkey"]='ID_COMPRA';
	$relaciones["inventario_compras"]["pname"]="CONCAT(OBSERVACION,' ',FECHA)";
	$relaciones["inventario_compras"]["alias"]='COMPRAS A INVENTARIO';
	$relaciones["inventario_compras"]["campos"]=array(
		'ID_PROVEEDOR'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'OBSERVACION'=>array("type"=>"text","obl"=>true,"alias"=>"DESCRIPCI&Oacute;N"),
		'FECHA'=>array("type"=>"date","obl"=>true,"alias"=>"FECHA"),
		'ID_USUARIO'=>array("type"=>"curuser","obl"=>true,"alias"=>"USUARIO QUE REGISTRA"),
		'FACTURA'=>array("type"=>"text","obl"=>true,"alias"=>"No. FACTURA"),
		"ID_PROVEEDOR"=>array("type"=>"rel","table"=>"proveedores","name"=>"NOMBRE","fk"=>"ID_PROVEEDOR","obl"=>true,"alias"=>"PROVEEDOR"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"MIEMBRO"),
		"ID_SERVICIO"=>array("type"=>"rel","table"=>"servicio","name"=>"FECHA","fk"=>"ID_SERVICIO","obl"=>false,"alias"=>"PAGADO CON CAJA"),
		"ID_FP"=>array("type"=>"rel","table"=>"formas_pago","name"=>"NOMBRE","fk"=>"ID_FP","obl"=>true,"alias"=>"FORMA DE PAGO")
	);

	
	$relaciones["servicio"]["pkey"]='ID_SERVICIO';
	$relaciones["servicio"]["pname"]="FECHA";
	$relaciones["servicio"]["alias"]='INSTANCIAS DE SERVICIO';
	$relaciones["servicio"]["campos"]=array(
		'ID_SERVICIO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'FECHA'=>array("type"=>"date","obl"=>true,"alias"=>"FECHA"),
		'BASE_CAJA'=>array("type"=>"number","obl"=>true,"alias"=>"BASE DE CAJA $"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"MIEMBRO")
	);
	
	$relaciones["impuestos"]["pkey"]='ID_IMPUESTO';
	$relaciones["impuestos"]["pname"]="NOMBRE";
	$relaciones["impuestos"]["alias"]='IMPUESTOS';
	$relaciones["impuestos"]["campos"]=array(
		'ID_IMPUESTO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE IMPUESTO"),
		'PORCENTAJE'=>array("type"=>"number","obl"=>true,"alias"=>"PORCENTAJE A APLICAR"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"MIEMBRO")
	);
		
	$relaciones["sitios_tarifas"]["pkey"]='ID_TARIFA';
	$relaciones["sitios_tarifas"]["pname"]="VALOR";
	$relaciones["sitios_tarifas"]["alias"]='REGISTRO DE PAGOS';
	$relaciones["sitios_tarifas"]["campos"]=array(
		'ID_TARIFA'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'FECHA'=>array("type"=>"date","obl"=>true,"alias"=>"TARIFA DESDE:"),
		'VALOR'=>array("type"=>"number","obl"=>true,"alias"=>"VALOR PERIODO"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"AFILIADO")
	);	

	$relaciones["sitios_pagos"]["pkey"]='ID_PAGO';
	$relaciones["sitios_pagos"]["pname"]="FECHA";
	$relaciones["sitios_pagos"]["alias"]='REGISTRO DE PAGOS';
	$relaciones["sitios_pagos"]["campos"]=array(
		'ID_PAGO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'FECHA'=>array("type"=>"date","obl"=>true,"alias"=>"FECHA DE PAGO"),
		'VALOR'=>array("type"=>"number","obl"=>true,"alias"=>"VALOR"),
		'ID_USUARIO'=>array("type"=>"curuser","obl"=>true,"alias"=>"USUARIO"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"AFILIADO")
	);

	
	$relaciones["formas_pago"]["pkey"]='ID_FP';
	$relaciones["formas_pago"]["pname"]="NOMBRE";
	$relaciones["formas_pago"]["alias"]='REGISTRO DE PAGOS';
	$relaciones["formas_pago"]["campos"]=array(
		'ID_FP'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"FORMA DE PAGO"),
		'ICONO'=>array("type"=>"icon","obl"=>false,"alias"=>"ICONO"),
		'CAJA'=>array("type"=>"boolean","obl"=>true,"alias"=>"AFECTA CAJA"),
		'CREDITO'=>array("type"=>"boolean","obl"=>true,"alias"=>"ES CARTERA (NO SUMA EN CUADRE DE CAJA)"),
		'POSICION'=>array("type"=>"number","obl"=>true,"alias"=>"POSICI&Oacute;N"),
		'FEL_CODE'=>array("type"=>"text","obl"=>true,"alias"=>"C&Oacute;DIGO FAC. ELECTR&Oacute;NICA"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"AFILIADO")
	);

	
	$relaciones["cartera_ingresos"]["pkey"]='ID_INGRESO';
	$relaciones["cartera_ingresos"]["pname"]="FECHA";
	$relaciones["cartera_ingresos"]["alias"]='REGISTRO DE PAGOS CARTERA';
	$relaciones["cartera_ingresos"]["campos"]=array(
		'ID_INGRESO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		"ID_SERVICIO"=>array("type"=>"rel","table"=>"servicio","name"=>"FECHA","fk"=>"ID_SERVICIO","obl"=>true,"alias"=>"SERVICIO"),
		'ID_USUARIO'=>array("type"=>"curuser","obl"=>false,"alias"=>"USUARIO"),
		"ID_FPC"=>array("type"=>"rel","table"=>"formas_pago","name"=>"NOMBRE","fk"=>"ID_FP","obl"=>true,"alias"=>"CARTERA"),
		"ID_FPP"=>array("type"=>"rel","table"=>"formas_pago","name"=>"NOMBRE","fk"=>"ID_FP","obl"=>true,"alias"=>"FORMA DE PAGO (INGRESO)"),
		'VALOR'=>array("type"=>"number","obl"=>true,"alias"=>"VALOR"),
		"FECHA"=>array("type"=>"now","obl"=>true,"alias"=>"FECHA DE REGISTRO")
	);

	$relaciones["gastos_tipos"]["pkey"]='ID_TIPO';
	$relaciones["gastos_tipos"]["pname"]="NOMBRE";
	$relaciones["gastos_tipos"]["alias"]='TIPOS DE GASTOS';
	$relaciones["gastos_tipos"]["campos"]=array(
		'ID_TIPO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"TIPO DE GASTO"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"AFILIADO")
	);
	
	
	
	$relaciones["gastos"]["pkey"]='ID_GASTO';
	$relaciones["gastos"]["pname"]="CONCAT(FECHA,' ',DESCRIPCION)";
	$relaciones["gastos"]["alias"]='REGISTRO DE GASTOS';
	$relaciones["gastos"]["campos"]=array(
		'ID_GASTO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'DESCRIPCION'=>array("type"=>"text","obl"=>true,"alias"=>"DESCRIPCION"),
		'FECHA'=>array("type"=>"date","obl"=>true,"alias"=>"FECHA DE PAGO"),
		"ID_TIPO"=>array("type"=>"rel","table"=>"gastos_tipos","name"=>"NOMBRE","fk"=>"ID_TIPO","obl"=>true,"alias"=>"TIPO DE GASTO"),
		'VALOR'=>array("type"=>"float","obl"=>false,"alias"=>"VALOR"),
		"ID_FP"=>array("type"=>"rel","table"=>"formas_pago","name"=>"NOMBRE","fk"=>"ID_FP","obl"=>true,"alias"=>"FORMA DE PAGO"),
		'ID_USUARIO'=>array("type"=>"curuser","obl"=>false,"alias"=>"USUARIO"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"AFILIADO"),
		"ID_SERVICIO"=>array("type"=>"rel","table"=>"servicio","name"=>"FECHA","fk"=>"ID_SERVICIO","obl"=>false,"alias"=>"PAGADO CON CAJA"),
		"ID_COCINA"=>array("type"=>"rel","table"=>"cocinas","name"=>"NOMBRE","fk"=>"ID_COCINA","obl"=>false,"alias"=>"RELACIONAR A UNA COCINA")
	);
	
	$relaciones["sillas_platos"]["pkey"]='ID_ITEM';
	$relaciones["sillas_platos"]["pname"]='COLOR';
	$relaciones["sillas_platos"]["alias"]='PLATOS POR SILLA';
	
	$relaciones["ajustes_caja"]["pkey"]='ID_AJUSTE';
	$relaciones["ajustes_caja"]["pname"]='OBSERVACION';
	$relaciones["ajustes_caja"]["alias"]='AJUSTES';


	$relaciones["inventario_bajas_motivos"]["pkey"]='ID_MOTIVO';
	$relaciones["inventario_bajas_motivos"]["pname"]="NOMBRE";
	$relaciones["inventario_bajas_motivos"]["alias"]='MOTIVOS PARA BAJAS MANUALES';
	$relaciones["inventario_bajas_motivos"]["campos"]=array(
		'ID_MOTIVO'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE MOTIVO"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"AFILIADO")
	);

	
	$relaciones["inventario_bajas"]["pkey"]='ID_REL';
	$relaciones["inventario_bajas"]["pname"]="FECHA";
	$relaciones["inventario_bajas"]["alias"]='REGISTRO DE BAJAS MANUALES';
	$relaciones["inventario_bajas"]["campos"]=array(
		'ID_REL'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		"ID_INGREDIENTE"=>array("type"=>"rel","table"=>"ingredientes","name"=>"NOMBRE","fk"=>"ID_INGREDIENTE","obl"=>true,"alias"=>"INGREDIENTE AFECTADO"),
		'FECHA'=>array("type"=>"now","obl"=>true,"alias"=>"FECHA DE BAJA"),
		"ID_MOTIVO"=>array("type"=>"rel","table"=>"inventario_bajas_motivos","name"=>"NOMBRE","fk"=>"ID_MOTIVO","obl"=>true,"alias"=>"MOTIVO DE BAJA"),
		'CANTIDAD'=>array("type"=>"number","obl"=>false,"alias"=>"CANTIDAD (EN UNIDADES MINIMAS, EJ: GRAMOS)"),
		'OBSERVACION'=>array("type"=>"text","obl"=>true,"alias"=>"OBSERVACIONES"),
		'ID_USUARIO'=>array("type"=>"curuser","obl"=>false,"alias"=>"USUARIO QUE REGISTRA")
	);
	
		
	$relaciones["inventario"]["pkey"]='ID_REL';
	$relaciones["inventario"]["pname"]="CANTIDAD";
	$relaciones["inventario"]["alias"]='INVENTARIO';
	$relaciones["inventario"]["campos"]=array(
		'ID_REL'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		"ID_COMPRA"=>array("type"=>"rel","table"=>"inventario_compras","name"=>"OBSERVACION","fk"=>"ID_COMPRA","obl"=>true,"alias"=>"COMPRA"),
		"ID_INGREDIENTE"=>array("type"=>"rel","table"=>"ingredientes","name"=>"NOMBRE","fk"=>"ID_INGREDIENTE","obl"=>true,"alias"=>"INGREDIENTE COMPRADO"),
		'CANTIDAD'=>array("type"=>"float","obl"=>false,"alias"=>"CANTIDAD PRESENTACIONES"),
		'PRECIO'=>array("type"=>"float","obl"=>false,"alias"=>"COSTO POR PRESENTACI&Oacute;N")
	);


	
	$relaciones["clientes"]["pkey"]='ID_CLIENTE';
	$relaciones["clientes"]["pname"]='NOMBRE';
	$relaciones["clientes"]["alias"]='LISTADO DE CLIENTES';
	$relaciones["clientes"]["campos"]=array(
		'ID_CLIENTE'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		'TIPO_ID'=>array("type"=>"array","obl"=>true,"alias"=>"TIPO IDENTIFICACION","arraycont"=>array('CC'=>'CEDULA','NI'=>'NIT','CE'=>'EXTRANJERIA')),
		'IDENTIFICACION'=>array("type"=>"text","obl"=>true,"alias"=>"IDENTIFICACI&Oacute;N"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'DIRECCION'=>array("type"=>"text","obl"=>true,"alias"=>"DIRECCION"),
		'TELEFONO'=>array("type"=>"text","obl"=>true,"alias"=>"TELEFONO"),
		'CORREO'=>array("type"=>"text","obl"=>true,"alias"=>"CORREO"),
		"CIUDAD"=>array("type"=>"rel","table"=>"ciudades","name"=>"NOMBRE","fk"=>"COD","obl"=>true,"alias"=>"CIUDAD"),
		"ID_SITIO"=>array("type"=>"rel","table"=>"sitios","name"=>"NOMBRE","fk"=>"ID_SITIO","obl"=>true,"alias"=>"MIEMBRO")
	);
	
	
	$relaciones["reservas"]["pkey"]='ID_RESERVA';
	$relaciones["reservas"]["pname"]='NOMBRE';
	$relaciones["reservas"]["alias"]='RESERVAS';
	$relaciones["reservas"]["campos"]=array(
		'ID_RESERVA'=>array("type"=>"auto","obl"=>true,"min"=>"","max"=>"","size"=>"","alias"=>"ID"),
		"ID_CLIENTE"=>array("type"=>"rel","table"=>"clientes","name"=>"NOMBRE","fk"=>"ID_CLIENTE","obl"=>true,"alias"=>"CLIENTE"),
		'NOMBRE'=>array("type"=>"text","obl"=>true,"alias"=>"NOMBRE"),
		'CREATED'=>array("type"=>"datetime","obl"=>true,"alias"=>"INGRESADA"),
		'FECHA'=>array("type"=>"datetime","obl"=>true,"alias"=>"FECHA Y HORA RESERVA"),
		"ID_USUARIO"=>array("type"=>"rel","table"=>"usuarios","name"=>"NOMBRES","fk"=>"ID_USUARIO","obl"=>true,"alias"=>"USUARIO QUE INGRESA"),
		"ID_MESA"=>array("type"=>"rel","table"=>"mesas","name"=>"NOMBRE","fk"=>"ID_MESA","obl"=>true,"alias"=>"MESA"),
		'ESTADO'=>array("type"=>"array","obl"=>true,"alias"=>"ESTADO RESERVA","arraycont"=>array('0'=>'EN ESPERA','1'=>'CUMPLIDA','2'=>'INCUMPLIDA','3'=>'CANCELADA')),
	);
?>