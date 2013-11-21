countFiles = 1;

/*
 * Método que cria uma nova regra de tamanho máximo de mensagem.
 */
function create_messages_size()
{
	select_owners = Element('ea_select_owners');
        hidden_owners_acl = Element('owners_acls');
	for(var i = 0;i < select_owners.options.length; i++){		
		var user = select_owners.options[i].value;
		select_owners.options[i].value = user;    
		select_owners.options[i].selected = true;	
	}
        //hidden_owners_acl.value =  admin_connector.serialize(sharemailbox.ownersAcl); 
		
	cExecuteForm ("$this.bomessages_size.create_rule", document.getElementById('messages_size_form_template'), handler_create_messages_size);
		hidden_owners_acl.value = "";
}

/*
 * Handlers do método que cria uma nova regra.
 */
function handler_create_messages_size(data_return)
{
	handler_create_messages_size2(data_return);
	return;
}

function handler_create_messages_size2(data_return)
{
	if (!data_return.status)
	{
		write_msg(data_return.msg, 'error');
	}
	else
	{
		close_lightbox();
		write_msg(get_lang('New rule successful created') + '.', 'normal');
		history.go();  
	}
}
/*
 * Fim dos processamentos para criar uma nova regra. 
 */


function save_default_max_size(default_max_size)
{
	/* Valida o valor do campo de tamanho padrão */
	
	/* Verifica se não é um número*/
	if(isNaN(default_max_size))
	{
		alert(get_lang("Default size must be a number"));
		return;
	}
	/* Verifica se é negativo */
	if(default_max_size < 0)
	{
		alert(get_lang("Default size can not be negative"));
		return;
	}
	cExecute ('$this.bomessages_size.save_default_rule&default_max_size='+default_max_size, handle_save_default_max_size);
} 
 
function handle_save_default_max_size(data_return) 
{
	if (!data_return.status)
	{
		write_msg(data_return.msg, 'error');
	}
	else
	{
		//close_lightbox();
		write_msg(get_lang('New default value successful created') + '.', 'normal');
		history.go();  
	}
} 
 
 
/*
 * Método que deleta uma regra.
 */
function delete_messages_size(rule_name)
{
	rule_name = rule_name.replace(/%/g," ");
	if (!confirm(get_lang('Are you sure that you want to delete this rule') + "?"))
		return;
	
	var handle_delete_messages_size = function(data_return)
	{
		if (!data_return.status)
		{
			write_msg(data_return.msg, 'error');
		}
		else
		{
			write_msg(get_lang('Rule successful deleted') + '.', 'normal');
			//get_messages_size(Element('ea_rules_search').value);
			history.go();
		}
		return;
	}
	cExecute ('$this.bomessages_size.delete_rule&rule_name='+rule_name, handle_delete_messages_size);
}
/*
 * Fim dos processamentos para deletar uma regra. 
 */

 
/*
 * Método que busca os usuários no ldap de acordo com a organização.
 */
function get_available_users(ctx, sentence, handler)  // Fazer com que retorne também grupos junto com usuários
{
	var	handler_get_users = function(data)
	{
		if ((data) && (data.length > 0))
		{
		    if( typeof data == "string" )
				data = (new Function("return " + data))();

		    handler( data );
		}
	}

	cExecute ('$this.ldap_functions.get_available_users_messages_size&context=' + ctx + ( sentence ? '&sentence=' + sentence: '' ), handler_get_users);
}
/*
 * Fim dos processamentos para buscar os usuários do LDAP. 
 */

 
/*
 * Método que busca os usuários e grupos no ldap de acordo com a organização.
 */
function get_available_users_and_groups(ctx, sentence, handler)  // Fazer com que retorne também grupos junto com usuários
{
	var	handler_get_users = function(data)
	{
		if ((data) && (data.length > 0))
		{
		    if( typeof data == "string" )
				data = (new Function("return " + data))();

		    handler( data );
		}
	}

	cExecute ('$this.bomessages_size.get_available_users_and_groups&context=' + ctx + ( sentence ? '&sentence=' + sentence: '' ), handler_get_users);
}
/*
 * Fim dos processamentos para buscar os usuários e grupos do LDAP. 
 */
 
 
/*
 * Método que edita uma regra de tamanho de mensagem.
 */
function edit_messages_size(name_rule)
{
	// Retira os % do nome da regra.
	name_rule = name_rule.replace(/%/g," ");
	var handle_edit_messages_size = function(data)
	{
		if (data.status == true)
		{  
			modal('messages_size_modal','save');			
			Element('original_rule_name').value = data.email_recipient;
            Element('rule_name').value = data.email_recipient; 
			
			//Necessario, pois o IE6 tem um bug que não exibe as novas opções se o innerHTML estava vazio
            Element('ea_select_owners').innerHTML = '&nbsp;' + data.options;
            Element('ea_select_owners').outerHTML = Element('ea_select_owners').outerHTML;
			Element('max_messages_size').value = data.email_max_recipient;
		}
		else
			write_msg(data.msg, 'error');
	}
	cExecute ("$this.bomessages_size.get_users_by_rule&name_rule="+name_rule, handle_edit_messages_size);
}
/*
 * Fim dos processamentos para editar uma regra. 
 */
 
  
