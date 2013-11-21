<?php

require ROOTPATH . '/library/oauth2/lib/OAuth2.php';
require ROOTPATH . '/library/oauth2/lib/IOAuth2Storage.php';
require ROOTPATH . '/library/oauth2/lib/IOAuth2GrantCode.php';
require ROOTPATH . '/library/oauth2/lib/IOAuth2RefreshTokens.php';

require ROOTPATH . '/library/oauth2/lib/IOAuth2GrantUser.php';


class OAuth2StorageUserCredential implements IOAuth2GrantUser, IOAuth2RefreshTokens {

	public function checkUserCredentials($client_id, $username, $password) {
	    
		//Authentica no LDAP
		$usuario =  Controller::find(array('concept'=>'user' , 'service' => 'OpenLDAP'), false , array('filter' => array('AND', array('=' , 'uid' , $username ), array('=' , 'password', '{md5}' . base64_encode(pack("H*",md5($password))))  ) ));

		if(isset($usuario[0]['id']))
		{
		    return array('scope' => 'all' , 'user_id' => $usuario[0]['id']);
		}

		return false;
	}
	
	/**
	 * Make sure that the client credentials is valid.
	 * 
	 * @param $client_id
	 * Client identifier to be check with.
	 * @param $client_secret
	 * (optional) If a secret is required, check that they've given the right one.
	 *
	 * @return
	 * TRUE if the client credentials are valid, and MUST return FALSE if it isn't.
	 * @endcode
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-3.1
	 *
	 * @ingroup oauth2_section_3
	 */
	public function checkClientCredentials($client_id, $client_secret = NULL) {
		if($client_secret == NULL) {
			return false;
		}

		$res =  Controller::find(array('concept' => 'oauthCliente'), array('client_secret') , array('filter' => array('=' , 'client_id' , $client_id)) );

		if(isset($res[0]['client_secret']) && $res[0]['client_secret'] == $client_secret) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Get client details corresponding client_id.
	 *
	 * OAuth says we should store request URIs for each registered client.
	 * Implement this function to grab the stored URI for a given client id.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 *
	 * @return array
	 * Client details. Only mandatory item is the "registered redirect URI", and MUST
	 * return FALSE if the given client does not exist or is invalid.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function getClientDetails($client_id) {
		$res =  Controller::find(array('concept' => 'oauthCliente'), array('redirect_uri') , array('filter' => array('=' , 'client_id' , $client_id)));
		if(isset($res[0]['redirect_uri'])) {
			return $res[0]['redirect_uri'];
		}
		else {
			return false;
		}
	}

	/**
	 * Look up the supplied oauth_token from storage.
	 *
	 * We need to retrieve access token data as we create and verify tokens.
	 *
	 * @param $oauth_token
	 * oauth_token to be check with.
	 *
	 * @return
	 * An associative array as below, and return NULL if the supplied oauth_token
	 * is invalid:
	 * - client_id: Stored client identifier.
	 * - expires: Stored expiration in unix timestamp.
	 * - scope: (optional) Stored scope values in space-separated string.
	 *
	 * @ingroup oauth2_section_7
	 */
	public function getAccessToken($oauth_token) {
		return $this->getToken($oauth_token, false);
	}

	/**
	 * Take the provided refresh token values and store them somewhere.
	 *
	 * This function should be the storage counterpart to getRefreshToken().
	 *
	 * If storage fails for some reason, we're not currently checking for
	 * any sort of success/failure, so you should bail out of the script
	 * and provide a descriptive fail message.
	 *
	 * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
	 *
	 * @param $refresh_token
	 * Refresh token to be stored.
	 * @param $client_id
	 * Client identifier to be stored.
	 * @param $expires
	 * expires to be stored.
	 * @param $scope
	 * (optional) Scopes to be stored in space-separated string.
	 *
	 * @ingroup oauth2_section_6
	 */
	public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = NULL) {
		
		$data = array();
		$data['refresh_token']  = $refresh_token;
		$data['client_id'] = $client_id;
		$data['user_id'] = $user_id;
		$data['expires'] = $expires;
		$data['scope'] = $scope;

	        Controller::create(array('concept' => 'oauthRefreshToken'), $data);
	}
	
