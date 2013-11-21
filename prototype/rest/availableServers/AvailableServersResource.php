<?php
/**
 * @uri /AvailableServers
 */
class AvailableServersResource extends Resource {

	function post($request){
		return $this->get($request);
	}
	
	function get($request){
		$error = null;		
		parse_str($request->data, &$data);
				
		if( file_exists(__DIR__ . '/../../config/REST.ini') )
		{
			$restServers = parse_ini_file( __DIR__ . '/../../config/REST.ini', true );
			
			foreach( $restServers as $key => $value)
			{
				if(substr( $key, 0, 11 ) == "ServersRest")
				{
					$servers[] = $value;
				}
			}
		}
		else{
			$error = array("code" => "001", "message" => "The servers list was not found.");
		}

		function cmp($a, $b)
		{
			return strcmp(strtolower($a["serverName"]), strtolower($b["serverName"]));
		}
	
		if(count($servers) > 0){
			usort($servers, "cmp");
		}

		$response = new Response($request);
		$response->code = Response::OK;
		$response->addHeader('content-type', 'application/json');
				
		$body = array();
		
		if($data['id']){
			$body['id'] = $data['id']; 
		}
		if($servers){
			$body['result'] = array( "servers" => $servers);
		}
		elseif($error){
			$body['error'] = $error;
		}
		else{
			$body['error'] = "OBJETO SEM RESULT E SEM ERRO REPORTADO.";
		}
						
		$response->body = json_encode($body);
			
		return $response;
	}		

}