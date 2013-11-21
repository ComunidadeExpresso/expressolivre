<?php
ini_set('display_errors', 'on');
/**
 * @file
 * Sample token endpoint.
 *
 * Obviously not production-ready code, just simple and to the point.
 *
 * In reality, you'd probably use a nifty framework to handle most of the crud for you.
 */

require "lib/OAuth2StoragePdo.php";
require "lib/OAuth2StorageUserCredential.php";

if(isset($_GET['grant_type']) && $_GET['grant_type'] === 'password') {
	$oauth = new OAuth2(new Oauth2StorageUserCredential());
}
else {
	$oauth = new OAuth2(new OAuth2StoragePDO());
}

try {
	$oauth->grantAccessToken();
} catch (OAuth2ServerException $oauthError) {
	$oauthError->sendHttpResponse();
}
