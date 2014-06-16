<!-- BEGIN list -->
<input type="hidden" value="{txt_loading}" id="txt_loading">
<input type="hidden" value="{txt_clear_trash}" id="txt_clear_trash">
<input type="hidden" value="{upload_max_filesize}" id="upload_max_filesize">
<input type="hidden" value="{msg_folder}" id="msg_folder">
<input type="hidden" value="{msg_number}" id="msg_number">
<input type="hidden" value="{user_email_alternative}" id="user_email_alternative">
<input type="hidden" value="{user_email}" id="user_email">
<input type="hidden" value="{user_organization}" id="user_organization">
<input type="hidden" value="{cyrus_delimiter}" id="cyrus_delimiter">
<table id="main_table" width="100%" cellspacing="0" cellpadding="0" border="0" style="display:none">
<tbody>
<tr>
	<td id="folderscol" width="170px" height="100%" valign="top"> 
                <table id="folders_tbl" width="200px" border="0" cellspacing="0" cellpadding="0"> 
		<tbody>
		<tr>
			<td class='content-menu'>
				<table border="0" cellspacing="0" cellpadding="0" style="width:100%"> 
					<tbody>
						<tr>
							<td>
								<div id="search_div" class="class_search_div" style="white-space:nowrap">
									<input type="text" id="em_message_search" size="17" style="margin-left: 5px;"/>
									<img style="padding:0px 8px; width:16px; height:16px; margin: -5px -5px; cursor:pointer;" class="" src="templates/default/images/search.gif" onMouseOut="window.status='';return true;" title='{lang_Open_Search_Window}' onMouseOver="window.status='{lang_Open_Search_Window}';return true;" href="javascript:void(0);"  onClick="javascript:EsearchE.quickSearchMail($('#em_message_search').val(), null, 'SORTDATE_REVERSE');"/>
									<img style="padding:0px 8px; width:16px; height:16px; margin: -5px -5px; cursor:pointer;" class="" src="templates/default/images/users.gif" onMouseOut="window.status='';return true;" title='{lang_search_user}' onMouseOver="window.status='{lang_search_user}' ;return true;" href="javascript:void(0);"  onClick="javascript:emQuickSearch(Element('em_message_search').value, 'null', 'null', 'expressoMail')"></img> 
								</div>
							</td>
						</tr>
						<tr height="24">
							<td class='content-menu-td' onclick='javascript:new_message("new","null");' onmouseover='javascript:set_menu_bg(this);' onmouseout='javascript:unset_menu_bg(this);'>
								<div class='em_div_sidebox_menu'>
									<img src='./templates/default/images/menu/createmail.gif' />
									<span class="em_sidebox_menu">{new_message}</span>
								</div>
							</td>
						</tr>
						<tr height="24">
							<td class='content-menu-td' id='em_refresh_button' onclick='javascript:refresh();' onmouseover='javascript:set_menu_bg(this);' onmouseout='javascript:unset_menu_bg(this);'>
								<div class='em_div_sidebox_menu'>
									<img src='./templates/default/images/menu/checkmail.gif' />
										<span class="em_sidebox_menu">{refresh}</span>
								</div>
							</td>
						</tr>
						<tr height="24">
							<td id="link_tools" class='content-menu-td'>
								<div class='em_div_sidebox_menu'>
									<img height='16px' src='./templates/default/images/menu/tools.gif' />
										<span class="em_sidebox_menu">{tools} ...</span>
								</div>
							</td>
						</tr>								
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td height="2px"></td>
		</tr>						
		<tr>
			<td class="image-menu" valign="top" style="padding:0px">
				<div id="content_folders" class="menu-degrade" style="width:230px;height:100%;overflow:auto"></div> 
			</td>
		</tr>
		<tr>
			<td>
				<div id="content_messenger" style="width:225px;"></div>
			</td>
		</tr>
		</tbody>
		</table>
		<script type="text/javascript">
			var element_input = document.getElementById('em_message_search');
			
			function keyPressQuickSearchEmail(e)
			{
				if( e.keyCode == 13 )
					performQuickSearch(Element('em_message_search').value);
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
	<td class="collapse_folders_td">
		<span class="collapse_folders"/>
	</td>			
	<td width="100%" valign="top" align="left">
		<div id="exmail_main_body" class="messagescol">
			<table id="border_table" width="auto" height="26" cellspacing="0" cellpadding="0" border="0">
				<tbody id="border_tbody">
					<tr id="border_tr">
						<td nowrap class="menu" onClick="alternate_border(0);resizeWindow();"  id="border_id_0">
							&nbsp;{lang_inbox}&nbsp;<font face="Verdana" size="1" color="#505050">[
							<span id="new_m">0</span> / 
							<span id="tot_m">0</span>]
							</font>
						</td>
						<td nowrap id="border_blank" class="last_menu" width="100%">&nbsp;</td>								
					</tr>
				</tbody>
			</table>
			<div id="content_id_0" class="conteudo"></div>
			<div id="footer_menu">
				<table style="border-top:0px solid black" id="footer_box" cellpadding=0 cellspacing=0 border=0 width="100%" height="14px">
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
<div id='forms_queue'></div>
<div style="display:none" id="send_queue">
	<table width="100%" height="100%">
		<tr>
			<td background="js/modal/images/fundo_exp.png" valign="top" align="center">
				<font color="#006699"><b><div id="text_send_queue"></div></b></font>
			</td>
		</tr>
	</table>
</div>
                                       
<script type="text/javascript" src="js/QuickCatalogSearch.js"></script>
<div style="display:none; width: auto; min-height: 0px; height: 410px; overflow: hidden" id="dialog-modal" class="dialog-modal expressomail-qs-container" title="{lang_quick_search_users_dialog_title}">
 		                <div id="accordion"> 
 		                        <div class="ui-widget" align="right"> 
							<fieldset class="search-catalog-options ui-corner-all">
 		                            <select id="combobox"> 
 		                                <!-- Pegar essas opções das preferências --> 
 		                                <option value="global">{lang_global_catalog}</option> 
 		                                <option value="personal">{lang_personal_catalog}</option> 
 		                                <option value="all">{lang_all_catalogs}</option> 
 		                            </select> 
 		                    </fieldset>     
                            <fieldset class="search-field ui-corner-all" style>
								<span title="Ajuda" class="ui-icon ui-icon-search"></span>
								<input class="search" id="busca" onFocus="setFocus();" onBlur="removeFocus();" onkeypress="javascript: if(checkEnter(event)) {buscaContato(this.value); }" type="text" />
							</fieldset>
							<input title="Buscar Contatos" class="button" type="button" onClick="show_help()" style="background-image: url('./templates/default/images/information.png'); background-repeat: no-repeat;"/>
 		                 </div>   
 		             </div> 
		                <fieldset id="fieldset1" class="details-container ui-corner-all">
                            <legend>{lang_contact_details}</legend>
                            <div style="overflow: auto; height: 230px;" id="detalhes_contato"></div>
                        </fieldset>    
            <div class="acc-list"> <ul id="selectable"></ul></div>
                    <div class="demo" style="float: left; margin-top: 10px; width: 100%; height: 20px; padding:5px 0 0 0;">
                        <div id="slider" style="width: 448px;"></div>
                            <p style="margin-top: -15px; margin-bottom: 0pt; margin-left: 453px; float: left;">
                                <label for="amount" style="padding-left: 6px;">{lang_page}: </label>
                                <input type="text" id="amount-text" style="padding-left: 3px; border:0; font-weight:bold; width: 175px;" readonly="true"/>
                            </p>            
 		        </div> 
</div>
<div style="display:none; width: auto; min-height: 0px; height: 380px;" id="dialog-modal_help" title="{lang_dialog_help} Help busca rápida de contatos"> 
	<fieldset id="fieldset2">
        <legend>{lang_help} Busca rápida de contatos</legend>
		<div>
			<strong>Para a utilização dessa funcionalidade, podem ser usados os seguintes atalhos:</strong><br /><br /><br />
			<ul>
				<li>Ir para a página anterior - Seta para esquerda</li>
				<li>Mover para contato acima - Seta para cima</li>
				<li>Mover para contato abaixo - Seta para baixo</li>
				<li>Mover para a próxima página - Seta para direita</li>
				<li>Adicionar contato selecionado - Enter</li>
				<li>Fechar - Esc</li>
			</ul> 
 		</div> 
	</fieldset>   
</div>
<div id="import-dialog" title="Importar Evento/Tarefa" style="display:none">
	<p>Seleciona uma Agenda para o Evento :</p>
	<p>
		<select style="width:100%;" id="select-agenda"> 
		</select>
	</p>
	<p>
		<label>Status : </label>
	</p>
	<p>
		<select style="width:100%;" id="select-status">
			<option value="1">Eu vou</option>
			<option value="3">Não vou</option>
			<option value="2">Tentativa</option>
		</select>
	</p>
</div>
<div id="sandbox" class="expresso-calendar-container hidden"></div>
<div id="windowLabels" class="label-configure-win" style="display: none;"></div>
<div id="followupFlag" class="followupflag-configure-win" style="display: none;"></div>
<div class="expressomail-module-container" style="display: none;"></div>
<div id="importEmails" style="display:none;padding:5px;"></div>
<div id="importEmailsLocal" style="display:none;padding:5px;"></div>
<div id="sendFileMessages" style="display:none;"></div>
<div id="quickAddContact" style="display:none;"></div>
<div id="freeow" class="freeow freeow-bottom-right"></div>
<div id="window_InfoQuota" style="display:none"></div>
<div id="error_reporter" style="display:none"></div>
<div id="shareMailbox" style="display:none;overflow:hidden;"></div>
<div id="info_card_cc" style="display:none"></div>
<div id="searchEmails" style="display:none"></div>
<!-- END list -->