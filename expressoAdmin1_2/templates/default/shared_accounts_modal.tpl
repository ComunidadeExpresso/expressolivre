<input type="hidden" id="{modal_id}_title" value="{lang_creation_of_shared_accounts}">
<input type="hidden" id="{modal_id}_height" value="550">
<input type="hidden" id="{modal_id}_width" value="930">
<input type="hidden" id="{modal_id}_close_action" value="close_lightbox()">
<input type="hidden" id="{modal_id}_create_action" value="create_shared_accounts()">
<input type="hidden" id="{modal_id}_save_action" value="save_shared_accounts()">
<input type="hidden" id="{modal_id}_onload_action" value="set_onload({manager_context})">
<input type="hidden" id="{modal_id}_alternate_mails" value="{alternate_mails}">
<form enctype="multipart/form-data" name="shared_accounts_form_template" method="post">
<input type="hidden" id="anchor" name="anchor">
<input type="hidden" id="owners_acls" name="owners_acl" value="">
<input type="hidden" id="owners_calendar_acls" name="owners_calendar_acl" value="">
<input type="hidden" id="uidnumber" name="uidnumber" value="">
<table border="0" cellspacing="4">
	<tr>
		<td width="35%" bgcolor="#DDDDDD">
			<div style="line-height: 220%">{lang_search_organization}:
			<input type="text" id="organization_search" autocomplete="off" size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org');" onBlur="javascript:sinc_combos_org(context.value);"><br>
			
			{lang_organization}:
			<select id="ea_combo_org" name="context" onchange="javascript:sinc_combos_org(this.value);javascript:get_associated_domain(this.value);">{manager_organizations}</select><br>
			
			<input type="hidden" id="associated_domain" name="associated_domain">
			{lang_full_name}: 
			<input id="cn" name="cn" size="36" autocomplete="off"><br>
			{lang_mail}: 
			<input id="mail" name="mail" onKeyUp='javascript:emailSugestion_expressoadmin2(this)' size="45" autocomplete="off"><br>
			<input type="button" value="{lang_add_alternative_mail}" onclick="addTextbox( 'mailalternateaddress[]', 'mailalternateaddress' );">
			<div style="overflow: auto; height: 40px; line-height: 100%;" id="mailalternateaddress">
