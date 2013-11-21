<!-- BEGIN rows_mails -->
{rows}
<!-- END rows_mails -->
<!-- BEGIN row_mails -->

			 <div class="{bg}">
                    <div class="email-cabecalho margin-geral">
                         <p class="email-normal {flag}">
                         	<input type="checkbox" name="msgs[]" style="display:{show_check};" value={msg_number}>
                         	<a href="index.php?menuaction=mobile.ui_mobilemail.show_msg&msg_number={msg_number}&msg_folder={msg_folder}">
                         		{subject}
                         	</a>
                         </p>
                    </div>
                    <span class="email-data"><img src="{url_images}/anexo.png" align="left" style='display:{show_attach};'/>{mail_time}</span>
                    <div class="{details}">
                         <a style="color:#837e7e !important;" href="index.php?menuaction=mobile.ui_mobilemail.show_msg&msg_number={msg_number}&msg_folder={msg_folder}">                    
	                         <p>{from}</p>
	                         <p>{mail_from}</p>
                         </a>	                         
                     </div>
   			 </div>


<!-- END row_mails -->
<!-- BEGIN no_messages -->
	<dt class="titulo_mensagem reset-dt">
					{lang_no_results}
	</dt>
<!-- END no_messages -->