/*
 * Método que salva uma regra. É chamado quando uma regra é aberta para edição.
 */
function save_messages_size()
{
	if (is_ie){
		var i = 0;
		while (document.forms(i).name != "messages_size_form_template"){i++}
		form = document.forms(i);
	}
	else   form = document.getElementById('messages_size_form_template');

		hidden_owners_acl = Element('owners_acls');
		select_owners = Element('ea_select_owners');
		for(var i = 0;i < select_owners.options.length; i++){		
			var user = select_owners.options[i].value;                
			select_owners.options[i].value = user;
			if(select_owners.options[i] != "")
				select_owners.options[i].selected = true;
		}
		hidden_owners_acl.value =  admin_connector.serialize(sharemailbox.ownersAcl);

		cExecuteForm ("$this.bomessages_size.save_rule", document.getElementById('messages_size_form_template'), handler_save_messages_size);	
	//cExecute ("$this.bomessages_size.save_rule&owners="+owners, handler_save_messages_size);
        //hidden_owners_acl.value = "";
}

/*
 * Handlers do método que salva uma regra.
 */
function handler_save_messages_size(data_return)
{
	handler_save_messages_size2(data_return);
	return;
}

function handler_save_messages_size2(data_return)
{
	if (!data_return.status)
	{
		write_msg(data_return.msg, 'error');
	}
	else
	{
		//get_messages_size(Element('ea_rules_search').value);
		close_lightbox();
		write_msg(get_lang('Rule successful saved') + '.', 'normal');
		history.go();
	}
	return;
}
/*
 * Fim dos processamentos para salvar uma regra. 
 */

 
/*
 * Método que busca as regras de acordo com uma entrada do usuário.
 */
function get_messages_size_timeOut(input, event)
{
	
	var table = 
	
	Element('messages_size_content').innerHTML = '<table border="0" width="90%"><tr bgcolor="#d3dce3"><td width="30%">'+get_lang("rule name")+'</td><td width="30%">'+get_lang("max size rule")+'</td><td width="5%" align="center">'+get_lang("remove")+'</td></tr>'+'</table>';

	if (event.keyCode === 13)
	{
		get_messages_size( input );
		//cExecute ('$this.bomessages_size.get_rules_by_user&input='+input, handler_get_messages_size);
	}
}

function get_messages_size(input, callback)
{
	var handler_get_messages_size = function(data)
	{
		if (data.status == 'true')
		{
			// Em data.trs está armazenado todas as linhas que foram retornadas da busca pelo parametro passado pelo usuário.
			var table = '<table border="0" width="90%"><tr bgcolor="#d3dce3"><td width="30%">'+get_lang("rule name")+'</td><td width="30%">'+get_lang("max size rule")+'</td><td width="5%" align="center">'+get_lang("remove")+'</td></tr>'+data.trs+'</table>';
			Element('messages_size_content').innerHTML = table;
		}
		else{ 
				write_msg(data.msg, 'error');
			}
	}
	// Modificar para chamar o get_rules_by_user para o usuário buscar por nome de usuário e não de regra.
	cExecute ('$this.bomessages_size.get_rules&input='+input, handler_get_messages_size);
}  
/*
 * Fim dos processamentos para buscar uma regra. 
 */ 
 


/*
 * Método que remove os usuários que foram adicionados para participar da regra no modal.
 */ 
function remove_user()
{
	select_owners = Element('ea_select_owners');
	for(var i = 0;i < select_owners.options.length; i++)
		if(select_owners.options[i].selected){
                        var user = select_owners.options[i].value;
                        delete sharemailbox.ownersAcl[user];
                        select_owners.options[i--] = null;
                }
		//Nova chamada a "Element" é Necessária devido a um bug do ie com select
	   select_owners = Element('ea_select_owners');
       if(select_owners.options.length > 0 ){
            select_owners.options[0].selected = true;
            var user = select_owners.options[0].value;
            sharemailbox.getaclfromuser(user);
       }
} 
 
 
 
 
function findUsersAndGroups(obj, numMin, event)
{
    if( event && event.keyCode != 13 )
	return;

        findUsersAndGroupsInLdap(obj.id,numMin);


}

function findUsersAndGroupsInLdap(id, numMin)
{
    optionFind( id, 'ea_select_available_users',
		    'expressoAdmin1_2.bomessages_size.get_available_users_and_groups2',
		    'ea_combo_org', 'ea_span_searching' );
}

