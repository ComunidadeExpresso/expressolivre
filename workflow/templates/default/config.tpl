<!-- BEGIN header -->
<form method="POST" action="{action_url}">
<table border="0" align="center" cellspacing="1" cellpading="1" width="90%">
   <tr class="th">
	   <td colspan="3"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
   </tr>
   <tr class="row_on" bgcolor="{th_err}">
    <td colspan="3">&nbsp;<b>{error}</b></font></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
	<tr class="th">
		<td colspan="3">&nbsp;<b>{lang_Workflow_configuration}</b></font></td>
	</tr>
	<tr class="row_on">
		<td colspan="3">&nbsp;<b>{error}</b></font></td>
	</tr>
	<tr class="th">
		<td colspan="2">&nbsp;<b>{lang_Default_settings_for_processes}</b></font></td>
		<td class="row_off">&nbsp;</td>
	</tr>
	<tr class="th">
		<td>&nbsp;<b>{lang_Graphic_options}</b></font></td>
		<td colspan="2" class="row_on">&nbsp;</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_draw_roles_on_the_graph_beside_activity_name_like_[that]}
		</td>
		<td width="20%" class="td_right">
			{lang_draw_roles}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[draw_roles]">
                                <option value="False" {selected_draw_roles_False}>{lang_No}</option>
                                <option value="True" {selected_draw_roles_True}>{lang_Yes}</option>
                        </select>
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_size_of_the_font_used_on_the_graph_12_should_be_a_good_default_value}
		</td>
		<td width="20%" class="td_right">
			{lang_font_size}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="3" name="newsettings[font_size]" value="{value_font_size}">
		</td>
	</tr>

	<tr class="th">
		<td>&nbsp;<b>{lang_Running_activities_options}</b></font></td>
		<td colspan="2" class="row_off">&nbsp;</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_this_will_automatically_release_the_instance_when_leaving_an_activity_without_completing}
		</td>
		<td width="20%" class="td_right">
			{lang_auto-release_on_leaving_activity}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[auto-release_on_leaving_activity]">
                               	<option value="False" {selected_auto-release_on_leaving_activity_False}>{lang_No}</option>
                                <option value="True" {selected_auto-release_on_leaving_activity_True}>{lang_Yes}</option>
       	                </select>
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_the_activities_will_be_executed_using_secure_connection}
		</td>
		<td width="20%" class="td_right">
			{lang_use_secure_connection}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[execute_activities_using_secure_connection]">
				<option value="False" {selected_execute_activities_using_secure_connection_False}>{lang_No}</option>
				<option value="True" {selected_execute_activities_using_secure_connection_True}>{lang_Yes}</option>
			</select>
		</td>
	</tr>

	<tr class="th">
		<td>&nbsp;<b>{lang_Actions_Right_Options}</b></font></td>
		<td colspan="2" class="row_off">&nbsp;</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_owner_of_the_instance_will_have_the_right_to_abort_the_instance_at_any_time}
		</td>
		<td width="20%" class="td_right">
			{lang_ownership_give_abort_right}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[ownership_give_abort_right]">
                               	<option value="False" {selected_ownership_give_abort_right_False}>{lang_No}</option>
                                <option value="True" {selected_ownership_give_abort_right_True}>{lang_Yes}</option>
       	                </select>
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_owner_of_the_instance_will_have_the_right_to_exception_or_resume_the_instance_at_any_time}
		</td>
		<td width="20%" class="td_right">
			{lang_ownership_give_exception_right}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[ownership_give_exception_right]">
                               	<option value="False" {selected_ownership_give_exception_right_False}>{lang_No}</option>
                                <option value="True" {selected_ownership_give_exception_right_True}>{lang_Yes}</option>
       	                </select>
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_owner_of_the_instance_will_have_the_right_to_release_(un-assign)_an_activity_assigned_to_an_user}
		</td>
		<td width="20%" class="td_right">
			{lang_ownership_give_release_right}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[ownership_give_release_right]">
                               	<option value="False" {selected_ownership_give_release_right_False}>{lang_No}</option>
                                <option value="True" {selected_ownership_give_release_right_True}>{lang_Yes}</option>
       	                </select>
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_if_user_is_in_a_role_for_an_activity_he_will_have_the_right_to_abort_the_related_instance}
		</td>
		<td width="20%" class="td_right">
			{lang_role_give_abort_right}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[role_give_abort_right]">
                               	<option value="False" {selected_role_give_abort_right_False}>{lang_No}</option>
                                <option value="True" {selected_role_give_abort_right_True}>{lang_Yes}</option>
       	                </select>
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_if_user_is_in_a_role_for_an_activity_he_will_have_the_right_to_exception_or_resume_the_related_instance}
		</td>
		<td width="20%" class="td_right">
			{lang_role_give_exception_right}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[role_give_exception_right]">
                               	<option value="False" {selected_role_give_exception_right_False}>{lang_No}</option>
                                <option value="True" {selected_role_give_exception_right_True}>{lang_Yes}</option>
       	                </select>
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_if_user_is_in_a_role_for_an_activity_he_will_have_the_right_to_release_(un-assign)_the_related_instance}
		</td>
		<td width="20%" class="td_right">
			{lang_role_give_release_right}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[role_give_release_right]">
                               	<option value="False" {selected_role_give_release_right_False}>{lang_No}</option>
                                <option value="True" {selected_role_give_release_right_True}>{lang_Yes}</option>
       	                </select>
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_disable_advanced_actions_for_all_users}
		</td>
		<td width="20%" class="td_right">
			{lang_disable_advanced_actions}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[disable_advanced_actions]">
                               	<option value="False" {selected_disable_advanced_actions_False}>{lang_No}</option>
                                <option value="True" {selected_disable_advanced_actions_True}>{lang_Yes}</option>
       	                </select>
		</td>
	</tr>
	<tr class="th">
		<td>&nbsp;<b>{lang_log_options_for_workflow}</b></font></td>
		<td colspan="2" class="row_off">&nbsp;</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_log_level_for_workflow}
		</td>
		<td width="20%" class="td_right">
			{lang_log_level}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[log_level]">
                               	<option value="0" {selected_log_level_0}>{lang_emergency}</option>
                                <option value="1" {selected_log_level_1}>{lang_alert}</option>
                                <option value="2" {selected_log_level_2}>{lang_critical}</option>
                                <option value="3" {selected_log_level_3}>{lang_error}</option>
                                <option value="4" {selected_log_level_4}>{lang_warning}</option>
                                <option value="5" {selected_log_level_5}>{lang_notice}</option>
                                <option value="6" {selected_log_level_6}>{lang_information}</option>
                                <option value="7" {selected_log_level_7}>{lang_debug}</option>
       	                </select>
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_allowed_log_types_for_workflow}
		</td>
		<td width="20%" class="td_right">
			{lang_log_types}:
		</td>
		<td width="20%" class="td_right">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						{lang_file}&nbsp;&nbsp;&nbsp;
					</td>
					<td>
						<select name="newsettings[log_type_file]">
							<option value="False" {selected_log_type_file_False}>{lang_No}</option>
							<option value="True" {selected_log_type_file_True}>{lang_Yes}</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						{lang_firebug}&nbsp;&nbsp;&nbsp;
					</td>
					<td>
						<select name="newsettings[log_type_firebug]">
							<option value="False" {selected_log_type_firebug_False}>{lang_No}</option>
							<option value="True" {selected_log_type_firebug_True}>{lang_Yes}</option>
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr class="th">
		<td>&nbsp;<b>{lang_database_options_for_workflow}</b></font></td>
		<td colspan="2" class="row_off">&nbsp;</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_name_of_the_database_used_to_store_data_of_the_workflow_module}
		</td>
		<td width="20%" class="td_right">
			{lang_database_name}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[workflow_database_name]" value="{value_workflow_database_name}">
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_database_server_where_the_database_is_stored}
		</td>
		<td width="20%" class="td_right">
			{lang_database_host}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[workflow_database_host]" value="{value_workflow_database_host}">
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_port_used_to_connect_to_database_server}
		</td>
		<td width="20%" class="td_right">
			{lang_database_port}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[workflow_database_port]" value="{value_workflow_database_port}">
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_user_to_access_the_database}
		</td>
		<td width="20%" class="td_right">
			{lang_database_user}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[workflow_database_user]" value="{value_workflow_database_user}">
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_password_of_the_default_user}
		</td>
		<td width="20%" class="td_right">
			{lang_database_password}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[workflow_database_password]" value="{value_workflow_database_password}">
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_type_of_the_database}
		</td>
		<td width="20%" class="td_right">
			{lang_database_type}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[workflow_database_type]">
				<option value="pgsql" {selected_workflow_database_type_pgsql}>PostgreSQL</option>
			</select>
		</td>
	</tr>
	<tr class="th">
		<td>&nbsp;<b>{lang_database_options_for_processes}</b></font></td>
		<td colspan="2" class="row_off">&nbsp;</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_name_of_the_database_used_to_store_data_from_workflow_processes}
		</td>
		<td width="20%" class="td_right">
			{lang_database_name}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[database_name]" value="{value_database_name}">
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_database_server_where_the_database_is_stored}
		</td>
		<td width="20%" class="td_right">
			{lang_database_host}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[database_host]" value="{value_database_host}">
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_port_used_to_connect_to_database_server}
		</td>
		<td width="20%" class="td_right">
			{lang_database_port}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[database_port]" value="{value_database_port}">
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_default_user_to_access_the_database}
		</td>
		<td width="20%" class="td_right">
			{lang_database_user}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[database_admin_user]" value="{value_database_admin_user}">
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_password_of_the_default_user}
		</td>
		<td width="20%" class="td_right">
			{lang_database_password}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[database_admin_password]" value="{value_database_admin_password}">
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_type_of_the_database}
		</td>
		<td width="20%" class="td_right">
			{lang_database_type}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[database_type]">
                               	<option value="pgsql" {selected_database_type_pgsql}>PostgreSQL</option>
       	                </select>
		</td>
	</tr>
	<tr class="th">
		<td>&nbsp;<b>{lang_ldap_options}</b></font></td>
		<td colspan="2" class="row_off">&nbsp;</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_host_of_the_ldap}
		</td>
		<td width="20%" class="td_right">
			{lang_ldap_host}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[ldap_host]" value="{value_ldap_host}">
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_user_context_of_the_ldap}
		</td>
		<td width="20%" class="td_right">
			{lang_ldap_user_context}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[ldap_user_context]" value="{value_ldap_user_context}">
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_group_context_of_the_ldap}
		</td>
		<td width="20%" class="td_right">
			{lang_ldap_group_context}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[ldap_group_context]" value="{value_ldap_group_context}" />
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_follow_ldap_referrals}
		</td>
		<td width="20%" class="td_right">
			{lang_follow_referrals}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[ldap_follow_referrals]">
				<option value="False" {selected_ldap_follow_referrals_False}>{lang_No}</option>
				<option value="True" {selected_ldap_follow_referrals_True}>{lang_Yes}</option>
			</select>
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_user_of_the_ldap}
		</td>
		<td width="20%" class="td_right">
			{lang_ldap_user}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[ldap_user]" value="{value_ldap_user}"/>
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_password_of_the_ldap_user}
		</td>
		<td width="20%" class="td_right">
			{lang_ldap_password}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[ldap_password]" value="{value_ldap_password}"/>
		</td>
	</tr>
	<tr class="th">
		<td>&nbsp;<b>{lang_mainframe_options}</b></font></td>
		<td colspan="2" class="row_off">&nbsp;</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_ip_of_the_mainframe}
		</td>
		<td width="20%" class="td_right">
			{lang_mainframe_ip}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[mainframe_ip]" value="{value_mainframe_ip}">
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_port_used_to_connect_the_mainframe}
		</td>
		<td width="20%" class="td_right">
			{lang_access_mainframe_port}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[mainframe_port]" value="{value_mainframe_port}">
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_key_used_to_access_the_mainframe}
		</td>
		<td width="20%" class="td_right">
			{lang_access_mainframe_key}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[mainframe_key]" value="{value_mainframe_key}">
		</td>
	</tr>
	<tr class="row_off">
		<td width="60%" class="td_left">
			{lang_password_used_to_access_the_mainframe}
		</td>
		<td width="20%" class="td_right">
			{lang_access_mainframe_password}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[mainframe_password]" value="{value_mainframe_password}">
		</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_environment_application_to_mainframe}
		</td>
		<td width="20%" class="td_right">
			{lang_mainframe_environment}:
		</td>
		<td width="20%" class="td_right">
			<select name="newsettings[mainframe_environment]">
				<option value="D" {selected_mainframe_environment_D}>(D) Desenvolvimento</option>
				<option value="P" {selected_mainframe_environment_P}>(P) Produção</option>
			</select>
		</td>
	</tr>
	<tr class="th">
		<td>&nbsp;<b>{lang_External_Applications_options}</b></font></td>
		<td colspan="2" class="row_on">&nbsp;</td>
	</tr>
	<tr class="row_on">
		<td width="60%" class="td_left">
			{lang_subnetworks_of_the_intranet_(separed_by_;)}
		</td>
		<td width="20%" class="td_right">
			{lang_subnetworks}:
		</td>
		<td width="20%" class="td_right">
			<input type="text" size="20" name="newsettings[intranet_subnetworks]" value="{value_intranet_subnetworks}"/>
		</td>
	</tr>
<!-- END body -->
<!-- BEGIN footer -->
  <tr class="row_off">
    <td colspan="3">
&nbsp;
    </td>
  </tr>
  <tr>
    <td colspan="3" align="center">
      <input type="submit" name="submit" value="{lang_submit}">
      <input type="submit" name="cancel" value="{lang_cancel}">
    </td>
  </tr>
</table>
</form>
<!-- END footer -->
