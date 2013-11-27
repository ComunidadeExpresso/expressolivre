<?php
class ExpressoAdapter extends Resource {

	private $cannotModifyHeader;
	private $expressoVersion;
	private $request;
	private $params;
	private $result;
	private $error;	
	private $id;
	
	function __construct($id){
		$GLOBALS['phpgw_info'] = array(
				'flags' => array(
						'currentapp'        	=> 'login',
						'noheader'              => True,
						'disable_Template_class' => True
				)
		);
		
		include_once(__DIR__.'/../../header.inc.php');
		$this->expressoVersion = substr($GLOBALS['phpgw_info']['server']['versions']['phpgwapi'],0,3);
		$this->setCannotModifyHeader(false);
	}
	
	protected function setRequest($request){
		$this->request = $request;
	}
	
	public function getRequest(){
		return $this->request;
	}
	
	protected function getExpressoVersion(){
		return $this->expressoVersion;
	}
	
	protected function setResult($result){
		$this->result = $result;
	}
	
	public function getResult(){
		return $this->result;
	}
	
	protected function setId($id){
		$this->id = $id;
	}
	
	public function getId(){
		return $this->id;
	}
	
	protected function setParams($params){		
		$this->params = $params;
	}
	
	public function getParams(){	
		return $this->params;
	}
	
	public function getParam($param){
		return mb_convert_encoding($this->params->$param, "ISO_8859-1", "UTF8");
	}
	
	public function setError($error) {
		$this-> error = $error;
	}
	
	protected function getError() {
		return $this-> error;
	}
	
	protected function setCannotModifyHeader($boolean){
		$this-> cannotModifyHeader = $boolean;
	}
	protected function getCannotModifyHeader(){
		return $this-> cannotModifyHeader;
	}

	public function post($request){
		if(!$request->data)
			$request->data = $_POST;
		$this->setRequest($request);		
		if(!is_array($request->data))
			parse_str(urldecode($request->data), $request->data);
		$data = (object)$request->data;		
		if($data){
			if($data->params){								
				$this->setParams(json_decode($data->params));
			}
			if($data->id)
				$this->setId($data->id);
		}
	}	
	
	public function get($request){
		$response = new Response($request);
		$response->code = Response::OK;
		$response->addHeader('content-type', 'text/html');		
		$response->body = "<H4>Metodo GET nao permitido para este recurso.</H4>";		
		return $response;
	}
	
	public function getResponse(){
		$response = new Response($this->getRequest());
		
		if($this->getCannotModifyHeader())
			return $response;
		
		$response->code = Response::OK;
		$response->addHeader('content-type', 'application/json');

		if($this->getId())
			$body['id']	= $this->getId();
		if($this->getResult())
			$body['result']	= $this->getResult();
		else {
			Errors::runException("E_UNKNOWN_ERROR");			
		}
		
		
		$response->body = json_encode($body);
		
		return $response;
	}
	
	protected function isLoggedIn(){
		if($this->getParam('auth') != null) {
			list($sessionid, $kp3) = explode(":", $this->getParam('auth'));
			if(!$GLOBALS['phpgw']->session->verify() && $GLOBALS['phpgw']->session->verify($sessionid, $kp3)){									
				return $sessionid;
			}
			else{
				Errors::runException("LOGIN_AUTH_INVALID");							
			}
		}
		elseif($sessionid = $GLOBALS['_COOKIE']['sessionid']) {
			if($GLOBALS['phpgw']->session->verify($sessionid)) {
				return $sessionid;
			}
			else{
				Errors::runException("LOGIN_NOT_LOGGED_IN");
			}
		}
		else{
			Errors::runException("LOGIN_NOT_LOGGED_IN");			
		}		
	}	
			
}