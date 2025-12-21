<?php
require_once("config.php");
require_once("lib_php/relations.php");
require_once("lib_php/dsTables.class.php");
require_once("lib_php/component.class.php");
spl_autoload_register('autoloader');
function autoloader( $NombreClase ) {
    $root = __DIR__;
    if(file_exists($root.'/lib_php/' . $NombreClase . '.class.php')){
        include_once $root.'/lib_php/' . $NombreClase . '.class.php';
    }
}
?>