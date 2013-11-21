<!-- BEGIN header -->
<form  method="POST" action="{action_url}">
<input type="hidden" name="migration" value="true">
<table border="0" align="center">
	<tr class="th">
		<td colspan="2"><font color="{th_text}">&nbsp;<b>{title}</b></font></td>
	</tr>
	<tr>
   		<td></td>
	</tr>
	<tr>
		<td colspan="2"><b>{error}</b></td>
	</tr>
   <tr>
   	<td></td>
   </tr>
<!-- END header -->
<!-- BEGIN body -->
	<tr class="th">
		<td colspan="2" align="center"><b>{lang_expressoCalendar_migration}</b></td>
	</tr>
	<tr>
   		<td></td>
	</tr>
	<tr class="row_on">
		<td>{lang_Migrate_all_calendar_events_for_expressoCalendar}:</td>
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
