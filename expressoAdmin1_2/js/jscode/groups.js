// Variaveis Globais
countFiles = 0;
function validate_fields(type, restrictionsOnGroup)
{
	document.forms[0].cn.value = document.forms[0].cn.value.toLowerCase();
	document.forms[0].old_cn.value = document.forms[0].old_cn.value.toLowerCase();
	
	if (document.forms[0].cn.value == ''){
		alert(get_lang('NAME field is empty') + '.');
		return;
	}
		
	if (document.forms[0].description.value == ''){
		alert(get_lang('DESCRIPTION field is empty') + '.');
		return;
	}
	
	if (restrictionsOnGroup == 'true')
	{
		cn_tmp = document.forms[0].cn.value.split("-");
		if ( (cn_tmp.length < 3) || ((cn_tmp[0] != 'grupo') && (cn_tmp[0] != 'smb')) ){
			alert(
				get_lang('NAME field is incomplete') + '.\n' +
				get_lang('the name field must be formed like') + ':\n' +
				get_lang('group') + '-' + get_lang('organization') + '-' + get_lang('group name') + '.\n' +
				get_lang('eg') + ': ' + 'grupo-celepar-rh.');
			return;
		}
	}
	
	var reCn = /^([a-zA-Z0-9_\-])+$/;
	var reDesc = /^([a-zA-Z0-9_\- .])+$/;
	
	if(!reCn.test(document.forms[0].cn.value)){
		alert(get_lang('NAME field contains characters not allowed') + '.');
		document.forms[0].cn.focus();
		return;
	}

	if(!reDesc.test(document.forms[0].description.value)){
		alert(get_lang('DESCRIPTION field contains characters not allowed') + '.');
		document.forms[0].description.focus();
		return;
	}
	
	var reEmail = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if ( (document.forms[0].email.value != '') && (!reEmail.test(document.forms[0].email.value)) )
	{
		alert(get_lang('EMAIL field is empty') + '.');
		return false;
	}
	
	var handler_validate_fields = function(data)
	{
		if (!data.status)
			alert(data.msg);
		else
		{
			if (type == 'create_group')
				cExecuteForm ("$this.group.create", document.forms[0], handler_create);
			else if (type == 'edit_group')
				cExecuteForm ("$this.group.save", document.forms[0], handler_save);
		}
	}

	// Needed select all options from select
	select_userInGroup = document.getElementById('ea_select_usersInGroup');
	for(var i=0; i<select_userInGroup.options.length; i++)
		select_userInGroup.options[i].selected = true;
	
	// O CN do grupo foi alterado ou é um novo grupo.
	if ((document.forms[0].old_cn.value != document.forms[0].cn.value) || (type == 'create_group')){
		cExecute ('$this.group.validate_fields&cn='+document.forms[0].cn.value, handler_validate_fields);
	}
	else if (type == 'edit_group')
	{
		cExecuteForm ("$this.group.save", document.forms[0], handler_save);
	}
}

function Element( id )
{
    return document.getElementById( id );
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
		alert(get_lang('Group successful created') + '.');
		location.href="./index.php?menuaction=expressoAdmin1_2.uigroups.list_groups";
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
		alert(get_lang('Group successful saved') + '.');
		location.href="./index.php?menuaction=expressoAdmin1_2.uigroups.list_groups";
	}
	return;
}

function sinc_combos_org(context)
{
	combo_org_groups = document.getElementById('ea_combo_org_groups');

	for (i=0; i<combo_org_groups.length; i++)
	{
		if (combo_org_groups.options[i].value == context)
		{
			combo_org_groups.options[i].selected = true;
			get_available_users();
			break;
		}
	}
}

function get_available_users()
{
/*	var sentence = Element('ea_input_searchUser').value;
		
	var url = '$this.ldap_functions.get_available_users_and_shared_acounts&context='
		    + Element('ea_combo_org_info').value
		    + '&sentence=' + sentence;

	var fillHandler = function( fill ){
			
	Element('ea_select_available_users').innerHTML = fill;

	return( fill !== "" );
	}
	
	userFinder( sentence, fillHandler, url, 'ea_span_searching' );*/

	optionFind( 'ea_input_searchUser', 'ea_select_available_users',
		    '$this.ldap_functions.get_available_users_and_shared_acounts',
		    'ea_combo_org_groups', 'ea_span_searching' );
}

