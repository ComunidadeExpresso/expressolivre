<!-- BEGIN list -->
<form name="form" method="POST" action="{action}">
<input type="hidden" name="dn" value="{dn}">
<input type="hidden" name="manager_context" value="{manager_context}">
<table border="0" width="80%" align="center">
	<tr>
		<td colspan="2" align="right" bgcolor="{color_bg1}">
			<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
			<input type="submit" value="{lang_delete}">
		</td>
	</tr>

	<tr>
		<td align="center">
			<font size="5">
				{lang_do_you_really_want_delete_this_sector}?
			</font>
			<font size="5" color="red">
				({sector_name})
			<font size="5">
			<br>
		</td>
	</tr>
	<tr>
		<td align="left">
			<font size="3" color="red">
				{lang_all_users_groups_and_subsectors_from_this_sector_will_be_deleted}!
			</font>
		</td>
	</tr>
	<tr>
		<td align="left">
			<font size="3">
				{lang_users_from_the_sector}:
			</font>
				<br>
				{users_list}
				<br>	
		</td>
	</tr>
	
	<tr>
		<td align="left">
			<font size="3">
				{lang_groups_from_the_sector}:
			</font>
				<br>
				{groups_list}
				<br>	
		</td>
	</tr>

	<tr>
		<td align="left">
			<font size="3">
				{lang_subsectors_from_the_sector}:
			</font>
				<br>
				{sectors_list}
				<br>
		</td>
	</tr>

	<tr>
		<td colspan="2" align="left" bgcolor="{color_bg1}">
			<input type="submit" value="{lang_delete}">
			<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">			
		</td>
	</tr>	
</table>
</form>
<!-- END list -->