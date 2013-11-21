<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

			$oProc->query("ALTER TABLE expressomail_message_followupflag ADD CONSTRAINT expressomail_message_followupflag_followupflag_id_fkey FOREIGN KEY (followupflag_id) REFERENCES expressomail_followupflag (id);");
			
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Follow up');");
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Read');");
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Forward');"); 
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Answer');"); 
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Don''t forward');");
			$oProc->query("INSERT INTO expressomail_followupflag(name) VALUES ('Don''t answer');");
						
			/* Seta o valor padrão para a configuração de número máximo de marcadores */
			$oProc->query("INSERT INTO phpgw_config(config_app, config_name, config_value) VALUES ('expressoMail1_2', 'expressoMail_limit_labels', 20);");
			$oProc->query("INSERT INTO phpgw_config(config_app, config_name, config_value) VALUES ('expressoMail1_2', 'allow_hidden_copy', 'True');");
			
			/* Registra o hook de validação do administrador*/
			$oProc->query("INSERT INTO phpgw_hooks( \"hook_appname\", \"hook_location\", \"hook_filename\") VALUES ('expressoMail1_2', 'config_validate', 'hook_config_validate.inc.php')");

			/* Cria um indice unico para um owner e mail para nao ocorrer duplicidade em e-mails para um mesmo owner  */
			$oProc->query("ALTER TABLE expressomail_dynamic_contact ADD CONSTRAINT owner_mail UNIQUE (owner, mail)");
?>
