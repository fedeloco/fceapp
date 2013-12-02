var Notificaciones =  new Array();
var app = {
    // Application Constructor
    initialize: function() {
        this.bindEvents();
		
		//Ahora pido al servidor el listado de notificaciones y si no esta en la base de datos local lo agrego.
		if (!navigator.onLine){
			//en vez de cargar los items de internet los tomo del local storage...
			db.transaction(function(tx){
				tx.executeSql("Select * from notificaciones",[],function(tx,results){
					var len = results.rows.length;
					
					
					resultado='<ul id="listadoList" data-role="listview"  data-filter="true"></ul>';
					$("#listado").append(resultado);
					for (var i=0; i < len; i = i + 1) {
						console.log(results.rows.item(i));
						$("#listadoList").append("<li >"+results.rows.item(i).mensaje+'<span class="ui-li-count ui-btn-up-c ui-btn-corner-all">Canal</span></li>');							
					}
					$("#listadoList").listview();
				});
			});
		}//fin sin coneccion
		else{
			 $.getJSON('http://federicoemiliani.com/gnix.com.ar/index.php?callback=?', {"act":"doPedirNotificaciones","id":miDNI}, 
			 	function (json) {
        			//cargar listado
					if (json.length == 0){
						alert("No hay notificaciones recientes");
					}
					resultado='<ul id="listadoList" data-role="listview"  data-filter="true"></ul>';
					$("#listado").append(resultado);
					jQuery.each(json, function(i, val) {
						$("#listadoList").append("<li >"+json[i].mensaje+'<span class="ui-li-count ui-btn-up-c ui-btn-corner-all">Canal</span></li>');
						//a medida que cargo los resultados me fijo si los tengo que agregar al storage local
						Notificaciones.push( new Array(json[i].id,json[i].mensaje));
					});
					ingresarNotificaciones(0);
					$("#listadoList").listview();
					$("#formulario").fadeOut();
      		});
		}//fin con conexion
		
		
    },
    // Bind Event Listeners
    //
    // Bind any events that are required on startup. Common events are:
    // 'load', 'deviceready', 'offline', and 'online'.
    bindEvents: function() {
        document.addEventListener('deviceready', this.onDeviceReady, false);
    },
    // deviceready Event Handler
    //
    // The scope of 'this' is the event. In order to call the 'receivedEvent'
    // function, we must explicity call 'app.receivedEvent(...);'
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
    // result contains any message sent from the plugin call
    successHandler: function(result) {
        alert('Success! Result = '+result)
    },
    // Update DOM on a Received Event
    receivedEvent: function(id) {
        var pushNotification = window.plugins.pushNotification;
        
        // TODO: Enter your own GCM Sender ID in the register call for Android
        if (device.platform == 'android' || device.platform == 'Android') {
            pushNotification.register(this.successHandler, this.errorHandler,{"senderID":"515684167197","ecb":"app.onNotificationGCM"});
        }
        else {
            pushNotification.register(this.tokenHandler,this.errorHandler,{"badge":"true","sound":"true","alert":"true","ecb":"app.onNotificationAPN"});
        }
        
    },
    // iOS
    onNotificationAPN: function(event) {
        var pushNotification = window.plugins.pushNotification;
        
        if (event.alert) {
            navigator.notification.alert(event.alert);
        }
        if (event.badge) {
            
            pushNotification.setApplicationIconBadgeNumber(this.successHandler, event.badge);
        }
        if (event.sound) {
            var snd = new Media(event.sound);
            snd.play();
        }
    },
    // Android
    onNotificationGCM: function(e) {
        
        switch( e.event )
        {
            case 'registered':
                if ( e.regid.length > 0 )
                {
                    // Your GCM push server needs to know the regID before it can push to this device
                    // here is where you might want to send it the regID for later use.
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
						   alert("todo OK");
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
              alert(e.message);
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
