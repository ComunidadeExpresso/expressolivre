<?php

class LoginResource extends ExpressoAdapter {

	private function getUserProfile(){
		if($this->getExpressoVersion() != "2.2") {

			$_SESSION['wallet']['user']['uidNumber'] = $GLOBALS['phpgw_info']['user']['account_id'];
			$_SESSION['wallet']['user']['uid'] = $GLOBALS['phpgw_info']['user']['account_lid'];
			$_SESSION['wallet']['user']['password'] = $GLOBALS['phpgw_info']['user']['password'];
			$_SESSION['wallet']['user']['cn'] = $GLOBALS['phpgw_info']['user']['fullname'];
			$_SESSION['wallet']['user']['mail'] = $GLOBALS['phpgw_info']['user']['email'];

		}
	
		return array(
				'contactID'			=> $GLOBALS['phpgw_info']['user']['account_dn'],
				'contactMails' 		=> array($GLOBALS['phpgw_info']['user']['email']),
				'contactPhones' 	=> array($GLOBALS['phpgw_info']['user']['telephonenumber']),
				'contactFullName'	=> $GLOBALS['phpgw_info']['user']['fullname'],
				'contactApps'		=> $this->getUserApps()
		);
	}
	
	private function getUserApps(){
		// Load Granted Apps for Web Service
		$config = parse_ini_file( __DIR__ . '/../../config/user.ini',true);
		$apps 	= $config['Applications.mapping'];
	
		// Load Granted Apps for User
		$contactApps = array();
		$acl 	= CreateObject('phpgwapi.acl');
		$user_id = $GLOBALS['phpgw_info']['user']['account_id']['acl'];
		foreach($acl->get_user_applications($user_id) as $app => $value){
			$enabledApp = array_search($app, $apps);
			if($enabledApp !== FALSE)
				$contactApps[] = $enabledApp;
		}
	
		return $contactApps;
	}
	
	public function post($request){
		// to Receive POST Params (use $this->params)
 		parent::post($request);
		if($sessionid = $GLOBALS['phpgw']->session->create($this->getParam('user'), $this->getParam('password')))
		{
			$result = array(
				'auth' 			=> $sessionid.":".$GLOBALS['phpgw']->session->kp3,
				'profile' 		=> array($this->getUserProfile())
			);

			$this->setResult($result);
		}
		else
		{
			Errors::runException($GLOBALS['phpgw']->session->cd_reason);
		}
		return $this->getResponse();
	}	

}