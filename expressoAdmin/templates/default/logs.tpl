<!-- BEGIN list -->
<p>
	<div align="center">
		<form method="POST" action="{accounts_url}">
			<table border="0" width="90%">
				<tr>
					<td><input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'"></td>
				</tr>

				<tr>
					<td align="left" width="20%">
						{lang_manager}:
					</td>
					<td align="left" width="80%">
						<input type="text" name="query_manager_lid" autocomplete="off" value="{query_manager_lid}">
					</td>
				</tr>
				<tr>
					<td align="left" width="10%">
						{lang_action}:
					</td>
					<td align="left" width="80%">
						<input type="text" name="query_action" autocomplete="off" value="{query_action}">
					</td>
				</tr>
				<tr>
					<td align="left" width="10%">
						{lang_date}:
					</td>
					<td align="left" width="80%">
						<input type="text" name="query_date" autocomplete="off" value="{query_date}">
						(dd/mm/aaaa)
					</td>
				</tr>				
				<tr>
					<td align="left" width="10%">
						{lang_hour}:
					</td>
					<td align="left" width="80%">
						<input type="text" name="query_hour" autocomplete="off" value="{query_hour}">
						(hh:mm)
					</td>
				</tr>				
				<tr>
					<td align="left" width="10%">
						{lang_other}:
					</td>
					<td align="left" width="80%">
						<input type="text" name="query_other" autocomplete="off" value="{query_other}">
					</td>
				</tr>
				<tr>
					<td>
						<input type="submit" value="{lang_search}">
					</td>
				</tr>				
			</table>
		</form>
	</div>
	
	<div align="center">
		<table border="0" width="90%">	
			<tr bgcolor="{th_bg}">
				<td width="10%" align="center">{lang_date}</td>
				<td width="10%" align="center">{lang_manager}</td>
				<td width="35%" align="center">{lang_action}</td>
				<td width="35%" align="center">{lang_about}</td>
			</tr>
			{rows}
		</table>
	</div>
<!-- END list -->
<!-- BEGIN row -->
	<tr bgcolor="{tr_color}">
		<td width="10%" align="center" NOWRAP>{row_date}</td>
		<td width="10%" align="center">{row_manager_lid}</td>
		<td width="35%" align="left">{row_action}</td>
		<td width="5%" align="center">{row_about}</td>
	</tr>
<!-- END row -->

<!-- BEGIN row_empty -->
	<tr>
		<td colspan="5" align="center"><font color="red"><b>{message}</b></font></td>
	</tr>
	<tr>
		<td><input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'"></td>
	</tr>
<!-- END row_empty -->