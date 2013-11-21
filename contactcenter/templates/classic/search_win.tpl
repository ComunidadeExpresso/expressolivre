{cc_api}

<script type="text/javascript" src="{cc_js_search_win}"></script>

<div id="cc_search" style="position: absolute; width: 450px; height: 300px; visibility: hidden; z-index: 2; border: 1px solid #999; border-top: 0px;">
	<input id="cc_search_title" type="hidden" value="{cc_search_title}">
	<table border="0" width="450px" height="300px" style="background: #eee">
		<tr valign="top">
			<td style="width: 250px">
				<table width="100%" border="0">
					<tr>
						<td align="left">{cc_search_catalogues}</td>
					</tr>
					<tr>
						<td align="left"><div id="cc_search_catalogues" style="position: relative; width: 250px; height: 265px; border: 1px solid #999; overflow: auto;" class="row_on"></div></td>
					</tr>
				</table>
			</td>
			<td style="width: 200px">
				<table width="100%" height="100%" border="0">
					<tr>
						<td align="left" colspan="2">{cc_search_for}</td>
					</tr>
					<tr>
						<td align="left" colspan="2"><input id="cc_search_for" type="text" /></td>
					</tr>
					<tr height="100%" valign="bottom">
						<td align="right" height="100%" valign="bottom"><input type="button" value="{cc_search_go}" style="width: 70px" onclick="javascript: ccSearchWin.go()" /></td>
						<td align="right" height="100%" width="1%" valign="bottom"><input type="button" value="{cc_search_cancel}" style="width: 70px" onclick="javascript:ccSearchWin.close();" /></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	var searchWin_load = document.body.onload;
	var ccSearchWin, ccSearchWindow;

	var searchWin_load_f = function(e)
	{
		ccSearchWindow = new dJSWin({'id': 'cc_search_window',
									 'content_id': 'cc_search',
									 'win_class': 'row_off',
									 'width': '452px',
									 'height': '300px',
									 'title_color': '#3978d6',
									 'title': Element('cc_search_title').value,
									 'title_text_color': 'white',
									 'button_x_img': Element('cc_phpgw_img_dir').value+'/winclose.gif',
									 'border': true});

		ccSearchWindow.draw();
		ccSearchWin = new ccSearchWinClass({'window': ccSearchWindow, 'tree': 'ccSearchTree'});
	}

	if (is_ie)
	{
		//document.body.onload = function(e) {setTimeout('searchWin_load_f()'); searchWin_load ? setTimeout('searchWin_load()', 10) : false;};
		document.body.onload = function(e) {searchWin_load_f(); searchWin_load ? searchWin_load() : false;};
	}
	else
	{
		searchWin_load_f();
	}
</script>
