<?php

class EventsResource extends CalendarAdapter {
	public function post($request){
		// to Receive POST Params (use $this->params)
 		parent::post($request);

 		$user_id     = $this->getUserId();
		$tz_offset   = $this->getTimezoneOffset();

		if($this-> isLoggedIn()) {

			$date_start  = $this->getParam('dateStart');
			$date_end    = $this->getParam('dateEnd');


			// check the dates parameters formats (ex: 31/12/2012 23:59:59, but the time is optional)
			$regex_date  = '/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/([12][0-9]{3})( ([01][0-9]|2[0-3])(:[0-5][0-9]){2})?$/';

			if(!preg_match($regex_date, $date_start))
				Errors::runException("CALENDAR_INVALID_START_DATE");

			if(!preg_match($regex_date, $date_end))
				Errors::runException("CALENDAR_INVALID_END_DATE");


			// get the start timestamp UNIX from the parameter
			$start_arr      = explode(' ', $date_start);
			$start_date_arr = explode('/', $start_arr[0]);
			$start_time_arr = !empty($start_arr[1]) ? explode(':', $start_arr[1]) : array('00', '00', '00');
			$rangeStart       = mktime($start_time_arr[0],$start_time_arr[1],$start_time_arr[2],$start_date_arr[1],$start_date_arr[0],$start_date_arr[2]) - ($tz_offset);

			// get the end timestamp UNIX from the parameter
			$end_arr        = explode(' ', $date_end);
			$end_date_arr   = explode('/', $end_arr[0]);
			$end_time_arr   = !empty($end_arr[1]) ? explode(':', $end_arr[1]) : array('23', '59', '59');
			$rangeEnd      = mktime($end_time_arr[0],$end_time_arr[1],$end_time_arr[2],$end_date_arr[1],$end_date_arr[0],$end_date_arr[2]) - ($tz_offset);

			$rangeStart = $rangeStart * 1000;
			$rangeEnd = $rangeEnd * 1000;

			$concept = "schedulable";
			$id = false;

			$criteria = array();
			$criteria['order'] = "startTime";
			$criteria['deepness'] = 2;

			$criteria['timezones'] = array();
			$criteria['timezones'][1] = 'America/Sao_Paulo';
			$criteria['timezones'][3] = 'America/Sao_Paulo';


			$criteria['filter'] = array();
			$criteria['filter'][0] = "AND";
			$criteria['filter'][1] = array();
			$criteria['filter'][1][0] = "OR";


			$criteria['filter'][1][1] = array();
			$criteria['filter'][1][1][0] = "AND";
			$criteria['filter'][1][1][1] = array();
			$criteria['filter'][1][1][1][0] = ">=";
			$criteria['filter'][1][1][1][1] = "rangeEnd";
			$criteria['filter'][1][1][1][2] = $rangeStart; //START
			$criteria['filter'][1][1][2] = array();
			$criteria['filter'][1][1][2][0] = "=<";
			$criteria['filter'][1][1][2][1] = "rangeEnd";
			$criteria['filter'][1][1][2][2] = $rangeEnd; //END


			$criteria['filter'][1][2] = array();
			$criteria['filter'][1][2][0] = "AND";
			$criteria['filter'][1][2][1] = array();
			$criteria['filter'][1][2][1][0] = ">=";
			$criteria['filter'][1][2][1][1] = "rangeStart";
			$criteria['filter'][1][2][1][2] = $rangeStart; //START
			$criteria['filter'][1][2][2] = array();
			$criteria['filter'][1][2][2][0] = "=<";
			$criteria['filter'][1][2][2][1] = "rangeStart";
			$criteria['filter'][1][2][2][2] = $rangeEnd; //END


			$criteria['filter'][1][3] = array();
			$criteria['filter'][1][3][0] = "AND";
			$criteria['filter'][1][3][1] = array();
			$criteria['filter'][1][3][1][0] = "<=";
			$criteria['filter'][1][3][1][1] = "rangeStart";
			$criteria['filter'][1][3][1][2] = $rangeStart; //START
			$criteria['filter'][1][3][2] = array();
			$criteria['filter'][1][3][2][0] = ">=";
			$criteria['filter'][1][3][2][1] = "rangeEnd";
			$criteria['filter'][1][3][2][2] = $rangeEnd; //END

			$criteria['filter'][2] = array("IN","calendar",array(1));

			$properties = ( $criteria && isset( $criteria['properties'] ) )? $criteria['properties']: false;
			$service = ( $criteria && isset( $criteria['service'] ) )? $criteria['service']: false;


			$res = Controller::call( 'find', Controller::URI( $concept ),false,$criteria );

			$arrEvents = array();
			foreach ($res as $event) {


				$timeZone = new DateTimeZone($event['timezone']);
				$timeStart = new DateTime('@' . (int) ( $event['startTime'] / 1000 ), $timeZone);
				$timeEnd = new DateTime('@' . (int) ( $event['endTime'] / 1000 ), $timeZone);

				$timeStart->setTimezone($timeZone);
				$timeEnd->setTimezone($timeZone);

				$newEvent = array();
				$newEvent['eventID'] =  "" . $event['id'];
				$newEvent['eventName'] = "" .$event['summary'];
				$newEvent['eventDescription'] = "" .$event['description'];
				$newEvent['eventLocation'] = "" . $event['location'];
				$newEvent['eventStartDate'] = "" . $timeStart->format('d/m/Y H:i:s');
				$newEvent['eventEndDate'] = "" . $timeEnd->format('d/m/Y H:i:s');
				//$newEvent['eventTimeZone'] = "" . $event['timezone'];
				$newEvent['eventAllDay'] = "" . $event['allDay']; 

				$arrEvents[] = $newEvent;
			}

			$result = array ('events' => $arrEvents); 

			$this->setResult($result); 


		}
		//to Send Response (JSON RPC format)
		return $this->getResponse();
	}

}
