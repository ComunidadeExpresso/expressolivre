<!-- BEGIN body -->
	<link rel="stylesheet" type="text/css" href="./expressoAdmin1_2/templates/default/institutional_accounts.css">
	<div style="display:none" id="{modal_id}">{institutional_accounts_modal}</div>

	<div align="center">
		<table border="0" width="90%">
			<tr>
				<td align="left" width="25%">
					<input type="button" value="{lang_create_institutional_account}" "{create_institutional_account_disabled}" onClick='{onclick_create_institutional_account}'>
					<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
				</td>
				<td align="center" "left" width="50%">
					{lang_contexts}: <font color="blue">{context_display}</font>
				</td>
				<td align="right" "left" width="25%">
						{lang_to_search}:
						<input type="text" onKeyUp="javascript:get_institutional_accounts_timeOut(this.value, event)" id="ea_institutional_account_search" autocomplete="off" value="{query}">
				</td>
			</tr>
		</table>
	</div>
 
	<div align="center" id="institutional_accounts_content">
		<table border="0" width="90%">
			<tr bgcolor="{th_bg}">
				<td width="30%">{lang_full_name}</td>
				<td width="30%">{lang_mail}</td>
				<td width="5%" align="center">{lang_delete}</td>
			</tr>
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