function add_user2group()
{
	var select_available_users = document.getElementById('ea_select_available_users');
	var select_usersInGroup = document.getElementById('ea_select_usersInGroup');

	var count_available_users = select_available_users.length;
	var count_usersInGroup = select_usersInGroup.options.length;
	var new_options = '';
	
	for (i = 0 ; i < count_available_users ; i++)
	{
		if (select_available_users.options[i].selected)
		{
			if(document.all)
			{
				if ( (select_usersInGroup.innerHTML.indexOf('value='+select_available_users.options[i].value)) == '-1' )
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
				if ( (select_usersInGroup.innerHTML.indexOf('value="'+select_available_users.options[i].value+'"')) == '-1' )
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
		select_usersInGroup.innerHTML = '&nbsp;' + new_options + select_usersInGroup.innerHTML;
		select_usersInGroup.outerHTML = select_usersInGroup.outerHTML;
		document.getElementById('ea_input_searchUser').value = "";
	}
}

function remove_user2group()
{
	select_usersInGroup = document.getElementById('ea_select_usersInGroup');
	
	for(var i = 0;i < select_usersInGroup.options.length; i++)
		if(select_usersInGroup.options[i].selected)
			select_usersInGroup.options[i--] = null;
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

	if( event.keyCode == 13 )
		{
	    limit = 0;
	    optionFinder(obj.id);
		}
}
function optionFinder(id)
{
	get_available_users();
}			

function delete_group(cn, gidnumber)
{
	if (confirm(get_lang('Do you really want delete the group') + ' ' + cn + " ??"))
	{
		var handler_delete_group = function(data)
		{
			if (!data.status)
				alert(data.msg);
			else
				alert(get_lang('Group success deleted') + '.');
			
			location.href="./index.php?menuaction=expressoAdmin1_2.uigroups.list_groups";
			return;
		}
		cExecute ('$this.group.delete&gidnumber='+gidnumber+'&cn='+cn, handler_delete_group);
	}
}

function use_samba_attrs(value)
{
	document.forms[0].sambasid.disabled = !value;
}

function get_available_sambadomains(context, type)
{
	if ((type == 'create_group') && (document.getElementById('ea_div_display_samba_options').style.display != 'none'))
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

function groupEmailSuggestion(concatenateDomain, type)
{
	if (document.forms[0].email.disabled)
		return;
	
	if (type != 'create_group')
		return;
	
	if (concatenateDomain == 'true')
	{
		var ldap_context = document.forms[0].ufn_ldap_context.value.toLowerCase();
		var organization_context = document.forms[0].context.value.toLowerCase();
		var select_orgs = document.getElementById('ea_combo_org_info');
		
		for(var i=0; i<select_orgs.options.length; i++)
		{
			if(select_orgs.options[i].selected == true)
			{
				var x;
				var context = '';
				select_context = select_orgs.options[i].value.toLowerCase();
				organization_name = organization_context.split(",");
			
				for (x in organization_name)
				{
					tmp = organization_name[x].split("=");
					context += tmp[1] + '.';
				}
			}
		}
		domain_name = document.forms[0].defaultDomain.value;
	
		x=context.indexOf(ldap_context,0);
		org_name_par = context.substring(0,(x-1));
		org_name = org_name_par.split('.');
		org_name = org_name[org_name.length-1];
		
		if (org_name != '')
			document.forms[0].email.value = document.forms[0].cn.value + '@' + org_name + '.aaa' + domain_name;
		else
			document.forms[0].email.value = document.forms[0].cn.value;
	}
	else
	{
		document.forms[0].email.value = document.forms[0].cn.value;
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

function popup_group_info()
{
	var select_usersInGroup = document.getElementById('ea_select_usersInGroup');
	var count_usersInGroup = select_usersInGroup.options.length;
	var html = '';
	
	for (i = 0 ; i < count_usersInGroup ; i++)
	{
		if(parseInt(select_usersInGroup.options[i].value) > 0)
			html += select_usersInGroup.options[i].text + '<br />';
	}

	var window_group = window.open('','','width=300,height=400,resizable=yes,scrollbars=yes,left=100,top=100');
	window_group.document.body.innerHTML = '<html><head></head><body><H1>'+ document.forms[0].cn.value + '</H1>'+html+'</body></html>';
	window_group.document.close();
	return true;
}