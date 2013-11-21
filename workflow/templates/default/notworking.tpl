{$header}
<div id="main_body" style="width:99.5%;">
	<table id="border_table" width="auto" height="26" cellspacing="0" cellpadding="0" border="0" align="center">
		<tbody>
			<tr>
				<td width="100%" align='center' height="100%" valign="center">
				<div style="background-color:white;height:150px;padding:20px;width:80%;border:1px dashed black"><br>
					<img align="left" src="templates/default/images/notworking.png" style="margin-right:15px;">
					<br>
					<p style="color:darkblue;text-align:justify;font-size:12px">Caro Usu&aacute;rio, <br><br>O <u>m&oacute;dulo workflow</u> do Expresso encontra-se temporariamente indispon&iacute;vel. Por favor, retorne mais tarde.</p>
					<div style="color:darkblue;font-size:12px" align="right"><b>Suporte Expresso Livre</b></div>
				</div>
				{foreach item=msg from=$errors}
					{$msg}<br>
				{/foreach}
				</td>
			</tr>
		</tbody>
	</table>
</div>
{$footer}
