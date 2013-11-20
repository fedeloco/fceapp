<?php
include_once("classes/Main.php");
include_once("includes/connect.php");
include_once("includes/frame.php");
include_once("classes/Pages.php");

class Usuarios extends Main{
    
    function showLogin(){
        set_file("content","showLogin.html"); 
        return Main::wrapper(pp("content"),"Registrarse!",0,0); 
    }
    
    function doLogin($arr){
        $sql= "select * from usuarios where mail='$arr[mail]' and password='$arr[password]'";
        $rs = mysql_query($sql);
        $vienede = $_SERVER[HTTP_REFERER];
        if ($fila = mysql_fetch_assoc($rs)){
            $_SESSION[usuario] = $fila;
            $_SESSION[idUsuario] = $fila[id];   
            header("location:$vienede");
        }else{
             //mostrar mensaje de error 
             set_file("content", "mensaje.html");
             s("titulo","Error!");
             s("mensaje","<b>Nombre de usuario o contraseña incorrecto.</b>");
             return Main::wrapper(pp("content"), "Error!",0,0);
         }
      }
    
    function showPublicarAviso($mensaje=''){
        //sql
        $sql= "select * from usuarios where id = $_SESSION[idUsuario]";
        $rs = mysql_query($sql);
        if ($fila = mysql_fetch_assoc($rs)){
            set_file("content","showPublicarAviso.html"); 
            if ($mensaje){
                s("mensaje",Main::divW("<b>$mensaje</b>","info2"));
            }else{
                s("mensaje","");
            }
            //sus datos personales:
            s("nombre",$fila[nombre]);
            s("apellido",$fila[apellido]);
            s("mail",$fila[mail]);
            s("cod_tel",$fila[cod_tel]);
            s("telefono",$fila[telefono]);
            s("cod_cel",$fila[cod_cel]);
            s("celular",$fila[celular]);
            s("domicilio",$fila[domicilio]);
            s("localidad",$fila[localidad]);
            s("cod_pos",$fila[cod_pos]);
            $provincia = Main::getField("provincias","nombre",$fila[id_provincia]);
            s("nombre_provincia",$provincia);
            //rubros:
            s("select_rubro",Main::selectHelper("id_tipo_rubro","rubros","nombre","id",$arr[id]));
            //marcas:
            s("select_marca",Main::selectHelper("id_tipo_marca","marcas","nombre","id",$arr[id]));
            //tipo combustibles:
            s("select_combustible",Main::selectHelper("id_tipo_combustible","combustibles","nombre","id",$arr[id]));
            //tipo autos:
            s("select_tipo",Main::selectHelper("id_tipo_auto","tipo_autos","nombre","id",$arr[id]));
			//tipo inmuebles:
            s("select_inmueble",Main::selectHelper("id_tipo_inmueble","tipo_inmuebles","nombre","id",$arr[id]));
            //tipo plan:
            s("select_publicar",Main::selectHelper("id_tipo_plan","planes","nombre","id",1,0));
            //dÃ­as en portada:
            $dias_portada = Main::getConfig("dias_en_portada");
            
            s("dias",$dias_portada);
			//aÃ±o:
            $fin = date("Y");
            s("select_anio",Main::selectHelperAnios("ano",1910,$fin,""));
            
            $rsP = sql("select * FROM  planes order by id");
            while($filaP = mfa($rsP)){
                if (!$precioSolo){
                    $precioSolo = $filaP[precio];
                }
                $precio1 .= "$filaP[id]:$filaP[precio],";    
                $precio2 .= "$filaP[id]:$filaP[portada_comun],";
                $precio3 .= "$filaP[id]:$filaP[portada_rotativa],";
            }
            $precio1 = substr($precio1,0,-1);
            $precio1 = "{".$precio1."}";
            $precio2 = substr($precio2,0,-1);
            $precio2 = "{".$precio2."}";
            $precio3 = substr($precio3,0,-1);
            $precio3 = "{".$precio3."}";
            
            s("precio",$precio1);
            s("portada_comun",$precio2);
            s("portada_rotativa",$precio3);
            s("precioSolo",$precioSolo);
            
            return Main::wrapper(pp("content"),"Publicar Aviso!",0,1); 
        } 
        else{
             //mostrar mensaje de error 
             set_file("content", "mensaje.html");
             s("titulo","Error!");
             s("mensaje","<b>Sesión finalizada.</b>");
             return Main::wrapper(pp("content"), "Error!",0,1);
         }
    }
	
