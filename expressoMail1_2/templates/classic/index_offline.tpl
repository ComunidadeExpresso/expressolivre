<!-- BEGIN list -->
<input type="hidden" value="{txt_loading}" id="txt_loading">
<input type="hidden" value="{txt_clear_trash}" id="txt_clear_trash">
<input type="hidden" value="{upload_max_filesize}" id="upload_max_filesize">
<input type="hidden" value="{msg_folder}" id="msg_folder">
<input type="hidden" value="{msg_number}" id="msg_number">
<input type="hidden" value="{user_email}" id="user_email">
<input type="hidden" value="{user_organization}" id="user_organization">
<input type="hidden" value="{cyrus_delimiter}" id="cyrus_delimiter">
<table id="main_table" width="100%" cellspacing="0" cellpadding="0" border="0" style="display:none">
	<tbody>
		<tr>
			<td id="folderscol" width="162px" height="100%" valign="top">			
				<table id="folders_tbl" width="162px" border="0" cellspacing="0" cellpadding="0" border="0">
					<tbody>
						<tr><td><div class='content-menu'>
							<table border="0" cellspacing="0" cellpadding="0" border="0"><tbody>
								<tr><td class='content-menu-td' onclick='javascript:new_message("new","null");' onmouseover='javascript:set_menu_bg(this);' onmouseout='javascript:unset_menu_bg(this);'><div class='em_div_sidebox_menu'><img src='./templates/{template}/images/menu/createmail.gif'><span class="em_sidebox_menu">{new_message}</span></div></td></tr>
								<!--tr><td class='content-menu-td' id='em_refresh_button' onclick='javascript:refresh();' onmouseover='javascript:set_menu_bg(this);' onmouseout='javascript:unset_menu_bg(this);'><div class='em_div_sidebox_menu'><img src='./templates/{template}/images/menu/checkmail.gif'><span class="em_sidebox_menu">{refresh}</span></div></td></tr-->

								<tr><td id="link_tools" class='content-menu-td' onmouseover='javascript:set_menu_bg(Element("link_tools"));' onmouseout='javascript:unset_menu_bg(this);'><div class='em_div_sidebox_menu'><img height='16px' src='./templates/{template}/images/menu/tools.gif'><span class="em_sidebox_menu">{tools} ...</span></div></td></tr>								
								<tr><td class='content-menu-td' onclick='expresso_offline_access.do_logoff()' onmouseover='javascript:set_menu_bg(this);' onmouseout='javascript:unset_menu_bg(this);'><div class='em_div_sidebox_menu'><img width='18px' height='18px' src='../phpgwapi/templates/default/images/logout.png'><span class="em_sidebox_menu">{logoff}</span></div></td></tr>
								<tr><td height="3px">&nbsp;</td></tr>								
							</tbody></table>
						</div></td></tr>
						<tr><td height="2px"></td></tr>						
						<tr><td class="content-folders" valign="top" style="padding:2px">
							<div id="content_folders" style="height:100%;width:162px;overflow:auto"></div>
						</td></tr>
					</tbody>
				</table>
				<div style="height:4px"></div>
				<div align="center">
					<div id="search_div" align="center" style="white-space:nowrap"></div>
					<input type="text" id="em_message_search" size="16" maxlength="22" onfocus="javascript:onFocusQuickSearchEmail(this); return false;"/>
					<a class='' onMouseOut="window.status='';return true;" title='{lang_Open_Search_Window}' onMouseOver="window.status='{lang_Open_Search_Window}';return true;" href="javascript:void(0);"  onClick="javascript:search_emails(Element('em_message_search').value)">
						<img valign="center" align="center" src="templates/{template}/images/search.gif">
					</a>
					<!--a class='' onMouseOut="window.status='';return true;" title='{lang_search_user}' onMouseOver="window.status='{lang_search_user}' ;return true;" href="javascript:void(0);"  onClick="javascript:emQuickSearch(Element('em_message_search').value, 'null', 'null')">
						<img valign="center" align="center" src="templates/{template}/images/users.jpg">
					</a-->
				</div>
				<script type="text/javascript">
					
					var element_input = document.getElementById('em_message_search');
				
					function keyPressQuickSearchEmail(e)
					{
						if( e.keyCode == 13 )
							search_emails(element_input.value);
					}

					if ( element_input.addEventListener )
						element_input.addEventListener('keypress', keyPressQuickSearchEmail, false);
					else if ( element_input.attachEvent )
						element_input.attachEvent('onkeypress', keyPressQuickSearchEmail);

					function onFocusQuickSearchEmail(pInput)
					{
						if ( pInput.createTextRange )
						{
							var FieldRange = pInput.createTextRange();
								FieldRange.moveStart('character', pInput.value.length);
								FieldRange.collapse();
								FieldRange.select();
						}
					}
				
				</script>
			</td>
			<td width="2px">&nbsp;</td>			
			<td width="*" valign="top" align="left">
				<div id="exmail_main_body" class="messagescol">
					<table id="border_table" width="auto" height="26" cellspacing="0" cellpadding="0" border="0">
						<tbody id="border_tbody">
							<tr id="border_tr">
								<td nowrap class="menu" onClick="alternate_border(0);"  id="border_id_0">&nbsp;{lang_inbox}&nbsp;<font face="Verdana" size="1" color="#505050">[<span id="new_m">0</span> / <span id="tot_m">0</span>]</font>
								</td>
								<td nowrap id="border_blank" class="last_menu" width="100%">&nbsp;</td>								
							</tr>
						</tbody>
					</table>
					<div id="content_id_0" class="conteudo"></div>
					<div id="footer_menu">
						<table style="border-top:0px solid black" id="footer_box" cellpadding=0 cellspacing=0 border=0 width="100%" height="10px">
							<tbody>
								<tr id="table_message"></tr>
							</tbody>
						</table>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<!-- END list -->
