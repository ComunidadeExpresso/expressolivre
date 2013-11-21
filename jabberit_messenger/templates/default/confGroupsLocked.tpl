<!-- BEGIN confGroups -->
<script type="text/javascript" src="jabberit_messenger/js/connector.js"></script>
<script type="text/javascript" src="phpgwapi/js/x_tools/xtools.js"></script>
<script type="text/javascript" src="jabberit_messenger/controller.php?act=j.setup"></script>
<form>
<table align="center" width="60%" cellspacing="2" style="border: 1px solid #000000;">
	<tr class="th">
		<td colspan="2">&nbsp;<b>{lang_Informe_as_Organizacoes}</b></td>
	</tr>
	<tr class="row_on">	
		<td colspan="2" style="padding:5 0 5 0px;">
			&nbsp;<b>&nbsp;{lang_Nome_Grupo}&nbsp;:</b><span style="font-size:12px;color:red;">&nbsp;{value_Name_Group}&nbsp;</span>
		</td>
	</tr>
	<tr class="row_on">
		<td colspan="2" style="padding:5 0 5 0px;">&nbsp;<b>&nbsp;{lang_Cadastrar_Organizacao}&nbsp;</b></td>
	</tr>
	<tr class="row_on">
		<td colspan="2">
			<table>
				<tr>
					<td colspan="3">
						<label>{lang_Organization}</label>&nbsp;.:&nbsp;
						<select id="organizations_ldap_jabberit">
				   			{value_organizations_ldap}
				   		</select>
					</td>
					<td class="row_on" style="border:1px solid #00000">
					  <input type="button" name="add" value="{lang_save}" {value_flag} onclick="constructScript.setOrgFgroups();">
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr class="row_on">
		<td colspan="2" style="padding:5 0 5 0px;">&nbsp;<b>{lang_Organizacoes_cadastradas_para_grupo}</b></td>
	</tr>
	<tr>
		<td colspan="2">
			<table id="tableOrganizationsEnabledGroupsJabberit" cellspacing="2" style="width:100%">
				<tr class='th'>
					<td align="left" class="row_on">{lang_Organization}</td>
					<td align="left" class="row_on" style="width:30% !important">{lang_Delete}</td>					
				</tr>
				{value_Groups_Organizations}
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
		  <input type="button" name="back" value="{lang_Back}" onclick="document.location.href='{action_url}'">
		  <input type="hidden" id="nameGroup" value="{value_Name_Group}"/>
		  <input type="hidden" id="gidNumber" value="{value_gidNumber}"/>
		  <br/>
		</td>
	</tr>
</table>
</form>
<!-- END confGroups -->