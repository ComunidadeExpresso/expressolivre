countFiles = 1;
isValidCallback = false;

function create_shared_accounts()
{
    // 	select_owners = Element('ea_select_owners');
    hidden_owners_acl = Element('owners_acls');
    hidden_owners_calendar_acl = Element('owners_calendar_acls');

    select_owners = Element('ea_select_owners');
    for(var i = 0;i < select_owners.options.length; i++)
        select_owners.options[i].selected = true;

    hidden_owners_acl.value =  admin_connector.serialize(sharemailbox.ownersAcl);
    hidden_owners_calendar_acl.value =  admin_connector.serialize(sharemailbox.ownersCalendarAcl);
    cExecuteForm ("$this.shared_accounts.create", document.forms['shared_accounts_form'], handler_create_shared_accounts);
    hidden_owners_acl.value = "";
    isValidCallback = true;
}

function handler_create_shared_accounts(data_return)
{
    handler_create_shared_accounts2(data_return);
    return;
}

function handler_create_shared_accounts2(data_return)
{
    if (data_return && !data_return.status)
    {
        write_msg(data_return.msg, 'error');
    }
    else
    {
        if(sharemailbox.ownersExpressoCalendarAcl && isValidCallback){
            isValidCallback = false;
            calback();
        }

        close_lightbox();
        write_msg(get_lang('Shared account successful created') + '.', 'normal');
    }
    return;
}

function empty_inbox(uid)
{
    var action = get_lang('Cleanned user mailbox');
    var handler_write_log = function(){}

    var handler_empty_inbox = function(data)
    {
        if (!data.status)
            alert(data.msg);
        else{
            cExecute ('$this.user.write_log_from_ajax&_action='+action+'&userinfo='+uid, handler_write_log);
            alert(get_lang('Emptied') +' '+ data.inbox_size + ' ' + get_lang('MB from user inbox'));
            document.getElementById('mailquota_used').value = data.mailquota_used;
        }
    }
    cExecute ('$this.shared_accounts.empty_inbox&uid='+Element('anchor').value, handler_empty_inbox);
}
function set_onload()
{

    if(sharemailbox.ownersAcl)
    {
        delete sharemailbox.ownersAcl;
        sharemailbox.ownersAcl = new Array();
    }

    if(sharemailbox.ownersCalendarAcl)
    {
        delete sharemailbox.ownersAcl;
        sharemailbox.ownersAcl = new Array();
    }

    get_associated_domain(Element('ea_combo_org').value);
}

function search_organization(key, element)
{
    var organizations = Element(element);
    var RegExp_org = new RegExp("\\b"+key, "i");

    for(i = 0; i < organizations.length; i++)
    {
        if (RegExp_org.test(organizations[i].text))
        {
            organizations[i].selected = true;
            return;
        }
    }
}

function sinc_combos_org(context)
{
    combo_org_available_users = Element('ea_combo_org_available_users');
    context = context.toLowerCase();
    for (i=0; i<combo_org_available_users.length; i++)
    {
        if (combo_org_available_users.options[i].value.toLowerCase() == context)
        {
            combo_org_available_users.options[i].selected = true;
            //			get_available_users(context);
            break;
        }
    }
}

function optionFinderTimeout(obj, event)
{
    if( event.keyCode === 13 )
    {
        limit = 0;
        optionFinder(obj.id);
    }
}
function optionFinder(id) {

    var sentence = Element(id).value;

    var url = '$this.ldap_functions.get_available_users2&context=' +
        Element('ea_combo_org_available_users').value +
        ( sentence ? '&sentence=' + sentence: '' );

    userFinder( sentence, 'ea_select_available_users', url, 'ea_span_searching' );
}

