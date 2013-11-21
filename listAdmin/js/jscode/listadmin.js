	/************************************************************************************\
	* Expresso Administração                 										    *
	* by Joao Alfredo Knopik Junior (joao.alfredo@gmail.com, jakjr@celepar.pr.gov.br)   *
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			*
	*  under the terms of the GNU General Public License as published by the			*
	*  Free Software Foundation; either version 2 of the License, or (at your			*
	*  option) any later version.														*
	\************************************************************************************/

function emailSuggestion_expressoadmin(use_suggestion_in_logon_script, concatenateDomain)
{
	if (concatenateDomain == 'true')
	{
		domain_name = document.forms[0].defaultDomain.value;
		document.forms[0].mail1.value = document.forms[0].uid.value + '@' + domain_name;
	}
	else
	{
		document.forms[0].mail1.value = document.forms[0].uid.value;
	}

	if (use_suggestion_in_logon_script == 'true')
		document.forms[0].sambalogonscript.value = document.forms[0].uid.value+'.bat';
	document.forms[0].sambahomedirectory.value = '/home/'+document.forms[0].uid.value+'/';
	var re_cpf = /^([0-9])+$/;
	if ((document.forms[0].uid.value.length == 11)&&(re_cpf.test(document.forms[0].uid.value))&&(validarCPF(document.forms[0].uid.value)))
		{
		document.forms[0].corporative_information_cpf.value=document.forms[0].uid.value;
		return;
		}
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
function emailGenerate()
{
     document.forms[0].mail.value=document.forms[0].mail1.value;
     document.forms[0].mailalternateaddress.value=document.forms[0].uid.value + '@mail.' + document.forms[0].defaultDomain.value;
     //document.forms[0].mailalteraddress[].value=document.forms[0].uid.value + '@mail.' + document.forms[0].defaultDomain.value;
}