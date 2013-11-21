<?php

class Error {

    public $code;
    public $description;
    public $title;

    public function setCode($code) {
	$this->code = $code;
    }

    public function setDescription($description) {
	$this->description = $description;
    }

    public function setTitle($title) {
	$this->title = $title;
    }

    public function getCode() {
	return $this->code;
    }

    public function getDescription() {
	return $this->description;
    }

    public function getTitle() {
	return $this->title;
    }

}

?>