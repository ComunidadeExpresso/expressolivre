<?php
/**
 *
 * Copyright (C) 2012 Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY FUNAMBOL, FUNAMBOL DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT  OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program; if not, see www.gnu.org/licenses or write to
 * the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301 USA.
 *
 * This code is based on the OpenXchange Connector and on the Prognus pSync
 * Connector both developed by the community and licensed under the GPL
 * version 2 or above as published by the Free Software Foundation.
 *
 * You can contact Prognus Software Livre headquarters at Av. Tancredo Neves,
 * 6731, PTI, Edifício do Saber, 3º floor, room 306, Foz do Iguaçu - PR - Brasil or at
 * e-mail address prognus@prognus.com.br.
 *
 * Classe de controle que faz manipulações de fluxo de informações para toda
 * a API a partir de vários métodos. 
 *
 * @package    Prototype
 * @license    http://www.gnu.org/copyleft/gpl.html GPL
 * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
 * @version    2.4
 * @sponsor    Caixa Econômica Federal
 * @since      Arquivo disponibilizado na versão 2.4
 */

if( !defined( 'ROOTPATH' ) )
    define( 'ROOTPATH', dirname(__FILE__).'/..' );

require_once(ROOTPATH.'/api/config.php');
use prototype\api\Config as Config;
/**
TODO list:

  * definir de forma centralizada os caminhos e as constantes necessÃ¡rias;
  * criar um User Agent detect e um OS server detect para customizaÃ§Ãµes espeÃ§Ã­ficas de cada browser / servidor;
  * criar um registrador para fallback handlers;
  * criar um dependency manager na configuraÃ§Ã£o dos serviÃ§os, para poder gerenciar os imports corretamente
  * criar um login e a recuperaÃ§Ã£o da sessÃ£o;

*/

/**
 *
 * @package    Prototype
 * @license    http://www.gnu.org/copyleft/gpl.html GPL
 * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
 * @version    2.4
 * @sponsor    Caixa Econômica Federal
 * @since      Classe disponibilizada na versão 2.4
 */
class Controller {

	static $cache;
	static $services = array();
	static $interceptors = array();
	static $config = array();
	static $includes = array();
	static $tx = array();
	static $isConcept = array();
	static $hasOne = array();
	static $fallbackHandlers = array();
	static $txID = 0;
	static $wallet;

	public function __destruct()
	{
// 	    if( $this->service )
// 		$this->service->close();
// 	    else
	    self::closeAll();
	}

	public static function closeAll()
	{
	    if( self::$services )
		foreach( self::$services as $serviceName => $service )
		    if( self::$config[ $serviceName ]['type'] === 'service' )
		      $service->close();
	}

	public static function clearAll()
	    {
	    return self::$cache->clearAll();
	    }

	public static function clear( $id )
	{
	    return self::$cache->clear( $id );
	}

	public static function check( $id )
	{
	    return self::$cache->get( $id );
	}

	public static function store( $id, $data, $expires, $compressed )
	{
	    return self::$cache->put( $id, $data, $expires, $compressed );
	}

	public static function find( $URI, $params = false, $criteria = false )
	{
	    if( isset($URI['id']) && $URI['id'] )
		return self::read( $URI, $params, $criteria );
	    
	    return self::call( 'find', $URI, $params, $criteria );
	}

	public static function read( $URI, $params = false, $criteria = false )
	{
	    if( !isset($URI['id']) || !$URI['id'] )
		return self::find( $URI, $params, $criteria );

	    return self::call( 'read', $URI, $params, $criteria );
	}

	public static function deleteAll( $URI, $params = false, $criteria = false )
	{
	    if( isset($URI['id']) && $URI['id'] )
		return self::delete( $URI, $params, $criteria );

	    return self::call( 'deleteAll', $URI, $params, $criteria );
	}

	public static function delete( $URI, $params = false, $criteria = false )
	{
	    if( !isset($URI['id']) || !$URI['id'] )
		return self::deleteAll( $URI, $params, $criteria );

	    return self::call( 'delete', $URI, $params, $criteria );
	}

	public static function replace( $URI, $params, $criteria = false )
	{
	    if( isset($URI['id']) && $URI['id'] )
		return self::update( $URI, $params, $criteria );

	    return self::call( 'replace', $URI, $params, $criteria );
	}

