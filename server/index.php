<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();
include_once("classes/Pages.php");
include_once("includes/class.phpmailer.php");




switch ($_GET[act]){
    case "":                    echo Pages::index();break;
    
    case "showInscripcion":     echo Pages::showInscripcion($_REQUEST);break;
    case "doInscripcion":       echo Pages::doInscripcion($_REQUEST);break;
}


		
?>
