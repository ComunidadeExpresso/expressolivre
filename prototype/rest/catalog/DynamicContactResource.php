<?php

if (!defined('ROOTPATH'))
    define('ROOTPATH', dirname(__FILE__) . '/..');

require_once(ROOTPATH . '/rest/hypermedia/hypermedia.php');

use prototype\api\Config as Config;

class DynamicContactResource extends Resource {

    /**
     * Retorna um contato recente 
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     José Vicente Tezza Jr. 
     * @return     Retorna um contato recente
     * @access     public
     * */
    function get($request, $id) {

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
	$c = new Collection($request->resources, 'DynamicContactResource', $id);

	try {
	    $dynamicContact = Controller::read(
			    array('concept' => 'dynamicContact'), false, array('filter' => array('AND', array('=', 'owner', Config::me("uidNumber")), array('=', 'id', $id)))
	    );

	    //Se nao foi encontrado contatos na consulta
	    if (!$dynamicContact) {
		$this->createException($request, $response, Response::NOTFOUND, 'Bad request', 'Dynamic Contact not found.');
		return $response;
	    }
	    
	    //Normaliza dado
	    if(is_array($dynamicContact))
		$dynamicContact = $dynamicContact[0];


	    $t = new Template();
	    $d = new Data();

	    $d->setName('name');
	    $d->setValue(null);
	    $d->setPrompt('Nome do Contato Recente');
	    $d->setDataType('string');
	    $d->setMaxLength(100);
	    $d->setMinLength(null);
	    $d->setRequired(true);

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

	    $d = new Data();
	    $d->setName('id');
	    $d->setValue($dynamicContact['id']);
	    $d->setPrompt('Identificador do Contato Recente');
	    $d->setDataType('integer');
	    $d->setMaxLength(null);
	    $d->setMinLength(null);
	    $d->setRequired(true);

	    $c->addData($d);

	    $d = new Data();

	    $d->setName('name');
	    $d->setValue($dynamicContact['name']);
	    $d->setPrompt('Nome do Contato Recente');
	    $d->setDataType('string');
	    $d->setMaxLength('100');
	    $d->setMinLength(null);
	    $d->setRequired(true);

	    $c->addData($d);

	    $d = new Data();
	    $d->setName('mail');
	    $d->setValue($dynamicContact['mail']);
	    $d->setPrompt('Email do Contato Recente');
	    $d->setDataType('string');
	    $d->setMaxLength('100');
	    $d->setMinLength(null);
	    $d->setRequired(true);

	    $c->addData($d);

	    $d = new Data();
	    $d->setName('number_of_messages');
	    $d->setValue($dynamicContact['number_of_messages']);
	    $d->setPrompt('Quantidade de mensagens enviadas');
	    $d->setDataType('integer');
	    $d->setMaxLength('100');
	    $d->setMinLength(null);
	    $d->setRequired(false);

	    $c->addData($d);


	    $l = new Link();

	    $l->setHref('');
	    $l->setRel('delete');
	    $l->setAlt('Remover');
	    $l->setPrompt('Remover');
	    $l->setRender('link');

	    $c->addLink($l);

	    $l = new Link();
	    $l->setHref('');
	    $l->setRel('put');
	    $l->setAlt('Atualizar');
	    $l->setPrompt('Atualizar');
	    $l->setRender('link');

	    $c->addLink($l);

	    $h->setCollection($c);
	} catch (Exception $ex) {
	    $this->createException($request, $response, Response::INTERNALSERVERERROR, 'Internal Server Error', 'Internal Server Error');
	    return $response;
	}

	$response->body = $h->getHypermedia($request->accept[10][0]);
	return $response;
    }

