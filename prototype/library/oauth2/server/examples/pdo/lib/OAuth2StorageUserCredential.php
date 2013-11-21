<?php

/**
 * @file
 * Sample OAuth2 Library PDO DB Implementation.
 * 
 * Simply pass in a configured PDO class, eg:
 * new OAuth2StoragePDO( new PDO('mysql:dbname=mydb;host=localhost', 'user', 'pass') );
 */
//session_start();
//require __DIR__ . '/../../../../lib/OAuth2.php';
///require __DIR__ . '/../../../../lib/IOAuth2Storage.php';
//require __DIR__ . '/../../../../lib/IOAuth2GrantCode.php';
//require __DIR__ . '/../../../../lib/IOAuth2RefreshTokens.php';
require __DIR__ . '/../../../../lib/IOAuth2GrantUser.php';

/**
 * PDO storage engine for the OAuth2 Library.
 * 
 * IMPORTANT: This is provided as an example only. In production you may implement
 * a client-specific salt in the OAuth2StoragePDO::hash() and possibly other goodies.
 * 
 *** The point is, use this as an EXAMPLE ONLY. ***
 */
class OAuth2StorageUserCredential implements IOAuth2GrantUser, IOAuth2RefreshTokens {

	private $valid_client_id = 666;

	public function checkUserCredentials($client_id, $username, $password) {
		if( $client_id == $this->valid_client_id && $username == 'adirkuhn' && $password == '666' ) {
			//return true; 
			return array('scope' => 'all'); 
		}
		else {
			return false;
		}
	}

	public function checkClientCredentials($client_id, $client_secret = NULL) {
	}

	public function getClientDetails($client_id) {
	}

	public function getAccessToken($oauth_token) {
	}

	
	public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = NULL) {
		var_dump(func_get_args());
	}

	
	public function checkRestrictedGrantType($client_id, $grant_type) {
		if($client_id == $this->valid_client_id	&& $grant_type == 'password') {
			return true;
		}
		else {
			return false;
		}
	}


	public function getRefreshToken($refresh_token) {
	}

	
	public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = NULL) {
	}


	public function unsetRefreshToken($refresh_token) {
	}

}


