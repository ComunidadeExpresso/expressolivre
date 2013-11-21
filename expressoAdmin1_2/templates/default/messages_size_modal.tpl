<input type="hidden" id="{modal_id}_title" value="{lang_messages_size_configuration}">
<input type="hidden" id="{modal_id}_height" value="425">
<input type="hidden" id="{modal_id}_width" value="750">
<input type="hidden" id="{modal_id}_close_action" value="close_lightbox()">
<input type="hidden" id="{modal_id}_create_action" value="create_messages_size()"> 
<input type="hidden" id="{modal_id}_save_action" value="save_messages_size()"> 
<input type="hidden" id="{modal_id}_onload_action" value="set_onload({manager_context})"> 

<form enctype="multipart/form-data" name="messages_size_form_template" id="messages_size_form_template" method="post">

<input type="hidden" id="anchor" name="anchor">
<input type="hidden" id="owners_acls" name="owners_acl" value="">
<input type="hidden" id="original_rule_name" name="original_rule_name" value="">

<table border="0" cellspacing="4">
	<tr>
		<td width="35%" valign="bottom" bgcolor="#DDDDDD">
			<p style="line-height: 300%">
				   	 {lang_rule_name}:
					     <input type="text" id="rule_name" name="rule_name" autocomplete="off" value="{rule_name}" size=30><br> 
						 
					 {lang_max_message_size_MB}:												
                        <input type="text" id="max_messages_size" name="max_messages_size" autocomplete="off" value="{max_messages_size}" size=5><br>   
                    					
			<b>{lang_participants}:</b><br>
			<select id="ea_select_owners" onchange="sharemailbox.getaclfromuser(this.value);" name="owners[]" style="width:300px; height:160px" multiple size="13"></select>
		</td>
						
		<td width="15%" valign="middle" align="center" bgcolor="#DDDDDD">
			<button id="bt_add_user" type="button" onClick="javascript:add_user();"><img src="expressoAdmin1_2/templates/default/images/add.png" style="vertical-align: middle;">&nbsp;{lang_add}</button>
			<br><br>
			<button id="bt_remove_user" type="button" onClick="javascript:remove_user();"><img src="expressoAdmin1_2/templates/default/images/rem.png" style="vertical-align: middle;">&nbsp;{lang_remove}</button>
		</td>
						
		<td width="35%" valign="bottom" bgcolor="#DDDDDD">
			{lang_search_organization}:<br>
			<input type="text" id="organization_search" autocomplete="off" size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org');" onBlur="javascript:findUsersAndGroups(this,3);">
			<br>
							
			{lang_organizations}:<br>
			<select name="org_context" id="ea_combo_org" onchange="javascript:optionFinder(this);">{all_organizations}</select>
			<br>
			<br><br>
							
			{lang_search_user_or_groups}:<br>
			<input name="ea_input_searchUser "id="ea_input_searchUser" size="35" autocomplete="off" onkeyup="javascript:findUsersAndGroups(this,3,event);"><br>
							
			<font color="red"><span id="ea_span_searching">&nbsp;</span></font>
			<br>
			<b>{lang_users_or_groups}:</b><br>
			<select id="ea_select_available_users" name="ea_select_available_users[]" style="width:300px; height:160px" multiple size="13"></select>
		</td>
	</tr>
</table>

</form>