function add_user()
{
    select_available_users = Element('ea_select_available_users');
    select_owners = Element('ea_select_owners');

    var count_available_users = select_available_users.length;
    var new_options = '';

    for (i = 0 ; i < count_available_users ; i++)
    {
        if (select_available_users.options[i].selected)
        {
            if(document.all)
            {
                if ( (select_owners.innerHTML.indexOf('value='+select_available_users.options[i].value)) == '-1' )
                {
                    new_options +=  "<option value="
                        + select_available_users.options[i].value
                        + ">"
                        + select_available_users.options[i].text
                        + "</options>";
                }
            }
            else
            {
                if ( (select_owners.innerHTML.indexOf('value="'+select_available_users.options[i].value+'"')) == '-1' )
                {
                    new_options +=  "<option value="
                        + select_available_users.options[i].value
                        + ">"
                        + select_available_users.options[i].text
                        + "</options>";
                }
            }
        }
    }

    if (new_options != '')
    {
        select_owners.innerHTML = "&nbsp;" + new_options + select_owners.innerHTML;
        select_owners.outerHTML = select_owners.outerHTML;
        Element('em_input_readAcl').checked = false;
        Element('em_input_deleteAcl').checked = false;
        Element('em_input_writeAcl').checked = false;
        Element('em_input_sendAcl').checked = false;
        Element('em_input_folderAcl').checked = false;

        Element('em_input_readCalendar').checked = false;
        Element('em_input_writeCalendar').checked = false;
        Element('em_input_editCalendar').checked = false;
        Element('em_input_deleteCalendar').checked = false;
        Element('em_input_restrictCalendar').checked = false;

        Element('em_input_readExpressoCalendar').checked = false;
        Element('em_input_writeExpressoCalendar').checked = false;
        Element('em_input_freebusyExpressoCalendar').checked = false;
        Element('em_input_deleteExpressoCalendar').checked = false;


        Element('em_input_sendAcl').disabled = true;
        select_owners = Element('ea_select_owners');
        select_owners.options[0].selected = true;
    }
}

function remove_user()
{
    select_owners = Element('ea_select_owners');

    var user = '';
    for(var i = 0;i < select_owners.options.length; i++){
        if(select_owners.options[i].selected){
            user = select_owners.options[i].value;
            delete sharemailbox.ownersAcl[user];
            sharemailbox.ownersCalendarAcl[user] = '';


            if(sharemailbox.ownersExpressoCalendarAcl && sharemailbox.ownersExpressoCalendarAcl[user] != undefined ){

                delete sharemailbox.ownersExpressoCalendarAcl[user];
                //sharemailbox.ownersExpressoCalendarAcl.length--;
                DataLayer.dispatchPath = "prototype/";
                var sharedUser = DataLayer.get('user', {
                    filter: ['=','mail',$('#mail').val()]
                });
                for(var j = 0; j < sharedUser.length; j++)
                    if(sharedUser[j].phpgwAccountType == 's'){
                        sharedUser = sharedUser[j];
                        break;
                    }

                var signature = DataLayer.get('calendarSignature', {
                    filter: ['=','user', sharedUser.id]
                });

                if(!signature)
                    return;

                var usuario = DataLayer.get('user', {
                    filter: ['=','uid',user],
                    criteria: {
                        notExternal: true
                    }
                });
                var calendarPermission = DataLayer.get('calendarToPermission', {
                    filter: ['AND', ['=','calendar',signature[0].calendar], ['=','user',usuario[0].id] ]
                });

                var signatureUser = DataLayer.get('calendarSignature', {
                    filter: ['AND', ['=','calendar', signature[0].calendar], ['=','user', usuario[0].id ] ]
                });

                if(!!signatureUser && signatureUser[0].id)
                    DataLayer.remove('calendarSignature', signatureUser[0].id);

                if(!!calendarPermission && calendarPermission[0].id)
                    DataLayer.remove('calendarToPermission', calendarPermission[0].id);

            }

            select_owners.options[i] = null;
            //break;
        }
    }
    //Nova chamada a "Element" é Necessária devido a um bug do ie com select
    select_owners = Element('ea_select_owners');
    if(select_owners.options.length > 0 ){
        select_owners.options[0].selected = true;
        user = select_owners.options[0].value;
        sharemailbox.getaclfromuser(user);
    }

}

function get_shared_accounts_timeOut(input, event)
{
    Element('shared_accounts_content').innerHTML = '<table border="0" width="90%"><tr bgcolor="#d3dce3"><td width="30%">'+get_lang("full name")+'</td><td width="30%">'+get_lang("display name")+'</td><td width="30%">'+get_lang("mail")+'</td><td width="5%" align="center">'+get_lang("remove")+'</td></tr></table>';

    if (event.keyCode === 13)
    {
        get_shared_accounts( input );
    }
}

