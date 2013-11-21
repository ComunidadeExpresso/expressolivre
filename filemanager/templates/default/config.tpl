<!-- BEGIN header -->
<script src='filemanager/inc/load_lang.php'></script>
<script src='filemanager/js/connector.js'></script>
<script src='filemanager/js/common_functions.js'></script>

<form method="POST" action="{action_url}">
	<table border="0" align="center">
		<tr bgcolor="{th_bg}">
			<td colspan="2">
				<font color="{th_text}">&nbsp;<b>{title}</b></font>
			</td>
		</tr>
<!-- END header -->
<!-- BEGIN body -->
		<tr bgcolor="{row_on}">
			<td>{lang_Max_file_size}</td>
			<td>
				<input size="3" name="newsettings[filemanager_Max_file_size]" value="{value_filemanager_Max_file_size}">&nbsp;Mb
			</td>
		</tr>
		<tr bgcolor="{row_off}">
			<td>{lang_quota_size}</td>
			<td>
				<input size="3" name="newsettings[filemanager_quota_size]" value="{value_filemanager_quota_size}">&nbsp;Mb
			</td>
		</tr>
		<tr bgcolor="{row_on}">
			<td>{lang_antivirus_command}</td> 
			<td>
				<input size="40" name="newsettings[filemanager_antivirus_command]" value="{value_filemanager_antivirus_command}">
			</td>
		</tr>

<!-- END body -->
<!-- BEGIN footer -->
		<tr bgcolor="{th_bg}">
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<input type="button" value="{lang_submit}" onclick="maxFileSize(document);">
				<!--
					No MSIE utilizar um botão do tipo submit não permite que seja gravado o arquivo e configuração.
					Por esse motivo o envio do formulário está sendo feito através do javascript.
					NÃO REMOVA...			 
				-->
				<input type="submit" name="submit" style="display: none">
				<input type="submit" name="cancel" value="{lang_cancel}">
			</td>
		</tr>
	</table>
</form>
<!-- END footer -->
