<?php

require_once ROOTPATH . '/modules/calendar/constants.php';
require_once ROOTPATH . '/modules/calendar/interceptors/Helpers.php';
require_once ROOTPATH . '/plugins/icalcreator/iCalcreator.class.php';
require_once ROOTPATH . '/api/parseTPL.php';

use prototype\api\Config as Config;

class Notifications extends Helpers {

    public function formatNotification(&$uri, &$params, &$data, $original) {
	switch ($params['type']) {
	    case 'suggestion':
		self::formatSuggestion($params);
		break;
	    case 'suggestionResponse':
		self::formatSuggestionResponse($params);
		break;
	}
    }

    /**
     * Analisa o commit do conceito participant e encaminha cada participant para seu devido metodo de notrificação
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Cristiano Corrêa Schmidt
     * @return     void
     * @access     public
     */
    public function commitParticipant(&$uri, &$result, &$data, $original) {
	if (Config::regGet('noAlarm') !== false)
	    return; //Escapa notificações caso a flag de noAlarm esteja ativa.



	$organizers = array(); //Cache Organizadores
	$schedulables = array(); //Cache Schedulables

	foreach ($data as $i => $concept) {
	    if ($concept['concept'] === 'participant') {
		if ($concept['method'] == 'create')
		    $created[] = $concept['id'];
		else if ($concept['method'] == 'update')
		    $updated[] = $concept['id'];
	    }
	    else if ($concept['concept'] === 'schedulable') { //Caso exista schedulable no commit antecipa o carregamento do owner		
		$schedulables[$concept['id']] = Controller::read(array('concept' => 'schedulable', 'id' => $concept['id']), false, array('deepness' => '2'));
		foreach ($schedulables[$concept['id']]['participants'] as $i => $v) //salva em $organizers as informações do organizador      
		    if (($v['isOrganizer'] === '1') && ($organizers[$concept['id']] = $v))
			break;
	    }else if ($concept['concept'] === 'schedulableToAttachment') {
		$relationAttachment = Controller::find(array('concept' => 'schedulableToAttachment'), false, array('filter' => array('=', 'id', $concept['id'])));



		foreach ($relationAttachment as $key => $value) {
		    if (!array_key_exists('attachments', $schedulables[$value['schedulable']]))
			$schedulables[$value['schedulable']]['attachments'] = array();

		    $temp = Controller::find(array('concept' => 'attachment'), false, array('filter' => array('=', 'id', $value['attachment'])));
		    array_push($schedulables[$value['schedulable']]['attachments'], $temp[0]);
		}
	    }
	}

	if (isset($created)) {
	    $psCreated = Controller::find(array('concept' => 'participant'), false, array('deepness' => '1', 'filter' => array('IN', 'id', $created)));
	    foreach ($psCreated as $i => $pCreated) {
		if ($pCreated['isOrganizer'] == '1' && $pCreated['delegatedFrom'] == '0')
		    continue; //escapa organizador

		$schedulable = isset($schedulables[$pCreated['schedulable']]) ? $schedulables[$pCreated['schedulable']] : Controller::read(array('concept' => 'schedulable', 'id' => $pCreated['schedulable']), false, array('deepness' => '2'));
		if (!self::futureEvent($schedulable['startTime'], $schedulable['rangeEnd'], $schedulable['id']))
		    continue; //Escapa eventos do passado

		$organizer = isset($organizers[$pCreated['schedulable']]) ? $organizers[$pCreated['schedulable']] : self::getOrganizer($pCreated['schedulable']);

		if ($pCreated['delegatedFrom'] != 0) {
		    self::participantDelegated($pCreated, $schedulable, $organizer);
		    continue;
		}

		switch ($pCreated['status']) {
		    case STATUS_CONFIRMED:
			self::participantStatusChange($pCreated['id'], $schedulable, $organizer, STATUS_ACCEPTED);
			break;
		    case STATUS_UNANSWERED:
			self::participantCreated($pCreated['id'], $schedulable, false, false, $organizer);
			break;
		}
	    }
	}

	if (isset($updated)) {

	    $psUpdated = Controller::find(array('concept' => 'participant'), false, array('deepness' => '1', 'filter' => array('IN', 'id', $updated)));


	    foreach ($psUpdated as $i => $pUpdated) {
		if ($pUpdated['isOrganizer'] == '1' && $pUpdated['delegatedFrom'] == '0'){
		    continue; //escapa organizador
        }

		$schedulable = isset($schedulables[$pUpdated['schedulable']]) ? $schedulables[$pUpdated['schedulable']] : Controller::read(array('concept' => 'schedulable', 'id' => $pUpdated['schedulable']), false, array('deepness' => '2'));
		if (!self::futureEvent($schedulable['startTime'], $schedulable['rangeEnd'], $schedulable['id']))
		    continue; //Escapa eventos do passado

		foreach ($schedulable['participants'] as $i => $v) //salva em $organizer as informações do organizador      
		    if (($v['isOrganizer'] === '1') && ($organizer = $v))
			break;

		if ($pUpdated['delegatedFrom'] != '0') {
		    self::participantDelegatedStatusChange($pUpdated, $schedulable, $organizer, $pUpdated['status']);
		} else if ($pUpdated['status'] != STATUS_UNANSWERED && $pUpdated['status'] != STATUS_DELEGATED)
		    self::participantStatusChange($pUpdated['id'], $schedulable, $organizer, $pUpdated['status']);
	    }
	}
    }

