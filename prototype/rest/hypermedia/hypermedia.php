<?php

require_once('collection.php');

class Hypermedia {

    public $collection;
    public $version = '0.1';

    function setCollection($collection) {
	$this->collection = $collection;
    }

    function getCollection() {
	return $this->collection;
    }

    function toUtf8($data) {

	if (!is_array($data))
	    return  is_string($data) ? mb_convert_encoding($data, 'UTF-8', 'UTF-8 , ISO-8859-1') : $data;

	$return = array();

	foreach ($data as $i => &$v){
	    if(is_object($v))
		$v = get_object_vars($v);
	    $return[$this->toUtf8($i)] = $this->toUtf8($v);
	}
	return $return;
    }

    function generateValidXmlFromObj(stdClass $obj, $node_block = 'nodes', $node_name = 'node') {
	$arr = get_object_vars($obj);
	return self::generateValidXmlFromArray($arr, $node_block, $node_name);
    }

    function generateValidXmlFromArray($array, $node_block = 'nodes', $node_name = 'node') {
	$xml = '<?xml version="1.0" encoding="UTF-8" ?>';

	$xml .= '<' . $node_block . '>';
	$xml .= self::generateXmlFromArray($array, $node_name);
	$xml .= '</' . $node_block . '>';

	return $xml;
    }

    function generateXmlFromArray($array, $node_name) {
	$xml = '';

	if (is_array($array) || is_object($array)) {
	    foreach ($array as $key => $value) {
		if (is_numeric($key)) {
		    $key = $node_name;
		}

		$xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $node_name) . '</' . $key . '>';
	    }
	} else {
	    $xml = htmlspecialchars($array, ENT_QUOTES);
	}

	return $xml;
    }

    function getHypermedia($accept = 'json') {

	$data = $this->toUtf8(get_object_vars($this));

	switch ($accept) {
	    case 'json':
		return json_encode($data);
		break;
	    case 'xml':
		return $this->generateValidXmlFromArray($data);
	    default :
		return json_encode($data);
	}
    }

}

?>
