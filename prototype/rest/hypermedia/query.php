<?php

class Querie {
	public $href;
	public $data = array();

	public function getHref(){
		return $this->href;
	}

	public function setHref($href){
		$this->href = $href;
	}

	public function setData($field, $value, $required=false){
		$this->data[] = array(
					'name' => $field,
					'value' => $value,
					'required' => $required
		);
	}
    
}

?>