<!-- 			<input id="mailalternateaddress" name="mailalternateaddress[]" size="45" autocomplete="off"><span onclick="appendClone('mailalternateaddress');" style="cursor: pointer;"> +</span> -->
			</div>
			{lang_description}:
			<input id="desc" name="desc" size="42" autocomplete="off"><br>
                        {lang_Email_quota_in_MB}:
                        <input type="text" id="mailquota" name="mailquota" autocomplete="off" value="{mailquota}" {changequote_disabled} {disabled} size=16><br>
                        <div  id='quota_used_field' name='quota_used_field' style="display:{display_quota_used}">{lang_quota_used_in_mb}:
                        <input type="text" name="mailquota_used" id="mailquota_used" value="{mailquota_used}" disabled size=10></div>
                        <div id='display_empty_inbox' name='display_empty_inbox' style="display:none"><input type='button' {disabled} {disabled_empty_inbox} value='{lang_empty_inbox}' onclick="javascript:empty_inbox(anchor.value);"></div>
							
			{lang_is_account_active}: <input type="checkbox" id="accountStatus" name="accountStatus" checked><!--<br>-->
			{lang_omit_account_from_the_catalog}: <input type="checkbox" id="phpgwAccountVisible" name="phpgwAccountVisible"></div>
							
			<b>{lang_owners}:</b><br>
			<select style="width:350px; height:170px" id="ea_select_owners" onchange="sharemailbox.getaclfromuser(this.value);" name="owners[]" multiple size="13"></select>
                      
		</td>
						
		<td width="15%" valign="bottom" align="center" bgcolor="#DDDDDD" style="padding-bottom: 5px;">
		    <input type="hidden" id="sharedAccountsLocation" value="{sharedAccountsLocation}" />
                    <table align="center" style="display: {aclCalendar}">
                        <tbody>
                                <tr>
                                    <td colspan="2" width="125"><b>{lang_calendar}: {calendarName}</b></td>
                                </tr>
                                <tr>
                                    <td>{lang_read}</td><td><input type="checkbox" name="checkAttr" id ="em_input_readCalendar" onclick="return sharemailbox.setCalendaraclfromuser();" /><img title="{lang_this_user_will_can_read_events_at_the_mailbox_calendar}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg"></td>
				</tr>
				<tr >
                                    <td>{lang_add}</td><td><input type="checkbox" name="checkAttr" id ="em_input_writeCalendar" onclick="return sharemailbox.setCalendaraclfromuser();" /><img title="{lang_this_user_will_can_create_events_at_the_mailbox_calendar}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg"></td>
				</tr>
				<tr>
                                    <td>{lang_edit}</td><td><input type="checkbox" name="checkAttr" id ="em_input_editCalendar" onclick="return sharemailbox.setCalendaraclfromuser();" /><img title="{lang_this_user_will_can_edit_events_at_the_mailbox_calendar}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg"></td>
				</tr>
				<tr>
                                    <td>{lang_exclusion}</td><td><input type="checkbox" name="checkAttr" id ="em_input_deleteCalendar" onclick="return sharemailbox.setCalendaraclfromuser();" /><img title="{lang_this_user_will_can_delete_events_at_the_mailbox_calendar}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg"></td>
                                </tr>
				<tr >
                                    <td>{lang_restrict}</td><td><input type="checkbox" name="checkAttr" id ="em_input_restrictCalendar" onclick="return sharemailbox.setCalendaraclfromuser();" /><img title="{lang_this_user_will_can_read_restrict_events_at_the_mailbox_calendar}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg"></td>
				</tr>
                            </tbody>
                    </table>
                    <table align="center" style="display: {aclExpressoCalendar}">
                        <tbody>
                                <tr>
                                    <td colspan="2" width="125"><b>{lang_calendar}: {calendarName}</b></td>
                                </tr>
                                <tr>
                                    <td>{lang_read}</td><td><input type="checkbox" name="checkAttr" id ="em_input_readExpressoCalendar" onclick="return sharemailbox.setExpressoCalendaraclfromuser();" /><img title="{lang_this_user_will_can_read_events_at_the_mailbox_calendar}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg"></td>
				</tr>
				<tr >
                                    <td>{lang_write}</td><td><input type="checkbox" name="checkAttr" id ="em_input_writeExpressoCalendar" onclick="return sharemailbox.setExpressoCalendaraclfromuser();" /><img title="{lang_this_user_will_can_create_events_at_the_mailbox_calendar}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg"></td>
				</tr>
				<tr>
                                    <td>{lang_exclusion}</td><td><input type="checkbox" name="checkAttr" id ="em_input_deleteExpressoCalendar" onclick="return sharemailbox.setExpressoCalendaraclfromuser();" /><img title="{lang_this_user_will_can_delete_events_at_the_mailbox_calendar}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg"></td>
                                </tr>
                                <tr >
                                    <td>{lang_freebusy}</td><td><input type="checkbox" name="checkAttr" id ="em_input_freebusyExpressoCalendar" onclick="return sharemailbox.setExpressoCalendaraclfromuser();" /><img title="{lang_this_user_will_can_read_restrict_events_at_the_mailbox_calendar}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg"></td>
				</tr>
                            </tbody>
                    </table>
                    <br />
                    <table align="ce" class="shared-permissions">
                    	<tbody>
                    		<tr>
                    			<td colspan="2" width="125">
                    				<b>{lang_Rights}:</b>
                    			</td>
                    		</tr>
	                    	<tr>
	                    		<td>{lang_read}:</td>
	                    		<td>
	                    			<input id="em_input_readAcl" onclick="return sharemailbox.setaclfromuser();" type="checkbox" class="shared-required">
	                    			<img title="{lang_this_user_will_can_read_messages}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg">
	                    		</td>
	                    	</tr>
	                    	<tr>
	                    		<td>
	                    			{lang_exclusion}:
	                    		</td>
	                    		<td>
	                    			<input disabled="disabled" id="em_input_deleteAcl" onclick="return sharemailbox.setaclfromuser();" type="checkbox" class="shared-other">
	                    			<img title="{lang_this_user_will_can_delete/move_messages}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg">
	                    		</td>
	                    	</tr>
	                    	<tr>
	                    		<td>
	                    			{lang_creation}:
	                    		</td>
	                    		<td>
	                    			<input disabled="disabled" id="em_input_writeAcl" onclick="return sharemailbox.setaclfromuser();" type="checkbox" class="shared-other">
	                    			<img title="{lang_this_user_will_can_create/add_messages}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg">
	                    		</td>
	                    	</tr>
	                    	<tr>
	                    		<td>
	                    			{lang_send}:
	                    		</td>
	                    		<td>
	                    			<input disabled="disabled" id="em_input_sendAcl" onclick="return sharemailbox.setaclfromuser();" type="checkbox" class="shared-other">
	                    			<img title="{lang_this_user_will_can_send_messages}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg">
	                    		</td>
	                    	</tr>
	                    	<tr>
	                    		<td>
	                    			{lang_folder}:
	                    		</td>
	                    		<td>
	                    			<input disabled="disabled" id="em_input_folderAcl" onclick="return sharemailbox.setaclfromuser();" type="checkbox" class="shared-other">
	                    			<img title="{lang_allow_create_or_delete_folders_on_this_mailbox}." src="./expressoAdmin1_2/templates/default/images/ajuda.jpg">
	                    		</td>
	                    	</tr>
                    	</tbody>
                	</table>
                <br />
			<button id="bt_add_user" type="button" onClick="javascript:add_user();"><img src="expressoAdmin1_2/templates/default/images/add.png" style="vertical-align: middle;">&nbsp;{lang_add_owner}</button>
			<br /><br />
			<button id="bt_remove_user" type="button" onClick="javascript:remove_user();"><img src="expressoAdmin1_2/templates/default/images/rem.png" style="vertical-align: middle;">&nbsp;{lang_remove_owner}</button><br><br>
		</td>
						
		<td width="25%" valign="bottom" bgcolor="#DDDDDD">
			{lang_search_organization}:<br>
			<input type="text" id="organization_search" autocomplete="off" size=20 onKeyUp="javascript:search_organization(this.value, 'ea_combo_org_available_users');" >
			<br>
							
			{lang_organizations}:<br>
			<select name="org_context" id="ea_combo_org_available_users" >{all_organizations}</select>
			<br>
			<br><br>
							
			{lang_search_user}:<br>
			<input id="ea_input_searchUser" size="35" autocomplete="off" onkeyup="javascript:optionFinderTimeout(this, event)"><br>
							
			<font color="red"><span id="ea_span_searching">&nbsp;</span></font>
			<br>
			<b>{lang_users}:</b><br>
<!-- 			<div style="overflow: scroll; width:350px; height:160px" > -->
			<select id="ea_select_available_users" style="overflow: scroll; width:350px; height:160px" multiple size="13" ></select>
<!-- 			</div> -->
		</td>
	</tr>
</table>

</form>
