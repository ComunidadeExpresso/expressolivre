{cc_api}

<script type="text/javascript" src="{cc_js_search}"></script>
<script type="text/javascript" src="{cc_js_email_win}"></script>
<input id="cc_email_win_title" type="hidden" value="{cc_email_win_title}"/>
<input id="cc_email_status" type="hidden" value="{cc_email_status}"/>
<input id="cc_email_search_text" type="hidden" value="{cc_email_search_text}"/>
<input id="cc_email_conn_1" type="hidden" value="{cc_email_conn_1}"/>
<input id="cc_email_conn_2" type="hidden" value="{cc_email_conn_2}"/>
<input id="cc_email_conn_3" type="hidden" value="{cc_email_conn_3}"/>

<div id="cc_email_win" style="position: absolute; width: 720px;z-index:1; height: 350px; visibility: hidden;  border: 1px solid #999; border-top: 0px; background-color: #eee;">
	<table border="0" width="100%" height="100%">
		<tr>
			<td>
				<table>
					<tr>
						<td id="cc_email_search"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table border="0" width="100%">
					<tr>
						<td valign="top">
							<table border="0">
								<tr>
									<td>{cc_label_catalogues}</td>
								</tr>
								<tr>
									<td><div id="cc_email_catalogues" style=" position: relative; width: 300px; height: 110px; border: 1px solid #999; overflow:auto" class="row_on"></div>
									</td>
								</tr>
								<tr>
									<td>{cc_label_entries}</td>
								</tr>
								<tr>
									<td><select id="cc_email_win_entries" style="width: 300px; height: 110px; overflow: auto;" multiple></select></td>
								</tr>
							</table>
						</td>
						<td valign="top">
							<table border="0" width="100%" height="100%">
								<tr>
									<td></td>
									<td>{cc_label_to}</td>
								</tr>
								<tr>
									<td>
										<table>
											<tr><td><input style="width: 90px;" type="button" value="{cc_btn_to_add}" onclick="javascript:ccEmailWin.entries_to();"></td></tr>
											<tr><td><input style="width: 90px;" type="button" value="{cc_btn_to_del}" onclick="javascript:ccEmailWin.to_entries();"></td></tr>
										</table>
									</td>
									<td><select id="cc_email_win_to" style="width: 280px; height: 65px;" multiple></select></td>
								</tr>
								<tr>
									<td></td>
									<td>{cc_label_cc}</td>
								</tr>
								<tr>
									<td>
										<table>
											<tr><td><input style="width: 90px;" type="button" value="{cc_btn_cc_add}" onclick="javascript:ccEmailWin.entries_cc();"></td></tr>
											<tr><td><input style="width: 90px;" type="button" value="{cc_btn_cc_del}" onclick="javascript:ccEmailWin.cc_entries();"></td></tr>
										</table>
									</td>
									<td><select id="cc_email_win_cc" style="width: 280px; height: 65px;" multiple></select></td>
								</tr>
								<tr>
									<td></td>
									<td>{cc_label_cco}</td>
								</tr>
								<tr>
									<td>
										<table>
											<tr><td><input style="width: 90px;" type="button" value="{cc_btn_cco_add}" onclick="javascript:ccEmailWin.entries_cco();"></td></tr>
											<tr><td><input style="width: 90px;" type="button" value="{cc_btn_cco_del}" onclick="javascript:ccEmailWin.cco_entries();"></td></tr>
										</table>
									</td>
									<td><select id="cc_email_win_cco" style="width: 280px; height: 65px;" multiple></select></td>
								</tr>								
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<table width="100%" border="0">
					<tr align="center">
						<td style="width: 523px">&nbsp;</td>
						<td style="width: 35px"><input style="width: 70px;" type="button" value="{cc_btn_ok}" onclick="javascript:ccEmailWin.ok();"></td>
						<td style="width: 35px"><input style="width: 70px;" type="button" value="{cc_btn_cancel}" onclick="javascript:ccEmailWin.close();"></td>
						<td style="width: 7px"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td id="cc_email_win_debug"></td>
		</tr>
	</table>
</div>

<!-- BOTTOM DETAILS-->
<script type="text/javascript">
	var emailWin_load = document.body.onload;
	var ccEmailWin, ccEmailWindow;
	
	
	var emailWin_load_f = function(e)
	{
		ccEmailWindow = new dJSWin({'id': 'cc_email_window',
									'content_id': 'cc_email_win',
									'win_class': 'row_off',
									'width': '720px',
									'height': '350px',
									'title_color': '#3978d6',
									'title': Element('cc_email_win_title').value,
									'title_text_color': 'white',
									'button_x_img': Element('cc_phpgw_img_dir').value+'/winclose.gif',
									'border': true});

		ccEmailWindow.draw();		
		ccEmailWin = new ccEmailWinClass({'window': ccEmailWindow});
		//ccEmailWin.open();
	}

	if (is_ie)
	{		
		document.body.onload = function(e) {emailWin_load ? emailWin_load() : false; emailWin_load_f()};
	}
	else
	{
		emailWin_load_f();
	}

</script>
<!-- END BOTTOM DETAILS-->
