<?php

require_once __DIR__ . '/../../../lib/default/diffbackend/diffbackend.php';
require_once EXPRESSO_PATH . '/prototype/api/controller.php';
require_once EXPRESSO_PATH . '/prototype/api/config.php';
require_once EXPRESSO_PATH . '/prototype/modules/calendar/constants.php';

use prototype\api\Config as Config;

class ExpressoCalendarProvider extends BackendDiff
{

    var $_uidnumber;

    function __construct()
    {

    }

    /**
     * Returns a list (array) of folders, each entry being an associative array
     * with the same entries as StatFolder(). This method should return stable information; ie
     * if nothing has changed, the items in the array must be exactly the same. The order of
     * the items within the array is not important though.
     *
     * @access protected
     * @return array/boolean        false if the list could not be retrieved
     */
    public function GetFolderList()
    {
        $return = array();
        $criteria = CALENDAR_SYNC_SIGNED_CALENDARS ? array( 'filter' => array( 'AND' , array( '=' , 'type' , '0' ) , array( '=' , 'user' , $this->_uidnumber ))) : array( 'filter' => array ( 'AND'  ,array( '=' , 'isOwner' , '1' ),array( '=' , 'type' , '0' ) , array( '=' , 'user' , $this->_uidnumber )));
        $sigs = Controller::find(array('concept' => 'calendarSignature'), array( 'id','calendar' ), $criteria);

        if(Request::GetDeviceType()  == 'iPhone' || Request::GetDeviceType()  == 'iPad')
        {
            foreach($sigs as $sig)
            {
                $calendar =  Controller::read( array( 'concept' => 'calendar' , 'id' => $sig['calendar'] ));
                $tmpSig = array();
                $tmpSig["id"] = 'calendar'.$sig['id'];
                $tmpSig["parent"] = 0;
                $tmpSig["mod"] = $calendar['name'];
                $return[] = $tmpSig;
            }
        }
        else
        {
            $defaultCalendar = Controller::find(array('concept' => 'modulePreference'), array('value','id') , array('filter' => array( 'and' , array('=' , 'name' , 'defaultCalendar') , array('=' , 'module' , 'expressoCalendar') , array('=' , 'user' , $this->_uidnumber )  )) );

            if(isset($defaultCalendar[0])) //Prioriza agenda default de importação pois o android so sincroniza a primeira agenda.
            {
                foreach($sigs as $i => $sig)
                {
                    if($sig['calendar'] == $defaultCalendar[0]['value'])
                    {
                        $calendar =  Controller::read( array( 'concept' => 'calendar' , 'id' => $sig['calendar'] ));
                        $tmpSig = array();
                        $tmpSig["id"] = 'calendar'.$sig['id'];
                        $tmpSig["parent"] = 0;
                        $tmpSig["mod"] = $calendar['name'];
                        $return[] = $tmpSig;
                    }
                }
            }
            else
            {
                $sig = $sigs[0];
                $calendar =  Controller::read( array( 'concept' => 'calendar' , 'id' => $sig['calendar'] ));
                $tmpSig = array();
                $tmpSig["id"] = 'calendar'.$sig['id'];
                $tmpSig["parent"] = 0;
                $tmpSig["mod"] = $calendar['name'];
                $return[] = $tmpSig;

            }
        }

        return $return;
    }

    /**
     * Returns an actual SyncFolder object with all the properties set. Folders
     * are pretty simple, having only a type, a name, a parent and a server ID.
     *
     * @param string        $id           id of the folder
     *
     * @access public
     * @return object   SyncFolder with information
     */
    public function GetFolder($id)
    {
        $idNumber = (int)str_replace('calendar' , '' , $id);

        $calendarSignature =  Controller::read( array( 'concept' => 'calendarSignature' , 'id' => $idNumber ));
        $calendar =  Controller::read( array( 'concept' => 'calendar' , 'id' => $calendarSignature['calendar'] ));

        if(is_array($calendarSignature) && count($calendarSignature) > 0 )
        {
            $folder = new SyncFolder();
            $folder->serverid = $id;
            $folder->parentid = "0";
            $folder->displayname = $calendar['name'];
            $folder->type = SYNC_FOLDER_TYPE_APPOINTMENT;
            return $folder;
        }

        return false;
    }

    /**
     * Returns folder stats. An associative array with properties is expected.
     *
     * @param string        $id             id of the folder
     *
     * @access public
     * @return array
     *          Associative array(
     *              string  "id"            The server ID that will be used to identify the folder. It must be unique, and not too long
     *                                      How long exactly is not known, but try keeping it under 20 chars or so. It must be a string.
     *              string  "parent"        The server ID of the parent of the folder. Same restrictions as 'id' apply.
     *              long    "mod"           This is the modification signature. It is any arbitrary string which is constant as long as
     *                                      the folder has not changed. In practice this means that 'mod' can be equal to the folder name
     *                                      as this is the only thing that ever changes in folders. (the type is normally constant)
     *          )
     */
    public function StatFolder($id)
    {
        $return = array();
        $idNumber = (int)str_replace('calendar' , '' , $id);
        $calendarSignature =  Controller::read( array( 'concept' => 'calendarSignature' , 'id' => $idNumber ));
        $calendar =  Controller::read( array( 'concept' => 'calendar' , 'id' => $calendarSignature['calendar'] ));

        $return["id"] = $id;
        $return["parent"] = 0;
        $return["mod"] = $calendar['name'];

        return $return;
    }

