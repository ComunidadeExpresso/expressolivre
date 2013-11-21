///Define countFiles para a função cExecuteForm;
countFiles = false;
////////////////////////////////////////////////

// Verifica versão do Firefox
var agt = navigator.userAgent.toLowerCase();
var is_firefox_0 = agt.indexOf('firefox/1.0') != -1 && agt.indexOf('firefox/0.') ? true : false;
var finderTimeout = '';
var selectRecipientsCloned = false;


function createLimitRecipientsByUser()
{
    var selectUsersInRule = Element('selectUsersInRule');
    for(i = 0; i < selectUsersInRule.length; i++)
         selectUsersInRule[i].selected = true;


    cExecuteForm("$this.boconfiguration.createLimitRecipientsByUser",document.getElementById('formLimitByUserModal'),handlerCreateLimitRecipientByUser);
}

function handlerCreateLimitRecipientByUser(data)
{
    handlerCreateLimitRecipientByUser2(data);
    return;
}

function handlerCreateLimitRecipientByUser2(data)
{ 
    if (!data.status)
    {
            write_msg(get_lang(data.msg), 'error');
    }
    else
    {
            close_lightbox();
            tableLimitRecipientByUserReload();
            write_msg(get_lang('rule created or update successfully') + '.', 'normal');
    }
    return;
}

function createBlockEmailForInstitutionalAcounteExeption()
{
    var selectSendersInRule = Element('selecSendersInRule');
    for(i = 0; i < selectSendersInRule.length; i++)
         selectSendersInRule[i].selected = true;

    var selectUsersOrGroupsInRule = Element('selectUsersOrGroupsInRule');
    for(i = 0; i < selectUsersOrGroupsInRule.length; i++)
         selectUsersOrGroupsInRule[i].selected = true;

    cExecuteForm("$this.boconfiguration.createBlockEmailForInstitutionalAcounteExeption",document.getElementById('formblockEmailForInstitutionalAccount'),handlerCreateBlockEmailForInstitutionalAcounteExeption);
}

function handlerCreateBlockEmailForInstitutionalAcounteExeption(data)
{
    handlerCreateBlockEmailForInstitutionalAcounteExeption2(data);
    return;
}

function handlerCreateBlockEmailForInstitutionalAcounteExeption2(data)
{
    if (!data.status)
    {
            write_msg(get_lang(data.msg), 'error');
    }
    else
    {
            close_lightbox();
            tableLimitRecipientByUserReload();
            write_msg(get_lang('rule created or update successfully') + '.', 'normal');
    }
    return;
}

function tableLimitRecipientByUserReload()
{
    document.location.reload();
}

function tableLimitRecipientByGroupReload()
{
    document.location.reload();
}

function createLimitRecipientsByGroup()
{
    var selectUsersInRule = Element('selectGroupsInRule');
    for(i = 0; i < selectUsersInRule.length; i++)
         selectUsersInRule[i].selected = true;

    cExecuteForm("$this.boconfiguration.createLimitRecipientsByGroup",document.getElementById('formLimitByGroupModal'),handlerCreateLimitRecipientsByGroup);
}

function handlerCreateLimitRecipientsByGroup(data)
{
    handlerCreateLimitRecipientsByGroup2(data);
    return;
}

function handlerCreateLimitRecipientsByGroup2(data)
{
    if (!data.status)
    {
            write_msg(get_lang(data.msg), 'error');
    }
    else
    {
            close_lightbox();
            tableLimitRecipientByGroupReload();
            write_msg(get_lang('rule created or update successfully') + '.', 'normal');
            
    }
    return;
}

function editLimitRecipientesByUser(pid)
{
    var handleEditLimitSendersByUser = function(data)
    {
        if(data.status)
        {
            modal('limitByUserModal','save');

            Element('inputTextMaximumRecipientsUserModal').value = data.email_max_recipient;
            Element('selectUsersInRule').options[Element('selectUsersInRule').length] = new Option( data.userCn, data.email_user );
        }
        else
            write_msg(data.msg, 'error');

    }

    cExecute ('$this.boconfiguration.editLimitRecipientesByUser&id='+pid, handleEditLimitSendersByUser);
}

