<?php

if (!defined('ROOTPATH'))
    define('ROOTPATH', dirname(__FILE__) . '/..');

require_once(ROOTPATH . '/rest/hypermedia/hypermedia.php');

use prototype\api\Config as Config;

class UserContactsResource extends Resource {

    /**
     * Retorna uma lista de grupos
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     Adrino Coutinho da Silva. 
     * @return     Retorna uma lista de Contatos Dinâmicos, Grupos, Contatos Pessoais, Grupos Compartilhados e Contatos Compartilhados
     * @access     public
     * */
    function get($request) {

	$this->secured();

        //verificar se a preferencia de contatos dinamicos nao esta ativada
        if(!$this->isEnabledDynamicContacts(Config::me("uidNumber")) ){
                $response = new Response($request);
                $this->createException($request, $response, Response::UNAUTHORIZED, 'Resource unauthorized', 'disabled dynamic contacts preference');
                return $response;
        }

	$response = new Response($request);
	$response->addHeader('Content-type', 'aplication/json');
	$response->code = Response::OK;

	$h = new Hypermedia();
	$c = new Collection($request->resources, 'UserContactsResource');

	try {

	    $d = new Data();

	    $d->setName('User Contacts');
	    $d->setValue(null);
	    $d->setPrompt('Contatos do usuário');
	    $d->setDataType(null);
	    $d->setMaxLength(null);
	    $d->setMinLength(null);
	    $d->setRequired(null);

	    $c->addData($d);

//Recupera os contatos dinâmicos do usuario
	    $dynamicContacts = Controller::find(
			    array('concept' => 'dynamicContact'), false, array('filter' => array('=', 'owner', Config::me("uidNumber")),
			'order' => array('number_of_messages', 'name', 'mail'),
			'orderDesc' => array(true, false, false))
	    );

	    if ($dynamicContacts) {
		foreach ($dynamicContacts as $value) {
		    $d = new Data();
		    $i = new Item($request->resources, 'DynamicContactsResource', $value['id']);

		    $d->setName('name');
		    $d->setValue($value['name']);
		    $d->setPrompt('Nome do Contato Recente');
		    $d->setDataType('string');
		    $d->setMaxLength('100');
		    $d->setMinLength(null);
		    $d->setRequired(true);

		    $i->addData($d);

		    $d = new Data();
		    $d->setName('mail');
		    $d->setValue($value['mail']);
		    $d->setPrompt('Email do Contato Recente');
		    $d->setDataType('string');
		    $d->setMaxLength('100');
		    $d->setMinLength(null);
		    $d->setRequired(true);

		    $i->addData($d);

		    $d = new Data();
		    $d->setName('number_of_messages');
		    $d->setValue((int)$value['number_of_messages']);
		    $d->setPrompt('Quantidade de mensagens enviadas');
		    $d->setDataType('integer');
		    $d->setMaxLength('100');
		    $d->setMinLength(null);
		    $d->setRequired(false);

		    $i->addData($d);

		    $d = new Data();
		    $d->setName('id');
		    $d->setValue($value['id']);
		    $d->setPrompt('Id do contato dinamico');
		    $d->setDataType('integer');
		    $d->setMaxLength('100');
		    $d->setMinLength(null);
		    $d->setRequired(false);

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
		    $l->setHref('/dynamiccontact/' . $value['id']);
		    $l->setRel('get');
		    $l->setAlt('Buscar');
		    $l->setPrompt('Buscar');
		    $l->setRender('link');

		    $i->addLink($l);
		    $c->addItem($i);
		}
	    }

	    $idS = array(Config::me("uidNumber"));
	    $acl = array();

//Recupera o uidNumber de todos os usuários que compartilham suas agendas com o usuário logado
	    $shareds = Controller::service('PostgreSQL')->execResultSql('select acl_account as "uidNumber", acl_rights as "acl" from phpgw_acl where (acl_location =   \'' . Config::me("uidNumber") . '\' AND acl_appname =  \'contactcenter\' )');

	    if (!empty($shareds) && $shareds)
		foreach ($shareds as $s) {
		    array_push($idS, $s['uidNumber']);
		    $acl[$s['uidNumber']] = $this->decodeAcl(decbin($s['acl']));
		}

	    //Recupera os grupos do usuario
	    $groups = Controller::find(array('concept' => 'contactGroup'), false, array('filter' => array('IN', 'user', $idS), 'order' => array('name')));

	    if ($groups) {
		foreach ($groups as $value) {
		    if (($value['user'] == Config::me("uidNumber")) || ($acl[$value['user']]['read'])) {

			$i = new Item($request->resources, ($value['user'] == Config::me("uidNumber") ? 'GroupsResource' : 'SharedGroupResource'), $value['id']);

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
			$d->setName('owner');
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
			$d->setName('mail');
			$d->setValue($value['email']);
			$d->setPrompt('Email do Grupo');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);
			$i->addData($d);

			if (Config::me("uidNumber") != $value['user']) {
			    /* Descomentar ao implementar os métodos
			      if ($acl[$value['user']]['delete']) {
			      $l = new Link();
			      $l->setHref('');
			      $l->setRel('delete');
			      $l->setAlt('Remover');
			      $l->setPrompt('Remover');
			      $l->setRender('link');
			      $i->addLink($l);
			      }

			      if ($acl[$value['user']]['update']) {
			      $l = new Link();
			      $l->setHref('');
			      $l->setRel('put');
			      $l->setAlt('Atualizar');
			      $l->setPrompt('Atualizar');
			      $l->setRender('link');
			      $i->addLink($l);
			      }

			      if ($acl[$value['user']]['write']) {
			      $l = new Link();
			      $l->setHref('');
			      $l->setRel('post');
			      $l->setAlt('Criar');
			      $l->setPrompt('Criar novo');
			      $l->setRender('link');
			      $i->addLink($l);
			      }
			     */

			    $l = new Link();
			    $l->setHref('/sharedgroup/' . $value['id']);
			    $l->setRel('get');
			    $l->setAlt('Buscar');
			    $l->setPrompt('Buscar');
			    $l->setRender('link');
			    $i->addLink($l);
			} else {
			    /* Descomentar ao implementar métodos no recurso
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
			     */

			    $l = new Link();
			    $l->setHref('/group/' . $value['id']);
			    $l->setRel('get');
			    $l->setAlt('Buscar');
			    $l->setPrompt('Buscar');
			    $l->setRender('link');

			    $i->addLink($l);
			}
			$c->addItem($i);
		    }
		}
	    }

//Recupera os contatos pessoais do usuario
	    $contacts = Controller::find(array('concept' => 'contact'), false, array('filter' => array('IN', 'user', $idS)));

	    if ($contacts) {

		foreach ($contacts as $value) {

		    if (($value['user'] == Config::me("uidNumber")) || ($acl[$value['user']]['read'])) {

			$d = new Data();
			$i = new Item($request->resources, (($value['user'] == Config::me("uidNumber")) ? 'PersonalContactResource' : 'SharedContactResource'), $value['id']);
			$d->setName('id');
			$d->setValue($value['id']);
			$d->setPrompt('Id do Contato');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);

			$i->addData($d);

			$d = new Data();
			$d->setName('owner');
			$d->setValue($value['user']);
			$d->setPrompt('Id Dono do Contato');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);

			$i->addData($d);

			$d = new Data();
			$d->setName('name');
			$d->setValue($value['name']);
			$d->setPrompt('Nome do Contato');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);

			$i->addData($d);

			$d = new Data();
			$d->setName('mail');
			$d->setValue(isset($value['email']) ? $value['email'] : null);
			$d->setPrompt('Email do Contato');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);

