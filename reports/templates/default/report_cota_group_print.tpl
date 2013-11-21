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

.style6 {
	font-size: 12px;
	font-weight: bold;
	color: #0000FF;
}

</style>
 <div id="conteudo"> <!-- Elemento camufla o contéudo até que todas as tags de imagens sejam carregadas -->
<input type="hidden" id="accounts_form_imapDelimiter" value="{imapDelimiter}">
{error_messages}
<p>
   <div align="center">
		<table width="90%"  border="0">
			<tr>
			  <td colspan="2" align="left">
			  	<div align="right" style="float:right">
					<form name="back" method="post" action="./index.php?menuaction=reports.uireports_cota.report_cota_group">
			  	    	<input type="hidden" name="organizacaodn" value="{organizacaodn}">
			  	    	<input type="button" value="{lang_back}" onClick="document.back.submit()">
			      </form>
	            </div>
			  </td>
		      <td width="9%" align="left">				  
				  <div align="right" style="float:left">
					<form action="./index.php?menuaction=reports.uireports_cota.report_users_cota_print_pdf" method="post" name="pdf" target="_blank">
				      <input name="button" type="button" onClick="document.pdf.submit()" value="{lang_print}">
				      <input type="hidden" name="setor" value="{sector_name}">
				      <input type="hidden" name="setordn" value="{sector_namedn}">		
				      <input type="hidden" name="subtitulo" value="{subtitulo1}">								  	
				    </form>		      
			      </div>
		  </tr>
        </table>
		<table border="0" width="90%">
			<tr>
			  <td colspan="5" align="center"><div align="center" class="style1">{subtitulo}</div><br></td>
	      </tr>
			<tr>
			  <td colspan="5" align="center"><div align="center" class="style6">{subtitulo1}</div><br></td>
	      </tr>
			<tr>
			  <td width="25%" align="left"><div align="left" class="style2">{lang_report date}:{data_atual}</div></td>
		      <td width="60%" colspan="2" align="left">{lang_total_users}&nbsp;<strong>{cont_user}</strong>			  </td>
		      <td width="15%" colspan="2" align="left">
			  <div align="right" class="style2">{titulo}</div></td>
		  </tr>
			<tr>
			  <td colspan="5" align="left">
				<table width="100%" border="0" align="center">
					<tr bgcolor="{th_bg}">
						<td colspan="2"></td>
					</tr>
					<tr>
				  		<td width="50%">&nbsp;
						</td>
				  	    <td width="50%"><div align="right">{lang_page_now}:&nbsp;<strong>{page_now}</strong>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;{lang_total_pages}:&nbsp;<strong>{cont_page}</strong>&nbsp;&nbsp;<br></div></td>
					</tr>
			  </table>
			</td>
		  </tr>
		</table>
   </div>
 
	<div align="center">
		<table border="0" width="90%">
			<tr>
			  <td colspan="3" align="center">{pages}</td>
		  </tr>
			<tr bgcolor="{th_bg}">
				<td width="15%">{lang_loginid}</td>
				<td width="20%">{lang_name}</td>
				<td width="25%">{lang_report email}</td>				
				<td width="8%" align="center">{lang_cota}</td>
				<td width="10%" align="center">{lang_cota_used}</td>
				<td width="12%" align="center">{lang_percent_cota}</td>
				<td width="10%" align="center">{lang_status}</td>
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
		<td align="right">{mailquota}</td>
		<td align="right">{mailquota_used}</td>
		<td align="right">{percent_cota}</td>
		<td align="center">{row_status}</td>
	</tr>
<!-- END row -->
<!-- BEGIN row_empty -->
	<tr>
		<td colspan="7" align="center"><font color="red"><b>{message}</b></font></td>
	</tr>
<!-- END row_empty -->
</div>