function handlerGetAvailableUsersAndGroups(data)
{

	var selectUsersAndGroups = Element('ea_select_available_users');

	for(var i=0; i < selectUsersAndGroups.options.length; i++)
        {
		selectUsersAndGroups.options[i] = null;
		i--;
	}

	var options = '###';
        if (data)
        {
		if(data.groups && data.groups.length > 0) {
			data.groups = '<option  value="-1" disabled>-------------'+Element("txt_groups").value+' --------- </option>' + data.groups;
		}
		if(data.users && data.users.length > 0) {
			data.users = '<option  value="-1" disabled>-------------'+Element("txt_users").value+' ---------</option>' + data.users;
		}
		options +=  data.groups && data.groups.length > 0 ? data.groups : '';
		options +=  data.users  && data.users.length  > 0 ? data.users  : '';

		//if(is_firefox_0)
			//fixBugInnerSelect(selectUsersAndGroups,options);
		//else
			selectUsersAndGroups.innerHTML = options;

		selectUsersAndGroups.outerHTML = selectUsersAndGroups.outerHTML;
		selectUsersAndGroups.disabled = false;
		//selectUsersClone = Element('selectUsers').cloneNode(true);
	}
     
}

function optionFinder(obj) {

	var id = obj.id;
	finder2( Element(id).value, 'ea_select_available_users', function( sentence, refillHandler ){

		Element("ea_span_searching").innerHTML = get_lang('searching') + '...';

		get_available_users( Element('ea_combo_org').value, sentence, function( data ){

		    Element("ea_span_searching").innerHTML = '&nbsp;';

		    refillHandler( data );
		});
	} );
}


function finder2( sentences, fillHandler, searchHandler )
{
    //caso fillHandler nao seja uma funcao, usar a default
    if( typeof fillHandler === "string" )
    {
	var selectId = fillHandler;

	fillHandler = function( fill ){

	    //recupera as options do respectivo select
	    var select = Element( selectId ).options;

	    //Limpa todo o select
	    select.length = 0;

	    //Inclui usuario comecando com a pesquisa
	    for( var value in fill )
	    select[select.length] = new Option( fill[value]["name"], value );

	    //chama o server side caso nao encontre resultado nenhum com essa sentenca
	    return( select.length === 0 );
	}
    }

    var original = sentences, fill = false;

    //checa se a variavel eh uma string ou regexp. Caso seja, a converte em um mapa
    if( typeof sentences === "string" || ( sentences.test && sentences.match ) )
	sentences = { "name": sentences };

    //varrer todas as sentencas e secoes especificas
    for( var section in sentences )
    {
	//sentenca para a secao especifica.
	var sentence = sentences[section];

	//checa se eh uma string. Se for, converte-la para uma regexp.
	if( typeof sentence === "string" )
	{
	    //TODO: tornar esse limite configuravel de acordo com a configuracao do expresso
	    if( sentence.length < 3 ) continue;
	    sentence = sentences[section] = new RegExp("\\b"+sentence, "i");
	}

	if( !fill )
	    fill = {};

	//populando o mapa filtrando pela determinada sentenca
	for( var key in userData )
	{
	    if( !userData[key] )
		userData[key] = {};

	    if ( sentence.test( userData[key][section] ) )
	    {
		if( !fill[key] )
		    fill[key] = {};

		fill[ key ][ section ] = userData[ key ][ section ];
	    }
	}
    }

    //tenta chamar o handler para popular, caso nao consiga chama o server side
    if( fill && fillHandler( fill ) && searchHandler )
    {
	//handler chamado pelo callback do servidor para repopular.
	var refillHandler = function( data, sections ){

	    if( !sections )
		sections = "name";

	    if( typeof sections === "string" )
	    {
		if( !data[sections] )
		    var dt = data, data = {};
		    data[sections] = dt;

		sections = [ sections ];
	    }
		
	    for( var i = 0; sections[i]; section = sections[i++] )
	    {
		for ( var key in data[section] )
		{
		    if( !userData[key] )
			userData[key] = {};

		    userData[key][section] = data[section][key];
		}
	    }

	    finder( sentences, fillHandler );
	};

	searchHandler( original, refillHandler );
    }
}


function set_onload()
{
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


var userData = {};

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
        select_owners = Element('ea_select_owners');
        select_owners.options[0].selected = true;
	}
}



	function cShareMailbox()
	{
		this.arrayWin = new Array();
		this.el;
		this.alert = false;
		this.ownersAcl = new Array();
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
		    acl = 'lrsa';
		}
		else{
		    Element('em_input_sendAcl').disabled = true;
		    Element('em_input_sendAcl').checked = false;
		}
					
		if (Element('em_input_deleteAcl').checked)
		    acl += 'te';

		if (Element('em_input_writeAcl').checked)
		    acl += 'wi';

		if (Element('em_input_sendAcl').checked)
		    acl += 'p';
			
		if (Element('em_input_folderAcl').checked)
		    acl += 'kx';
					

	                        
		this.ownersAcl[user] = acl;		
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
				select_users.options[i--] = null;
				
		Element('em_input_readAcl').checked = false;		
		Element('em_input_deleteAcl').checked = false;
		Element('em_input_writeAcl').checked = false;
		Element('em_input_sendAcl').checked = false;
		Element('em_input_folderAcl').checked = false;
	}
	
	
/* Build the Object */
	var sharemailbox;
	sharemailbox = new cShareMailbox();