    public function formatSuggestion(&$params) {

	$schedulable = Controller::read(array('concept' => 'schedulable', 'id' => $params['schedulable']), null, array('deepness' => '2'));

	foreach ($schedulable['participants'] as $i => $v) //salva em $organizer as informações do organizador      
	    if (($v['isOrganizer'] === '1') && ($organizer = $v))
		break;

	$method = 'COUNTER';
	$notificationType = 'Sugestão de horário';
	$part = 'other';

	$schedulableReference = $schedulable;

	$referenceSuggestion = array('startTime' => strtotime($params['startTime'] . ' ' . $schedulable['timezone']) . '000',
	    'endTime' => strtotime($params['endTime'] . ' ' . $schedulable['timezone']) . '000',
	    'allDay' => $params['allDay']
	);

	$schedulable = array_merge($schedulable, $referenceSuggestion);

	self::mountStruture(false, $schedulable, false, $data, $subject, $ical, $part, $method, $notificationType);


	$timezone = new DateTimeZone('UTC');
	$sTime = new DateTime('@' . (int) ($schedulableReference['startTime'] / 1000), $timezone);
	$eTime = new DateTime('@' . (int) ($schedulableReference['endTime'] / 1000), $timezone);

	if (isset($schedulableReference['timezone'])) {
	    $sTime->setTimezone(new DateTimeZone($schedulableReference['timezone']));
	    $eTime->setTimezone(new DateTimeZone($schedulableReference['timezone']));
	}

	$data['nowStartDate'] = date_format($sTime, 'd/m/Y');
	$data['nowStartTime'] = ($schedulableReference['allDay']) ? '' : date_format($sTime, 'H:i');
	$data['nowEndDate'] = date_format($eTime, 'd/m/Y');
	$data['nowEndTime'] = ($schedulableReference['allDay']) ? '' : date_format($eTime, 'H:i');
	$data['userRequest'] = Config::me('uid');

	$ical2 = $ical;
	$ical2['type'] = 'text/calendar';
	$ical2['name'] = 'thunderbird.ics';
	$params['attachments'][] = $ical2;
	$params['attachments'][] = $ical;
	$params['isHtml'] = true;
	$params['body'] = parseTPL::load_tpl($data, ROOTPATH . '/modules/calendar/templates/notify_suggestion_body.tpl');
	$params['subject'] = parseTPL::load_tpl($subject, ROOTPATH . '/modules/calendar/templates/notify_subject.tpl');
	;
	$params['from'] = '"' . Config::me('cn') . '" <' . Config::me('mail') . '>';
	$params['to'] = $organizer['user']['mail'];
    }