function editBlockInstitutionalAccountExeption()
{
    var handleeditBlockInstitutionalAccountExeption = function(data)
    {
        if(data.status)
        {
            modal('blockEmailForInstitutionalAccountModal','save');

            var recipientIndex = document.getElementById('inputSelectRecipients').selectedIndex;
            var recipientValue = document.getElementById('inputSelectRecipients').options[recipientIndex].value;
            var recipientText = document.getElementById('inputSelectRecipients').options[recipientIndex].text;

            if(recipientValue == '*')
                document.getElementById('inputCheckAllRecipientsInstitutionalAccountRule').checked = true;
            else
                Element('selectUsersOrGroupsInRule').options[Element('selectUsersOrGroupsInRule').length] = new Option(recipientText, recipientValue );

            if(data.allSender)
            {
                document.getElementById('inputCheckAllSendersInstitutionalAccountRule').checked = true;
            }
            else
            {
                var selectSenders = document.getElementById('selecSendersInRule');
                selectSenders.innerHTML = data.options;
            }
        }
        else
            write_msg(data.msg, 'error');

    }
     var selectRecipient = document.getElementById('inputSelectRecipients');

     var index = selectRecipient.selectedIndex;
     var value = selectRecipient.options[index].value;

    cExecute ('$this.boconfiguration.getRecipientsInstitutionalAcounteExeption&recipient='+value, handleeditBlockInstitutionalAccountExeption);
}

function editLimitRecipientesByGroup(pid)
{
    var handleEditLimitSendersByGroup = function(data)
    {
        if(data.status)
        {
            modal('limitByGroupModal','save');

            Element('inputTextMaximumRecipientsGroupModal').value = data.email_max_recipient;
            Element('selectGroupsInRule').options[Element('selectGroupsInRule').length] = new Option( data.groupCn, data.email_user );
        }
        else
            write_msg(data.msg, 'error');

    }

    cExecute ('$this.boconfiguration.editLimitRecipientesByGroup&id='+pid, handleEditLimitSendersByGroup);
}

function removeLimitRecipientsByUser(pId)
{
    if (!confirm(get_lang('Are you sure that you want to delete this Rule') + "?"))
    return;
    
    var handleRemoveLimitSendersByUser = function(data_return)
	{
            if (!data_return.status)
            {
                    write_msg(data_return.msg, 'error');
            }
            else
            {
                    write_msg(get_lang('Rule successful deleted') + '.', 'normal');
                    document.location.reload();
            }
            return;
	}

    cExecute ('$this.boconfiguration.removeLimitRecipientsByUser&id='+pId,handleRemoveLimitSendersByUser);
}

function removeBlockInstitutionalAccountExeption()
{
    if (!confirm(get_lang('Are you sure that you want to delete this Rule') + "?"))
    return;

    var recipientIndex = document.getElementById('inputSelectRecipients').selectedIndex;
    var recipientValue = document.getElementById('inputSelectRecipients').options[recipientIndex].value;

    var handleRemoveBlockInstitutionalAccountExeption = function(data_return)
	{
            if (!data_return.status)
            {
                    write_msg(data_return.msg, 'error');
            }
            else
            {
                    write_msg(get_lang('Rule successful deleted') + '.', 'normal');
                    document.location.reload();
            }
            return;
	}

    cExecute ('$this.boconfiguration.removeBlockEmailForInstitutionalAcounteExeption&recipient='+recipientValue,handleRemoveBlockInstitutionalAccountExeption);
}

function removeLimitRecipientsByGroup(pId)
{
    if (!confirm(get_lang('Are you sure that you want to delete this Rule') + "?"))
    return;

    var handleRemoveLimitSendersByGroup = function(data_return)
	{
            if (!data_return.status)
            {
                    write_msg(data_return.msg, 'error');
            }
            else
            {
                    write_msg(get_lang('Rule successful deleted') + '.', 'normal');
                    document.location.reload();
            }
            return;
	}

    cExecute ('$this.boconfiguration.removeLimitRecipientsByGroup&id='+pId,handleRemoveLimitSendersByGroup);
}

