{$header}
<title>Adicionar Itens</title>
{$javaScripts}
<form name="frmAdd" method="POST" onsubmit="javascript:genericListAdd(); return false;">
	<center>
		<table cellspacing="3" cellpadding="3">
			<tr>
				<td>
					<div id="divAppboxHeader">Adicionar Itens</div>
					<div id="divAppbox">
						<table border="0">
							<tr><td>Lista:</td></tr>
							<tr><td>{html_options name="genericList" id="genericList" multiple="multiple" style="width:250px" size="18" options=$list selected=$selected}</td></tr>
							<tr><td>&nbsp;</td></tr>
							<tr>
								<td>
									<center>{$lang_to_Search}:&nbsp;<input type="text" id="query" name="query" onkeyup="genericListOptionFinder(this, event)" autocomplete="off"></center>
								</td>
							</tr>
							<tr><td>&nbsp;</td>	</tr>
							<tr>
								<td>
									<center>
										<input type="button" id="addButton" class="button" value={$lang_Add} onClick="javascript:genericListAdd()">
										<input type="button" id="closeButton" value={$lang_Close} onClick="javascript:window.close()">
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
<script type="text/javascript">
	genericListSetLists('genericList','{$targetElement}');
	document.getElementById("query").focus();
</script>
{$footer}
