function agregarElemento(texto,id,etiqueta){
	$("#listadoList").append("<li id='elementoLista"+id+"'><a href='#' class='mensajeA'><span class='texto'>"+texto +
						'</span><span class="ui-li-count ui-btn-up-c ui-btn-corner-all">'+etiqueta+'</span></a>' +
						'<a href="javascript:eliminarElemento('+id+')" data-theme="a" >Eliminar</a>' +
						'</li>');
}

function eliminarElemento(id){
 	$("#elementoLista"+id).slideUp(200);
	ocultarElementoEnLocal(id);	
	alert("borrando"+window.localStorage.getItem("miDNI")+" "+id);
	
	$.getJSON('http://federicoemiliani.com/gnix.com.ar/index.php?callback=?', 
		{"act":"doBorrarNotificacionParaMi","dni":window.localStorage.getItem("miDNI"),"idNotificacion":id}, 
	 	function (json){});
	
		
}


var Notificaciones =  new Array();
var app = {

    initialize: function() {
        this.bindEvents();
		if (!navigator.onLine){
			db.transaction(function(tx){
				tx.executeSql("Select * from notificaciones",[],function(tx,results){
					var len = results.rows.length;
					resultado='<ul id="listadoList" data-role="listview"  data-filter="true"></ul>';
					$("#listado").append(resultado);
					for (var i=0; i < len; i = i + 1) {
						$("#listadoList").append("<li >"+results.rows.item(i).mensaje+'<span class="ui-li-count ui-btn-up-c ui-btn-corner-all">Canal</span></li>');							
					}
					$("#listadoList").listview();
				});
			});
		}
		else{
			 $.getJSON('http://federicoemiliani.com/gnix.com.ar/index.php?callback=?', 
			 			{"act":"doPedirNotificaciones","dni":window.localStorage.getItem("miDNI")}, 
			 	function (json) {
					if (json.length == 0){
						alert("No hay notificaciones recientes");
					}
					console.log(json.length);
					console.log(json);
					resultado='<ul id="listadoList" data-role="listview"  data-filter="true" data-split-icon="delete"></ul>';
					$("#listado").append(resultado);
					$("#cargando").hide();
					//$("#listadoList").hide();
					jQuery.each(json, function(i, val) {
						if (json[i].id_usuario != null){
							agregarElemento(json[i].mensaje,json[i].id,json[i].nombre_canal);
							//a medida que cargo los resultados me fijo si los tengo que agregar al storage local
							Notificaciones.push( new Array(json[i].id,json[i].mensaje,json[i].nombre_canal,0));
						}
					});
					ingresarNotificaciones(0);
					$("#listadoList").listview();
					$("#formulario").hide();
      		});
		}
    },
    bindEvents: function() {
        document.addEventListener('deviceready', this.onDeviceReady, false);
    },
    onDeviceReady: function() {
        app.receivedEvent('deviceready');
    },
    tokenHandler:function(msg) {
        console.log("Token Handler " + msg);
    },
    errorHandler:function(error) {
        console.log("Error Handler  " + error);
        alert("Errors: "+error);
    },
    successHandler: function(result) {
        alert('Success! Result = '+result)
    },
    receivedEvent: function(id) {
        var pushNotification = window.plugins.pushNotification;
        if (device.platform == 'android' || device.platform == 'Android') {
            pushNotification.register(this.successHandler, this.errorHandler,{"senderID":"515684167197","ecb":"app.onNotificationGCM"});
        }
        else {
            pushNotification.register(this.tokenHandler,this.errorHandler,{"badge":"true","sound":"true","alert":"true","ecb":"app.onNotificationAPN"});
        }
    },
    onNotificationAPN: function(event) {
   
    },
    // Android
    onNotificationGCM: function(e) {
        
        switch( e.event )
        {
            case 'registered':
                if ( e.regid.length > 0 )
                {
                 
                    alert('registration id = '+e.regid);
					$.ajax({
						type: 'get',
						url: "http://federicoemiliani.com/gnix.com.ar/index.php?callback=?",
						async: false,
						jsonpCallback: 'jsonCallback',
						contentType: "application/json",
						dataType: 'jsonp',
						data : {"act":"doGuardarDatos","reg_id":e.regid,"dni":miDNI},
						success: function(json) {
						   alert("todo OK " + e.regid);
						},
						error: function(e) {
						 	alert("todo Mal");
						}
					});
					
                }
            break;

            case 'message':
              	// this is the actual push notification. its format depends on the data model
              	// of the intermediary push server which must also be reflected in GCMIntentService.java
		    	$("#Mensaje").html(e.message);
				$("#myPopUp").popup("open");
           		
            break;

            case 'error':
              alert('GCM error = '+e.msg);
            break;

            default:
              alert('An unknown GCM event has occurred');
              break;
        }
    }

};
