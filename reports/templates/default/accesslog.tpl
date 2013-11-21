<!-- BEGIN body -->
<p>
	<div align="center">
		<table border="0" width="100%">
			<tr>
				<td align="left" width="12%">{lang_name}.:&nbsp;<strong>{nome_usuario}</strong></td>
			    <td align="left" width="13%"><div align="right">
			      {back_url}
		        </div></td>
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
		<td colspan="7" align="right">
			{back_url}
		</td>
	</tr>
