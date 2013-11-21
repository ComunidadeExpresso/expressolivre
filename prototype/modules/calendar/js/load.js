Refresh = {
    //Tempo em que ap�s a ultima sincroniza��o ser� verificado atualiza��es
    timeRefresh : 180,
    clookRefresh: false,

    init: function(){

	delete DataLayer.tasks[this.clookRefresh];
	
	//Realiza agendamentos de atualiza��o de view somente se o m�dulo aberto for expressoCalendar
	if(User.moduleName == "expressoCalendar"){
	    this.clookRefresh = (parseInt(($.now()) / 1000) + this.timeRefresh);

	    DataLayer.task( this.clookRefresh , function(){

            DataLayer.remove('schedulable', false);
            Calendar.rerenderView(true);
	    });
	}
    }
}

Calendar = {
  
    load: function(){
	this.lastView = 0;
	var filter = ['=', 'user', User.me.id];
	if(!!User.me.gidNumber){
	    if(!$.isArray(User.me.gidNumber))
		User.me.gidNumber = [User.me.gidNumber];

	    filter = ['OR', filter, ['IN', 'user', User.me.gidNumber]];
	}

	//var descart = DataLayer.get("calendarSignature", {filter: filter, criteria: {deepness: 2}});
	this.signatures  = DataLayer.get("calendarSignature", {
	    filter: filter, 
	    criteria: {
		deepness: 2
	    }
	});
	  
    var prevSources = this.sources;
    
    this.sources = DataLayer.encode( "calendarSignature:calendar", this.signatures );

     if( prevSources )
    {
	var newSources = this.sources.slice();

	for( var i = 0; i < newSources.length; i++ )
	    $('#calendar').fullCalendar( 'addEventSource', newSources[i] );

	for( var i = 0; i < prevSources.length; i++ )
	    $('#calendar').fullCalendar( 'removeEventSource', prevSources[i] );
    }

    this.calendarIds = [], this.groupIds = [], this.signatureOf = {}, this.calendars = [], this.groups = [], this.calendarOf = {}, this.groupOf= {};

    for( var i = 0; i < this.signatures.length; i++ ){
	if(this.signatures[i].isOwner == "0")
	    this.signatures[i].permission =  DataLayer.encode('calendarToPermission:detail', this.signatures[i].permission);

        if(this.signatures[i].calendar.type == '1')
           this.signatureOf[ this.groupIds[this.groupIds.length] = ( this.groups[ this.groups.length ] = this.groupOf[ this.signatures[i].id ] = this.signatures[i].calendar ).id ] = this.signatures[i];
        else
    	   this.signatureOf[ this.calendarIds[ this.calendarIds.length] = ( this.calendars[ this.calendars.length ] = this.calendarOf[ this.signatures[i].id ] = this.signatures[i].calendar ).id ] = this.signatures[i];
    }

    delete Calendar.currentViewKey;
    Refresh.init();
    },

    rerenderView: function(force){
        //TODO - Remover if quando centralizar o objeto User que contem as informa��es do usu�rio logado em um local acess�vel a todos m�dulos
        if(User.moduleName == "expressoCalendar"){
            if((typeof($tabs) != "undefined") && $tabs.tabs('option' ,'selected') == 0){
                if(force){
                    //Remove a inc�nsistencia do aninhamento de um mesmo tipo em diferentes conceitos
                    DataLayer.rollback('user');

                    delete Calendar.currentViewKey;
                    $('#calendar').fullCalendar( 'refetchEvents' );

                    //Recarrega os alarmes de eventos    
                    Alarms.load();

                    Refresh.init();
                }

                var calendarNotSelected = getSelectedCalendars( true );
                for(var i = 0; i < calendarNotSelected.length; i++)
                        if(!!Calendar.currentView[ calendarNotSelected[i] ])
                        Calendar.currentView[ calendarNotSelected[i] ].hidden = true;

                $('#calendar').fullCalendar( 'refetchEvents' );	

                contentMenu();
            }else if((typeof($tabs) != "undefined") && $tabs.tabs('option' ,'selected') != 0)
				
                pageselectCallback($('.events-list-win.active [name=keyword]').val(), 0, false, ($tabs.tabs('option' ,'selected') > 2) ? 2 : ($tabs.tabs('option' ,'selected') == 1) ? 0 : 1);
        }
    }
}

Calendar.load();
