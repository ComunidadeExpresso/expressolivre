desktopNotification = {
    
    notification: [],

    initDesktopNotificationAddon: function(){
		if(useDesktopNotification()){
			if ($.cookie('no-notification') == 'true'){
				return false;
			}


			if (window.webkitNotifications && $.browser.mozilla) {
				$.Zebra_Dialog(get_lang("you must unninstall html5 notifications"), {
                        'custom_class': 'request-notification-permission',
                        'type':     'question',
                        'overlay_opacity': '0.5',
                    'buttons':  ['Ok']
                    });

					$('div.ZebraDialog.request-notification-permission a').click(function() {
						$.cookie('no-notification','true');
					});
			}
			else {
				if ($.browser.msie || !("Notification" in window)) {
					$.cookie('no-notification',null);
					alert(get_lang("This browser does not support desktop notification"));
				}
				else if (Notification.permission === 'default') {
	                 $.Zebra_Dialog(get_lang("wish installing notification plugin?"), {
	                    'custom_class': 'request-notification-permission',
	                    'type':     'question',
	                    'overlay_opacity': '0.5',
	                    'buttons':  ['Não', 'Sim']
                    });
	                $('div.ZebraDialog.request-notification-permission a').click(function() {
	                    if($(this).html() == 'Sim'){
	                        Notification.requestPermission(function (permission) {
								if(!('permission' in Notification)) {
									$.cookie('no-notification','true');
								}
							});      
	                	}else if ($(this).html() == 'Não'){
	                		$.cookie('no-notification','true');
	                	}
	                });
				}			
            }
		}else{
			$.cookie('no-notification',null);
		}
    },

    installDektopNotificationAddon: function(){

    	var params = {
    	    "Foo": {
    			URL: '../prototype/plugins/desktop.notification/html_desktop_notifications.xpi' ,
    			IconURL:'../prototype/plugins/desktop.notification/desktop-notification.png',
    			//Hash:'sha1:28857e60d043447c5f4550853f2d40770b326a13',
    			toString: function () {
    				return this.URL;
    			}
    	    }
    	};
	   ;
	   InstallTrigger.install(params);
	   return false;
    },

    verifyComplement: function(){

		if ($.browser.msie || !("Notification" in window))
			return false;

		if (Notification.permission !== 'granted')
			return false;

		return true;
    },

    sentNotification: function(icon, title, body){
		var reference = this.notification.length;
		this.notification[reference] = {icon: icon, title: title, body:body};
		return reference;
	},

	cancelByReference: function(index){
		if(this.notification[index]) {
			if(!this.notification[index].icon) //if showNotification was called, theres no this.notification[index].icon, but we must close the popup.
				this.notification[index].close();
			this.notification.splice(index,1);
		}
	},
	
	
    showNotification: function(onClose, onClick, onDisplay, onError){
		var length = this.notification.length -1; 

		var notify = new Notification(this.notification[length].title,this.notification[length]);
		notify.ondisplay = onDisplay;
		notify.onclose = onClose;
		notify.onclick = onClick;
		notify.onerror = onError;
		this.notification[length] = notify;
    }
}

$(document).ready(function() {
    activePage = true;
    $(window)
	.focus(function() { 
	    activePage = true;  
	    if(desktopNotification.verifyComplement()){
			setTimeout(function(){		    
				
				for(var i = 0; i < desktopNotification.notification.length; i++){
				desktopNotification.notification[i].cancel();
				}		    
				desktopNotification.notification = [];
				
			}, 60000);
	    }
	})
	.blur(function() {
	    activePage = false;
	});

    desktopNotification.initDesktopNotificationAddon();
});
