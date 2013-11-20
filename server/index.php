<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();
include_once("classes/Pages.php");


switch ($_GET[act]){
   
    case "doGuardarDatos":       echo Pages::doGuardarDatos($_REQUEST);break;
}


		
?>
