<!-- BEGIN body -->
<input type="hidden" id="accounts_form_imapDelimiter" value="{imapDelimiter}">
{error_messages}
<p>
	<div align="center">
		<table border="0" width="90%">
			<tr>
				<td align="left" width="25%">
					<form name="form" method="POST" action="{add_action}">
						<input type="submit" value="{lang_create_user}" "{create_user_disabled}">
						<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
					</form>
				</td>
				<td align="center" "left" width="50%">
					{lang_contexts}: <font color="blue">{context_display}</font>
				</td>
				<td align="right" "left" width="25%">
					<form method="POST" action="{accounts_url}">
						{lang_search}:
						<input type="text" name="query" autocomplete="off" value="{query}">
					</form>
				</td>
			</tr>
		</table>
	</div>
 
	<div align="center">
		<table border="0" width="90%">
			<tr bgcolor="{th_bg}">
				<td width="20%">{lang_loginid}</td>
				<td width="30%">{lang_name}</td>
				<td width="30%">{lang_mail}</td>
				<td width="5%" align="center">{lang_edit}</td>
				<td width="5%" align="center">{lang_rename}</td>
				<td width="5%" align="center">{lang_to_delete}</td>
			</tr>
			{rows}
			<tr>
				<td><input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'"></td>
			</tr>
		</table>
	</div>
<!-- END body -->
<!-- BEGIN row -->
	<tr bgcolor="{tr_color}">
		<td>{row_loginid}</td>
		<td>{row_cn}</td>
		<td>{row_mail}</td>
		<td width="5%" align="center">{row_edit}</td>
		<td width="5%" align="center">{row_rename}</td>
		<td width="5%" align="center">{row_delete}</td>
	</tr>
<!-- END row -->

<!-- BEGIN row_empty -->
	<tr>
		<td colspan="7" align="center"><font color="red"><b>{message}</b></font></td>
	</tr>
<!-- END row_empty -->
