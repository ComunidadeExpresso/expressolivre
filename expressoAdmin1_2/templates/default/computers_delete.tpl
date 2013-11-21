<!-- BEGIN body -->
<form name="form" method="POST" action="{delete_action}">
<input type="hidden" name="computer_dn" value="{computer_dn}">
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
				{lang_do_you_really_want_delete_this_computer}?
			</font>
			<font size="5" color="red">
				({computer_cn})
			</font>
			<br>
			<font size="3" color="black">
				{computer_dn}
			</font>
			<br>
			<font size="3" color="black">
				{computer_description}
			</font>
			
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
<!-- END body -->