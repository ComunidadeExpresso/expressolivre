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
	* Neste arquivo são ser implementadas regras de negócio para a exportação de eventos e tarefas do ExpressoCalendar. 
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @version    1.0
	* @sponsor    Caixa Econômica Federal
	* @since      Arquivo disponibilizado na versão Expresso 2.4.0
	*/

require_once '../../api/controller.php';
$params = $_GET;

//if( isset($params['calendars']) )
//{
//    if(!is_array($params['calendars']))
//       $params['calendars'] = array($params['calendars']);
//   
//   foreach ($params['calendars'] as &$calendar)
//   {
//       $eventLinks = Controller::find(array('concept' => 'calendarToSchedulable') , array('schedulable') , array('filter' => array( '=' , 'calendar' , $calendar)));
//       
//       $eventsIds = array();
//       foreach ($eventLinks as &$eventLink)
//           $eventsIds[] = $eventLink['schedulable'];
//       
//       $events = Controller::find(array('concept' => 'schedulable') , false , array('filter' => array('IN','id',$eventsIds) , 'deepness' => '2' ));
//       $ics = Controller::format( array( 'service' => 'iCal' ) , $events );
//       
//   }
//
//}

if( isset($params['calendar']) )
{
    $eventLinks = Controller::find(array('concept' => 'calendarToSchedulable') , array('schedulable') , array('filter' => array( '=' , 'calendar' , $params['calendar'])));
    $calendar = Controller::read(array('concept' => 'calendar' , 'id' => $params['calendar']));

    $eventsIds = array();
    foreach ($eventLinks as &$eventLink)
       $eventsIds[] = $eventLink['schedulable'];

    $events = Controller::find(array('concept' => 'schedulable') , false , array('filter' => array('IN','id',$eventsIds) , 'deepness' => '2', 'timezones' => array($calendar['id'] => $calendar['timezone']) ));	
    $ics = Controller::format( array( 'service' => 'iCal' ) , $events , array('defaultTZI' => $calendar['timezone']) );

    header( 'Content-Type: text/calendar; charset=utf-8' );
    header( 'Content-Length: '.  mb_strlen($ics) );
    header( 'Content-Disposition: attachment; filename="Calendar.ics"' );
    header( 'Cache-Control: max-age=10' );
    echo $ics;
    die(); 
}

if( isset($params['event']) )
{    
    $event = Controller::read(array('concept' => 'schedulable' , 'id' => $params['event']));
    $attachmentRelation = Controller::find( array( 'concept' => 'schedulableToAttachment' ) , false ,array( 'filter' => array('=', 'schedulable'  ,  $event['id']) )); 
    if(is_array($attachmentRelation)){
	    $attachments = array();
	    foreach($attachmentRelation as $key => $value)
		    if(isset($value['attachment']) || !!$value['attachment'])
			    $attachments[$key]  = $value['attachment'];
	    //Pega os anexos sem source
	    $event['attachments'] = Controller::find( array( 'concept' => 'attachment' ) , false ,array( 'filter' => array('IN', 'id' , $attachments) )); 
    }
        
    $repeat = Controller::find( array( 'concept' => 'repeat' ) , false ,array( 'filter' => array('=', 'schedulable'  ,  $event['id']) ));    
        
    if(is_array($repeat))
        $event['repeat'] = $repeat[0];

     
    $ics = Controller::format( array( 'service' => 'iCal' ) , array($event) , array('defaultTZI' => $event['timezone']) );
    
    header( 'Content-Type: text/calendar; charset=utf-8' );
    header( 'Content-Length: '.  mb_strlen($ics) );
    header( 'Content-Disposition: attachment; filename="'.$event['summary'].'.ics"' );
    header( 'Cache-Control: max-age=10' );
    echo $ics;
    die(); 
}

?>
