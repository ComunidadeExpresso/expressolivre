<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Adriano
 * Date: 28/12/12
 * Time: 18:36
 * To change this template use File | Settings | File Templates.
 */
if(!defined('ROOTPATH'))
    define('ROOTPATH', dirname(__FILE__).'/../..');

require_once ROOTPATH.'/api/controller.php';
require_once ROOTPATH.'/modules/calendar/constants.php';
require_once ROOTPATH.'/modules/calendar/interceptors/DBMapping.php';

use prototype\api\Config as Config;

class Schedule{

    function findEventsRange( $start, $end, $calendars, $timezones  ){

        $sql =
            ' SELECT calendar_object.id as id ,calendar_object.cal_uid as "uid", calendar_object.type_id as "type", '
                .'calendar_object.dtstart as "startTime", calendar_object.summary as "summary", '
                .'calendar_object.description as "description", calendar_object.dtend as "endTime" , '
                .'calendar_object.priority as "priority", calendar_object.due as "due", '
                .'calendar_object.percentage as "percentage", calendar_object.status as "status", '
                .'calendar_object.location as "location", calendar_object.allday as "allDay", '
                .'calendar_object.transp as "transparent", calendar_object.class_id as "class", '
                .'calendar_object.repeat as "repeat", calendar_object.range_start as "rangeStart", '
                .'calendar_object.range_end as "rangeEnd", calendar_object.last_update as "lastUpdate", '
                .'calendar_object.dtstamp as "dtstamp", calendar_object.sequence as "sequence", '

                .'count(calendar_task_to_activity_object.id) as "tasks", '

                .'calendar_object.tzid as "timezone" ,calendar_to_calendar_object.calendar_id as '
                .'calendar FROM calendar_object left join calendar_task_to_activity_object on ( calendar_object.id = calendar_task_to_activity_object.calendar_object_activity_id  ), calendar_to_calendar_object '

                .'WHERE ( calendar_to_calendar_object.calendar_id IN (\'' . implode('\',\'', $calendars) . '\')) '
                .'AND calendar_to_calendar_object.calendar_object_id = calendar_object.id '
                .'AND calendar_object.id NOT IN(select calendar_object_task_id from calendar_task_to_activity_object where owner = \'' . Config::me('uidNumber') . '\') ';

        $ids = array();
        $occ = array();

        if ($occurrences = DBMapping::checkOccurrences($start, $end, $calendars))
            foreach ($occurrences as $id => $occurrence) {
                $ids[] = $id;
                $occ[] = $occurrence;
            }

        $where =
            ' AND ((range_end >= \'' . $start . '\' AND range_end <= \'' . $end . '\') OR '
                .'(range_start >= \'' . $start . '\' AND range_start <= \'' . $end . '\') OR '
                .'(range_start <= \'' . $start . '\' AND range_end >= \'' . $end . '\')) '
                .(!empty($ids) ? ' ' .'AND calendar_object.id NOT IN (\'' . implode('\',\'', $ids) . '\') ' : ' ')
                .'AND calendar_object.dtstart NOT IN (SELECT calendar_repeat_occurrence.occurrence from calendar_repeat_occurrence, '
                .'calendar_repeat where (calendar_repeat_occurrence.repeat_id = calendar_repeat.id) '
                .'AND (calendar_repeat.object_id = calendar_object.id)) '
                .'group by
                    calendar_object.id, calendar_object.cal_uid, calendar_object.type_id,
                    calendar_object.dtstart, calendar_object.summary, calendar_object.description,
                    calendar_object.dtend, calendar_object.priority, calendar_object.due, calendar_object.percentage,
                    calendar_object.status, calendar_object.location, calendar_object.allday, calendar_object.transp,
                    calendar_object.class_id, calendar_object.repeat, calendar_object.range_start, calendar_object.range_end,
                    calendar_object.last_update, calendar_object.dtstamp, calendar_object.sequence,
                    calendar_object.tzid, calendar_to_calendar_object.calendar_id
                ORDER BY
                    calendar_object.dtstart';

        $params = Controller::service('PostgreSQL')->execResultSql($sql.$where);
        $params = array_merge($params, $occ);

        return $this->normalizeEvents( $params, $timezones );
    }

