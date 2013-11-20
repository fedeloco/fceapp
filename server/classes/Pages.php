<?php

include_once("classes/Main.php");
include_once("_gestion/classes/Formularios.php");
header('Content-Type: text/html; charset=utf-8');
//error_reporting(E_ALL);
ini_set('display_errors', '1');

class Pages{
    function index(){
        set_file("all","index.html");
        //Evento?
        $idEvento = $_REQUEST[idEvento];
        $_SESSION["nn"]["idEvento"] =$idEvento;
        $filaEvento = mfa(sql("select * from eventos where id='$idEvento'"));
        s("texto",$filaEvento[introduccion]);
        
        //genero el formulario
        
        $idFormulario = $filaEvento[id_formulario];
        $rs = sql("select * from campos_base");
        
        while($fila = mfa($rs)){
            s("nombre_campo",$fila[nombre]);
            $nombreBase = strtolower($fila[nombre]);
            s("campo","<input type='text' id='$nombreBase' name='$nombreBase' class='cajaTexto' />");
            pp("campos");
        }
        
        //campos nuevos
        $rs = sql("select * from campos where id_formulario = '$idFormulario'");
        while($fila = mfa($rs)){
            s("nombre_campo",$fila[nombre]);
            
            $nombreBase = strtolower($fila[nombre]);
            //ahora dependiendo del campo...
            if ($fila[tipo] == 1){
                //es un campo de texto
                s("campo","<input type='text' id='campoExtra[$fila[id]]' name='campoExtra[$fila[id]]' class='cajaTexto' />");
            }
            if ($fila[tipo] == 2){
                $array = explode(",",$fila[valores]);
                s("campo",Formularios::getSelectFor($fila[id],$array));
            }
            pp("campos");
        }
        
        return Main::wrapper(parse("all"),$filaEvento[titulo],$filaEvento[subtitulo],$filaEvento);
    }
    
    
    function doInscripcion($arr){
       
        $idEvento = $_SESSION["nn"]["idEvento"];
        //primero guardo los datos comunes...
        $nombre = mysql_real_escape_string($arr[nombre]);
        $apellido = mysql_real_escape_string($arr[apellido]);
        $dni = mysql_real_escape_string($arr[dni]);
        $email = mysql_real_escape_string($arr[email]);
        $fecha_registro = time();
        sql("insert into inscripciones(id_evento,fecha_registro,nombre,apellido,dni,email) 
        VALUES('$idEvento','$fecha_registro','$nombre','$apellido','$dni','$email')");
        //tomo el id de lo que se agrego...
        $idRegistro = mysql_insert_id();
        //ahora con ese id ciclo por todos los campos especiales
        foreach($arr[campoExtra] as $key => $value){
            sql("insert into inscripciones_campos(id_inscripcion,id_campo,valor)
            VALUES('$idRegistro','$key','$value')  ")    ;
        }
        
        set_file("all","doInscripcion.html");
        s("id",$idRegistro);
        $filaEvento = mfa(sql("select * from eventos where id='$idEvento'"));
        return Main::wrapper(parse("all"),$filaEvento[titulo],$filaEvento[subtitulo],$filaEvento);
            
    }//function doInscripcion 
        
        
        
    
    
  



}
?>
