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
	* Neste arquivo são ser implementadas regras de negócio para consistir e normalizar os dados correspondentes às operações do usuário para o ExpressoCalendar. 
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @version    1.0
	* @sponsor    Caixa Econômica Federal
	* @since      Arquivo disponibilizado na versão Expresso 2.4.0
	*/
	
	
//Definindo Constantes
require_once ROOTPATH . '/modules/calendar/constants.php';

require_once ROOTPATH . '/modules/calendar/interceptors/Helpers.php';

use prototype\api\Config as Config;

/**
* Classe com implementações das regras de negócio para consistir e normalizar os dados correspondentes às operações do usuário para o ExpressoCalendar.
*
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @sponsor    Caixa Econômica Federal
* @version    1.0
* @since      Classe disponibilizada na versão Expresso 2.4.0 
*/

class DBMapping extends Helpers {



    /*
     * Verificar inconsistencia no FF
     * */
    static function encodeCreateParticipant(&$uri, &$params, &$criteria, $original)
    {
        if(isset($params['delegatedFrom']) && is_array($params['delegatedFrom'])){
            $params['delegatedFrom'] = $params['delegatedFrom']['id'];
        }
    }

    static function validateCreateSchedulable(&$uri, &$params, &$criteria, $original)
    {
        $permission = Controller::find(array('concept' => 'calendarToPermission'), false, array('filter' => array('AND', array('=', 'calendar', $params['calendar']), array('=', 'user', Config::me('uidNumber')), 'deepness' => 2)));
        if (is_array($permission) && $permission[0]['acl'] == 'r')
            return false;
    }

    static function encodeCreateSchedulable(&$uri, &$params, &$criteria, $original) 
    {

        if($params['type'] == 2 && $params['allDay'] == 0){
            $params['due'] = date('Y-m-d', strtotime($params['startTime']));
            $params['endTime'] = $params['due'] . ' 23:59';
            $params['due'] = self::parseTimeDate($params['due'] . ' 23:59', $params['timezone']);
        }

    	if (isset($params['startTime']) && !is_numeric($params['startTime']))
    	    $params['startTime'] = self::parseTimeDate($params['startTime'], $params['timezone']);
	
        $params['rangeStart'] = $params['startTime'];
	
    	if (isset($params['endTime']) && !is_numeric($params['endTime'])) {
    	    $params['endTime'] = self::parseTimeDate($params['endTime'], $params['timezone']);

    	    if ($params['allDay'])
    	       	$params['endTime'] = $params['endTime'] + 86400000;
	    }
	    
        $params['rangeEnd'] = $params['endTime'];
    	

        if (isset($params['due']) && $params['due'] != '' && !is_numeric($params['due'])){
            $params['due'] = self::parseTimeDate($params['due'], $params['timezone']);

            if ($params['allDay'])
                $params['due'] = $params['due'] + 86400000;
        }


    	///////////////////////////////////////////////////////////////////

    	$params['dtstamp'] = (isset($params['dtstamp'])) ? $params['dtstamp'] : time() . '000';
    	$params['lastUpdate'] = (isset($params['lastUpdate'])) ? $params['lastUpdate'] : time() . '000';
    	$params['uid'] = isset($params['uid']) ? $params['uid'] : self::_makeUid();
        }

    static function parseTimeDate($time, $timezone) {
	    return strtotime($time . ' ' . $timezone) . '000';
    }

     public function encodeCreateAlarm( &$uri , &$params , &$criteria , $original ){
      	
        if(!isset($params['schedulable']) || !isset($params['rangeStart']) || !isset($params['rangeEnd']) )
        {
            $participant = Controller::read( array( 'concept' => 'participant' , 'id' => $params['participant'] ) , array('schedulable')  );

            $params['schedulable'] = $participant['schedulable'];

	    $params['type'] = self::codeAlarmType($params['type']);

	    $params['offset'] = $params['time'] * 1000;

	    switch( strtolower($params['unit']) )
	    {
		case 'd': $params['offset'] *= 24;
		case 'h': $params['offset'] *= 60;
		case 'm': $params['offset'] *= 60;
	    }
        }
    }

    public function encodeCreateSuggestion(&$uri, &$params, &$criteria, $original) {
	$params['dtstamp'] = (isset($params['dtstamp'])) ? $params['dtstamp'] : time() . '000';
    }

    public function encodeUpdateAlarm(&$uri, &$params, &$criteria, $original) {
	if (isset($params['type']))
	    $params['type'] = self::codeAlarmType($params['type']);
	else{
	    $alarm = Controller::read( array('concept' => 'alarm' , 'id' => $params['id'] ));

	    $params['unit'] = $alarm['unit'];
	}
	$params['offset'] = $params['time'] * 1000;


	switch( strtolower($params['unit']) )
	{
	    case 'd': $params['offset'] *= 24;
	    case 'h': $params['offset'] *= 60;
	    case 'm': $params['offset'] *= 60;
	}
	
    }