	function doPublicarAviso($arr){
    //si esta todo bien:
    if ($arr["id_tipo_rubro"] && $arr["titulo"] && $arr["descripcion"] && $arr["domicilio_cobro"]){
		 	//hago el alta con:
            
            $filaPrecio = mfa(sql("select * from planes where id = '$arr[id_tipo_plan]'"));
            
            $id_tipo_anuncio = 1;
            $precio = $filaPrecio[precio];
            
            if ($arr[tipo_aviso2]){
                $id_tipo_anuncio = 3;
                $precio = $filaPrecio[portada_rotativa];
            }
            
            if ($arr[tipo_aviso1]){
                $id_tipo_anuncio = 2;
                $precio = $filaPrecio[portada_comun];
            }
            
            sql("insert into avisos values ('','$_SESSION[idUsuario]','$arr[id_tipo_rubro]','$arr[id_tipo_marca]','$arr[id_tipo_combustible]','$arr[id_tipo_auto]','$arr[id_tipo_inmueble]','$arr[id_tipo_plan]','$id_tipo_anuncio','','$arr[titulo]','$arr[descripcion]','$arr[comentario]','$arr[ano]','','','0','$arr[domicilio_cobro]','$arr[horario_cobro]','$precio','$arr[forma_pago]','','','','$arr[publicar_email]','$arr[publicar_telefono]','$arr[publicar_domicilio]','$arr[precio]')");
			 
             $idAviso = mysql_insert_id();
             //mostrar foto:
             $filaRubro = mfa(sql("select * from rubros where id = '$arr[id_tipo_rubro]'"));
             
             $inicioRef = strtolower(substr(str_replace(array("á"," "),array("a",""), $filaRubro[nombre]),0,3));
             $ref = $inicioRef ."_$idAviso";
             sql("update avisos set n_referencia = '$ref' where id = '$idAviso'");
			 
			 //usuario solicitante:
			 $sql= "select * from usuarios where id = $_SESSION[idUsuario]";
             $rs = mysql_query($sql);
			 $fila = mysql_fetch_assoc($rs);
			 
             //enviar correo:
			 $cuerpo .= "<h3>Nuevo Anuncio:</h3> <br>";
			 $cuerpo .= "Nombre: $fila[nombre] <br>";
			 $cuerpo .= "Email: $fila[mail] <br>";
			 $cuerpo .= "Telefono: $fila[cod_tel] - $fila[telefono] <br>";
			 $cuerpo .= "Celular: $fila[cod_cel] - $fila[celular] <br>";
			 $from = "$fila[mail]";
			 
			 $cuerpo .= "Domicilio de Cobro: $arr[domicilio_cobro] <br>";
			 $cuerpo .= "Día y Horario de Cobro: $arr[horario_cobro] <br>";
			 
			 $sql= "select * from avisos where id = '$idAviso'";
             $rs = mysql_query($sql);
			 $fila = mysql_fetch_assoc($rs);
			 
			 $cuerpo .= "Valor del Anuncio: $fila[precio] <br>";
			 
			 $tipo_plan=$fila[id_tipo_plan];
			 
			 $sql= "select * from planes where id = '$tipo_plan'";
             $rs = mysql_query($sql);
			 $fila = mysql_fetch_assoc($rs);
			 
			 $cuerpo .= "Tipo de Plan a: $fila[nombre] <br>";
			 
			 $link="Para ver los detalles del anuncio click aquí : <a href = 'http://www.comprayvendetodo.com.ar/mostrar_productos.php?id=$idAviso'>http://www.comprayvendetodo.com.ar/mostrar_productos.php?id=$idAviso</a>";
			 
			 $cuerpo .= $link .'<br>' ;
			
			 $to = "enrique_0000@hotmail.es";
			 
			 $titulo = "Nuevo Anuncio:";
			 Main::enviarHTMLMail($from,$to,$titulo,$cuerpo);
        
        	 //cargar foto:
             header("location:usuarios.php?act=showCargarFoto&idAviso=$idAviso");
			 
            /* return Main::wrapper(pp("content"), "Anuncio Registrado!",0,1);*/
         }else{
             set_file("content", "mensaje.html");
			 s("titulo","Error!");
             s("mensaje","<b>No se ha podido publicar su aviso. Intentelo de nuevo por favor y asegúrese de ingresar bien todos los datos.</b>");
             return Main::wrapper(pp("content"), "Error!",0,1);
         }
  }
  
