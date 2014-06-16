<?php

if (!defined('ROOTPATH'))
    define('ROOTPATH', dirname(__FILE__) . '/..');

require_once(ROOTPATH . '/rest/hypermedia/hypermedia.php');

use prototype\api\Config as Config;

class DynamicContactsResource extends Resource {

    /**
     * Retorna uma lista de contatos recentes 
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     José Vicente Tezza Jr. 
     * @return     Retorna uma lista de contatos recentes
     * @access     public
     * */
    function get($request) {

	$this->secured();

	$response = new Response($request);
	$response->addHeader('Content-type', 'aplication/json');
	$response->code = Response::OK;

	$h = new Hypermedia();
	$c = new Collection($request->resources, 'DynamicContactsResource');

	try {

	    $dynamicContacts = Controller::find(
			    array('concept' => 'dynamicContact'), false, array('filter' => array('=', 'owner', Config::me("uidNumber")),
			'order' => array('number_of_messages', 'name', 'mail'),
			'orderDesc' => array(true, false, false))
	    );

	    //Se nao foi encontrado contatos na consulta
	    if (!$dynamicContacts) {
		$this->createException($request, $response, Response::NOTFOUND, 'Bad request', 'Dynamic Contact not found.');
		return $response;
	    }

	    $dynamicContacts = array_slice($dynamicContacts, 0,50);
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
		$d->setValue($value['number_of_messages']);
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

	    $t = new Template();
	    $d = new Data();

	    $d->setName('name');
	    $d->setValue(null);
	    $d->setPrompt('Nome do Contato Recente');
	    $d->setDataType('string');
	    $d->setMaxLength(100);
	    $d->setMinLength(null);
	    $d->setRequired(false);

	    $t->addData($d);

	    $d = new Data();
	    $d->setName('mail');
	    $d->setValue(null);
	    $d->setPrompt('Email do Contato Recente');
	    $d->setDataType('string');
	    $d->setMaxLength(100);
	    $d->setMinLength(null);
	    $d->setRequired(true);

	    $t->addData($d);

	    $d = new Data();
	    $d->setName('number_of_messages');
	    $d->setValue(null);
	    $d->setPrompt('Quantidade de mensagens enviadas');
	    $d->setDataType('integer');
	    $d->setMaxLength(100);
	    $d->setMinLength(null);
	    $d->setRequired(false);

	    $t->addData($d);

	    $c->setTemplate($t);

	    $h->setCollection($c);
	} catch (Exception $ex) {
	    $this->createException($request, $response, Response::INTERNALSERVERERROR, 'Internal Server Error', $ex);
	    return $response;
	}

	$response->body = $h->getHypermedia($request->accept[10][0]);
	return $response;
    }

    /**
     * Salva um contato recente
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     José Vicente Tezza Jr. 
     * @return     retorna a uri do contato recente
     * @access     public
     * */
    function post($request) {

	$this->secured();

	//verificar se a preferencia de contatos dinamicos nao esta ativada
	if(!$this->isEnabledDynamicContacts(Config::me("uidNumber")) ){
		$response = new Response($request);
		$this->createException($request, $response, Response::UNAUTHORIZED, 'Resource unauthorized', 'disabled dynamic contacts preference');
		return $response;
	}


	if (count($_POST) == 0) {
	    $response = new Response($request);
	    $response->code = Response::INTERNALSERVERERROR;
	    return $response;
	}

	//recuperar os atributos definidos no conceito 'user'
	$map = Config::get('dynamicContact', 'PostgreSQL.mapping');

	$params = array();
	foreach ($_POST as $key => $value) {

	    if (!isset($map[$key]) || $key == 'id' || $key == 'timestamp' || $key == 'number_of_messages') {
		continue;
	    }
	    $params[$key] = $value;
	}

	if (count($params) == 0) {
	    $response = new Response($request);
	    $response->code = Response::INTERNALSERVERERROR;
	    return $response;
	}

	$response = new Response($request);
	$response->addHeader('Content-type', 'aplication/json');
	$response->code = Response::CREATED;


	//completar os atributos
	$params['owner'] = Config::me("uidNumber");
	$params['number_of_messages'] = '1';
	$params['timestamp'] = time();


	try {

		//verificar o limite maximo de contatos dinamicos nas preferencias do administrador
		$sql = 	"SELECT config_value ".
			"FROM phpgw_config ".
			"WHERE config_app = 'expressoMail' ".
				"AND config_name = 'expressoMail_Number_of_dynamic_contacts'";

		$numberOfMessages = Controller::service('PostgreSQL')->execResultSql($sql, true);
		$numberOfMessages = (count($numberOfMessages) > 0) ? (int)$numberOfMessages['config_value'] : 0;

		//realizar busca de contatos dinamicos ordenados pela data de utilizacao
		$dynamicContacts = Controller::find(array('concept' => 'dynamicContact'), false, array('filter' => array('=', 'owner', Config::me("uidNumber")), 'order' => array('timestamp') ) );
		$numberOfDynamicContacts = ($dynamicContacts !== false) ? count($dynamicContacts) : 0;


		//se a quantidade de contatos dinamicos de usuario exceder o limite maximo definido nas preferencias do administrador,
		//remover o contato dinamico mais antigo
		if($numberOfMessages > 0 && $numberOfDynamicContacts >= $numberOfMessages){
			$id = $dynamicContacts[0]['id'];
			$delete = Controller::delete(array('concept' => 'dynamicContact', 'id' => $id));

			if (!$delete) {
				$this->createException($request, $response, Response::INTERNALSERVERERROR, 'Internal Server Error', Controller::service('PostgreSQL')->error);
				return $response;
			}
		}	

		//inserir o novo contato dinamico
		$create = Controller::create( array('concept' => 'dynamicContact'), $params );

		if (!$create) {
			throw new Exception(Controller::service('PostgreSQL')->error);
		}

	} catch (Exception $ex) {

	    $response->code = Response::INTERNALSERVERERROR;
	    return $response;
	}
	$response->body = json_encode(null);

	return $response;
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
