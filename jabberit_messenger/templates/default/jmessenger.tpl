<!-- BEGIN edit_groups_jmessenger -->
<script type="text/javascript" src="jabberit_messenger/js/connector.js"></script>
<script type="text/javascript" src="phpgwapi/js/x_tools/xtools.js"></script>
<script type="text/javascript" src="jabberit_messenger/controller.php?act=j.groups_ldap"></script>

<form method="POST" action="{action_url}">
	<table align="center" width="60%" cellspacing="2" style="border: 1px solid #000000;">
		<tr class="th">
			<td colspan="2">&nbsp;<b>{lang_settings}</b></td>
		</tr>
		<tr class="row_off">
			<td colspan="2">{lang_description}</td>
		</tr>
		
		<tr class="row_on">
			<td colspan="2">
				{lang_organizations} :
				&nbsp;
				<select id="admin_organizations_ldap" serverLdap="{value_serverLdap}" name="organizations" onchange="groups_ldap.groups(this);">
					{value_ous_ldap}
				</select>
				<span id="admin_span_loading" style="color:red;visibility:hidden;">&nbsp;{lang_load}</span>
			</td>
			
		</tr>
		
		<tr class="row_off">
			<td colspan="2">
				<label>{lang_Search_quick_for} : </label>
				<input type="text" id="groups_search_quick_for" size="40" maxlength="40" onkeyup="groups_ldap.quickSearch(this, event);">
				<label id='label_groups_search_quick_for' serverLdap="{value_serverLdap}" style="color:red;"/>
			</td>
		</tr>	
		
		<tr class="row_on">
			<td colspan="2">
				<table align="center" cellspacing="0">
					<tr>
						<td class="row_off">	
							{lang_groups_ldap} :
							<br/>
							<select id="groups_ldap_jabberit" size="10" style="width: 300px" multiple></select>
						</td>
						<td class="row_off">
							<input type="button" value="{lang_add}" onclick="groups_ldap.add('groups_ldap_jabberit', 'groups_added_jabberit');" />
							<br/>
							<br/>
							<input type="button" value="{lang_remove}" onclick="groups_ldap.remove('groups_added_jabberit');" />
						</td>
						<td class="row_off">
							{lang_groups_add} :
							<br/>
							<select id="groups_added_jabberit" size="10" style="width: 300px" multiple name="groups_added_jabberit[]">
								{value_groups_added}
							</select>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<input type="hidden" name="name_serverLdap" value="{value_serverLdap}" />
				<input type="submit" name="save" value="{lang_save}" onclick="groups_ldap.selectAll('groups_added_jabberit');" />
				<input type="submit" name="cancel" value="{lang_cancel}" />
				<br>
			</td>
		</tr>
	</table>
</form>

<!-- END edit_groups_jmessenger -->