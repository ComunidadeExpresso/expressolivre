<?php

/*
 * Remove and Update by criteria 
 */

$data = $_POST;

require_once "api/controller.php";

Controller::addFallbackHandler(0, function($e) {
	    throw $e;
	});

$result = array();
foreach ($data as $concept => &$content) {
    if (!is_array($content))
	$content = array($content);

    foreach ($content as $key => $value) {
	$criteria = isset($value['filter']) ? isset($value['criteria']) ?
			array_merge($value['criteria'], array('filter' => $value['filter'])) :
			array('filter' => $value['filter']) :
		$value['criteria'];

	$service = ( isset($value['criteria']) && isset($value['criteria']['service']) ) ? $value['criteria']['service'] : false;

	try {
	    $result[$concept][] = Controller::call($value['method'], Controller::URI($concept, false, $service), false, $criteria);
	} catch (Exception $e) {
	    $result[$concept]['error'] = $e->getMessage();
	}
    }
}
echo json_encode($result);