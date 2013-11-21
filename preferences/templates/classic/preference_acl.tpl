{errors}
{title}
<script src='phpgwapi/templates/classic/js/listUsers.js' type='text/javascript'></script>
<form method="POST" action="{action_url}" name="formAcl">
<center>
<table border="0"
	<tr>
		<td width="10%"></td>
		<td  width="55%"><center><strong>Lista de Usu&aacute;rios</strong></center></td>		
		<td  width="35%">
			<strong>Atributos</strong>
		</td>
	</tr>
	
	<tr>
		<td></td>
		<td>
			<center>
				<select id="user_list" name="user_values" style="width:250px" size="10" onChange="javascript:execAction('LOAD')" onClick="javascript:execAction('LOAD')">{row}</select>
			</center>
		</td>			
		<td valign = 'top'> 
			<table border="0">
				<tr>
					<td><input type="checkbox" name="checkAttr" value="Y" onclick="javascript:execAction('SAVE')"></td>
					<td>Ler</td>
				</tr>
				<tr {add_invisible}>
					<td><input type="checkbox" name="checkAttr" value="Y" onclick="javascript:execAction('SAVE')"></td>
					<td>Adicionar</td>
				</tr>
				<tr>													
					<td><input type="checkbox" name="checkAttr" value="Y" onclick="javascript:execAction('SAVE')"></td>
					<td>Editar</td>				
				</tr>
				<tr>					
					<td><input type="checkbox" name="checkAttr" value="Y" onclick="javascript:execAction('SAVE')"></td>
					<td>Excluir</td>
				</tr>
				
				<tr {private_invisible}>
					<td><input type="checkbox" name="checkAttr" value="Y" onclick="javascript:execAction('SAVE')"></td>
					<td>Restritos</td>
				</tr>
				
			</table>
			
		</td>
	</tr>
	
	<tr>
		<td></td>
		<td width="40%" id="tdHiddens">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td></td>
	<td>
		{common_hidden_vars_form}
		{hiddens}
 			<input type="hidden" name="processed" value="{processed}">
 		<center>
 			<input type="submit" name="submit" value="{submit_lang}">
			<input type="button" value="Adicionar" onclick="openListUsers(340,533,'preferences')">
			<input type="button" value="Remover" onclick="javascript:remUserAcl()">
			<input type="button" value="Cancelar" onclick="javascript:history.back()">
 		</center>
	</td>
	</tr>
	
</table>
</center>
</form>
<script language="Javascript1.3">
if(!document.formAcl.user_values.length)
	for(j = 0; j < document.formAcl.checkAttr.length; j++)
		document.formAcl.checkAttr[j].disabled = true;

</script>