    /**
     * Creates or modifies a folder
     *
     * @param string        $folderid       id of the parent folder
     * @param string        $oldid          if empty -> new folder created, else folder is to be renamed
     * @param string        $displayname    new folder name (to be created, or to be renamed to)
     * @param int           $type           folder type
     *
     * @access public
     * @return boolean                      status
     * @throws StatusException              could throw specific SYNC_FSSTATUS_* exceptions
     *
     */
    public function ChangeFolder($folderid, $oldid, $displayname, $type)
    {
        if($oldid)
        {
            $idNumber = (int)str_replace('calendar' , '' , $oldid);
            $calendarSignature =  Controller::read( array( 'concept' => 'calendarSignature' , 'id' => $idNumber ));

            Controller::update( array('concept' => 'calendar' , 'id' => $calendarSignature['calendar']), array( 'name' => $displayname) );

            return $this->StatFolder($oldid);
        }
        else
        {
             $cal = array('name' => $displayname,
                'timezone' => 'America/Sao_Paulo',
                'type' => '0'
            );

            $calCreated = Controller::create(array('concept' => 'calendar'), $cal);

            if(!$calCreated){
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
                return false;
            }
            else
            {
                $return = array();
                $return["id"] = 'calendar'.$calCreated;
                $return["parent"] = 0;
                $return["mod"] = $displayname;
                return $return;
            }
        }

        return false;

    }

    /**
     * Deletes a folder
     *
     * @param string        $id
     * @param string        $parent         is normally false
     *
     * @access public
     * @return boolean                      status - false if e.g. does not exist
     * @throws StatusException              could throw specific SYNC_FSSTATUS_* exceptions
     */
    public function DeleteFolder($id, $parent)
    {
        $interation = array();
        $idNumber = (int)str_replace('calendar' , '' , $id);
        $calendarSignature =  Controller::read( array( 'concept' => 'calendarSignature' , 'id' => $idNumber ));

        $interation['calendar://' . $calendarSignature['calendar']] = false;
        ob_start();
        $args = $interation;
        include EXPRESSO_PATH.'/prototype/Sync.php';
        ob_end_clean();

        return true;
    }

