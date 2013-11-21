<!-- BEGIN body -->

<div class="menu-contexto">
	<span><a href="{href_back}">{lang_back}</a></span><span class="titulo-secao">{lang_contact_title}</span>
</div>

<dt class="menu-diverso">
    <strong id="editando">{lang_title_add_edit}</strong>
</dt>

<form name="formcontact" method="post" action="{form_action}">
<input type="hidden" name="id" value="{id}">
<input type="hidden" name="catalog" value="{catalog}">
<input type="hidden" name="id_connection_email" value="{var_connection_email}">
<input type="hidden" name="id_connection_phone" value="{var_connection_phone}">

<div id="campos-correspondencia">
	<div class="posiciona-esquerda" style="width:99%; padding:0;">
        <div class="limpar_div_margin"><label class="email-labels"><a href="#"><strong>{lang_title_alias}:</strong> </a></label> <input type="text" name="alias" value="{lang_alias}" />  </div>
        <div class="limpar_div_margin"><label class="email-labels"><a href="#"><strong>{lang_title_name}:</strong> </a></label> <input type="text" name="given_names" value="{lang_name}" />  </div>
        <div class="limpar_div_margin"><label class="email-labels"><a href="#"><strong>{lang_title_lastname}:</strong> </a></label> <input type="text" name="family_names" value="{lang_lastname}" />  </div>
        <div class="limpar_div_margin"><label class="email-labels"><a href="#"><strong>{lang_title_email}:</strong> </a></label> <input type="text" name="email" value="{lang_email}" /> </div>
        <div class="limpar_div_margin"><label class="email-labels"><a href="#"><strong>{lang_title_phone}:</strong> </a></label> <input type="text" name="phone" value="{lang_phone}" /></div>
	</div>
</div>

</form>

<div class="menu-contexto"></div>

<div id="operacao_lista">
	{lang_selecteds}:
	<button id="cancelar" class="btn-generico" onclick="javascript:history.back()" >{lang_cancel}</button>
	<button id="confirmar_edicao" class="btn-generico" onclick="javascript:document.formcontact.submit();">{lang_confirm}</button>
</div>

<!-- END body -->

