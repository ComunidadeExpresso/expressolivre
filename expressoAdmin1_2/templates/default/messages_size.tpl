<!-- BEGIN body -->
<input type="hidden" id="txt_users" value="{lang_Users}" />
<input type="hidden" id="txt_groups" value="{lang_Groups}" />

	<link rel="stylesheet" type="text/css" href="./expressoAdmin1_2/templates/default/messages_size.css">
	<div style="display:none" id="{modal_id}">{messages_size_modal}</div>

	<div align="center">
		<table border="0" width="90%">
			<tr>
				<td align="left" width="25%">
					{lang_default_size_value}
					<input type="text" id="max_size" name="max_size" value="{default_value}" size="4"> MB 
					<input type="button" value="{lang_save}" onClick="javascript:save_default_max_size(document.getElementById('max_size').value)"> <br><br>
					
					<input type="button" value="{lang_create_new_rule}" "{create_share:_account_disabled}" onClick='{onclick_create_messages_size}'>
					<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
				</td>
				<td align="center" "left" width="50%">
					{lang_contexts}: <font color="blue">{context_display}</font>
				</td>
				<td align="right" "left" width="25%">
						{lang_to_search}:
						<input type="text" onKeyUp="javascript:get_messages_size_timeOut(this.value, event)" id="ea_rules_search" autocomplete="off">
				</td>
			</tr>
		</table>
	</div>
 
	<div align="center" id="messages_size_content">
		<table border="0" width="90%">
			<tr bgcolor="{th_bg}">
				<td width="30%">{lang_rule_name}</td>
				<td width="30%">{lang_max_size_rule}</td>
				<td width="5%" align="center">{lang_remove}</td>
			</tr>
			{list_rules}
		</table>
	</div>	

	<div align="center">
		<table border="0" width="90%">
			<tr>
				<td><input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'"></td>
			</tr>
		</table>
	</div>

<!-- END body -->