    public function formatSuggestionResponse(&$params) {
	$schedulable = $params['schedulable'];
	foreach ($schedulable['participants'] as $i => $v) {//salva em $organizer as informações do organizador      
	    if ($v['isOrganizer'] === '1')
		$organizer = $v;
	    if ($v['user']['mail'] == Config::me('mail'))
		$me = $v;
	}
	$method = 'DECLINECOUNTER';
	$notificationType = 'Sugestão de horário';
	$part = 'other';

	$schedulable['participants'] = array();
	array_push($schedulable['participants'], $me, $organizer);

	self::mountStruture(false, $schedulable, false, $data, $subject, $ical, $part, $method, $notificationType);

	if ($params['status'] == 'DECLINECOUNTER')
	    $data['status'] = 'não pode ser aceito';
	$ical2 = $ical;
	$ical2['type'] = 'text/calendar';
	$ical2['name'] = 'thunderbird.ics';
	$params['attachments'][] = $ical2;
	$params['attachments'][] = $ical;
	$params['isHtml'] = true;
	$params['body'] = parseTPL::load_tpl($data, ROOTPATH . '/modules/calendar/templates/notify_suggestion_response_body.tpl');
	$params['subject'] = parseTPL::load_tpl($subject, ROOTPATH . '/modules/calendar/templates/notify_subject.tpl');
	;
	$params['to'] = $params['from'];
	$params['from'] = $params['from'] = '"' . Config::me('cn') . '" <' . Config::me('mail') . '>';
    }

    public static function _getAttendeeById($attendeId, $schedulable) {
	foreach ($schedulable['participants'] as $id => $dv)
	    if ($dv['id'] == $attendeId)
		return $dv;
    }

    public static function _getAttendeeOrganizer($schedulable) {
        foreach ($schedulable['participants'] as $v)
            if ($v['isOrganizer'] == '1')
                return $v;
    }

    /**
     * Prepara para criação de email de delegação
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Adriano Coutinho da Silva
     * @return     void
     * @access     public
     */
    public static function participantDelegated(&$partID, &$schedulable, &$organizer) {
        $delegatedParams = array();

        $delegatedFrom = self::_getAttendeeById($partID['delegatedFrom'], $schedulable);
        $delegatedParams['delegatedFrom'] = $delegatedFrom['user']['uid'];

        self::participantCreated($partID['id'], $schedulable, STATUS_DELEGATED, $delegatedParams);

        $delegatedTo = self::_getAttendeeById($partID['id'], $schedulable);
        $delegatedParams['delegated'] = $delegatedTo['user']['uid'];

        if($partID['isOrganizer'] == '0'){
            self::participantStatusChange($partID['delegatedFrom'], $schedulable, $organizer, STATUS_DELEGATED, $delegatedParams);
        }
    }

    /**
     * Monta o email de resposta que sera enviado ao delegatedFrom
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Cristiano Corrêa Schmidt
     * @return     void
     * @access     public
     */
    public static function participantDelegatedStatusChange(&$partID, &$schedulable, $organizer, &$type = false) {
        $delegatedParams = array();

        $delegated = self::_getAttendeeById($partID['id'], $schedulable);
        $delegatedParams['delegated'] = $delegated['user']['uid'];

        switch ($partID['status']) {
            case STATUS_ACCEPTED:
            $delegatedParams['status'] = 'aceitou';
            break;
            case STATUS_TENTATIVE:
            $delegatedParams['status'] = 'marcou como tentativa';
            break;
            case STATUS_CANCELLED:
            $delegatedParams['status'] = 'rejeitou';
            break;
            case STATUS_DELEGATED:
            $delegatedParams['status'] = 'delegou para um novo participante';
            break;
	    }
	    //notifica o organizador a resposta do delegado
	    if($partID['isOraganizer'] == '0')
            self::participantStatusChange($partID['delegatedFrom'], $schedulable, $organizer, $type, $delegatedParams);

        $method = 'REQUEST';
        $notificationType = 'Resposta Delegação';
        $part = 'attendees';
        self::mountStruture($partID['delegatedFrom'], $schedulable, $type, $data, $subject, $ical, $part, $method, $notificationType);

        $data = array_merge($data, $delegatedParams);

        self::sendMail($data, $ical, $part['user']['mail'], $subject, $schedulable['type'] == '1' ? 'notify_response_delegated_status_body' : 'notify_response_delegated_status_body_task');
    }

