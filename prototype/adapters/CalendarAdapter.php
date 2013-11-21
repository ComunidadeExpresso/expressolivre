<?php

class CalendarAdapter extends ExpressoAdapter {
	public function __construct($id){
		parent::__construct($id);
	}

	protected function getUserId(){
		return $GLOBALS['phpgw_info']['user']['account_id'];
	}

	protected function getDb(){
		return $GLOBALS['phpgw']->db;
	}

	protected function getTimezoneOffset(){
		return $GLOBALS['phpgw']->datetime->tz_offset;
	}
}
