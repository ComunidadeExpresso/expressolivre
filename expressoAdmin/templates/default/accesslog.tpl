<!-- BEGIN body -->

<p>
	<div align="center">
		<table border="0" width="100%">
			<tr>
				<td align="left" width="25%">
					<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
				</td>
			</tr>
		</table>
	</div>
 
	<div align="center">
		<table width="100%">
			<tr bgcolor="{th_bg}" align="center">
				<td width="20%">{lang_loginid}</td>
				<td width="20%">{lang_ip_address}</td>
				<td width="30%">{lang_login}</td>
				<td width="30%">{lang_logout}</td>
			</tr>
			
			{rows}
			
<!-- END body -->
<!-- BEGIN row -->
	<tr>
		<td style="border-bottom:1px solid black">{row_loginid}</td>
		<td style="border-bottom:1px solid black">{row_ip}</td>
		<td style="border-bottom:1px solid black">{row_li}</td>
		<td style="border-bottom:1px solid black">{row_lo}</td>
	</tr>
<!-- END row -->

<!-- BEGIN row_empty -->
	<tr>
		<td colspan="7" align="center"><font color="red"><b>{message}</b></font></td>
	</tr>
<!-- END row_empty -->

	<tr>
		<td>
			<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
		</td>
	</tr>
