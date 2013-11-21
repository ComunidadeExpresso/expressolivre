<?php

include_once('item.php');
include_once('link.php');
include_once('data.php');
include_once('template.php');
include_once('query.php');
include_once('error.php');
/*
  Valores padrão

  Números são enviados como números, sem aspas;
  Atributos não obrigatórios sem valor serão nulos;
  Strings vazias serão nulos;
  Todos os indices / chaves estarão sempre presentes na mensagem;
  Arrays vazios serão nulos;
  Objetos vazios serão nulos;
  Números sem valor serão nulos;
  valores booleanos são escritos como booleanos (sem aspas);
 */

class Collection {

    public $itens;
    public $href;
    public $error;
    public $pagination;
    public $queries;
    public $template;
    public $type;
    public $data;
    public $links;

    function __construct($config, $className, $id = null) {
	foreach ($config as $key => $value) {
	    if ($value['class'] == $className) {
		$uri = ($id ? (preg_replace('/\/[:][a-zA-Z-0-9]+/', '', $key) . '/' . $id ) : $key);
		break;
	    }
	}
	$this->href = $uri;
    }

    function addLink($link) {
	$this->links[] = $link;
    }

    function addItem($item) {
	$this->itens[] = $item;
    }

    function addData($data) {
	$this->data[] = $data;
    }

    function getData() {
	return $this->data;
    }

    function setTemplate($template) {
	$this->template = $template;
    }

    function getTemplate() {
	return $this->template;
    }

    function setType($type) {
	$this->type = $type;
    }

    function getType() {
	return $this->type;
    }

    function addQueries($queries) {
	$this->queries[] = $queries;
    }

    function getQueries() {
	return $this->queries;
    }

    function setError($error) {
	$this->error = $error;
    }

    function getError() {
	return $this->error;
    }

    function setPagination($pagination) {
	$this->pagination = $pagination;
    }

    function getPagination() {
	return $this->pagination;
    }

}

?>
