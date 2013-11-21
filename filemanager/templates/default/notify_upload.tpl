<!-- BEGIN index -->

	<script src='filemanager/js/connector.js'></script>
	<script src='filemanager/js/common_functions.js'></script>
	<script src='filemanager/js/notifications.js'></script>

	<div style="margin-left:10px">
		<div style="margin-top: 10px">
			<span style="position:relative; float:left;">
				<form method="POST" action="{action_url}">
					<input type="submit" name="button_add" value="{lang_Add}" />
				</form>	
			</span>
			<span style="postion:relative;float:right;">
				<form method="POST" action="{action_url}">
					<label style="margin-left:5px;">{lang_search}.:</label>
					<input type="text" name="search_email" size="30" maxlength="30" value="{value_search_email}" />
				</form>
			</span>
		</div>
		<br/>
		<br/>
		<table style="border:0px; width:100% !important;">
			<tr class="th">
				<td align="left" width="40%">{lang_From} ( Email )</td>
				<td align="left" width="40%">{lang_To} ( Email ) </td>
				<td align="center" width="10%">{lang_Edit}</td>
				<td align="center" width="10%">{lang_Delete}</td>	
			</tr>
			{value_email_to}
		</table>
		
		<div>
			<div style="margin-top:10px; position:relative; float:left;">
				<input type="button" onClick="document.location.href='{action_url_back}'" value="{lang_Back}"/>
			</div>
			<div style="margin:10px; position:relative; float:right;">
				<form method="POST" action="{action_url}">
					<input style="display:{display_bt_previous}" type="submit" name="bt_previous" value="{lang_previous}"/>
					<input style="display:{display_bt_next}" type="submit" name="bt_next" value="{lang_next}" />					
					<input type="hidden" name="search_email" value="{value_search_email}" />
					<input type="hidden" name="button_previous" value="{value_previous}" />
					<input type="hidden" name="button_next" value="{value_next}" />
				</form>					
			</div>
		</div>
	</div>

<!-- END index -->

<!-- BEGIN AddEmail -->

	<script src='filemanager/js/connector.js'></script>
	<script src='filemanager/js/common_functions.js'></script>
	<script src='filemanager/js/notifications.js'></script>
	
	<div id="principal" style="width:35%; border:1px solid #000; margin:0 auto; padding:10px; text-align:left;">
		
		<div style="margin:5 0 15 5;">	
			<fieldset style="width:400px;">	
				<legend>{lang_legend1}</legend>
				<label>{lang_from}</label>
				<br/>
				<input id="filemanager_add_email_from" value="{value_email_from}" {attr_readonly} type="text" size="45" maxlength="50" />
			</fieldset>
		</div>
		
		<div style="margin:5px;">	
			<fieldset style="width:400px;">
				<legend>{lang_legend2}</legend>
				<label>{lang_to}</label>
				<br/>
				<input id="filemanager_add_email_to" type="text" size="45" maxlength="50" />
				<input type="button" value="{lang_Add}" onclick="notify.addEmail();" />
			</fieldset>
		</div>
		
		<div style="margin:5px;">
			<fieldset style="width:400px;">
				<legend>{lang_legend3}</legend>
				<table id="table_email_notifications" style="border:0px solid #000; width:100%;">
					<tr class="th">
						<td width="80%">{lang_Email}</td>
						<td width="20%" align="center">{lang_Excluir}</td>
					</tr>
					{value_email_to}
				</table>
			</fieldset>
		</div>

		<div style="margin: 5px">
			<input type="button" onclick="document.location.href='{action_url_back}'" value="{lang_Back}"/>
		</div>
		
	</div>
	<br/>
	<br/>
		
<!-- END AddEmail -->