  function showCargarFoto($idAviso){
        set_file("content", "showCargarFoto.html");
        s("idAviso",$idAviso);
        $rand = rand(1,1000);
        if (is_file('uploads/avisos/1/'.$idAviso.'.jpg')){
           $temp ='uploads/avisos/1/'.$idAviso.'.jpg?rand='.$rand.'"';
           s("url1",$temp);
        }else{
            s("url1","cargando.html");
        }
        if (is_file('uploads/avisos/2/'.$idAviso.'.jpg')){
           $temp ='uploads/avisos/2/'.$idAviso.'.jpg?rand='.$rand.'"';
           s("url2",$temp);
        }else{
            s("url2","cargando.html");
        }
        if (is_file('uploads/avisos/3/'.$idAviso.'.jpg')){
           $temp ='uploads/avisos/3/'.$idAviso.'.jpg?rand='.$rand.'"';
           s("url3",$temp);
        }else{
            s("url3","cargando.html");
        }
       
       return Main::wrapper(pp("content"), "Cargar foto",0,1);
  }
  
  function doCargarFoto($arr){
      $idAviso = $arr[idAviso];
      $nrFoto = $arr[nfoto];
      
       if($_FILES[foto_1]){
                 include_once("classes/Utiles.php");
             Utiles::miniatura(140,140,$_FILES[foto_1][tmp_name],"uploads/avisos/1/",$idAviso.".jpg");
             Utiles::miniaturaR(600,800,$_FILES[foto_1][tmp_name],"uploads/avisos/g/1/",$idAviso.".jpg");
            }
        if($_FILES[foto_2]){
             include_once("classes/Utiles.php");
             Utiles::miniatura(140,140,$_FILES[foto_2][tmp_name],"uploads/avisos/2/",$idAviso.".jpg");
             Utiles::miniaturaR(600,800,$_FILES[foto_2][tmp_name],"uploads/avisos/g/2/",$idAviso.".jpg");
        }
        if($_FILES[foto_3]){
             include_once("classes/Utiles.php");
             Utiles::miniatura(140,140,$_FILES[foto_3][tmp_name],"uploads/avisos/3/",$idAviso.".jpg");
             Utiles::miniaturaR(600,800,$_FILES[foto_3][tmp_name],"uploads/avisos/g/3/",$idAviso.".jpg");
        }
        $rand = rand(1,1000);
        echo "<img src='uploads/avisos/$nrFoto/$idAviso.jpg?rand=$rand' />";
      
  }
   
   function showVerAvisos($mensaje=''){
        set_file("content","showVerAvisos.html"); 
        if ($mensaje){
            s("mensaje",Main::divW("<b>$mensaje</b>","info2"));
        }else{
            s("mensaje","");
        }
		$sql="select * from avisos where id_usuario = '$_SESSION[idUsuario]'";
		$rs = mysql_query($sql);
		
		while ($fila = mysql_fetch_assoc($rs)){
			s("referencia",$fila[n_referencia]);
			s("titulo",$fila[titulo]);
			s("fecha_inicio",date("d/m/Y",$fila[fecha_inicio]));
			s("fecha_final",date("d/m/Y",$fila[fecha_final]));
			s("id",$fila[id]);
			pp("misavisos");
		}	
		
        return Main::wrapper(pp("content"),"Editar Avisos!",0,1); 
  }
  