	public static function update( $URI, $params, $criteria = false )
	{
	    if( !isset($URI['id']) || !$URI['id'] )
		return self::replace( $URI, $params, $criteria );

	    return self::call( 'update', $URI, $params, $criteria );
	}

	public static function create( $URI, $params, $criteria = false )
	{
	    return self::call( 'create', $URI, $params, $criteria );
	}

	public static function begin( $URI, $params = false, $criteria = false )
	{
	    return self::call( 'begin', $URI, $params, $criteria );
	}

	public static function commit( $URI, $criteria = false )
	    {
	    return self::call( 'commit', $URI, false, $criteria );
	}

	public static function rollback( $URI, $criteria = false )
	{
	    if( isset( $URI['service'] ) )
		unset( self::$tx[ $URI['service'] ] );

	    self::$txID--;

	    return self::call( 'rollback', $URI, false, $criteria );
	}

	public static function format( $URI, $params, $criteria = false )
	{
	    return self::call( 'format', $URI, $params, $criteria );
	}

	public static function parse( $URI, $data, $criteria = false )
	{
	    return self::call( 'parse', $URI, $data, $criteria );
	    }

	public static function URI( $className, $id = false, $service = false )
	{
	    return array( 'concept' => $className,
			  'service' => $service ? $service : false, 
			  'id' => $id ? $id : '' );
	}

	//TODO: Compatibilizar as configs relativas aos modulos, adicionando os mesmo nos parametros passados
	public static function links( $concept = false, $linkage = false )
	{
	    

	    if( !isset(self::$config[ $concept ]) )
	      self::$config[ $concept ] = self::loadConfig( $concept );

	    $links = array();
	    self::$isConcept[ $concept ] = array();
	    self::$hasOne[ $concept ] = array();

	    if( isset(self::$config[ $concept ][ 'model.hasOne' ]) )
		foreach( self::$config[ $concept ][ 'model.hasOne' ] as $linkName => $linkTarget )
		{
		    list( $target, $link ) = explode( '.', $linkTarget );

		    if( $linkage === $linkName )
			$return = $link;

		    $links[$linkName] = $target;
		    self::$hasOne[ $concept ][ $linkName ] = true;
		}
	    if( isset(self::$config[ $concept ][ 'model.depends' ]) )
		foreach( self::$config[ $concept ][ 'model.depends' ] as $linkName => $linkTarget )
		{
		    list( $target, $link ) = explode( '.', $linkTarget );

		     if( $linkage === $linkName )
			$return = $link;

		    $links[$linkName] = $target;
		    self::$hasOne[ $concept ][ $linkName ] = true;
		    self::$isConcept[ $concept ][ $linkName ] = true;
		}
	    if( isset(self::$config[ $concept ][ 'model.hasMany' ]) )
		foreach( self::$config[ $concept ][ 'model.hasMany' ] as $linkName => $linkTarget )
		{
		    list( $target, $link ) = explode( '.', $linkTarget );

		     if( $linkage === $linkName )
			$return = $link;

		    $links[$linkName] = $target;
		}

	    return( isset($return) ? $return : $links );
	}

	public static function isConcept( $concept, $linkName )
	{ 
	    if( !isset( self::$isConcept[ $concept ] ) )
		self::links( $concept );

	    return( isset(self::$isConcept[ $concept ][ $linkName ]) );
	}

	public static function hasOne( $concept, $linkName )
	{ 
	    if( !isset( self::$hasOne[ $concept ] ) )
		self::links( $concept );

	    return( isset(self::$hasOne[ $concept ][ $linkName ]) );
	}

	public static function getConcept( $concept, $moduleName = false )
	{
	    if( isset( self::$config[ $concept ] ) )
		return( self::$config[ $concept ] );

	    return( self::$config[ $concept ] = self::loadConfig( $concept, $moduleName ) );
	}

	public static function loadCache( $cacheType = 'Memory' )
	{
	    include_once( "cache/MemoryCache.php" );
	    return new MemoryCache();
	}

