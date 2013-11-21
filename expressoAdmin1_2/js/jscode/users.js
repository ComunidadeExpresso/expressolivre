countFiles = 1;

function Element( id )
{
    return document.getElementById( id );
}

function validate_fields(type)
{
	if (type == 'create_user')
	{
		//UID
		document.forms[0].uid.value = document.forms[0].uid.value.toLowerCase();
		
		if (document.forms[0].uid.value == ''){
			alert(get_lang('LOGIN field is empty') + '.');
			return;
		}
		else if (document.forms[0].uid.value.length < document.forms[0].minimumSizeLogin.value){
			alert(get_lang('LOGIN field must be bigger than') + ' ' + document.forms[0].minimumSizeLogin.value + ' ' + get_lang('characters') + '.');
			return;
		}
		
		// Verifica se o delimitador do Cyrus permite ponto (dot.) nas mailboxes;
		if (document.forms[0].imapDelimiter.value == '/')
			var reUid = /^([a-zA-Z0-9_\.\-])+$/;
		else
			var reUid = /^([a-zA-Z0-9_\-])+$/;
		if(!reUid.test(document.forms[0].uid.value)){
			alert(get_lang('LOGIN field contains characters not allowed') + '.');
			return;
		}
	
		//PASSWORD's
		if (document.forms[0].password1.value == ''){
			alert(get_lang('Password field is empty') + '.');
			return;
		}
		if (document.forms[0].password2.value == ''){
			alert(get_lang('re-password field is empty') + '.');
			return;
		}
	}

	if (document.forms[0].password1.value != document.forms[0].password2.value){
		alert(get_lang('password and re-password are different') + '.');
		return;
	}

	// Corporative Information
	if (document.forms[0].corporative_information_employeenumber.value != "")
	{
		var re_employeenumber = /^([0-9])+$/;
		
		if(!re_employeenumber.test(document.forms[0].corporative_information_employeenumber.value))
		{
			alert(get_lang('EmployeeNumber contains characters not allowed') + '. ' + get_lang('Only numbers are allowed') + '.');
			document.forms[0].corporative_information_employeenumber.focus();
			return;
		}
	}

	//MAIL
	document.forms[0].mail.value = document.forms[0].mail.value.toLowerCase();
	if (document.forms[0].mail.value == ''){
		alert(get_lang('Email field is empty') + '.');
		return;
	}
	var reEmail = /^([a-zA-Z0-9_\-])+(\.[a-zA-Z0-9_\-]+)*\@([a-zA-Z0-9_\-])+(\.[a-zA-Z0-9_\-]+)*$/;
	if(!reEmail.test(document.forms[0].mail.value)){
		alert(get_lang('Email field is not valid') + '.');
		return false;
	}
	
	//FIRSTNAME
	var reGivenname = /^[a-zA-Z0-9 \-\.]+$/;
	if(!reGivenname.test(document.forms[0].givenname.value)){
		alert(get_lang('First name field contains characters not allowed') + '.');
		return false;
	}
	else if (document.forms[0].givenname.value == ''){
		alert(get_lang('First name field is empty') + '.');
		return;
	}
	
	//LASTNAME
	var reSn = /^[a-zA-Z0-9 \-\.]+$/;
	if(!reSn.test(document.forms[0].sn.value)){
		alert(get_lang('Last name field contains characters not allowed') + '.');
		return false;
	}
	else if (document.forms[0].sn.value == ''){
		alert(get_lang('Last name field is empty') + '.');
		return;
	}
	
	//TELEPHONENUMBER
	if (document.forms[0].telephonenumber.value != '')
	{
		reg_tel = /\(\d{2}\)\d{4}-\d{4}$/;
		if (!reg_tel.exec(document.forms[0].telephonenumber.value))
		{
			alert(get_lang('Phone field is incorrect') + '.');
			return;
		}
	}
	
	//FORWAR ONLY
	if ((document.forms[0].deliverymode.checked) && (document.forms[0].mailforwardingaddress.value == '')){
		alert(get_lang('Forward email is empty') + '.');
		return;
	}
	
	// Email Quota
	if (document.forms[0].mailquota.value == ''){
		alert(get_lang('User without email quota') + '.');
		return;
	}
	
	//GROUPS
	if (document.getElementById('ea_select_user_groups').length < 1){
		alert(get_lang('User is not in any group') + '.');
		return;
	}

	//SAMBA
	if (document.getElementById('tabcontent6').style.display != 'none'){
		if ((document.forms[0].sambalogonscript.value == '') && (!document.forms[0].sambalogonscript.disabled)){
			alert(get_lang('Logon script is empty') + '.');
			return;
		}
		if ((document.forms[0].sambahomedirectory.value == '') && (!document.forms[0].sambahomedirectory.disabled)){
			alert(get_lang('Users home directory is empty') + '.');
			return;
		}
	}

	// Uid, Mail and CPF exist?
	var attrs_array = new Array();
	attrs_array['type'] = type;
	attrs_array['uid'] = document.forms[0].uid.value;
	attrs_array['mail'] = document.forms[0].mail.value;
	attrs_array['cpf'] = document.forms[0].corporative_information_cpf.value;
	
	if (document.forms[0].mailalternateaddress.value != '')
		attrs_array['mailalternateaddress'] = document.forms[0].mailalternateaddress.value;
	var attributes = admin_connector.serialize(attrs_array);

	var handler_validate_fields = function(data)
	{
		if (!data.status)
		{
			alert(data.msg);
		}
		else
		{
			if ( (data.question) && (!confirm(data.question)) )
			{
				return false;
			}

			if (type == 'create_user')
			{	// Turn enabled checkbox on create user.
                                document.getElementById('changepassword').disabled = false; 
				cExecuteForm ("$this.user.create", document.forms[0], handler_create);
			}
			else
			{
				//Turn enabled all checkboxes and inputs
				document.getElementById('changepassword').disabled = false;
				document.getElementById('phpgwaccountstatus').disabled = false;
				document.getElementById('phpgwaccountvisible').disabled = false;
				document.getElementById('telephonenumber').disabled = false;
				document.getElementById('mailforwardingaddress').disabled = false;
				document.getElementById('mailalternateaddress').disabled = false;
				document.getElementById('passwd_expired').disabled = false;
				document.getElementById('accountstatus').disabled = false;
				document.getElementById('deliverymode').disabled = false;
				document.getElementById('use_attrs_samba').disabled = false;
				
				table_apps = document.getElementById('ea_table_apps');
				var inputs = table_apps.getElementsByTagName("input");
				for (var i = 0; i < inputs.length; i++)
				{
					inputs[i].disabled = false;
				}
				cExecuteForm ("$this.user.save", document.forms[0], handler_save);
			}
		}
	}
	
	// Needed select all options from select
	select_user_maillists = document.getElementById('ea_select_user_maillists');
	select_user_groups = document.getElementById('ea_select_user_groups');
	for(var i=0; i<select_user_maillists.options.length; i++)
		select_user_maillists.options[i].selected = true;
	for(var i=0; i<select_user_groups.options.length; i++)
		select_user_groups.options[i].selected = true;
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	document.getElementById('uid').disabled = false; //Caso o login seja gerado automÃ¡tico, tirar o disabled.
	cExecute ('$this.ldap_functions.validate_fields&attributes='+attributes, handler_validate_fields);
}