function get_shared_accounts(input, callback)
{
    var handler_get_shared_accounts = function(data)
    {
        if (data.status == 'true')
        {
            var table = '<table border="0" width="90%"><tr bgcolor="#d3dce3"><td width="30%">'+get_lang("full name")+'</td><td width="30%">'+get_lang("display name")+'</td><td width="30%">'+get_lang("mail")+'</td><td width="5%" align="center">'+get_lang("remove")+'</td></tr>'+data.trs+'</table>';
            Element('shared_accounts_content').innerHTML = table;
        }
        else
            write_msg(data.msg, 'error');
    }
    cExecute ('$this.ldap_functions.get_shared_accounts&input='+input, handler_get_shared_accounts);
}

function edit_shared_account(uid)
{
    var handle_edit_shared_account = function(data)
    {
        if (data.status == 'true')
        {
            modal('shared_accounts_modal','save');

            Element( 'lightboxCaption' ).innerHTML = get_lang( 'Edit Shared Accounts' );
            Element('shared_accounts_modal').value = data.mailquota;


            var combo_org = Element('ea_combo_org');
            var context_to_select = data.user_context.toLowerCase();
            for (i=0; i<combo_org.length; i++)
            {
                if (combo_org.options[i].value.toLowerCase() == context_to_select)
                {
                    combo_org.options[i].selected = true;
                    break;
                }
            }

            // anchor
            Element('anchor').value = "uid=" + uid + ',' + data.user_context;

            if (data.accountStatus != 'active')
                Element('accountStatus').checked = false;

            if (data.phpgwAccountVisible == '-1')
                Element('phpgwAccountVisible').checked = true;
            Element('uidnumber').value = data.uidnumber;
            Element('cn').value = data.cn;
            Element('mail').value = data.mail;
            Element('mailquota').value = data.mailquota;
            Element('mailquota_used').value = data.mailquota_used;
            Element('quota_used_field').style.display = 'inline';
            Element('desc').value = data.description;
            //Necessario, pois o IE6 tem um bug que não exibe as novas opções se o innerHTML estava vazio

            if(data.owners_options){
                Element('ea_select_owners').innerHTML = '&nbsp;' + data.owners_options;
                Element('ea_select_owners').outerHTML = Element('ea_select_owners').outerHTML;
            }
            Element('display_empty_inbox').style.display = data.display_empty_inbox;
            if( data.allow_edit_shared_account_acl == "0"){
                Element('bt_add_user').disabled = true;
                Element('bt_remove_user').disabled = true;
                Element('em_input_readAcl').disabled = true;
                Element('em_input_deleteAcl').disabled = true;
                Element('em_input_writeAcl').disabled = true;
                Element('em_input_sendAcl').disabled = true;
                Element('em_input_folderAcl').disabled = true;
            }

            if( data.mailalternateaddress )
                loadAppended( 'mailalternateaddress', data.mailalternateaddress );

            sinc_combos_org(data.user_context);
            sharemailbox.ownersAcl = new Array();
            sharemailbox.ownersCalendarAcl = new Array();
            if( data.owners && data.owners != "undefined" && data.owners_acl != "undefined" ){
                for (i=0; i<data.owners.length; i++){
                    sharemailbox.ownersAcl[ data.owners[i] ] = data.owners_acl[i];
                    sharemailbox.ownersCalendarAcl[ data.owners[i] ] = data.owners_calendar_acl[i];
                }
            }

            //new API compatibility
            DataLayer.dispatchPath = "prototype/";
            var signature = DataLayer.get('calendarSignature', {
                filter: ['=','user', data.uidnumber]
            });
            if(!!signature){
                sharemailbox.currentPemissions[data.uidnumber] = true;
                var calendarPermission = DataLayer.get('calendarToPermission:detail', {
                    filter: ['=','calendar',signature[0].calendar],
                    criteria:{
                        deepness: 2
                    }
                });
                if(calendarPermission){
                    current = {};
                    for(var i=0; i<data.owners.length; i++)
                        current[data.owners[i]] = true;

                    for (var i=0; i < calendarPermission.length; i++){
                        if (calendarPermission[i].user.uid ){
                            sharemailbox.ownersExpressoCalendarAcl[ calendarPermission[i].user.uid ] = calendarPermission[i].aclValues;
                            sharemailbox.currentPemissions[calendarPermission[i].user.uid] = calendarPermission[i].id;
                            // sharemailbox.ownersExpressoCalendarAcl.length = sharemailbox.currentPemissions.length = i;
                        }
                        if(calendarPermission[i].user.uid && !current[calendarPermission[i].user.uid ]){
                            Element('ea_select_owners').innerHTML = Element('ea_select_owners').innerHTML+'<option value='+ calendarPermission[i].user.uid +'>'+ calendarPermission[i].user.name +'</option>';
                            Element('ea_select_owners').outerHTML = Element('ea_select_owners').outerHTML;
                        }

                    }

                    delete sharemailbox.currentPemissions[undefined];
                }

            }


        }
        else
            write_msg(data.msg, 'error');
    }
    cExecute ('$this.shared_accounts.get_data&uid='+uid, handle_edit_shared_account);
}