function searchOrganization(input, select ,pInputSearch ,pSelectResult)
{
 
    var organizations = Element(select);
    var RegExp_org = new RegExp("\\b"+input, "i");
    var selected = organizations.selectedIndex;


    for(i = 0; i < organizations.length; i++)
    {
        if (RegExp_org.test(organizations[i].text))
        {
           if(selected != i)
           {
               organizations[i].selected = true;
               if(pInputSearch || pSelectResult)
                  organizationChange(pInputSearch,pSelectResult,false)
           }
            return;
        }
    }

}

function organizationChange(pInputSearch,pSelectResult,pClearSearch)
{
    var txtSearch = document.getElementById(pInputSearch);
    var SelectResults = document.getElementById(pSelectResult);

    if(pClearSearch){
        if(txtSearch)
			txtSearch.value = '';
	}
	
    for(var i = 0;i < SelectResults.options.length; i++)
         SelectResults.options[i--] = null;

     
}

function findGroups(obj, numMin, event)
{

    if( event && event.keyCode !== 13 )
	return( true );


    findGroupsInLdap( obj.id, numMin );

    return( false );
}

function findUsers(obj, numMin, event)
{

    if( event && event.keyCode !== 13 )
	return true;

    findUsersInLdap( obj.id, numMin );

    return false;
}

function findUsersAndGroups(obj, numMin, event)
{

    if( event && event.keyCode !== 13 )
	return( true );

    findUsersAndGroupsInLdap(obj.id, numMin);

    return( false );
}

function findUsersAndGroupsInLdap(id, numMin)
{

/*    var sentence = Element( id ).value;

    var url = 'expressoAdmin1_2.boconfiguration.searchUsersAndGroupsForSelect&context=' 
	      + Element( 'selectOrganizationsInstitutionalAccountModal' ).value
	      + '&filter=' + sentence;

   var fillHandler = function( fill ){

	return fillContentSelect( fill, 'selectUsersAndGroups' );
    }

    userFinder( sentence, fillHandler, url, 'spanSearching' );*/

    optionFind( id, 'selectUsersAndGroups', 'expressoAdmin1_2.boconfiguration.searchUsersAndGroupsForSelect',
		'selectOrganizationsInstitutionalAccountModal', 'spanSearching' );
}

function findSenders(obj, numMin, event)
{

    if( event && event.keyCode !== 13 )
	return( true );

    findSendersInLdap( obj.id, numMin );

    return( false );
}

function findSendersInLdap(id, numMin)
{
//     var sentence = Element( id ).value;
// 
//     var url = 'expressoAdmin1_2.boconfiguration.searchInstitutionalAccountsForSelect&context=' 
// 	      + Element( 'selectOrganizationsInstitutionalAccountModal' ).value
// 	      + '&filter=' + sentence;
// 
//     var fillHandler = function( fill ){
// 
// 	return fillContentSelect( fill, 'selecSenders' );
//     }
// 
//     userFinder( sentence, fillHandler, url, 'spanSearchingSender' );

    optionFind( id, 'selecSenders', 'expressoAdmin1_2.boconfiguration.searchInstitutionalAccountsForSelect',
    'selectOrganizationsInstitutionalAccountModal' ,'spanSearchingSender' );
}

function handlerGetAvailableSenders(data)
{

	var selectSenders = Element('selecSenders');

	for(var i=0; i < selectSenders.options.length; i++)
        {
		selectSenders.options[i] = null;
		i--;
	}

	var options = '###';
	if (data) {

		options +=  data  && data.length  > 0 ? data : '';

                if(is_firefox_0)
			fixBugInnerSelect(selectSenders,options);
		else
			selectSenders.innerHTML = options;

		selectSenders.outerHTML = selectSenders.outerHTML;
		selectSenders.disabled = false;
		selectSendersClone = Element('selecSenders').cloneNode(true);
	}
}

function handlerGetAvailableUsersAndGroups(data)
{

	var selectUsersAndGroups = Element('selectUsersAndGroups');

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

		if(is_firefox_0)
			fixBugInnerSelect(selectUsersAndGroups,options);
		else
			selectUsersAndGroups.innerHTML = options;

		selectUsersAndGroups.outerHTML = selectUsersAndGroups.outerHTML;
		selectUsersAndGroups.disabled = false;
		selectUsersClone = Element('selectUsers').cloneNode(true);
	}
     
}

