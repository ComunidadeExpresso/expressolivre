<?php

if( !defined( 'ROOTPATH' ) )
    define( 'ROOTPATH', dirname(__FILE__).'/..' );

require_once(ROOTPATH.'/rest/hypermedia/hypermedia.php');

use prototype\api\Config as Config;

class GroupResource extends Resource {

    /**
     * Retorna um grupo pelo seu identificador e seus parcitipantes
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Gustavo Pereira dos Santos Stabelini
     * @return     Retorna um grupo pelo seu identificador e seus parcitipantes
     * @access     public
     * */
    function get($request, $id) {

	$response = new Response($request);
	$response->addHeader('Content-type', 'aplication/json');
	$response->code = Response::OK;

	$h = new Hypermedia();
	$c = new Collection($request->resources, 'GroupResource');

	try {
	    $this->secured();

            $group = Controller::find( array( 'concept' => 'contactGroup' ), false, array( 'filter' => array( 'AND', array('=', 'user',  Config::me("uidNumber") ), array('=', 'id', $id) )  ) );

	    //Se nao foi encontrado contatos na consulta
	    if($group===false){
		$error = new Error();
		$error->setCode(Response::NOTFOUND);
		$error->setTitle('Group not found');
		$error->setDescription('Group not found.');

		$c->setError($error);
		$h->setCollection($c);

		$response->code = Response::NOTFOUND;
		$response->body = $h->getHypermedia($request->accept[10][0]);
		return $response;
	    }
		
		foreach($group[0]['contacts'] as $value){
			$d = new Data();
			$i = new Item($request->resources, 'ContactResource', $value['id']);

			$d->setName('name');
			$d->setValue($value['name']);
			$d->setPrompt('Nome do Grupo');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);

			$i->addData($d);

			$d = new Data();
			$d->setName('id');
			$d->setValue($value['id']);
			$d->setPrompt('Id do Contato');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);

			$i->addData($d);

			$d = new Data();
			$d->setName('email');
			$d->setValue($value['email']);
			$d->setPrompt('Email do Contato');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);

			$i->addData($d);

			$l = new Link();

			$l->setHref('');
			$l->setRel('delete');
			$l->setAlt('Remover');
			$l->setPrompt('Remover');
			$l->setRender('link');

			$i->addLink($l);

			$l = new Link();
			$l->setHref('');
			$l->setRel('put');
			$l->setAlt('Atualizar');
			$l->setPrompt('Atualizar');
			$l->setRender('link');

			$i->addLink($l);

			$l = new Link();
			$l->setHref('/contact/'.$value['id']);
			$l->setRel('get');
			$l->setAlt('Buscar');
			$l->setPrompt('Buscar');
			$l->setRender('link');

			$i->addLink($l);
			$c->addItem($i);
	    }

		$t = new Template();
		$d = new Data();

		$d->setName('name');
		$d->setValue(null);
		$d->setPrompt('Nome do Contato');
		$d->setDataType('string');
		$d->setMaxLength(100);
		$d->setMinLength(null);
		$d->setRequired(true);

		$t->addData($d);

		$d = new Data();
		$d->setName('email');
		$d->setValue(null);
		$d->setPrompt('Email do Contato');
		$d->setDataType('string');
		$d->setMaxLength(100);
		$d->setMinLength(null);
		$d->setRequired(true);

		$t->addData($d);

		$d = new Data();
		$d->setName('telefone');
		$d->setValue(null);
		$d->setPrompt('Telefone do Contato');
		$d->setDataType('string');
		$d->setMaxLength(100);
		$d->setMinLength(null);
		$d->setRequired(true);

		$t->addData($d);

		$c->setTemplate($t);

		$d = new Data();
		$d->setName('id');
		$d->setValue($group[0]['id']);
		$d->setPrompt('Id do Grupo');
		$d->setDataType('string');
		$d->setMaxLength(100);
		$d->setMinLength(null);
		$d->setRequired(true);

		$c->addData($d);

		$d = new Data();
		$d->setName('name');
		$d->setValue($group[0]['name']);
		$d->setPrompt('Nome do Grupo');
		$d->setDataType('string');
		$d->setMaxLength(100);
		$d->setMinLength(null);
		$d->setRequired(true);

		$c->addData($d);
		
		$d = new Data();
		$d->setName('email');
		$d->setValue($group[0]['email']);
		$d->setPrompt('Email do Grupo');
		$d->setDataType('string');
		$d->setMaxLength(100);
		$d->setMinLength(null);
		$d->setRequired(true);

		$c->addData($d);	
		
		$l = new Link();
		$l->setHref('/group/'.$group[0]['id']);
		$l->setRel('get');
		$l->setAlt('Buscar');
		$l->setPrompt('Buscar');
		$l->setRender('link');

		$c->addLink($l);
			
		$l = new Link();
		$l->setHref(null);
		$l->setRel('put');
		$l->setAlt('Edit');
		$l->setRender('link');
		$l->setPrompt('Editar');

		$c->addLink($l);
		
		$l = new Link();
		$l->setHref(null);
		$l->setRel('post');
		$l->setAlt('Create');
		$l->setRender('link');
		$l->setPrompt('Criar');

		$c->addLink($l);
		
		$l = new Link();
		$l->setHref(null);
		$l->setRel('delete');
		$l->setAlt('Delete');
		$l->setRender('link');
		$l->setPrompt('Remover');

		$c->addLink($l);
		
		$h->setCollection($c);

	}catch (Exception $ex){
		$error = new Error();
		$error->setCode(Response::INTERNALSERVERERROR);
		$error->setTitle('Internal Server Error');
		$error->setDescription($ex);

		$c->setError($error);
		$h->setCollection($c);

		$response->code = Response::INTERNALSERVERERROR;
		$response->body = $h->getHypermedia($request->accept[10][0]);
		return $response;
	}

	$response->body = $h->getHypermedia($request->accept[10][0]);
	return $response;
  }
}
?>
