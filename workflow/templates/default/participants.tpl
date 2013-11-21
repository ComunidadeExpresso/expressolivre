{$header}
{$javaScripts}
{literal}
<style>
#divPoweredBy
{
   	display: none;
}
</style>
{/literal}
<input type="hidden" value="" id="txt_loading"/>
<input type="hidden" id="entities" value="{$entities}"/>
<input type="hidden" id="id" value="{$id}"/>
<input type="hidden" id="target" value="{$target}"/>
<input type="hidden" id="usePreffix" value="{$usePreffix}"/>
<input type="hidden" id="useCCParams" value="{$useCCParams}"/>
<table border="0" width="400">
{if !($hideOrganizations && $hideSectors)}
	<tr id="organizationSectors">
		{if !$hideOrganizations}
		<td {if $hideSectors} colspan="2"{/if}>
			{html_options values=$organizations|upper output=$organizations selected=$selectedOrganization|upper name="organization" id="organization"}
		</td>
		{/if}
		{if !$hideSectors}
		<td {if $hideOrganizations}colspan="2"{else}style="text-align: right;"{/if}>
			{html_options options=$sectors name="sector" id="sector"}
		</td>
		{/if}
	</tr>
{/if}
	<tr id="globalSearchTitle" style="display: none;">
		<td colspan="2">
			<p style="text-align: center; font-weight: bold; font-size: 130%;">Busca Global</p>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<label><input type="checkbox" name="onlyVisibleAccounts" id="onlyVisibleAccounts" value="true" {if $onlyVisibleAccounts} checked {/if}/> Exibir apenas contas vis&iacute;veis</label>
		</td>
	</tr>
	{if !($hideOrganizations && $hideSectors)}
	<tr>
		<td>
			<label><input type="checkbox" name="useGlobalSearch" id="useGlobalSearch" {if $useGlobalSearch} checked {/if}/> Utilizar busca global</label>
		</td>
		<td>
			<p id="globalSearchWarnings" style="color: #FF0000; text-align: right;"></p>
		</td>
	</tr>
	{/if}
	<tr>
		<td colspan="2">
			<input type="text" name="search" id="search" value="" style="width: 480px;" autocomplete="off"/>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table border="0">
				<tr>
					<td>
						{if !($hideOrganizations && $hideSectors)}
						{html_options options=$participants name="participants" id="participants" multiple="multiple" style="height: 210px; width: 425px;"}
						{else}
						{html_options options=$participants name="participants" id="participants" multiple="multiple" style="height: 260px; width: 425px;"}
						{/if}
					</td>
					<td>
						<a id="addUserLink"><img src="workflow/templateFile.php?file=images/add_user_big.png" alt="Adicionar" title="Adicionar" id="addUser" style="cursor: pointer;"/></a>
						<br/><br/><br/>
						<a id="exitLink"><img src="workflow/templateFile.php?file=images/exit_big.png" alt="Sair" title="Sair" id="exit" style="cursor: pointer;"/></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

	{*<title>{lang_Add_Participants}</title>
	<script src="{js_participant_file}" type="text/javascript"></script>
	<form name="frmAdd" method="POST" onsubmit="javascript:{add_function}; return false;">
	 <center>
	  <table cellspacing="3" cellpadding="3">
	   <tr>
		 <td>
		  <div id="divAppboxHeader">{lang_Add_Participants}</div>
		  <div id="divAppbox">
		   <table border=0>
			<tr><td>{lang_Organization}:</td></tr>
			<tr><td>
				<select name="organization" style='width:250px;' onchange="frmAdd.change_org.value='True';frmAdd.submit();">
					<!-- BEGIN organization_block -->
					<option value="{org_id}" {org_selected}>{org_name}</option>
					<!-- END organization_block -->
				</select>
			</td></tr>
			<tr><td>{lang_sector}:</td></tr>
			<tr><td>
				<select name="sector" style='width:250px;' onchange="frmAdd.submit()">
					<!-- BEGIN sector_block -->
					<option value="{sector_id}" {sector_selected}>{sector_name}</option>
					<!-- END sector_block -->
				</select></td></tr>
			<input type="hidden" name="change_org" value="false">
			<tr>
			 <td>
			  <select name="participants" multiple style="width:250px" size="18" id="participant_list">
				<!-- BEGIN user_group_block -->
				{participant_options}
				<!-- END user_group_block -->
			  </select>
			 </td>
		    </tr>
			<tr><td>&nbsp;</td>	</tr>
			<tr>
			 <td>
			  <center>{lang_to_Search}:&nbsp;<input type="text" id="query" name="query" onkeyup="optionFinder(this, event)" autocomplete="off" /></center>
			 </td>
			</tr>
			<tr><td>&nbsp;</td>	</tr>
			<tr>
			 <td>
			  <center>
			   <input type="button" id="addButton" class="button" value={lang_Add} onClick="javascript:{add_function}">
			   <input type="button" id="closeButton" value={lang_Close} onClick="javascript:window.close()">
			  </center>
			 </td>
			</tr>
		   </table>
		  </div>
		 </td>
		</tr>
	   </table>
	  </center>
	 </form>
<script>
	setLists('participant_list','{target_element}');
	document.getElementById("query").focus();
</script>
*}
