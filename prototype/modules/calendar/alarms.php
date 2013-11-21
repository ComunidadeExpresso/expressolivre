<?php

if(!defined('ROOTPATH'))
    define('ROOTPATH', dirname(__FILE__).'/../..');

require_once ROOTPATH.'/api/controller.php';
require_once ROOTPATH.'/modules/calendar/constants.php';
require_once ROOTPATH.'/api/parseTPL.php';

use prototype\api\Config as Config;

$target = (gmdate('U') - 300 ).'000';

$parts = Controller::service('PostgreSQL')->execSql('SELECT part.user_info_id as "user", co.id as "schedulable", co.type_id as "type", co.allDay as "allDay" ,co.dtend as "endTime", co.dtstart as "startTime", co.summary as "summary", co.tzid as "timezone", co.location as "location", al.id as "id" FROM calendar_object as co INNER JOIN calendar_alarm al ON co.id = al.object_id JOIN calendar_participant part  ON part.id = al.participant_id LEFT JOIN calendar_repeat rep ON  rep.object_id = co.id  LEFT JOIN calendar_repeat_occurrence occ ON occ.repeat_id = rep.id WHERE ( al.action_id = \''.ALARM_MAIL.'\' AND al.sent = \'0\' AND CASE WHEN occ.occurrence > 0 THEN occ.occurrence - al.alarm_offset ELSE co.dtstart - al.alarm_offset END BETWEEN \''.$target.'\' AND \''.($target + 360000).'\') ');

if(!is_array($parts))
  return;

$ids = array();

foreach ($parts as $i => $part)
{
	///Montando lista de participantes

	$users = Controller::find( array( 'concept' => 'participant' ) , array( 'user', 'id', 'isExternal' ) ,array('filter' => array ('=', 'schedulable' , $part['schedulable'] ), 'deepness' => 1 ) );

	$attList = array();

	foreach( $users as $user )
	{
	    if( $part['user'] === $user['user']['id'] )
		$part['mail'] = $user['user']['mail'];

	    $attList[] = $user['user']['name'];
	}

        $timezone = new DateTimeZone('UTC');
	$sTime = new DateTime('@' . (int) ($part['startTime'] / 1000), $timezone);
	$eTime = new DateTime('@' . (int) ($part['endTime'] / 1000), $timezone);

        $timezone = $part['timezone'];
        $sTime->setTimezone(new DateTimeZone($part['timezone']));
        $eTime->setTimezone(new DateTimeZone($part['timezone']));
        
	$data = array('startDate' =>  date_format( $sTime , 'd/m/Y') ,
		      'startTime' =>  $part['allDay'] ? '' : date_format( $sTime , 'H:i'),
		      'endDate' =>  date_format( $eTime , 'd/m/Y') ,
		      'endTime' =>  $part['allDay'] ? '' : date_format( $eTime , 'H:i'),
		      'eventTitle' =>  $part['summary'],
		      'eventLocation' =>  $part['location'],
		      'timezone' => $timezone,
		      'participants' =>  '<UL> <LI> '.implode( '<LI></LI> ', $attList ).'</LI> </UL>');

	Controller::create( array( 'service' => 'SMTP' ), array( 'body' => parseTPL::load_tpl( $data, ROOTPATH.'/modules/calendar/templates/'. ($parts['type'] == '1' ? 'notify_alarm_body.tpl' : 'notify_alarm_body_task.tpl')),
								  'isHtml' => true,
								  'subject' => 'Alarme de Calendario',
								  'from' => $part['mail'],
								  'to' => $part['mail'] ) );

	Config::regSet('noAlarm', TRUE); //Evita o envio de notificação ?????
	$ids[] = $part['id'];
}

if( !empty( $ids ) )
    Controller::update( array( 'concept' => 'alarm' ) , array('sent' => '1'), array('filter' => array( 'IN', 'id', $ids ) ));

?>