    public function encodeCreateAttachment(&$uri, &$params, &$criteria, $original) {

	if (!isset($params['source']))
	    return false;

	if (isset($_FILES[$params['source']]))
	    if (isset($params['id']))
		$params = array_merge($_FILES[$params['source']], array('id' => $params['id']));
	    else
		$params = $_FILES[$params['source']];

	if (isset($params['owner']))
	    $params['owner'] = Config::me('uidNumber');
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function encodeSignatureAlarmType(&$uri, &$params, &$criteria, $original) {
	    $params['type'] = self::codeAlarmType($params['type']);
    }

    public function schedulableSecurity(&$uri, &$params, &$criteria, $original)
    {

        if( !isset($criteria['filter'][1]) || !$criteria['filter'][1] == 'uid')
        {
            $security = 'schedulable.id = calendarToSchedulable.schedulable';
            $security .= ' AND calendar.id = calendarToSchedulable.calendar';
            $security .= ' AND calendar.id = calendarSignature.calendar';
            $security .= ' AND calendarSignature.user = ' . base64_encode(Config::me('uidNumber'));

            $criteria['condition'] = $security;
        }

    }

    public function calendarSecurity(&$uri, &$params, &$criteria, $original)
    {
        $security = 'calendar.id = calendarSignature.calendar';
        $security .= ' AND calendarSignature.user = ' . base64_encode(Config::me('uidNumber'));

        $criteria['condition'] = $security;
    }

    public function calendarSignatureSecurity(&$uri, &$params, &$criteria, $original)
    {
//        $security = 'calendarSignature.user = ' . base64_encode(Config::me('uidNumber'));
//
//        $criteria['condition'] = $security;
    }

    public function insertOwnerLink(&$uri, &$params, &$criteria, $original) {
	$params['owner'] = Config::me('uidNumber');
    }

    public function encodeServiceUser(&$uri, &$params, &$criteria, $original) {
	if (isset($params['isExternal']) && $params['isExternal'] == '1')
	    $uri['service'] = 'PostgreSQL';
    }

    public function prepareRepeat(&$uri, &$params, &$criteria, $original) {
        if (isset($params['startTime']) || isset($params['endTime'])) {

            if(!isset($params['schedulable']))
            $params = array_merge($params , Controller::read(array('concept' => 'repeat', 'id' => $params['id']), array('schedulable')));

            $timezone = Controller::read(array('concept' => 'schedulable', 'id' => $params['schedulable']), array('timezone'));


            if ( isset($params['startTime']) && !is_numeric($params['startTime']) )
                $params['startTime'] = self::parseTimeDate($params['startTime'], $timezone['timezone']);
            if ( isset($params['endTime']) && !is_numeric($params['endTime']))
                $params['endTime'] = self::parseTimeDate($params['endTime'], $timezone['timezone']);

        }
    }

    public function findSchedulable(&$uri, &$params, &$criteria, $original) {
        if (isset($criteria['customQuery']) && $criteria['customQuery'] == '1') {

            $sql = ' SELECT calendar_object.id as id ,calendar_object.cal_uid as "uid", calendar_object.type_id as "type", '
            .'calendar_object.dtstart as "startTime", calendar_object.summary as "summary", '
            .'calendar_object.description as "description", calendar_object.dtend as "endTime" , '
            .'calendar_object.priority as "priority", calendar_object.due as "due", '
            .'calendar_object.percentage as "percentage", calendar_object.status as "status", '
            .'calendar_object.location as "location", calendar_object.allday as "allDay", '
            .'calendar_object.transp as "transparent", calendar_object.class_id as "class", '
            .'calendar_object.repeat as "repeat", calendar_object.range_start as "rangeStart", '
            .'calendar_object.range_end as "rangeEnd", calendar_object.last_update as "lastUpdate", '
            .'calendar_object.dtstamp as "dtstamp", calendar_object.sequence as "sequence", '
            .'calendar_object.tzid as "timezone" ,calendar_to_calendar_object.calendar_id as '
            .'calendar FROM calendar_to_calendar_object , calendar_object '
            .'WHERE ( calendar_to_calendar_object.calendar_id IN (\'' . implode('\',\'', $criteria['calendar']) . '\')) '
            .'AND calendar_to_calendar_object.calendar_object_id = calendar_object.id '
            .'AND calendar_object.id NOT IN(select calendar_object_task_id from calendar_task_to_activity_object where owner = \'' . Config::me('uidNumber') . '\') ';

            if(isset($criteria['searchEvent']) && $criteria['searchEvent']){
                $where = 'AND (((upper("summary") like upper(\'%'.$criteria['filter'][1][1][2].'%\') OR upper("description") like upper(\'%'.$criteria['filter'][1][2][2].'%\')))) ORDER BY dtstart LIMIT '.$criteria['limit'].'  OFFSET '.$criteria['offset'].' ';
                $params = Controller::service('PostgreSQL')->execResultSql($sql.$where);

            }else{
                $start = $criteria['rangeStart'];
                $end = $criteria['rangeEnd'];

                $ids = array();
                $occ = array();

                if ($occurrences = self::checkOccurrences($start, $end, $criteria['calendar']))
                    foreach ($occurrences as $id => $occurrence) {
                        $ids[] = $id;
                        $occ[] = $occurrence;
                    }

                $where = 'AND ((range_end >= \'' . $start . '\' AND range_end <= \'' . $end . '\') OR '
                    .'(range_start >= \'' . $start . '\' AND range_start <= \'' . $end . '\') OR '
                    .'(range_start <= \'' . $start . '\' AND range_end >= \'' . $end . '\')) '
                .(!empty($ids) ? ' ' .'AND calendar_object.id NOT IN (\'' . implode('\',\'', $ids) . '\') ' : ' ')
                .'AND calendar_object.dtstart NOT IN (SELECT calendar_repeat_occurrence.occurrence from calendar_repeat_occurrence, '
                .'calendar_repeat where (calendar_repeat_occurrence.repeat_id = calendar_repeat.id) '
                .'AND (calendar_repeat.object_id = calendar_object.id))';

                $params = Controller::service('PostgreSQL')->execResultSql($sql.$where);
                $params = array_merge($params, $occ);
            }


            $params = self::deepnessFindEvent($uri, $params, $criteria, $original);
            return false;
        }
    }

    public function findTask(&$uri, &$params, &$criteria, $original) {

	if (isset($criteria['filterTasks']) && $criteria['filterTasks']) {

	    $sql = ' SELECT calendar_object.id as id ,calendar_object.cal_uid as "uid", calendar_object.type_id as "type", '
	    .'calendar_object.dtstart as "startTime", calendar_object.summary as "summary", '
	    .'calendar_object.description as "description", calendar_object.dtend as "endTime" , '
	    .'calendar_object.priority as "priority", calendar_object.due as "due", '
	    .'calendar_object.percentage as "percentage", calendar_object.status as "status", '
	    .'calendar_object.location as "location", calendar_object.allday as "allDay", '
	    .'calendar_object.transp as "transparent", calendar_object.class_id as "class", '
	    .'calendar_object.repeat as "repeat", calendar_object.range_start as "rangeStart", '
	    .'calendar_object.range_end as "rangeEnd", calendar_object.last_update as "lastUpdate", '
	    .'calendar_object.dtstamp as "dtstamp", calendar_object.sequence as "sequence", '
	    .'calendar_object.tzid as "timezone" ,calendar_to_calendar_object.calendar_id as '
	    .'calendar FROM calendar_to_calendar_object , calendar_object '
	    .'WHERE ( calendar_to_calendar_object.calendar_id IN (\'' . implode('\',\'', $criteria['filter'][3][2]) . '\')) '
	    .'AND calendar_to_calendar_object.calendar_object_id = calendar_object.id '
        .'AND calendar_to_calendar_object.calendar_object_id NOT IN (select calendar_object_activity_id from calendar_task_to_activity_object where owner =  \'' . Config::me("uidNumber") . '\' ) '    
        .'AND calendar_to_calendar_object.calendar_object_id NOT IN (select calendar_object_task_id from calendar_task_to_activity_object where owner =  \'' . Config::me("uidNumber") . '\' ) ';    

        if(isset($criteria['filter'][4]))
            $sql .= 'AND (((upper("summary") like upper(\'%'.$criteria['filter'][4][1][2].'%\') OR upper("description") like upper(\'%'.$criteria['filter'][4][1][2].'%\'))))';

	    $sql .= 'AND (range_start >= \'' . $criteria['filter'][2][2] . '\' AND type_id <= \'' .  $criteria['filter'][1][2] . '\')';

	    $params = Controller::service('PostgreSQL')->execResultSql($sql);
	    $params = self::deepnessFindEvent($uri, $params, $criteria, $original);
	    return false;
	}
    }

    public function deepnessFindRepeatOccurrence(&$uri, &$result, &$criteria, $original) {

	if (!isset($criteria['deepness']) || $criteria['deepness'] == 0)
	    return;

	foreach ($result as $i => &$res)
	    if (isset($res['repeat']))
		$res['repeat'] = Controller::read(array('concept' => 'repeat', 'id' => $res['repeat']), false, array('deepness' => intval($criteria['deepness']) - 1));
    }

    public function deepnessRepeat(&$uri, &$result, &$criteria, $original) {

	if (!isset($criteria['deepness']) || $criteria['deepness'] == 0)
	    return;

	$result['schedulable'] = Controller::find(array('concept' => 'schedulable'), false, array('filter' => array('=', 'id', $result['schedulable']), 'deepness' => intval($criteria['deepness']) - 1));
	$result['schedulable'] = $result['schedulable'][0];
    }

    public function saveOccurrences(&$uri, &$result, &$criteria, $original) {
	$ranges = Controller::find(array('concept' => 'repeatRange'), array('rangeStart', 'rangeEnd'), array('filter' => array('=', 'user', Config::me("uidNumber"))));

	if (!is_array($ranges) || !isset($ranges[0]['rangeStart']) || !isset($ranges[0]['rangeEnd']))
	    return;

	if (isset($result['id']))
	    $id = $result['id'];
	else
	    $id = $uri['id'];

	$repeat = Controller::read(array('concept' => 'repeat', 'id' => $id));

	unset($repeat['id']);

	$exceptions = array();

	if (isset($original['properties']['exceptions'])) {
	    $exceptions = explode(',', $original['properties']['exceptions']);
	    $event = Controller::read(array('concept' => 'schedulable', 'id' => $repeat['schedulable']));

        if(array_key_exists(0, $event)) $event = $event[0];

        $date = new DateTime('now', new DateTimeZone('UTC'));

        foreach($exceptions as &$e){
            $date->setTimestamp((int) ($e / 1000));
            $date->setTimezone( new DateTimeZone( $event['timezone'] ));
            $e = ($date->getTimestamp() - $date->getOffset()).'000';
        }

        unset($repeat['exceptions']);
	}

    unset($repeat['schedulable']);

    $lastExceptions = Controller::find(array('concept' => 'repeatOccurrence'), array("occurrence"), array('filter' => array('AND', array('=', 'repeat', $id), array('=', 'exception', 1))));

	//Recurepa as execeções anteriores caso exista
	if (isset($lastExceptions) && count($lastExceptions) && $lastExceptions)
	    foreach ($lastExceptions as $value)
		array_push($exceptions, $value['occurrence']);

	$params = array_diff(self::decodeRepeat($repeat, $ranges[0]['rangeStart'], $ranges[0]['rangeEnd']), $exceptions);

	Controller::delete(array('concept' => 'repeatOccurrence'), false, array('filter' => array('=', 'repeat', $id)));

	if (!empty($params))
	    Controller::service('PostgreSQL')->execResultSql("INSERT INTO calendar_repeat_occurrence(repeat_id,exception,occurrence)VALUES('" . $id . "','0','" . implode("'),('" . $id . "','0','", $params) . "')" . ( empty($exceptions) ? "" : ",('" . $id . "','1','" . implode("'),('" . $id . "','1','", $exceptions) . "')" ));
	else if(!empty($exceptions))
	    Controller::service('PostgreSQL')->execResultSql("INSERT INTO calendar_repeat_occurrence(repeat_id,exception,occurrence)VALUES ('" . $id . "','1','" . implode("'),('" . $id . "','1','", $exceptions) . "')" );
    }

    public function checkOccurrences($start, $end, $calendarIds) {

	$ranges = Controller::find(array('concept' => 'repeatRange'), array('rangeStart', 'rangeEnd'), array('filter' => array('=', 'user', Config::me("uidNumber"))));
	$ranges = $ranges[0];

	$origStart = $start;
	$origEnd = $end;

	if ($initialized = (isset($ranges['rangeStart']) && isset($ranges['rangeEnd']))) {
	    if ($ranges['rangeStart'] <= $start)
		$start = false;
	    if ($ranges['rangeEnd'] >= $end)
		$end = false;
	}

	$repeats = self::findRepeats($calendarIds);
	if (!is_array($repeats) || empty($repeats))
	    return( false );

	$result = array();
	$ids = array();

	foreach ($repeats as $repeat) {
	    $ids[] = $id = $repeat['id'];
	    unset($repeat['id']);

	    if (!isset($result[$id]))
		$result[$id] = !$initialized ? array($repeat['startTime']) : array();

	    if (!$initialized)
		$result[$id] = array_merge($result[$id], self::decodeRepeat($repeat, $start, $end));
	    else {
		if ($start)
		    $result[$id] = array_merge($result[$id], self::decodeRepeat($repeat, $start, $ranges['rangeStart']));

		if ($end)
		    $result[$id] = array_merge($result[$id], self::decodeRepeat($repeat, $ranges['rangeEnd'], $end));
	    }

	    if (empty($result[$id]))
		unset($result[$id]);
	}

	if ($start || $end) {
	    Controller::begin(array('service' => 'PostgreSQL'));

	    foreach ($result as $id => $res){

            $ocurrences = array_unique($res);

            /*
             * Check current range decoded
             * */
            $current = Controller::find(array('concept' => 'repeatOccurrence'), array("occurrence"), array('filter' => array('=', 'repeat', $id)));
            $toDiff = array();

            if(!empty($current))
                foreach($current as $c) $toDiff[] = $c['occurrence'];

            $ocurrences = array_diff($ocurrences, $toDiff);

            if(!empty($ocurrences))
                Controller::service('PostgreSQL')->execResultSql("INSERT INTO calendar_repeat_occurrence(repeat_id,occurrence)VALUES('" . $id . "','" . implode("'),('" . $id . "', '", $ocurrences) . "')");

        }
	    $data = array();

	    if ($start)
		$data['rangeStart'] = $start;

	    if ($end)
		$data['rangeEnd'] = $end;

	    if (!$initialized)
		$data['user'] = Config::me('uidNumber');

	    Controller::call(( $initialized ? 'replace' : 'create'), array('concept' => 'repeatRange'), $data, array('filter' => array('=', 'user', Config::me('uidNumber'))));

	    Controller::commit(array('service' => 'PostgreSQL'));
	}

// 	$return = Controller::find( array( 'concept' => 'repeatOccurrence' ), false, array( 'filter' => array( 'AND', array( '>=', 'occurrence', $origStart ), array( '<=', 'occurrence', $origEnd ), array( 'IN', 'repeat', $ids ) ), 'deepness' => $deep ) );

	$return = Controller::service('PostgreSQL')->execResultSql('SELECT calendar_repeat_occurrence.occurrence as "occurrence", calendar_repeat.object_id as "schedulable" FROM calendar_repeat, calendar_repeat_occurrence WHERE calendar_repeat_occurrence.occurrence >= \'' . $origStart . '\' AND calendar_repeat_occurrence.occurrence <= \'' . $origEnd . '\' AND calendar_repeat_occurrence.repeat_id IN (\'' . implode('\',\'', $ids) . '\') AND calendar_repeat.id = calendar_repeat_occurrence.repeat_id AND calendar_repeat_occurrence.exception != 1 order by calendar_repeat_occurrence.occurrence');

	if (!is_array($return))
	    return( false );

	$result = array();
	$params = array();
    $realResult = array();
	foreach ($return as $ret) {
	    $currentId = $ret['schedulable'];

	    if (!isset($result[$currentId])) {
		$result[$currentId] = Controller::read(array('concept' => 'schedulable', 'id' => $currentId));
		$result[$currentId]['occurrences'] = array();
	    }

	    $result[$currentId]['occurrences'][] = $ret['occurrence'];
	}

    foreach($result as $i => $v)
    {
        $calendarToCalendarObj = self::schedulable2calendarToObject($v['id']);
            foreach($calendarToCalendarObj as $vv)
            {
                $v['calendar'] = $vv['calendar_id'];
                $realResult[] = $v;
            }
    }

	return( $realResult );
    }

    public static function findRepeats($ids) {
	return Controller::service('PostgreSQL')->execResultSql('SELECT calendar_repeat.wkst as "wkst", calendar_repeat.byweekno as "byweekno", calendar_repeat.byminute as "byminute", calendar_repeat.bysecond as "bysecond", calendar_repeat.byyearday as "byyearday", calendar_repeat.bymonthday as "bymonthday", calendar_repeat.bysetpos as "bysetpos", calendar_repeat.byday as "byday", calendar_repeat.byhour as "byhour", calendar_repeat.interval as "interval", calendar_repeat.frequency as "frequency", calendar_repeat.until as "endTime", calendar_repeat.id as "id", calendar_repeat.count as "count", calendar_repeat.dtstart as "startTime" FROM calendar_repeat, calendar_to_calendar_object WHERE calendar_repeat.object_id = calendar_to_calendar_object.calendar_object_id AND calendar_to_calendar_object.calendar_id IN (\'' . implode('\',\'', $ids) . '\')');
    }

//HELPERS
    public static function decodeRepeat($repeat, $start, $end) {

	date_default_timezone_set('UTC');

	require_once ROOTPATH . '/plugins/when/When.php';

	$r = new When();

	if ($repeat['frequency'] === 'none')
	    return( array() );

	//Nao deve ser usando o horário da repeticao pois nela contem apenas o dias,
	//deve se recuperar o horário do evento para um correto calculo.
	if (max($start, $repeat['startTime']) != $repeat['startTime']) {
	    $time = new DateTime('@' . (int) ( $repeat['startTime'] / 1000 ), new DateTimeZone('UTC'));

	    $hoursOcurrence = new DateTime('@' . (int) ( $start / 1000 ), new DateTimeZone('UTC'));
	    $hoursOcurrence = $hoursOcurrence->format('H');

	    $diffTime = ((($time->format('H') - $hoursOcurrence) * (3600000)) + ($time->format('i') * (60000)));
	    $start = new DateTime('@' . (int) ( ( $start + $diffTime ) / 1000 ), new DateTimeZone('UTC'));
	}else
	    $start = new DateTime('@' . (int) ( max($start, $repeat['startTime']) / 1000 ), new DateTimeZone('UTC'));

	foreach ($repeat as $rule => $value) {
	    if (!isset($value) || !$value || $value === "0")
		continue;

	    switch (strtolower($rule)) {
		case "starttime": break;
		case "frequency":
		    $r->recur($start, $value);
		    break;
		case "endtime":
		    $r->until(new DateTime('@' . (int) ( $value / 1000 )));
		    break;
		case "count": case "interval": case "wkst":
		    $r->$rule($value);
		    break;
		default :
		    $r->$rule(!is_array($value) ? explode(',', $value) : $value );
		    break;
	    }
	}

	$return = array();

	while ($result = $r->next()) {
	    $u = $result->format('U') * 1000;

	    if ($u > $end) //data da repetição atual maior que a data final da busca do usuario ?
		break;

	    $return[] = $u;
	}

	return( $return );
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function updateCalendar(&$uri, &$params, &$criteria, $original) {       
        $calendarToObject = self::schedulable2calendarToObject($uri['id']);
        $permission = Controller::find(array('concept' => 'calendarToPermission'), false, array('filter' => array('AND', array('=', 'calendar', $calendarToObject[0]['calendar_id']), array('=', 'user', Config::me('uidNumber')), 'deepness' => 2)));

        if (is_array($permission) && $permission[0]['acl'] == 'r')
            return false;
        if (isset($params['calendar'])) {
            if(isset($params['lastCalendar'])){
                $calendarObjects = self::referenceCalendarToObject($uri['id'], $params['lastCalendar']);
                $params2['calendar'] = $params['calendar'];
            }else{
                $calendarObjects = self::schedulable2calendarToObject($uri['id']);
                $params2['calendar'] = $params['calendar'];
            }
	    
            foreach ($calendarObjects as $calendarObject)
		Controller::update(array('concept' => 'calendarToSchedulable', 'id' => $calendarObject['calendar_to_calendar_object']), $params2);

	    unset($params['calendar']);

	    if (count($params) < 1)
		return false;
	}
    }

    public function setLastUpdateSchedulable(&$uri, &$params, &$criteria, $original) {
        $params['lastUpdate'] = time() * 1000;
    }

	//Encode Update
    public function encodeUpdateSchedulable(&$uri, &$params, &$criteria, $original) {
    	$event = Controller::read(array('concept' => 'schedulable', 'id' => $uri['id']));
    	if (isset($params['startTime'])) {

    	    if (!is_numeric($params['startTime']))
    		$params['startTime'] = self::parseTimeDate($params['startTime'], $event['timezone']);

    	    $params['rangeStart'] = $params['startTime'];
    	}

        if (isset($params['endTime'])) {

    	    if (!is_numeric($params['endTime'])) {
        		$params['endTime'] = self::parseTimeDate($params['endTime'], $event['timezone']);

        	    if ((isset($params['allDay']) && $params['allDay']) || ( !isset($params['allDay']) && $event['allDay']))
        		$params['endTime'] = $params['endTime'] + 86400000;
    	    }
    	    $params['rangeEnd'] = $params['endTime'];

            if($event['type'] == '2'){

                if(!isset($params['due']) && $params['endTime'] != $event['endTime'])
                    $params['due'] = $params['endTime'];

            }
    	}

        if (isset($params['due']) && $params['due'] != '' && !is_numeric($params['due'])){
            $params['due'] = self::parseTimeDate($params['due'], $event['timezone']);

            if ((isset($params['allDay']) && $params['allDay']) || ( !isset($params['allDay']) && $event['allDay']))
                $params['due'] = $params['due'] + 86400000;
        }

        if($event['type'] == '2'){
            $criteria['historic'] = $params;
            $criteria['beforeValue'] = $event;

            if(isset($params['startTime']) && $params['startTime'] == $event['startTime'])
                unset($criteria['historic']['startTime']);
            if(isset($params['due']) && $params['due'] == $event['due'])
                unset($criteria['historic']['due']);

            //necessário para atulizar a atividade de composta pela tarefa aqui sendo atualizada
            if(isset($criteria['historic']['startTime']) || isset($criteria['historic']['endTime'])){
                /*
                 * Verify current task is built-in activity
                 * */
                $taskToActivity = Controller::find(array('concept' => 'taskToActivity'), false, array('filter' => array('AND', array('=', 'task', $uri['id']), array('=', 'owner', Config::me('uidNumber'))), 'deepness' => 2));
                if(!empty($taskToActivity)){
                    $activity = Controller::read(array('concept' => 'schedulable', 'id' => $taskToActivity[0]['activity']), array('startTime', 'endTime', 'rangeStart', 'rangeEnd', 'allDay'));
                    $isAllDay = 1;

                    /*
                     * Get all task in activity
                     * */
                    $taskToActivity = Controller::find(array('concept' => 'taskToActivity'), false, array('filter' => array('AND', array('=', 'activity', $taskToActivity[0]['activity']), array('=', 'owner', Config::me('uidNumber'))), 'deepness' => 2));

                    if(!empty($activity)){
                        $start = $params['startTime'];
                        $end = $params['endTime'];
                        foreach($taskToActivity as $t){


                            if($t['task']['id'] != $params['id']){
                                $start = $t['task']['startTime'] < $start ? $t['task']['startTime'] : $start;
                                $end = $t['task']['endTime'] > $end ? $t['task']['endTime'] : $end;
                                $isAllDay = (($isAllDay == 1) && ($t['task']['allDay'] == '1')) ? 1 : 0;
                            }
                        }

                        if($event['allDay'] == '0' || (isset($params['allDay']) && $params['allDay'] == '0'))
                            $isAllDay = 0;

                        $toUpdate = array();

                        if($start != $activity['startTime'])
                            $toUpdate['startTime'] = $start;

                        if($end != $activity['endTime']){
                            $toUpdate['endTime'] = $end;
                            $toUpdate['due'] = $end;
                        }

                        if($isAllDay != $activity['allDay'])
                            $toUpdate['allDay'] = $isAllDay;

                        if(!empty($toUpdate))
                            Controller::update(array('concept' => 'schedulable', 'id' => $taskToActivity[0]['activity']), $toUpdate);
                    }
                }
            }
            /*
             * Clean historic not used
             * */
            unset($criteria['historic']['endTime']);
            unset($criteria['historic']['rangeEnd']);
            unset($criteria['historic']['rangeStart']);
            unset($criteria['historic']['class']);
            unset($criteria['historic']['type']);
            unset($criteria['historic']['allDay']);
            unset($criteria['historic']['id']);
            unset($criteria['historic']['lastUpdate']);
            unset($criteria['historic']['timezone']);
        }
    }

    static function putEvent(&$uri, &$result, &$criteria, $original) {
	if (Config::module('useCaldav', 'expressoCalendar')) { //Ignorar Put dos eventos ja vindos do caldav
	    require_once ROOTPATH . '/modules/calendar/interceptors/DAViCalAdapter.php';

	    $eventID = (isset($result['id'])) ? $result['id'] : $uri['id'];
	    $event = Controller::read(array('concept' => 'schedulable', 'id' => $eventID));

	    $participants = Controller::find(array('concept' => 'participant'), false, array('filter' => array('=', 'schedulable', $eventID)));

	    if (is_array($participants) && count($participants) > 0)
		foreach ($participants as $ii => $vv) {
		    if ($vv['isExternal'] == 1)
			$participants[$ii]['user'] = Controller::read(array('concept' => 'user', 'id' => $vv['user'], 'service' => 'PostgreSQL'));
		    else
			$participants[$ii]['user'] = Controller::read(array('concept' => 'user', 'id' => $vv['user']));
		}

	    $event['URI']['concept'] = 'schedulable';
	    $event['participants'] = $participants;

	    $ical = Controller::format(array('service' => 'iCal'), array($event));
	    $calendars = self::schedulable2calendarToObject($original['properties']['id']); //Busca os calendarios do usuario logado que contenham o evento
	    if (is_array($calendars))
		foreach ($calendars as $calendar)
		    DAViCalAdapter::putIcal($ical, array('uid' => $event['uid'], 'location' => $calendar['calendar_location']));
	}
    }


    static function prepareParticipantHistoric(&$uri, &$params, &$criteria, $original){
       $participant = Controller::read(array('concept' => 'participant', 'id' => $uri['id']));
       $schedulable = Controller::read(array('concept' => 'schedulable', 'id' => $participant['schedulable']));


       if($schedulable['type'] == '2')
            $criteria['historic']['participant']  = $participant;
    } 

    static function removeParticipantHistoric(&$uri, &$params, &$criteria, $original){
        if(isset($criteria['historic'])){

            $participant = $criteria['historic']['participant'];

             Controller::create(array('concept' => 'calendarHistoric'), 
                array('schedulable' => $participant['schedulable'], 
                    'user' => Config::me('uidNumber'),
                    'time' => time() . '000',
                    'attribute' => 'participant',
                    'beforeValue' => $participant['user'],
                    'afterValue' => ''
                    )
                );

        }

    }

    static function createParticipantHistoric(&$uri, &$params, &$criteria, $original){

	if(isset($original['properties']) && isset($original['properties']['isOrganizer']) && $original['properties']['isOrganizer'] != '1'){
	    if(!isset($criteria['event'])){
		$event = Controller::read(array('concept' => 'schedulable', 'id' => $original['properties']['schedulable']));
		$criteria['event'] = $event;
	    }else
		$event = $criteria['event'];

	    if($event['type'] == '2'){
		Controller::create(array('concept' => 'calendarHistoric'), 
		    array('schedulable' => $original['properties']['schedulable'], 
			'user' => Config::me('uidNumber'),
			'time' => time() . '000',
			'attribute' => 'participant',
			'beforeValue' => '',
			'afterValue' => $original['properties']['user']
			)
		    );
	    }
	}
    }

    static function autoImportCalendar(&$uri, &$params, &$criteria, $original){
        $autoCommit = Controller::service('PostgreSQL')->execResultSql('Select config_value FROM phpgw_config WHERE config_app = \'expressoCalendar\' AND config_name = \'expressoCalendar_autoImportCalendars\'');


        if(isset($autoCommit[0]) && $autoCommit[0]['config_value'] == 'true')
        {
            if(isset($original['properties']) && isset($original['properties']['user'])  && isset($original['properties']['isOrganizer']) &&  $original['properties']['isOrganizer'] != '1')
            {
                $defaultCalendar = Controller::find(array('concept' => 'modulePreference'), array('value') , array('filter' => array( 'and' , array('=' , 'name' , 'dafaultImportCalendar') , array('=' , 'module' , 'expressoCalendar') , array('=' , 'user' , $original['properties']['user'])  )) );
                if(isset($defaultCalendar[0]) && $defaultCalendar[0]['value'] > 0)
                {
                    Controller::create(array('concept' => 'calendarToSchedulable'),
                        array('schedulable' => $original['properties']['schedulable'],
                            'calendar' => $defaultCalendar[0]['value']
                        )
                    );
                }
            }
        }
    }

    static function createHistoric(&$uri, &$result, &$criteria, $original) {

        if(isset($criteria['historic']) && count($criteria['historic'])){
            $time =  time() . '000';

            foreach($criteria['historic'] as $k => $v){
                Controller::create(array('concept' => 'calendarHistoric'),
                    array('schedulable' => $uri['id'],
                        'user' => Config::me('uidNumber'),
                        'time' => $time,
                        'attribute' => $k,
                        'beforeValue' => $criteria['beforeValue'][$k],
                        'afterValue' => $v
                        )
                    );
            }
        }    
    }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function verifyCalendarLocation(&$uri, &$params, &$criteria, $original) {
	if (!isset($params['location']))
	    $params['location'] = Config::me('uid') . '/' . $params['name'];
    }

    //TODO: Remover apos suporte a ManytoMany na api 
    public function createCalendarToSchedulable(&$uri, &$result, &$criteria, $original) {

	Controller::create(array('concept' => 'calendarToSchedulable'), array('calendar' => $original['properties']['calendar'], 'schedulable' => $result['id']));
    }

    //TODO: Remover apos suporte a ManytoMany na api 
    public function createTasksToActivity(&$uri, &$result, &$criteria, $original) {

        if(isset($original['properties']['taskToActivity']) && $original['properties']['taskToActivity']){

            foreach($original['properties']['taskToActivity'] as $relational)

                Controller::create(array('concept' => 'taskToActivity'), array('task' => $relational['task'], 'owner' => $relational['owner'], 'activity' => $result['id']));

        }
    }

    public function removeAttachmentHistoric(&$uri, &$params, &$criteria, $original) {

        if(isset($criteria['historic'])){
            $attachment = $criteria['historic']['attachment'];

            $attachment['attachment'] = Controller::read(array('concept' => 'attachment', 'id' => $attachment['attachment']), array('name'));
            Controller::create(array('concept' => 'calendarHistoric'), 
                    array('schedulable' => $attachment['schedulable'], 
                        'user' => Config::me('uidNumber'),
                        'time' => time() . '000',
                        'attribute' => 'attachment',
                        'beforeValue' => $attachment['attachment']['name'],
                        'afterValue' => ''
                        )
                    );

        }
    
    }

    public function createAttachmentHistoric(&$uri, &$params, &$criteria, $original) {
        $event = Controller::read(array('concept' => 'schedulable', 'id' => $original['properties']['schedulable']));

        if($event['type'] == '2'){
            $attachment = Controller::read(array('concept' => 'attachment', 'id' => $original['properties']['attachment']), array('name'));

            Controller::create(array('concept' => 'calendarHistoric'), 
                array('schedulable' => $original['properties']['schedulable'], 
                    'user' => Config::me('uidNumber'),
                    'time' => time() . '000',
                    'attribute' => 'attachment',
                    'beforeValue' => '',
                    'afterValue' => $attachment['name']
                    )
                );

        }
    }

    public function deepnessFindCalendarShared(&$uri, &$result, &$criteria, $original) {
    	if (isset($original['criteria']['deepness']) && $original['criteria']['deepness'] != '0' && count($result) > 0) {

    	    $calendarIds = array();
    	    foreach ($result as $value)
    		    $calendarIds[] = $value['calendar'];

    	    $calendar = Controller::find(array('concept' => 'calendar'), false, array('filter' => array('AND', array('IN', 'id', $calendarIds))));

    	    if ($calendar && count($calendar) > 0){
                $newResult = array();
        		foreach ($calendar as  $value) {
        		    foreach ($result as  $r) {

            			if ($r['calendar'] == $value['id']) {
            			    $r['calendar'] = $value;
            			    $newResult[] = $r;
            			}
        		    }
        		}

        		foreach ($newResult as &$value) {
        		    if ($value['user'] != 0) {
            			$value['user'] = Controller::read(array('concept' => 'user', 'id' => $value['user']));

            			if (!$value['user'])
            			    $value['user'] = Controller::read(array('concept' => 'group', 'id' => $value['user']));
        		    }
                    $value['owner'] = Controller::read(array('concept' => 'user', 'id' => $value['owner']));
        		}
        		$result = $newResult;
    	    }else
    		  $result = '';
    	}
    }

    public function getCalendarTask( $task ){

        $sql = 'select co.calendar_id as "calendar" from calendar_to_calendar_object as "co", calendar_signature as "cs" where cs.user_uidnumber = '. Config::me('uidNumber')
            .' AND cs.is_owner = 1 AND cs.calendar_id = co.calendar_id AND co.calendar_object_id = '. $task;


        $returns = Controller::service('PostgreSQL')->execResultSql($sql);

        return $returns[0]['calendar'];
    }

    //TODO: Remover apos suporte a deepness na api 
    public function deepnessFindTask(&$uri, &$result, &$criteria, $original) {
        if (isset($criteria['deepness']) && $criteria['deepness'] != 0){

            foreach($result as &$value){

                $value['task'] = Controller::read(array('concept' => 'schedulable', 'id' => $value['task']));
                $value['task']['calendar'] = self::getCalendarTask( $value['task']['id'] );
            }

            return $result;
        }
    }

    //TODO: Remover apos suporte a deepness na api 
    public function deepnessFindHistoric(&$uri, &$result, &$criteria, $original) {
        if (isset($criteria['deepness']) && $criteria['deepness'] != 0) {
            foreach($result as &$v){
                $v['user'] = Controller::read(array('concept' => 'user', 'id' => $v['user']));

                if($v['attribute'] == 'participant'){
                    if($v['beforeValue'] != '')
                        $v['beforeValue'] = Controller::read(array('concept' => 'user', 'id' => $v['beforeValue']));

                    if($v['afterValue'] != '')
                        $v['afterValue'] = Controller::read(array('concept' => 'user', 'id' => $v['afterValue']));
                }
            }
        }
    }

    //TODO: Remover apos suporte a deepness na api
    public function findDeepnessOne(&$uri, &$params, &$criteria, $original){

        if (isset($criteria['findOne']) && $criteria['findOne'] == '1') {

            $sql = ' SELECT DISTINCT calendar_object.id as id ,calendar_object.cal_uid as "uid", calendar_object.type_id as "type", '
                .'calendar_object.dtstart as "startTime", calendar_object.summary as "summary", '
                .'calendar_object.description as "description", calendar_object.dtend as "endTime" , '
                .'calendar_object.priority as "priority", calendar_object.due as "due", '
                .'calendar_object.percentage as "percentage", calendar_object.status as "status", '
                .'calendar_object.location as "location", calendar_object.allday as "allDay", '
                .'calendar_object.transp as "transparent", calendar_object.class_id as "class", '
                .'calendar_object.repeat as "repeat", calendar_object.range_start as "rangeStart", '
                .'calendar_object.range_end as "rangeEnd", calendar_object.last_update as "lastUpdate", '
                .'calendar_object.dtstamp as "dtstamp", calendar_object.sequence as "sequence", '
                .'calendar_object.tzid as "timezone", calendar_to_calendar_object.calendar_id as "calendar" '
                .'FROM calendar_object, calendar_to_calendar_object '
                .'WHERE ( calendar_object.id = '. $criteria['schedulable'] .' AND calendar_to_calendar_object.calendar_object_id = calendar_object.id'
                .' AND calendar_to_calendar_object.calendar_id IN (select calendar_id from calendar_signature where calendar_signature.user_uidnumber = '. Config::me('uidNumber') .' )         )';

            $params = Controller::service('PostgreSQL')->execResultSql($sql);

            foreach( $params as &$event ){


                if(isset( $event['repeat'] ) && isset( $event['repeat']['id'] ) ){
                    $occurrences = Controller::service('PostgreSQL')->execResultSql('SELECT DISTINCT occurrence FROM calendar_object as "c", calendar_repeat_occurrence as "o", calendar_repeat as "r" WHERE r.object_id = '. $event['id'] .' AND o.repeat_id = r.id ' );

                    if($occurrences){
                        $event['occurrences'] = array();

                        foreach($occurrences as $o)
                            $event['occurrences'][] = $o['occurrence'];
                    }else
                        unset( $event['occurrences'] );
                }
            }

            $params = self::deepnessFindEvent($uri, $params, $criteria, $original);
            return false;
        }
    }

    //TODO: Remover apos suporte a deepness na api 
    public function deepnessFindEvent(&$uri, &$result, &$criteria, $original) {
        if ((isset($criteria['deepness']) && $criteria['deepness'] != 0) ) {

            $date = new DateTime('now', new DateTimeZone('UTC'));
            $DayLigth = array();

            foreach ($result as $i => $v) {

                $currentTimezone = (isset($v['calendar']) && isset($original['criteria']['timezones'][$v['calendar']])) ? $original['criteria']['timezones'][$v['calendar']] : $v['timezone'];

                $date->setTimestamp((int) ($v['startTime'] / 1000));
                $date->setTimezone( new DateTimeZone( $v['timezone'] ));
                $DayLigth['event']['startTime'] = ($date->getTimestamp() + $date->getOffset()).'000';

                $date->setTimezone( new DateTimeZone($currentTimezone));
                $DayLigth['calendar']['startTime'] = ($date->getTimestamp() + $date->getOffset()).'000';

                $date->setTimestamp((int) ($v['endTime'] / 1000));
                $date->setTimezone( new DateTimeZone($currentTimezone));
                $DayLigth['event']['endTime'] = ($date->getTimestamp() + $date->getOffset()).'000';

                if(isset($v['due']) && $v['due'] != '0'){
                    $date->setTimestamp((int) ($v['due'] / 1000));
                    $DayLigth['event']['due'] = ($date->getTimestamp() + $date->getOffset()).'000';
                }else{
                    $DayLigth['event']['due'] = $v['due'];
                }


                $date->setTimezone( new DateTimeZone($currentTimezone));
                $DayLigth['calendar']['endTime'] = ($date->getTimestamp() + $date->getOffset()).'000';


                $result[$i]['DayLigth'] = $DayLigth;

                if(((int)$v['type']) == 2){

                    $taskToActivity = Controller::find(array('concept' => 'taskToActivity'), false, array('filter' => array('AND', array('=', 'activity', $v['id']), array('=','owner', Config::me('uidNumber'))) , 'deepness' => 2));

                    if($taskToActivity)
                        $result[$i]['taskToActivity'] = $taskToActivity;

                    $result[$i]['historic'] = Controller::find(array('concept' => 'calendarHistoric'), false, array('filter' => array('=', 'schedulable', $v['id']) , 'deepness' => 2));

                }

                if(isset( $v['occurrences'] ) && count( $v['occurrences'] ) > 0){

                    $date->setTimestamp((int) ($v['startTime'] / 1000));
                    $date->setTimezone( new DateTimeZone( $v['timezone'] ));

                    foreach( $result[$i]['occurrences'] as &$o){

                        $o = ($o + $date->getOffset()).'000';

                    }
                }

                if (isset($v['id'])) {
                    $data = self::decodeParticipantsEvent($uri, $v, $criteria, $original);

                    $result[$i]['statusAttendees'] = isset($data['statusAttendees']) ? $data['statusAttendees'] : false;
                    $result[$i]['sizeAttendees'] = isset($data['sizeAttendees']) ? $data['sizeAttendees'] : false;
                    $result[$i]['participants'] = $data['attendees'];

                    $attachmentRelation = Controller::find(array('concept' => 'schedulableToAttachment'), false, array('filter' => array('=', 'schedulable', $v['id'])));
                    if (is_array($attachmentRelation)) {
                    $attachments = array();
                    foreach ($attachmentRelation as $key => $value)
                        if (isset($value['attachment']) || !!$value['attachment'])
                        $attachments[$key] = $value['attachment'];
                    //Pega os anexos sem source
                    $result[$i]['attachments'] = Controller::find(array('concept' => 'attachment'), array('id', 'name', 'type', 'size'), array('filter' => array('IN', 'id', $attachments)));
                    }

                    $repeat = Controller::find(array('concept' => 'repeat'), false, array('filter' => array('=', 'schedulable', $v['id'])));

                    unset($result[$i]['repeat']);

                    if (is_array($repeat))
                    $result[$i]['repeat'] = $repeat[0];
                }
            }
        }

        return $result;
    }

//TODO: Remover apos suporte a deepness na api
    public static function deepnessReadParticipant( &$uri , &$result , &$criteria , $original ){
	
       if(isset($criteria['deepness']) && $criteria['deepness'] != 0)
       {
            if(isset($result['id']) && isset($result['user']))
            {
		$result['user'] = Controller::read( array( 'concept' => 'user' , 'id' => $result['user'] , 'service' => ( $result['isExternal'] == 1 ? 'PostgreSQL' : 'OpenLDAP' ) ) );

		if($result['user']['id'] == Config::me('uidNumber'))
		  $result['alarms'] = Controller::find( array( 'concept' => 'alarm' ) , null , array('filter' => array('=', 'participant' ,$result['id'] ) ) );
            }
       }
       
      
       
   } 

    //TODO: Remover apos suporte a deepness na api 
    public function deepnessReadEvent( &$uri , &$result , &$criteria , $original ){		
    
       if(isset($original['criteria']['deepness']) && $original['criteria']['deepness'] != 0)
       {
            if(isset($result['id']))
            {
                $result['participants'] = Controller::find( array( 'concept' => 'participant' ) , false ,array( 'filter' => array('=' ,  'schedulable' ,  $result['id']), 'deepness' => $original['criteria']['deepness'] - 1) ); 

		$repeat =  Controller::find( array( 'concept' => 'repeat' ), false, array( 'filter' => array( '=', 'schedulable', $result['id'] ) ) );

                if(is_array($repeat))
		    $result['repeat'] = $repeat[0];
	    } 
	    
       }
       
       if(isset($result['id']) && $result['type'] == '2'){
	   $result['historic'] =  Controller::find( array( 'concept' => 'calendarHistoric' ), false, array( 'filter' => array( '=', 'schedulable', $result['id'] ) ) );
       }
   } 
   
    //TODO: Remover apos suporte a deepness na api 
    public static function deepnessFindParticipant( &$uri , &$result , &$criteria , $original ){
       if(isset($original['criteria']['deepness']) && $original['criteria']['deepness'] != 0)
       {
           foreach ($result as $i => &$v)
           {
		self::deepnessReadParticipant( $uri, $v, $criteria, $original );
	   }
       }  
       
   } 

    //TODO: Remover apos suporte a deepness na api 
    public function deepnessReadCalendarSignature(&$uri, &$result, &$criteria, $original) {

	if (isset($original['criteria']['deepness']) && $original['criteria']['deepness'] != 0)
	    if (isset($result['calendar'])) {
		$result['calendar'] = Controller::read(array('concept' => 'calendar', 'id' => $result['calendar']));
		$result['defaultAlarms'] = Controller::find(array('concept' => 'calendarSignatureAlarm'), false, array('filter' => array('=', 'calendarSignature', $result['id'])));
	    }
    }

    //TODO: Remover apos suporte a deepness na api 
    public function deepnessFindCalendarSignature(&$uri, &$result, &$criteria, $original) {

	if (isset($original['criteria']['deepness']) && $original['criteria']['deepness'] != 0) {
	    foreach ($result as $i => $v) {
		if (isset($v['calendar'])) {
		    $result[$i]['calendar'] = Controller::read(array('concept' => 'calendar', 'id' => $v['calendar']), false, false);
		    $result[$i]['defaultAlarms'] = Controller::find(array('concept' => 'calendarSignatureAlarm'), false, array('filter' => array('=', 'calendarSignature', $v['id'])));
		    //Caso não seja o dono da agenda retorna o objeto permission com as acls
		    if ($result[$i]['isOwner'] == 0) {
			$permission = Controller::find(array('concept' => 'calendarToPermission'), false, array('filter' => array('AND', array('=', 'calendar', $v['calendar']), array('=', 'user', Config::me('uidNumber')), 'deepness' => 2)));

			if (!is_array($permission) || !$permission) {

			    $permission = Controller::find(array('concept' => 'calendarToPermission'), false, array('filter' => array('AND', array('=', 'calendar', $v['calendar']), array('=', 'type', '1')), 'deepness' => 2 ));
			}
			$result[$i]['permission'] = $permission[0];
		    }
		}
		//TODO - Padronizar retorno do deepness

        if (isset($v['user']))

		    $user = $v['user'];
            $result[$i]['user'] = Controller::read(array('concept' => 'user', 'id' => $v['user']), false, false);
	    }

        if(empty( $result[$i]['user'] ) || count( $result[$i]['user'] ) == 0 ){
            $result[$i]['user'] = Controller::read(array( 'concept' => 'group', 'id' => $user) );

        }


	}
    }

//Decode Find       
//    public function decodeFindConcept(&$uri, &$result, &$criteria, $original) {
//	if ($result && is_array($result)) {
//	    $m = array_flip(self::${$uri['concept'] . 'Map'});
//	    $new = array();
//	    foreach ($result as $i => $v)
//		$new[$i] = self::parseConcept($result[$i], $m);
//
//
//	    $result = $new;
//	}
//    }

    public function addOwner(&$uri, &$params, &$criteria, $original) {
        $owner = Controller::read( array( 'concept' => 'calendarSignature' ) , array('user') ,array( 'filter' => array('AND', array('=', 'isOwner'  ,  '1'), array('=', 'calendar', $params['calendar']))));
        $params['owner'] = $owner[0]['user'];
    }

//    public function decodeFindSchedulable(&$uri, &$result, &$criteria, $original) {
//	if ($result && is_array($result)) {
//	    $m = array_flip(self::${$uri['concept'] . 'Map'});
//	    $m['calendar_id'] = 'calendar';
//	    $new = array();
//	    foreach ($result as $i => $v)
//		$new[$i] = self::parseConcept($result[$i], $m);
//
//
//	    $result = $new;
//	}
//    }

    public function decodeFindAttachment(&$uri, &$result, &$criteria, $original) {
	if (isset($result))
	    foreach ($result as $key => &$value)
		$value['source'] = base64_decode($value['source']);
    }

    public function decodeSignatureAlarmType(&$uri, &$result, &$criteria, $original) {
	if (is_array($result))
	    foreach ($result as &$param)
		if (isset($param['type']))
		    $param['type'] = self::decodeAlarmType($param['type']);
    }

/////////////////////////////////////////////////////////////////////////

    static function decodeParticipantsEvent( &$uri, $result, &$criteria, $original) {
	$participants = Controller::find( array( 'concept' => 'participant' ) , false ,array( 'filter' => array('=', 'schedulable'  ,  $result['id']) ));

	if($participants && ($size = count($participants)) < 100){
	    if(isset($original['criteria']['deepness']) && $original['criteria']['deepness'] != 0){
		self::deepnessFindParticipant($uri, $participants, $criteria, $original);
		 $participants['attendees'] = $participants;
	    }
	    
	}else if($participants && ($size = count($participants)) > 100){
	    $owner = Controller::read( array( 'concept' => 'calendarSignature' ) , false ,array( 'filter' => array('AND', array('=', 'calendar'  ,  $result['calendar']), array('=', 'isOwner', '1'))));
	    $owner = Controller::read( array( 'concept' => 'participant' ) , false ,array( 'filter' => array('AND', array('=', 'schedulable'  ,  $result['id']), array('=', 'user', $owner[0]['user'])), 'deepness' => 2));

	    if(is_array($owner))
		$owner = $owner[0];

	    $reference = array_slice($participants, 0, 100);
	    $organizer = false;
	    $asOwner = false;

	    foreach($reference as $r => &$v){
		if($v['id'] == $owner['id']){
		    $v = $owner;
		    $asOwner = true;
		    continue;
		}

		self::deepnessReadParticipant($uri, $v, $criteria, $original);

		if($v['isOrganizer'] == "1" )
		    $organizer = $v;
	    }

	    if(!$organizer){
		$organizer = Controller::find( array( 'concept' => 'participant' ) , false ,array( 'filter' => array('AND', array('=', 'schedulable'  ,  $result['id']), array('=', 'isOrganizer', '1')), 'deepness' => 2));

		array_push($reference, $organizer[0]);

	    }else if($organizer && ($organizer['id'] != $owner['id']))
		array_merge($reference, $organizer);

	    if(!$asOwner)
		array_push($reference, $owner);

	    $statusAttendees = array( 'default' => 0, 'accepted' => 0, 'tentative' => 0, 'cancelled' => 0, 'unanswered' => 0, 'delegated' => 0 );
	    $statusLabels = array( 'default', 'accepted', 'tentative', 'cancelled', 'unanswered', 'delegated' );

	    foreach($participants as $k => &$p){
		if(!$organizer && $p['isOrganizer'] == "1"){
		    self::deepnessReadParticipant($uri, $p, $criteria, $original);
		    $reference = array_merge($reference, array($p));
		}

		$statusAttendees[$statusLabels[$p['status']]]++;
	    }

	    $participants['statusAttendees'] = $statusAttendees;
	    $participants['sizeAttendees'] = $size;
	    $participants['attendees'] = $reference;
	}
	
	return $participants;
    }
    
    static function dayAlarm( &$uri , &$params , &$criteria , $original ) {
        if(isset($criteria['filter'][1]) && $criteria['filter'][1] == 'date')
        {
            $start = $criteria['filter'][2];
            $end =  $start + 86400000;
            $params = array();

            $select = "SELECT co.id as \"id\", co.cal_uid as \"uid\", co.type_id as \"type\", co.dtstart as \"startTime\", co.summary as \"summary\", co.description as \"description\",co.dtend as \"endTime\", co.location as \"location\", co.allday as \"allDay\", co.transp as transparent, co.class_id as class, co.range_start as \"rangeStart\", co.range_end as \"rangeEnd\", co.last_update as \"lastUpdate\", co.dtstamp as \"dtstamp\", co.sequence as \"sequence\", co.tzid as \"timezone\", CASE WHEN occ.occurrence > 0 THEN occ.occurrence - al.alarm_offset ELSE co.dtstart - al.alarm_offset END as \"sendTime\", al.unit as \"unit\",al.time as \"time\" FROM calendar_object as co INNER JOIN calendar_alarm al ON co.id = al.object_id JOIN calendar_participant part  ON part.id = al.participant_id LEFT JOIN calendar_repeat rep ON  rep.object_id = co.id LEFT JOIN calendar_repeat_occurrence occ ON occ.repeat_id = rep.id  WHERE part.user_info_id = '".Config::me('uidNumber')."' AND al.action_id = '".ALARM_ALERT."' AND al.sent = '0' AND CASE WHEN occ.occurrence > 0 THEN occ.occurrence - al.alarm_offset ELSE co.dtstart - al.alarm_offset END BETWEEN $start AND $end ";

            $al = Controller::service('PostgreSQL')->execSql($select);


            if(is_array($al))
              foreach( $al as $v )
                  $params[] = array('schedulable' =>  $v);
            else
              $params = false;

                return false;
        }
    } 
    
    static private function countMyCalendarsEvent($id, $owner) {
		$sig = Controller::find(array('concept' => 'calendarSignature'), array('user', 'calendar', 'isOwner'), array('filter' => array('AND', array('=', 'isOwner', '1'), array('=', 'user', $owner))));
		$calendars = array();
		foreach ($sig as $val)
			$calendars[] = $val['calendar'];

		$return = Controller::find(array('concept' => 'calendarToSchedulable'), null, array('filter' => array('AND', array('IN', 'calendar', $calendars), array('=', 'schedulable', $id))));

		return (isset($return[0])) ? count($return) : 0;
    }
    
    public function deleteSchedulable(&$uri, &$params, &$criteria, $original) {

        if (Config::module('useCaldav', 'expressoCalendar'))
	    require_once ROOTPATH . '/modules/calendar/interceptors/DAViCalAdapter.php';
        
        if(isset($criteria['filter']) && $criteria['filter'] && isset($criteria['filter'][1][2])){
            $idSchedulable = $criteria['filter'][1][2];
	        $idCalendar = $criteria['filter'][2][2];
            $owner = $criteria['filter'][3][2];

            $qtdMyCalendars = self::countMyCalendarsEvent($idSchedulable, $owner);

            $link = Controller::read(array('concept' => 'calendarToSchedulable'), false, array('filter' => array('AND', array('=','calendar',$idCalendar), array('=','schedulable',$idSchedulable))));
            $link = (is_array($link) && isset($link[0])) ? $link[0] : $link;
            
            $calendar = Controller::read(array('concept' => 'calendar'), false, array('filter' => array('=','id',$idCalendar)));
            $calendar = (is_array($calendar) && isset($calendar[0])) ? $calendar[0] : $calendar;

            if($isAttende = !self::ownerSchedulable($idSchedulable, $owner)){
                Controller::delete(array('concept' => 'calendarToSchedulable', 'id' => $link['id']));
                
                if($qtdMyCalendars <= 1){

                    $participant = Controller::read(array('concept' => 'participant'), array('id'), array('filter' =>
                        array('AND',
                            array('=', 'user', $owner),
                            array('=', 'schedulable', $idSchedulable)
                        )));

                    Controller::call(('update'), array('concept' => 'participant', 'id' => $participant[0]['id']), array('status' => STATUS_CANCELLED));
                }

            }else{
                if($qtdMyCalendars > 1 )
                    Controller::delete(array('concept' => 'calendarToSchedulable', 'id' => $link['id']));
            }

            if (Config::module('useCaldav', 'expressoCalendar'))
		    DAViCalAdapter::deleteEvent($idSchedulable, array('location' => $calendar['location']));

            if($isAttende || ($qtdMyCalendars > 1))
                return false;

            $uri['id'] = $idSchedulable;

            if(isset($criteria['type']) && $criteria['type'] == '2'){

                $tasks = Controller::find(array('concept' => 'taskToActivity'), array('task'), array('filter' => array('=', 'activity', $idSchedulable)));

                if(is_array( $tasks ) and count( $tasks ) > 0){
                    Controller::delete(array('concept' => 'taskToActivity'), null, array('filter' => array('=', 'activity', $idSchedulable)));
                }

                if(isset($criteria['removeTaskToActivity']) && $criteria['removeTaskToActivity']){
                    if(is_array( $tasks ) and count( $tasks ) > 0){

                        $ids = array();
                        foreach($tasks as $v){
                            $ids[] = $v['task'];
                        }

                        Controller::delete(array('concept' => 'schedulable'), false, array('filter' => array('IN','id', $ids )));
                    }
                }
            }
        }
        
    }

    public function deleteCalendarToPermissionDependences(&$uri, &$params, &$criteria, $original) {
	$permission = Controller::read($uri, array('user', 'calendar'));

	$calendarSignature = Controller::find(array('concept' => 'calendarSignature'), array('id'), array('filter' => array('AND', array('=', 'calendar', $permission['calendar']), array('=', 'user', $permission['user']), array('=', 'isOwner', '0'))));

	if ($calendarSignature)
	    Controller::delete(array('concept' => 'calendarSignature', 'id' => $calendarSignature[0]['id']));
    }

    public function deleteCalendarSignatureDependences(&$uri, &$params, &$criteria, $original) {
	$signature = Controller::read($uri, array('isOwner', 'calendar','user'));

	if ($signature['isOwner'] == '1') {
	    $calendarToSchedulables = Controller::find(array('concept' => 'calendarToSchedulable'), null, array('filter' => array('=', 'calendar', $signature['calendar'])));

	    $schedulables = array();
	    if (is_array($calendarToSchedulables))
		foreach ($calendarToSchedulables as $key => $calendarToSchedulable)
		    $schedulables[] = $calendarToSchedulable['schedulable'];

	    if (!empty($schedulables))
			Controller::deleteALL(array('concept' => 'schedulable'), null, array('filter' => array('IN', 'id', $schedulables)));

	    Controller::delete(array('concept' => 'calendar', 'id' => $signature['calendar']));

        $autoCommit = Controller::service('PostgreSQL')->execResultSql('Select config_value FROM phpgw_config WHERE config_app = \'expressoCalendar\' AND config_name = \'expressoCalendar_autoImportCalendars\'');

        if(isset($autoCommit[0]) && $autoCommit[0]['config_value'] == 'true')
        {
            $defaultCalendar = Controller::find(array('concept' => 'modulePreference'), array('value','id') , array('filter' => array( 'and' , array('=' , 'name' , 'dafaultImportCalendar') , array('=' , 'module' , 'expressoCalendar') , array('=' , 'user' , $signature['user'])  )) );


            if(isset($defaultCalendar[0])  && $defaultCalendar[0]['value'] == $signature['calendar'] )
            {
                Controller::delete(array('concept' => 'modulePreference', 'id' => $defaultCalendar[0]['id']));
            }
        }

        $permissions = Controller::find(array('concept' => 'calendarToPermission'), array('id'), array('filter' => array('=', 'calendar', $signature['calendar'])));

		if($permissions && count($permissions) > 0){
			$ids = array();
			foreach($permissions as $key => $value)
				array_push($ids, $value['id']);
		
			Controller::deleteALL(array('concept' => 'calendarToPermission'), null, array('filter' => array('IN', 'id', $ids)));
			
		}
		
		
	}
    }

    public function decodeSchedulablettachment(&$uri, &$params, &$criteria, $original) {
	if (isset($original['URI']['id'])){
            $schedulableAttachment = Controller::read(array('concept' => 'schedulableToAttachment'), false, array('filter' => array( '=', 'attachment' , $original['URI']['id'] )));
            $uri['id'] = $schedulableAttachment[0]['id'];

            $params = $schedulableAttachment[0];

            $event = Controller::read(array('concept' => 'schedulable', 'id' => $params['schedulable']));
            if($event['type'] == '2')
                $criteria['historic']['attachment'] = $schedulableAttachment[0];

        }
    }

    public function deleteAttachmentDependences(&$uri, &$params, &$criteria, $original) {
        Controller::delete(array('concept' => 'attachment', 'id' => (isset($params['attachment']) ? $params['attachment'] : $original['URI']['id'])));
    }
    
    public function decodeDeleteCalendarSignatureAlarm(&$uri, &$params, &$criteria, $original) {
		if ($original['URI']['id'] == '' && isset($original['criteria']['filter'])){
			Controller::deleteAll(array('concept' => 'calendarSignatureAlarm'), null,$original['criteria']);
			return false;
		}
    }


     public function createDefaultGroup(&$uri, &$result, &$criteria, $original) {
        if( $original['criteria']['filter'][1][0] == '=' &&
        $original['criteria']['filter'][1][1] == 'user' &&
        $original['criteria']['filter'][1][2] == $_SESSION['phpgw_session']['account_id']){

            $existDefaultGroup = false;

            foreach($result as $v){
                if($v['type'] == 1 && $v['calendar']['type'] == 1)
                    $existDefaultGroup = true;
            }

            if(!$existDefaultGroup){

                $cal = array('name' => 'Sem grupo',
                    'description' => 'Sem grupo',
                    'timezone' => (date_default_timezone_get()) ? date_default_timezone_get() : 'America/Sao_Paulo',
                    'dtstamp' => time() . '000',
                    'type' => '1'
                );

                $calCreated = Controller::create(array('concept' => 'calendar'), $cal);

                $sig = array('user' => $_SESSION['wallet']['user']['uidNumber'],
                    'calendar' => $calCreated['id'],
                    'isOwner' => '1',
                    'dtstamp' => time() . '000',
                    'fontColor' => 'FFFFFF',
                    'backgroundColor' => '3366CC',
                    'borderColor' => '3366CC',
                    'type' => '1'
                );

                $sigCreated = Controller::create(array('concept' => 'calendarSignature'), $sig);
                $sigCreated = Controller::read(array('concept' => 'calendarSignature', 'id' => $sigCreated['id']), false, array('deepness' => 2 ));

                array_push($result, $sigCreated);
            }

        }

     }

    public function createDefaultSignature(&$uri, &$result, &$criteria, $original) {

    if(count($result) == 0 && isset($criteria['filter'][3]) && isset($criteria['filter'][3]['isRecursion'])){
        throw new Exception('It was not possible to find to calendar!');
        return false;
    }

	//Caso uma busca não retorne nenhum resultado e foi buscado pelas assinaturas do usuario logado apenas
	$isValidSignature = false;

	//Veirifica pois o usuário pode ter varias assinaturas mas não ser dona de nenhuma
	if (count($result) > 0) {
	    foreach ($result as $value) {
    		if (isset($value['isOwner']) && $value['isOwner'] != 0 && isset($value['type']) && $value['type'] == 0)
    		    $isValidSignature = true;
	    }
	}

	if (!$isValidSignature &&
		( $original['criteria']['filter'][1][0] == '=' &&
		$original['criteria']['filter'][1][1] == 'user' &&
		$original['criteria']['filter'][1][2] == $_SESSION['phpgw_session']['account_id']
		)) {

	    if (Config::module('useCaldav', 'expressoCalendar')) {
    		require_once ROOTPATH . '/modules/calendar/interceptors/DAViCalAdapter.php';
    		$calendario = DAViCalAdapter::findCalendars();
	    }

	    if (Config::module('useCaldav', 'expressoCalendar') && is_array($calendario) && count($calendario) > 0) {
    		foreach ($calendario as $i => $v) {

    		    $urlA = explode('/', $v->url);
    		    $name = isset($v->displayname) ? $v->displayname : $urlA[(count($urlA) - 2)];
    		    $cal = array('name' => $name,
    			'description' => isset($v->description) ? $v->description : $name,
    			'timezone' => isset($v->timezone) ? $v->timezone : (date_default_timezone_get()) ? date_default_timezone_get() : 'America/Sao_Paulo',
    			'dtstamp' => time() . '000',
    			'location' => $urlA[(count($urlA) - 3)] . '/' . $urlA[(count($urlA) - 2)]
    		    );

    		    $calCreated = Controller::create(array('concept' => 'calendar'), $cal);

                if(!$calCreated){
                    throw new Exception('Error to create calendar');
                    return false;
                }

    		    $sig = array('user' => $_SESSION['wallet']['user']['uidNumber'],
    			'calendar' => $calCreated['id'],
    			'isOwner' => '1',
    			'dtstamp' => time() . '000',
    			'fontColor' => 'FFFFFF',
    			'backgroundColor' => '3366CC',
    			'borderColor' => '3366CC',
    		    );

    		    $sigCreated = Controller::create(array('concept' => 'calendarSignature'), $sig);

                if(!$sigCreated){
                    throw new Exception('Error to create signature');
                    return false;
                }

                if($i == 0)
                {
                    $pref = array();
                    $pref['user'] = $_SESSION['wallet']['user']['uidNumber'];
                    $pref['value'] = $calCreated['id'];
                    $pref['name'] = 'dafaultImportCalendar' ;
                    $pref['module'] = 'expressoCalendar';
                    Controller::create(array('concept' => 'modulePreference'), $pref);
                }

    		}
	    } else {
    		//Criaremos uma agenda padrão
    		$cal = array('name' => 'Calendario',
    		    'description' => 'Calendario Padrão',
    		    'timezone' => (date_default_timezone_get()) ? date_default_timezone_get() : 'America/Sao_Paulo',
    		    'dtstamp' => time() . '000'
    		);

    		$calCreated = Controller::create(array('concept' => 'calendar'), $cal);

    		$sig = array('user' => $_SESSION['wallet']['user']['uidNumber'],
    		    'calendar' => $calCreated['id'],
    		    'isOwner' => '1',
    		    'dtstamp' => time() . '000',
    		    'fontColor' => 'FFFFFF',
    		    'backgroundColor' => '3366CC',
    		    'borderColor' => '3366CC',
    		);

    		$sigCreated = Controller::create(array('concept' => 'calendarSignature'), $sig);


            $pref = array();
            $pref['user'] = $_SESSION['wallet']['user']['uidNumber'];
            $pref['value'] = $calCreated['id'];
            $pref['name'] = 'dafaultImportCalendar' ;
            $pref['module'] = 'expressoCalendar';
            Controller::create(array('concept' => 'modulePreference'), $pref);


        }
    
        $original['criteria']['filter'][] = array('isRecursion' => true);
        $result = Controller::find($original['URI'], $original['properties'] ? $original['properties'] : null, $original['criteria']);
	    return false;
    	}
    }

    //TODO - Criar conceito separado para participantes externos e remover o criterio notExternal
    public function findExternalPaticipants(&$uri, &$result, &$criteria, $original) {
        if (Config::me('uidNumber') && !isset($criteria['notExternal'])) {
            $newuri['concept'] = 'user';
            $newuri['service'] = 'PostgreSQL';

            $newCriteria = $original['criteria'];
            $valid = true;

            $newCriteria['filter'] = array('AND', $newCriteria['filter'], array('=', 'owner', Config::me('uidNumber')));
            $externalUsers = Controller::find($newuri, $original['properties'] ? $original['properties'] : null, $newCriteria );

            if (!is_array($result))
                $result = array();

            if (is_array($externalUsers)) {
                foreach ($externalUsers as $i => $v)
                    $externalUsers[$i]['isExternal'] = '1';
            }
            else
                $externalUsers = array();

            $result = array_merge($result, $externalUsers);


            if(isset($original['criteria']['externalCatalogs']) &&  $original['criteria']['externalCatalogs'] == true)
            {
                $externalCatalogs = self::findExternalCatalogContacts($original['criteria']['filter'][2]);

                foreach($externalCatalogs as $i => $v)
                {
                    $exist = false;
                    foreach($result as $vv)
                    {
                        if($v['mail'] == $vv['mail'] )
                            $exist = true;
                    }
                    if(!$exist)
                        $result[] = $v;
                }
            }


            if(isset($original['criteria']['personalContacts']) &&  $original['criteria']['personalContacts'] == true)
            {
                $personalContacts = self::findPersonalContacts($original['criteria']['filter'][2]);

                foreach($personalContacts as $i => $v)
                {
                    $exist = false;
                    foreach($result as $vv)
                    {
                        if($v['mail'] == $vv['mail'] )
                            $exist = true;
                    }
                    if(!$exist)
                        $result[] = $v;
                }
            }

            return $result ;
        }
    }

    public function davcalCreateCollection(&$uri, &$params, &$criteria, $original) {
    	if (Config::module('useCaldav', 'expressoCalendar')) {
    	    require_once ROOTPATH . '/modules/calendar/interceptors/DAViCalAdapter.php';
    	    DAViCalAdapter::mkcalendar($params['location'], $params['name'], isset($params['description']) ? $params['description'] : '' );
	   }
    }

    public function davcalDeleteCollection(&$uri, &$params, &$criteria, $original) {
    	if (Config::module('useCaldav', 'expressoCalendar') && Config::module('onRemoveCascadeCalDav')) {
    	    require_once ROOTPATH . '/modules/calendar/interceptors/DAViCalAdapter.php';
    	    $calendar = Controller::read($uri);
    	    DAViCalAdapter::rmCalendar($calendar['location']);
    	}
    }

    public function davcalUpdateCollection(&$uri, &$params, &$criteria, $original) {
    	if (Config::module('useCaldav', 'expressoCalendar')) {
    	    require_once ROOTPATH . '/modules/calendar/interceptors/DAViCalAdapter.php';
    	    if (isset($params['location'])) {
    		$calendar = Controller::read($uri);
    		if ($calendar['location'] !== $params['location'])
    		    DAViCalAdapter::mvcalendar($calendar['location'], $params['location']);
    	    }
    	}
    }

    private static function _makeUid() {

	$date = date('Ymd\THisT');
	$unique = substr(microtime(), 2, 4);
	$base = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPrRsStTuUvVxXuUvVwWzZ1234567890';
	$start = 0;
	$end = strlen($base) - 1;
	$length = 6;
	$str = null;
	for ($p = 0; $p < $length; ++$p)
	    $unique .= $base{mt_rand($start, $end)};

	return $date . $unique . '@expresso-calendar';
    }

    private function getStatus($id) {
	$a = array(
	    STATUS_CONFIRMED => 'CONFIRMED',
	    STATUS_CANCELLED => 'CANCELLED',
	    STATUS_TENATIVE => 'TENATIVE',
	    STATUS_UNANSWERED => 'NEEDS-ACTION',
	    STATUS_DELEGATED => 'DELEGATED'
	);
	return $a[$id];
    }

    private static function decodeAlarmType($id) {
	$a = array(ALARM_ALERT => 'alert',
	    ALARM_MAIL => 'mail',
	    ALARM_SMS => 'sms');

	return $a[$id];
    }

    private static function codeAlarmType($type) {
	$a = array('alert' => ALARM_ALERT,
	    'mail' => ALARM_MAIL,
	    'sms' => ALARM_SMS);

	return $a[$type];
    }

    private static function codeAlarmUnit($u) {
	if ($u === 'd')
	    return 'days';
	if ($u === 'm')
	    return 'minutes';
	if ($u === 'H')
	    return 'hours';
    }

    private static function ownerSchedulable($id, $me) {

	$isOwner = Controller::find(array('concept' => 'participant'), array('id'), array('filter' =>
		    array('AND',
			array('=', 'isOrganizer', '1'),
			array('=', 'user', $me),
			array('=', 'schedulable', $id)
			)));

	return ( isset($isOwner[0]['id']) ) ? true : false;
    }
    
    
    private static function referenceCalendarToObject($schedulable, $calendar) {
        return Controller::service('PostgreSQL')->execResultSql('SELECT calendar_to_calendar_object.id as calendar_to_calendar_Object FROM calendar_to_calendar_object'
            . ' WHERE calendar_to_calendar_object.calendar_id = '. $calendar
            . ' AND calendar_to_calendar_object.calendar_object_id = ' . addslashes($schedulable));
    }
    
    private static function schedulable2calendarToObject($Schedulable) {
	return Controller::service('PostgreSQL')->execResultSql('SELECT calendar_to_calendar_object.id as calendar_to_calendar_Object , calendar.name as calendar_name ,calendar.location as calendar_location, calendar.id as calendar_id FROM calendar_to_calendar_object , calendar , calendar_signature'
			. ' WHERE calendar_signature.user_uidnumber = ' . $_SESSION['wallet']['user']['uidNumber']
			//      .' AND calendar_signature.is_owner = 1'
			. ' AND calendar_signature.calendar_id = calendar.id'
			. ' AND calendar_to_calendar_object.calendar_id = calendar.id'
			. ' AND calendar_to_calendar_object.calendar_object_id = ' . addslashes($Schedulable));
    }

    protected static function isAllowDeleteInCalendar($calendar) {
	$f = Controller::find(array('concept' => 'calendarToPermission'), false, array('filter' => array('AND', array('=', 'user', Config::me('uidNumber')), array('=', 'calendar', $calendar))));
	return (strpos($f[0]['acl'], CALENDAR_ACL_REMOVE) === false) ? false : true;
    }

    private static function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }

    static function findExternalCatalogContacts( $search )
    {
            $result = array();
            $external_srcs = array();
            $external_mappings = array();

            include_once dirname(__DIR__) .'/../../../contactcenter/setup/external_catalogs.inc.php';

            $search = str_replace(' ', '*', $search) ;
            $search = '*' . $search . '*';

            foreach($external_srcs as $i => $v)
            {
                $con = ldap_connect($v['host']);

                ldap_set_option( $con , LDAP_OPT_PROTOCOL_VERSION , 3 );

                if( isset( $v['acc'] ) && isset( $v['pw'] ) )
                    ldap_bind( $con, $v['acc'], $v['pw'] );

                $fields = array();
                $fields[] = $external_mappings[$i]['contact.names_ordered'][0];
                $fields[] = $external_mappings[$i]['contact.connection.typeof_connection.contact_connection_type_name']['email'][0];

                $ldapFilter = '(&(objectClass='.$v['obj'].')(|('.$fields[0].'='.$search.')('.$fields[1].'='.$search.')))';
                $sr =  ldap_search( $con, utf8_encode($v['dn']) , $ldapFilter , $fields );
                if($sr)
                {
                    $search = ldap_get_entries($con, $sr);

                    for ($j = 0; $j < $search["count"]; ++$j) {
                        $tmp = array();
                        $tmp['name'] = $search[$j][$fields[0]][0];
                        $tmp['mail'] = $search[$j][$fields[1]][0];
                        $tmp['isExternal'] = '1';

                        $result[] = $tmp;
                    }
                }
            }
            return $result;
    }

    static function findPersonalContacts($search_for)
    {

              $query = 'select'

                . ' C.id_connection,'
                . ' A.id_contact,'
                . ' A.names_ordered,'
                . ' A.alias,'
                . ' A.birthdate,'
                . ' A.sex,'
                . ' A.pgp_key,'
                . ' A.notes,'
                . ' A.web_page,'
                . ' A.corporate_name,'
                . ' A.job_title,'
                . ' A.department,'
                . ' C.connection_name,'
                . ' C.connection_value,'
                . ' B.id_typeof_contact_connection,'
                . ' phpgw_cc_contact_addrs.id_typeof_contact_address,'
                . ' phpgw_cc_addresses.address1,'
                . ' phpgw_cc_addresses.address2,'
                . ' phpgw_cc_addresses.complement,'
                . ' phpgw_cc_addresses.postal_code,'
                . ' phpgw_cc_city.city_name,'
                . ' phpgw_cc_state.state_name,'
                . ' phpgw_cc_addresses.id_country'
            ;

            $query .= ' from'
                . ' phpgw_cc_contact A'
                . ' inner join phpgw_cc_contact_conns B on ( A.id_contact = B.id_contact )'
                . ' inner join phpgw_cc_connections C on ( B.id_connection = C.id_connection )'
                . ' left join phpgw_cc_contact_addrs on ( A.id_contact = phpgw_cc_contact_addrs.id_contact )'
                . ' left join phpgw_cc_addresses on ( phpgw_cc_contact_addrs.id_address = phpgw_cc_addresses.id_address )'
                . ' left join phpgw_cc_city on ( phpgw_cc_addresses.id_city = phpgw_cc_city.id_city )'
                . ' left join phpgw_cc_state on ( phpgw_cc_addresses.id_state = phpgw_cc_state.id_state)'
            ;

            $query .= ' where '
                . 'A.id_owner=' . Config::me('uidNumber')
                . ' and lower(translate(names_ordered, \'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇñÑ\',\'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN\'))'
                . ' LIKE lower(translate(\'%' . $search_for . '%\', \'áàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇñÑ\',\'aaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN\'))';

            //Se não existir parametro na busca, limita os usuarios no resultado da pesquisa.
            if(!$search_for){
                $query .= 'LIMIT 11';
            }

            $r = Controller::service('PostgreSQL')->execResultSql($query);


            $all_contacts = array();
            foreach( $r as $i => $object )
            {
                if ( ! array_key_exists( $object[ 'id_contact' ], $all_contacts ) )
                    $all_contacts[ $object[ 'id_contact' ] ] = array(
                        'connection_value' => '',
                        'telephonenumber' => '',
                        'mobile' => '',
                        'cn' => '',
                        'id_contact' => '',
                        'id_connection' => '',
                        'alias' => '',
                        'birthdate' => '',
                        'sex' => '',
                        'pgp_key' => '',
                        'notes' => '',
                        'web_page' => '',
                        'corporate_name' => '',
                        'job_title' => '',
                        'department' => '',
                        'mail' => '',
                        'aternative-mail' => '',
                        'business-phone' => '',
                        'business-address' => '',
                        'business-complement' => '',
                        'business-postal_code' => '',
                        'business-city_name' => '',
                        'business-state_name' => '',
                        'business-id_country' => '',
                        'business-fax' => '',
                        'business-pager' => '',
                        'business-mobile' => '',
                        'business-address-2' => '',
                        'home-phone' => '',
                        'home-address' => '',
                        'home-complement' => '',
                        'home-postal_code' => '',
                        'home-city_name' => '',
                        'home-state_name' => '',
                        'home-fax' => '',
                        'home-pager' => '',
                        'home-address-2' => ''


                    );

                switch( $object[ 'id_typeof_contact_connection' ] )
                {
                    case 1 :
                        $all_contacts[ $object[ 'id_contact' ] ][ 'connection_value' ] = $object[ 'connection_value' ];
                        switch ( strtolower( $object[ 'connection_name' ] ) )
                        {
                            case 'alternativo' :
                                $all_contacts[ $object[ 'id_contact' ] ][ 'alternative-mail' ] = $object[ 'connection_value' ];
                                break;
                            case 'principal' :
                                $all_contacts[ $object[ 'id_contact' ] ][ 'mail' ] = $object[ 'connection_value' ];
                                break;
                        }
                        break;
                    case 2 :
                        $all_contacts[ $object[ 'id_contact' ] ][ 'telephonenumber' ] = $object[ 'connection_value' ];
                        switch ( strtolower( $object[ 'connection_name' ] ) )
                        {
                            case 'casa' :
                                $all_contacts[ $object[ 'id_contact' ] ][ 'home-phone' ] = $object[ 'connection_value' ];
                                break;
                            case 'celular' :
                                $all_contacts[ $object[ 'id_contact' ] ][ 'mobile' ] = $object[ 'connection_value' ];
                                break;
                            case 'trabalho' :
                                $all_contacts[ $object[ 'id_contact' ] ][ 'business-phone' ] = $object[ 'connection_value' ];
                                break;
                            case 'fax' :
                                $all_contacts[ $object[ 'id_contact' ] ][ 'home-fax' ] = $object[ 'connection_value' ];
                                break;
                            case 'pager' :
                                $all_contacts[ $object[ 'id_contact' ] ][ 'home-pager' ] = $object[ 'connection_value' ];
                                break;
                            case 'celular corporativo' :
                                $all_contacts[ $object[ 'id_contact' ] ][ 'business-mobile' ] = $object[ 'connection_value' ];
                                break;
                            case 'pager corporativo' :
                                $all_contacts[ $object[ 'id_contact' ] ][ 'business-pager' ] = $object[ 'connection_value' ];
                                break;
                            case 'fax corporativo' :
                                $all_contacts[ $object[ 'id_contact' ] ][ 'business-fax' ] = $object[ 'connection_value' ];
                                break;
                        }
                        break;
                }

                $all_contacts[ $object[ 'id_contact' ] ][ 'cn' ] = utf8_encode($object[ 'names_ordered' ]);
                $all_contacts[ $object[ 'id_contact' ] ][ 'id_contact' ]    = $object[ 'id_contact' ];
                $all_contacts[ $object[ 'id_contact' ] ][ 'id_connection' ] = $object[ 'id_connection' ];
                $all_contacts[ $object[ 'id_contact' ] ][ 'alias' ]         = $object[ 'alias' ];
                $all_contacts[ $object[ 'id_contact' ] ][ 'birthdate' ] 	= $object[ 'birthdate' ];
                $all_contacts[ $object[ 'id_contact' ] ][ 'sex' ]    		= $object[ 'sex' ];
                $all_contacts[ $object[ 'id_contact' ] ][ 'pgp_key' ] 		= $object[ 'pgp_key' ];
                $all_contacts[ $object[ 'id_contact' ] ][ 'notes' ]         = $object[ 'notes' ];
                $all_contacts[ $object[ 'id_contact' ] ][ 'web_page' ] 		= $object[ 'web_page' ];
                $all_contacts[ $object[ 'id_contact' ] ][ 'corporate_name' ]= $object[ 'corporate_name' ];
                $all_contacts[ $object[ 'id_contact' ] ][ 'job_title' ] 	= $object[ 'job_title' ];
                $all_contacts[ $object[ 'id_contact' ] ][ 'department' ]    = $object[ 'department' ];

                switch( $object[ 'id_typeof_contact_address' ] )
                {
                    case 1 :
                        $all_contacts[ $object[ 'id_contact' ] ][ 'business-address' ]     = $object[ 'address1' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'business-address-2' ]   = $object[ 'address2' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'business-complement' ]  = $object[ 'complement' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'business-postal_code' ] = $object[ 'postal_code' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'business-city_name' ]   = $object[ 'city_name' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'business-state_name' ]  = $object[ 'state_name' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'business-id_country' ]  = $object[ 'id_country' ];
                        break;
                    case 2 :
                        $all_contacts[ $object[ 'id_contact' ] ][ 'home-address' ]     = $object[ 'address1' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'home-address-2' ]   = $object[ 'address2' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'home-complement' ]  = $object[ 'complement' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'home-postal_code' ] = $object[ 'postal_code' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'home-city_name' ]   = $object[ 'city_name' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'home-state_name' ]  = $object[ 'state_name' ];
                        $all_contacts[ $object[ 'id_contact' ] ][ 'home-id_country' ]  = $object[ 'id_country' ];
                        break;
                }
            }
            $all = array_values($all_contacts);

            $result = array();
            foreach($all as $i => $v)
            {
                if(!$v['mail']) continue;

                $tmp = array();
                $tmp['mail'] = $v['mail'];
                $tmp['name'] = $v['cn'];
                $tmp['isExternal'] = '1';
                $result[] = $tmp;
            }

            return $result;

    }
}

?>
