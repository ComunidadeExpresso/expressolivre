countFiles = 0;

function Element(id){
   return document.getElementById( id );
}

function validate_fields(type){

	document.forms[0].uid.value = document.forms[0].uid.value.toLowerCase();
	document.forms[0].old_uid.value = document.forms[0].old_uid.value.toLowerCase();

	if (document.forms[0].uid.value == ''){
		alert(get_lang('login field is empty') + '.');
		return;
	}

	if (document.forms[0].cn.value == ''){
		alert(get_lang('name field is empty') + '.');
		return;
	}
	
	if (document.forms[0].restrictionsOnEmailLists.value == 'true')
	{
		uid_tmp = document.forms[0].uid.value.split("-");
		if ((uid_tmp.length < 3) || (uid_tmp[0] != 'lista')){
			alert(
				get_lang('login field is incomplete') + '.\n' +
				get_lang('the login field must be formed like') + ':\n' +
				get_lang('list') + '-' + get_lang('organization') + '-' + get_lang('listname') + '.\n' +
				get_lang('eg') + ': ' + 'lista-celepar-rh.');
			return;
		}
	}
		
	if (document.forms[0].uid.value.split(" ").length > 1){
		alert(get_lang('LOGIN field contains characters not allowed') + '.');
		document.forms[0].uid.focus();
		return;
	}
	
	if (document.forms[0].mail.value == ''){
		alert(get_lang('EMAIL field is empty') + '.');
		document.forms[0].mail.focus();
		return;
	}
	var reEmail = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if(!reEmail.test(document.forms[0].mail.value)){
		alert(get_lang('Email field is not valid') + '.');
		return false;
	}
	
	select_userInMaillist = document.getElementById('ea_select_usersInMaillist');
	if (select_userInMaillist.options.length == 0){
		alert(get_lang('Any user is in the list') + '.');
		return;
	}
	
	var handler_validate_fields = function(data)
	{
		if(!data.status){
			alert(data.msg);
        }else if(type == 'create_maillist'){
            $.ajax({
               type: "POST",
               url: "expressoAdmin1_2/controller.php?action=$this.maillist.create",
               data: new FormData(document.forms[0]),
               mimeType:"multipart/form-data",
               contentType: false,
               cache: false,
               processData:false,
               success: function(data, textStatus, jqXHR)
               {
                 alert('Sua lista foi criada com sucesso!');
                 location.href="./index.php?menuaction=expressoAdmin1_2.uimaillists.list_maillists";
               },
               error: function(jqXHR, textStatus, errorThrown)
               {
                 alert('Sua lista não pode ser criada:\n'+errorThrown.getStatusMessage());
               }
            });
        }else if(type == 'edit_maillist'){
            $.ajax({
                type: "POST",
                url: "expressoAdmin1_2/controller.php?action=$this.maillist.save",
                data: new FormData(document.forms[0]),
                mimeType:"multipart/form-data",
                contentType: false,
                cache: false,
                processData:false,
                success: function(data, textStatus, jqXHR)
                {
                   alert('Sua lista foi modificada com sucesso!');
                   location.href="./index.php?menuaction=expressoAdmin1_2.uimaillists.list_maillists";
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                   alert('Sua lista não pode ser modificada:\n'+errorThrown.getStatusMessage());
                }
            });
        }
	}
	// Needed select all options from select
    for(var i=0; i<select_userInMaillist.options.length; i++)
	{
		// No IE, não seleciona o separador do select
		if (select_userInMaillist.options[i].value != -1)
			select_userInMaillist.options[i].selected = true;
		else
			select_userInMaillist.options[i].selected = false;
	}

	// O UID da lista foi alterado ou é uma nova lista.
	if ((document.forms[0].old_uid.value != document.forms[0].uid.value) || (type == 'create_maillist')){
		cExecute('$this.maillist.validate_fields&uid='+document.forms[0].uid.value+'&mail='+document.forms[0].mail.value, handler_validate_fields);
	}
	else if (type == 'edit_maillist')
	{
        $.ajax({
            type: "POST",
            url: "expressoAdmin1_2/controller.php?action=$this.maillist.save",
            data: new FormData(document.forms[0]),
            mimeType:"multipart/form-data",
            contentType: false,
            cache: false,
            processData:false,
            success: function(data, textStatus, jqXHR)
            {
                alert('Sua lista foi modificada com sucesso!');
                location.href="./index.php?menuaction=expressoAdmin1_2.uimaillists.list_maillists";
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                alert('Sua lista não pode ser modificada:\n'+errorThrown.getStatusMessage());
            }
        });
	}
}