function generate_login(first_name,second_name) {
	if ((first_name=='') || (second_name=='')) {
		alert(get_lang("You must type the first and the second name before generate the login"));
		return;
	}
	var attrs_array = new Array();
	attrs_array['first_name'] = first_name;
	attrs_array['second_name'] = second_name;
	var attributes = admin_connector.serialize(attrs_array);
	
	var handler_generate_login = function(data) {
		if(data['status']) {
			document.getElementById('uid').value = data['msg'];
			emailSuggestion_expressoadmin('true','true');
			document.getElementById("mail").value=document.getElementById("mail1").value;
		}
		else
			alert(data['msg']);
	}

	cExecute ('$this.ldap_functions.generate_login&attributes='+attributes, handler_generate_login);
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
	else
		alert(get_lang('User successful created') + '.');

	location.href="./index.php?menuaction=expressoAdmin1_2.uiaccounts.list_users";
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
	if (!data.status){
		alert(data.msg);
	}
	else{
		alert(get_lang('User successful saved') + '.');
	}
	location.href="./index.php?menuaction=expressoAdmin1_2.uiaccounts.list_users";
	return;
}

function get_available_groups(context, sentence)
{
//     if( !sentence )
// 	sentence = Element( 'ea_input_searchGroup' ).value;
// 
// 	var url = '$this.ldap_functions.get_available_groups&context='+context + '&sentence='+sentence;
// 
//     var fillHandler = function( fill ){
// 
// 	//fill = fill.users;
// 
// 	Element('ea_select_available_groups').innerHTML = fill;
// 
// 	return( fill !== "" );
//     }
// 
//     userFinder( sentence, fillHandler, url, new Function('x','return x'), 'ea_span_searching_group' );

    if( !sentence )
	sentence = 'ea_input_searchGroup';

    optionFind( sentence, 'ea_select_available_groups',
		'$this.ldap_functions.get_available_groups',
		context, 'ea_span_searching_group' );
		
}
	