   function showRegistrarse($mensaje=''){
        set_file("content","showRegistrarse.html"); 
        if ($mensaje){
            s("mensaje",Main::divW("<b>$mensaje</b>","info2"));
        }else{
            s("mensaje","");
        }
        s("select_provincia",Main::selectHelper("id_provincia","provincias","nombre","id","1"));
        return Main::wrapper(pp("content"),"Registro de Usuarios!",0,0); 
  }
  
   function doRegistrarse($arr){
             //verificar mail:
              if (mysql_num_rows(sql("select * from usuarios where mail='$arr[mail]'"))){
                   return self::showRegistrarse("Este e-mail ya esta siendo usado por otro usuario.");
                }
            //si esta todo bien:
         if ($arr["apellido"] && $arr["nombre"] && $arr["mail"] && $arr["password"] && $arr["domicilio"]){
             //hago el alta con:
             sql("insert into usuarios values ('','$arr[apellido]','$arr[nombre]','$arr[mail]','$arr[password]','$arr[codtel]','$arr[telefono]','$arr[codcel]','$arr[celular]','$arr[domicilio]','$arr[localidad]','$arr[codpos]','$arr[id_provincia]')");
             //mostrar el mensaje de registrado:
             set_file("content", "mensaje.html");
             s("titulo","Excelente Usuario Registrado!. Muchas Gracias por elegirnos.");
             s("mensaje","<b>Presione en Publicar Aviso para continuar.</b>");
             return Main::wrapper(pp("content"), "Registrado!",0,0);
         }else{
             //mostrar mensaje de error 
             set_file("content", "mensaje.html");
             s("titulo","Error!");
             s("mensaje","<b>No se ha podido registrar. Intentelo de nuevo por favor y asegúrese de ingresar bien todos los datos.</b>");
             return Main::wrapper(pp("content"), "Error!",0,0);
         }
    }
    
   function showOlvideContrasena($mensaje=''){
        set_file("content","showOlvideContrasena.html"); 
         if ($mensaje){
            s("mensaje",Main::divW("<b>$mensaje</b>","info2"));
        }else{
            s("mensaje","");
        }
        return Main::wrapper(pp("content"),"Olvido su Contraseña!",0,0); 
      }
    
   function doOlvideContrasena($arr){
        if ($arr["mail"]){
            $sql= "select * from usuarios where mail='$arr[mail]'";
            $rs = mysql_query($sql);
            $can =  mysql_num_rows($rs);
            if ($can==1) {
                    $fila = mysql_fetch_assoc($rs);
                    $contrasena=$fila[password];
                    //envio de mail
                    $cuerpo .= "<h3>Solicitud de Contraseña :</h3><br>";
                    $cuerpo .= "<strong>Su Contraseña es :". ' ' .$contrasena . '</strong>'.'<br>'.'<br>';
                    $cuerpo .= "<b>Gracias por elegirnos : <a href = 'http://comprayvendetodo.com.ar'>    http://comprayvendetodo.com.ar</a></b>";
                    $from = "www.comprayvendetodo.com.ar";
                    $to = "$arr[mail]";
                    $titulo = "Su Password :";
                    Main::enviarHTMLMail($from,$to,$titulo,$cuerpo);
                    //fin de envio
                    set_file("content", "mensaje.html");
                    s("titulo","Excelente!");
                    s("mensaje","<b>La contraseña solicitada a sido enviada a su e-mail.</b>");
                    return Main::wrapper(pp("content"), "Excelente!",0,0);
             }else{
                    //mostrar mensaje de error 
                    set_file("content", "mensaje.html");
                    s("titulo","Error!");
                    s("mensaje","<b>Su e-mail no existe en nuestra base de datos.</b>");
                    return Main::wrapper(pp("content"), "Error!",0,0);
            }
        }else{
                //mostrar mensaje de error 
                set_file("content", "mensaje.html");
                s("titulo","Error!");
                s("mensaje","<b>Debe ingresar su e-mail para completar la operación.</b>");
                return Main::wrapper(pp("content"), "Error!",0,0);
        }
    }
    
