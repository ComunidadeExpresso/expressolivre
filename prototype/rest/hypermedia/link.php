<?php

class Link {

    public $href;
    public $rel;
    public $alt;
    public $prompt;
    public $render;

    function setHref($href) {
	$this->href = $href;
    }

    function getHref() {
	return $this->href;
    }

    function setRel($rel) {
	$this->rel = $rel;
    }

    function getRel() {
	return $this->rel;
    }

    function setAlt($alt) {
	$this->alt = $alt;
    }

    function getAlt() {
	return $this->alt;
    }

    function setPrompt($prompt) {
	$this->prompt = $prompt;
    }

    function getPrompt() {
	return $this->prompt;
    }

    function setRender($render) {
	$this->render = $render;
    }

    function getRender() {
	return $this->render;
    }

}

?>