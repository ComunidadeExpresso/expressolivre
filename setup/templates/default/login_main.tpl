<!-- begin login_main.tpl -->
<!--<p>To enter the setup please login with the setup-password.</p>-->
<!--<table align="center" cellspacing="0" cellpadding="5" style="border: 1px solid rgb(72, 101, 145);" width="430">-->
<table align="center" cellspacing="0" cellpadding="5" style="border: 2px solid #e8f0f0;" width="450">
{V_login_stage_header}
	<tr class="row_on" >
		<td colspan="2">&nbsp;</strong></td>
	</tr>
	<tr class="th">
		<td  bgcolor="#749ece" style="color:white" colspan="2">&nbsp;<strong>{lang_header_login}</strong></td>
	</tr>
	<tr class="row_on">
		<td colspan="2" class="msg" align="center">{HeaderLoginMSG}</td>
	</tr>
	<tr class="row_on">
		<td colspan="2" align=center>
			<form action="manageheader.php" method="post" name="admin">
				<table>
					<tr>
						<td nowrap>{lang_header_username}:</td>
						<td><input type="text" name="FormUser" value=""></td>
						<td>{lang_select}</td>
					</tr>
					<tr>
						<td>{lang_header_password}:</td>
						<td><input type="password" name="FormPW" value=""></td>
					</tr>
				</table>
				<input type="submit" name="Submit" value="Login">
				<input type="hidden" name="HeaderLogin" value="Login">
			</form>
		</td>
	</tr>
</tbody>
</table>
<br>
<!-- end login_main.tpl -->