   function showContacto($mensaje=''){
        set_file("content","showContacto.html"); 
        if ($mensaje){
            s("mensaje",Main::divW("<b>$mensaje</b>","info2"));
        }else{
            s("mensaje","");
        }
		if($_SESSION[idUsuario]==0){
        	return Main::wrapper(pp("content"),"Contacto!",0,0); 
		}else{
			return Main::wrapper(pp("content"),"Contacto!",0,1); 
		}
    }
    
   function doContacto($arr){
	
	if ($arr["nombre"] && $arr["email"]){
            
		$cuerpo .= "<h3>Formulario de Contacto:</h3> <br>";
        $cuerpo .= "Nombre: $arr[nombre] <br>";
        $cuerpo .= "Email: $arr[email] <br>";
        $cuerpo .= "Telefono: $arr[telefono] <br>";
        $cuerpo .= "Consulta:<br> $arr[consulta] <br>";
        
        $from = "$arr[email]";
        $to = "juliogerez4@hotmail.com";
        
        $titulo = "Formulario Web de Consulta:";
        Main::enviarHTMLMail($from,$to,$titulo,$cuerpo);
        
        set_file("content","mensaje.html"); 
        s("titulo","Muchas Gracias!");
        s("mensaje","<b>Nos contactaremos con usted a la brevedad.</b>");
		
        if($_SESSION[idUsuario]==0){
        	return Main::wrapper(pp("content"),"Muchas Gracias!",0,0); 
		}else{
			return Main::wrapper(pp("content"),"Muchas Gracias!",0,1); 
		}
		
		}else{
		 //mostrar mensaje de error 
             set_file("content", "mensaje.html");
             s("titulo","Error!");
             s("mensaje","<b>No se ha podido realizar la operación. Intentelo de nuevo por favor y asegúrese de ingresar bien todos los datos.</b>");
			 
             if($_SESSION[idUsuario]==0){
        		return Main::wrapper(pp("content"),"Error!",0,0); 
			 }else{
				return Main::wrapper(pp("content"),"Error!",0,1); 
			 }
         }
    }
    
   function showRecomendar($mensaje=''){
        set_file("content","showRecomendar.html"); 
        if ($mensaje){
            s("mensaje",Main::divW("<b>$mensaje</b>","info2"));
        }else{
            s("mensaje","");
        }
        return Main::wrapper(pp("content"),"Recomendar!",0,1); 
    }
	
   function doRecomendar($arr){
	
		 if (!$arr["tu_nombre"] || !$arr["email_1"] || !$arr["su_nombre"] || !$arr["email_2"] || !$arr["texto"]){
             //mostrar mensaje de error 
             set_file("content", "mensaje.html");
             s("titulo","Error!");
             s("mensaje","<b>No se ha podido realizar la operación. Intentelo de nuevo por favor y asegúrese de ingresar bien todos los datos.</b>");
             return Main::wrapper(pp("content"), "Error!",0,1);
         }
		 
        $cuerpo .= "<h3>Formulario de Recomendación:</h3> <br>";
        $cuerpo .= "Nombre: $arr[tu_nombre] <br>";
        $cuerpo .= "Email: $arr[email_1] <br>";
		$cuerpo .= "Amigo: $arr[su_nombre] <br>";
        $cuerpo .= "AtenciÃ³n:<br> $arr[texto] <br>";
        
        $from = "$arr[email_1]";
        $to = "$arr[email_2]";
        
        $titulo = "Te Recomiendo esta página:";
        Main::enviarHTMLMail($from,$to,$titulo,$cuerpo);
        
        set_file("content","mensaje.html"); 
        s("titulo","Excelente!. La operación se realizó con éxito.");
        s("mensaje","<b>Puede continuar realizando otras operaciones.</b>");
        
        return Main::wrapper(pp("content"),"Muchas Gracias!",0,1); 
    }
    