// HANDLER CREATE
// É necessário 2 funcões de retorno por causa do cExecuteForm.
function handler_create(data)
{
	return_handler_create(data);
}
function return_handler_create(data)
{
	if (!data.status)
		alert(data.msg);
	else{
		alert(get_lang('Email list successful created') + '.');
		location.href="./index.php?menuaction=expressoAdmin1_2.uimaillists.list_maillists";
	}
	return;
}

// HANDLER SAVE
// É necessário 2 funcões de retorno por causa do cExecuteForm.
function handler_save(data)
{
	return_handler_save(data);
}
function return_handler_save(data)
{
	if (!data.status)
		alert(data.msg);
	else{
		alert(get_lang('Email list successful saved') + '.');
		location.href="./index.php?menuaction=expressoAdmin1_2.uimaillists.list_maillists";
	}
	return;
}

function save_scl()
{
	select_users_SCL_Maillist = document.getElementById('ea_select_users_SCL_Maillist');
	// Needed select all options from select
	for(var i=0; i<select_users_SCL_Maillist.options.length; i++)
		select_users_SCL_Maillist.options[i].selected = true;

	cExecuteForm ("$this.maillist.save_scl", document.forms[0], handler_save_scl);
}
function handler_save_scl(data)
{
	return_handler_save_scl(data);
}

function return_handler_save_scl(data)
{
	if (!data.status)
		alert(data.msg);
	else
		alert(get_lang('SCL successful saved') + '.');
	location.href="./index.php?menuaction=expressoAdmin1_2.uimaillists.list_maillists";
	return;
}

function sinc_combos_org(context, recursive)
{
	combo_org_maillists = document.getElementById('ea_combo_org_maillists');

	for (i=0; i<combo_org_maillists.length; i++)
	{
		if (combo_org_maillists.options[i].value == context)
		{
			combo_org_maillists.options[i].selected = true;
			get_available_users(context, recursive);
			break;
		}
	}
}

function get_available_users(context, recursive)
{
// 	var sentence = Element('ea_input_searchUser').value;
// 
// 	var url = '$this.ldap_functions.get_available_users_and_maillist&context='
// 		    + Element('ea_combo_org_maillists').value
// 		    + '&sentence=' + sentence
// 		    + '&denied_uidnumber=' + document.forms[0].uidnumber.value;
// 
// 	var fillHandler = function( fill ){
// 
// 	Element('ea_select_available_users').innerHTML = fill;
// 
// 	return( fill !== "" );
//     }
// 
// 	userFinder( sentence, fillHandler, url, 'ea_span_searching' );
			
	var url = '$this.ldap_functions.get_available_users_and_maillist&denied_uidnumber=' + document.forms[0].uidnumber.value;
	
	optionFind( 'ea_input_searchUser', 'ea_select_available_users', url,
		    'ea_combo_org_maillists', 'ea_span_searching' );
}

