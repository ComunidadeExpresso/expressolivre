<!-- BEGIN confServersJabber -->
<script type="text/javascript" src="jabberit_messenger/js/connector.js"></script>
<script type="text/javascript" src="phpgwapi/js/x_tools/xtools.js"></script>
<script type="text/javascript" src="jabberit_messenger/controller.php?act=j.setup"></script>
<form>
<table align="center" width="60%" cellspacing="2" style="border: 1px solid #000000;">
	<tr class="th">
		<td colspan="2">&nbsp;<b>{lang_Add_Organizations_and_Servers_Jabber}</b></td>
	</tr>
	<tr class="row_on">
		<td colspan="2">
			<table width="100%;">
				<tr class="row_off">
					<td style="width:20%">
						<label>{lang_Organization}</label>
					</td>
					<td style="width:80%">
						<input id="organizationLdapJabberit" type="text" size="30" maxlength="30"/>
						<label style="font-size:7pt !important;color:red;">
							{lang_Example}&nbsp;.:&nbsp;ORGANIZACAO&nbsp;&nbsp;(ou = CIA)
						</label>
					</td>
				</tr>
				<tr class="row_off">
					<td style="width:20%">
						<label>{lang_ServerJabber}</label>
					</td>
					<td style="width:80%">
						<input id="hostNameJabberit" type="text" size="40" maxlength="40"/>
						<label style="font-size:7pt !important;color:red;">{lang_Example}&nbsp;:&nbsp;jabber.server.com</label>
					</td>
				</tr>
				<tr class="row_off">
					<td style="width:20%">
						<label>Servidor Ldap</label>
					</td>
					<td style="width:80%">
						<input id="serverLdapJabberit" type="text" size="50" maxlength="50"/>
					</td>
				</tr>
				<tr class="row_off">
					<td style="width:20%">
						<label>Contexto</label>
					</td>
					<td style="width:80%">
						<input id="contextLdapJabberit" type="text" size="50" maxlength="50"/>
					</td>
				</tr>
				<tr class="row_off">
					<td style="width:20%">
						<label>Usuário Ldap</label>
					</td>
					<td style="width:80%">
						<input id="userLdapJabberit" type="text" size="50" maxlength="50"/>
					</td>
				</tr>
				<tr class="row_off">
					<td style="width:20%">
						<label>Password</label>	
					</td>
					<td style="width:80%">
						<input id="passwordLdapJabberit" type="password" size="40" maxlength="40"/>					
					</td>
				</tr>
				<tr class="row_off">
					<td colspan="2" style="border:1px solid #00000; width:100%;">
					  <input type="button" name="add" value="{lang_save}" onclick="constructScript.setConfServerJabber();" />
					  <input type="reset" name="reset" value="{lang_new}" />
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr class="row_on">
		<td colspan="2" style="padding:5 0 5 0px;">&nbsp;<b>{lang_Registration_Organizations_and_Server_Jabber}</b></td>
	</tr>
	<tr>
		<td colspan="2">
			<table id="tableConfServersJabber" cellspacing="2" style="width:100%">
				<tr class='th'>
					<td align="left" class="row_on" style="width:40%">{lang_Organization}</td>
					<td align="left" class="row_on" style="width:40% !important">{lang_ServerJabber}</td>
					<td align="left" class="row_on" style="width:10% !important">{lang_Edit}</td>										
					<td align="left" class="row_on" style="width:10% !important">{lang_Delete}</td>
				</tr>
				{value_Organizations_Servers}
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
		  <input type="button" name="back" value="{lang_Back}" onClick="document.location.href='{action_url}'" />
		  <br/>
		</td>
	</tr>
</table>
</form>
<!-- END confServersJabber -->