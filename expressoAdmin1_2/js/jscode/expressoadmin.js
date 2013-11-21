	/************************************************************************************\
	* Expresso Administração                 										    *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)   *
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			*
	*  under the terms of the GNU General Public License as published by the			*
	*  Free Software Foundation; either version 2 of the License, or (at your			*
	*  option) any later version.														*
	\************************************************************************************/

function load_lang(){
	cExecute ('$this/inc/load_lang', handler_load_lang);
}

var global_langs = new Array();
var emailSugestion = 1;

function handler_load_lang(data)
{
	global_langs = eval(data);
}

function get_associated_domain(context)
{
	var handler_associated_domain = function(data)
	{
		if( document.forms[0].associated_domain )
		{
			if ( data != null )
			{
				document.forms[0].associated_domain.value = data;
			}
			else
			{

				document.forms[0].associated_domain.value = '';
			}
		}
	};
	cExecute ('$this.ldap_functions.get_associated_domain&context=' + context, handler_associated_domain);
}

function get_lang(key_raw)
{
	key = key_raw.replace(/ /g,"_");
	key = key.replace(/-/g,"");
	lang = eval("global_langs."+key.toLowerCase());
	
	if (typeof(lang)=='undefined')
		return key_raw + '*';
	else
		return lang;
}

function emailSugestion_expressoadmin2(email) {		
		if ( email.value.indexOf('@', 0) < 0 )  emailSugestion = 1;
		if ( (email.value.indexOf('@', 0) == (email.value.length - 1)) && emailSugestion == 1 && email.value.length > 0 ) {
			var tmp;
			var context = "";

			organization_context = Element('ea_combo_org').value.toLowerCase();
			// Transformar os DN em User Friendly Naming format
			organization_name = organization_context.split(",");
			for (i in organization_name) {
				tmp = organization_name[i].split("=");
				context += tmp[1];
				if( i < (organization_name.length - 1) ) context +=  '.';
			}
			if( document.forms[0].associated_domain && document.forms[0].associated_domain.value != '' )
			{
				associatedDomain_name = document.forms[0].associated_domain.value;
				email.value = email.value + associatedDomain_name;
				emailSugestion = 0;
			} else{
				email.value = email.value + context;
				emailSugestion = 0;
			}
		}		
}

function emailSuggestion_expressoadmin(use_suggestion_in_logon_script, concatenateDomain)
{
	if (concatenateDomain == 'true')
	{
		// base_dn do LDAP Expresso
		var ldap_context = document.forms[0].ldap_context.value.toLowerCase();
		
		// OU selecionada
		organization_context = document.forms[0].context.value.toLowerCase();
		
		select_orgs = document.getElementById('ea_combo_org_info');
		for(var i=0; i<select_orgs.options.length; i++)
		{
			if(select_orgs.options[i].selected == true)
			{
				var x;
				var context = '';
				
				// OU selecionada
				select_context = select_orgs.options[i].value.toLowerCase();
				
				// Transformar os DN em User Friendly Naming format
				organization_name = organization_context.split(",");
				for (x in organization_name)
				{
					tmp = organization_name[x].split("=");
					context += tmp[1] + '.';
				}
			}
		}
		domain_name = document.forms[0].defaultDomain.value;
	
		// Retira o base_dn do valor do dn e retorna o numero de caracteres.
		x=context.indexOf(ldap_context,0);
		// Obtenho a string, sem o base_dn
		org_name_par = context.substring(0,(x-1));
		// Obtenho o nome da organização: String entre pontos, anterior ao base_dn
		org_name = org_name_par.split('.');
		org_name = org_name[org_name.length-1];

		if (org_name != '')
				document.forms[0].mail1.value = document.forms[0].uid.value + '@' + org_name + '.' + domain_name;
		else
			document.forms[0].mail1.value = document.forms[0].uid.value;
	}
	else
	{
		document.forms[0].mail1.value = document.forms[0].uid.value;
	}
	
	if (use_suggestion_in_logon_script == 'true')
		document.forms[0].sambalogonscript.value = document.forms[0].uid.value+'.bat';
	document.forms[0].sambahomedirectory.value = '/home/'+document.forms[0].uid.value+'/';
}	

