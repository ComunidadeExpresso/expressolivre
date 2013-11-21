<!-- BEGIN module -->
<form method="POST" action="{action_url}">
<table align="center" width="60%" cellspacing="2" style="border: 1px solid #000000;">
	<tr class="th">
		<td colspan="2">&nbsp;<b>{lang_Enable_the_Expresso_Messenger_module}</b></td>
	</tr>
	<tr class="row_on">
		<td style="height:30px;">&nbsp;{lang_Select_the_modules_where_the_Expresso_Messenger_will_be_loaded}</td>
	</tr>
	<tr class="row_off">
		<td colspan="2">
			<table align="center" cellspacing="0" style="margin-top:15px">
				<tr>
					<td class="row_off">	
						{lang_Modules_List} :
						<br/>
						<select id="apps_list" size="10" style="width: 300px" multiple>{apps_list}</select>
					</td>
					<td class="row_off">
						<input type="button" value="Adicionar" onclick="javascript:App.add();" />
						<br/>
						<br/>
						<input type="button" value="Remover" onclick="javascript:App.remove();" />
					</td>
					<td class="row_off">
						{lang_Modules_Enabled} :
						<br/>
						<select id="apps_enabled" size="10" style="width: 300px" multiple name="apps_enabled[]">{apps_enabled}</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
		  <input type="submit" name="save" value="{lang_save}" onclick="javascript:App.select_();">
		  <input type="submit" name="cancel" value="{lang_cancel}">
		  <br>
		</td>
	</tr>
</table>
</form>
<!-- END module -->