	/**
	 * Store the supplied access token values to storage.
	 *
	 * We need to store access token data as we create and verify tokens.
	 *
	 * @param $oauth_token
	 * oauth_token to be stored.
	 * @param $client_id
	 * Client identifier to be stored.
	 * @param $user_id
	 * User identifier to be stored.
	 * @param $expires
	 * Expiration to be stored.
	 * @param $scope
	 * (optional) Scopes to be stored in space-separated string.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = NULL, $refresh_token) {
	   
		$data = array();
		$data['oauth_token']  = $oauth_token;
		$data['client_id'] = $client_id;
		$data['user_id'] = $user_id;
		$data['expires'] = $expires;
		$data['scope'] = $scope;
		$data['refresh_token'] = $refresh_token;
		
		Controller::create(array('concept' => 'oauthToken'), $data);

	}

	/**
	 * Check restricted grant types of corresponding client identifier.
	 *
	 * If you want to restrict clients to certain grant types, override this
	 * function.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 * @param $grant_type
	 * Grant type to be check with, would be one of the values contained in
	 * OAuth2::GRANT_TYPE_REGEXP.
	 *
	 * @return
	 * TRUE if the grant type is supported by this client identifier, and
	 * FALSE if it isn't.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function checkRestrictedGrantType($client_id, $grant_type) {
		/*TODO: essa função deve verificar se o cliente tem permissao para realizar o login via 'password'
		 * deixe para implementar no prox. sprint, quando trataremos de seguranca. Deve-se criar um campo extra no banco para guardar quais tipos
		 * de grant_type o cliente esta autorizado a fazer
		*/

		return true;		
	}

	/**
	 * Grant refresh access tokens.
	 *
	 * Retrieve the stored data for the given refresh token.
	 *
	 * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
	 *
	 * @param $refresh_token
	 * Refresh token to be check with.
	 *
	 * @return
	 * An associative array as below, and NULL if the refresh_token is
	 * invalid:
	 * - client_id: Stored client identifier.
	 * - expires: Stored expiration unix timestamp.
	 * - scope: (optional) Stored scope values in space-separated string.
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-6
	 *
	 * @ingroup oauth2_section_6
	 */
	public function getRefreshToken($refresh_token) {
		return $this->getToken($refresh_token, true);
	}

	/**
	 * Expire a used refresh token.
	 *
	 * This is not explicitly required in the spec, but is almost implied.
	 * After granting a new refresh token, the old one is no longer useful and
	 * so should be forcibly expired in the data store so it can't be used again.
	 *
	 * If storage fails for some reason, we're not currently checking for
	 * any sort of success/failure, so you should bail out of the script
	 * and provide a descriptive fail message.
	 *
	 * @param $refresh_token
	 * Refresh token to be expirse.
	 *
	 * @ingroup oauth2_section_6
	 */
	public function unsetRefreshToken($refresh_token) {
		Controller::delete(
			array('concept' => 'oauthRefreshToken'),
			false,
			array('filter' => array('=', 'refresh_token', $refresh_token ))
		);
	}
	public function unsetAccessToken($refresh_token) {
		Controller::delete(
			array('concept' => 'oauthToken'),
			false,
			array('filter' => array('=', 'oauth_token', $refresh_token ))
		);
	}

	/**
	 * Retrieves an access or refresh token.
	 * 
	 * @param string $token
	 * @param bool $refresh
	 */
	protected function getToken($token, $isRefresh = true) {

		$tokenConcept = $isRefresh ? 'oauthRefreshToken' : 'oauthToken';
		$filter =  $isRefresh ? 'refresh_token' : 'oauth_token';
		
		$res = Controller::find(
			array('concept' => $tokenConcept), 
			false, 
			array('filter' => array('=' , $filter , $token))
		);

		/*return (!empty($res[0]))? $res[0] : false;*/
		if(!empty($res[0])) {
			return $res[0];
		}
		else {
			return false;
		}
	}
}


