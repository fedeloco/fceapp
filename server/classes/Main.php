<?php
error_reporting(1);
include_once("classes/Utiles.php");
include_once("includes/frame.php");
include_once("includes/connect.php");

session_start(); 

class Main {

   public static $salir = "Salir";
    
   function wrapper($content,$title,$subtitulo,$arr)  {
        set_file("all", "wrapper.html");        
     
        s("title",$title);
        s("titulo_evento",$title);
        s("subtitulo",$subtitulo);
        s("contenido", $content);
        s("anio",date("Y"));
        
        s("imagen","<img src='uploads/banners/$arr[id].jpg' />");
        
        return parse("all");
    }
    
    function wrapper2($content,$title)  {
        set_file("all", "wrapper2.html");
        set_var("contenido", $content);
        return parse("all");
    }
    
    function cargarTabla($tabla){
        $rs = mysql_query("select * from $tabla");
        while($fila = mysql_fetch_assoc($rs)){
            $arr[$fila[id]]=$fila;
        }
        return $arr;
    }
    
    function getField($tabla,$campo,$id_primaria){
        $fila = mysql_fetch_assoc(sql("select $campo FROM $tabla where id='$id_primaria'"));
        return $fila[$campo];
    }
    
    function getRow($tabla,$nombrePK ,$valorPK){
        return mfa(sql("select * from $tabla where $nombrePK = '$valorPK'"));
    }
  
    
 function enviarHTMLMail($to,$titulo,$mensaje){
            $mail = new PHPMailer();

            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->Host       = "smtp.unl.edu.ar"; // SMTP server
            $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
                                                       // 1 = errors and messages
                                                       // 2 = messages only
            $mail->SMTPAuth   = false;                  // enable SMTP authentication
            $mail->Host       = "smtp.unl.edu.ar"; // sets the SMTP server
            $mail->Port       = 25;                    // set the SMTP port for the GMAIL server
            $mail->Username   = ""; // SMTP account username
            $mail->Password   = "";        // SMTP account password

            $mail->SetFrom('juc2013@fce.unl.edu.ar', 'JUC2013');

            $mail->AddReplyTo("juc2013@fce.unl.edu.ar","JUC2013");

            $mail->Subject    = $titulo;

            $mensaje = stripcslashes($mensaje);
            $mensaje = str_replace("rn", "", $mensaje);
            $mail->MsgHTML($mensaje);

            $address = $to;
            $mail->AddAddress($address, $address);

            $mail->Send();
    }
    
     function selectHelperWhere($nombre_select,$tabla,$campo_nombre,$campo_id,$seleccionado = "",$mostrarVacio="1",$where=""){
        $rs =mysql_query("select $campo_nombre,$campo_id FROM $tabla $where order by $campo_nombre");
        
        $returnTXT = "<select name='$nombre_select' id='$nombre_select'class=\"texto\">";
        echo mysql_error();
        if ($mostrarVacio)
            $returnTXT .='<option value=""></option>';
        while($fila = mysql_fetch_assoc($rs)){
            $selected= "";
            if ($seleccionado == $fila[$campo_id]){
                $selected = 'selected="selected"';
            }
            
            $returnTXT = $returnTXT .'<option value="'.$fila[$campo_id].'" '.$selected.' >'.$fila[$campo_nombre].'</option>';
        }
        $returnTXT .="</select>";
        return $returnTXT;
    }
    
    function divW($texto,$clase){
        return '<div class="'.$clase.'">'.$texto."</div>";
    }
    
    function selectHelper($nombre_select,$tabla,$campo_nombre,$campo_id,$seleccionado = "",$vacio=1){
        $rs =mysql_query("select $campo_nombre,$campo_id FROM $tabla order by $campo_nombre");
        
        $returnTXT = "<select name='$nombre_select' id='$nombre_select'>";
        echo mysql_error();
        $returnTXT .=$vacio ? '<option value=""></option>' : "";
        while($fila = mysql_fetch_assoc($rs)){
            $selected= "";
            if ($seleccionado == $fila[$campo_id]){
                $selected = 'selected="selected"';
            }
            
            $returnTXT = $returnTXT .'<option value="'.$fila[$campo_id].'" '.$selected.' >'.$fila[$campo_nombre].'</option>';
        }
        $returnTXT .="</select>";
        return $returnTXT;
    }
 
}//end class
?>