	//TODO: Compatibilizar as configs relativas aos modulos, adicionando os mesmo nos parametros passados
	public static function loadConfig( $className, $isService = false)
	{
	    $fileName = $className.'.'.($isService ? 'srv' : 'ini');

	    $config = self::$cache->get( $fileName );
        
	    if( !$config )
	    {
                $config = parse_ini_file( ROOTPATH.'/config/'.$fileName, true );

		self::$cache->put( $fileName, $config );
	    }

	    return( $config );
	}

	public static function import( $path, $ext = ".php" )
	{ 
	    if( !isset(self::$includes[$path]) )
	{
		require_once( ROOTPATH.'/'.$path.$ext );
		self::$includes[$path] = false;
	    }

	    return( self::$includes[$path] );
	}

	public static function load( $path, $class = false )
	    { 
            if( $return = self::import( $path, "" ) )
		return( $return );

	    if( !$class ){
		preg_match( '/^\/?.*\/([^\/]+).php$/', $path, $class );
		$class = $class[1];
	    }

	    $object =  self::$cache->get( $class );

	    if( !$object )
	    {
		$object = new $class();
		 self::$cache->put( $class, $object );
	    }

	    self::$includes[$path] = $object;

	    return( $object );
	}

	public static function wallet( $serviceName )
	{
	    if( !isset( self::$wallet ) )
	    {
		//// Hack //// TODO: passar o init da sessÃ£o no login do expresso
		Config::init();

                if(isset($_SESSION['wallet']))
                    self::$wallet = $_SESSION['wallet'];
		/////////////
	    }

	    return isset( self::$wallet[ $serviceName ] )? self::$wallet[ $serviceName ] : false;
	}
		
	public static function connect( $service, $config )
	    {
	    $result = $service->open( $config );

	    if( is_string( $result ) )
		throw new Exception( $result );

	    return( true );
	}

	public static function configure( $config, $newConfig )
	{
	    foreach( $newConfig as $key => $value )
		$config[$key] = $value;

	    return( $config );
	    }

	public static function dispatch( $dispatcher, $data, $optionsMap = false )
	{
// 	    if( $mappedTo )
// 		$data = array( $mappedTo => $data );
// 
// 	    foreach( $data as $method => $params )
// 	    {
// // 		foreach( $data[ $method ] as $name => $value )
// 	    }
// 
// 	    self::import( "$dispatcher.php" );
	}

	//TODO: Compatibilizar as configs relativas aos modulos, adicionando os mesmo nos parametros passados
	public static function service( $serviceName, $concept = false )
	{
	    if( isset( self::$services[ $serviceName ] ) )
		return self::$services[ $serviceName ];

	    if( !isset(self::$config[ $serviceName ]) )
		 self::$config[ $serviceName ] = self::loadConfig( $serviceName, true );

	    if( !isset(self::$config[ $serviceName ]) )
		return( false );

	    if( !isset(self::$config[ $serviceName ]['type']) )
		self::$config[ $serviceName ]['type'] = 'service';

	    self::import( 'api/'.self::$config[ $serviceName ]['type'] );   //TODO: Item 4

	    $service = self::load( self::$config[ $serviceName ]['path'],
				   self::$config[ $serviceName ]['class'] );

	      $srvConfig = array();

	    if( isset(self::$config[ $serviceName ][ 'config' ]) )
		$srvConfig = self::configure( $srvConfig, self::$config[ $serviceName ][ 'config' ] );
	    if( $wallet = self::wallet( $serviceName ) )
		$srvConfig = self::configure( $srvConfig, $wallet );
	    if( $concept && isset(self::$config[ $concept ]['service.config']) )
		$srvConfig = self::configure( $srvConfig, self::$config[ $concept ]['service.config'] );

	    if( empty( $srvConfig ) )
		$srvConfig = false;

	    if( $service && self::$config[ $serviceName ]['type'] === 'service' )
		self::connect( $service, $srvConfig );

	    return( self::$services[ $serviceName ] = $service );
	}