function add_user2maillist()
{
	select_available_users = document.getElementById('ea_select_available_users');
	select_usersInMaillist = document.getElementById('ea_select_usersInMaillist');

	var count_available_users = select_available_users.length;
	var count_usersInMailList = select_usersInMaillist.options.length;
	var new_options = '';

	for (i = 0 ; i < count_available_users ; i++)
	{
		if (select_available_users.options[i].selected)
		{
			if(document.all)
			{
				if ( (select_usersInMaillist.innerHTML.indexOf('value='+select_available_users.options[i].value)) == '-1' )
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
				if ( (select_usersInMaillist.innerHTML.indexOf('value="'+select_available_users.options[i].value+'"')) == '-1' )
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
		select_usersInMaillist.innerHTML = '&nbsp;' + new_options + select_usersInMaillist.innerHTML;
		select_usersInMaillist.outerHTML = select_usersInMaillist.outerHTML;
		document.getElementById('ea_input_searchUser').value = "";
	}
}

function remove_user2maillist()
{
	select_usersInMaillist = document.getElementById('ea_select_usersInMaillist');
	
	for(var i = 0;i < select_usersInMaillist.options.length; i++)
		if(select_usersInMaillist.options[i].selected)
			select_usersInMaillist.options[i--] = null;
}

function add_user2scl_maillist()
{
	select_available_users = document.getElementById('ea_select_available_users');
	select_usersInMaillist = document.getElementById('ea_select_users_SCL_Maillist');

	var count_available_users = select_available_users.length;
	var count_usersInMailList = select_usersInMaillist.options.length;
	var new_options = '';

	for (i = 0 ; i < count_available_users ; i++)
	{
		if (select_available_users.options[i].selected)
		{
			if(document.all)
			{
				if ( (select_usersInMaillist.innerHTML.indexOf('value='+select_available_users.options[i].value)) == '-1' )
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
				if ( (select_usersInMaillist.innerHTML.indexOf('value="'+select_available_users.options[i].value+'"')) == '-1' )
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
		usersOptionsHtml = select_usersInMaillist.innerHTML;
		usersOptionsHtml = (($.browser.msie && $.browser.version < 9) ? '#' : '&nbsp;') + new_options + usersOptionsHtml;
		$(select_usersInMaillist).html(usersOptionsHtml);
	}
}

function remove_user2scl_maillist()
{
	select_usersInMaillist = document.getElementById('ea_select_users_SCL_Maillist');
	
	for(var i = 0;i < select_usersInMaillist.options.length; i++)
		if(select_usersInMaillist.options[i].selected)
			select_usersInMaillist.options[i--] = null;
}


// Variaveis Locais 
if (document.getElementById('ea_select_available_users'))
{
	var select_available_users  = document.getElementById('ea_select_available_users');
	var select_available_users_clone = select_available_users.cloneNode(true);
}
else
{
	var select_available_users  = '';
	var select_available_users_clone = '';
}
var finderTimeout = '';

// Funcoes
function optionFinderTimeout(obj, event)
{
    if( event && event.keyCode !== 13 )
	return;
	
	optionFinder( obj.id );
}
function optionFinder(id) {
	get_available_users();
}			

function delete_maillist(uid, uidnumber)
{
	if (confirm(get_lang('Do you really want delete the email list') + ' ' + uid + " ??"))
	
	{
		var handler_delete_maillist = function(data)
		{
			if (!data.status)
				alert(data.msg);
			else
				alert(get_lang('Email list successful deleted') + '.');
			
			location.href="./index.php?menuaction=expressoAdmin1_2.uimaillists.list_maillists";
			return;
		}
		cExecute ('$this.maillist.delete&uidnumber='+uidnumber, handler_delete_maillist);
	}
}

function search_organization(key, element)
{
	var organizations = document.getElementById(element);
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

function emailSuggestion_maillist()
{
	var defaultDomain = document.forms[0].defaultDomain.value;
	var base_dn = "." + dn2ufn(document.forms[0].ldap_context.value);
	var selected_context = dn2ufn(document.forms[0].context.value.toLowerCase());

	var uid = document.getElementById("ea_maillist_uid");
	var mail= document.getElementById("ea_maillist_mail");
	
	var raw_selected_context = selected_context.replace(base_dn, "");
	
	var array_org_name = raw_selected_context.split('.');
	var org_name = array_org_name[array_org_name.length-1];
	
	if (mail.value == "")
		mail.value = uid.value + "@" + org_name + "." + defaultDomain;
}

function dn2ufn(dn)
{
	var ufn = '';
	var array_dn = dn.split(",");
	for (x in array_dn)
	{
		var tmp = array_dn[x].split("=");
		ufn += tmp[1] + '.';
	}
	return ufn.substring(0,(ufn.length-1));
}

function LTrim(value) 
{ 
     var w_space = String.fromCharCode(32); 
     var strTemp = ""; 
     var iTemp = 0; 

     var v_length = value ? value.length : 0; 
     if(v_length < 1) 
             return ""; 

     while(iTemp < v_length){ 
             if(value && value.charAt(iTemp) != w_space){ 
                     strTemp = value.substring(iTemp,v_length); 
                     break; 
             } 
             iTemp++; 
     } 
     return strTemp; 
} 

function validateEmail() 
{ 

     externalEmail = document.getElementById('ea_input_externalUser'); 

     if( externalEmail.value ) 
     { 
             //var element = arguments[0]; 
             var validate = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/; 

             if(this.LTrim(externalEmail.value) != "" && externalEmail.value != "") 
             { 
                     if(!validate.test(externalEmail.value)) 
                     { 
                             alert(get_lang('Email address is not valid') + '.'); 
                             externalEmail.focus(); 
                             return false; 
                     }else { 
                             this.add_externalUser2maillist(externalEmail.value); 
                     } 
             } 

     } 
} 

function add_externalUser2maillist(mailAddress) 
{ 
     input_externalUsers = mailAddress.toLowerCase(); //document.getElementById('ea_input_externalUser').value; 
     select_usersInMaillist = document.getElementById('ea_select_usersInMaillist'); 

     var count_externalUsers = input_externalUsers.length; 
     var count_usersInMaillist = select_usersInMaillist.options.length; 
     var new_options = ''; 

     var teste = ''; //Variavel que ira receber mensagem de alerta ao usuario; 
     var alerta = new Boolean(0); //Variavel que sera usada para verificar se o alerta ao usuario sera exibido ou nao; 

     //Laco abaixo compara se o valor escolhido em select_available_users ja existe em select_usersInMaillist 
     //se existir, adiciona o valor em teste e muda a variavel alerta para true; teste sera exibido em tela 
     //apenas de alerta  true; ver if no fim da funcao; 
     for(j = 0; j < count_usersInMaillist; j++) 
     { 
             var tmp = select_usersInMaillist.options[j].text 

             if(tmp.match(input_externalUsers)) 
             { 
                     teste = get_lang("User already belongs to the list") + "\n" + input_externalUsers + "\n"; 
                     alerta = new Boolean(1); 
             } 
     } 

     if(alerta != true) 
     { 
             if(document.all) 
             { 
                     if ( (select_usersInMaillist.innerHTML.indexOf('value='+input_externalUsers)) == '-1' ) 
                     { 
                             new_options +=  "<option value=" 
                                                     + input_externalUsers 
                                                     + ">" 
                                                     + input_externalUsers 
                                                     + "</option>"; 
                     } 
             } 
             else 
             { 
                     if ( (select_usersInMaillist.innerHTML.indexOf('value="'+input_externalUsers+'"')) == '-1' ) 
                     { 
                             new_options +=  "<option value=" 
                                                     + input_externalUsers 
                                                     + ">" 
                                                     + input_externalUsers 
                                                     + "</option>"; 
                     } 
             } 
     } 

     if(alerta == true) 
     { 
             alert(teste); 
     } 


     if (new_options != '') 
     { 
             select_usersInMaillist.innerHTML = '#' + new_options + select_usersInMaillist.innerHTML; 
             select_usersInMaillist.outerHTML = select_usersInMaillist.outerHTML; 
     } 

     document.getElementById('ea_input_externalUser').value = ''; 
} 