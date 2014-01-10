<!-- BEGIN body -->
<table border="0" width="90%" align="center">
	<tr>
		<td valign="top">
			{rows}
		</td>
		<td valign="top">
			<form action="{form_action}" method="POST" name="app_form">
				<input type="hidden" name="try_saved" value="false">
				<input type="hidden" name="uidnumber" value="{uidnumber}">
				<input type="hidden" name="computer_dn" value="{computer_dn}">					
				<input type="hidden" name="old_computer_context" value="{old_computer_context}">
				<input type="hidden" name="old_computer_cn" value="{old_computer_cn}">
				<input type="hidden" name="old_computer_dn" value="{old_computer_dn}">
				<input type="hidden" name="old_computer_sambaAcctFlags" value="{old_computer_sambaAcctFlags}">
				<input type="hidden" name="old_computer_description" value="{old_computer_description}">
				<input type="hidden" name="old_sambasid" value="{old_sambasid}">
				<table border="0" width=100%>			
					<tr>
						<td colspan="2" align="right" bgcolor="{color_bg1}">
							<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
							<input type="submit" value="{lang_save}" onClick="javascript:submitValues(); try_saved.value='true';">
						</td>
					</tr>

					<tr>
						<td></td>
						
						<td colspan="0">				
						
							<table border=0 width=100%>
								<tr bgcolor={row_off}>
									<td>{lang_search_organization}:</td>
									<td><input type="text" id="organization_search" autocomplete="off" size=20 onKeyUp="javascript:search_organization(this.value);"></td>
								</tr>
							
								<tr bgcolor={row_on}>
									<td>
										{lang_organizations}:
									</td>
									<td>
										<select id="ea_combo_org_info" name="sector_context" {disabled} {combo_sectors}
									</td>
								</tr>							

								<tr bgcolor={row_off}>
									<td>{lang_domain}:</td>
									<td>
										<select {disabled_samba} name="sambasid">
											{sambadomainname_options}
										</select>
									</td>
								</tr>
								
								<tr bgcolor={row_off}>
									<td width="25%">{lang_computer_uid}:</td>
									<td>
										<input name="computer_cn" size="25" value="{computer_cn}" {disabled} autocomplete="off">
									</td>
								</tr>
								
								<tr bgcolor={row_on}>
									<td>{lang_computer_type}:</td>
									<td>
										<select name="sambaAcctFlags" onBlur=javascript:hide_element(this.value)>
											<option value="[W          ]" {active_workstation_selected} onClick='javascript:hide_element(this.id)'>{lang_active_workstation}</option>
											<option value="[DW         ]" {desactive_workstation_selected} onClick='javascript:hide_element(this.id)'>{lang_desactive_workstation}</option>
											<option id="show_tr_computer_password" value="[I          ]" {trust_account_selected} onClick='javascript:hide_element(this.id)'>{lang_trust_account}</option>
											<option value="[S          ]" {server_selected} onClick='javascript:hide_element(this.id)'>{lang_server}</option>
										</select>
									</td>
								</tr>

								<tr id="tr_computer_password" bgcolor={row_on} style="{display_tr_computer_password}">
									<td width="25%">{lang_password}:</td>
									<td>
										<input type="password" name="computer_password" size="25" {disabled} autocomplete="off">
									</td>
								</tr>
								<tr bgcolor={row_on}>
									<td width="25%">{lang_description}:</td>
									<td>
										<input name="computer_description" size="60" value="{computer_description}" {disabled} autocomplete="off">
									</td>
								</tr>
							</table>
						</td>
					</tr>

					<tr>
						<td colspan="2" align="left" bgcolor="{color_bg1}">
							<input type="submit" value="{lang_save}" onClick="javascript:try_saved.value='true';">
							<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
</table>
{error_messages}
<script language="Javascript">
	function hide_element(ID)
	{
		element = document.getElementById('tr_computer_password');
		if ((ID == 'show_tr_computer_password') || (ID == '[I          ]'))
		{
			element.style.display = '';			
		}
		else
		{
			element.style.display = 'none';

		}
	}
</script>
<!-- END body -->