    public static function mountStruture(&$partID, &$schedulable, $type = false, &$data, &$subject, &$ical, &$part = false, &$method, &$notificationType, $regSet = false) {

	if ((Config::regGet('ical://' . $schedulable['id'] . '/' . $method) === false) || ($method == 'CANCEL')) { //Verifica se o ical ja não esta no reg
	    $schedulable['URI']['concept'] = 'schedulable';
	    $ical = Controller::format(array('service' => 'iCal'), array($schedulable), array('method' => $method , 'compatible' => true ));
	    if ($regSet)
		Config::regSet('ical://' . $schedulable['id'] . '/' . $method, $ical);
	}
	else
	    $ical = Config::regGet('ical://' . $schedulable['id'] . '/' . $method);

	if (!is_numeric($schedulable['endTime']))
	    $schedulable['startTime'] = self::parseTimeDate($schedulable['startTime'], $schedulable['timezone']);

	if (!is_numeric($schedulable['endTime'])) {
	    $schedulable['endTime'] = self::parseTimeDate($schedulable['endTime'], $schedulable['timezone']);

	    if ($schedulable['allDay'])
		    $schedulable['endTime'] = $schedulable['endTime'] + 86400000;
	}

	$timezone = new DateTimeZone('UTC');
	$sTime = new DateTime('@' . (int) ($schedulable['startTime'] / 1000), $timezone);
	$eTime = new DateTime('@' . (int) ($schedulable['endTime'] / 1000), $timezone);

	if (isset($schedulable['timezone'])) {
	    $sTime->setTimezone(new DateTimeZone($schedulable['timezone']));
	    $eTime->setTimezone(new DateTimeZone($schedulable['timezone']));
	}

	$data = array('startDate' => date_format($sTime, 'd/m/Y'),
	    'startTime' => ($schedulable['allDay']) ? '' : date_format($sTime, 'H:i'),
	    'endDate' => date_format($eTime, 'd/m/Y'),
	    'endTime' => ($schedulable['allDay']) ? '' : date_format($eTime, 'H:i'),
	    'eventTitle' => $schedulable['summary'],
	    'eventLocation' => $schedulable['location'],
	    'timezone' => ($schedulable['timezone']) ? $schedulable['timezone'] : 'UTC');
	$temp = $part;
	$part = false;

	switch ($temp) {
	    case 'attendees':
		$attList = '<UL> ';
		foreach ($schedulable['participants'] as $i => $v) {
		    if ($part === false && $v['id'] == $partID)
			$part = $v;

		    $attList .= ' <LI> ' . (isset($v['user']['name']) ? $v['user']['name'] : $v['user']['mail']);
		}
		$attList .= '</UL>';
		$data['participants'] = $attList;
		break;
	    case 'me':
		$part = self::_getAttendeeById($partID, $schedulable);
		$data['participant'] = isset($part['user']['name']) ? $part['user']['name'] : $part['user']['mail'];
		$partID = $part;
		break;
	    case 'othersAttendees':
		$data['participants'] = '<UL> ';
		foreach ($schedulable['participants'] as $ii => $participant) {
		    if ($participant['isOrganizer'] != '1')
			$part[] = $participant['user']['mail'];

		    $data['participants'] .= ' <LI> ' . (isset($participant['user']['name']) ? $participant['user']['name'] : $participant['user']['mail']);
		}
		break;
	}
	$subject['notificationType'] = $notificationType;
	$subject['eventTitle'] = mb_convert_encoding($schedulable['summary'], 'ISO-8859-1', 'ISO-8859-1,UTF-8');
	$subject['startDate'] = date_format($sTime, 'd/m/Y');
	$subject['startTime'] = ($schedulable['allDay']) ? '' : date_format($sTime, 'H:i');
	$subject['endDate'] = date_format($eTime, 'd/m/Y');
	$subject['endTime'] = ($schedulable['allDay']) ? '' : date_format($eTime, 'H:i');
	$subject['participant'] = (is_array($partID) && isset($partID['user']) )? $partID['user']['uid'] : Config::me('uid');
    }