	//TODO: Compatibilizar as configs relativas aos modulos, adicionando os mesmo nos parametros passados
	public static function interceptor( $method, $concept = false, $serviceName = false, $isService = false )
	{
	    if( $concept && !isset(self::$config[ $concept ]) )
	      self::$config[ $concept ] = self::loadConfig( $concept );

	    if( !$concept ) $concept = 'global';
	    if( !$isService || !$serviceName ) $serviceName = 'global';

	    if( !isset( self::$interceptors[ $concept ] ) )
		self::$interceptors[ $concept ] = array();

	    if( !isset( self::$interceptors[ $concept ][ $serviceName ] ) )
		self::$interceptors[ $concept ][ $serviceName ] = array();

	    if( !isset( self::$interceptors[ $concept ][ $serviceName ][ $method ] ) )
	    {
		$events = array( 'before', 'after' );
		$interceptors = array();

		$prefix = ( $isService )? "$serviceName." : "";

		foreach( $events as $i => $event )
		{
		    $interceptors[$event] = array();

		    if( !isset(self::$config[$concept]["$prefix$event.$method"]) )
		      continue;

		    foreach( self::$config[$concept]["$prefix$event.$method"] as $intercept => $interceptor )
			    $interceptors[$event][$intercept] = self::load( $interceptor );
		}

		self::$interceptors[ $concept ][ $serviceName ][ $method ] = $interceptors;
	    }

	    return( self::$interceptors[ $concept ][ $serviceName ][ $method ] );
	}

	public static function interceptorCommit( $eventType, $commitList, $isService = false )
	{
	    $result = array( $eventType => array() );
        
	    if( is_array( $commitList ) )
	        foreach( $commitList as $i => $tx )
	        {
		    $interceptors = self::interceptor( 'commit', $tx['concept'], $tx['service'], $isService );
      
		    $result[$eventType] = array_merge( $result[$eventType], $interceptors[$eventType] );
	        }

	    return( $result );
	}

	public static function fire( $eventType, $method, &$params, $original, $isService = false )
	{
	    if( $method === 'commit' )
		$interceptors = self::interceptorCommit( $eventType, $params['criteria'], $isService );

	    else
		$interceptors = self::interceptor( $method,
						   isset($original['URI']['concept']) ? $original['URI']['concept'] : false,
						   isset($params['URI']['service']) ? $params['URI']['service'] : false, $isService );

	    if( $interceptors && isset($interceptors[ $eventType ]) )
		foreach( $interceptors[ $eventType ] as $intercept => $interceptor )
		{
		    $return = $interceptor->$intercept( $params['URI'], $params['properties'], $params['criteria'], $original /*, $params['service'] */);

		    if( $return === false )
			return( false );

		    if( isset($return) )
			$params['properties'] = $return;
		}

	      return( $params );
	}

	/*
	  * ex: array
	  *		(
	  *			[0] array( 'OR', array( array( '=', 'campo', 'valor' ), 
							  array( 'OR', array( array( '=', 'campo', 'valor' ) ) ) )
	  *			[1] array( '=', 'campo' , 'valor' )
	  *			[2] array( 'OR' , array( array( '=' , campo', 'valor' ) ) )
	  *			[3] array( 'IN', 'campo', array( '1' , '2' , '3' ) )
	  *		)
	  * OR
	  *	    array( '=' , 'campo' , 'valor' )
	*/

	//TODO: Compatibilizar as configs relativas aos modulos, adicionando os mesmo nos parametros passados
	public static function serviceName( $URI, $original = false )
	{
	     $concept = "";

	    if( $original && isset($original['concept']) && $original['concept'] )
		$concept = $original['concept'];
	    elseif( isset($URI['concept']) && $URI['concept'] )
		$concept = $URI['concept'];

	    if( ( !isset($URI['service']) || !$URI['service'] ) && $concept )
	    {
		if( !isset(self::$config[ $concept ]) )
		    self::$config[ $concept ] = self::loadConfig( $concept );

		$URI['service'] = self::$config[ $concept ][ 'service' ];
	    }

	    if( !isset($URI['service']) )
		throw new Exception( "CONFIGURATION ERROR: service name from concept '$concept' not found" );

	    return( $URI );
	}
	
	public static function finalizeCommit( $method, $params, $original, $TX = array() )
	{
	    if( $TX !== false )
	    {
		$TX['rollback'] = !!!$params['properties'];

		if( $params['properties'] && is_array($params['properties']) && isset($params['properties']['id']) )
		    $TX['id'] = $params['properties']['id'];

		self::$tx[ $params['URI']['service'] ][] = array_merge( $TX, $original['URI'], array( 'service' => $params['URI']['service'], 'method' => $method ) );
	    }

	    return( empty($params['properties']) ? false : $params['properties'] );
	}