function add_user2group()
{
	select_available_groups = document.getElementById('ea_select_available_groups');
	select_user_groups = document.getElementById('ea_select_user_groups');
	combo_primary_user_group = document.getElementById('ea_combo_primary_user_group');

	for (i = 0 ; i < select_available_groups.length ; i++)
	{
		if (select_available_groups.options[i].selected)
		{
			isSelected = false;
			for(var j = 0;j < select_user_groups.options.length; j++)
			{
				if(select_user_groups.options[j].value == select_available_groups.options[i].value)
				{
					isSelected = true;						
					break;	
				}
			}

			if(!isSelected)
			{
				new_option1 = document.createElement('option');
				new_option1.value =select_available_groups.options[i].value;
				new_option1.text = select_available_groups.options[i].text;
				new_option1.selected = true;
				select_user_groups.options[select_user_groups.options.length] = new_option1;
				
				new_option2 = document.createElement('option');
				new_option2.value =select_available_groups.options[i].value;
				new_option2.text = select_available_groups.options[i].text;
				combo_primary_user_group.options[combo_primary_user_group.options.length] = new_option2;
			}
		}
	}
		
	for (j =0; j < select_available_groups.options.length; j++)
		select_available_groups.options[j].selected = false;
} 	
	
function remove_user2group()
{
	select_user_groups = document.getElementById('ea_select_user_groups');
	combo_primary_user_group = document.getElementById('ea_combo_primary_user_group');
	
	var x;
	var j=0;
	var to_remove = new Array();
	
	for(var i = 0;i < select_user_groups.options.length; i++)
	{
		if(select_user_groups.options[i].selected)
		{
			to_remove[j] = select_user_groups.options[i].value;
			j++;
			select_user_groups.options[i--] = null;
		}
	}
	
	for (x in to_remove)
	{
		for(var i=0; i<combo_primary_user_group.options.length; i++)
		{
			if (combo_primary_user_group.options[i].value == to_remove[x])
			{
				combo_primary_user_group.options[i] = null;
			}	
		}
	}
}
	
function get_available_maillists(context, sentence)
{
    if( !sentence )
	sentence = Element('ea_input_searchMailList').value;

    var url = '$this.ldap_functions.get_available_maillists&context='+context + '&sentence='+sentence;

    var fillHandler = function( fill ){

	//fill = fill.users;

	Element('ea_select_available_maillists').innerHTML = fill;

	return( fill !== "" );
	}

    userFinder( sentence, fillHandler, url, new Function('x','return x'), 'ea_span_searching_maillist' );
}
	
function add_user2maillist()
{
	select_available_maillists = document.getElementById('ea_select_available_maillists');
	select_user_maillists = document.getElementById('ea_select_user_maillists');

	for (i = 0 ; i < select_available_maillists.length ; i++)
	{

		if (select_available_maillists.options[i].selected)
		{
			isSelected = false;
			for(var j = 0;j < select_user_maillists.options.length; j++)
			{
				if(select_user_maillists.options[j].value == select_available_maillists.options[i].value)
				{
					isSelected = true;						
					break;	
				}
			}

			if(!isSelected)
			{
				new_option = document.createElement('option');
				new_option.value =select_available_maillists.options[i].value;
				new_option.text = select_available_maillists.options[i].text;
				new_option.selected = true;
					
				select_user_maillists.options[select_user_maillists.options.length] = new_option;
			}
		}
	}
		
	for (j =0; j < select_available_maillists.options.length; j++)
		select_available_maillists.options[j].selected = false;
} 	
	
