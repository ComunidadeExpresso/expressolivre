<?php
// load Tonic library
require_once(__DIR__ . '/../library/tonic/lib/tonic.php');
require_once(__DIR__.'/../library/utils/Errors.php');
require_once(__DIR__ . '/../api/controller.php');
require_once(ROOTPATH . '/rest/oauth/OAuth2StorageUserCredential.php');

// load adapters
require_once(__DIR__."/../adapters/ExpressoAdapter.php");
require_once(__DIR__."/../adapters/MailAdapter.php");
require_once(__DIR__."/../adapters/CatalogAdapter.php");
require_once(__DIR__."/../adapters/CalendarAdapter.php");

//Retrieveing the mapping of the URIs and his respectives classNames and classPath
$config = parse_ini_file( __DIR__ . '/../config/Tonic.srv', true );

//looping through the mapping to create 2 separated maps:
// First indexed by the uri that carry the classNames, 
// that its used by Tonic to autoload them when routed by him accordingly;
// Second indexed by the className that carry the classPaths,
// used by the autoload register to find the correct path of the class that's going to
// be loaded;

$autoload = array();
$classpath = array();

foreach( $config as $uri => $classFile )
{
    foreach( $classFile as $className => $filePath )
    {
		$autoload[ $uri ] = $className;
		$classpath[ $className ] = $filePath;
    }
}

//The autoload function that's called by the PHP when Tonic routes a class not declared previously
function __autoload($class) {

	global $classpath;

	if(isset($classpath[ $class ])){
		require_once(__DIR__ . $classpath[ $class ] );
	}
}

// handle request, passing the current env baseUri and autoload mapping;

$restConf = parse_ini_file( __DIR__ . '/../config/REST.ini', true );
$request = new Request(array(
	'baseUri'=> $restConf['baseUri'],
	'autoload' => $autoload,
));

try {
    $resource = $request->loadResource();
    $response = $resource->exec($request);

} catch (ResponseException $e) {
    switch ($e->getCode())
    {
	    case Response::UNAUTHORIZED:
	        $response = $e->response($request);
	        $response->addHeader('WWW-Authenticate', 'Basic realm="Tonic"');
	        break;
	    
		default:
			$response = new Response($request);
			$response->code = Response::OK;
			$response->addHeader('content-type', 'application/json');
			if($request->id)
			{
				$body['id']	= $request->id;
			}
			$body['error'] = array("code" => "".$e->getCode(), "message" => $e->getMessage());
			
			$response->body = json_encode($body);
			
			//$response = $e->response($request);
    }
}

$response->output();

?>