function save_shared_accounts()
{
    if (is_ie){
        var i = 0;
        while (document.forms(i).name != "shared_accounts_form"){
            i++
        }
        form = document.forms(i);
    }
    else   form = document.forms["shared_accounts_form"];

    hidden_owners_calendar_acl = Element('owners_calendar_acls');
    hidden_owners_acl = Element('owners_acls');
    select_owners = Element('ea_select_owners');
    for(var i = 0;i < select_owners.options.length; i++){
        var user = select_owners.options[i].value;
        select_owners.options[i].value = user;
    }
    hidden_owners_acl.value =  admin_connector.serialize(sharemailbox.ownersAcl);
    hidden_owners_calendar_acl.value =  admin_connector.serialize(sharemailbox.ownersCalendarAcl);
    cExecuteForm ("$this.shared_accounts.save", form, handler_save_shared_accounts);
    get_shared_accounts(Element('ea_shared_account_search').value);

    if(sharemailbox.ownersExpressoCalendarAcl)
        calback();
}

function handler_save_shared_accounts(data_return)
{
    handler_save_shared_accounts2(data_return);
    return;
}
function handler_save_shared_accounts2(data_return)
{
    if(data_return){
        if (data_return.status){
            hidden_owners_acl.value = "";
            close_lightbox();
            write_msg(get_lang('Shared account successful saved') + '.', 'normal');
        }else
            write_msg(data_return.msg , 'error');
    }
    return;
}

function callbackDelete(sharedUser){

    if(!!sharedUser && $.isArray(sharedUser))
        for(var i = 0; i < sharedUser.length; i++)
            if(sharedUser[i].phpgwAccountType == 's'){
                sharedUser = sharedUser[i];
                break;
            }
    DataLayer.dispatchPath = "prototype/";
    var signature = DataLayer.get('calendarSignature', {
        filter: ['=','user', sharedUser.id]
    });

    signature = $.isArray(signature) ? signature[0] : signature;

    DataLayer.remove('calendarSignature', signature.id)
    DataLayer.commit();
}

function calback(){
    DataLayer.dispatchPath = "prototype/";
    var sharedUser = DataLayer.get('user', {
        filter: ['=','mail',$('#mail').val()]
    });

    if(!!sharedUser && $.isArray(sharedUser))
        for(var i = 0; i < sharedUser.length; i++)
            if(sharedUser[i].phpgwAccountType == 's'){
                sharedUser = sharedUser[i];
                break;
            }

    if(!!!sharemailbox.currentPemissions[sharedUser.id])
        DataLayer.put('calendarSignature', {
            user: sharedUser.id,
            calendar:  {
                timezone: 'America/Sao_Paulo',
                name: $('#cn').val(),
                location : $('#sharedAccountsLocation').val() + '/' + $('#cn').val(),
                description : $('#cn').val()
            },
            isOwner:  '1',
            fontColor:  '000000',
            backgroundColor: 'f1efac',
            borderColor: 'eddb21'
        });

    var returns = function(data){


        var calendar = '';

        if(data){
            for(var i in data)
                if(i.indexOf('calendar:') >= 0)
                    calendar = data[i].id;
        }else{
            calendar = DataLayer.get('calendarSignature', {
                filter: ['=','user', sharedUser.id]
            });
            calendar = calendar[0].calendar;
        }

        $.each(sharemailbox.ownersExpressoCalendarAcl, function(user, acl) {
            if (user != "undefined"){
                var usuario = DataLayer.get('user', {
                    filter: ['=','uid',user],
                    criteria: {
                        notExternal: true
                    }
                });

                if($.isArray(usuario))
                    usuario = usuario[0];

                DataLayer.put('calendarToPermission', DataLayer.merge({
                    user:  usuario.id,
                    type: '0',
                    acl: acl,
                    calendar: calendar
                }, !!sharemailbox.currentPemissions[usuario.uid] ? {
                    id: sharemailbox.currentPemissions[usuario.uid]
                } : {}));

                if(!!!sharemailbox.currentPemissions[usuario.uid])
                    DataLayer.put('calendarSignature', {
                        user: usuario.id,
                        calendar:  calendar,
                        isOwner:  '0',
                        fontColor:  '000000',
                        backgroundColor: 'f1efac',
                        borderColor: 'eddb21'
                    });
            }
        })
        DataLayer.commit();
    };
    if(!!sharemailbox.currentPemissions[sharedUser.id])
        returns(false);
    else
        DataLayer.commit(false, false, returns);
}