    function findEventsSearch( $summary, $description, $calendars, $timezones, $limit, $offset ){

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

            .'count(calendar_task_to_activity_object.id) as "tasks", '

            .'calendar_object.tzid as "timezone" ,calendar_to_calendar_object.calendar_id as '
            .'calendar FROM calendar_object left join calendar_task_to_activity_object on ( calendar_object.id = calendar_task_to_activity_object.calendar_object_activity_id  ), calendar_to_calendar_object '
            .'WHERE ( calendar_to_calendar_object.calendar_id IN (\'' . implode('\',\'', $calendars) . '\')) '
            .'AND calendar_to_calendar_object.calendar_object_id = calendar_object.id '
            .'AND calendar_object.id NOT IN(select calendar_object_task_id from calendar_task_to_activity_object where owner = \'' . Config::me('uidNumber') . '\') ';


            $where = 'AND (((upper("summary") like upper(\'%'.$summary.'%\') OR upper("description") like upper(\'%'.$description.'%\'))))
                group by
                    calendar_object.id, calendar_object.cal_uid, calendar_object.type_id,
                    calendar_object.dtstart, calendar_object.summary, calendar_object.description,
                    calendar_object.dtend, calendar_object.priority, calendar_object.due, calendar_object.percentage,
                    calendar_object.status, calendar_object.location, calendar_object.allday, calendar_object.transp,
                    calendar_object.class_id, calendar_object.repeat, calendar_object.range_start, calendar_object.range_end,
                    calendar_object.last_update, calendar_object.dtstamp, calendar_object.sequence,
                    calendar_object.tzid, calendar_to_calendar_object.calendar_id
                ORDER BY
                    dtstart LIMIT '.$limit.'  OFFSET '.$offset.' ';

            $params = Controller::service('PostgreSQL')->execResultSql($sql.$where);

        return $this->normalizeEvents( $params, $timezones );
    }

    function normalizeEvents( $result, $timezones ){

        $mySig = Controller::find(array('concept' => 'calendarSignature') , array('calendar') , array('filter' => array( 'AND' , array('=' , 'type' , '0' ) , array( '=' , 'user' , Config::me('uidNumber') ) , array('=' , 'isOwner' , '0' ))));

        $signedCalendars = array();
        if( is_array($mySig) )
        {
            foreach($mySig as $v)
            {
                $tmp = Controller::find(array('concept' => 'calendarToPermission') , array('acl' ,'owner') , array('filter' => array( 'AND' ,array( '=' , 'calendar' , $v['calendar'] ) , array( '=' , 'user' , Config::me('uidNumber')  ) )));
                $signedCalendars[$v['calendar']] = $tmp[0];
            }
        }

        $date = new DateTime('now', new DateTimeZone('UTC'));
        $DayLigth = array();

        foreach ($result as $i => $v) {

            $currentTimezone = (isset($v['calendar']) && isset($timezones[$v['calendar']])) ? $timezones[$v['calendar']] : $v['timezone'];

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


            if(isset( $v['occurrences'] ) && count( $v['occurrences'] ) > 0){

                $date->setTimestamp((int) ($v['startTime'] / 1000));
                $date->setTimezone( new DateTimeZone( $currentTimezone ));

                foreach( $result[$i]['occurrences'] as &$o){

                    $o = ((int) ($o / 1000) + $date->getOffset()).'000';

                }
            }

            $attend = (isset($signedCalendars[$result[$i]['calendar']])) ?
                Controller::read(array('concept' => 'participant'), null, array('filter' => array('AND', array('=','schedulable',$v['id']), array('=','user', $signedCalendars[$result[$i]['calendar']]['owner'] )  ))):
                Controller::read(array('concept' => 'participant'), null, array('filter' => array('AND', array('=','schedulable',$v['id']), array('=','user', Config::me('uidNumber'))  )));

                $result[$i]['unanswered'] = 0;

            if(count($attend) > 0 && !empty($attend)){
               if(array_key_exists(0, $attend))
                  $attend = $attend[0];

                if(isset($signedCalendars[$result[$i]['calendar']])) //Caso agenda compartilhada verificar tmb se tem compartilhamento de escrita
                    $result[$i]['editable'] = (strpos($signedCalendars[$result[$i]['calendar']]['acl'],"w") >=0  &&  (strstr($attend['acl'],"w") || strstr($attend['acl'],"o") || $attend['isOrganizer'] == '1') ) ? 1 : 0;
                else
                   $result[$i]['editable'] = (strstr($attend['acl'],"w") || strstr($attend['acl'],"o") || $attend['isOrganizer'] == '1') ? 1 : 0;

                if($attend['status'] == STATUS_UNANSWERED && !isset($signedCalendars[$result[$i]['calendar']]) )
                   $result[$i]['unanswered'] = 1;

            }else{

               $result[$i]['editable'] = $v['type'] == '2' ? 0 : 2;

            }
            if( $v['type'] == 2 && $v['tasks'] > 0)
                $result[$i]['type'] = 3;
        }

        return $this->toUtf8( $result );
    }

    function srtToUtf8($data) {
        return mb_convert_encoding($data, 'UTF-8', 'UTF-8 , ISO-8859-1');
    }

    function toUtf8($data) {
        if (is_array($data)) {
            $return = array();
            foreach ($data as $i => $v)
                $return[$this->srtToUtf8($i)] = (is_array($v)) ? $this->toUtf8($v) : $this->srtToUtf8($v);

            return $return;
        }else
            return $this->srtToUtf8($data);
    }

}

$params = $_GET;
$schedule = new Schedule();

if(isset( $params['rangeStart'] ))
    $events = $schedule->findEventsRange( $params['rangeStart'], $params['rangeEnd'], $params['calendar'], $params['timezones'] );
else
    $events = $schedule->findEventsSearch( $params['summary'], $params['description'], $params['calendar'], $params['timezones'], $params['limit'], $params['offset'] );

echo json_encode( $events );
?>