    /**
     * Atualiza um contato recente
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     José Vicente Tezza Jr. 
     * @return     retorna a uri do contato recente
     * @access     public
     * */
    function put($request, $id) {
	
	$this->secured();

        //verificar se a preferencia de contatos dinamicos nao esta ativada
        if(!$this->isEnabledDynamicContacts(Config::me("uidNumber")) ){
                $response = new Response($request);
                $this->createException($request, $response, Response::UNAUTHORIZED, 'Resource unauthorized', 'disabled dynamic contacts preference');
                return $response;
        }


	$post = $request->dataDecoded;
	$response = new Response($request);

	if (count($post) == 0){
	    $this->createException($request, $response, Response::BADREQUEST, 'Bad request', 'Invalid template data');
	    return $response;
	}

	//recupera os atributos definidos no conceito 'user'
	$map = Config::get('dynamicContact', 'PostgreSQL.mapping');

	$params = array();
	foreach ($post as $key => $value) {

	    if (!isset($map[$key]) || $key == 'id' || $key == 'timestamp' || $key == 'number_of_messages') {
		continue;
	    }
	    $params[$key] = $value;
	}

	if (count($params) == 0) {
	    $this->createException($request, $response, Response::BADREQUEST, 'Bad request', 'Invalid template data');
	    return $response;
	}

	//completar os atributos
	$params['owner'] = Config::me("uidNumber");
	$params['timestamp'] = time();
	$params['id'] = $id;

	$response->addHeader('Content-type', 'aplication/json');
	$response->code = Response::NOCONTENT;

	try {

	    $dynamicContact = Controller::read(
			    array('concept' => 'dynamicContact'), false, array('filter' => array(
			    'AND',
			    array('=', 'owner', Config::me("uidNumber")),
			    array('=', 'id', $id)))
	    );


	    //Se existe o recurso
	    if ($dynamicContact) {
		//Normaliza o recurso
		if(is_array($dynamicContact))
		    $dynamicContact = $dynamicContact[0];
		
		$params['number_of_messages'] = $dynamicContact['number_of_messages'] + 1; 

		$dynamicContact = Controller::update(array('concept' => 'dynamicContact', 'id' => $id), $params);

		if (!$dynamicContact) {
		    $this->createException($request, $response, Response::INTERNALSERVERERROR, 'Internal Server Error', Controller::service('PostgreSQL')->error);
		    return $response;
		}
	    } else {
		/*
		$idDynamicContact = Controller::create(	array('concept' => 'dynamicContact'), $params);
		*/
		//if (!$idDynamicContact) {
		    $this->createException($request, $response, Response::NOTFOUND, 'Bad request', 'Invalid data');
		    return $response;
		//}
	    }
	} catch (Exception $ex) {
	    $this->createException($request, $response, Response::INTERNALSERVERERROR, 'Internal Server Error', 'Internal Server Error');
	    return $response;
	}
	$response->body = json_encode(null);
	return $response;
    }

    /**
     * Remove um contato recente
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     José Vicente Tezza Jr. 
     * @access     public
     * */
    function delete($request, $id) {

	$this->secured();

        //verificar se a preferencia de contatos dinamicos nao esta ativada
        if(!$this->isEnabledDynamicContacts(Config::me("uidNumber")) ){
                $response = new Response($request);
                $this->createException($request, $response, Response::UNAUTHORIZED, 'Resource unauthorized', 'disabled dynamic contacts preference');
                return $response;
        }

	$response = new Response($request);
	$response->addHeader('Content-type', 'aplication/json');
	$response->code = Response::NOCONTENT;

	try {
	    //Verifica se o recurso existe
	    $dinamicContact = Controller::read(array('concept' => 'dynamicContact', 'id' => $id));

	    //Se existe o recurso
	    if ($dinamicContact) {

		$delete = Controller::delete(array('concept' => 'dynamicContact', 'id' => $id));

		if (!$delete) {
		    $this->createException($request, $response, Response::INTERNALSERVERERROR, 'Internal Server Error', Controller::service('PostgreSQL')->error);
		    return $response;
		}
	    } else {
		$this->createException($request, $response, Response::NOTFOUND, 'Bad request', 'Invalid data');
		return $response;
	    }
	} catch (Exception $ex) {
	    $this->createException($request, $response, Response::INTERNALSERVERERROR, 'Internal Server Error', 'Internal Server Error');
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