function delete_shared_accounts(uid, mail)
{
    if (!confirm(get_lang('Are you sure that you want to delete this shared account') + "?"))
        return;

    var user = {};
    DataLayer.dispatchPath = "prototype/";
    if(sharemailbox.ownersExpressoCalendarAcl)
        user =  DataLayer.get('user', {
            filter: ['=','mail',mail]
        });


    var handle_delete_shared_account = function(data_return)
    {
        if (!data_return.status)
        {
            write_msg(data_return.msg, 'error');
        }
        else
        {
            if(sharemailbox.ownersExpressoCalendarAcl){
                callbackDelete(user);
            }

            write_msg(get_lang('Shared account successful deleted') + '.', 'normal');
            get_shared_accounts(Element('ea_shared_account_search').value);
        }
        return;
    }



    cExecute ('$this.shared_accounts.delete&uid='+uid, handle_delete_shared_account);
}
function cShareMailbox()
{
    this.arrayWin = new Array();
    this.el;
    this.alert = false;
    this.ownersAcl = new Array();
    this.ownersCalendarAcl = new Array();
    this.ownersExpressoCalendarAcl = {};
    this.currentPemissions = {};
}

cShareMailbox.prototype.clear = function()
{
    sharemailbox.arrayWin = new Array();
    sharemailbox.ownersAcl = new Array();
    sharemailbox.ownersCalendarAcl = new Array();
    sharemailbox.ownersExpressoCalendarAcl = {};
    sharemailbox.currentPemissions = {};
}

cShareMailbox.prototype.get_available_users = function(context)
{
    var handler_get_available_users = function(data)
    {
        select_available_users = document.getElementById('em_select_available_users');

        //Limpa o select
        for(var i=0; i<select_available_users.options.length; i++)
        {
            select_available_users.options[i] = null;
            i--;
        }

        if ((data) && (data.length > 0))
        {
            // Necessario, pois o IE6 tem um bug que retira o primeiro options se o innerHTML estiver vazio.
            select_available_users.innerHTML = '#' + data;
            select_available_users.outerHTML = select_available_users.outerHTML;

            select_available_users.disabled = false;
            select_available_users_clone = document.getElementById('em_select_available_users').cloneNode(true);
            document.getElementById('em_input_searchUser').value = '';
        }
    }
    cExecute ("$this.ldap_functions.get_available_users2&context="+context, handler_get_available_users);
}

