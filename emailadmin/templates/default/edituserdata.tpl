<!-- BEGIN form -->
 <form method="POST" action="{form_action}">
  <center>
	<table border="0" width="95%">
		<tr>
			<td valign="top">
					{rows}
			</td>
			<td>
				<table border=0 width=100% cellspacing="0" cellpadding="2">
					<tr bgcolor="{th_bg}">
						<td colspan="2">
							<b>{lang_email_config}</b>
						</td>
						<td align="right">
							{lang_emailaccount_active}
							<input type="checkbox" name="accountStatus" {account_checked}>
						</td>
					</tr>
					<tr bgcolor="{tr_color1}">
						<td width="200">{lang_emailAddress}</td>
						<td colspan="2">
							<input name="mailLocalAddress" value="{mailLocalAddress}" size=35>
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td rowspan="4">{lang_mailAlternateAddress}</td>
						<td rowspan="4" align="center">
								{options_mailAlternateAddress}
						</td>
						<td align="center">
							<input type="submit" value="{lang_remove} -->" name="remove_mailAlternateAddress">
						</td>
					</tr>
					<tr bgcolor="{tr_color1}">
						<td>
							&nbsp;
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td align="center">
							<input name="mailAlternateAddressInput" value="{mailAlternateAddress}" size=35>
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td align="center">
							<input type="submit" value="<-- {lang_add}" name="add_mailAlternateAddress">
						</td>
					</tr>

					<tr bgcolor="{tr_color1}">
						<td>
							{lang_forward_only}
						</td>
						<td colspan="2">
							<input type="checkbox" name="forwardOnly" {forwardOnly_checked}>
						</td>
					</tr>
					
					<tr bgcolor="{tr_color2}">
						<td rowspan="4">{lang_mailRoutingAddress}</td>
						<td rowspan="4" align="center">
								{options_mailRoutingAddress}
						</td>
						<td align="center">
							<input type="submit" value="{lang_remove} -->" name="remove_mailRoutingAddress">
						</td>
					</tr>
					<tr bgcolor="{tr_color1}">
						<td>
							&nbsp;
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td align="center">
							<input name="mailRoutingAddressInput" value="{mailRoutingAddress}" size=35>
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td align="center" >
							<input type="submit" value="<-- {lang_add}" name="add_mailRoutingAddress">
						</td>
					</tr>

					<tr>
						<td colspan="3">
							&nbsp;
						</td>
					</tr>
					<tr bgcolor="{th_bg}">
						<td colspan="3">
							<b>{lang_quota_settings}</b>
						</td>
					</tr>
					<tr bgcolor="{tr_color2}">
						<td width="200">{lang_qoutainmbyte}</td>
						<td colspan="2">
							<input name="quotaLimit" value="{quotaLimit}" size=35> ({lang_0forunlimited})
						</td>
					</tr>
					<tr>
						<td colspan="3">
							&nbsp;
						</td>
					</tr>
				</table>
				<table border=0 width=100%>
					<tr bgcolor="{tr_color1}">
						<td align="right" colspan="2">
							<input type="submit" name="save" value="{lang_button}">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
  </center>
 </form>
<!-- END form -->

<!-- BEGIN link_row -->
					<tr bgcolor="{tr_color}">
						<td colspan="2">&nbsp;&nbsp;<a href="{row_link}">{row_text}</a></td>
					</tr>
<!-- END link_row -->
