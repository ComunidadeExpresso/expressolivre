<?php
require_once  (dirname(__FILE__).'/api/controller.php');
use prototype\api\Config as Config;

$me = Controller::read(array('concept' => 'user', 'service' => 'OpenLDAP'  , 'id' => Config::me('uidNumber')));

echo json_encode( $me );