    function showCambiarContrasena($mensaje=''){
        set_file("content","showCambiarContrasena.html"); 
        if ($mensaje){
            s("mensaje",Main::divW("<b>$mensaje</b>","info2"));
        }else{
            s("mensaje","");
        }
        return Main::wrapper(pp("content"),"Cambiar Contraseña!",0,1); 
    }
    
    function doCambiarContrasena($arr){
        
         if (!$arr["password_1"] || !$arr["password_2"] || !$arr["password_3"]){
             //mostrar mensaje de error 
             set_file("content", "mensaje.html");
             s("titulo","Error!");
             s("mensaje","<b>No se ha podido realizar la operación. Intentelo de nuevo por favor y asegúrese de ingresar bien todos los datos.</b>");
             return Main::wrapper(pp("content"), "Error!",0,1);
         }
         
         $sql= "select * from usuarios where id='$_SESSION[idUsuario]'";
         $rs = mysql_query($sql);
         $can =  mysql_num_rows($rs);
         
         if ($can==1){
             sql("update usuarios set password = '$arr[password_2]' where id = '$_SESSION[idUsuario]'");
             
             $fila = Main::getRow("usuarios","id",$_SESSION[idUsuario]);
             $mail= $fila[mail];
             
             $cuerpo .= "<h3>Formulario de Cambio de Contraseña:</h3> <br>";
             $cuerpo .= "Tu contraseña nueva es: $arr[password_2] <br>";
            
             $from = "formulario@comprayvendetodo.com.ar";
             $to = $mail;
            
             $titulo = "Su nueva Contraseña:";
             Main::enviarHTMLMail($from,$to,$titulo,$cuerpo);
            
             set_file("content","mensaje.html"); 
             s("titulo","Excelente!. La operación se realizó con éxito.");
             s("mensaje","<b>Puede continuar realizando otras operaciones.</b>");
            
             return Main::wrapper(pp("content"),"Muchas Gracias!",0,1); 
         }else{
             //mostrar mensaje de error 
             set_file("content", "mensaje.html");
             s("titulo","Error!");
             s("mensaje","<b>Error! Su contraseña es incorrecta. Intentelo de nuevo por favor y asegúrese de ingresar bien todos los datos.</b>");
             return Main::wrapper(pp("content"), "Error!",0,1);
         }
    }
	
    function showEditarUsuario($mensaje=''){
         //sql
        $sql= "select * from usuarios where id = $_SESSION[idUsuario]";
        $rs = mysql_query($sql);
        if ($fila = mysql_fetch_assoc($rs)){
            set_file("content","showEditarUsuario.html"); 
            if ($mensaje){
               s("mensaje",Main::divW("<b>$mensaje</b>","info2"));
            }else{
                s("mensaje","");
            }
            //sus datos personales:
            s("nombre",$fila[nombre]);
            s("apellido",$fila[apellido]);
            s("cod_tel",$fila[cod_tel]);
            s("telefono",$fila[telefono]);
            s("cod_cel",$fila[cod_cel]);
            s("celular",$fila[celular]);
            s("domicilio",$fila[domicilio]);
            s("localidad",$fila[localidad]);
            s("cod_pos",$fila[cod_pos]);
            s("select_provincia",Main::selectHelper("id_provincia","provincias","nombre","id",$fila[id_provincia]));
            //
            return Main::wrapper(pp("content"),"Editar Usuario!",0,1); 
        } 
        else{
             //mostrar mensaje de error 
             set_file("content", "mensaje.html");
             s("titulo","Error!");
             s("mensaje","<b>Sesión Finalizada.</b>");
             return Main::wrapper(pp("content"), "Error!",0,1);
         }
    }
    