function findUsersInLdap(id, numMin)
{

//     var sentence = Element( id ).value;
// 
//     var url = 'expressoAdmin1_2.boconfiguration.searchUsersForSelect&context=' 
// 	      + Element( 'selectOrganizationsUserModal' ).value
// 	      + '&filter=' + sentence;
// 
//     var fillHandler = function( fill ){
// 
// 	return fillContentSelect( fill, 'selectUsers' );
//     }
// 
//     userFinder( sentence, fillHandler, url, 'spanSearching' );

    optionFind( id, 'selectUsers', 'expressoAdmin1_2.boconfiguration.searchUsersForSelect',
		'selectOrganizationsUserModal', 'spanSearching' );
}

function findGroupsInLdap(id, numMin)
{

//     var sentence = Element( id ).value;
// 
//     var url = 'expressoAdmin1_2.boconfiguration.searchGroupsForSelect&context=' 
// 	      + Element( 'selectOrganizationsGroupModal' ).value
// 	      + '&filter=' + sentence;
// 
//     var fillHandler = function( fill ){
// 
// 	return fillContentSelect( fill, 'selectGroups' );
//     }
// 
//     userFinder( sentence, fillHandler, url, 'spanSearching' );

    optionFind( id, 'selectGroups', 'expressoAdmin1_2.boconfiguration.searchGroupsForSelect',
		'selectOrganizationsGroupModal', 'spanSearching' );
}

function handlerGetAvailableUsers(data)
{

	var selectUsers = Element('selectUsers');
        
	for(var i=0; i < selectUsers.options.length; i++)
        {
		selectUsers.options[i] = null;
		i--;
	}

	var options = '###';
	if (data) {

		options +=  data  && data.length  > 0 ? data : '';

                if(is_firefox_0)
			fixBugInnerSelect(selectUsers,options);
		else
			selectUsers.innerHTML = options;
                    
		selectUsers.outerHTML = selectUsers.outerHTML;
		selectUsers.disabled = false;
		selectUsersClone = Element('selectUsers').cloneNode(true);
	}
}

function handlerGetAvailableGroups(data)
{

      	var selectGroups = Element('selectGroups');
 
	for(var i=0; i < selectGroups.options.length; i++)
        {
		selectGroups.options[i] = null;
		i--;
	}

	var options = '###';
	if (data) {

		options +=  data  && data.length  > 0 ? data : '';

                if(is_firefox_0)
			fixBugInnerSelect(selectGroups,options);
		else
			selectGroups.innerHTML = options;


		selectGroups.outerHTML = selectGroups.outerHTML;
		selectGroups.disabled = false;
		selectGroupsClone = Element('selectGroups').cloneNode(true);
	}
}

function addUserOrGroupsInInstitutionalAccountRule()
{


        var selectUsersAndGroupsInRule = document.getElementById('selectUsersOrGroupsInRule');
	var selectUsersAndGroups = document.getElementById('selectUsersAndGroups');
	var selectUsersAndGroupsCount = selectUsersAndGroups.length;

	for (i = 0 ; i < selectUsersAndGroupsCount ; i++)
        {
		if (selectUsersAndGroups.options[i].selected)
                {
                       //Salva em value do item selecionado
                       var value = selectUsersAndGroups.options[i].value;
                       var text = selectUsersAndGroups.options[i].text;
                       //Asssume-se que ja exite no select
                       existInSelect = true;

                       //Verifica a existencia do usuario no select///
                       if(document.all)
                       {
                            if ( (selectUsersAndGroupsInRule.innerHTML.indexOf('value='+value)) == '-1' )
                                existInSelect = false;
                       }
                       else if ( (selectUsersAndGroupsInRule.innerHTML.indexOf('value="'+value+'"')) == '-1' )
                                existInSelect = false;
                       ///////////////////////////////////////////////

                      //Adiciona o a option no select
                      if(existInSelect == false)
                         selectUsersAndGroupsInRule.options[selectUsersAndGroupsInRule.length] = new Option( text, value );

		}
	}
}

