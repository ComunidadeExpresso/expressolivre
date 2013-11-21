<?php

class ExpressoVersionResource extends ExpressoAdapter {		

	public function get ($request) {
		return $this->post($request);
	}

	public function post($request){
		// to Receive POST Params (use $this->params)
 		parent::post($request); 		 
 		
		$result = array('expressoVersion' =>  $this->getExpressoVersion(),
						'apiVersion' => '1.0');
 		$this->setResult($result);

		//to Send Response (JSON RPC format)
		return $this->getResponse(); 		
	}

}
