{$header}
<input type="hidden" value="{$txt_loading}" id="txt_loading"/>
<input type="hidden" value="{$processID}" id="workflowAdminSourceProcessID"/>
{$css}
{$javaScripts}
<div id="main_body" style="display:none; width:99.5%;">
	<table id="border_table" width="auto" height="26" cellspacing="0" cellpadding="0" border="0">
		<tbody id="border_tbody">
			<tr id="border_tr">
			{foreach from=$tabs item=tab name="feTabs"}
				{assign var='index' value=$smarty.foreach.feTabs.index}
				<td nowrap class="menu" id="border_id_{$index}" onClick="change_folder({$index})">
					&nbsp;&nbsp;{$tab}&nbsp;&nbsp;
				</td>
			{/foreach}
				<td nowrap id="border_blank" class="last_menu" width="100%">
					&nbsp;
				</td>
			</tr>
		</tbody>
	</table>
{foreach from=$tabs item=tab name="feTabs"}
	<div id="content_id_{$smarty.foreach.feTabs.index}" class="conteudo"></div>
{/foreach}
</div>