function remove_user2maillist()
{
	select_user_maillists = document.getElementById('ea_select_user_maillists');

	for(var i = 0;i < select_user_maillists.options.length; i++)
		if(select_user_maillists.options[i].selected)
			select_user_maillists.options[i--] = null;
}
	
function sinc_combos_org(context)
{
	combo_org_groups = document.getElementById('ea_combo_org_groups');
	combo_org_maillists = document.getElementById('ea_combo_org_maillists');

	for (i=0; i<combo_org_groups.length; i++)
	{
		if (combo_org_groups.options[i].value == context)
		{
			combo_org_groups.options[i].selected = true;
			combo_org_maillists.options[i].selected = true;
		}
	}
}
	
function use_samba_attrs(value)
{
	if (value)
	{
		if (document.forms[0].sambalogonscript.value == '')
		{
			if (document.forms[0].defaultLogonScript.value == '')
			{
				document.forms[0].sambalogonscript.value = document.forms[0].uid.value + '.bat';
			}
			else
			{
				document.forms[0].sambalogonscript.value = document.forms[0].defaultLogonScript.value;
			}
		}
		if (document.forms[0].sambahomedirectory.value == '')
		{
			document.forms[0].sambahomedirectory.value = '/home/'+document.forms[0].uid.value+'/';
		}
	}
	
	if (!document.forms[0].use_attrs_samba.disabled)
	{
		document.forms[0].sambaacctflags.disabled = !value;
		document.forms[0].sambadomain.disabled = !value;
		document.forms[0].sambalogonscript.disabled = !value;
		document.forms[0].sambahomedirectory.disabled = !value;
	}
}
	
function set_user_default_password()
{
	var handler_set_user_default_password = function(data)
	{
		if (!data.status)
			alert(data.msg);
		else
			alert(get_lang('Default password successful saved') + '.');
		return;
	}
	cExecute ('$this.user.set_user_default_password&uid='+document.forms[0].uid.value, handler_set_user_default_password);	
}

function return_user_password()
{
	var handler_return_user_password = function(data)
	{
		if (!data.status)
			alert(data.msg);
		else
			alert(get_lang('Users password successful returned') + '.');
		return;
	}
	cExecute ('$this.user.return_user_password&uid='+document.forms[0].uid.value, handler_return_user_password);
}

function delete_user(uid, uidnumber)
{
	if (confirm(get_lang("Do you really want delete the user") + " " + uid + "?"))
	{
		var handler_delete_user = function(data)
		{
			if (!data.status)
				alert(data.msg);
			else
				alert(get_lang('User successful deleted') + '.');
			
			location.href="./index.php?menuaction=expressoAdmin1_2.uiaccounts.list_users";
			return;
		}
		cExecute ('$this.user.delete&uidnumber='+uidnumber+'&uid='+uid, handler_delete_user);
	}
}

function rename_user(uid, uidnumber)
{
	if (document.getElementById('accounts_form_imapDelimiter').value == '/')
		var reUid = /^([a-zA-Z0-9_\.\-])+$/;
	else
		var reUid = /^([a-zA-Z0-9_\-])+$/;

	new_uid = prompt(get_lang('Rename users login from') + ': ' + uid + " " + get_lang("to") + ': ', uid);

	if(!reUid.test(new_uid)){
		alert(get_lang('LOGIN field contains characters not allowed') + '.');
		document.forms[0].account_lid.focus();
		return;
	}
	
	if ((new_uid) && (new_uid != uid))
	{
		var handler_validate_fields = function(data)
		{
			if (!data.status)
				alert(data.msg);
			else
				cExecute ('$this.user.rename&uid='+uid+'&new_uid='+new_uid, handler_rename);
			
			return;
		}
		
		// New uid exist?
		attrs_array = new Array();
		attrs_array['type'] = 'rename_user';
		attrs_array['uid'] = new_uid;
		attributes = admin_connector.serialize(attrs_array);
	
		cExecute ('$this.ldap_functions.validate_fields&attributes='+attributes, handler_validate_fields);
	}
}

