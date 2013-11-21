<?php

if (!defined('ROOTPATH'))
    define('ROOTPATH', dirname(__FILE__) . '/..');

require_once(ROOTPATH . '/rest/hypermedia/hypermedia.php');

use prototype\api\Config as Config;

class SharedContactResource extends Resource {

    /**
     * Retorna um contato compartilhado 
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     José Vicente Tezza Jr. 
     * @return     Retorna um contato Compartilhado
     * @access     public
     * */
    function get($request, $id) {

	$this->secured();

	$response = new Response($request);
	$response->addHeader('Content-type', 'aplication/json');
	$response->code = Response::OK;

	$h = new Hypermedia();
	$c = new Collection($request->resources, 'SharedGroupResource');

	try {

	    //Recupera o contato
            $contact = Controller::find( array( 'concept' => 'contact' ), false, array( 'filter' => array('=', 'id', $id) ) );

            if (!$contact) {
                $this->createException($request, $response, Response::NOTFOUND, 'Bad request', 'Resource not found.');
                return $response;
            }

	    //Proprietario do contato 
	    $ownerId = $contact[0]['user'];

	    $idS = array(Config::me("uidNumber"));
	    $acl = array();

	    //Recupera o uidNumber do usuário que compartilhou o grupo com o usuário logado
	    $sql = 'SELECT acl_account as "uidNumber", acl_rights as "acl" '.
		   'FROM phpgw_acl '.
		   'WHERE (acl_location =   \'' . Config::me("uidNumber") . '\' AND acl_appname =  \'contactcenter\' AND acl_account = \''.$ownerId.'\')';
	    $shareds = Controller::service('PostgreSQL')->execResultSql($sql);

	    //Verifica o acesso definido para o usuario logado
	    $flagContact = false;
	    if (!empty($shareds) && $shareds){
		foreach ($shareds as $s) {
		    array_push($idS, $s['uidNumber']);
		    $acl[$s['uidNumber']] = $this->decodeAcl(decbin($s['acl']));

		    //verifica se o proprietario do contato habilitou o acesso de leitura para o usuario logado
		    if($s['uidNumber'] == $ownerId && $acl[$s['uidNumber']]['read']){
			$flagContact = true;
		    }
		}
	    }

	    //Se o contato nao esta compartilhado
	    if(!$flagContact){
		$this->createException($request, $response, Response::UNAUTHORIZED, 'unauthorized', 'Resource unauthorized.');
		return $response;
	    }

	    //Obtem informacoes do proprietario do contato
	    $userOwner = Controller::read(
					   array('concept' => 'user','service'=>'OpenLDAP'), 
					   false, 
					   array('filter' => array('=', 'id', $ownerId ), 'notExternal' => true)
	    );

	    if(is_array($userOwner)){
		$userOwner = $userOwner[0];
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
            $d->setName('name');
            $d->setValue($contact[0]['name']);
            $d->setPrompt('Nome do Contato');
            $d->setDataType('string');
            $d->setMaxLength('100');
            $d->setMinLength(null);
            $d->setRequired(true);

            $c->addData($d);

            $d = new Data();
            $d->setName('email');
            $d->setValue($contact[0]['email']);
            $d->setPrompt('Email do Contato');
            $d->setDataType('string');
            $d->setMaxLength('100');
            $d->setMinLength(null);
            $d->setRequired(true);

            $c->addData($d);

            $d = new Data();
            $d->setName('telephone');
            $d->setValue($contact[0]['telephone']);
            $d->setPrompt('Telefone do Contato');
            $d->setDataType('string');
            $d->setMaxLength('100');
            $d->setMinLength(null);
            $d->setRequired(true);

            $c->addData($d);

            $d = new Data();
            $d->setName('ownerId');
            $d->setValue($userOwner['id']);
            $d->setPrompt('Atributo UID (LDAP)');
            $d->setDataType('string');
            $d->setMaxLength(100);
            $d->setMinLength(null);
            $d->setRequired(true);

            $c->addData($d);

            $d = new Data();
            $d->setName('ownerName');
            $d->setValue($userOwner['name']);
            $d->setPrompt('Atributo cn (LDAP)');
            $d->setDataType('string');
            $d->setMaxLength(100);
            $d->setMinLength(null);
            $d->setRequired(true);

            $c->addData($d);

            //Define os link baseado nas permissoes de acesso
            if(Config::me('uidNumber') != $value['user']){
                    /*Descomentar ao implementar os métodos
                    if($acl[$value['user']]['delete']){
                          $l = new Link();
                          $l->setHref('');
                          $l->setRel('delete');
                          $l->setAlt('Remover');
                          $l->setPrompt('Remover');
                          $l->setRender('link');
                          $i->addLink($l);
                    }

                    if($acl[$value['user']]['update']){
                          $l = new Link();
                          $l->setHref('');
                          $l->setRel('put');
                          $l->setAlt('Atualizar');
                          $l->setPrompt('Atualizar');
                          $l->setRender('link');
                          $i->addLink($l);
                    }

                    if($acl[$value['user']]['write']){
                          $l = new Link();
                          $l->setHref('');
                          $l->setRel('post');
			  $l->setAlt('Criar');
                          $l->setPrompt('Criar novo');
                          $l->setRender('link');
                          $i->addLink($l);
                    }

                    if($acl[$value['user']]['read']){
                          $l = new Link();
                          $l->setHref('');
                          $l->setRel('get');
                          $l->setAlt('Buscar');
                          $l->setPrompt('Buscar');
                          $l->setRender('link');
                          $i->addLink($l);
                    }*/
            }
            else{
                    /*Descomentar ao implementar métodos no recurso
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
                    $l->setHref('');
                    $l->setRel('get');
                    $l->setAlt('Buscar');
                    $l->setPrompt('Buscar');
                    $l->setRender('link');

                    $i->addLink($l);
                    */
            }

            $h->setCollection($c);

	} catch (Exception $ex) {
	    $this->createException($request, $response, Response::INTERNALSERVERERROR, 'Internal Server Error', $ex);
	    return $response;
	}

	$response->body = $h->getHypermedia($request->accept[10][0]);
	return $response;
    }

    function decodeAcl($bin) {

	$acl = array();
	$bin = str_split($bin);
	$acl['read'] = (isset($bin[0]) && $bin[0] == 1) ? true : false;
	$acl['write'] = (isset($bin[1]) && $bin[1] == 1) ? true : false;
	$acl['update'] = (isset($bin[2]) && $bin[2] == 1) ? true : false;
	$acl['delete'] = (isset($bin[3]) && $bin[3] == 1) ? true : false;

	return $acl;
    }

    private function createException($request, &$response, $code, $title, $description) {
	$response->code = $code;

	$h = new Hypermedia();
	$c = new Collection($request->resources, 'DynamicContactResource');
	$e = new Error();

	$e->setCode($code);
	$e->setTitle($title);
	$e->setDescription($description);

	$c->setError($e);
	$h->setCollection($c);

	$response->body = $h->getHypermedia($request->accept[10][0]);
    }
}

?>
