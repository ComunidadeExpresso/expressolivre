<!-- BEGIN main_body -->
		<div class="menu-contexto" style="height:25px !important;">
			<form id="form_catalog" action="index.php?menuaction=mobile.ui_mobilecc.change_catalog" method="post">
				<div style="float:left; position:absolute;">
					<select name="catalog" onChange="document.getElementById('form_catalog').submit();">
						{catalogs}
					</select>
				</div>
				<span style="float:right; position:relative;" class="titulo-secao">{lang_contacts}</span>
			</form>
		</div>
		
		<form id="form_busca" action="index.php" method="post">
			<input type="hidden" name="menuaction" value="mobile.ui_home.search">
			<input type="hidden" name="catalog_to_search" value="{actual_catalog}">
			<input type="hidden" name="contacts_request_from" value="{contacts_request_from}">
			{search}
		</form>

		<dl id="lista_miolo">
			
			<dt class="menu-diverso">
			<div class="margin-geral centraliza">	
			<a href="{href_back}" style="display:{show_back};"> < </a>
			{pagging_letters}
			<a href="{href_next}" class="btn_off" style="display:{show_next};"> > </a>
			</div>
			
			</dt>
			<form method="post" action="index.php" id="formu_contacts">
			<input type="hidden" id="menuaction" name="menuaction" value="mobile.ui_mobilecc.delete_contacts">
			<input type="hidden" id="catalog" name="catalog" value="{actual_catalog}">
			{contacts}
			</form>
				
				
		</dl>

		<div class="menu-contexto centraliza" style="display:{show_more};">
			<button name="more_messages" title="{lang_more_messages}" class="btn-contexto" onClick="location.href='index.php?menuaction=mobile.ui_mobilecc.change_max_results&results={next_max_results}'">{lang_more} 10 {lang_contacts}</button>
		</div>
		
		<div id="operacao_lista" style="display:{show_actions};">
 	        <div class="margin-rodape">
                {selecteds}:
            	<button id="remover" class="btn-generico" onClick="document.getElementById('menuaction').value='mobile.ui_mobilecc.delete_contacts';document.getElementById('formu_contacts').submit();">remover</button>
				<button id="adicionar_contato" onClick="location.href=('../mobile/index.php?menuaction=mobile.ui_mobilecc.contact_add_edit')" class="btn-generico" style="display:{show_add_button};">adicionar contato</button>
            </div>
		</div>
		
		
<!-- END main_body -->	
		
<!-- BEGIN catalog_row -->
 <option value='{catalog_value}' {selected}>{catalog_name}</option>
<!-- END catalog_row -->

<!-- BEGIN pagging_block -->
	
<a href="{href}" class="{class_button}"> {letter} </a>
	
<!-- END pagging_block -->