    /**
     * Returns a list (array) of messages, each entry being an associative array
     * with the same entries as StatMessage(). This method should return stable information; ie
     * if nothing has changed, the items in the array must be exactly the same. The order of
     * the items within the array is not important though.
     *
     * The $cutoffdate is a date in the past, representing the date since which items should be shown.
     * This cutoffdate is determined by the user's setting of getting 'Last 3 days' of e-mail, etc. If
     * the cutoffdate is ignored, the user will not be able to select their own cutoffdate, but all
     * will work OK apart from that.
     *
     * @param string        $folderid       id of the parent folder
     * @param long          $cutoffdate     timestamp in the past from which on messages should be returned
     *
     * @access public
     * @return array/false                  array with messages or false if folder is not available
     */
    public function GetMessageList($folderid, $cutoffdate)
    {
        $idNumber = (int)str_replace('calendar' , '' , $folderid);
        $cal_ids = null;
        $messages = array();

        $sql = 'SELECT calendar_object.last_update , calendar_object.cal_uid FROM calendar_signature , calendar , calendar_to_calendar_object, calendar_object WHERE calendar_signature.id = '.$idNumber.' AND calendar_signature.calendar_id = calendar.id AND calendar_to_calendar_object.calendar_id = calendar.id AND calendar_to_calendar_object.calendar_object_id = calendar_object.id  AND calendar_object.last_update > '. $cutoffdate . '000';

        $rs = Controller::service('PostgreSQL')->execSql($sql);

        if(is_array($rs))
        {
            foreach($rs as $v)
            {
                $message = array();
                $message["id"] = $v['cal_uid'];
                $message["mod"] = substr($v['last_update'], 0, -3);
                $message["flags"] = 1; // always 'read'
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * Returns the actual SyncXXX object type. The '$folderid' of parent folder can be used.
     * Mixing item types returned is illegal and will be blocked by the engine; ie returning an Email object in a
     * Tasks folder will not do anything. The SyncXXX objects should be filled with as much information as possible,
     * but at least the subject, body, to, from, etc.
     *
     * @param string            $folderid           id of the parent folder
     * @param string            $id                 id of the message
     * @param ContentParameters $contentparameters  parameters of the requested message (truncation, mimesupport etc)
     *
     * @access public
     * @return object/false                 false if the message could not be retrieved
     */
    public function GetMessage($folderid, $id, $contentparameters)
    {
        $idNumber = (int)str_replace('calendar' , '' , $folderid);
        $calendarSignature =  Controller::read( array( 'concept' => 'calendarSignature' , 'id' => $idNumber ));

        $schedulable = Controller::find(array('concept' => 'schedulable'), null , array('filter' => array( '=' , 'uid' , $id)));
        if( is_array($schedulable) && count($schedulable) > 0 )

            $schedulable = $schedulable[0];
        else
            return false;

        $message = new SyncAppointment();
        $message->uid = $id;
        $message->dtstamp = (int) substr($schedulable['dtstamp'], 0, -3);
        $message->starttime =  (int) substr($schedulable['startTime'], 0, -3);
        $message->endtime = (int) substr($schedulable['endTime'], 0, -3);
        $message->deleted = 0;

        $message->subject = mb_convert_encoding($schedulable['summary'] , 'UTF-8' , 'UTF-8,ISO-8859-1');
        $message->location =  mb_convert_encoding($schedulable['location'], 'UTF-8' , 'UTF-8,ISO-8859-1');

        if(isset($schedulable['description']) && $schedulable['description'] != "") {
            $message->body = mb_convert_encoding($schedulable['description'], 'UTF-8' , 'UTF-8,ISO-8859-1');  // phpgw_cal.description
            $message->bodysize = strlen($message->body);
            $message->bodytruncated = 0;
        }

        $message->sensitivity = 0; // 0 - Normal,
        $message->alldayevent = (int)$schedulable['allDay']; // (0 - Não(default), 1- Sim)
        $message->timezone = base64_encode($this->_getSyncBlobFromTZ($this->_getGMTTZ()));


        /*
         * Sincronização de participantes e organizador
         */
        $participants = Controller::find(array('concept' => 'participant'), null , array('deepness' => 1 , 'filter' => array( '=' , 'schedulable' , $schedulable['id'] )));
        if(is_array($participants) && count($participants) > 0)
        {
            $message->attendees = array();
            foreach($participants as $participant)
            {
                if($participant['isOrganizer'] == 1) //organizador
                {
                    $message->organizername = mb_convert_encoding($participant['user']['name'], 'UTF-8' , 'UTF-8,ISO-8859-1');
                    $message->organizeremail = mb_convert_encoding($participant['user']['mail'], 'UTF-8' , 'UTF-8,ISO-8859-1');
                }
                else
                {
                    $attendee = new SyncAttendee();
                    $attendee->name =  mb_convert_encoding($participant['user']['name'], 'UTF-8' , 'UTF-8,ISO-8859-1');
                    $attendee->email = mb_convert_encoding($participant['user']['mail'], 'UTF-8' , 'UTF-8,ISO-8859-1');
                    $message->attendees[] = $attendee;
                }

                if($participant['user']['id'] == $this->_uidnumber  )
                {
                    if($participant['isOrganizer'] == 1 || strpos($participant['acl'] ,'w') !== false) // Caso ele seja organizador ou tenha permisão de editar o evento
                    {
                        $message->meetingstatus = 0;
                    }
                    else
                    {
                        $message->meetingstatus = 3;
                    }

                    if(isset($participant['alarms'][0]) )
                    {
                        switch($participant['alarms'][0]['unit'])
                        {
                            case 'h':
                                $mult = 60;
                                break;
                            case 'd':
                                $mult = 1440;
                                break;
                            default:
                                $mult = 1;
                                break;
                        }

                        $message->reminder = $participant['alarms'][0]['time'] * $mult;
                    }

                    switch($participant['status'])
                    {
                        case STATUS_ACCEPTED:
                            $message->busystatus = 2;
                            break;
                        case STATUS_TENTATIVE:
                            $message->busystatus = 1;
                            break;
                        case STATUS_DECLINED:
                            $message->busystatus = 3;
                            break;
                        case STATUS_UNANSWERED:
                            $message->busystatus = 0;
                            break;
                    }

                }
            }
        }
        //------------------------------------------------------------------------------------------------------------//

        /*
        * Sincronização de Recorrência
        */
        $repeats = Controller::find(array('concept' => 'repeat'), null , array( 'filter' => array( 'and' , array( '=' , 'schedulable' , $schedulable['id'] ),array( '!=' , 'frequency' , 'none' )  ) ));
        if(is_array($repeats) && count($repeats) > 0)
        {
            $repeat = $repeats[0];
            $recur = new SyncRecurrence();

            switch($repeat['frequency'])
            {
                case 'daily':
                    $recur->type = 0;
                    break;
                case 'weekly':
                    $recur->type = 1;
                    break;
                case 'monthly':
                    $recur->type = 2;
                    break;
                case 'yearly':
                    $recur->type = 5;
                    break;
            }

            if($repeat['endTime'])
                $recur->until =  (int) substr($repeat['endTime'], 0, -3);

            $recur->interval = $repeat['interval'] ? $repeat['interval'] : 1;

            if($repeat["count"])
                $recur->occurrences = (int)$repeat["count"];

            if($repeat["byweekno"])
                $recur->weekofmonth = (int)$repeat["byweekno"];

            if($repeat["bymonthday"])
                $recur->dayofmonth = (int)$repeat["bymonthday"];


            if($repeat["byday"])
                $recur->dayofweek = $this->formatDoWeek($repeat["byday"]);

            //$recurrence->monthofyear ; //Não implementado no expresso

            $expetions = Controller::find(array('concept' => 'repeatOccurrence'), null , array( 'filter' => array( 'and' , array( '=' , 'exception' , '1' ),array( '=' , 'repeat' , $repeat['id'] ) )));
            if(is_array($expetions) && count($expetions) > 0)
            {
                $message->exceptions = array();
                foreach($expetions as $expetion)
                {
                    $exception = new SyncAppointmentException();
                    $exception->exceptionstarttime =  (int) substr($expetion['occurrence'], 0, -3);
                    $exception->deleted = '1';
                    $message->exceptions[] = $exception;
                }
            }

            $message->recurrence = $recur;
        }


        return $message;
    }

    /**
     * Returns message stats, analogous to the folder stats from StatFolder().
     *
     * @param string        $folderid       id of the folder
     * @param string        $id             id of the message
     *
     * @access public
     * @return array or boolean if fails
     *          Associative array(
     *              string  "id"            Server unique identifier for the message. Again, try to keep this short (under 20 chars)
     *              int     "flags"         simply '0' for unread, '1' for read
     *              long    "mod"           This is the modification signature. It is any arbitrary string which is constant as long as
     *                                      the message has not changed. As soon as this signature changes, the item is assumed to be completely
     *                                      changed, and will be sent to the PDA as a whole. Normally you can use something like the modification
     *                                      time for this field, which will change as soon as the contents have changed.
     *          )
     */
    public function StatMessage($folderid, $id)
    {
        $sql = 'SELECT last_update , cal_uid FROM calendar_object WHERE cal_uid = \''.pg_escape_string($id) .'\'';
        $message = array();

        $rs = Controller::service('PostgreSQL')->execSql($sql);
        if(is_array($rs))
        {
            $message["mod"] = substr($rs[0]['last_update'], 0, -3);
            $message["id"] = $id;
            $message["flags"] = 1;
        }
        return $message;
    }

    /**
     * Called when a message has been changed on the mobile. The new message must be saved to disk.
     * The return value must be whatever would be returned from StatMessage() after the message has been saved.
     * This way, the 'flags' and the 'mod' properties of the StatMessage() item may change via ChangeMessage().
     * This method will never be called on E-mail items as it's not 'possible' to change e-mail items. It's only
     * possible to set them as 'read' or 'unread'.
     *
     * @param string        $folderid       id of the folder
     * @param string        $id             id of the message
     * @param SyncXXX       $message        the SyncObject containing a message
     *
     * @access public
     * @return array                        same return value as StatMessage()
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public function ChangeMessage($folderid, $idMessage, $message, $contentParameters)
    {

        $idNumber = (int)str_replace('calendar' , '' , $folderid);
        $calendarSignature =  Controller::read( array( 'concept' => 'calendarSignature' , 'id' => $idNumber ));
        $calendar =  Controller::read( array( 'concept' => 'calendar' , 'id' => $calendarSignature['calendar'] ));

        if($idMessage)
        {
            $schedulable = Controller::find(array('concept' => 'schedulable'), null , array('deepness'=> 2 , 'filter' => array( '=' , 'uid' , $idMessage)));
            $schedulable = $schedulable[0];


            foreach($schedulable['participants'] as $i => $v)
            {
                if($v['user']['id'] == $this->_uidnumber )
                {
                    if(strpos($v['acl'] ,'w') !== false) //Caso o usuario tenha permissão de editar o evento
                    {
                        return  $this->updateEvent($folderid, $idMessage, $message , $calendar ,$schedulable);
                    }
                    else
                    {
                        $interation = array();

                        if(isset($message->reminder) && $message->reminder > 0)
                        {
                            $alarm = array();
                            $alarmID = mt_rand() . '6(Formatter)';
                            $alarm['type'] = 'alert';
                            $alarm['time'] = $message->reminder;
                            $alarm['unit'] = 'm';
                            $alarm['participant'] = $v['id'];
                            $alarm['schedulable'] = $schedulable['id'];
                            $interation['alarm://' . $alarmID ] = $alarm;

                        }

                        $status  = $this->formatBusy($message->busystatus);

                        if($status == STATUS_DECLINED ) //Caso ele não seja dono do evento e recusou o convite deletar o evento da sua agenda.
                        {
                            Controller::deleteAll(array('concept' => 'calendarToSchedulable' ) , false , array('filter' => array('AND', array('=','calendar',$calendarSignature['calendar']), array('=','schedulable',$schedulable['id']))));
                        }

                        $v['status'] = $status;

                        $interation['participant://' . $v['id'] ] = $v;

                        ob_start();
                        $args = $interation;
                        include EXPRESSO_PATH.'/prototype/Sync.php';
                        ob_end_clean();

                    }
                }
            }
            return $this->StatMessage($folderid, $message->uid);
        }
        else
        {
            if (!$schedulable = $this->_getSchedulable($message->uid))
                return  $this->createEvent($folderid, $idMessage, $message ,$calendar);
            else{
                $links = Controller::read(array('concept' => 'calendarToSchedulable'), array('id'), array('filter' =>
                array('AND',
                    array('=', 'calendar', $calendar['id']),
                    array('=', 'schedulable', $schedulable['id'])
                )));

                if(!$links &&  !isset($links[0]))
                    Controller::create(array('concept' => 'calendarToSchedulable'), array('calendar' => $calendar['id'], 'schedulable' => $schedulable['id']));

                foreach($schedulable['participants'] as $i => $v)
                {
                    if($v['user']['id'] == $this->_uidnumber)
                    {
                        Controller::update(array('concept' => 'participant','id' => $v['id']), array('status' => $this->formatBusy($message->busystatus ) ));
                    }
                }

                return $this->StatMessage($folderid, $message->uid);
            }
        }

    }

    private function _getSchedulable($uid) {
        $schedulable = Controller::find(array('concept' => 'schedulable'), false, array('filter' => array('=', 'uid', $uid), 'deepness' => 2));
        return (isset($schedulable[0])) ? $schedulable[0] : false;
    }

    private function updateEvent($folderid, $idMessage, $message , $calendar ,$schedulable )
    {


        $tz_CEL = $this->_getTZFromSyncBlob(base64_decode($message->timezone));
        $GMT_CEL = -(($tz_CEL["bias"] + $tz_CEL["dstbias"]) * 60);

        $interation = array();
        $eventID = $schedulable['id'];
        $schedulable['uid'] = $message->uid;
        $schedulable['summary'] = $message->subject;
        $schedulable['location'] = $message->location;
        $schedulable['class'] = 1;

        /// Eliminana o timezone, enviado pelo ceulular e coloca no timezone do calendario.
        // o celular não manda o nome do timezone apenas o offset dele dae não tem como saber qual foi o timezone selecionado.
        $calendarSignatureTimezone = new DateTimeZone($calendar['timezone']) ;
        $schedulable['startTime'] = (($message->starttime + $GMT_CEL) + ($calendarSignatureTimezone->getOffset(new DateTime('@'.($message->starttime + $GMT_CEL), new DateTimeZone('UTC'))) * -1) ) *1000; //$message->starttime  * 1000;
        $schedulable['endTime'] = (($message->endtime + $GMT_CEL) + ($calendarSignatureTimezone->getOffset(new DateTime('@'.($message->endtime + $GMT_CEL), new DateTimeZone('UTC')))* -1)) *1000;//$message->endtime  * 1000;
        $schedulable['timezone'] = $calendar['timezone'];


        $sv  = new DateTime('@'.($message->starttime + $GMT_CEL), $calendarSignatureTimezone);

        if($sv->format('I') == 0)
            $schedulable['startTime'] = $schedulable['startTime'] - 3600000;

        $ev  = new DateTime('@'.($message->endtime + $GMT_CEL), $calendarSignatureTimezone);

        if($ev->format('I') == 0)
            $schedulable['endTime'] = $schedulable['endTime'] - 3600000;

        $schedulable['allDay'] = $message->alldayevent;
        $schedulable['description'] = $message->body;
        $schedulable['dtstamp'] = $message->dtstamp;
        $schedulable['lastUpdate'] = time() * 1000;
        $schedulable['type'] = '1';


        if(isset($message->recurrence))
        {
            $repeatID = isset($schedulable['repeat']) ? $schedulable['repeat']['id'] : mt_rand() . '3(Formatter)';

            $repeat = array();
            $repeat['schedulable'] = $eventID;

            switch( $message->recurrence->type )
            {
                case 0:
                    $repeat['frequency'] = 'daily';
                    break;
                case 1:
                    $repeat['frequency'] = 'weekly';
                    break;
                case 2:
                    $repeat['frequency'] = 'monthly';
                    break;
                case 5:
                    $repeat['frequency'] = 'yearly';
                    break;
            }

            if(isset($message->recurrence->until))
                $repeat['endTime'] =  $message->recurrence->until  * 1000 ;
            else
                $repeat['endTime'] = null;

            $repeat['startTime'] =  $message->starttime * 1000 ;

            $repeat['interval'] =  isset($message->recurrence->interval) ? $message->recurrence->interval : 1;

            if(isset($message->recurrence->occurrences) && $message->recurrence->occurrences > 0)
                $repeat["count"] = $message->recurrence->occurrences;
            else
                $repeat["count"] = 0;

            if(isset($message->recurrence->weekofmonth) && $message->recurrence->weekofmonth > 0)
                $repeat["byweekno"] =  $message->recurrence->weekofmonth;
            else
                $repeat["byweekno"] = 0;

            if(isset($message->recurrence->dayofmonth) && $message->recurrence->dayofmonth > 0)
                $repeat["bymonthday"] = $message->recurrence->dayofmonth;
            else
                $repeat["bymonthday"] = 0;

            $day = $message->recurrence->dayofweek;
            $day_of_week_array = array();
            if (($day & 1) > 0) $day_of_week_array[] = 'SU';
            if (($day & 2) > 0) $day_of_week_array[] = 'MO';
            if (($day & 4) > 0) $day_of_week_array[] = 'TU';
            if (($day & 8) > 0) $day_of_week_array[] = 'WE';
            if (($day & 16) > 0) $day_of_week_array[] = 'TH';
            if (($day & 32) > 0) $day_of_week_array[] = 'FR';
            if (($day & 64) > 0) $day_of_week_array[] = 'SA';

            $repeat["byday"] = implode(',' ,$day_of_week_array);
            $interation['repeat://' . $repeatID] = $repeat;

        }
        else if (isset($schedulable['repeat']) )
        {
            $interation['repeat://'.$schedulable['repeat']['id']] = null;
        }

        $partForDelete = $schedulable['participants'];

        foreach($partForDelete as $partForDeleteIndice => $partForDeleteValue)
        {
            if($partForDeleteValue['isOrganizer'] == '1')
            {
                if(isset($message->reminder) && $message->reminder > 0)
                {
                    $alarm = array();
                    $alarmID =  isset($partForDeleteValue['alarms'][0]['id']) ? $partForDeleteValue['alarms'][0]['id'] :  mt_rand() . '6(Formatter)';
                    $alarm['type'] = 'alert';
                    $alarm['time'] = $message->reminder;
                    $alarm['unit'] = 'm';

                    foreach ($interation as $iint => &$vint)
                    {
                        if(isset($vint['user']) && $vint['user'] == $this->_uidnumber)
                        {
                            $alarm['participant'] = str_replace('participant://', '', $iint);
                            $vint['alarms'][] = $alarmID;
                        }
                    }

                    $alarm['schedulable'] = $eventID;
                    $interation['alarm://' . $alarmID ] = $alarm;


                }
                else if(isset($partForDeleteValue['alarms'][0]['id']))
                    $interation['alarm://' . $partForDeleteValue['alarms'][0]['id'] ] = false;

                unset($partForDelete[$partForDeleteIndice]);
                unset($schedulable['participants'][$partForDeleteIndice]['alarms']);
            }
        }

        if(isset($message->attendees)  && count($message->attendees) > 0)
        {

            foreach($message->attendees as $attendee)
            {

                if($this->_getParticipantByMail($attendee->email, $schedulable['participants']) === false)
                {
                    $participantID = mt_rand() . '2(Formatter)';
                    $participant = array();
                    $participant['schedulable'] = $eventID;
                    $participant['isOrganizer'] = '0';
                    $participant['acl'] = 'r';

                    /* Verifica se este usuario é um usuario interno do ldap */
                    $intUser = Controller::find(array('concept' => 'user'), array('id', 'isExternal'), array('filter' => array('OR', array('=', 'mail', $attendee->email), array('=', 'mailAlternateAddress', $attendee->email))));

                    $user = null;
                    if ($intUser && count($intUser) > 0) {
                        $participant['isExternal'] = isset($intUser[0]['isExternal']) ? $intUser[0]['isExternal'] : 0;
                        $participant['user'] = $intUser[0]['id'];
                    } else {
                        $participant['isExternal'] = 1;
                        /* Gera um randon id para o contexto formater */
                        $userID = mt_rand() . '4(Formatter)';

                        $user['mail'] = $attendee->email;
                        $user['name'] = ( isset($attendee->name) ) ? $attendee->name : '';
                        $user['participants'] = array($participantID);
                        $user['isExternal'] = '1';
                        $participant['user'] = $userID;
                        $interation['user://' . $userID] = $user;
                    }

                    $interation['participant://' . $participantID] = $participant;
                    $schedulable['participants'][] = $participantID;
                }
                else
                    unset($partForDelete[$this->_getParticipantByMail($attendee->email, $schedulable['participants'])]);

            }

        }

