<!-- BEGIN form -->

<!--JS Imports from phpGW javascript class -->
{scripts_java}

<form action="{action}" name="managers_form" method="post">
 	<div align="center">
 	
 	<input type=hidden name="type" value="{type}">
	<input type=hidden name="hidden_manager_lid" value="{hidden_manager_lid}">
	<input type=hidden name="context">
    
	<table border="0" width="90%">
		<tr>
			<tr bgcolor="{color_bg1}" align="right">
				<td colspan="2">
				<input type="button" value="{lang_back}" onclick="javascript:location.href='index.php?menuaction=expressoAdmin1_2.uimanagers.list_managers'">
				<input type="button" value="{lang_save}" onclick="javascript:validade_managers_data('{type}');">
				</td>
			</tr>
			<td valign="top">
				<table border=0 width=100%>
					<tr bgcolor="{color_bg2}">
						<td colspan="2"><b>{lang_manager}</b></td>
					</tr>				
			    	<tr bgcolor="{color_font1}">
			    		<td width="25%">{lang_search_for_manager}:</td>
						<td>
							<input type="text" id="manager_lid" {input_manager_lid_disabled} name="manager_lid" value="{manager_lid}" size=30 autocomplete="off" onkeypress="return search_manager(this.value, event)"></input>
							<font color="red"><span id="ea_span_searching_manager">&nbsp;</span></font>
						</td>
					</tr>
					
					<tr bgcolor="{color_font1}" style="display:{display_manager_select}">
						<td width="25%">{lang_found_managers}:</td>
						<td>
							<select id="ea_select_managers" name="ea_select_manager" style="width: 400px" size="10">{ea_select_managers}</select>
						</td>
					</tr>
				    <tr bgcolor="{color_font2}">
						<td>{lang_context}:</td>
						<td id="td_input_context">
							<select id="ea_select_contexts">{options_contexts}</select>
							<span style="cursor:pointer" onclick="javascript:add_input_context();">+</span>
							<br>
							<span id="ea_spam_warn" style="color:red">&nbsp;</span>
							{input_context_fields}
						</td>
			    	</tr>
					<tr bgcolor="{color_bg2}">
						<td colspan="2"><b>{lang_access_control_list}</b></td>
					</tr>

					<tr><td colspan="2">
					
					<!-- BEGIN ACL CONTROL -->
					<!-- last acl: 2147483648 -->
					<table id="ea_table_acl">
					    <tr bgcolor="{color_font2}">
							<td width="20%"><input type="button" value="{lang_select_all}" onclick="select_all_acls('ea_table_acl');"></td>
					    </tr>
					    <tr bgcolor="{color_font1}" align='right'>
							<td width="20%">{lang_add_users}:</td>
							<td width="2%"><input type="checkbox" name="acl_add_users" value="true" {acl_add_users}></td>
							<td width="20%">{lang_add_groups}:</td>
							<td width="2%"><input type="checkbox" name="acl_add_groups" value="true" {acl_add_groups}></td>
							<td width="20%">{lang_add_email_lists}:</td>
							<td width="2%"><input type="checkbox" name="acl_add_maillists" value="true" {acl_add_maillists}></td>
							<td width="20%">{lang_create_organizations}:</td>
							<td width="2%"><input type="checkbox" name="acl_create_sectors" value="true" {acl_create_sectors}></td>
					    </tr>
					    <tr bgcolor="{color_font2}" align='right'>
							<td>{lang_edit_users}:</td>
							<td><input type="checkbox" name="acl_edit_users" value="true" {acl_edit_users}></td>
							<td>{lang_edit_groups}:</td>
							<td><input type="checkbox" name="acl_edit_groups" value="true" {acl_edit_groups}></td>
							<td>{lang_edit_email_lists}:</td>
							<td><input type="checkbox" name="acl_edit_maillists" value="true" {acl_edit_maillists}></td>
							<td>{lang_edit_organizations}:</td>
							<td><input type="checkbox" name="acl_edit_sectors" value="true" {acl_edit_sectors}></td>
						</tr>
						<tr bgcolor="{color_font1}" align='right'>
							<td>{lang_delete_users}:</td>
							<td><input type="checkbox" name="acl_delete_users" value="true" {acl_delete_users}></td>
							<td>{lang_delete_groups}:</td>
							<td><input type="checkbox" name="acl_delete_groups" value="true" {acl_delete_groups}></td>
							<td>{lang_delete_email_lists}:</td>
							<td><input type="checkbox" name="acl_delete_maillists" value="true" {acl_delete_maillists}></td>
							<td>{lang_delete_organizations}:</td>
							<td><input type="checkbox" name="acl_delete_sectors" value="true" {acl_delete_sectors}></td>
						</tr>
						<tr align='right'>
							<td bgcolor="{color_font2}">{lang_rename_users}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_rename_users" value="true" {acl_rename_users}></td>
							<td bgcolor="{color_font2}">{lang_edit_email_attribute_from_the_groups}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_edit_email_groups" value="true" {acl_edit_email_groups}></td>
							<td bgcolor="{color_font2}">{lang_edit_SCL_email_lists}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_edit_scl_email_lists" value="true" {acl_edit_scl_email_lists}></td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
						</tr>
						<tr  align='right'>
							<td bgcolor="{color_font1}" >{lang_manipulate_corporative_information}:</td>
							<td bgcolor="{color_font1}" ><input type="checkbox" name="acl_manipulate_corporative_information" value="true" {acl_manipulate_corporative_information}></td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
						</tr>
						<tr bgcolor="{color_font2}" align='right'>
							<td>{lang_view_user}:</td>
							<td><input type="checkbox" name="acl_view_users" value="true" {acl_view_users}></td>
							<td>{lang_add_shared_accounts}:</td>
							<td><input type="checkbox" name="acl_add_shared_accounts" value="true" {acl_add_shared_accounts}></td>
                            <td>{lang_add_institutional_accounts}:</td>
							<td><input type="checkbox" name="acl_add_institutional_accounts" value="true" {acl_add_institutional_accounts}></td>
                            <td>{lang_edit_general_maximum_number_of_recipients}:</td>
							<td><input type="checkbox" name="acl_edit_maximum_number_of_recipients_generally" value="true" {acl_edit_maximum_number_of_recipients_generally}></td>
                        </tr>
						<tr align='right'>
							<td bgcolor="{color_font1}" >{lang_edit_users_picture}:</td>
							<td bgcolor="{color_font1}" ><input type="checkbox" name="acl_edit_users_picture" value="true" {acl_edit_users_picture}></td>
							<td bgcolor="{color_font1}" >{lang_edit_shared_accounts}:</td>
							<td bgcolor="{color_font1}" ><input type="checkbox" name="acl_edit_shared_accounts" value="true" {acl_edit_shared_accounts}></td>
							<td bgcolor="{color_font1}" >{lang_edit_institutional_accounts}:</td>
							<td bgcolor="{color_font1}" ><input type="checkbox" name="acl_edit_institutional_accounts" value="true" {acl_edit_institutional_accounts}></td>
                            <td bgcolor="{color_font1}" >{lang_add_maximum_number_of_recipients_by_user}:</td>
							<td bgcolor="{color_font1}" ><input type="checkbox" name="acl_add_maximum_number_of_recipients_by_user" value="true" {acl_add_maximum_number_of_recipients_by_user}></td>
						</tr>
						<tr align='right'>
							<td bgcolor="{color_font2}">{lang_edit_users_phonenumber}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_edit_users_phonenumber" value="true" {acl_edit_users_phonenumber}></td>
							<td bgcolor="{color_font2}">{lang_delete_shared_accounts}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_delete_shared_accounts" value="true" {acl_delete_shared_accounts}></td>
							<td bgcolor="{color_font2}">{lang_remove_institutional_accounts}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_remove_institutional_accounts" value="true" {acl_remove_institutional_accounts}></td>
                            <td bgcolor="{color_font2}">{lang_edit_and_remove_maximum_number_of_recipients_by_user}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_edit_and_remove_maximum_number_of_recipients_by_user" value="true" {acl_edit_and_remove_maximum_number_of_recipients_by_user}></td>
                        </tr>
						<tr  align='right'>
							<td bgcolor="{color_font1}">{lang_change_users_password}:</td>
							<td bgcolor="{color_font1}"><input type="checkbox" name="acl_change_users_password" value="true" {acl_change_users_password}></td>
							<td bgcolor="{color_font1}">{lang_edit_shared_accounts_acl}:</td>
							<td bgcolor="{color_font1}"><input type="checkbox" name="acl_edit_shared_accounts_acl" value="true" {acl_edit_shared_accounts_acl}></td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                            <td bgcolor="{color_font1}">{lang_add_maximum_number_of_recipients_by_group}:</td>
							<td bgcolor="{color_font1}"><input type="checkbox" name="acl_add_maximum_number_of_recipients_by_group" value="true" {acl_add_maximum_number_of_recipients_by_group}></td>

						</tr>
						<tr align='right'>
							<td bgcolor="{color_font2}"> {lang_change_users_quote}:</td> 
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_change_users_quote" value="true" {acl_change_users_quote}></td>
							<td bgcolor="{color_font2}">{lang_edit_shared_accounts_quote}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_edit_shared_accounts_quote" value="true" {acl_edit_shared_accounts_quote}></td>
							<td bgcolor="{color_font2}">{lang_active_blocking_sending_email_to_shared_accounts}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_active_blocking_sending_email_to_shared_accounts" value="true" {acl_active_blocking_sending_email_to_shared_accounts}></td>
							<td bgcolor="{color_font2}">{lang_edit_and_remove_maximum_number_of_recipients_by_group}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_edit_and_remove_maximum_number_of_recipients_by_group" value="true" {acl_edit_and_remove_maximum_number_of_recipients_by_group}></td>
						</tr>
						<tr align='right'>
							<td bgcolor="{color_font1}">{lang_set_default_users_password}:</td>
							<td bgcolor="{color_font1}"><input type="checkbox" name="acl_set_user_default_password" value="true" {acl_set_user_default_password}></td>
							<td bgcolor="{color_font1}">{lang_empty_shared_accounts_inbox}:</td>
							<td bgcolor="{color_font1}"><input type="checkbox" name="acl_empty_shared_accounts_inbox" value="true" {acl_empty_shared_accounts_inbox}></td>
                            <td bgcolor="{color_font1}">{lang_add_blocking_sending_email_to_shared_accounts_exception}:</td>
							<td bgcolor="{color_font1}"><input type="checkbox" name="acl_add_blocking_sending_email_to_shared_accounts_exception" value="true" {acl_add_blocking_sending_email_to_shared_accounts_exception}></td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
						</tr>
						<tr align='right'>
							<td  bgcolor="{color_font2}">{lang_empty_user_inbox}:</td>
							<td  bgcolor="{color_font2}"><input type="checkbox" name="acl_empty_user_inbox" value="true" {acl_empty_user_inbox}></td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td bgcolor="{color_font2}">{lang_edit_and_remove_blocking_sending_email_to_shared_accounts_exception}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_edit_and_remove_blocking_sending_email_to_shared_accounts_exception" value="true" {acl_edit_and_remove_blocking_sending_email_to_shared_accounts_exception}></td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
						</tr>
						
						<tr><td>&nbsp;</td></tr>
						<tr bgcolor="{color_font1}" align='right'>
							<td style="{display_samba_suport}">{lang_edit_SAMBA_users_attributes}:</td>
							<td style="{display_samba_suport}"><input type="checkbox" name="acl_edit_sambausers_attributes" value="true" {acl_edit_sambausers_attributes}></td>
						</tr>
						<tr bgcolor="{color_font2}" align='right'>
							<td style="{display_samba_suport}">{lang_edit_SAMBA_domains}:</td>
							<td style="{display_samba_suport}"><input type="checkbox" name="acl_edit_sambadomains" value="true" {acl_edit_sambadomains}></td>
						</tr>
						<tr><td>&nbsp;</td></tr>

							<td>&nbsp;</td>
							<td>&nbsp;</td>
						<td bgcolor="{color_font2}" align='right'>{lang_add_messages_size_rule}:</td>
						<td bgcolor="{color_font2}" align='right'><input type="checkbox" name="acl_add_messages_size_rule" value="true" {acl_add_messages_size_rule}></td>
                                         
						<tr bgcolor="{color_font2}" align='right'>
							<td>{lang_show_sessions}:</td>
							<td><input type="checkbox" name="acl_view_global_sessions" value="true" {acl_view_global_sessions}></td>
							
							<td bgcolor="{color_font2}">{lang_edit_messages_size_rule}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_edit_messages_size_rule" value="true" {acl_edit_messages_size_rule}></td>
							
							<td style="{display_samba_suport}">{lang_create_computers}:</td>
							<td style="{display_samba_suport}"><input type="checkbox" name="acl_create_computers" value="true" {acl_create_computers}></td>
						</tr>
						<tr bgcolor="{color_font1}" align='right'>
							<td>{lang_view_logs}:</td>
							<td><input type="checkbox" name="acl_view_logs" value="true" {acl_view_logs}></td>
							
							<td bgcolor="{color_font2}">{lang_remove_messages_size_rule}:</td>
							<td bgcolor="{color_font2}"><input type="checkbox" name="acl_remove_messages_size_rule" value="true" {acl_remove_messages_size_rule}></td>
							
							<td style="{display_samba_suport}">{lang_edit_computers}:</td>
							<td style="{display_samba_suport}"><input type="checkbox" name="acl_edit_computers" value="true" {acl_edit_computers}></td>
						</tr>
						<tr bgcolor="{color_font2}" align='right'>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td style="{display_samba_suport}">{lang_delete_computers}:</td>
							<td style="{display_samba_suport}"><input type="checkbox" name="acl_delete_computers" value="true" {acl_delete_computers}></td>
						</tr> 
						</table></td>

						</tr>
					<!-- END ACL CONTROL -->
					
					
					<tr bgcolor="{color_bg2}">
					  <td colspan="4"><b>{lang_applications}</b></td>
					</tr>
					<table id="ea_table_app">
						<tr bgcolor="{color_font2}">
							<td width="20%"><input type="button" value="{lang_select_all}" onclick="select_all_acls('ea_table_app');"></td>
					    </tr>
						<tr>
							{app_list}
						</tr>
					</table>
					<tr bgcolor={color_bg1} align="left">
						<td>
							<input type="button" value="{lang_save}" onclick="javascript:validade_managers_data('{type}');">
							<input type="button" value="{lang_back}" onclick="javascript:location.href='index.php?menuaction=expressoAdmin1_2.uimanagers.list_managers'">
						</td>
					</tr>
				</table>
			</td>
   		</tr>
   	</table>
	</div>
 </form>
 {error_messages}
<!-- END form -->
 