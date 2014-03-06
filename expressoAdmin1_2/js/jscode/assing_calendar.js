function assing_calendar_user(path){
        
        path = !!path ? path : 'templates/default/';
    	var html = DataLayer.render(path+'assing_calendar.ejs', {});	
	
	//Variaval global para manipulação dos usuários
	//melhor perforface
	currentUsers = {};
	currentUsers[User.me.id] = true;
        
        searchType = 'calendar';
	
	if (!UI.dialogs.assingCalendar) {
			UI.dialogs.assingCalendar = jQuery('#assingCalendar').append('<div title="Compatilhamento de Agenda" class="shared-calendar assing-calendar active"> <div>').find('.assing-calendar.active').html(html).dialog({
				resizable: false, 
				modal:true, 
				autoOpen: false,
				width:620, 
				position: 'center', 
				close: function(event, ui) {
					//Implementações de cancelar
					currentUsers = {};
				}
			});
		} else {
			UI.dialogs.assingCalendar.html(html);
		}		
		
		UI.dialogs.assingCalendar.find('.button').button()
		.filter('.cancel').click(function(evt){
			UI.dialogs.assingCalendar.dialog("close");
		}).end()
		.filter('.save').click(function(evt){
			//TODO - API ainda não suporta
			//UI.dialogs.assingCalendar.find('form').submit();
                        var calendarSelected = UI.dialogs.assingCalendar.find('dd.calendar input[name="calendarId"]').val();
			var save = function(){
				if($('li.not-user').length == 0)
				$.each(UI.dialogs.assingCalendar.find('.user-list li.new'), function( i , element ){
					var user = $(element).find('input[name="user[]"]').val();
					var acl = $(element).find('input[name="attendeeAcl[]"]').val()+'p';
					if(acl == "")
                                            return true;
					
                                        DataLayer.put('calendarToPermission', {calendar: calendarSelected, type: 0, user: user, acl: acl});

                                        DataLayer.put('calendarSignature', 
                                        {
                                            calendar: calendarSelected, 
                                            isOwner: 0, 
                                            user: user, 
                                            fontColor: '120d0d', 
                                            backgroundColor: 'e36d76', 
                                            borderColor: 'd5130b' 
                                        });
				});
                                
                                $.each(UI.dialogs.assingCalendar.find('.user-list li.current'), function( i , element ){
                                    
                                    var user = $(element).find('input[name="user[]"]').val();
                                    var acl = $(element).find('input[name="attendeeAcl[]"]').val()+'p';
                                    var id = $(element).find('input[name="permission[]"]').val();
                                    if(acl == "")
                                        DataLayer.remove('calendarToPermission', id);
					
                                    DataLayer.put('calendarToPermission', {id: id, calendar: calendarSelected, type: 0, user: user, acl: acl});
                                    
                                });
				
                                DataLayer.commit(false, false, function(data){
					UI.dialogs.assingCalendar.dialog("close");
				});
			};

			if(!!UI.dialogs.assingCalendar.find('.user-list li input[name="attendeeAcl[]"][value=""]').length){
				$.Zebra_Dialog('Alguns usuários estão sem permissões e serão automáticamente removidos, deseja continuar ?', {
					'type':     'question',
					'overlay_opacity': '0.5',
					'buttons':  ['Continuar', 'Cancelar'],
					'onClose':  function(clicked) {
						if(clicked == 'Continuar') {
							save();
						}
					}
				});
			}else
				save();		
		});
		
		UI.dialogs.assingCalendar.find('.add-user-search .ui-icon-search').click(function(event) {
			UI.dialogs.assingCalendar.find('.add-user-search input').keydown();
		});
		
		var incompatibleAcl = function (obj, acls, buttons){
			for (var i = 0; i < acls.length; i++){
				var objremove = obj.parent().find('.'+buttons[i]+'');
				if(objremove.hasClass('attendee-permissions-change-button'))
					changeAcl(objremove, acls[i]);
			}
		}

		var removeAcl = function(current, acl){
			var acls = '';
			for(var i = 0; i < current.length; i++)
				if(current.charAt(i) != acl)
					acls += current.charAt(i) ;
			return acls;
		}

		var dependsAcl = function(obj, acls, buttons){
			for (var i = 0; i < acls.length; i++){
				var objremove = obj.parent().find('.'+buttons[i]+'');
				if(!objremove.hasClass('attendee-permissions-change-button'))
					changeAcl(objremove, acls[i]);
			}
		}
		
		var changeAcl = function(obj, acl){
			if(obj.hasClass('attendee-permissions-change-button')){
				 obj.parent().siblings('input[name="attendeeAcl[]"]').val(removeAcl( obj.parent().siblings('input[name="attendeeAcl[]"]').val(), acl)) ; 
			}else{
				var acls = obj.parent().siblings('input[name="attendeeAcl[]"]').val();
				obj.parent().siblings('input[name="attendeeAcl[]"]').val(acls + acl);
			}
			obj.toggleClass('attendee-permissions-change-button')
			.find('span:first').toggleClass('attendee-permissions-change').end();  
		}
		
		
		UI.dialogs.assingCalendar.find('.add-user-search input').bind('keypress', function(event) {
			if(event.keyCode == '13' || typeof(event.keyCode) == 'undefined') {
                            
                                var result = '';
                                var group  = '';
                                if(searchType == 'calendar') {
                                    var calendarIds = [];
                                	var findCalendars = DataLayer.get('calendar', {filter: ['OR', ['i*', 'name', $(this).val()], ['i*', 'description', $(this).val()] ] });
                                
                                	for( var i in findCalendars ) { 
                                		if (findCalendars[i]['id']) 
                                			calendarIds.push( findCalendars[i]['id'] );
                                	}	

                                	result = DataLayer.get('calendarToPermission',  {filter: ['AND', ['=','type','1'], ['IN', 'calendar', calendarIds] ], criteria: {deepness: 2} });
                                } else {
                                    result = DataLayer.get('user', ["*", "name", $(this).val()], true);
                                    group = DataLayer.get('group', ["*", "name", $(this).val()], true); 
				
                                    if(!!group){
                                        if(!!result)
                                                DataLayer.merge(result, group);
                                        else
                                                result = group;
                                    }
                                }
                                /**
				* TODO: trocar por template
				*/
				UI.dialogs.assingCalendar.find('ul.search-result-list').empty().css('overflow', 'hidden');
				if (!result) {
					UI.dialogs.assingCalendar.find('ul.search-result-list').append('<li><label class="empty">Nenhum resultado encontrado.</label></li>');
				}

                                if(searchType == 'calendar')
                                     for(i=0; i<result.length; i++){
                                    	result[i].name = result[i].calendar.name;
                                        result[i].id = result[i].calendar.id;
                                        result[i].enabled = true;
                                     }
                                else
                                    for(i=0; i<result.length; i++)
                                    	result[i].enabled = currentUsers[result[i].id] ? false : true;

				UI.dialogs.assingCalendar.find('ul.search-result-list').append(DataLayer.render( path+'participants_search_itemlist.ejs', result));

				UI.dialogs.assingCalendar.find('ul.search-result-list li').click(function(event, ui){
					if ($(event.target).is('input')) {
                                             old_item = $(event.target).parents('li');
                                             
                                             if(searchType == 'calendar'){
                                                 
                                                 searchType = 'user';
                                                 
                                                 UI.dialogs.assingCalendar.find('dd.calendar input[name="calendarId"]').val(old_item.find('.id').html());
                                                 
                                                 //TODO - Trocar por template
                                                 UI.dialogs.assingCalendar.find('dd.calendar ul').append('<li class="calendar-selected">' + old_item.find('.name').html() +
                                                 '<a class="button tiny removeCalendar"></a></li>');
                                             
                                             
                                                 var currentData = DataLayer.get('calendarToPermission:detail', {filter: ['AND', ['=','calendar', old_item.find('.id').html()], ['i*','acl','p'] ],  criteria: {deepness: 2}})
                                                 
                                                 if(currentData){
                                                     for(var i = 0; i < currentData.length; i++){
                                                         
                                                         currentUsers[currentData[i].user.id] = currentData[i].user;
                                                         
                                                         UI.dialogs.assingCalendar.find('dd.calendar-list ul.user-list')
                                                        .append(DataLayer.render(path+'user_shared_add_itemlist.ejs', [{id: currentData[i].user.id, name: currentData[i].user.name, mail: currentData[i].user.mail, isCurrent: true, permission: currentData[i].id}]))
                                                        .scrollTo('max');

                                                        $('li.not-user').remove();
                                                        callbackSharedCotactsAdd();
                                                        
                                                        for (var f in currentData[i].acl){
                                                            if(currentData[i].acl[f]){
                                                                    UI.dialogs.assingCalendar.find('.'+f+':last').toggleClass('attendee-permissions-change-button')
                                                                    .find('span:first').toggleClass('attendee-permissions-change').end();  
                                                            }
                                                        }
                                                         
                                                     }
                                                     
                                                 }

                                                 UI.dialogs.assingCalendar.find('.removeCalendar').button({
                                                        icons: {
                                                                primary: "ui-icon-close"
                                                        },
                                                        text: false
                                                }).click(function(evt){
                                                        $(this).parents('li').remove();
                                                        searchType = 'calendar';
                                                        
                                                        UI.dialogs.assingCalendar.find('ul.search-result-list li').remove();
                                                        UI.dialogs.assingCalendar.find('dt.add-user.search').html('Pesquisa agendas');
                                                 });
                                                 
                                                 UI.dialogs.assingCalendar.find('ul.search-result-list li').remove();
                                                 UI.dialogs.assingCalendar.find('dt.add-user.search').html('Pesquisa usuários');
                                                 
                                                 
                                             }else{
						
						var id = old_item.find('.id').html();
						
						currentUsers [id] = {
							id: id,
							name: old_item.find('.name').html(),
							mail: old_item.find('.mail').html()
						};
											
						UI.dialogs.assingCalendar.find('dd.calendar-list ul.user-list')
						.append(DataLayer.render(path+'user_shared_add_itemlist.ejs', [{id: id, name: currentUsers [id] .name, mail: currentUsers [id] .mail}]))
						.scrollTo('max');
						
						$('li.not-user').remove();
						callbackSharedCotactsAdd();
						old_item.remove();
                                            }
					}
				});
				event.preventDefault();
			}
		});

		var callbackSharedCotactsAdd = function(event){
			UI.dialogs.assingCalendar.find('.button').filter(".read.new").button({
				icons: {
					primary: "ui-icon-circle-zoomin"
				},
				text: false
			}).click(function () {
				incompatibleAcl($(this), ['b'], ['busy']);
				changeAcl($(this), 'r');
			})
			.addClass('tiny disable ui-button-disabled ui-state-disabled')
			.removeClass('new').end()
			
			.filter(".write.new").button({
				icons: {
					primary: "ui-icon-key"
				},
				text: false
			}).click(function () {
				incompatibleAcl($(this), ['b'], ['busy']);
				dependsAcl($(this), ['r'], ['read']);
				changeAcl($(this), 'w');
			})
			.addClass('tiny disable ui-button-disabled ui-state-disabled')
			.removeClass('new').end()
			
			.filter(".remove.new").button({
				icons: {
					primary: "ui-icon-trash"
				},
				text: false
			}).click(function () {
				incompatibleAcl($(this), ['b'], ['busy']);
				dependsAcl($(this), ['r'], ['read']);
				changeAcl($(this), 'd');
			})
			.addClass('tiny disable ui-button-disabled ui-state-disabled')
			.removeClass('new').end()
			
			.filter(".busy.new").button({
				icons: {
					primary: "ui-icon-clock"
				},
				text: false
			}).click(function () {
				//Remove incompativbilidade de acls
				incompatibleAcl($(this), ['r', 'w', 'd', 's' ], ['read', 'write', 'remove', 'shared']);
				changeAcl($(this), 'b');
			})
			.addClass('tiny disable ui-button-disabled ui-state-disabled')
			.removeClass('new').end()
			
			.filter(".shared.new").button({
				icons: {
					primary: "ui-icon-transferthick-e-w"
				},
				text: false
			}).click(function () {
				incompatibleAcl($(this), ['b',], ['busy']);
				dependsAcl($(this), ['r'], ['read']);
				changeAcl($(this), 's');
			})
			.addClass('tiny disable ui-button-disabled ui-state-disabled')
			.removeClass('new').end()

			.filter(".close.new").button({
				icons: {
					primary: "ui-icon-close"
				},
				text: false
			}).click(function () {
				currentUsers[$(this).parents().find('input[name="user[]"]').val()] = false;
                                var id = false;
                                
                                if(id = $(this).parents().find('input[name="permission[]"]').val())
                                    DataLayer.remove('calendarToPermission', id);
                                
				$(this).parents('li').remove();
			})
			.addClass('tiny disable ui-button-disabled ui-state-disabled')
			.removeClass('new').end();

		UI.dialogs.assingCalendar.find('.user-list li').hover(
			function () {
				$(this).addClass("hover-user");
				$(this).find('.button').removeClass('disable ui-button-disabled ui-state-disabled').end()
				.find('.user-acls-shared-calendar').addClass('hover-user');
			},
			function () {
				$(this).removeClass("hover-user");
				$(this).find('.button').addClass('disable ui-button-disabled ui-state-disabled').end()
				.find('.user-acls-shared-calendar').removeClass('hover-user');;
			}
		);		
	}
	UI.dialogs.assingCalendar.dialog('open');
}