// HANDLER RENAME
function handler_rename(data)
{
	if (!data.status)
		alert(data.msg);
	else{
		alert(get_lang('User login successful renamed') + "\n" + data.exec_return);
		location.href="./index.php?menuaction=expressoAdmin1_2.uiaccounts.list_users";
	}
	return;

}


// Variaveis Locais
var finderTimeout_maillist = '';

// Funcoes Find MailList
function optionFinderTimeout_maillist(obj, event)
{
    if( event && event.keyCode !== 13 )
	return;

    optionFinder_maillist( obj.id );
}
function optionFinder_maillist(id) {
		
    get_available_maillists( Element('ea_combo_org_maillists').value, Element(id).value );
	
}			

// Variaveis Locais
var finderTimeout_group = '';


// Funcoes Find Group
function optionFinderTimeout_group(obj, event)
{
		
	if( event && event.keyCode !== 13 )
	    return;
	
	optionFinder_group(obj.id);
}
function optionFinder_group(id) {	
	
	get_available_groups( Element('ea_combo_org_groups').value, Element(id).value );
}

function get_available_sambadomains(context, type)
{
	if ((type == 'create_user') && (document.getElementById('tabcontent7').style.display != 'none'))
	{
		var handler_get_available_sambadomains = function(data)
		{
			document.forms[0].use_attrs_samba.checked = data.status;
			use_samba_attrs(data.status);
			
			if (data.status)
			{
				combo_sambadomains = document.getElementById('ea_combo_sambadomains');
				for (i=0; i<data.sambaDomains.length; i++)
				{
					for (j=0; j<combo_sambadomains.length; j++)
					{
						if (combo_sambadomains.options[j].text == data.sambaDomains[i])
						{
							combo_sambadomains.options[j].selected = true;
							break;
						}
					}
				}
				
			}
		}
		
		cExecute ('$this.ldap_functions.exist_sambadomains_in_context&context='+context, handler_get_available_sambadomains);
	}
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
			alert(get_lang('Emptied')+' '+ data.inbox_size + ' ' + get_lang('MB from user inbox'));
			document.getElementById('mailquota_used').value = data.mailquota_used;
		}
	}
	cExecute ('$this.imap_functions.empty_user_inbox&uid='+uid, handler_empty_inbox);
}

function validarCPF(cpf)
{
	if(cpf.length != 11 || cpf == "00000000000" || cpf == "11111111111" ||
		cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" ||
		cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" ||
		cpf == "88888888888" || cpf == "99999999999"){
	  return false;
   }

	soma = 0;
	for(i = 0; i < 9; i++)
		soma += parseInt(cpf.charAt(i)) * (10 - i);
	resto = 11 - (soma % 11);
	if(resto == 10 || resto == 11)
		resto = 0;
	if(resto != parseInt(cpf.charAt(9)))
	{
		return false;
	}
	
	soma = 0;
	for(i = 0; i < 10; i ++)
		soma += parseInt(cpf.charAt(i)) * (11 - i);
	resto = 11 - (soma % 11);
	if(resto == 10 || resto == 11)
		resto = 0;
	if(resto != parseInt(cpf.charAt(10))){
		return false;
	}
	return true;
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

function add_input_mailalternateaddress()
{
	var input = document.createElement("INPUT");
	input.size = 30;
	input.name = "mailalternateaddress[]";
	input.setAttribute("autocomplete","off");
	document.getElementById("td_input_mailalternateaddress").appendChild(document.createElement("br"));
	document.getElementById("td_input_mailalternateaddress").appendChild(input);
}

function add_input_mailforwardingaddress()
{
	var input = document.createElement("INPUT");
	input.size = 30;
	input.name = "mailforwardingaddress[]";
	input.setAttribute("autocomplete","off");
	document.getElementById("td_input_mailforwardingaddress").appendChild(document.createElement("br"));
	document.getElementById("td_input_mailforwardingaddress").appendChild(input);
}

function set_changepassword()
{
	if (document.getElementById('passwd_expired').checked)
		{
		document.getElementById('changepassword').checked = true;
		document.getElementById('changepassword').disabled = true;
		}
		else
		{
		document.getElementById('changepassword').disabled =false;
		}
}


