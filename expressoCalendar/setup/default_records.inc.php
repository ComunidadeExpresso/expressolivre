<?php
	/**
	*
	* Copyright (C) 2011 Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	*
	* This program is free software; you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published by
	* the Free Software Foundation; either version 3 of the License, or
	* any later version.
	*
	* This program is distributed in the hope that it will be useful, but WITHOUT
	* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
	* FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
	* details.
	*
	* You should have received a copy of the GNU General Public License
	* along with this program; if not, write to the Free Software Foundation,
	* Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
	*
	* You can contact Prognus Software Livre headquarters at Av. Tancredo Neves,
	* 6731, PTI, Edifício do Saber, 3º floor, room 306, Foz do Iguaçu - PR - Brasil
	* or at e-mail address prognus@prognus.com.br.
	*
	* Neste arquivo são inseridos os valores padrões nas tabelas do módulo expressoCalendar. 
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @version    1.0
	* @sponsor    Caixa Econômica Federal
	* @since      Arquivo disponibilizado na versão Expresso 2.4.0
	*/
	
		//Problemas com cascade na forenkey
		//calendar_object
		$oProc->query("ALTER TABLE calendar_object ADD CONSTRAINT fk_calendar_calendar_class FOREIGN KEY (class_id) REFERENCES calendar_class (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
		$oProc->query("ALTER TABLE calendar_object ADD CONSTRAINT fk_calendar_calendar_type FOREIGN KEY (type_id) REFERENCES calendar_object_type (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
		
                //calendar_to_calendar_object
                $oProc->query("ALTER TABLE calendar_to_calendar_object ADD CONSTRAINT fk_calendar_to_calendar_object_calendar FOREIGN KEY (calendar_id) REFERENCES calendar (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
                $oProc->query("ALTER TABLE calendar_to_calendar_object ADD CONSTRAINT fk_calendar_to_calendar_object_object FOREIGN KEY (calendar_object_id) REFERENCES calendar_object (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
                
		//calendar_attach
		$oProc->query("ALTER TABLE calendar_attach ADD CONSTRAINT fk_calendar_attach_attachment FOREIGN KEY (attach_id) REFERENCES attachment (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
		$oProc->query("ALTER TABLE calendar_attach ADD CONSTRAINT fk_calendar_attach_calendar FOREIGN KEY (object_id) REFERENCES calendar_object (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
		
		//calendar_participant
		$oProc->query("ALTER TABLE calendar_participant ADD CONSTRAINT fk_calendar_int_participant_calendar_object FOREIGN KEY (object_id) REFERENCES calendar_object (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
		$oProc->query("ALTER TABLE calendar_participant ADD CONSTRAINT fk_calendar_int_participant_calendar_participant_status FOREIGN KEY (participant_status_id) REFERENCES calendar_participant_status (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
		
		//calendar_alarm
		$oProc->query("ALTER TABLE calendar_alarm ADD CONSTRAINT fk_calendar_alarm_calendar_object FOREIGN KEY (object_id) REFERENCES calendar_object (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
		$oProc->query("ALTER TABLE calendar_alarm ADD CONSTRAINT fk_calendar_alarm_calendar_participant FOREIGN KEY (participant_id) REFERENCES calendar_participant (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
		
		//calendar_repeat
		$oProc->query("ALTER TABLE calendar_repeat ADD CONSTRAINT fk_calendar_repeat_calendar_object FOREIGN KEY (object_id) REFERENCES calendar_object (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");

		//calendar_repeat_occurrence
		$oProc->query("ALTER TABLE calendar_repeat_occurrence ADD CONSTRAINT fk_calendar_repeat_to_calendar_repeat_occurrence FOREIGN KEY (repeat_id) REFERENCES calendar_repeat (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");	

		//calendar_signature
		$oProc->query("ALTER TABLE calendar_signature ADD CONSTRAINT fk_calendar_signature_calendar_espec FOREIGN KEY (calendar_id) REFERENCES calendar (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
                
                //calendar_signature_alarm
		$oProc->query("ALTER TABLE calendar_signature_alarm ADD CONSTRAINT fk_calendar_signature_alarm_calendar_signature FOREIGN KEY (calendar_signature_id) REFERENCES calendar_signature (id) MATCH SIMPLE ON UPDATE CASCADE ON DELETE CASCADE;");
                
		$oProc->query("ALTER TABLE calendar_signature ALTER COLUMN dtstamp SET DEFAULT (date_part('epoch'::text, ('now'::text)::timestamp(3) with time zone) * (1000)::double precision);");
		
		$oProc->query("ALTER TABLE calendar ALTER COLUMN dtstamp SET DEFAULT (date_part('epoch'::text, ('now'::text)::timestamp(3) with time zone) * (1000)::double precision);");
	
        $oProc->query("INSERT INTO calendar_object_type( \"id\", \"name\") VALUES ('1','VEVENT');");
		$oProc->query("INSERT INTO calendar_object_type( \"id\", \"name\") VALUES ('2','TODO');");
                $oProc->query("INSERT INTO calendar_class( \"id\", \"name\") VALUES ('1','Public'),('2','Private'),('3','Confidential');");
                $oProc->query("INSERT INTO calendar_participant_status( \"id\", \"name\") VALUES ('1','CONFIRMED'),('2','TENTATIVE'),('3','CANCELLED'),('4','UNANSWERED'),('5', 'DELEGATED');");

                //Admin conf
                $oProc->query("INSERT INTO phpgw_hooks( \"hook_appname\", \"hook_location\", \"hook_filename\") VALUES ('expressoCalendar','admin', 'hook_admin.inc.php');");
?>