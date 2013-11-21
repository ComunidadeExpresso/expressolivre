<!-- BEGIN rows_contacts -->
{rows}
<!-- END rows_contacts -->
<!-- BEGIN row_contacts -->

            <div class="email-geral {bg}">
                <div class="contato-cabecalho margin-geral" >
                     <p><input type="checkbox" name="contacts[]" style="display:{show_check};" value="{contact_id}"> {contact_name}</p>
                </div>
                <span class="btn-anexo"><a href="index.php?menuaction=mobile.{href_details}">{lang_see_details}</a></span>
                <div class="{details}">
                    <p>{email}</p>
                    <p style="display:{show_tel};"><span>{lang_tel}:</span>{tel}</p>
                 </div>
   			 </div>
				
<!-- END row_contacts -->
<!-- BEGIN row_groups -->
	<dt class="titulo_mensagem {bg}">
		<div class="contato-cabecalho margin-geral" >
             <p><input type="checkbox" name="contacts[]" style="display:{show_check};" value="{group_id}"> {group_name}</p>
        </div>
        <span class="btn-anexo"><a href="index.php?menuaction=mobile.{href_details}">{lang_see_details}</a></span>
	</dt>
<!-- END row_groups -->
<!-- BEGIN no_contacts -->
	<dt class="titulo_mensagem reset-dt">
					{lang_no_results}
	</dt>
<!-- END no_contacts -->