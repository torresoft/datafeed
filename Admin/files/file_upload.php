<?php
session_start();
if(isset($_SESSION["restuname"]) && isset($_SESSION["restprofile"])){
	
	$upDir=$_POST["carpeta"];	
	if(isset($_GET["control"])){
		$control= $gf->cleanVar($_GET["control"]);
	}else{
		$control="archivo";
	}
  	$tamano = toBytes($_FILES[$control]['size']);
	$tipo = $_FILES[$control]['type'];
	$archivo = $_FILES[$control]['name'];
	$temporal = $_FILES[$control]['tmp_name'];
	
	$arexten=explode(".",$archivo);
	$exten=end($arexten);
	if($exten=="jpg" || $exten=="png"){
		$isimg=true;
	}else{
		$isimg=false;
	}
	
	$upload_max = toBytes(ini_get('upload_max_filesize'));
	if($upload_max>$tamano){
		$tipoar = explode(",",$tipo);
		
		$extnot=array("php","js","asp","aspx","hphp","java","cpp","jsp","css","bin","class","c","vbs");
		$exta=explode(".",$archivo);
		$ext=end($exta);
		$ext=strtolower($ext);
		$blacklist=array("jsp","jspx","php","asp","aspx","exe","com","bat"."sh","cgi","htaccess","php","js","asp","aspx","hphp","java","cpp","jsp","css","bin","class","c","vbs","conf","ini","json","inf","cnf","key","pid","ps","py","csh","ksh","pyc","pyo","sql");
		$whitemime=array("text/php","text/php5","text/php4","text/php7","text/x-php","application/php","application/php4","application/php5","application/php7","application/x-php","application/x-httpd-php","application/x-httpd-php-source","application/x-httpd-cgi","application/javascript","text/javascript","text/x-java-source","text/x-fortran","text/x-c","text/vnd.curl","application/x-sql","application/x-sh","application/vnd.curl.pcurl","application/java-vm","application/java-archive","application/zlib","application/sql");
		$namexpl=explode(".",strtolower($archivo));
		$rsul=array_intersect($namexpl,$blacklist);
		$rsulwhite=array_intersect($tipoar,$whitemime);
		if(count($rsul)==0 && count($namexpl)>1){
			if(count($rsulwhite)==0){
				$noval=array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Ð','?','Œ','œ','Š','š','Ÿ','Ž','ž',' ','-','°',',');
				$sival=array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','D','IJ','OE','oe','S','s','Y','Z','z','_','_','_',',');
				$carpeta=$upDir;
				$rsa=array();
				$archivo=str_replace($noval,$sival,$archivo);
				$archivo = str_replace(' ', '-', $archivo);
				$archivo = preg_replace('/[^a-zA-Z0-9_.]/', '', $archivo);
				$destino =  "../../".$carpeta.$archivo;
				if($isimg==true){
					$rsa=getimagesize($temporal);
				}else{
					$rsa[0]=0;
				}
				$w=$rsa[0];
				if($w<=500){
					if(!file_exists($destino)){
						if(move_uploaded_file($temporal,$destino)){
							echo $carpeta.$archivo;
						}else{
							echo "-3";
						}
					}else{
						echo "-2";
					}
				}else{
					//$images = $_FILES["userfile"]["tmp_name"];
					//$new_images = "thumbnails_".$_FILES["userfile"]["name"];
					//copy($_FILES,"Photos/".$_FILES["userfile"]["name"]);
					
					$size=getimagesize($temporal);
					
					$w=$size[0];
					$h=$size[1];
					
					if($w>$h){
						$width=500;
						$height=round($width*$size[1]/$size[0]);
					}else{
						$height=500;
						$width=round($height*$size[0]/$size[1]);
					}
					if($exten=="jpg"){
						$images_orig = ImageCreateFromJPEG($temporal);
					}else{
						$images_orig = ImageCreateFromPNG($temporal);
					}
					$photoX = ImagesX($images_orig);
					$photoY = ImagesY($images_orig);
					$images_fin = ImageCreateTrueColor($width, $height);
					ImageCopyResampled($images_fin, $images_orig, 0, 0, 0, 0, $width+1, $height+1, $photoX, $photoY);
					if($exten=="jpg"){
						ImageJPEG($images_fin,"../../".$upDir.$archivo);
					}else{
						ImagePNG($images_fin,"../../".$upDir.$archivo);
					}
					ImageDestroy($images_orig);
					ImageDestroy($images_fin);
					echo $carpeta.$archivo;
				}
			}else{
				echo "-2";
			}
		}else{
			echo "-1";
		}
	}else{
		echo "0";
	}
}else{
	echo "Sin session";	
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
?>