cShareMailbox.prototype.getaclfromuser = function(user)
{
    Element('em_input_readAcl').checked = false;
    Element('em_input_deleteAcl').checked = false;
    Element('em_input_writeAcl').checked = false;
    Element('em_input_sendAcl').checked = false;
    Element('em_input_folderAcl').checked = false;
    Element('em_input_readCalendar').checked = false;
    Element('em_input_writeCalendar').checked = false;
    Element('em_input_editCalendar').checked = false;
    Element('em_input_deleteCalendar').checked = false;
    Element('em_input_restrictCalendar').checked = false;

    Element('em_input_readExpressoCalendar').checked = false;
    Element('em_input_writeExpressoCalendar').checked = false;
    Element('em_input_freebusyExpressoCalendar').checked = false;
    Element('em_input_deleteExpressoCalendar').checked = false;

    Element('em_input_editCalendar').disabled = true;
    Element('em_input_deleteCalendar').disabled = true;
    Element('em_input_restrictCalendar').disabled = true;

    if (!this.ownersExpressoCalendarAcl[user] && !this.ownersCalendarAcl[user])
    {
        DataLayer.dispatchPath = "prototype/";
        var sharedUser = DataLayer.get('user', {
            filter: ['=','mail',$('#mail').val()]
        });

        if(sharedUser)
        {
            for(var j = 0; j < sharedUser.length; j++)
                if(sharedUser[j].phpgwAccountType == 's'){
                    sharedUser = sharedUser[j];
                    break;
                }

            var signature = DataLayer.get('calendarSignature', {
                filter: ['=','user', sharedUser.id]
            });

            if(!signature)
                return;

            var usuario = DataLayer.get('user', {
                filter: ['=','uid',user],
                criteria: {
                    notExternal: true
                }
            });

            var calendarPermission = DataLayer.get('calendarToPermission', {
                filter: ['AND', ['=','calendar',signature[0].calendar], ['=','user',usuario[0].id] ]
            });

            this.ownersExpressoCalendarAcl[user] = calendarPermission[0] ? calendarPermission[0].acl : "";
        }
        else
        {
            this.ownersExpressoCalendarAcl[user] = '';
        }

    }

    if(this.ownersExpressoCalendarAcl[user])
    {
        if (this.ownersExpressoCalendarAcl[user].indexOf('r') >= 0)
        {
            Element('em_input_readExpressoCalendar').checked = true;
            Element('em_input_writeExpressoCalendar').checked = false;
            Element('em_input_freebusyExpressoCalendar').checked = false;
            Element('em_input_deleteExpressoCalendar').checked = false;

        }
        if (this.ownersExpressoCalendarAcl[user].indexOf('w') >= 0)
        {
            Element('em_input_writeExpressoCalendar').checked = true;
        }
        if (this.ownersExpressoCalendarAcl[user].indexOf('d') >= 0)
        {
            Element('em_input_deleteExpressoCalendar').checked = true;
        }
        if (this.ownersExpressoCalendarAcl[user].indexOf('b') >= 0)
        {
            Element('em_input_freebusyExpressoCalendar').checked = true;
        }
    }


    if(this.ownersCalendarAcl[user])
    {
        if (this.ownersCalendarAcl[user].indexOf('1-',0) >= 0)
        {
            Element('em_input_readCalendar').checked = true;
            Element('em_input_editCalendar').disabled = false;
            Element('em_input_deleteCalendar').disabled = false;
            Element('em_input_restrictCalendar').disabled = false;

            Element('em_input_readExpressoCalendar').checked = true;
            Element('em_input_writeExpressoCalendar').checked = false;
            Element('em_input_freebusyExpressoCalendar').checked = false;
            Element('em_input_deleteExpressoCalendar').checked = false;

        }
        if (this.ownersCalendarAcl[user].indexOf('2-',0) >= 0)
        {
            Element('em_input_writeCalendar').checked = true;
            Element('em_input_writeExpressoCalendar').checked = true;
        }
        if (this.ownersCalendarAcl[user].indexOf('4-',0) >= 0)
        {
            Element('em_input_editCalendar').checked = true;
            Element('em_input_writeExpressoCalendar').checked = true;
        }
        if (this.ownersCalendarAcl[user].indexOf('8-',0) >= 0)
        {
            Element('em_input_deleteCalendar').checked = true;
            Element('em_input_deleteExpressoCalendar').checked = true;
        }
        if (this.ownersCalendarAcl[user].indexOf('16-',0) >= 0)
        {
            Element('em_input_restrictCalendar').checked = true;
        }
    }

    if(this.ownersAcl[user])
    {
        if ( (this.ownersAcl[user].indexOf('l',0) >= 0) &&
            (this.ownersAcl[user].indexOf('r',0) >= 0) &&
            (this.ownersAcl[user].indexOf('s',0) >= 0)
            )
        {
            Element('em_input_sendAcl').disabled = false;
            Element('em_input_readAcl').checked = true;
        }
        else
            Element('em_input_sendAcl').disabled = true;

        if ( (this.ownersAcl[user].indexOf('t',0) >= 0) &&
            (this.ownersAcl[user].indexOf('e',0) >= 0)
            )
        {
            Element('em_input_deleteAcl').checked = true;
        }
        if ( (this.ownersAcl[user].indexOf('w',0) >= 0) &&
            (this.ownersAcl[user].indexOf('i',0) >= 0)
            )
        {
            Element('em_input_writeAcl').checked = true;
        }
        if ((this.ownersAcl[user].indexOf('p',0) >= 0) &&
            (this.ownersAcl[user].indexOf('a',0) >= 0) )
        {
            Element('em_input_sendAcl').disabled = false;
            Element('em_input_sendAcl').checked = true;
        }

        if ( (this.ownersAcl[user].indexOf('k',0) >= 0) &&
            (this.ownersAcl[user].indexOf('x',0) >= 0)
            )
        {
            Element('em_input_folderAcl').checked = true;
        }

    }

    //$()
    var checkboxes = $(".shared-permissions input:checkbox");
    var check = $("#em_input_readAcl").attr("checked") == undefined ? false : true;
    if(check){
        checkboxes.removeAttr("disabled");
    }else{
        checkboxes.not(".shared-required").attr("disabled", "disabled");
        checkboxes.removeAttr("checked");
    }

}

