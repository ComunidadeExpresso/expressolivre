<?php

if( !defined( 'ROOTPATH' ) )
    define( 'ROOTPATH', dirname(__FILE__).'/..' );

require_once(ROOTPATH.'/rest/hypermedia/hypermedia.php');

use prototype\api\Config as Config;

class UsersResource extends Resource {

    /**
     * Retorna uma lista de usuários
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     José Vicente Tezza Jr. 
     * @return     Retorna uma lista de usuários do LDAP
     * @access     public
     * */
    function get($request) {

	$response = new Response($request);
	$response->addHeader('Content-type', 'aplication/json');
	$response->code = Response::OK;

	$h = new Hypermedia();
	$c = new Collection($request->resources, 'UsersResource');

	$this->secured();


	try {	    
	    //Executa uma consulta de usuários do LDAP a partir de um determinado atributo e valor
	    if(isset($_GET['field']) && isset($_GET['value'])){

		    //recupera os atributos definidos no conceito 'user'
		    $map = Config::get('user', 'OpenLDAP.mapping');

		    //verifica se o campo(atributo) passado pelo usuário está definido no conceito 'user'
		    if(isset($map[ $_GET['field'] ])){
			    $users = Controller::find(
							array('concept' => 'user','service'=>'OpenLDAP'), 
							false, 
							array('filter' => array('=', $_GET['field'],$_GET['value'] ), 'notExternal' => true)
			    );
		    }
		    else{
				//lança warning no log do Expresso
				trigger_error("Invalid field (".$_GET['field'].") in the query.", E_USER_WARNING);

				//formata os atributos LDAP do conceito 'user'
				$attributes = implode(', ', $map); 

				//Configura o erro na hypermedia
				$error = new Error();
				$error->setCode(Response::NOTFOUND);
				$error->setTitle('UserLDAP not found');
				$error->setDescription("Invalid field (".$_GET['field'].") in the query. Use of these: ".$attributes);

				$c->setError($error);
				$h->setCollection($c);

				//retorna a hypermedia
				$response->code = Response::NOTFOUND;
				$response->body = $h->getHypermedia($request->accept[10][0]);
				return $response;
		    }
	    }
	    else{
		    //Executa a consulta dos primeiros 20 usuarios do LDAP
		    $users = Controller::find(
						array('concept' => 'user','service'=>'OpenLDAP'), 
						false, 
						array('filter' => array('=', 'phpgwAccountType', 'u'),
						      'limit' => 20,
						      'notExternal' => true)
		    );
	    }

	    //Se nao foi encontrado usuarios na consulta
	    if($users===false){
			$error = new Error();
			$error->setCode(Response::NOTFOUND);
			$error->setTitle('UserLDAP not found');
			$error->setDescription('Users not found.');

			$c->setError($error);
			$h->setCollection($c);

			$response->code = Response::NOTFOUND;
			$response->body = $h->getHypermedia($request->accept[10][0]);
			return $response;
	    }

		foreach($users as $value){
			$d = new Data();
			$i = new Item($request->resources, 'UsersResource', $value['uid']);

			$d->setName('name');
			$d->setValue($value['name']);
			$d->setPrompt('Nome do Usuario');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);

			$i->addData($d);

			$d = new Data();
			$d->setName('email');
			$d->setValue($value['mail']);
			$d->setPrompt('Email do Usuario');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);

			$i->addData($d);

			$d = new Data();
			$d->setName('telephoneNumber');
			$d->setValue($value['telephoneNumber']);
			$d->setPrompt('Telefone do Usuario');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);

			$i->addData($d);

			$d = new Data();
			$d->setName('vacationActive');
			$d->setValue($value['vacationActive']);
			$d->setPrompt('Status da Regra fora de Escritorio');
			$d->setDataType('boolean');
			$d->setMaxLength('10');
			$d->setMinLength(null);
			$d->setRequired(null);

			$i->addData($d);
			
			$d = new Data();
			$d->setName('vacationInfo');
			$d->setValue($value['vacationInfo']);
			$d->setPrompt('Mensagem da Regra fora de Escritorio');
			$d->setDataType('boolean');
			$d->setMaxLength('10');
			$d->setMinLength(null);
			$d->setRequired(null);

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
			$l->setHref('/userldap/'.$value['uid']);
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
		$d->setPrompt('Nome do Usuario');
		$d->setDataType('string');
		$d->setMaxLength(100);
		$d->setMinLength(null);
		$d->setRequired(true);

		$t->addData($d);

		$d = new Data();
		$d->setName('email');
		$d->setValue(null);
		$d->setPrompt('Email do Usuario');
		$d->setDataType('string');
		$d->setMaxLength(100);
		$d->setMinLength(null);
		$d->setRequired(true);

		$t->addData($d);

		$d = new Data();
		$d->setName('telefone');
		$d->setValue(null);
		$d->setPrompt('Telefone do Usuario');
		$d->setDataType('string');
		$d->setMaxLength(100);
		$d->setMinLength(null);
		$d->setRequired(true);

		$t->addData($d);

	    $queries = new Querie();    
		$queries->setHref($c->href);
	    $queries->setData('field','',true);
	    $queries->setData('value','',true);
	    $c->addQueries($queries);


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
