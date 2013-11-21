<?php

class Item {

    public $href;
    public $dataType;
    public $data = array();
    public $links = array();

    function __construct($config, $className, $id) {
	foreach ($config as $key => $value) {
	    if ($value['class'] == $className) {
		//TODO - Verificar expressão regular
		$uri = preg_replace('/\/[:][a-zA-Z-0-9]+/', '', $key);
		break;
	    }
	}
	$this->href = $uri .'/'. $id;
	$this->dataType = $uri;
    }
    
    function setDataType($dataType) {
	$this->dataType = $dataType;
    }

    function getDataType() {
	return $this->dataType;
    }

    function setHref($href) {
	$this->href = $href;
    }

    function getHref() {
	return $this->href;
    }

    function addData($data) {
	$this->data[] = $data;
    }

    function addLink($link) {
	$this->links[] = $link;
    }

}

?>  
