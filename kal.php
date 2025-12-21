<?php
session_start();
if(!isset($_SESSION["restuiduser"])){
    echo $gf->utf8("<META HTTP-EQUIV='REFRESH' CONTENT='0;URL=indexadm.php'>");
}
?>