<?php

include_once("classes/Main.php");
include_once("_gestion/classes/Formularios.php");
header('Content-Type: text/html; charset=utf-8');
//error_reporting(E_ALL);
ini_set('display_errors', '1');

class Pages{
    function doGuardarDatos($arr){
       //viene el dni y el reg-id
       //Si ya tengo el dni actualizo el regid
       $rs = sql("select * from usuarios where dni ='$arr[dni]'");
       //ya tengo el DNI cambio el regid del dispositivo
       if (mysql_num_rows($rs)){
          sql("update usuarios set reg_id = '$arr[reg_id]' where dni='$arr[dni]'");
          $mensaje ="Modificado";
       }else{
           //usuario nuevo
           $mensaje ="Nuevo usuario";
           sql("insert into usuarios (dni,reg_id) values('$arr[dni]','$arr[reg_id]')");
       }
       
       return "jsonCallback(".json_encode(array("OK","$mensaje")).");";
       
       
    }
    
  
        
        
        
    
    
  



}
?>
