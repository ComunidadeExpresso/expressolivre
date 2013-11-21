<!-- BEGIN body -->

<div class="menu-contexto">
	<span><a href="{href_back}">{lang_back}</a></span><span class="titulo-secao">{lang_contact_title}</span>
</div>

<dt class="menu-diverso">
    <strong id="editando">{title_view_contact}</strong>
</dt>

{row_body}

<div class="menu-contexto"></div>

{row_operacao}

<form name="formedit" action="../mobile/index.php?menuaction=mobile.ui_mobilecc.contact_add_edit" method="post">
	<input type="hidden" name="id" value="{id}">
	<input type="hidden" name="catalog" value="{catalog}">
</form>
<!-- END body -->

<!-- BEGIN row_view_operacao -->
<div id="operacao_lista">
	{lang_selecteds}:
	{buttom_use}
	{buttom_editar}
</div>
<!-- END row_view_operacao -->

<!-- BEGIN buttom_use_contact -->
<button id="remover" class="btn-generico" onclick="location.href=('../mobile/index.php?menuaction=mobile.ui_mobilemail.new_msg&type=from_mobilecc&input_to={email_to}')" >{lang_use_contact}</button>
<!-- END buttom_use_contact -->

<!-- BEGIN buttom -->
<button id="adicionar_contato" class="btn-generico" onclick="javascript:document.formedit.submit();">{lang_edit}</button>
<!-- END buttom -->

<!-- BEGIN people -->
<div id="corpo_mensagem"  class="contato-unico-bg" >
    <div class="contato-unico" >
		<div id="foto"><img src="{photo}" height="70" width="52"></div>
		<div class="bloco">
		    <div class="container">
		        <div id="block-label">{lang_title_name}:</div>
		        <div id="block-dados">{cc_name}</div>
		    </div>
		    
		    <div class="container">
		        <div id="block-label">{lang_title_alias}:</div>
		        <div id="block-dados">{lang_alias}</div>
		    </div>						
		
		    <div class="container">
		        <div id="block-label">{lang_title_email}:</div>
		        <div id="block-dados">{lang_email}</div>
		    </div>
		    
		    <div class="container">
		        <div id="block-label">{lang_title_phone}:</div>
		        <div id="block-dados">{lang_phone}</div>
		    </div>

		</div>
	</div>
</div>
<!-- END people -->

<!-- BEGIN people_ldap -->
<div id="corpo_mensagem"  class="contato-unico-bg" >
    <div class="contato-unico" >
		<div id="foto"><img src="{photo}" height="70" width="52"></div>
		<div class="bloco">
		    <div class="container">
		        <div id="block-label">{lang_title_name}:</div>
		        <div id="block-dados">{cc_name}</div>
		    </div>

		    <div class="container">
		        <div id="block-label">{lang_title_email}:</div>
		        <div id="block-dados">{lang_email}</div>
		    </div>
		    
		    <div class="container">
		        <div id="block-label">{lang_title_phone}:</div>
		        <div id="block-dados">{lang_phone}</div>
		    </div>

		</div>
	</div>
</div>
<!-- END people_ldap -->

<!-- BEGIN group -->
{group_rows}
<!-- END group -->

<!-- BEGIN group_row -->
<div id="lista_miolo">
<div class="email-geral {bg}">
	<div class="email-cabecalho">
		<p class="email-margin">{lang_name}</p>
	</div>
	<span class="btn-anexo"><a href="index.php?menuaction=mobile.{href_details}">DETALHES</a></span>
		<div class="email-corpo">
			<p class="email-margin">{lang_email}</p>
		</div>
</div>
</div>
<!-- END group_row -->