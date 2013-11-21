<?php

/*
 * @uri /token
 */

require_once dirname(__FILE__).'/../../api/controller.php';

require_once 'OAuth2StorageUserCredential.php';

class TokenResource extends Resource {

	public function post($request) {
		$res = new Response($request);

		try {
			$oauth = new OAuth2(new Oauth2StorageUserCredential());
			$oauth->grantAccessToken($_POST);
		} 
		catch (OAuth2ServerException $oauthError) {
			$oauthError->sendHttpResponse();
		}
		
		return $res;
	}

}

?>
