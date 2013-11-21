<!-- BEGIN body -->
<style type="text/css">
.myBox
{
    margin: 24px 0px 0px 0px;
    border: 0px solid #ffffff;
    color: #000000;
    width: 99%;
    padding: 20px;
    text-align: left;
    background-color: #ffffff;
    border: 3px solid #ffffff;


}

.myBox2
{
    margin: 24px 0px 0px 0px;
    border: 0px solid #ffffff;
    width: 99%;
    padding: 5px;
    text-align: left;
    background-color: #ECECE5;
    border: 3px solid #ECECE5;
}

#loader{
position:absolute;
width:100%;
height:600px;
}

.info{
width:100px;
float:left;
margin-right:25%;
margin-top: 300px;
}

.style1 {
	font-size: 20px;
	font-weight: bold;
	color: #0000FF;
}
.style2 {
	font-size: 18px
	font-weight: bold;
}
.style4 {color: #000000}
</style>
 <div id="conteudo"> <!-- Elemento camufla o contéudo até que todas as tags de imagens sejam carregadas -->
<input type="hidden" id="accounts_form_imapDelimiter" value="{imapDelimiter}">
{error_messages}
<p>
   <div align="center">
		<table border="0" width="90%">
			<tr>
			  <td colspan="2" align="left">
				<div align="left" class="style1">{lang_reports title1}:&nbsp;{context_display}</div>
				<br>
		      </td>
			  <td width="28%" align="left">
			  	<div align="right">
				<input type="button" value="{lang_back}" onClick="document.location.href='{back_url}'">
				&nbsp;
				<input type="button" value="Imprimir" onClick="document.location.href='./index.php?menuaction=reports.uireports.report_users_print_pdf'">
			    </div></td>
			</tr>
			<tr>
			  <td width="26%" align="left"><div align="left" class="style2">{lang_report date}:{data_atual}</div></td>
		      <td align="left">{lang_total_users}&nbsp;<strong>{cont_user}</strong>			  </td>
		      <td width="28%" align="left">
			  <div align="right" class="style2">{titulo}</div></td>
		  </tr>
			<tr>
			  <td colspan="3" align="left">
				<table width="100%" border="0" align="center">
					<tr bgcolor="{th_bg}">
						<td colspan="2"></td>
					</tr>
					<tr>
				  		<td width="50%">&nbsp;
						</td>
				  	    <td width="50%"><div align="right">{lang_page_now}:&nbsp;<strong>{page_now}</strong>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;{lang_total_pages}:&nbsp;<strong>{cont_page}</strong>&nbsp;&nbsp;<br></div></td>
					</tr>
					<tr>
						<td colspan="2"></td>
				  </tr>
			  </table>			</td>
		  </tr>
		</table>
   </div>
 
	<div align="center">
		<table border="0" width="90%">
			<tr bgcolor="{th_bg}">
				<td width="20%">{lang_loginid}</td>
				<td width="30%">{lang_name}</td>
				<td width="30%">{lang_report email}</td>
			</tr>
			{rows}
		</table>
	</div>
<!-- END body -->
<!-- BEGIN rowpag -->
	<b>{paginat}</b>
<!-- END rowpag -->
<!-- BEGIN row -->
	<tr bgcolor="{tr_color}">
		<td>{row_loginid}</td>
		<td>{row_cn}</td>
		<td>{row_mail}</td>
	</tr>
<!-- END row -->
<!-- BEGIN row_empty -->
	<tr>
		<td colspan="7" align="center"><font color="red"><b>{message}</b></font></td>
	</tr> 
	<!-- END row_empty -->
</div>
