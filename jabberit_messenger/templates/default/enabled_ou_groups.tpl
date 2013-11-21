<!-- BEGIN enabled_ous -->
<script type="text/javascript" src="jabberit_messenger/js/connector.js"></script>
<script type="text/javascript" src="phpgwapi/js/x_tools/xtools.js"></script>
	<form>
		<table align="center" width="90%" cellspacing="2" style="border:1px solid #000;margin-top:20px;">
			<tr class="th">
				<td width="30%"><b>Grupos<b/></td>
				<td width="55%"><b>Organizações Liberadas<b/></td>
				<td width="5%" align="center"><b>Editar</b></td>
			</tr>
			{list_groups}
		</table>
		<br/>
		<table align="center" width="90%" cellspacing="2">
			<tr>
				<td><input type="button" value="{lang_back}" onClick="document.location.href='{action_url}'"></td>
			</tr>
		</table>
	</form>
<!-- END enabled_ous -->