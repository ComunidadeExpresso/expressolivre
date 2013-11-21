<!-- BEGIN body -->
<table border="0" width="90%" align="center">
	<tr>
		<td valign="top">
			{rows}
		</td>
		<td valign="top">
			<table border="0" width=100% cellspacing="4">
				<form action="{form_action}" method="POST" name="app_form">
					<input type="hidden" name="uidnumber" value="{uidnumber}">
					<input type="hidden" name="dn" value="{dn}">
					<input type="hidden" name="ldap_context" value="{ldap_context}">
					<input type="hidden" name="manager_context" value="{manager_context}">
					
					<tr>
						<td colspan="3" align="right" bgcolor="{color_bg1}">
							<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
							<input type="button" value="{lang_save}" onClick="javascript:save_scl();">
						</td>
					</tr>
					
					<tr>
						<td width="25%" valign="bottom" bgcolor="#DDDDDD">
							{lang_email_list}:<br><b><span style="color:red;">{mail}<span></b><br><br>
							{lang_apply_send_control_list_to_this_list}?<input type="checkbox" {accountRestrictive_checked} name="accountRestrictive"><br>
							{lang_participants_from_the_list_can_send_email_to_this_list}? <input type="checkbox" {participantCanSendMail_checked} name="participantCanSendMail"><br>
							<br>
							<b>{lang_users_who_can_send_email_to_this_list}:</b><br>
							<select id="ea_select_users_SCL_Maillist" name="members[]" style="width:400px; height:200px" multiple size="13">{ea_select_users_SCL_Maillist}</select>
						</td>
						
						<td valign="middle" align="center" bgcolor="#DDDDDD">
							<br><br><br><br><br><br>
							<button type="button" onClick="javascript:add_user2scl_maillist();"><img src="expressoAdmin1_2/templates/default/images/add.png" style="vertical-align: middle;" >&nbsp;{lang_add_user}</button>
							<br><br>
							<button type="button" onClick="javascript:remove_user2scl_maillist();"><img src="expressoAdmin1_2/templates/default/images/rem.png" style="vertical-align: middle;" >&nbsp;{lang_remove_user}</button>
						</td>
						
						<td valign="bottom" bgcolor="#DDDDDD">
							{lang_organizations}:<br>
							<select name="org_context" id="ea_combo_org_maillists" onchange="javascript:get_available_users(this.value, ea_check_allUsers.checked);">{combo_org}</select>
							
							<br>
							<input type="checkbox" name="ea_check_allUsers" id="ea_check_allUsers" onclick="javascript:get_available_users(org_context.value, this.checked);">
							{lang_show_users_from_all_sub-organizations}.
							<br><br>
							
							{lang_search_user}:<br>
							<input id="ea_input_searchUser" size="35" autocomplete="off" onkeyup="javascript:optionFinderTimeout(this)"><br>
							
							<font color="red"><span id="ea_span_searching">&nbsp;</span></font>
							<br>
							<b>{lang_users}:</b><br>
							<select id="ea_select_available_users" style="width:400px; height:200px" multiple size="13"></select>
						</td>
					</tr>
					
					<tr>
						<td colspan="3" align="left" bgcolor="{color_bg1}">
							<input type="button" value="{lang_save}" onClick="javascript:save_scl();">
							<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
						</td>
					</tr>
				</form>
			</table>
		</td>
	</tr>
</table>
<!-- END body -->