cShareMailbox.prototype.setaclfromuser = function()
{
    var acl		= '';
    var select 	= Element('ea_select_owners');

    if(select.selectedIndex == "-1"){
        alert("Selecione antes um usuario!");
        return false;
    }

    for(var k = 0; k < select.options.length; k ++ )
    {
        if(select.options[k].selected !== true ) continue;


        acl = '';
        var user = select.options[k].value;

        if (Element('em_input_readAcl').checked) {
            Element('em_input_sendAcl').disabled = false;
            acl = 'lrs';
        }
        else{
            Element('em_input_sendAcl').disabled = true;
            Element('em_input_sendAcl').checked = false;
        }

        if (Element('em_input_deleteAcl').checked)
            acl += 'tea';

        if (Element('em_input_writeAcl').checked)
            acl += 'wia';

        if (Element('em_input_sendAcl').checked)
            acl += 'pa';

        if (Element('em_input_folderAcl').checked)
            acl += 'kxa';



        this.ownersAcl[user] = acl;
    }
}

cShareMailbox.prototype.setCalendaraclfromuser = function()
{
    var acl		= '';
    var select 	= Element('ea_select_owners');

    sharemailbox.ownersExpressoCalendarAcl = false;

    if(select.selectedIndex == "-1"){
        alert("Selecione antes um usuario!");
        return false;
    }

    for(var k = 0; k < select.options.length; k ++ )
    {
        if(select.options[k].selected !== true ) continue;

        acl = '';
        var user = select.options[k].value;

        if (Element('em_input_readCalendar').checked)
        {

            acl += '1-';

            Element('em_input_editCalendar').disabled = false;
            Element('em_input_deleteCalendar').disabled = false;
            Element('em_input_restrictCalendar').disabled = false;

            if (Element('em_input_editCalendar').checked)
                acl += '4-';

            if (Element('em_input_deleteCalendar').checked )
                acl += '8-';

            if (Element('em_input_restrictCalendar').checked)
                acl += '16-';
        }
        else
        {
            Element('em_input_editCalendar').disabled = true;
            Element('em_input_deleteCalendar').disabled = true;
            Element('em_input_restrictCalendar').disabled = true;
            Element('em_input_editCalendar').checked = false;
            Element('em_input_deleteCalendar').checked = false;
            Element('em_input_restrictCalendar').checked = false;

        }

        if (Element('em_input_writeCalendar').checked || Element('em_input_writeExpressoCalendar').checked)
            acl += '2-';


        this.ownersCalendarAcl[user] = acl;
    }
}