	function doEditarUsuario($arr){
		
         if ($arr["apellido"] && $arr["nombre"] && $arr["domicilio"]){
             //hago el alta con:
             sql("update usuarios set nombre = '$arr[nombre]',apellido = '$arr[apellido]',cod_tel = '$arr[cod_tel]',telefono = '$arr[telefono]',cod_cel = '$arr[cod_cel]',celular = '$arr[celular]',domicilio = '$arr[domicilio]',localidad = '$arr[localidad]',cod_pos = '$arr[cod_pos]',id_provincia = '$arr[id_provincia]' where id = '$_SESSION[idUsuario]'");
             //mostrar el mensaje de registrado:
             set_file("content", "mensaje.html");
             s("titulo","Excelente!. La operaciópn se realizó con éxito.");
             s("mensaje","<b>Puede continuar realizando otras operaciones.</b>");
             return Main::wrapper(pp("content"), "Usuario Editado!",0,1);
         }else{
             //mostrar mensaje de error 
             set_file("content", "mensaje.html");
             s("titulo","Error!");
             s("mensaje","<b>No se ha podido realizar la operación. Intentelo de nuevo por favor y asegúrese de ingresar bien todos los datos.</b>");
             return Main::wrapper(pp("content"), "Error!",0,1);
         }
	}
	
	function showEditarAviso($idAviso){
        //sql
        $sql= "select * from usuarios where id = $_SESSION[idUsuario]";
        $rs = mysql_query($sql);
        if ($fila = mysql_fetch_assoc($rs)){
            set_file("content","showEditarAviso.html"); 
            //sus datos personales:
            s("nombre",$fila[nombre]);
            s("apellido",$fila[apellido]);
            s("mail",$fila[mail]);
            s("cod_tel",$fila[cod_tel]);
            s("telefono",$fila[telefono]);
            s("cod_cel",$fila[cod_cel]);
            s("celular",$fila[celular]);
            s("domicilio",$fila[domicilio]);
            s("localidad",$fila[localidad]);
            s("cod_pos",$fila[cod_pos]);
            $provincia = Main::getField("provincias","nombre",$fila[id_provincia]);
            s("nombre_provincia",$provincia);
			//sql:
			 $sql= "select * from avisos where id = '$idAviso'";
			 $rs = mysql_query($sql);
        	 $fila = mysql_fetch_assoc($rs);
			 
			 $_SESSION[idAviso] = $fila[id];
			 //publicar:
			 if ($fila[publicar_email]==1) {
				s("valor1","checked=checked");
				}else{
			  	s("valor1","");
				
			  }
			 
			  if ($fila[publicar_domicilio]==1) {
			 	s("valor2","checked=checked");
			  }else{
			  	s("valor2","");
			  }
			  
			  if ($fila[publicar_telefono]==1) {
			 	s("valor3","checked=checked");
			  }else{
			  	s("valor3","");
			  }
			  
			 //el aviso:
			  $rubro = Main::getField("rubros","nombre",$fila[id_tipo_rubro]);
              s("rubro",$rubro);
              
              if ( ($fila[id_tipo_rubro] == 1) || ($fila[id_tipo_rubro] == 3) ||($fila[id_tipo_rubro] == 5) ||($fila[id_tipo_rubro] == 6)){
                  
            //marcas:
            s("select_marca",Main::selectHelper("id_tipo_marca","marcas","nombre","id",$fila[id_tipo_marca]));
					
			//tipo combustibles:
			s("select_combustible",Main::selectHelper("id_tipo_combustible","combustibles","nombre","id",$fila[id_tipo_combustible]));
			//tipo autos:
			s("select_tipo",Main::selectHelper("id_tipo_auto","tipo_autos","nombre","id",$fila[id_tipo_auto]));
			//modelo:
			$fin = date("Y");
            s("select_anio",Main::selectHelperAnios("ano",1910,$fin,$fila[ano]));
            
                  s("mostrarVehiculos","");
              }else{
                  s("mostrarVehiculos","display:none");
              }
              
              //mismo para inmuebles 
              if ( ($fila[id_tipo_rubro] == 8) || ($fila[id_tipo_rubro] == 9) ||($fila[id_tipo_rubro] == 14) ||($fila[id_tipo_rubro] == 15) ||($fila[id_tipo_rubro] == 16) ||($fila[id_tipo_rubro] == 17) ||($fila[id_tipo_rubro] == 18) ||($fila[id_tipo_rubro] == 19)||($fila[id_tipo_rubro] == 20)){
              
              //tipo inmuebles:
            s("select_inmueble",Main::selectHelper("id_tipo_inmueble","tipo_inmuebles","nombre","id",$fila[id_tipo_inmueble]));
              
              s("mostrarInmuebles","");
              }else{
                  s("mostrarInmuebles","display:none");
              }
			  
              s("titulo",$fila[titulo]);
			  s("descripcion",$fila[descripcion]);
			  s("precio",$fila[precio_venta]);
			  s("forma_pago",$fila[forma_pago]);
			  s("comentario",$fila[comentario]);
			  s("domicilio_cobro",$fila[domicilio_cobro]);
			  s("horario_cobro",$fila[horario_cobro]);
			  
            return Main::wrapper(pp("content"),"Editar Aviso!",0,1); 
        } 
        else{
             //mostrar mensaje de error 
             set_file("content", "mensaje.html");
             s("titulo","Error!");
             s("mensaje","<b>Sesión finalizada.</b>");
             return Main::wrapper(pp("content"), "Error!",0,1);
         }
    }
	
