<input type="hidden" id="{limitByGroupModalId}_title" value="{lang_limit_by_group}" />
<input type="hidden" id="{limitByGroupModalId}_height" value="300" />
<input type="hidden" id="{limitByGroupModalId}_width" value="800" />
<input type="hidden" id="{limitByGroupModalId}_close_action" value="close_lightbox()" />
<input type="hidden" id="{limitByGroupModalId}_create_action" value="createLimitRecipientsByGroup()" />
<input type="hidden" id="{limitByGroupModalId}_save_action" value="createLimitRecipientsByGroup()" />
<input type="hidden" id="{limitByGroupModalId}_onload_action" value="set_onload()" />

<form  name="limitByGroupModal_form_template" id="formLimitByGroupModal" method="post" >
<input type="hidden" id="anchor" name="anchor" />

<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
             <td>{lang_maximum_number_of_recipients} <input type="text" name="inputTextMaximumRecipientsGroupModal" id="inputTextMaximumRecipientsGroupModal"  size="5"/></td>
        </tr>
</table>
<br />
<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
             <td width="14%">{lang_search_organization} :</td>
             <td width="35%">{lang_organizations} :</td>
             <td width="30%">{lang_search_group} :</td>
        </tr>
         <tr>
             <td><input type="text" id="inputTextSearchOrganizationGroupModal" onKeyUp="javascript:searchOrganization(this.value, 'selectOrganizationsGroupModal','inputTextSearchOrganizationGroupModal','selectGroups');"  size="20"/></td>
             <td><select id="selectOrganizationsGroupModal" onChange="javascript:organizationChange('inputTextSearchUserGroupModal','selectGroups',true);"><option value=""></option>{optionsOrganizations}</select></td>
             <td><input type="text" id="inputTextSearchUserGroupModal" autocomplete="off" onkeypress="return findGroups(this,3,event);" size="40"/></td>
        </tr>
     
</table>
<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
            <td> <span id='spanSearching' style="color: red">&nbsp;</span></td>
        </tr>
</table>


<table width="90%" border="0" cellspacing="0" cellpading="0" align="center">
        <tr>
             <td width="35%">{lang_groups} :</td>
             <td width="30%"></td>
             <td width="35%">{lang_groups_in_rule} :</td>
        </tr>
         <tr>
             <td><select name="selectGroups" id="selectGroups" style="width:270px; height:85px" multiple="true" size="13"></select></td>
             <td align="center">
                <button type="button" onClick="javascript:addGroupInLimitSendersRule();"><img src="expressoAdmin1_2/templates/default/images/add.png" style="vertical-align: middle" width="20px" />&nbsp;{lang_add}</button>
                  <br />
                  <br />
                <button type="button" onClick="javascript:removeSelectedsOptions('selectGroupsInRule');"><img src="expressoAdmin1_2/templates/default/images/rem.png" style="vertical-align: middle;" width="20px" />&nbsp;{lang_remove}</button>
             </td>
             <td><select name="selectGroupsInRule[]" id="selectGroupsInRule" style="width:270px; height:85px" multiple="true" size="13"></select></td>
        </tr>
</table>
 
</form>