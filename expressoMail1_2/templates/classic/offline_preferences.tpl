<div style="display:none" id="loading">
	<table width="100%" height="100%">
		<tr>
			<td background="js/modal/images/fundo_exp.png" valign="top" align="center">
				<font color="#006699"><b><div id="text_loading">Instalando módulo offline... aguarde!!</div></b></font>
			</td>
		</tr>
	</table>
</div>
<input type="hidden" id="lang_gears_redirect" value="{lang_gears_redirect}" />
<input type="hidden" id="lang_offline_installed" value="{lang_offline_installed}" />
<input type="hidden" id="lang_offline_uninstalled" value="{lang_offline_uninstalled}" />
<input type="hidden" id="lang_only_spaces_not_allowed" value="{lang_only_spaces_not_allowed}"/>


<script src="js/main.js"></script>
<script src="js/gears_init.js"></script>
<script src="js/local_messages.js"></script>
<script src="js/modal/modal.js"></script>
<script src="js/md5.js"></script>

<script language="javascript">
	var close_lightbox_div = true;

</script>
<center>

<table width="50%" border="0" cellspacing="2" cellpadding="2">
	<tr bgcolor="{th_bg}">
        <td colspan="2" align="center">{lang_expresso_offline}</td>
    </tr>
	<tr bgcolor="{tr_color1}">
        <td width="25%">
			{lang_pass_offline}:
		</td>
        <td>
        	<input type="password" name="offline_pass" id='offline_pass'>
        </td>
    </tr>
    <tr>
    	<td colspan="2">
			<table width="100%" border="0" cellspacing="2" cellpadding="2">
	    		<tr>
	        		<td align="center" >
	        			<input type="button" name="install_off" value="{lang_install_offline}" onclick="close_lightbox_div=true;expresso_local_messages.install_offline('{url_offline}','{url_icon}','{user_uid}','{user_login}',MD5(document.getElementById('offline_pass').value),'{go_back}');">
	        		</td>
	        		<!--td align="right" width="50%">
	        			<input type="button" name="install_off" value="{lang_uninstall_offline}" onclick="expresso_local_messages.uninstall_offline()">
	        		</td-->
	        	</tr>
			</table>
		</td>
	</tr>
</table>
</center>
