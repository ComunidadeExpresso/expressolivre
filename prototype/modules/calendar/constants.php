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
	* Neste arquivo são definidas as contantes a serem utilizadas pelo módulo ExpressoCalendar. 
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @version    1.0
	* @sponsor    Caixa Econômica Federal
	* @since      Arquivo disponibilizado na versão Expresso 2.4.0
	*/

define('EVENT_ID', 1);
define('TODO_ID', 2);

define('SIGNATURE_TYPE_NORMAL', 0);
define('SIGNATURE_TYPE_DEFAULT', 1);

define('CALENDAR_TYPE_EVENT', 0);
define('CALENDAR_TYPE_TASK_GROUP', 1);

define('STATUS_CONFIRMED', 1);
define('STATUS_ACCEPTED', 1);
define('STATUS_TENTATIVE', 2);
define('STATUS_DECLINED', 3);
define('STATUS_CANCELLED', 3);
define('STATUS_UNANSWERED', 4);
define('STATUS_DELEGATED', 5);

define('ALARM_ALERT', 1);
define('ALARM_MAIL', 2);
define('ALARM_SMS', 3);

define('TRANSP_OPAQUE', 0);
define('TRANSP_TRANSPARENT', 1);

define('CLASS_PUBLIC', 1);
define('CLASS_PRIVATE', 2);
define('CLASS_CONFIDENTIAL', 3);

define('ICAL_ACTION_IMPORT', 1);
define('ICAL_ACTION_UPDATE', 2);
define('ICAL_ACTION_DELETE', 3);
define('ICAL_ACTION_NONE', 4);
define('ICAL_ACTION_REPLY', 5);
define('ICAL_ACTION_SUGGESTION', 6);
define('ICAL_ACTION_IMPORT_REQUIRED', 7);
define('ICAL_ACTION_ORGANIZER_UPDATE', 8);
define('ICAL_ACTION_ORGANIZER_NONE', 9);
define('ICAL_ACTION_IMPORT_FROM_PERMISSION', 10);
define('ICAL_ACTION_NONE_FROM_PERMISSION', 11);
define('ICAL_NOT_FOUND', 12);

define('ATTENDEE_ACL_ORGANIZATION', 'o');
define('ATTENDEE_ACL_WRITE', 'w');
define('ATTENDEE_ACL_PARTICIPATION_REQUIRED', 'p');
define('ATTENDEE_ACL_INVITE_GUESTES', 'i');
define('ATTENDEE_ACL_READ', 'r');

define('CALENDAR_PRIVATE', 0);
define('CALENDAR_PUBLIC', 1);

define('CALENDAR_ACL_WRITE', 'w');
define('CALENDAR_ACL_READ', 'r');
define('CALENDAR_ACL_REMOVE', 'd');
define('CALENDAR_ACL_BUSY', 'b');
define('CALENDAR_ACL_SHARED', 's');
define('CALENDAR_ACL_REQUIRED', 'p');

define('PRIORITY_HIGH', 1);
define('PRIORITY_NORMAL', 2);
define('PRIORITY_LOW', 3);

define('STATUS_TODO_NEED_ACTION', 1);
define('STATUS_TODO_IN_PROGRESS', 2);
define('STATUS_TODO_COMPLETED', 3);
define('STATUS_TODO_CANCELLED', 4);

define('EVENT_NOT_EDITABLE', 0);
define('EVENT_EDITABLE', 1);
define('EVENT_EDITABLE_FROM_PERMISSION', 2)
?>