function loadAppended( id, values, name )
{
    if( !values ) return;


    if( typeof name === "undefined" || !name )
	name = id + '[]';
	
    for( var i = 0; i < values.length; i++ )
    {
	if( !values[i] || values[i] === "" ) continue;

	var clone = addTextbox( name, id );

	clone.value = values[i];
    }
}

function addTextbox( name, targetId, id )
{
    var input = document.createElement( "input" );
    input.type = "text";
    input.id = ( typeof id === "undefined" )? "" : id;
    input.name = name;

    var target = document.getElementById( targetId );

    target.appendChild( input );

    removable( input );

    return( input );
}

function removable( target )
{
    with( target.parentNode )
    {
	var minus = document.createElement( "span" );
	var br = document.createElement( "br" );

	minus.innerHTML = " -";
	minus.style.cursor = "pointer";
	minus.onclick = function(){

	    removeChild( target );
	    removeChild( minus );
	    removeChild( br );
	}

	appendChild( minus );
	appendChild( br );
    }
}

function multiply( id, full )
{
    var target = document.getElementById( id );

    var clone = target.cloneNode( false );

    clone.id = "";

    if( !full )
	clone.value = "";

    target.parentNode.appendChild( clone );

    removable( clone );

    return( clone );
}

function appendClone( id, full )
{
    return multiply( id, full );
}

function FormataValor(event, campo)
{
	separador1 = '(';
	separador2 = ')';
	separador3 = '-';
		
	vr = campo.value;
	tam = vr.length;

	if ((tam == 1) && (( event.keyCode != 8 ) || ( event.keyCode != 46 )))
		campo.value = '';

	if ((tam == 3) && (( event.keyCode != 8 ) || ( event.keyCode != 46 )))
		campo.value = vr.substr( 0, tam - 1 );
	
	if (( tam <= 1 ) && ( event.keyCode != 8 ) && ( event.keyCode != 46 ))
 		campo.value = separador1 + vr;
		
	if (( tam == 3 ) && ( event.keyCode != 8 ) && ( event.keyCode != 46 ))
		campo.value = vr + separador2;
			
	if (( tam == 8 ) && (( event.keyCode != 8 ) && ( event.keyCode != 46 )))
		campo.value = vr + separador3;

	if ((( tam == 9 ) || ( tam == 8 )) && (( event.keyCode == 8 ) || ( event.keyCode == 46 )))
		campo.value = vr.substr( 0, tam - 1 );
}

function FormataCPF(event, campo)
{
	if (event.keyCode == 8)
		return;
	
	vr = campo.value;
	tam = vr.length;
	
	var RegExp_onlyNumbers = new RegExp("[^0-9.-]+");
	if ( RegExp_onlyNumbers.test(campo.value) )
		campo.value = vr.substr( 0, (tam-1));
	
	if ( (campo.value.length == 3) || (campo.value.length == 7) )
	{
		campo.value += '.';
	}
	
	if (campo.value.length == 11)
		campo.value += '-';
	return;
	
	
	alert(campo.value);
	return;
	
	separador1 = '.';
	separador2 = '-';
		
	vr = campo.value;
	tam = vr.length;

	if ((tam == 1) && (( event.keyCode != 8 ) || ( event.keyCode != 46 )))
		campo.value = '';

	if ((tam == 3) && (( event.keyCode != 8 ) || ( event.keyCode != 46 )))
		campo.value = vr.substr( 0, tam - 1 );
	
	if (( tam <= 1 ) && ( event.keyCode != 8 ) && ( event.keyCode != 46 ))
 		campo.value = separador1 + vr;
		
	if (( tam == 3 ) && ( event.keyCode != 8 ) && ( event.keyCode != 46 ))
		campo.value = vr + separador2;
			
	if (( tam == 8 ) && (( event.keyCode != 8 ) && ( event.keyCode != 46 )))
		campo.value = vr + separador3;

	if ((( tam == 9 ) || ( tam == 8 )) && (( event.keyCode == 8 ) || ( event.keyCode == 46 )))
		campo.value = vr.substr( 0, tam - 1 );
}
load_lang();
