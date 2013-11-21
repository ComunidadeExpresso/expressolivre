<script language="javascript">
	var account_id = {account_id};
</script>
{libs}

<center>
<form name="formu" method="POST" action="{save_action}" id="form_auto_arch">
<input type="hidden" name="save" value="save">
<table width="500" border="0" cellspacing="2" cellpadding="2">
	<tr bgcolor="{th_bg}">
		<td colspan="3" align="center"> 
			{lang_folders_to_sync}
		</td>
	</tr>
	
	 <tr bgcolor="{tr_color1}">
		<td width="25%" bgcolor="#DDDDDD">
			<select id="combo_sync_folders" name="folders_1" style="width: 200px" multiple size="13"></select>
		</td>
		
		<td valign="middle" align="center" bgcolor="#DDDDDD">
			<button type="button" onClick="javascript:expresso_mail_sync.add_folder(document.getElementById('combo_sync_folders'),document.getElementById('combo_all_folders'));"><img src="templates/default/images/add.png" style="vertical-align: middle;" >&nbsp;{lang_add}</button>
			<br><br>
			<button type="button" onClick="javascript:expresso_mail_sync.remove_folder(document.getElementById('combo_sync_folders'));"><img src="templates/default/images/rem.png" style="vertical-align: middle;" >&nbsp;{lang_rem}</button>
		</td>
		
		<td valign="bottom" bgcolor="#DDDDDD">
			<select id="combo_all_folders" name="folders_2" style="width: 200px" multiple size="13">{all_folders}</select>
		</td>
	</tr>
	<tr>
		<td>{lang_keep_messages_on_server?}</td>
		<td>
		
		</td>
	</tr>
	<tr bgcolor="{tr_color2}" >
        <td colspan="2">{lang_Would_you_like_to_keep_messages_on_server_?}</td>
        <td >
        	<select name="keep_after_auto_archiving" id="remove_after_auto_archiving" >
				<option {keep_after_auto_archiving_No_selected} value="0">{lang_No}</option>
				<option {keep_after_auto_archiving_Yes_selected} value="1">{lang_Yes}</option>
			</select>
        </td>
    </tr>
	 <tr>
    	<td colspan="3">
			<table width="100%" border="0" cellspacing="2" cellpadding="2">
	    		<tr>
	        		<td align="center" >
	        			<input type="button" name="install_off" value="{lang_save}" onclick="close_lightbox_div=true;expresso_mail_sync.configure_sync(document.getElementById('combo_sync_folders').options,document.getElementById('form_auto_arch'));">
	        		</td>
	        	</tr>
			</table>
		</td>
	</tr>
	
</table>
</form>
<script language="javascript">
	expresso_mail_sync.fill_combos_of_folders(document.getElementById('combo_sync_folders'));
</script>
</center>
