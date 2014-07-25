function show_modal_shared(calendarId){
    $('.qtip.qtip-blue').remove();

    var html = DataLayer.render('templates/shared_calendar.ejs', {
        calendar: Calendar.calendars,
        signature : Calendar.signatures
    });

    //Variaval global para manipulação dos usuários
    //melhor perforface
    currentUsers = {};
    currentUsers[User.me.id] = true;
    changePublic = false;

    if (!UI.dialogs.sharedCalendar) {
        UI.dialogs.sharedCalendar = jQuery('#calendarShared').append('<div title="'+ '_[[Sharing Calendar]]' +'" class="shared-calendar active"> <div>').find('.shared-calendar.active').html(html).dialog({
            resizable: false,
            modal:true,
            autoOpen: false,
            width:620,
            position: 'center',
            close: function(event, ui) {
                //Implementações de cancelar
                DataLayer.rollback('calendarToPermission');
                currentUsers = {};
            }
        });
    } else {
        UI.dialogs.sharedCalendar.html(html);
    }

    /*Seleciona a agenda selecionada para compartilhamento*/
    UI.dialogs.sharedCalendar.find('option[value="'+calendarId+'"]').attr('selected','selected').trigger('change');

    UI.dialogs.sharedCalendar.find('input[name="isPublic"]').change(function(event){
        $(this).parent().find('.free-busy').toggleClass('hidden');
        changePublic = changePublic ? false: true;
    });

    UI.dialogs.sharedCalendar.find('.button').button()
        .filter('.cancel').click(function(evt){
            UI.dialogs.sharedCalendar.dialog("close");
        }).end()
        .filter('.save').click(function(evt){
            //TODO - API ainda não suporta
            //UI.dialogs.sharedCalendar.find('form').submit();
            var calendarSelected = UI.dialogs.sharedCalendar.find('option:selected').val();
            var save = function(){
                if($('li.not-user').length == 0)
                    $.each(UI.dialogs.sharedCalendar.find('.user-list li.new'), function( i , element ){
                        var user = $(element).find('input[name="user[]"]').val();
                        var acl = $(element).find('input[name="attendeeAcl[]"]').val();
                        if(acl == "")
                            return true;
                        DataLayer.put('calendarToPermission', {
                            calendar: calendarSelected,
                            type: 0,
                            user: user,
                            acl: acl
                        });
                    });

                $.each(UI.dialogs.sharedCalendar.find('.user-list li.current'), function( i , element ){
                    var id = $(element).find('input[type="checkbox"]').val();
                    var acl = $(element).find('input[name="attendeeAcl[]"]').val();
                    if(acl == "")
                        DataLayer.remove('calendarToPermission', id);
                    else
                        DataLayer.put('calendarToPermission', {
                            calendar: calendarSelected,
                            type: 0,
                            id: id,
                            acl: acl
                        });
                });

                DataLayer.commit(false, false, function(result){

                    /*
                     * Force clean cache
                     * */
                    DataLayer.storage.cache = {};
                    UI.dialogs.sharedCalendar.dialog("close");

                });

            };

            //Trata a criação de um acesso público a agenda
            if(changePublic){
                if(UI.dialogs.sharedCalendar.find('input[name="isPublic"]').is(':checked')){
                    var acl = UI.dialogs.sharedCalendar.find('input[name="busy"]').is(':checked') ? 'b' : 'w';
                    DataLayer.put('calendarToPermission', {
                        calendar: calendarSelected,
                        type: '1',
                        user: '0',
                        acl: acl
                    });
                }else{
                    //TODO - remover quando for implementado remove com criteria
                    var permission = DataLayer.get('calendarToPermission', {
                        filter: ['AND', ['=', 'calendar', calendarId], ['=', 'user', '0']]
                    }, true);
                    if($.isArray(permission))
                        permission = permission[0];

                    DataLayer.remove('calendarToPermission', permission.id);
                }
            }
            if(!!UI.dialogs.sharedCalendar.find('.user-list li input[name="attendeeAcl[]"][value=""]').length){
                $.Zebra_Dialog('_[[Some users are without permission and will be automatically removed. Do you want to continue?]]', {
                    'type':     'question',
                    'overlay_opacity': '0.5',
                    'buttons':  ['_[[Continue]]', '_[[Cancel]]'],
                    'onClose':  function(clicked) {
                        if(clicked == '_[[Continue]]') {
                            save();
                        }
                    }
                });
            }else
                save();
        });

    UI.dialogs.sharedCalendar.find('.add-user-search .ui-icon-search').click(function(event) {
        UI.dialogs.sharedCalendar.find('.add-user-search input').keydown();
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


    UI.dialogs.sharedCalendar.find('.add-user-search input').keydown(function(event) {
        if(event.keyCode == '13' || typeof(event.keyCode) == 'undefined') {
            var result = DataLayer.get('user', ["*", "name", $(this).val()], true);
            /**
             * TODO: trocar por template
             */
            UI.dialogs.sharedCalendar.find('ul.search-result-list').empty().css('overflow', 'hidden');
            if (!result) {
                UI.dialogs.sharedCalendar.find('ul.search-result-list').append('<li><label class="empty">' + '_[[No results found.]]' + '</label></li>');
            }

            for(i=0; i<result.length; i++)
                result[i].enabled = currentUsers[result[i].id] ? false : true;

            UI.dialogs.sharedCalendar.find('ul.search-result-list').append(DataLayer.render( 'templates/participants_search_itemlist.ejs', result));

            UI.dialogs.sharedCalendar.find('ul.search-result-list li').click(function(event, ui){
                if ($(event.target).is('input')) {
                    old_item = $(event.target).parents('li');
                    var id = old_item.find('.id').html();

                    currentUsers [id] = {
                        id: id,
                        name: old_item.find('.name').html(),
                        mail: old_item.find('.mail').html()
                    };

                    UI.dialogs.sharedCalendar.find('dd.user-list ul.user-list')
                        .append(DataLayer.render('templates/user_shared_add_itemlist.ejs', [{
                            id: id,
                            name: currentUsers [id] .name,
                            mail: currentUsers [id] .mail
                        }]))
                        .scrollTo('max');

                    $('li.not-user').remove();
                    callbackSharedCotactsAdd();
                    old_item.remove();
                }
            });
            event.preventDefault();
        }
    });

    var callbackSharedCotactsAdd = function(event){
        UI.dialogs.sharedCalendar.find('.button').filter(".read.new").button({
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
                incompatibleAcl($(this), ['b'], ['busy']);
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
                //remove permissão
                if($(this).parents('li.current').length)
                    DataLayer.remove('calendarToPermission', $(this).parents('li.current').find('input[type="checkbox"]').val());

                $(this).parents('li').remove();
            })
            .addClass('tiny disable ui-button-disabled ui-state-disabled')
            .removeClass('new').end();

        UI.dialogs.sharedCalendar.find('.user-list li').hover(
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

    //Carrega os dados já cadastrados
    var loadOldData = function (calendar)
    {
        UI.dialogs.sharedCalendar.find('dd.user-list ul.user-list').empty();

        var dataCurrent = DataLayer.get('calendarToPermission:detail', {
            filter: ['=','calendar', calendar]  ,
            criteria: {
                deepness: 2
            }
        });

        if(dataCurrent){
            for(var i = 0; i < dataCurrent.length; i++){
                if(dataCurrent[i].user == "0"){
                    UI.dialogs.sharedCalendar.find('input[name="isPublic"]').attr('checked', 'checked')
                        .parent().find('.free-busy').toggleClass('hidden');
                    if(dataCurrent[i].acl['busy'])
                        UI.dialogs.sharedCalendar.find('input[name="busy"]').attr('checked', 'checked');
                }else{
                    currentUsers[dataCurrent[i].user.id] = true;

                    UI.dialogs.sharedCalendar.find('dd.user-list ul.user-list')
                        .append(DataLayer.render('templates/user_shared_add_itemlist.ejs', [{
                            id: dataCurrent[i].user.id,
                            name: dataCurrent[i].user.name,
                            mail: dataCurrent[i].user.mail,
                            acl:dataCurrent[i].acl,
                            aclValues: dataCurrent[i].aclValues,
                            isCurrent: true,
                            idPermission: dataCurrent[i].id
                        }]))
                        .scrollTo('max');
                    $('li.not-user').remove();
                    callbackSharedCotactsAdd();

                    for (var f in dataCurrent[i].acl){
                        if(dataCurrent[i].acl[f]){
                            UI.dialogs.sharedCalendar.find('.'+f+':last').toggleClass('attendee-permissions-change-button')
                                .find('span:first').toggleClass('attendee-permissions-change').end();
                        }
                    }
                }
            }
        }
    }

    loadOldData(calendarId);

    //Bind calendar select change
    var select = UI.dialogs.sharedCalendar.find('select[name="calendar"]');
    select.change(function() {
        loadOldData(select.val());
    });

    UI.dialogs.sharedCalendar.dialog('open');
}

function show_modal_search_shared(){
    $('.qtip.qtip-blue').remove();

    var html = DataLayer.render('templates/shared_calendar.ejs', {});

    //Variaval global para manipulação dos usuários
    //melhor perforface
    currentCalendars = {};

    if (!UI.dialogs.sharedCalendar) {
        UI.dialogs.sharedCalendar = jQuery('#calendarShared').append('<div title="'+ '_[[Search agendas]]' +'" class="shared-calendar active"> <div>').find('.shared-calendar.active').html(html).dialog({
            resizable: false,
            modal:true,
            autoOpen: false,
            width:620,
            position: 'center',
            close: function(event, ui) {
                //Implementações de cancelar :D
                currentCalendars = {};
                $('.signed-calendars').find('.calendar-shared-search input').val('').Watermark('_[[Search agendas]]' + '...');
            }
        });
    } else {
        UI.dialogs.sharedCalendar.html(html);
    }

    UI.dialogs.sharedCalendar.find('.button').button()
        .filter('.cancel').click(function(evt){
            DataLayer.rollback('calendarSignature');
            UI.dialogs.sharedCalendar.dialog("close");
        }).end()
        .filter('.save').click(function(evt){
            $.each(UI.dialogs.sharedCalendar.find('.user-list li.new'), function( i , element ){
                var idPermission = $(element).find('input[name="idpermission[]"]').val();
                var calendarId = $(element).find('input[name="calendar[]"]').val();
                var type = parseInt($(element).find('input[name="type[]"]').val());
                DataLayer.put('calendarSignature', DataLayer.merge({
                    calendar: calendarId,
                    isOwner: 0,
                    user: User.me.id,
                    fontColor: '120d0d',
                    backgroundColor: (!!type ? 'fbec88' : '8c8c8c'),
                    borderColor: (!!type ? 'fad24e' : '120d0d')
                }, !!idPermission ? {
                    id: idPermission
                } : {} ));
            });

            DataLayer.commit( false, false, function( received ){
                delete Calendar.currentViewKey;
                Calendar.load();
                refresh_calendars();
            });

            UI.dialogs.sharedCalendar.dialog("close");

        });

    UI.dialogs.sharedCalendar.find('.add-user-search .ui-icon-search').click(function(event) {
        UI.dialogs.sharedCalendar.find('.add-user-search input').keydown();
    });

    UI.dialogs.sharedCalendar.find('.add-user-search input').keydown(function(event) {
        if(event.keyCode == '13' || typeof(event.keyCode) == 'undefined') {
            /*
            * @var calendarIds
            * Comment: This new variable has data filter result -> ['OR', ['i*','name',$(this).val()], ["i*", "description", $(this).val()]]
            * */
            var calendarIds = [];

            var findCalendars = DataLayer.get('calendar', {filter: ['OR', ['i*', 'name', $(this).val()], ['i*', 'description', $(this).val()] ] });

            for( var i in findCalendars ) {
                if (findCalendars[i]['id'])
                    calendarIds.push( findCalendars[i]['id'] );
            }

            var result = DataLayer.get('calendarToPermission',  {filter: ['AND', ['=','user',User.me.id], ['IN', 'calendar', calendarIds] ], criteria: {deepness: 2} });

            var resultPublic = DataLayer.get('calendarToPermission', {
                  filter: ['AND', ['=','type',1], ['IN', 'calendar', calendarIds], ['!IN','calendar', Calendar.calendarIds]],
                criteria: {
                    deepness: 2
                }
            }, true);

            /**
             * TODO: trocar por template
             */
            UI.dialogs.sharedCalendar.find('ul.search-result-list').empty().css('overflow', 'hidden');
            if (!result && !resultPublic) {
                UI.dialogs.sharedCalendar.find('ul.search-result-list').append('<li><label class="empty">' + '_[[No results found.]]' + '</label></li>');
            }

            if(resultPublic){
                var notConflict = [];
                var conflit = false;
                for(var i = 0; i < resultPublic.length; i++){
                    for(var j = 0; j < result.length; j++){
                        if(resultPublic[i].id == result[j].calendar.id)
                            conflit = true;
                    }
                    if(!conflit){
                        notConflict.push(resultPublic[i]);
                        conflit = false;
                    }
                }
            }
            resultPublic = notConflict;

            var resultNormalize = [];
            for(i=0; i<result.length; i++){
                resultNormalize.push({
                    id: result[i].calendar.id,
                    name:result[i].calendar.name,
                    mail: result[i].calendar.description,
                    owner: result[i].owner,
                    type: 0
                })
                resultNormalize[(resultNormalize.length - 1)].enabled = currentCalendars[result[i].id] ? false : true;
            }
            if(resultPublic)
                for(i=0; i<resultPublic.length; i++){
                    resultNormalize.push({
                        id: resultPublic[i].calendar.id,
                        name:resultPublic[i].calendar.name,
                        mail: resultPublic[i].calendar.description,
                        owner: resultPublic[i].owner,
                        type: 1
                    })
                    resultNormalize[(resultNormalize.length - 1)].enabled = currentCalendars[resultPublic[i].id] ? false : true;
                }

            UI.dialogs.sharedCalendar.find('ul.search-result-list').append(DataLayer.render( 'templates/calendar_search_itemlist.ejs', resultNormalize));

            UI.dialogs.sharedCalendar.find('ul.search-result-list li').click(function(event, ui){
                if ($(event.target).is('input')) {
                    old_item = $(event.target).parents('li');
                    var id = old_item.find('.id').html();

                    for(var i = 0; i<resultNormalize.length; i++){
                        if(resultNormalize[i].id == id)
                            currentCalendars[id] = {
                                id: id,
                                name: resultNormalize[i].name + ' ( '+resultNormalize[i].owner.uid +' )',
                                description: resultNormalize[i].description,
                                type: resultNormalize[i].type,
                                isCalendar: true
                            };
                    }

                    UI.dialogs.sharedCalendar.find('dd.calendar-list ul.user-list')
                        .append(DataLayer.render('templates/user_shared_add_itemlist.ejs', [currentCalendars[id]]))
                        .scrollTo('max');

                    $('li.not-user').remove();
                    callbackSharedCalendarAdd();
                    old_item.remove();
                }
            });
            event.preventDefault();
        }
    });

    var callbackSharedCalendarAdd = function(event){

        UI.dialogs.sharedCalendar.find('.button').filter(".close.new").button({
            icons: {
                primary: "ui-icon-close"
            },
            text: false
        }).click(function () {
            var id = $(this).parents('li').find('input[name="idPermission"]').val();
            currentCalendars[$(this).parents().find('input[name="calendar[]"]').val()] = false;
            $(this).parents('li').remove();
            if(!!id)
                DataLayer.remove('calendarSignature', id);
        })
            .addClass('tiny disable ui-button-disabled ui-state-disabled')
            .removeClass('new').end();

        UI.dialogs.sharedCalendar.find('.user-list li').hover(
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

    //Carrega os dados já cadastrados
    for (var i = 0; i < Calendar.signatures.length; i++)
        if(Calendar.signatures[i].isOwner == "0"){

            /*
             * Verificar se o Owner é um objeto caso ao contrário é realizado a busca e adicionado ao mesmo o uid
             * */
            if (Calendar.signatures[i].permission.owner != "object"){

                var result = DataLayer.get('calendarToPermission', {
                    filter: ['AND', ['=','id', Calendar.signatures[i].permission.id]]  ,
                    criteria: {
                        deepness: 2
                    }
                }, true);

                Calendar.signatures[i].permission.owner = {};
                Calendar.signatures[i].permission.owner['uid'] = result[0].owner.uid;

            }

            var dataCurrent = Calendar.signatures[i].calendar;
            currentCalendars[Calendar.signatures[i].permission.id] = {
                id: dataCurrent.id,
                idPermission:Calendar.signatures[i].id ,
                name: dataCurrent.name + ' ( ' + Calendar.signatures[i].permission.owner.uid + ' )',
                description: dataCurrent.description,
                type: Calendar.signatures[i].permission.type,
                isCalendar: true,
                current: true
            };

            UI.dialogs.sharedCalendar.find('dd.calendar-list ul.user-list')
                .append(DataLayer.render('templates/user_shared_add_itemlist.ejs', [currentCalendars[Calendar.signatures[i].permission.id]]))
                .scrollTo('max');

            $('li.not-user').remove();
            callbackSharedCalendarAdd();
        }

    UI.dialogs.sharedCalendar.dialog('open');
}

function cancel_signature(signatureId){
    $.Zebra_Dialog('_[[Confirms the removal of this signature?]]', {
        'type':     'question',
        'overlay_opacity': '0.5',
        'buttons':  ['_[[No]]', '_[[Yes]]'],
        'onClose':  function(clicked) {
            if(clicked == '_[[Yes]]'){
                DataLayer.remove('calendarSignature', ''+signatureId);
                DataLayer.commit( false, false, function( received ){
                    delete Calendar.currentViewKey;
                    Calendar.load();
                    refresh_calendars();
                });
            }
        }
    });
}
