<!-- BEGIN header -->
<form method="POST" action="{action_url}">
<table border="0" align="center">
<script src="./expressoAdmin1_2/js/jscode/connector.js" type="text/javascript" language="JavaScript1.2"></script>
<script>
function test_db_connection()
{
	var handler_test = function(data)
	{
		var result = document.getElementById('nextid_db_result');
		if (data.status)
			result.innerHTML = '<font color=green><h3>OK</h3></font>';
		else
			result.innerHTML = '<font color=red><h3>Fail</h3></font>';
	}

	var host = document.getElementById('nextid_db_host').value;
	var port = document.getElementById('nextid_db_port').value;
	var name = document.getElementById('nextid_db_name').value;
	var user = document.getElementById('nextid_db_user').value;
	var pass = document.getElementById('nextid_db_password').value;
	
	cExecute ('$this.db_functions.test_db_connection&host='+host+'&port='+port+'&name='+name+'&user='+user+'&pass='+pass, handler_test);
}
</script>

<!-- END header -->
<!-- BEGIN body -->
	<tr class="th">
		<td colspan="2" align="center"><b>{lang_expressoAdmin_Setup}</b></td>
	</tr>

	<tr><td></td></tr>
	
	<tr class="row_off">
		<td>{lang_manage_userPasswordRFC2617_attribute}:</td>
		<td>
			<select name="newsettings[expressoAdmin_userPasswordRFC2617]">
				<option value="false" {selected_expressoAdmin_userPasswordRFC2617_false}>{lang_no}</option>
				<option value="true"  {selected_expressoAdmin_userPasswordRFC2617_true}>{lang_yes}</option>
			</select>			
		</td>
	</tr>
	<tr class="row_on">
		<td>{lang_REALM_for_userPasswordRFC2617_attribute}:</td>
		<td><input name="newsettings[expressoAdmin_realm_userPasswordRFC2617]" value="{value_expressoAdmin_realm_userPasswordRFC2617}" size="15" /></td>
	</tr>
	
	<tr class="row_off">
		<td>{lang_Manage_SAMBA_attributes}:</td>
		<td>
			<select name="newsettings[expressoAdmin_samba_support]">
				<option value="false" {selected_expressoAdmin_samba_support_false}>{lang_no}</option>
				<option value="true"  {selected_expressoAdmin_samba_support_true}>{lang_yes}</option>
			</select>			
		</td>
	</tr>
	<tr class="row_on">
		<td>{lang_Group_ID_to_samba_computers}:</td>
		<td><input name="newsettings[expressoAdmin_sambaGIDcomputers]" value="{value_expressoAdmin_sambaGIDcomputers}" size="15" /></td>
	</tr>
	<tr class="row_off">
		<td>{lang_Default_Logon_Script}:</td>
		<td><input name="newsettings[expressoAdmin_defaultLogonScript]" value="{value_expressoAdmin_defaultLogonScript}" size="15" />({lang_Let_it_blank_to_generate_the_logon_script_with_user_login})</td>
	</tr>
	<tr class="row_on">
		<td>{lang_Default_User_Password}:</td>
		<td><input name="newsettings[expressoAdmin_defaultUserPassword]" value="{value_expressoAdmin_defaultUserPassword}" size="15" /></td>
	</tr>
	<tr class="row_off">
		<td>{lang_Use_organization_prefix_on_account_creation}:</td>
		<td>
			<select name="newsettings[expressoAdmin_prefix_org]">
				<option value="false" {selected_expressoAdmin_prefix_org_false}>{lang_no}</option>
				<option value="true"  {selected_expressoAdmin_prefix_org_true}>{lang_yes}</option>
			</select>			
		</td>
	</tr>
	<tr class="row_on">
		<td>{lang_Default_User_Quota}:</td>
		<td><input name="newsettings[expressoAdmin_defaultUserQuota]" value="{value_expressoAdmin_defaultUserQuota}" size="10" /></td>
	</tr>
	<tr class="row_off">
		<td>{lang_Default_Shared_Account_Quota}:</td>
		<td><input name="newsettings[expressoAdmin_defaultSharedAccountQuota]" value="{value_expressoAdmin_defaultSharedAccountQuota}" size="10" /></td>
	</tr>
	<tr class="row_on">
		<td>{lang_Minimum_Size_Login}:</td>
		<td><input name="newsettings[expressoAdmin_minimumSizeLogin]" value="{value_expressoAdmin_minimumSizeLogin}" size="10" /></td>
	</tr>
	<tr class="row_off">
		<td>{lang_Default_Domain}:</td>
		<td><input name="newsettings[expressoAdmin_defaultDomain]" value="{value_expressoAdmin_defaultDomain}" size="15" /></td>
	</tr>
	<tr class="row_on">
		<td>{lang_concatenate_default_domain_to_the_mail}:</td>
		<td>
			<select name="newsettings[expressoAdmin_concatenateDomain]">
				<option value="false" {selected_expressoAdmin_concatenateDomain_false}>{lang_no}</option>
				<option value="true"  {selected_expressoAdmin_concatenateDomain_true}>{lang_yes}</option>
			</select>			
		</td>
	</tr>
	<tr class="row_off">
		<td>{lang_use_restrictions_in_the_creation_of_groups}:</td>
		<td>
			<select name="newsettings[expressoAdmin_restrictionsOnGroup]">
				<option value="false" {selected_expressoAdmin_restrictionsOnGroup_false}>{lang_no}</option>
				<option value="true"  {selected_expressoAdmin_restrictionsOnGroup_true}>{lang_yes}</option>
			</select>			
		</td>
	</tr>
	<tr class="row_on">
		<td>{lang_use_restrictions_in_the_creation_of_emaillists}:</td>
		<td>
			<select name="newsettings[expressoAdmin_restrictionsOnEmailLists]">
				<option value="false" {selected_expressoAdmin_restrictionsOnEmailLists_false}>{lang_no}</option>
				<option value="true"  {selected_expressoAdmin_restrictionsOnEmailLists_true}>{lang_yes}</option>
			</select>			
		</td>
	</tr>
	<tr class="row_off">
		<td>{lang_This_server_uses_Sending_Control_List_by_Policy}:</td>
		<td>
			<select name="newsettings[expressoAdmin_scl]">
				<option value="0" {selected_expressoAdmin_scl_0}>{lang_no}</option>
				<option value="1" {selected_expressoAdmin_scl_1}>{lang_yes}</option>
			</select>			
		</td>
	</tr>
	<tr class="row_on">
		<td>{lang_Does_CPF_field_must_be_completed}?</td>
		<td>
			<select name="newsettings[expressoAdmin_cpf_obligation]">
				<option value="0" {selected_expressoAdmin_cpf_obligation_0}>{lang_no}</option>
				<option value="1" {selected_expressoAdmin_cpf_obligation_1}>{lang_yes}</option>
			</select>			
		</td>
	</tr>
	
	<tr class="th">
		<td colspan="2" align="center"><b>{lang_Configurations_to_get_nextID_from_another_DB}<br>{lang_Leave_the_host_field_empty_to_use_the_same_DB_of_the_ExpressoLivre}</b></td>
	</tr>
	<tr class="row_on">
		<td>{lang_db_host}:</td>
		<td><input id="nextid_db_host" name="newsettings[expressoAdmin_nextid_db_host]" value="{value_expressoAdmin_nextid_db_host}" size="30" /></td>
	</tr>
	<tr class="row_off">
		<td>{lang_db_port}:</td>
		<td><input id="nextid_db_port" name="newsettings[expressoAdmin_nextid_db_port]" value="{value_expressoAdmin_nextid_db_port}" size="30" /></td>
	</tr>
	<tr class="row_on">
		<td>{lang_db_name}:</td>
		<td><input id="nextid_db_name" name="newsettings[expressoAdmin_nextid_db_name]" value="{value_expressoAdmin_nextid_db_name}" size="30" /></td>
	</tr>
	<tr class="row_off">
		<td>{lang_db_user}:</td>
		<td><input id="nextid_db_user" name="newsettings[expressoAdmin_nextid_db_user]" value="{value_expressoAdmin_nextid_db_user}" size="30" /></td>
	</tr>
	<tr class="row_on">
		<td>{lang_db_password}:</td>
		<td><input id="nextid_db_password" name="newsettings[expressoAdmin_nextid_db_password]" value="{value_expressoAdmin_nextid_db_password}" size="30" /></td>
	</tr>
	<tr class="row_off">
		<td><input type="button" value="{lang_test_connection_with_DB}" onClick="javascript:test_db_connection()"></td>
		<td><span>{lang_Result}: </span><span id="nextid_db_result"></span></td>
	</tr>
	<tr class="row_off"> 
                <td>{lang_use_quotas_control_for_ou}</td> 
                <td> 
                <select name="newsettings[expressoAdmin_cotasOu]"> 
                         <option value="">{lang_No}</option> 
                         <option value="true"{selected_expressoAdmin_cotasOu_true}>{lang_Yes}</option> 
                </select> 
                </td> 
        </tr>   
	<tr class="row_on">
		<td>{lang_use_login_generator}</td>
		<td>
		<select name="newsettings[expressoAdmin_loginGenScript]">
			 <option value="0">{lang_none}</option>
			 {rows_login_generator}
		</select>
		</td>
	</tr>
<!-- END body -->
<!-- BEGIN footer -->
	<tr class="th">
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" name="submit" value="{lang_submit}">
			<input type="submit" name="cancel" value="{lang_cancel}">
		</td>
	</tr>
</table>
</form>
<!-- END footer -->
