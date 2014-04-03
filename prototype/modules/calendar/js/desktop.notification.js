desktopNotification = {
    
    notification: [],

    initDesktopNotificationAddon: function(){
		if(useDesktopNotification()){
			if ($.cookie('no-notification') == 'true'){
				return false;
			}
			if (!window.webkitNotifications && $.browser.mozilla)
			this.installDektopNotificationAddon();

			if(window.webkitNotifications && window.webkitNotifications.checkPermission()){
			    if($.browser.safari){
                     $.Zebra_Dialog('_[[Do you want to install the notification plugin desktop?]]', {
                        'custom_class': 'request-notification-permission',
                        'type':     'question',
                        'overlay_opacity': '0.5',
                        'buttons':  ['_[[No]]', '_[[Yes]]']
                    });

                    $('div.ZebraDialog.request-notification-permission a').click(function() {
                        if($(this).html() == '_[[Yes]]'){
                            window.webkitNotifications.requestPermission();       
                    	}else if ($(this).html() == '_[[No]]'){
                    		$.cookie('no-notification','true');
                    	}
                    });
                }else
                    window.webkitNotifications.requestPermission();
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

		if(!window.webkitNotifications)
			return false;

		if(window.webkitNotifications.checkPermission())
			return false;

		return true;
    },

    sentNotification: function(icon, title, body){
		var reference = this.notification.length;
		this.notification[reference] = window.webkitNotifications.createNotification( icon, title, body);
		return reference;
	},

	cancelByReference: function(index){
		if(this.notification[index])
			this.notification[index].cancel();
	},
	
	
    showNotification: function(onClose, onClick, onDisplay, onError){
		var length = this.notification.length -1; 

		this.notification[length].ondisplay = onDisplay;
		this.notification[length].onclose = onClose;
		this.notification[length].onclick = onClick;
		this.notification[length].onerror = onError;

		this.notification[length].show();
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