    /**
     * Monta o email de convite que sera enviado ao participant
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Cristiano Corrêa Schmidt
     * @return     void
     * @access     public
     */
    public static function participantCreated(&$partID, &$schedulable, $type = false, $delegatedParams = false, $organizer = false) {
        $method = 'REQUEST';
        $notificationType = 'Convite de Calendario';
        $part = 'attendees';

        if($schedulable['type'] == '2'){
            $template = !$delegatedParams ? 'notify_create_body_task' : 'notify_create_delegated_body_task';
        }else{
            $template = !$delegatedParams ? 'notify_create_body' : 'notify_create_delegated_body';
        }

        self::mountStruture($partID, $schedulable, $type, $data, $subject, $ical, $part, $method, $notificationType, true);

        if ($delegatedParams)
            $data = array_merge($data, $delegatedParams);

        self::sendMail($data, $ical, $part['user']['mail'], $subject, $template, $organizer);
    }

    /**
     * Monta o email de aceito que sera enviado ao organizador 
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Cristiano Corrêa Schmidt
     * @return     void
     * @access     public
     */
    public static function participantStatusChange(&$partID, &$schedulable, &$organizer, $type, $delegatedParams = false) {	
	$method = 'REPLY';
	$notificationType = 'Convite Aceito';
	$part = 'me';

	self::mountStruture($partID, $schedulable, $type, $data, $subject, $ical, $part, $method, $notificationType, true);

	if ($delegatedParams) {
	    $data = array_merge($data, $delegatedParams);
	    $tplDelegated = $schedulable['type'] == '1' ? 'notify_delegated_status_body' : 'notify_delegated_status_body_task';
	}

	switch ($type) {
	    case STATUS_ACCEPTED:
		$tpl = $delegatedParams ? $tplDelegated : ($schedulable['type'] == '1' ? 'notify_accept_body' : 'notify_accept_body_task');
		$subject['notificationType'] = 'Convite Aceito';
		break;
	    case STATUS_TENTATIVE:
		$tpl = $delegatedParams ? $tplDelegated :($schedulable['type'] == '1' ? 'notify_attempt_body' : 'notify_attempt_body_task');
		$subject['notificationType'] = 'Convite  aceito provisoriamente';
		break;
	    case STATUS_CANCELLED:
		$tpl = $delegatedParams ? $tplDelegated :($schedulable['type'] == '1' ? 'notify_reject_body' : 'notify_reject_body_task');
		$subject['notificationType'] = 'Convite rejeitado';
		break;
	    case STATUS_DELEGATED:
		if ($delegatedParams)
		    $data = array_merge($data, $delegatedParams);
		$tpl = $schedulable['type'] == '1' ? 'notify_delegated_body' : 'notify_delegated_body_task';
		$subject['notificationType'] = 'Convite delegado';
		break;
	}

	self::sendMail($data, $ical, $organizer['user']['mail'], $subject, $tpl, $partID);
    }

    /**
     * Monta o body e envia o email 
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Cristiano Corrêa Schmidt
     * @return     void
     * @access     public
     */
    private static function sendMail(&$data, &$ical, $to, &$subject, $template, $from = false) {


    $ical1['type'] = 'text/plain';
    $ical1['source'] = $ical['compatible'];
    $ical1['name'] = 'outlook2003.ics';
    $ical2['source'] = $ical['ical'];
	$ical2['type'] = 'text/calendar';
	$ical2['name'] = 'calendar.ics';

	unset($ical);
	$mail['attachments'][] = $ical2;
	$mail['attachments'][] = $ical1;
	unset($ical1);
	unset($ical2);
	$mail['isHtml'] = true;
	$mail['body'] = parseTPL::load_tpl($data, ROOTPATH . '/modules/calendar/templates/' . $template . '.tpl');
	$mail['subject'] = parseTPL::load_tpl($subject, ROOTPATH . '/modules/calendar/templates/notify_subject.tpl');
	$mail['from'] = $from ? ('"' . $from['user']['name'] . '" <' . $from['user']['mail'] . '>') : ('"' . Config::me('cn') . '" <' . Config::me('mail') . '>');
	$mail['to'] = $to;
	Controller::create(array('service' => 'SMTP'), $mail);
    }

