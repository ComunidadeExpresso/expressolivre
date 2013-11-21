<!-- BEGIN main -->
		<div class="menu-contexto">
			<span><a href="{href_back}">{lang_back}</a></span> <span class="titulo-secao">{lang_search_return}</span>
		</div>
			
			<form method="post" action="index.php" id="form_busca">
				<input type="hidden" name="menuaction" value="mobile.ui_home.search">
				<input type="hidden" name="default_folders" value="{default_folders}">
				<input type="hidden" name="personal_folders" value="{personal_folders}">
				<input type="hidden" name="folder_to_search" value="{folder_to_search}">
				<input type="hidden" name="contacts_search" value="{contacts_search}">
				<input type="hidden" name="catalog_to_search" value="{catalog_to_search}">
				<input type="hidden" name="calendar_search" value="{calendar_search}">
				
				<input type="hidden" name="contacts_request_from" value="{contacts_request_from}">
			
		{search}
				</form>
		
		<dl id="lista_miolo">
			<dt id="palavra-procurada">&nbsp;&nbsp;{lang_your_search_was_by}: <strong><i>{search_param}</i></strong></dt>

			<dt class="resultado-titulo" style="display:{show_mails};">&nbsp;{lang_emails}</dt>
			{mails}
			<div class="menu-contexto centraliza" style="display:{show_more_messages};">
				<form method="post" action="index.php">
					<input type="hidden" name="menuaction" value="mobile.ui_home.search">
					<input type="hidden" name="default_folders" value="{default_folders}">
					<input type="hidden" name="personal_folders" value="{personal_folders}">
					<input type="hidden" name="folder_to_search" value="{folder_to_search}">
					<input type="hidden" name="contacts_search" value="{contacts_search}">
					<input type="hidden" name="catalog_to_search" value="{catalog_to_search}">
					<input type="hidden" name="calendar_search" value="{calendar_search}">
					<input type="hidden" name="name" value="{search_param}">
					<input type="hidden" name="max_msgs" value="{next_max_msgs}">
					<input type="hidden" name="max_contacts" value="{max_contacts}">
					<input type="hidden" name="max_events" value="{max_events}">
					<button type="submit" title="" class="btn-contexto"> {lang_more} 10 {lang_messages}</button>
				</form>
			</div>
			<dt class="resultado-titulo" style="display:{show_contacts};">&nbsp;{lang_contacts}</dt>
			{contacts}
			<div class="menu-contexto centraliza" style="display:{show_more_contacts};">
				<form method="post" action="index.php">
					<input type="hidden" name="menuaction" value="mobile.ui_home.search">
					<input type="hidden" name="default_folders" value="{default_folders}">
					<input type="hidden" name="personal_folders" value="{personal_folders}">
					<input type="hidden" name="folder_to_search" value="{folder_to_search}">
					<input type="hidden" name="contacts_search" value="{contacts_search}">
					<input type="hidden" name="catalog_to_search" value="{catalog_to_search}">
					<input type="hidden" name="calendar_search" value="{calendar_search}">
					<input type="hidden" name="name" value="{search_param}">
					<input type="hidden" name="max_msgs" value="{max_msgs}">
					<input type="hidden" name="max_contacts" value="{next_max_contacts}">
					<input type="hidden" name="max_events" value="{max_events}">
					<button type="submit" title="" class="btn-contexto"> {lang_more} 10 {lang_contacts}</button>
				</form>
			</div>
			<dt class="resultado-titulo" style="display:{show_calendar};">&nbsp;{lang_calendar}</dt>
			{calendar_results}
			<div class="menu-contexto centraliza" style="display:{show_more_events};">
				<form method="post" action="index.php">
					<input type="hidden" name="menuaction" value="mobile.ui_home.search">
					<input type="hidden" name="default_folders" value="{default_folders}">
					<input type="hidden" name="personal_folders" value="{personal_folders}">
					<input type="hidden" name="folder_to_search" value="{folder_to_search}">
					<input type="hidden" name="contacts_search" value="{contacts_search}">
					<input type="hidden" name="catalog_to_search" value="{catalog_to_search}">
					<input type="hidden" name="calendar_search" value="{calendar_search}">
					<input type="hidden" name="name" value="{search_param}">
					<input type="hidden" name="max_msgs" value="{max_msgs}">
					<input type="hidden" name="max_contacts" value="{next_max_contacts}">
					<input type="hidden" name="max_events" value="{next_max_events}">
					<button type="submit" title="" class="btn-contexto"> {lang_more} 10 {lang_events}</button>
				</form>
			</div>
		</dl>
<!-- END main -->

<!-- BEGIN row_events -->
				<p class="{bg} espacamento_contato_search"><strong>{date}</strong> - {title}</p>
<!-- END row_events -->
<!-- BEGIN no_events -->
	<dt class="titulo_mensagem reset-dt">
					{lang_no_results}
	</dt>
<!-- END no_events -->