	function doEditarAviso($arr){
		
         if ($arr["titulo"] && $arr["descripcion"] && $arr["domicilio_cobro"]){
		 //hago el alta con:
             sql("update avisos set titulo = '$arr[titulo]',descripcion = '$arr[descripcion]',precio_venta = '$arr[precio]',forma_pago = '$arr[forma_pago]',comentario = '$arr[comentario]',domicilio_cobro = '$arr[domicilio_cobro]',horario_cobro = '$arr[horario_cobro]',id_tipo_marca = '$arr[id_tipo_marca]',id_tipo_combustible = '$arr[id_tipo_combustible]',id_tipo_auto = '$arr[id_tipo_auto]',ano = '$arr[ano]',id_tipo_inmueble = '$arr[id_tipo_inmueble]',publicar_email = '$arr[publicar_email]',publicar_domicilio = '$arr[publicar_domicilio]',publicar_telefono = '$arr[publicar_telefono]' where id = '$_SESSION[idAviso]'");
			 //guardar id en session:
			 $_SESSION[idAviso]='';
             //mostrar el mensaje de registrado:
             set_file("content", "mensaje.html");
             s("titulo","Excelente!. La operación se realizó con éxito.");
             s("mensaje","<b>Puede continuar realizando otras operaciones.</b>");
             return Main::wrapper(pp("content"), "Usuario Editado!",0,1);
         }else{
             //mostrar mensaje de error 
             set_file("content", "mensaje.html");
             s("titulo","Error!");
             s("mensaje","<b>No se ha podido realizar la operación. Intentelo de nuevo por favor y asegúrese de ingresar bien todos los datos.</b>");
             return Main::wrapper(pp("content"), "Error!",0,1);
         }
	}
	
	function showAyuda(){
		set_file("content","showAyuda.html"); 
        if($_SESSION[idUsuario]==0){
        	return Main::wrapper(pp("content"),"Ayuda!",0,0); 
		}else{
			return Main::wrapper(pp("content"),"Ayuda!",0,1); 
		}
	}
	
	function doSalir(){
		//setear sesiones:
		$_SESSION[usuario] = 0;
        $_SESSION[idUsuario] = 0;
		//incluir clase:
		echo Pages::index();
	
	}
	
	function doBajaAviso($idAviso){
		
		mysql_query("update avisos set mostrar ='0' where id = '$idAviso'");
		
		set_file("content","mensaje.html"); 
		s("titulo","Excelente!. La operación se realizó con éxito. Su aviso no aparecera mas en nuestro sitio.");
		s("mensaje","<b>Puede continuar realizando otras operaciones.</b>");
            
        return Main::wrapper(pp("content"),"Eliminado!",0,1); 
	}
    
}//end class usuarios   
?>
