<?php

if( !defined( 'ROOTPATH' ) )
    define( 'ROOTPATH', dirname(__FILE__).'/..' );

require_once(ROOTPATH.'/rest/hypermedia/hypermedia.php');

use prototype\api\Config as Config;

class GroupsResource extends Resource {

    /**
     * Retorna uma lista de grupos
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Douglas Zilli. 
     * @return     Retorna uma lista de Grupos
     * @access     public
     * */
    function get($request) {

	$response = new Response($request);
	$response->addHeader('Content-type', 'aplication/json');
	$response->code = Response::OK;

	$h = new Hypermedia();
	$c = new Collection($request->resources, 'GroupsResource');
	
	try {
	    $this->secured();

        $groups = Controller::find( array( 'concept' => 'contactGroup' ), false, array( 'filter' => array('=', 'user',  Config::me("uidNumber") ), 'order' => array('name') ) );			
			
	    //Se nao foi encontrado contatos na consulta
	    if($groups===false){
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

		foreach($groups as $value){
	
				$i = new Item($request->resources, 'GroupsResource', $value['id']);
				
				$d = new Data();
				$d->setName('id');
				$d->setValue($value['id']);
				$d->setPrompt('Id do Grupo');
				$d->setDataType('string');
				$d->setMaxLength('100');
				$d->setMinLength(null);
				$d->setRequired(true);
				$i->addData($d);

				$d = new Data();
				$d->setName('user');
				$d->setValue($value['user']);
				$d->setPrompt('Id Dono do Grupo');
				$d->setDataType('string');
				$d->setMaxLength('100');
				$d->setMinLength(null);
				$d->setRequired(true);
				$i->addData($d);

				$d = new Data();				
				$d->setName('name');
				$d->setValue($value['name']);
				$d->setPrompt('Nome do Grupo');
				$d->setDataType('string');
				$d->setMaxLength('100');
				$d->setMinLength(null);
				$d->setRequired(true);
				$i->addData($d);

				$d = new Data();				
				$d->setName('email');
				$d->setValue($value['email']);
				$d->setPrompt('Email do Grupo');
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
				$l->setHref('/group/'.$value['id']);
				$l->setRel('get');
				$l->setAlt('Buscar');
				$l->setPrompt('Buscar');
				$l->setRender('link');

				$i->addLink($l);
				$c->addItem($i);
	    }

            $t = new Template();

            $d = new Data();
			
			$d->setName('id');
			$d->setValue(null);
			$d->setPrompt('Id do Grupo');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);
			
			$t->addData($d);

			$d = new Data();
			$d->setName('user');
			$d->setValue(null);
			$d->setPrompt('Id Dono do Grupo');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);
			
			$t->addData($d);

			$d = new Data();				
			$d->setName('name');
			$d->setValue(null);
			$d->setPrompt('Nome do Grupo');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);
			
            $t->addData($d);

            $c->setTemplate($t);
			
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