        foreach( $partForDelete as $toDelete)
        {
            $interation['participant://' . $toDelete['id']] = false;
            foreach ($schedulable['participants'] as $ipart => $part)
            {
                if($part['id'] == $toDelete['id'])
                {
                    unset($schedulable['participants'][$ipart]);
                }
            }

            $schedulable['participants'] = array_merge($schedulable['participants'] , array());

        }

        foreach($schedulable['participants'] as $i => $v)
        {
            if($v['user']['id'] == $this->_uidnumber )
            {
                $schedulable['participants'][$i]['status'] = $this->formatBusy($message->busystatus);
            }
        }

        unset($schedulable['repeat']);

        $interation['schedulable://' . $eventID] = $schedulable;

        ob_start();
        $args = $interation;
        include EXPRESSO_PATH.'/prototype/Sync.php';
        ob_end_clean();

        return $this->StatMessage($folderid, $message->uid);
    }

    private function createEvent($folderid, $idMessage, $message , $calendar)
    {
        $tz_CEL = $this->_getTZFromSyncBlob(base64_decode($message->timezone));
        $GMT_CEL = -(($tz_CEL["bias"] + $tz_CEL["dstbias"]) * 60);


        $interation = array();
        $schedulable = array();
        $eventID = mt_rand() . '(Formatter)';

        $schedulable['calendar'] = $calendar['id'];
        $schedulable['uid'] = $message->uid;
        $schedulable['summary'] = $message->subject;
        $schedulable['location'] = $message->location;
        $schedulable['class'] = 1;


        /// Eliminana o timezone, enviado pelo ceulular e coloca no timezone do calendario.
        // o celular não manda o nome do timezone apenas o offset dele dae não tem como saber qual foi o timezone selecionado.
        $calendarSignatureTimezone = new DateTimeZone($calendar['timezone']) ;
        $schedulable['startTime'] = (($message->starttime + $GMT_CEL) + ($calendarSignatureTimezone->getOffset(new DateTime('@'.($message->starttime + $GMT_CEL), new DateTimeZone('UTC'))) * -1) ) *1000; //$message->starttime  * 1000;
        $schedulable['endTime'] = (($message->endtime + $GMT_CEL) + ($calendarSignatureTimezone->getOffset(new DateTime('@'.($message->endtime + $GMT_CEL), new DateTimeZone('UTC')))* -1)) *1000;//$message->endtime  * 1000;

        $sv  = new DateTime('@'.($message->starttime + $GMT_CEL), $calendarSignatureTimezone);

        if($sv->format('I') == 0)
            $schedulable['startTime'] = $schedulable['startTime'] - 3600000;

        $ev  = new DateTime('@'.($message->endtime + $GMT_CEL), $calendarSignatureTimezone);

        if($ev->format('I') == 0)
            $schedulable['endTime'] = $schedulable['endTime'] - 3600000;

        $schedulable['timezone'] = $calendar['timezone'];


        $schedulable['allDay'] = $message->alldayevent;
        $schedulable['description'] = $message->body;
        $schedulable['dtstamp'] = $message->dtstamp;
        // $schedulable['lastUpdate'] = 0;
        $schedulable['type'] = '1';
        $participant = array();
        $participantID = mt_rand() . '2(Formatter)';
        $participant['schedulable'] = $eventID;
        $participant['isOrganizer'] = '1';
        $participant['acl'] = 'rowi';
        $participant['status'] = '1';

        if($message->organizeremail)
        {
            /* Verifica se este usuario é um usuario interno do ldap */
            $intUser = Controller::find(array('concept' => 'user'), array('id', 'isExternal'), array('filter' => array('OR', array('=', 'mail', $message->organizeremail), array('=', 'mailAlternateAddress', $message->organizeremail))));

            $user = null;
            if ($intUser && count($intUser) > 0) {
                $participant['isExternal'] = isset($intUser[0]['isExternal']) ? $intUser[0]['isExternal'] : 0;
                $participant['user'] = $intUser[0]['id'];
            } else {
                $participant['isExternal'] = 1;
                /* Gera um randon id para o contexto formater */
                $userID = mt_rand() . '4(Formatter)';

                $user['mail'] = $message->organizeremail;
                $user['name'] = ( isset($message->organizername) ) ? $message->organizername : '';
                $user['participants'] = array($participantID);
                $user['isExternal'] = '1';
                $participant['user'] = $userID;
                $interation['user://' . $userID] = $user;
            }
        }
        else
        {
            $participant['isExternal'] = 0;
            $participant['user'] = $this->_uidnumber;
            $participant['status'] = $this->formatBusy($message->busystatus);
        }

        //Caso exista recorrencias
        if(isset($message->recurrence))
        {
            /* Gera um randon id para o contexto formater */
            $repeatID = mt_rand() . '3(Formatter)';

            $repeat = array();
            $repeat['schedulable'] = $eventID;

            switch( $message->recurrence->type )
            {
                case 0:
                    $repeat['frequency'] = 'daily';
                    break;
                case 1:
                    $repeat['frequency'] = 'weekly';
                    break;
                case 2:
                    $repeat['frequency'] = 'monthly';
                    break;
                case 5:
                    $repeat['frequency'] = 'yearly';
                    break;
            }

            if(isset($message->recurrence->until))
                $repeat['endTime'] =  $message->recurrence->until  * 1000 ;

            $repeat['startTime'] =  $message->starttime * 1000 ;

            $repeat['interval'] =  isset($message->recurrence->interval) ? $message->recurrence->interval : 1;

            if(isset($message->recurrence->occurrences) && $message->recurrence->occurrences > 0)
                $repeat["count"] = $message->recurrence->occurrences;

            if(isset($message->recurrence->weekofmonth) && $message->recurrence->weekofmonth > 0)
                $repeat["byweekno"] =  $message->recurrence->weekofmonth;

            if(isset($message->recurrence->dayofmonth) && $message->recurrence->dayofmonth > 0)
                $repeat["bymonthday"] = $message->recurrence->dayofmonth;

            $day = $message->recurrence->dayofweek;
            $day_of_week_array = array();
            if (($day & 1) > 0) $day_of_week_array[] = 'SU';
            if (($day & 2) > 0) $day_of_week_array[] = 'MO';
            if (($day & 4) > 0) $day_of_week_array[] = 'TU';
            if (($day & 8) > 0) $day_of_week_array[] = 'WE';
            if (($day & 16) > 0) $day_of_week_array[] = 'TH';
            if (($day & 32) > 0) $day_of_week_array[] = 'FR';
            if (($day & 64) > 0) $day_of_week_array[] = 'SA';

            $repeat["byday"] = implode(',' ,$day_of_week_array);
            $interation['repeat://' . $repeatID] = $repeat;

        }

        $interation['participant://' . $participantID] = $participant;
        $schedulable['participants'][] = $participantID;


        if(isset($message->attendees)  && count($message->attendees) > 0)
        {
            foreach($message->attendees as $attendee)
            {
                $participantID = mt_rand() . '2(Formatter)';
                $participant = array();
                $participant['schedulable'] = $eventID;
                $participant['isOrganizer'] = '0';
                $participant['acl'] = 'r';

                /* Verifica se este usuario é um usuario interno do ldap */
                $intUser = Controller::find(array('concept' => 'user'), array('id', 'isExternal'), array('filter' => array('OR', array('=', 'mail', $attendee->email), array('=', 'mailAlternateAddress', $attendee->email))));

                $user = null;
                if ($intUser && count($intUser) > 0) {
                    $participant['isExternal'] = isset($intUser[0]['isExternal']) ? $intUser[0]['isExternal'] : 0;
                    $participant['user'] = $intUser[0]['id'];
                } else {
                    $participant['isExternal'] = 1;
                    /* Gera um randon id para o contexto formater */
                    $userID = mt_rand() . '4(Formatter)';

                    $user['mail'] = $attendee->email;
                    $user['name'] = ( isset($attendee->name) ) ? $attendee->name : '';
                    $user['participants'] = array($participantID);
                    $user['isExternal'] = '1';
                    $participant['user'] = $userID;
                    $interation['user://' . $userID] = $user;

                    if($userID == $this->_uidnumber)
                    {
                        $participant['status'] = $this->formatBusy($message->busystatus);
                    }

                }

                $interation['participant://' . $participantID] = $participant;
                $schedulable['participants'][] = $participantID;

            }

        }

        if(isset($message->reminder) && $message->reminder > 0)
        {
            $alarm = array();
            $alarmID = mt_rand() . '6(Formatter)';
            $alarm['type'] = 'alert';
            $alarm['time'] = $message->reminder;
            $alarm['unit'] = 'm';

            foreach ($interation as $iint => &$vint)
            {
                if(isset($vint['user']) && $vint['user'] == $this->_uidnumber)
                {
                    $alarm['participant'] = str_replace('participant://', '', $iint);
                    $vint['alarms'][] = $alarmID;
                }
            }

            $alarm['schedulable'] = $eventID;
            $interation['alarm://' . $alarmID ] = $alarm;


        }

        $interation['schedulable://' . $eventID] = $schedulable;

        ob_start();
        $args = $interation;
        include EXPRESSO_PATH.'/prototype/Sync.php';
        ob_end_clean();

        return $this->StatMessage($folderid, $message->uid);
    }


    /**
     * Changes the 'read' flag of a message on disk. The $flags
     * parameter can only be '1' (read) or '0' (unread). After a call to
     * SetReadFlag(), GetMessageList() should return the message with the
     * new 'flags' but should not modify the 'mod' parameter. If you do
     * change 'mod', simply setting the message to 'read' on the mobile will trigger
     * a full resync of the item from the server.
     *
     * @param string        $folderid       id of the folder
     * @param string        $id             id of the message
     * @param int           $flags          read flag of the message
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public function SetReadFlag($folderid, $id, $flags, $contentParameters)
    {
        return true;
    }

    /**
     * Called when the user has requested to delete (really delete) a message. Usually
     * this means just unlinking the file its in or somesuch. After this call has succeeded, a call to
     * GetMessageList() should no longer list the message. If it does, the message will be re-sent to the mobile
     * as it will be seen as a 'new' item. This means that if this method is not implemented, it's possible to
     * delete messages on the PDA, but as soon as a sync is done, the item will be resynched to the mobile
     *
     * @param string        $folderid       id of the folder
     * @param string        $id             id of the message
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public function DeleteMessage($folderid, $id, $contentParameters)
    {

        $idNumber = (int)str_replace('calendar' , '' , $folderid);
        $calendarSignature =  Controller::read( array( 'concept' => 'calendarSignature' , 'id' => $idNumber ));
        $even = $this->_getSchedulable($id );
        $calendar =  Controller::read( array( 'concept' => 'calendar' , 'id' => $calendarSignature['calendar'] ));

        $link = Controller::read(array('concept' => 'calendarToSchedulable'), false, array('filter' => array('AND', array('=','calendar',$calendarSignature['calendar']), array('=','schedulable',$even['id']))));

        $delete = false;
        foreach($even['participants'] as $i => $v)
        {
            if($v['user']['id'] == $this->_uidnumber && $v['user']['isOrganizer']  == '1')
            {
                $delete = true;
            }
        }

        if( $delete === true)
        {
            Controller::delete(array('concept' => 'schedulable' , 'id' => $even['id']));
        }
        else
        {
            Controller::delete(array('concept' => 'calendarToSchedulable', 'id' => $link[0]['id']));

            foreach($even['participants'] as $i => $v)
            {
                if($v['user']['id'] == $this->_uidnumber)
                {
                    Controller::update(array('concept' => 'participant','id' => $v['id']), array('status' => STATUS_CANCELLED ));
                }
            }

        }
        return true;
    }

    /**
     * Called when the user moves an item on the PDA from one folder to another. Whatever is needed
     * to move the message on disk has to be done here. After this call, StatMessage() and GetMessageList()
     * should show the items to have a new parent. This means that it will disappear from GetMessageList()
     * of the sourcefolder and the destination folder will show the new message
     *
     * @param string        $folderid       id of the source folder
     * @param string        $id             id of the message
     * @param string        $newfolderid    id of the destination folder
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_MOVEITEMSSTATUS_* exceptions
     */
    public function MoveMessage($folderid, $id, $newfolderid , $contentParameters)
    {
        return false;
    }

    /**
     * Authenticates the user
     *
     * @param string        $username
     * @param string        $domain
     * @param string        $password
     *
     * @access public
     * @return boolean
     * @throws FatalException   e.g. some required libraries are unavailable
     */
    public function Logon($username, $domain, $password)
    {
        $ldapConfig = parse_ini_file(EXPRESSO_PATH . '/prototype/config/OpenLDAP.srv' , true );
        $ldapConfig =  $ldapConfig['config'];

        $sr = ldap_search( $GLOBALS['connections']['ldap'] , $ldapConfig['context'] , "(uid=$username)" , array('uidNumber','uid','mail'), 0 , 1 );
        if(!$sr) return false;

        $entries = ldap_get_entries( $GLOBALS['connections']['ldap'] , $sr );
        $this->_uidnumber = $entries[0]['uidnumber'][0];


        //Inicia Variaveis de para API expresso
        if(!isset($_SESSION))
            session_start();

        $userWallet = array();
        $userWallet['uidNumber'] = $entries[0]['uidnumber'][0];
        $userWallet['uid'] = $entries[0]['uid'][0];
        $userWallet['mail'] = $entries[0]['mail'][0];

        $_SESSION['wallet'] = array();
        $_SESSION['wallet']['user'] = $userWallet;
        $_SESSION['flags']['currentapp'] = 'expressoCalendar';

        //----------------------------------------------------------------------------------------//

        return true;
    }

    /**
     * Logs off
     * non critical operations closing the session should be done here
     *
     * @access public
     * @return boolean
     */
    public function Logoff()
    {

    }

    /**
     * Sends an e-mail
     * This messages needs to be saved into the 'sent items' folder
     *
     * Basically two things can be done
     *      1) Send the message to an SMTP server as-is
     *      2) Parse the message, and send it some other way
     *
     * @param SyncSendMail        $sm         SyncSendMail object
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function SendMail($sm)
    {
        return false;
    }

    /**
     * Returns the waste basket
     *
     * The waste basked is used when deleting items; if this function returns a valid folder ID,
     * then all deletes are handled as moves and are sent to the backend as a move.
     * If it returns FALSE, then deletes are handled as real deletes
     *
     * @access public
     * @return string
     */
    public function GetWasteBasket()
    {
        return false;
    }

    /**
     * Returns the content of the named attachment as stream. The passed attachment identifier is
     * the exact string that is returned in the 'AttName' property of an SyncAttachment.
     * Any information necessary to locate the attachment must be encoded in that 'attname' property.
     * Data is written directly - 'print $data;'
     *
     * @param string        $attname
     *
     * @access public
     * @return SyncItemOperationsAttachment
     * @throws StatusException
     */
    public function GetAttachmentData($attname)
    {
        return false;
    }

    function _getGMTTZ() {
        //$tz = array("bias" => 0, "stdbias" => 0, "dstbias" => 0, "dstendyear" => 0, "dstendmonth" => 2, "dstendday" => 0, "dstendweek" => 2, "dstendhour" => 2, "dstendminute" => 0, "dstendsecond" => 0, "dstendmillis" => 0,
        //"dststartyear" => 0, "dststartmonth" =>10, "dststartday" =>0, "dststartweek" => 3, "dststarthour" => 2, "dststartminute" => 0, "dststartsecond" => 0, "dststartmillis" => 0);
        $tz = array("bias" => 120, "stdbias" => 0, "dstbias" => -60, "dstendyear" => 0, "dstendmonth" => 2, "dstendday" => 0, "dstendweek" => 2, "dstendhour" => 2, "dstendminute" => 0, "dstendsecond" => 0, "dstendmillis" => 0, "dststartyear" => 0, "dststartmonth" =>10, "dststartday" =>0, "dststartweek" => 3, "dststarthour" => 2, "dststartminute" => 0, "dststartsecond" => 0, "dststartmillis" => 0);

        return $tz;
    }
    function _getSyncBlobFromTZ($tz) {
        $packed = pack("la64vvvvvvvv" . "la64vvvvvvvv" . "l",
            $tz["bias"], "", 0, $tz["dstendmonth"], $tz["dstendday"], $tz["dstendweek"], $tz["dstendhour"], $tz["dstendminute"], $tz["dstendsecond"], $tz["dstendmillis"],
            $tz["stdbias"], "", 0, $tz["dststartmonth"], $tz["dststartday"], $tz["dststartweek"], $tz["dststarthour"], $tz["dststartminute"], $tz["dststartsecond"], $tz["dststartmillis"],
            $tz["dstbias"]);

        return $packed;
    }

    function _getTZFromSyncBlob($data) {
        $tz = unpack(    "lbias/a64name/vdstendyear/vdstendmonth/vdstendday/vdstendweek/vdstendhour/vdstendminute/vdstendsecond/vdstendmillis/" .
            "lstdbias/a64name/vdststartyear/vdststartmonth/vdststartday/vdststartweek/vdststarthour/vdststartminute/vdststartsecond/vdststartmillis/" .
            "ldstbias", $data);

        // Make the structure compatible with class.recurrence.php
        $tz["timezone"] = $tz["bias"];
        $tz["timezonedst"] = $tz["dstbias"];

        return $tz;
    }

    private function formatDoWeek($week)
    {
        $recday = explode(',' , $week);
        $nday = 0;
        foreach ($recday as $day)
        {
            switch($day)
            {
                case 'SU':
                    $nday=$nday +1;
                    break;
                case 'MO':
                    $nday=$nday +2;
                    break;
                case 'TU':
                    $nday=$nday +4;
                    break;
                case 'WE':
                    $nday=$nday +8;
                    break;
                case 'TH':
                    $nday=$nday +16;
                    break;
                case 'FR':
                    $nday=$nday +32;
                    break;
                case 'SA':
                    $nday=$nday +64;
                    break;

            }
        }
        return $nday;
    }

    private function _getParticipantByMail($mail, &$participants, $isFull = false) {
        if ($participants && $participants != '')
            foreach ($participants as $i => $v)
                if ((is_array($v) && isset($v['user'])) && ($v['user']['mail'] == $mail || (isset($v['user']['mailAlternateAddress']) && in_array($mail, $v['user']['mailAlternateAddress']))))
                    return $i;
        return false;
    }

    private function _getParticipantIDByMail($mail, &$participants, $isFull = false) {
        if ($participants && $participants != '')
            foreach ($participants as $i => $v)
                if ((is_array($v) && isset($v['user'])) && ($v['user']['mail'] == $mail || (isset($v['user']['mailAlternateAddress']) && in_array($mail, $v['user']['mailAlternateAddress']))))
                    return !!$isFull ? $v : $v['id'];
        return false;
    }


    private function formatBusy($status)
    {
        switch($status)
        {
            case 2:
                return STATUS_ACCEPTED;
                break;
            case 1:
                return STATUS_TENTATIVE;
                break;
            case 3:
                return STATUS_DECLINED;
                break;
            case 0:
                return STATUS_UNANSWERED;
                break;
        }
    }



}
