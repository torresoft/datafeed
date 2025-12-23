<?php
class generalFunctions{
	function conectarDB(){
		//include_once("../config.php");
		if ($link=mysqli_connect(SITE_HOST, SITE_USER, SITE_PASS, SITE_DB)){
			return $link;
		}else{
			return false;
		}
	}
	function isUserAdm($id_user,$token){
		return true;
	}
	

	function encriptar($cadena){
		$key='plxAt';
		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $cadena, MCRYPT_MODE_CBC, md5(md5($key))));
		return $encrypted;
	 
	}
	 
	function desencriptar($cadena){
		 $key='plxAt';
		 $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($cadena), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
		return $decrypted;
	}
	
	
	function enID($work){
		return (($work*2)+552);
	}
	function deID($work){
		return (($work-552)/2);
	}
	
	function YenID($work){
		$inc=array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
		$inu=array("1","2","3","4","5","6","7","8","9","0","_");
		foreach($inc as $le){
			array_push($inc,strtoupper($le));
		}
		
		$art=array_merge($inc,$inu);
		$rart=array_reverse($art);
		return str_replace($art,$rart,$work);
	}
	function YdeID($work){
		$inc=array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
		$inu=array("1","2","3","4","5","6","7","8","9","0","_");
		foreach($inc as $le){
			array_push($inc,strtoupper($le));
		}
		
		$art=array_merge($inc,$inu);
		$rart=array_reverse($art);
		return str_replace($rart,$art,$work);
	}
	
	function enY($work){
		$f=1;
		$m="";
		$cmp="qwertyuioplkjhgfdsazxcvbnmrtyuihgfas";
		for($i=0;$i<strlen($work)-1;$i++){
			$let=substr($work,$i,1);
			if(is_numeric($let)){
				$m.=($work[$i]*8)%10;
			}else{
				$m.=$cmp[$f+2];
			}
			$f++;
		}
		return $m;
	}

	function fecha_add($fecha,$dias){
		$fechapum=explode("-",$fecha);
		$dia=$fechapum[2];
		$mes=$fechapum[1];
		$ano=$fechapum[0];
		$lens=array(1=>31,2=>28,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31);
		$lenmes=$lens[$mes];
		if(($ano%4==0) && $mes==2){
			$lenmes+=1;
		}
		if(($dia+$dias)>$lenmes){
			$new_dia=($dia+$dias)-$lenmes;
			$canmes=$mes+1;
			if($canmes>12){
				$new_mes=$canmes-12;
				$new_ano=$ano+1;
			}else{
				$new_mes=$canmes;
				$new_ano=$ano;
			}
		}else{
			$new_dia=$dia+$dias;
			$new_mes=$mes;
			$new_ano=$ano;
		}
		$new_fecha="$new_ano-$new_mes-$new_dia";
		return $new_fecha;
	}
	function fecha_verb($fecha){
		$fechapum=explode("-",$fecha);
		$dia=$fechapum[2];
		$mes=$fechapum[1];
		$ano=$fechapum[0];
		$ardays=array(0=>"Domingo",1=>"Lunes",2=>"Martes",3=>"Mi&eacute;rcoles",4=>"Jueves",5=>"Viernes",6=>"S&aacute;bado");
		$f_months=array("01"=>"Enero","02"=>"Febrero","03"=>"Marzo","04"=>"Abril","05"=>"Mayo","06"=>"Junio","07"=>"Julio","08"=>"Agosto","09"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre","1"=>"Enero","2"=>"Febrero","3"=>"Marzo","4"=>"Abril","5"=>"Mayo","6"=>"Junio","7"=>"Julio","8"=>"Agosto","9"=>"Septiembre",1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre");
		$wd = date('w', strtotime($fecha)); 
		$dateverbo=$ardays[$wd]." ".$dia." de ".$f_months[$mes]." de ".$ano;
		return $dateverbo;
	}
	
	function fecha_verb_lt($fecha){
		$fechapum=explode(" ",$fecha);
		$feca=$fechapum[0];
		if(isset($fechapum[1])){
			$hora=$fechapum[1];
			$arh=explode(":",$hora);
			$h=$arh[0];
			$m=$arh[1];
		}else{
			$h=-1;
		}
		$arf=explode("-",$feca);

		$dia=$arf[2];
		$mes=$arf[1];
		$ano=$arf[0];
	
		$f_months=array("01"=>"Ene","02"=>"Feb","03"=>"Mar","04"=>"Abr","05"=>"May","06"=>"Jun","07"=>"Jul","08"=>"Ago","09"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dic","1"=>"Ene","2"=>"Feb","3"=>"Mar","4"=>"Abr","5"=>"May","6"=>"Jun","7"=>"Jul","8"=>"Ago","9"=>"Sep",1=>"Ene",2=>"Feb",3=>"Mar",4=>"Abr",5=>"May",6=>"Jun",7=>"Jul",8=>"Ago",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dic");
		
		if($feca!=date("Y-m-d")){
			$salida="$dia {$f_months[$mes]}";
		}else{
			if($h>-1){
				if($h>11){
					$lm="pm";
					if($h>12){
						$h-=12;
					}
				}else{
					$lm="am";
				}
				$salida=$h.":".$m." ".$lm;
			}else{
				$salida="$dia {$f_months[$mes]}";
			}
		}
		return $salida;
	}
	
	function fechahora_verb($fecha){
		$arfechahora=explode(" ",$fecha);
		$lafecha=$arfechahora[0];
		$lahora=$arfechahora[1];
		$fechapum=explode("-",$lafecha);
		$dia=$fechapum[2];
		$mes=$fechapum[1];
		$ano=$fechapum[0];
		$horapum=explode(":",$lahora);
		$h=$horapum[0];
		if($h==12){
			$me="m";
		}elseif($h>12){
			$me="pm";
			$h-=12;
		}else{
			$me="am";
		}
		$horafin=$h.":".$horapum[1]." ".$me;
		$ardays=array(0=>"Dom",1=>"Lun",2=>"Mar",3=>"Mie",4=>"Jue",5=>"Vie",6=>"Sab");
		$f_months=array("01"=>"Enero","02"=>"Febrero","03"=>"Marzo","04"=>"Abril","05"=>"Mayo","06"=>"Junio","07"=>"Julio","08"=>"Agosto","09"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre","1"=>"Enero","2"=>"Febrero","3"=>"Marzo","4"=>"Abril","5"=>"Mayo","6"=>"Junio","7"=>"Julio","8"=>"Agosto","9"=>"Septiembre",1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre");
		$wd = date('w', strtotime($fecha)); 
		$dateverbo=$ardays[$wd].", ".$f_months[$mes]." ".$dia." de ".$ano." ".$horafin;
		return $dateverbo;
	}
	
	
	
	
	function hora_verb($hora){
		$horapum=explode(":",$hora);
		$h=$horapum[0];
		if($h==12){
			$me="m";
		}elseif($h>12){
			$me="pm";
			$h-=12;
		}else{
			$me="am";
		}
		$horafin=$h.":".$horapum[1]." ".$me;
		return $horafin;
	}
	

	function fechahora_verb_lt($fecha){
		$arfechahora=explode(" ",$fecha);
		$lafecha=$arfechahora[0];
		$lahora=$arfechahora[1];
		$fechapum=explode("-",$lafecha);
		$dia=$fechapum[2];
		$mes=$fechapum[1];
		$ano=substr($fechapum[0],2,2);
		
		$horapum=explode(":",$lahora);
		$h=$horapum[0];
		if($h==12){
			$me="m";
		}elseif($h>12){
			$me="pm";
			$h-=12;
		}else{
			$me="am";
		}
		$horafin=$h.":".$horapum[1]."".$me;
		$ardays=array(0=>"Dom",1=>"Lun",2=>"Mar",3=>"Mie",4=>"Jue",5=>"Vie",6=>"Sab");
		$f_months=array("01"=>"Ene","02"=>"Feb","03"=>"Mar","04"=>"Abr","05"=>"May","06"=>"Jun","07"=>"Jul","08"=>"Ago","09"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dic","1"=>"Ene","2"=>"Feb","3"=>"Mar","4"=>"Abr","5"=>"May","6"=>"Jun","7"=>"Jul","8"=>"Ago","9"=>"Sep",1=>"Ene",2=>"Feb",3=>"Mar",4=>"Abr",5=>"May",6=>"Jun",7=>"Jul",8=>"Ago",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dic");
		$wd = date('w', strtotime($fecha)); 
		$dateverbo=$f_months[$mes]." ".$dia."/".$ano." ".$horafin;
		return $dateverbo;
	}	
	
	function HexToRGB($hex) {
		$hex = str_replace("#", "", $hex);
		$color = array();
 
		if(strlen($hex) == 3) {
			$color[0] = hexdec(substr($hex, 0, 1) . $r);
			$color[1] = hexdec(substr($hex, 1, 1) . $g);
			$color[2] = hexdec(substr($hex, 2, 1) . $b);
		}
		else if(strlen($hex) == 6) {
			$color[0] = hexdec(substr($hex, 0, 2));
			$color[1] = hexdec(substr($hex, 2, 2));
			$color[2] = hexdec(substr($hex, 4, 2));
		}
		return $color;
	}
	
	function dataSet($SQL,$params=array()){
		$db = new PDO('mysql:host='.SITE_HOST.';dbname='.SITE_DB.";charset=utf8", SITE_USER, SITE_PASS);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$query = $db->prepare($SQL);
		foreach($params as $namep=>$valup){
			$query->bindValue($namep, $valup);
		}
		try {
			$query->execute();
			$rows = $query->fetchAll(PDO::FETCH_ASSOC);
			$filas=array();
			foreach($rows as $row){
				$fila=array();
				foreach($row as $vkey=>$vcol){
					$val=$this->deutf8($vcol);
					$fila[$vkey]=$val;
				}
				$filas[]=$fila;
			}
			return $filas;
		}
		catch (PDOException $e){
			echo $e->getMessage();
			return array();
		}
	}
	
	function dataIn($SQL,$params=array()){
		$db = new PDO('mysql:host='.SITE_HOST.';dbname='.SITE_DB.';charset=utf8', SITE_USER, SITE_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$query = $db->prepare($SQL);
		foreach($params as $namep=>$valup){
			$query->bindValue($namep, $valup);
		}
		try {
			$query->execute();
			return 1;
		}
		catch (PDOException $e){
			//echo $e->getMessage();
			//echo $SQL;
			return 0;
		}
	}
	
	function log($sitio,$id_mesa,$id_pedido,$operacion,$id_usuario){
		$result=$this->dataIn("INSERT LOW_PRIORITY INTO log (ID_SITIO,ID_MESA,ID_PEDIDO,OPERACION,FECHA,ID_USUARIO) VALUES ('$sitio','$id_mesa','$id_pedido','$operacion',NOW(),'$id_usuario')");
	}
	
	function dataInLast($SQL,$params=array()){
		$db = new PDO('mysql:host='.SITE_HOST.';dbname='.SITE_DB.';charset=utf8', SITE_USER, SITE_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$query = $db->prepare($SQL);
		foreach($params as $namep=>$valup){
			$query->bindValue($namep, $valup);
		}
		try {
			$query->execute();
			return $db->lastInsertId(); 
		}
		catch (PDOException $e){
			return 0;
		}
	}

	function crt($val){
		$arcar=array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","_","1","2","3","4","5","6","7","8","9","0","/");
		$arsen=array_reverse($arcar);
		$ares=str_split ($val);
		$rsult="";
		foreach($ares as $lt){
			$aval=array_keys($arcar,$lt);
			if(count($aval)>0){
				$res=$aval[0];
				$rsult.=$arsen[$res];
			}else{
				$rsult.=$lt;
			}
		}
		return $rsult;
	}
	
	function dcr($val){
		$arcar=array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","_","1","2","3","4","5","6","7","8","9","0","/");
		$arsen=array_reverse($arcar);
		$ares=str_split($val);
		$rsult="";
		foreach($ares as $lt){
			
			$aval=array_keys($arsen,$lt);
			if(count($aval)>0){
				$res=$aval[0];
				$rsult.=$arcar[$res];
			}else{
				$rsult.=$lt;
			}
		}
		return $rsult;
	}	
	
	function iniMay($val){
		
		$noval=array('�','�','�','�','�','�','�','�','�','�','�');
		$sival=array('�','�','�','�','�','�','�','�','�','�','�');
		
		$val=str_replace($noval,$sival,$val);
		$arpal=explode(" ",ucwords(strtolower($val)));
		$salida="";
		foreach($arpal as $w){
			$salida.=ucfirst($w)." ";
		}
		$salida=substr($salida,0,strlen($salida)-1);
		return $salida;
	}
		
	function toBytes( $value ) {
		if ( is_numeric( $value ) ) {
			return $value;
		} else {
			$value_length = strlen($value);
			$qty = substr( $value, 0, $value_length - 1 );
			$unit = strtolower( substr( $value, $value_length - 1 ) );
			switch ( $unit ) {
				case 'k':
					$qty *= 1024;
					break;
				case 'm':
					$qty *= 1048576;
					break;
				case 'g':
					$qty *= 1073741824;
					break;
			}
			return $qty;
		}
	}

	  
	function cleanVar ($string) {
		$string = trim($string);
	  $string = htmlentities($string);
	  $noval=array(" ","'","\"","\'","<",">","´");
	  $sival=array("_","_","_","_","_","_","_");
	  $string=str_replace($noval,$sival,$string);
		return $string;
	}


	function xifmax($client,$id){
		$rxx = $this->dataSet("SELECT GPS FROM sitios WHERE ID_SITIO='$id' ORDER BY ID_SITIO");
		
		if(count($rxx)==0) return false;
		$serie=$rxx[0]["GPS"];
		$tokens = str_split('ABCDEFGHIJKLMNOPQRST.UVWXYZ0123456789-');
		$inch = str_split('MNOPQRSATUVWGHIJKLXYZ01231456789ABCDEF');
		$inx=strlen($client);
		$dte=date("n") . substr($client,0,2) . $inx . date("y") . substr($client,2,4). date("m").substr($client,4,2) . date("n") . substr($client,5,1);
		$dte = strtoupper($dte);
		$serial = str_replace($tokens,$inch,$dte);
		$see=str_split($serial,4);
		$seei=implode("-",$see);
		return $seei == $serie;
	}

	function xifmaxY($client,$id){
		$tokens = str_split('ABCDEFGHIJKLMNOPQRST.UVWXYZ0123456789-');
		$inch = str_split('MNOPQRSATUVWGHIJKLXYZ01231456789ABCDEF');
		$inx=strlen($client);
		$dte=date("n") . substr($client,0,2) . $inx . date("y") . substr($client,2,4). date("m").substr($client,4,2) . date("n") . substr($client,5,1);
		$dte = strtoupper($dte);
		$serial = str_replace($tokens,$inch,$dte);
		$see=str_split($serial,4);
		$seei=implode("-",$see);
		//echo $dte;
		return $seei;
	}
	
	function utf8($string) {
		if (function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
		} else {
			return @$gf->utf8($string);
		}
	}
	function deutf8($string) {
		if (function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
		} else {
			return @utf8_decode($string);
		}
	}
}
?>