function addSenderInInstitutionalAccountRule()
{


        var selectSendersInRule = document.getElementById('selecSendersInRule');
	var selectSenders = document.getElementById('selecSenders');
	var selectSendersCount = selectSenders.length;

	for (i = 0 ; i < selectSendersCount ; i++)
        {
		if (selectSenders.options[i].selected)
                {
                       //Salva em value do item selecionado
                       var value = selectSenders.options[i].value;
                       var text = selectSenders.options[i].text;
                       //Asssume-se que ja exite no select
                       existInSelect = true;

                       //Verifica a existencia do usuario no select///
                       if(document.all)
                       {
                            if ( (selectSendersInRule.innerHTML.indexOf('value='+value)) == '-1' )
                                existInSelect = false;
                       }
                       else if ( (selectSendersInRule.innerHTML.indexOf('value="'+value+'"')) == '-1' )
                                existInSelect = false;
                       ///////////////////////////////////////////////

                      //Adiciona o a option no select
                      if(existInSelect == false)
                         selectSendersInRule.options[selectSendersInRule.length] = new Option( text, value );

		}
	}
}

function addUserInLimitSendersRule()
{
        var selectUsersInRule = document.getElementById('selectUsersInRule');
	var selectUsers = document.getElementById('selectUsers');
	var selectUsersCount = selectUsers.length;
    
	for (i = 0 ; i < selectUsersCount ; i++)
        {
		if (selectUsers.options[i].selected)
                {
                       //Salva em value do item selecionado
                       var value = selectUsers.options[i].value;
                       var text = selectUsers.options[i].text;
                       //Asssume-se que ja exite no select
                       existInSelect = true;

                       //Verifica a existencia do usuario no select///
                       if(document.all)
                       {
                            if ( (selectUsersInRule.innerHTML.indexOf('value='+value)) == '-1' )
                                existInSelect = false;
                       }
                       else if ( (selectUsersInRule.innerHTML.indexOf('value="'+value+'"')) == '-1' )
                                existInSelect = false;
                       ///////////////////////////////////////////////

                      //Adiciona o a option no select
                      if(existInSelect == false) 
                         selectUsersInRule.options[selectUsersInRule.length] = new Option( text, value );                    

		}
	} 
}

function addGroupInLimitSendersRule()
{
        var selectGroupsInRule = document.getElementById('selectGroupsInRule');
	var selectGroups = document.getElementById('selectGroups');
	var selectGroupsCount = selectGroups.length;

	for (i = 0 ; i < selectGroupsCount ; i++)
        {
		if (selectGroups.options[i].selected)
                {
                       //Salva em value do item selecionado
                       var value = selectGroups.options[i].value;
                       var text = selectGroups.options[i].text;
                       //Asssume-se que ja exite no select
                       existInSelect = true;

                       //Verifica a existencia do usuario no select///
                       if(document.all)
                       {
                            if ( (selectGroupsInRule.innerHTML.indexOf('value='+value)) == '-1' )
                                existInSelect = false;
                       }
                       else if ( (selectGroupsInRule.innerHTML.indexOf('value="'+value+'"')) == '-1' )
                                existInSelect = false;
                       ///////////////////////////////////////////////

                      //Adiciona o a option no select
                      if(existInSelect == false)
                         selectGroupsInRule.options[selectGroupsInRule.length] = new Option( text, value );

		}
	}
}

function removeSelectedsOptions(pSelect)
{
    theSel = document.getElementById(pSelect);
    var selIndex = theSel.selectedIndex;

    if (selIndex != -1)
    {
        for(i=theSel.length-1; i>=0; i--)
        {
            if(theSel.options[i].selected)
                theSel.options[i] = null;
        }
            if (theSel.length > 0) {
                theSel.selectedIndex = selIndex == 0 ? 0 : selIndex - 1;
        }
    }

}

function set_onload()
{
   return true;
}

