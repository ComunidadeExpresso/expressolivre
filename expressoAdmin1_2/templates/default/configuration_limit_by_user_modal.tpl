<input type="hidden" id="{limitByUserModalId}_title" value="{lang_limit_by_user}" />
<input type="hidden" id="{limitByUserModalId}_height" value="300" />
<input type="hidden" id="{limitByUserModalId}_width" value="800" />
<input type="hidden" id="{limitByUserModalId}_close_action" value="close_lightbox()" />
<input type="hidden" id="{limitByUserModalId}_create_action" value="createLimitRecipientsByUser()" />
<input type="hidden" id="{limitByUserModalId}_save_action" value="createLimitRecipientsByUser()" />
<input type="hidden" id="{limitByUserModalId}_onload_action" value="set_onload()" />

<form  name="limitByUserModal_form_template" id="formLimitByUserModal" method="post" >
<input type="hidden" id="anchor" name="anchor" />

<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
             <td>{lang_maximum_number_of_recipients} <input type="text" name="inputTextMaximumRecipientsUserModal" id="inputTextMaximumRecipientsUserModal"  size="5"/></td>
        </tr>
</table>
<br />
<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
             <td width="14%">{lang_search_organization} :</td>
 	     <td width="35%">{lang_organizations} :</td>
 	     <td width="30%">{lang_search_user} :</td>
        </tr>
         <tr>
             <td><input type="text" id="inputTextSearchOrganizationUserModal" onKeyUp="javascript:searchOrganization(this.value, 'selectOrganizationsUserModal','inputTextSearchUserUserModal','selectUsers');"  size="20"/></td>
             <td><select id="selectOrganizationsUserModal" onChange="javascript:organizationChange('inputTextSearchUserUserModal','selectUsers',true);"><option value=""></option>{optionsOrganizations}</select></td>
             <td><input type="text" id="inputTextSearchUserUserModal" autocomplete="off" onkeypress="return findUsers(this,3,event);" size="40"/></td>
        </tr>
     
</table>
<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
            <td> <span id='spanSearching' style="color: red">&nbsp;</span></td>
        </tr>
        
     
</table>


<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
             <td width="35%">{lang_users} :</td>
             <td width="30%"></td>
             <td width="35%">{lang_users_in_rule} :</td>
        </tr>
         <tr>
             <td><select name="selectUsers" id="selectUsers" style="width:270px; height:85px" multiple="true" size="13"></select></td>
             <td align="center">
                <button type="button" onClick="javascript:addUserInLimitSendersRule();"><img src="expressoAdmin1_2/templates/default/images/add.png" style="vertical-align: middle" width="20px" />&nbsp;{lang_add}</button>
                  <br />
                  <br />
                <button type="button" onClick="javascript:removeSelectedsOptions('selectUsersInRule');"><img src="expressoAdmin1_2/templates/default/images/rem.png" style="vertical-align: middle;" width="20px" />&nbsp;{lang_remove}</button>
             </td>
             <td><select name="selectUsersInRule[]" id="selectUsersInRule" style="width:270px; height:85px" multiple="true" size="13"></select></td>
        </tr>
</table>
 
</form>
