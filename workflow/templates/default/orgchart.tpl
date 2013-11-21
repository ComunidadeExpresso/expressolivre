{$header}
<input type="hidden" value="{$txt_loading}" id="txt_loading">
{$css}
{$javaScripts}
<div id="main_body" style="width:99.5%;">
	<table id="border_table" width="auto" height="26" cellspacing="0" cellpadding="0" border="0">
		<tbody id="border_tbody">
			<tr id="border_tr">
				<td nowrap class="menu" id="border_id_0" onClick="(alternate_border(0) == 0) ? draw_org_folder() : ''">
					&nbsp;&nbsp;Organograma&nbsp;&nbsp;
				</td>
				<td nowrap id="border_blank" class="last_menu" width="100%">
					&nbsp;
				</td>
			</tr>
		</tbody>
	</table>
	<div id="content_id_0" class="conteudo"></div>
</div>
{$footer}
<script type="text/javascript">
init_orgchart({$tabIndex});
</script>