function finderRecipientInstitutionalAcounteExeption(pSearch)
{

    if(selectRecipientsCloned == false)
    {
        selectRecipientsClone = Element('inputSelectRecipients').cloneNode(true);
        selectRecipientsCloned = true;
    }
    

    var oText = pSearch;
    var selectRecipientsTmp = Element('inputSelectRecipients');
    for(var i = 0;i < selectRecipientsTmp.options.length; i++)
            selectRecipientsTmp.options[i--] = null;
    var RegExp_name = new RegExp("\\b"+oText.value, "i");


    for(i = 0; i < selectRecipientsClone.length; i++){
            if (RegExp_name.test(selectRecipientsClone[i].text) || selectRecipientsClone[i].value =="-1")
            {
                    sel = selectRecipientsTmp.options;
                    option = new Option(selectRecipientsClone[i].text,selectRecipientsClone[i].value);
                    if( selectRecipientsClone[i].value == "-1") option.disabled = true;
                    sel[sel.length] = option;
            }
    }
}

function getOptionsSendersInstitutionalAcounteExeption()
{
    var selectRecipient = document.getElementById('inputSelectRecipients');
    var selectSenders = document.getElementById('inputSelectSenders');

    for(var i=0; i < selectSenders.options.length; i++)
    {
            selectSenders.options[i] = null;
            i--;
    }

    var index = selectRecipient.selectedIndex;
    var value = selectRecipient.options[index].value;
    
    cExecute ('$this.boconfiguration.getOptionsSenderInstitutionalAcounteExeption&recipient='+value, handlegetOptionsSendersInstitutionalAcounteExeption);

}

function handlegetOptionsSendersInstitutionalAcounteExeption(data)
{
    
    if(data)
    {
        var selectSenders = document.getElementById('inputSelectSenders');
        var option = data.replace('>*<', '>'+get_lang('all')+'<');
        selectSenders.innerHTML = option;
    }
        
    return;
}

function saveGlobalSettings()
{
    var handlesaveGlobalSettings = function(data)
    {
        if(data.status)
        {
             write_msg(get_lang('save sucess') + '.', 'normal');
        }
        else
            write_msg(data.msg, 'error');

    }

    var blockComunication = Element('inputCheckAllUserBlockCommunication').checked;
    var maximumRecipient = Element('inputTextMaximumRecipientGenerally').value;

    cExecute ('$this.boconfiguration.saveGlobalConfiguration&blockComunication='+blockComunication+'&maximumRecipient='+maximumRecipient, handlesaveGlobalSettings);
}

function fixBugInnerSelect(objeto,innerHTML){
/******
* select_innerHTML - altera o innerHTML de um select independente se é FF ou IE
* Corrige o problema de não ser possível usar o innerHTML no IE corretamente
* Veja o problema em: http://support.microsoft.com/default.aspx?scid=kb;en-us;276228
* Use a vontade mas coloque meu nome nos créditos. Dúvidas, me mande um email.
* Versão: 1.0 - 06/04/2006
* Autor: Micox - Náiron José C. Guimarães - micoxjcg@yahoo.com.br
* Parametros:
* objeto(tipo object): o select a ser alterado
* innerHTML(tipo string): o novo valor do innerHTML
*******/
    objeto.innerHTML = ""
    var selTemp = document.createElement("micoxselect")
    var opt;
    selTemp.id="micoxselect1"
    document.body.appendChild(selTemp)
    selTemp = document.getElementById("micoxselect1")
    selTemp.style.display="none"
    if(innerHTML.toLowerCase().indexOf("<option")<0){//se não é option eu converto
        innerHTML = "<option>" + innerHTML + "</option>"
    }
    innerHTML = innerHTML.replace(/<option/g,"<span").replace(/<\/option/g,"</span")
    selTemp.innerHTML = innerHTML
    for(var i=0;i<selTemp.childNodes.length;i++){
        if(selTemp.childNodes[i].tagName){
            opt = document.createElement("OPTION")
            for(var j=0;j<selTemp.childNodes[i].attributes.length;j++){
                opt.setAttributeNode(selTemp.childNodes[i].attributes[j].cloneNode(true))
            }
            opt.value = selTemp.childNodes[i].getAttribute("value")
            opt.text = selTemp.childNodes[i].innerHTML
            if(document.all){ //IEca
                objeto.add(opt)
            }else{
                objeto.appendChild(opt)
            }
        }
    }
    document.body.removeChild(selTemp)
    selTemp = null
}