	//TODO: Compatibilizar as configs relativas aos modulos, adicionando os mesmos nas options passadas
	public static function call( $method, $URI, $properties = false, $options = false, $service = false )
	{
	    try
	    {
		if( !isset($URI['concept']) ) $URI['concept'] = false;

		$original = $params = array( 'properties' => $properties, 
				             'criteria' => $options, 
					     'URI' => $URI,
					     'service' => $service );

		if( isset($params['URI']['concept'])  && !self::fire( 'before', $method, $params, $original ) )
		   return( self::finalizeCommit( $method, $params, $original ) );

		if( $params && !$params['service'] )
		{
		    $params['URI'] = self::serviceName( $params['URI'], $original['URI'] );

		    $params['service'] = self::service( $params['URI']['service'], $params['URI']['concept'] );
		}

		if( isset($params['URI']['service']) )
		{
		    if( $method === 'create' || $method === 'update' || $method === 'delete' )
		    {
			if( $commit = !isset(self::$tx[ $params['URI']['service'] ])  )
			{
			    self::call( 'begin', $params['URI'] );
			}

			$TX = array();
		    }

		    if( !self::fire( 'before', $method, $params, $original, true ) )
			return( self::finalizeCommit( $method, $params, $original, isset($TX) ? $TX : false ) );
		}

		if( $params['service'] )
		    switch( $method )
		    { 
			case 'find': $return = $params['service']->find( $params['URI'], $params['properties'], $params['criteria'] ); break;

			case 'read': $return = $params['service']->read( $params['URI'], $params['properties'] , $params['criteria'] ); break;

			case 'create': $return = $params['service']->create( $params['URI'], $params['properties']/*, $criteria*/ ); break;

			case 'delete': $return = $params['service']->delete( $params['URI'], $params['properties'], $params['criteria'] ); break;

			case 'deleteAll': $return = $params['service']->deleteAll( $params['URI'], $params['properties'], $params['criteria'] ); break;

			case 'update': $return = $params['service']->update( $params['URI'], $params['properties'], $params['criteria'] ); break;

			case 'replace': $return = $params['service']->replace( $params['URI'], $params['properties'], $params['criteria'] ); break;

			case 'begin': $return = $params['service']->begin( $params['URI'] ); break;

			case 'commit': $return = $params['service']->commit( $params['URI'], $params['criteria'] ); break;

			case 'rollback': $return = $params['service']->rollback( $params['URI'], $params['criteria'] ); break;

			case 'parse': $return = $params['service']->parse( $params['properties'], $params['criteria'] ); break;

			case 'analize': $return = $params['service']->analize( $params['properties'], $params['criteria'] ); break;

			case 'format': $return = $params['service']->format( $params['properties'], $params['criteria'] ); break;

			default : $return = $params['service']->$method( $params['properties'], $params['criteria'] );
		    }

		if( isset($return) && $return !== false )
		    $params['properties'] = $return;

		if( isset($params['URI']['service']) )
		    if( !self::fire( 'after', $method, $params, $original, true ) )
			return( self::finalizeCommit( $method, $params, $original, isset($TX) ? $TX : false ) );

		if( isset($URI['concept']) )
		    self::fire( 'after', $method, $params, $original );

		if( empty($params['properties']) )
		    $params['properties'] = false;

		if( isset( $TX ) )
		{
		    //self::finalizeCommit( $params, $original, $method, $TX );
		    self::finalizeCommit( $method, $params, $original, $TX );
		    if( isset($commit) && $commit )
		    {
			if( !self::call( 'commit', $params['URI'], false, self::$tx[ $params['URI']['service'] ] ) )
			    self::call( 'rollback', $params['URI'] , false, self::$tx[ $params['URI']['service'] ] );

			unset( self::$tx[ $params['URI']['service'] ] );
		    }
		}
	    }
	    catch( Exception $e )
	    {
		if( !self::fallback( $e, $URI ) )
		    self::closeAll();

		return( false );
	    }

	    return( $params['properties'] ); 
	}

