<script type="text/javascript" src="{cc_js_contacts}"></script>
<script type="text/javascript" src="{cc_js_wz_dragdrop}"></script>

<div id="cc_email_win_title" style="position:absolute; width: 500px; height: 20px; visibility: hidden; z-index: 0; border: 1px solid #999; font-weight: bold; border-bottom: 0px;" class="th">
	<table border="0" width="100%" height="100%" cellpadding="2" cellspacing="0">
        <tr>
			<td align="left" valign="top" width="99%">{cc_email_win_title}</td>
			<td align="center" valign="top" width="1%">
				<img id="cc_email_win_close" alt="{cc_search_close}" src="{phpgw_img_dir}/winclose.gif" width="16px" height="16px" onclick="dd.elements.cc_email_win_title.hide();" 
				style="cursor: pointer; cursor: hand;">
			</td>
		</tr>
	</table>																					
</div>

<div id="cc_email_win" style="position: absolute; width: 500px; height: 400px; visibility: hidden; z-index: 0; border: 1px solid #999; border-top: 0px; background-color: #eee;">
	<table border="0" width="100%" height="100%">
		<tr>
			<td>
				<table border="0">
					<tr>
						<td>{cc_label_catalogue_type}</td>
						<td>{cc_label_ordinance_type}</td>
					</tr>
					<tr>
						<td><select id="cc_email_win_catalogues" style="width: 200px;" onchange="javascript:catalogue_change();"><option>{cc_choose_catalogue}</option></select></td>
						<td><select id="cc_email_win_ordinances" style="width: 200px;" onchange="javascript:ordinance_change();"><option>{cc_choose_ordinance}</option></select></td>
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
									<td>{cc_label_contacts}</td>
								</tr>
								<tr>
									<td><select id="cc_email_win_contacts" style="width: 200px; height: 230px; overflow: auto;" multiple></select></td>
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
									<td align="center" valign="middle">
										<table border="0">
											<tr><td><input style="width: 60px;" type="button" value="{cc_btn_to_add}" onclick="javascript:contacts_to_to();"></td></tr>
											<tr><td><input style="width: 60px;" type="button" value="{cc_btn_to_del}" onclick="javascript:contacts_to_to();"></td></tr>
										</table>
									</td>
									<td><select id="cc_email_win_to" style="width: 200px; height: 60px;" multiple></select></td>
								</tr>
								<tr>
									<td></td>
									<td>{cc_label_cc}</td>
								</tr>
								<tr>
									<td align="center">
										<table valign="middle" border="0">
											<tr><td><input style="width: 60px;" type="button" value="{cc_btn_cc_add}" onclick="javascript:contacts_to_cc();"></td></tr>
											<tr><td><input style="width: 60px;" type="button" value="{cc_btn_cc_del}" onclick="javascript:contacts_to_cc();"></td></tr>
										</table>
									</td>
									<td><select id="cc_email_win_cc" style="width: 200px; height: 60px;" multiple></select></td>
								</tr>
								<tr>
									<td></td>
									<td>{cc_label_cco}</td>
								</tr>
								<tr>
									<td align="center" valign="middle">
										<table border="0">
											<tr><td><input style="width: 60px;" type="button" value="{cc_btn_cco_add}" onclick="javascript:contacts_to_cco();"></td></tr>
											<tr><td><input style="width: 60px;" type="button" value="{cc_btn_cco_del}" onclick="javascript:contacts_to_cco();"></td></tr>
										</table>
									</td>
									<td><select id="cc_email_win_cco" style="width: 200px; height: 60px;" multiple></select></td>
								</tr>								
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table width="100%" border="0">
					<tr align="center">
						<td width="10%"><input style="width: 70px;" type="button" value="{cc_btn_search}" onclick="javascript:search();"></td>
						<td width="10%"><input style="width: 70px;" type="button" value="{cc_btn_new}" onclick="javascript:new_contact();"></td>
						<td width="10%"><input style="width: 70px;" type="button" value="{cc_btn_details}" onclick="javascript:details();"></td>
						<td width="50%">&nbsp;</td>
						<td width="10%"><input style="width: 70px;" type="button" value="{cc_btn_ok}" onclick="javascript:ok();"></td>
						<td width="10%"><input style="width: 70px;" type="button" value="{cc_btn_cancel}" onclick="javascript:cancel();"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>

<div id="cc_email_win_shadow" style="position: absolute; width: 500px; height: 420px; visibility: hidden; z-index: 0; background-color: #666;">

<!-- BOTTOM DETAILS-->
<script type="text/javascript">
  	SET_DHTML(	'cc_email_win'+NO_DRAG,
				'cc_email_win_shadow'+NO_DRAG,
				'cc_email_win_title'+CURSOR_MOVE);
			
	dd.elements.cc_email_win_title.addChild(dd.elements.cc_email_win);
	dd.elements.cc_email_win_title.addChild(dd.elements.cc_email_win_shadow);

	dd.elements.cc_email_win_title.moveTo(0,0);
	dd.elements.cc_email_win.moveTo(0,20);
	dd.elements.cc_email_win_shadow.moveTo(5,5);
	dd.elements.cc_email_win_title.moveTo(window.innerWidth/2 - dd.elements.cc_email_win_title.w/2, window.innerHeight/2 - dd.elements.cc_email_win.h/2);

	dd.elements.cc_email_win.setZ(2);
	dd.elements.cc_email_win_title.setZ(2);
	dd.elements.cc_email_win_shadow.setZ(1);

	dd.elements.cc_email_win_title.show();
</script>
<!-- END BOTTOM DETAILS-->