    /**
     * Monta o email de cancelado que sera enviado a todos os participantes 
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Cristiano Corrêa Schmidt
     * @return     void
     * @access     public
     */
    public function deleteEvent(&$uri, &$result, &$params, $original) {
	$schedulable = Controller::read(array('concept' => 'schedulable', 'id' => $uri['id']), null, array('deepness' => '2'));

	if ((Config::regGet('noAlarm') === false) && (self::futureEvent($schedulable['startTime'], $schedulable['rangeEnd'], $schedulable['id']))) {
	    $method = 'CANCEL';
	    $notificationType = 'Cancelamento de Calendario';
	    $part = 'othersAttendees';
	    self::mountStruture($uri['id'], $schedulable, false, $data, $subject, $ical, $part, $method, $notificationType);

	    if (count($part) > 0)
        {
            $from = false;
            foreach($schedulable['participants'] as $v)
            {
                if($v['isOrganizer'] == 1)
                    $from =  $v;
            }

            self::sendMail($data, $ical, implode(',', $part), $subject, 'notify_cancel_body' , $from );
        }
	}
    }

    /**
     * Monta o email de cancelado que sera enviado ao participant deleteado 
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Cristiano Corrêa Schmidt
     * @return     void
     * @access     public
     */
    public function deleteParticipant(&$uri, &$result, &$params, $original) {

	$participant = Controller::read(array('concept' => 'participant', 'id' => $uri['id']), null, array('deepness' => '1'));
    $schedulable = Controller::read(array('concept' => 'schedulable', 'id' => $participant['schedulable']) , false , array('deepness' => '2'));

	if ((Config::regGet('noAlarm') === false) && (self::futureEvent($schedulable['startTime'], $schedulable['rangeEnd'], $schedulable['id']))) {
	    $method = 'CANCEL';
	    $notificationType = 'Cancelamento de Calendario';
	    $part = 'me';
	    self::mountStruture($schedulable['id'], $schedulable, false, $data, $subject, $ical, $part, $method, $notificationType);

        $from = false;
        foreach($schedulable['participants'] as $v)
        {
            if($v['isOrganizer'] == 1)
                $from =  $v;
        }
 	    self::sendMail($data, $ical, $participant['user']['mail'], $subject, 'notify_cancel_body' , $from);
	}
    }

    /**
     * Faz um diff do update se ouve realmente uma alteração envia um email a todos os participants
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Cristiano Corrêa Schmidt
     * @return     void
     * @access     public
     */
    public function updateEvent(&$uri, $params, &$criteria, $original) {
	$schedulableOld = Controller::read(array('concept' => 'schedulable', 'id' => $uri['id']), null, array('deepness' => '2'));
	$schedulable = $schedulableOld;
	$alt = false;

	foreach ($params as $i => $v) //Verifica se ouve alteração no evento
	    if (isset($schedulableOld[$i]) && $schedulableOld[$i] != $v && $i != 'participants') {
    		$schedulable[$i] = $v;
    		$alt = true;
	    }

        if (($alt === true) && (Config::regGet('noAlarm') === false) && (self::futureEvent($schedulable['startTime'], $schedulable['rangeEnd'], $schedulable['id']))) {
            $method = 'REQUEST';
            $notificationType = 'Modificação de Calendario';
            $part = 'othersAttendees';
            self::mountStruture($uri['id'], $schedulable, false, $data, $subject, $ical, $part, $method, $notificationType);

            $from = self::_getAttendeeOrganizer($schedulable);

            if (isset($part) && $part && count($part) > 0)
                self::sendMail($data, $ical, implode(',', $part), $subject, $schedulableOld['type'] == '1' ?  'notify_modify_body' : 'notify_modify_body_task', $from);
        }
    }

    static private function parseTimeDate($time, $timezone) {
    	return strtotime($time . ' ' . $timezone) . '000';
    }

}

?>