			$i->addData($d);

			$d = new Data();
			$d->setName('telephone');
			$d->setValue(isset($value['telephone']) ? $value['telephone'] : null);
			$d->setPrompt('Telefone do Contato');
			$d->setDataType('string');
			$d->setMaxLength('100');
			$d->setMinLength(null);
			$d->setRequired(true);

			$i->addData($d);

			if (Config::me("uidNumber") != $value['user']) {

			    $l = new Link();
			    $l->setHref('/sharedcontact/' . $value['id']);
			    $l->setRel('get');
			    $l->setAlt('Buscar');
			    $l->setPrompt('Buscar');
			    $l->setRender('link');
			    $i->addLink($l);

			    /* Descomentar ao criar recursos
			      if ($acl[$value['user']]['delete']) {
			      $l = new Link();
			      $l->setHref('');
			      $l->setRel('delete');
			      $l->setAlt('Remover');
			      $l->setPrompt('Remover');
			      $l->setRender('link');
			      $i->addLink($l);
			      }

			      if ($acl[$value['user']]['put']) {
			      $l = new Link();
			      $l->setHref('');
			      $l->setRel('put');
			      $l->setAlt('Atualizar');
			      $l->setPrompt('Atualizar');
			      $l->setRender('link');
			      $i->addLink($l);
			      }
			     */
			} else {
			    /* Descomentar ao criar recursos
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
			     */
			    $l = new Link();
			    $l->setHref('/contact/' . $value['id']);
			    $l->setRel('get');
			    $l->setAlt('Buscar');
			    $l->setPrompt('Buscar');
			    $l->setRender('link');

			    $i->addLink($l);
			}
			$c->addItem($i);
		    }
		}
	    }


	    if (!$contacts && !$dynamicContacts && !$groups) {
		$this->createException($request, $response, Response::NOTFOUND, 'Bad request', 'Resource not found.');
		return $response;
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

	    $response->body = $h->getHypermedia($request->accept[10][0]);
	    return $response;
	} catch (Exception $ex) {
	    $this->createException($request, $response, Response::INTERNALSERVERERROR, 'Internal Server Error', $ex);
	    return $response;
	}
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

    private function isEnabledDynamicContacts($user){

		//recuperando as preferencias (suas preferencias, preferencia padrão, preferencia obrigatoria)
		//dos contatos dinamicos
        $sql = 'SELECT preference_owner, preference_value '.
                'FROM phpgw_preferences '.
                'WHERE preference_app = \'expressoMail\' AND '.
                        'preference_owner in (-1,-2, ' . $user . ')';

        $preferences = Controller::service('PostgreSQL')->execResultSql($sql);

		$array = array();
        if(count($preferences) > 0){
			foreach($preferences as $preference){
				//recupera a preferencia
                $preference_value = unserialize( $preference['preference_value'] );
		
				//gera um array com o owner e o valor da preferencia:
				//true: SIM  (1)
				//false: NAO (0)
				//null: escolha pelo usuario/ usar padrao / sem padrao
				$value = null;
				if(isset($preference_value['use_dynamic_contacts'])){
					$value = (isset($preference_value['use_dynamic_contacts'])) ? $preference_value['use_dynamic_contacts'] == '1' : false;
				}
				$array[ $preference['preference_owner'] ] = $value;
			}
        }

		//preferencia obrigatoria (SIM)
		if(array_key_exists(-1,$array) && $array[-1]){
			return true;
		}
		//preferencia do user (SIM)
		else if(array_key_exists($user,$array) && $array[$user] ){
			return true;
		}
		//preferencia padrao (SIM) escolhida pelo user
		else if(array_key_exists($user, $array) && $array[$user] === null &&
		        array_key_exists(-2, $array) && $array[-2]){
			return true;
		}
		return false;
    }
}

?>