	public static function fallback( $exception, $URI )
	{
	    $code = $exception->getCode();

	    if( isset( self::$fallbackHandlers[ $code ] ) )
		{
			$fn = self::$fallbackHandlers[ $code ];
			return $fn( $exception, $URI );
		}

	    error_log( $exception->getMessage() );
	    return( true );
	}

	public static function addFallbackHandler( $code, $function )
	{
	    self::$fallbackHandlers[ $code ] = $function;
	}
	/*
	 *NULL evita erros caso nÃ£o seja passado nenhuma variavel por referÃªncia
	*/
	public static function put( $URI, $data, &$txIds = NULL )
	{
	    try
	    {
		$URI = self::serviceName( $URI );

		if( $commit = !$txIds )
		    $txIds = array();

		if( !isset( self::$tx[ $URI['service'] ] ) )
		{
		    self::call( 'begin', $URI );
		    self::$tx[ $txIds[] = $URI['service'] ] = array();
		}

		$method = $data ? isset( $data['id'] ) ?
			  'update' : 'create' : 'delete';

		$links = self::links( $URI['concept'] );

		$order = self::$txID++;

		$postpone = array();
		$linkNames = array();

		if( $data )
		{
		    $URI['id'] = isset( $data['id'] ) ? $data['id'] : false;

		    foreach( $links as $linkName => $linkTarget )
		    {
			if( isset( $data[$linkName] ) && is_array( $data[$linkName] ) )
			{
				if( self::isConcept( $URI['concept'], $linkName ) )
				    $data[$linkName] = self::put( array( 'concept' => $linkTarget ), $data[$linkName], $txIds );
			    else
			    {
				    $postpone[ $linkTarget ] =  $data[$linkName];
				    $linkNames[ $linkTarget ] = $linkName;
			    }
			}
		    }
		}
		else
		  $URI['id'] = isset( $data['id'] ) ? $data['id'] : $URI['id'];

		$result = Controller::call( $method, $URI, $data, false, false, true );

		if( is_array( $result ) && isset( $result['id'] ) )
		      $URI['id'] = $result['id'];

		$index =  count(self::$tx[ $URI['service'] ]) - 1;

		self::$tx[ $URI['service'] ][ $index ]['order'] = $order;
		self::$tx[ $URI['service'] ][ $index ]['id'] = $URI['id'];

		if( !isset(self::$tx[ $URI['service'] ][ $index ]['concept']) )
		    self::$tx[ $URI['service'] ][ $index ]['concept'] = $URI['concept'];

		foreach( $postpone as $linkTarget => $dt )
		{
		      if( Controller::hasOne( $URI['concept'], $linkNames[ $linkTarget ] ) )
			  $dt = array( $dt );

		      foreach( $dt as $ii => $value )
		      {
			  if( !is_array( $value ) )
			    $value = array( 'id' => $value );

			  $value[ self::links( $URI['concept'], $linkNames[ $linkTarget ] ) ] = $URI['id'];
  
			  self::put( array( 'concept' => $linkTarget ), $value, $txIds );
		      }
		}
		if( $commit )
		{
		      $result = array();

		      for( $i = count( $txIds ) - 1; $i >= 0; $i-- )
		      {
			      $currentTx = self::$tx[ $txIds[$i] ];
			      unset( self::$tx[ $txIds[$i] ] );

			      if( !self::commit( array( 'service' => $txIds[$i] ), $currentTx ) )
			      {
				  self::rollback( array( 'service' => $txIds[$i] ), $currentTx );

				  foreach( $currentTx as $i => $st )
				      $currentTx[$i][ 'rollback' ] = true;
			      }

			      $result = array_merge( $result, $currentTx );
		      }

		      self::$txID = 0;

		      return( $result );
		}

	    }
	    catch( Exception $e )
	    {
		if( !self::fallback( $e, $URI ) )
		    self::closeAll();

		return( false );
	    }
	
	    return( $URI['id'] );
	}
	
	public static function get()
	{
	
	}
}

Controller::$cache = Controller::loadCache();

require_once(__DIR__.'/esecurity.php');
$s = new ESecurity();
$s->valid();


// ?>
