countFiles = 1;

function create_institutional_accounts()
{
	select_owners = Element('ea_select_owners');
	for(var i = 0;i < select_owners.options.length; i++)
		select_owners.options[i].selected = true;	
	cExecuteForm ("$this.ldap_functions.create_institutional_accounts", document.forms['institutional_accounts_form'], handler_create_institutional_accounts);
}

function handler_create_institutional_accounts(data_return)
{
	handler_create_institutional_accounts2(data_return);
	return;
}

function handler_create_institutional_accounts2(data_return)
{
	if (!data_return.status)
	{
		write_msg(data_return.msg, 'error');
	}
	else
	{
		close_lightbox();
		write_msg(get_lang('Institutional account successful created') + '.', 'normal');
	}
	return;
}

function set_onload()
{
	sinc_combos_org(Element('ea_combo_org').value);
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

	for (i=0; i<combo_org_available_users.length; i++)
	{
		if (combo_org_available_users.options[i].value == context)
		{
			combo_org_available_users.options[i].selected = true;
			break;
		}
	}
}

// var finderTimeout = '';
// optionFinderTimeout
function optionFinderTimeout(obj, event)
{

    if( event && event.keyCode !== 13 )
	return( true );
// 	clearTimeout(finderTimeout);
// 	var oWait = Element("ea_span_searching");
// 	oWait.innerHTML = get_lang('searching') + '...';
// 	finderTimeout = setTimeout("optionFinder('"+obj.id+"')",500);
    optionFinder( obj.id );
	
    return( false );
}
function optionFinder(id) {
//     var sentence = Element( id ).value;
// 
//     var url = '$this.ldap_functions.get_available_users&context=' + Element('ea_combo_org').value + '&sentence=' + sentence;
// 
//     var fillHandler = function( fill ){
// 
// 	    return fillContentSelect( fill, 'ea_select_available_users' );
// 
// 	}
// 
//     userFinder( sentence, fillHandler, url, 'ea_span_searching' );

    optionFind( id, 'ea_select_available_users', '$this.ldap_functions.get_available_users',
		    'ea_combo_org_available_users', 'ea_span_searching' );
}			

function add_user()
{
	select_available_users = Element('ea_select_available_users');
	select_owners = Element('ea_select_owners');

	var count_available_users = select_available_users.length;
//	var count_owners = select_owners.options.length;
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
		select_owners.innerHTML = '&nbsp;' + new_options + select_owners.innerHTML;
		select_owners.outerHTML = select_owners.outerHTML;
		document.getElementById('ea_input_searchUser').value = "";
	}
}

function remove_user()
{
	select_owners = Element('ea_select_owners');
	for(var i = 0;i < select_owners.options.length; i++)
		if(select_owners.options[i].selected)
			select_owners.options[i--] = null;
}

function get_institutional_accounts_timeOut(input, event)
{
	Element('institutional_accounts_content').innerHTML = '<table border="0" width="90%"><tr bgcolor="#d3dce3"><td width="30%">'+get_lang("full name")+'</td><td width="30%">'+get_lang("mail")+'</td><td width="5%" align="center">'+get_lang("remove")+'</td></tr></table>';
	
	if (event.keyCode === 13)
	{
	    get_institutional_accounts( input );
	}
}

function get_institutional_accounts(input)
{
	var handler_get_institutional_accounts = function(data)
	{
		if (data.status == 'true')
		{
			var table = '<table border="0" width="90%"><tr bgcolor="#d3dce3"><td width="30%">'+get_lang("full name")+'</td><td width="30%">'+get_lang("mail")+'</td><td width="5%" align="center">'+get_lang("delete")+'</td></tr>'+data.trs+'</table>';
			Element('institutional_accounts_content').innerHTML = table;
		}
		else
			write_msg(data.msg, 'error');
	}
	cExecute ('$this.ldap_functions.get_institutional_accounts&input='+input, handler_get_institutional_accounts);
}

function edit_institutional_account(uid)
{
	var handle_edit_institutional_account = function(data)
	{
		if (data.status == 'true')
		{
			modal('institutional_accounts_modal','save');
			
			var combo_org = Element('ea_combo_org');
			for (i=0; i<combo_org.length; i++)
			{
				if (combo_org.options[i].value == data.user_context)
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
			
			Element('cn').value = data.cn;
			Element('mail').value = data.mail;
			Element('desc').value = data.description;
			Element('ea_select_owners').innerHTML = data.owners;

			sinc_combos_org(data.user_context);
		}
		else
			write_msg(data.msg, 'error');
	}
	cExecute ('$this.ldap_functions.get_institutional_account_data&uid='+uid, handle_edit_institutional_account);
}

function save_institutional_accounts()
{
	if (is_ie){
		var i = 0;
		while (document.forms(i).name != "institutional_accounts_form"){i++}
		form = document.forms(i);
	}
	else
		form = document.forms["institutional_accounts_form"];
	
	select_owners = Element('ea_select_owners');
	for(var i = 0;i < select_owners.options.length; i++)
		select_owners.options[i].selected = true;	
	cExecuteForm ("$this.ldap_functions.save_institutional_accounts", form, handler_save_institutional_accounts);
}

function handler_save_institutional_accounts(data_return)
{
	handler_save_institutional_accounts2(data_return);
	return;
}
function handler_save_institutional_accounts2(data_return)
{
	if (!data_return.status)
	{
		write_msg(data_return.msg, 'error');
	}
	else
	{
		get_institutional_accounts(Element('ea_institutional_account_search').value);
		close_lightbox();
		write_msg(get_lang('Institutional account successful saved') + '.', 'normal');
	}
	return;
}

function delete_institutional_accounts(uid)
{
	if (!confirm(get_lang('Are you sure that you want to delete this institutional account') + "?"))
		return;
	
	var handle_delete_institutional_account = function(data_return)
	{
		if (!data_return.status)
		{
			write_msg(data_return.msg, 'error');
		}
		else
		{
			write_msg(get_lang('Institutional account successful deleted') + '.', 'normal');
			get_institutional_accounts(Element('ea_institutional_account_search').value);
		}
		return;
	}
	cExecute ('$this.ldap_functions.delete_institutional_account_data&uid='+uid, handle_delete_institutional_account);
}