cShareMailbox.prototype.setExpressoCalendaraclfromuser = function()
{
    var acl		= '';
    var select 	= Element('ea_select_owners');

    sharemailbox.ownersCalendarAcl = false;

    if(select.selectedIndex == "-1"){
        alert("Selecione antes um usuario!");
        return false;
    }

    for(var k = 0; k < select.options.length; k ++ )
    {
        if(select.options[k].selected !== true ) continue;

        acl = '';
        var user = select.options[k].value;

        if(Element('em_input_freebusyExpressoCalendar').checked)
        {
            Element('em_input_writeExpressoCalendar').disabled = true;
            Element('em_input_deleteExpressoCalendar').disabled = true;
            Element('em_input_readExpressoCalendar').disabled = true;
            Element('em_input_writeExpressoCalendar').checked = false;
            Element('em_input_deleteExpressoCalendar').checked = false;
            Element('em_input_readExpressoCalendar').checked = false;


            acl += 'b'
        }
        else
        {
            Element('em_input_readExpressoCalendar').disabled = false;
            if (Element('em_input_readExpressoCalendar').checked)
            {
                acl += 'r';

                Element('em_input_writeExpressoCalendar').disabled = false;
                Element('em_input_deleteExpressoCalendar').disabled = false;
                //Element('em_input_freebusyExpressoCalendar').disabled = false;

                if (Element('em_input_writeExpressoCalendar').checked)
                    acl += 'w';

                if (Element('em_input_deleteExpressoCalendar').checked)
                    acl += 'd';

            }
            else
            {
                Element('em_input_writeExpressoCalendar').disabled = true;
                Element('em_input_deleteExpressoCalendar').disabled = true;
                //Element('em_input_freebusyExpressoCalendar').disabled = true;
                Element('em_input_writeExpressoCalendar').checked = false;
                Element('em_input_deleteExpressoCalendar').checked = false;
                // Element('em_input_freebusyExpressoCalendar').checked = false;
            }

        }

        this.ownersExpressoCalendarAcl[user] = acl;
    }
}

cShareMailbox.prototype.add_user = function()
{
    var select_available_users = document.getElementById('em_select_available_users');
    var select_users = document.getElementById('ea_select_owners');

    var count_available_users = select_available_users.length;
    var count_users = select_users.options.length;
    var new_options = '';

    for (i = 0 ; i < count_available_users ; i++)
    {
        if (select_available_users.options[i].selected)
        {
            if(document.all)
            {
                if ( (select_users.innerHTML.indexOf('value='+select_available_users.options[i].value)) == '-1' )
                {
                    new_options +=  '<option value='
                        + select_available_users.options[i].value
                        + '>'
                        + select_available_users.options[i].text
                        + '</option>';
                }
            }
            else
            {
                if ( (select_users.innerHTML.indexOf('value="'+select_available_users.options[i].value+'"')) == '-1' )
                {
                    new_options +=  '<option value='
                        + select_available_users.options[i].value
                        + '>'
                        + select_available_users.options[i].text
                        + '</option>';
                }
            }
        }
    }

    if (new_options != '')
    {
        select_users.innerHTML = '#' + new_options + select_users.innerHTML;
        select_users.outerHTML = select_users.outerHTML;

    }
}

cShareMailbox.prototype.remove_user = function()
{
    select_users = document.getElementById('ea_select_owners');

    for(var i = 0;i < select_users.options.length; i++)
        if(select_users.options[i].selected)
        {

            var user = select_users.options[i].value;
            this.ownersCalendarAcl[user] = '';
            select_users.options[i--] = null;

        }

    Element('em_input_readAcl').checked = false;
    Element('em_input_deleteAcl').checked = false;
    Element('em_input_writeAcl').checked = false;
    Element('em_input_sendAcl').checked = false;
    Element('em_input_folderAcl').checked = false;

    Element('em_input_readCalendar').checked = false;
    Element('em_input_writeCalendar').checked = false;
    Element('em_input_editCalendar').checked = false;
    Element('em_input_deleteCalendar').checked = false;
    Element('em_input_restrictCalendar').checked = false;

    Element('em_input_readExpressoCalendar').disabled = true;
    Element('em_input_writeExpressoCalendar').disabled = true;
    Element('em_input_deleteExpressoCalendar').disabled = true;
    Element('em_input_readExpressoCalendar').disabled = true;
}


/* Build the Object */
var sharemailbox;
sharemailbox = new cShareMailbox();
