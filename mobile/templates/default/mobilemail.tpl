<!-- BEGIN main_emails -->

		<div class="menu-contexto" style="height:16pt;">
			<div style="float:left; position:absolute;">
				<button class="btn-contexto" onclick="location.href='{href_back}'">{lang_back}</button>
				<button class="btn-contexto" onclick="location.href='index.php?menuaction=mobile.ui_mobilemail.new_msg&type=clk'">{lang_new}</button>
			</div>
			<div style="float:right; position:relative;">
				<span class="titulo-secao">{folder}</span>
			</div>
		</div>
			
		<form action="index.php" method="post" id="form_busca"> 
		<input type="hidden" name="menuaction" value="mobile.ui_home.search">
		<input type="hidden" name="folder_to_search" value="{folder_id}">
		{search}
		</form>
	
		
		<dl id="lista_miolo">
			<dt id="palavra-procurada" style="height:22pt !important;">
				<div style="float:left; position:absolute;">
					{filter_by}:
					<select name="fitros" onChange="location.href='index.php?menuaction=mobile.ui_mobilemail.change_search_box_type&search_box_type='+this.value">
						 <option value="all" {selected_all}>Todos</option>
						 <option value="flagged" {selected_flagged}>Importante</option>
						 <option value="seen" {selected_seen}>Lidas</option>
						 <option value="unseen" {selected_unseen}>N&atilde;o Lidas</option>
						 <option value="answered" {selected_answered}>Respondidas</option>	
					</select>
				</div>					
				<div style="float:right; position:relative; margin-right: 5px;font-size:12pt">
					<button class="btn-contexto" onclick="window.location.reload();">{refresh}</button>
				</div>					
			</dt>
		
			<form id="formu" action="index.php" method="post">
			<input type="hidden" id="menuaction" name="menuaction" value="mobile.ui_mobilemail.delete_msg">
			<input type="hidden" id="msg_folder" name="msg_folder" value="{folder_id}">
			<input type="hidden" id="flag" name="flag" value="seen">
			{mails}
			</form>
		
			<div class="menu-contexto centraliza" style="display:{show_more};">
				<form method="post" action="index.php">
					<input type="hidden" name="menuaction" value="mobile.ui_mobilemail.change_page">
					<input type="hidden" name="page" value="{page}">
					<button type="submit" name="more_messages" title="{lang_more_messages}" class="btn-contexto"> {lang_more} 10 {lang_messages}</button>
				</form>
			</div>
			
			<div id="operacao_lista">
				<div class="margin-rodape">
					{selecteds}:
					<button id="selecionar" class="btn-generico" onclick="document.getElementById('menuaction').value='mobile.ui_mobilemail.mark_message_with_flag';document.getElementById('formu').submit();" >marcar como lido</button>
					<button id="remover" class="btn-generico" onclick="document.getElementById('menuaction').value='mobile.ui_mobilemail.delete_msg';document.getElementById('formu').submit();">remover</button>
				</div>
			</div>
		</dl>
<!-- END main_emails -->