<input type="hidden" id="{blockEmailForInstitutionalAccountId}_title" value="{lang_Blocking_sending_email_to_shared_accounts}" />
<input type="hidden" id="{blockEmailForInstitutionalAccountId}_height" value="450" />
<input type="hidden" id="{blockEmailForInstitutionalAccountId}_width" value="800" />
<input type="hidden" id="{blockEmailForInstitutionalAccountId}_close_action" value="close_lightbox()" />
<input type="hidden" id="{blockEmailForInstitutionalAccountId}_create_action" value="createBlockEmailForInstitutionalAcounteExeption()" />
<input type="hidden" id="{blockEmailForInstitutionalAccountId}_save_action" value="createBlockEmailForInstitutionalAcounteExeption()" />
<input type="hidden" id="{blockEmailForInstitutionalAccountId}_onload_action" value="set_onload()" />

<form  name="blockEmailForInstitutionalAccount_form_template" id="formblockEmailForInstitutionalAccount" method="post" >
<input type="hidden" id="anchor" name="anchor" />
<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
            <td width="150px">{lang_search_organization} :</td>
             <td >{lang_organizations} :</td>
         </tr>
         <tr>
             <td><input type="text" id="inputTextSearchOrganizationInstitutionalAccountModal" onKeyUp="javascript:searchOrganization(this.value, 'selectOrganizationsInstitutionalAccountModal','inputTextSearchUserInstitutionalAccountModal','selectUsers');"  size="40"/></td>
             <td><select id="selectOrganizationsInstitutionalAccountModal" onChange="javascript:organizationChange('inputTextSearchUserInstitutionalAccountModal','selectUsers',true);"><option value=""></option>{optionsOrganizations}</select></td>
        </tr>
     
</table>
<br />
<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
            <td>{lang_search_senders} :</td>
            <td> &nbsp; </td>
            <td> &nbsp; </td>
        </tr>
        <tr>
            <td width="175px"><input type="text" id="inputTextSearchUserAndGroupsInstitutionalAccountModal" autocomplete="off" onkeyup="return findUsersAndGroups(this,3,event);" size="40"/></td>
            <td align="left"><input id="inputCheckAllRecipientsInstitutionalAccountRule" name="inputCheckAllRecipientsInstitutionalAccountRule" type="checkbox" /> {lang_all_recipients}</td>
            <td><span id='spanSearching' style="color: red">&nbsp;</span></td>
        </tr>
</table>
<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
             <td width="35%">{lang_senders} :</td>
             <td width="30%"></td>
             <td width="35%">{lang_senders_in_rule} :</td>
        </tr>
         <tr>
             <td><select name="selectUsersAndGroups" id="selectUsersAndGroups" style="width:270px; height:85px" multiple="true" size="13"></select></td>
             <td align="center">
                <button type="button"  onClick="javascript:addUserOrGroupsInInstitutionalAccountRule();"><img src="expressoAdmin1_2/templates/default/images/add.png" style="vertical-align: middle" width="20px" />&nbsp;{lang_add}</button>
                  <br />
                  <br />
                <button type="button" onClick="javascript:removeSelectedsOptions('selectUsersOrGroupsInRule');"><img src="expressoAdmin1_2/templates/default/images/rem.png" style="vertical-align: middle;" width="20px" />&nbsp;{lang_remove}</button>
             </td>
             <td><select name="selectUsersOrGroupsInRule[]" id="selectUsersOrGroupsInRule" style="width:270px; height:85px" multiple="true" size="13"></select></td>
        </tr>
</table>
<br />
<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
            <td>{lang_search_recipients} :</td>
            <td> &nbsp;</td>
            <td>&nbsp; </td>
        </tr>
        <tr>
            <td width="170px"><input type="text" id="inputTextSearchSenderInstitutionalAccountModal" autocomplete="off" onkeyup="javascript:findSenders(this,3,event);" size="40"/></td>
            <td align="left"><input id="inputCheckAllSendersInstitutionalAccountRule" name="inputCheckAllSendersInstitutionalAccountRule" type="checkbox" /> {lang_all_senders}</td>
            <td><span id='spanSearchingSender' style="color: red">&nbsp;</span>&nbsp; </td>
        </tr>
</table>
<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
             <td width="35%">{lang_recipients} :</td>
             <td width="30%"></td>
             <td width="35%">{lang_recipients_in_rule} :</td>
        </tr>
         <tr>
             <td><select name="selecSenders" id="selecSenders" style="width:270px; height:85px" multiple="true" size="13"></select></td>
             <td align="center">
                <button type="button" onClick="javascript:addSenderInInstitutionalAccountRule();"><img src="expressoAdmin1_2/templates/default/images/add.png" style="vertical-align: middle" width="20px" />&nbsp;{lang_add}</button>
                  <br />
                  <br />
                <button type="button" onClick="javascript:removeSelectedsOptions('selecSendersInRule');"><img src="expressoAdmin1_2/templates/default/images/rem.png" style="vertical-align: middle;" width="20px" />&nbsp;{lang_remove}</button>
             </td>
            <td><select name="selecSendersInRule[]" id="selecSendersInRule" style="width:270px; height:85px" multiple="true" size="13"></select></td>
        </tr>
</table>
 
</form>
