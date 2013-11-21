<?php

class Data {

    public $name;
    public $value;
    public $prompt;
    public $dataType;
    public $maxLength;
    public $minLength;
    public $required;

    function setName($name) {
	$this->name = $name;
    }

    function getName() {
	return $this->name;
    }

    function setValue($value) {
	$this->value = $value;
    }

    function getVame() {
	return $this->value;
    }

    function setPrompt($prompt) {
	$this->prompt = $prompt;
    }

    function getPrompt() {
	return $this->prompt;
    }

    function setDataType($dataType) {
	$this->dataType = $dataType;
    }

    function getDataType() {
	return $this->dataType;
    }

    function setMaxLength($maxLength) {
	$this->maxLength = $maxLength;
    }

    function getMaxLength() {
	return $this->maxLength;
    }

    function setMinLength($minLength) {
	$this->minLength = $minLength;
    }

    function getMinLength() {
	return $this->minLength;
    }

    function setRequired($required) {
	$this->required = $required;
    }

    function getRequired() {
	return $this->required;
    }

}

?>