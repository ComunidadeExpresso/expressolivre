<?php

if( !defined( 'ROOTPATH' ) )
    define( 'ROOTPATH', dirname(__FILE__).'/..' );

require_once(ROOTPATH.'/api/config.php');
use prototype\api\Config as Config;

/**
TODO list:

  * definir de forma centralizada os caminhos e as constantes necessárias;
  * criar um User Agent detect e um OS server detect para customizações espeçíficas de cada browser / servidor;
  * criar um registrador para fallback handlers;
  * criar um dependency manager na configuração dos serviços, para poder gerenciar os imports corretamente
  * criar um login e a recuperação da sessão;

*/

class Controller {

	static $cache;
	static $services = array();
	static $interceptors = array();
	static $config = array();
	static $includes = array();
	static $tx = array();
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

	

// 	public static function read( $concept, $id = false, $options = false )
// 	{
// 	    if( !isset($URI['id']) || !$URI['id'] )
// 		return self::find( $URI, $params, $criteria );
// 
// 	    return self::call( 'read', $URI, $params, $criteria );
// 	}

// 	public static function deleteAll( $URI, $params = false, $criteria = false )
// 	{
// 	    if( isset($URI['id']) && $URI['id'] )
// 		return self::delete( $URI, $params, $criteria );
// 
// 	    return self::call( 'deleteAll', $URI, $params, $criteria );
// 	}

	// 	public static function replace( $URI, $params, $criteria = false )
// 	{
// 	    if( isset($URI['id']) && $URI['id'] )
// 		return self::update( $URI, $params, $criteria );
// 
// 	    return self::call( 'replace', $URI, $params, $criteria );
// 	}

	public static function find( $concept, $options )
	{    
	    return self::get( $options['filter'],  self::context( $options, array( 'concept' => $concept ) ) );
	}

	public static function delete( $concept, $id = false, $options = array() )
	{
	    return self::put( false, self::context( $options, array( 'concept' => $concept, 'id' => $id ) ) );
	}

	public static function update( $concept, $data, $id = false, $options = array() )
	{
	    return self::put( $data, self::context( $options, array( 'concept' => $concept, 'id' => $id ) ) );
	}

	public static function create( $concept, $data, $options = array() )
	{
	    return self::put( $data, self::context( $options, array( 'concept' => $concept ) ) );
	}

	public static function put( $data, $options )
	{
	    try
	    {
		$context = self::context( $options );

		$txId = self::begin( $context['service'] );

		if( $context['format'] )
		    $data = self::parse( $data, $context['format'], $options );

		if( !isset($options['concept']) )
		{
		    $return = array();

		    for( $data as $concept => $dt )
			 $return[] = self::put( $dt, array_merge( array( 'concept' => $concept ), $options ) );
			
		    return $return;
		}


		$model = self::$models[ $options['concept'] ];

		$postpone = array();

		if( $data )
		{
		    foreach( $model['hasMany'] as $linkName => $linkTarget )
		    {
			$postpone[$linkTarget] = $dt[$linkName];
		    }
		    foreach( $model['hasOne'] as $linkName => $linkTarget )
		    {
			if( isset( $dt[$linkName] ) && is_array( $dt[$linkName] ) )
			    $dt[$linkName] = self::put( $dt[$linkName],  array_merge( array( 'concept' => $linkTarget ), $options ) );
		    }
		}

		$method = 	 $dt ? isset( $dt['id'] ) ?
				'update' : 'create' : 'delete';

		$context['id'] = $dt ? isset( $dt['id'] ) ?
				 $dt['id'] : false : $context['id'];

		self::before( $concept.':'.$method, &$context, $options );
		self::call( $method, $options, $dt );
		self::after( $concept.':'.$method, &$context, $options );

		$result = $context['result'];

		if( !is_bool( $result ) && !is_string( $result ) && isset( $result['id'] ) )
		      $context['id'] = $result['id'];

		foreach( $postpone as $linkTarget => $dt )
		      foreach( $dt as $ii => $value )
		      {
			  if( !is_array( $value ) )
			    $value = array( 'id' => $value );

			  $value[ $options['concept'] ] = $options['id'];

			  self::put( $value, array_merge( array( 'concept' => $linkTarget ), $options ) );
		      }

		if( $txId )
		    return self::commit( $options['service'], $txId );
	    }
	    catch( Exception $e )
	    {
		if( !self::fallback( $e ) )
		    self::closeAll();

		return( false );
	    }
	
	    return( $options['id'] );
	}

	public static function context( $options, $custom = false )
	{
	    if( $service )
		$options['service'] = $service;

	    return $options;
	}

	public static function get( $filter, $options = false )
	{
	    return self::call( 'find', self::context( $options, array( 'filter' => $filter ) ) );
	}

	public static function connect( $service, $options )
	{
	    $result = self::call( 'open', self::context( $options, array( 'service' => $service ) ) );

	    if( is_string( $result ) )
		throw new Exception( $result );

	    return( true );
	}

	public static function begin( $service, $txId = false, $options = false )
	{
	    $context = self::context( $options );

	    $result = self::call( 'begin', $options );

	    if( !$txId )
		$txId = $result ? $result : self::$txID++;

	    if( isset( self::$transactions[ $txId ] ) )
		return( false );

	    self::$transactions[ $txId ] = array( 'txID' => $txId );
	    self::$transactions[ $service ][] =& self::$transactions[ $txId ];

	    return( $txId );
	}

	public static function commit( $service, $txId = false, $options = false )
	{
	    $context = self::context( $options );

	    $txs = self::$transactions[ $service . ( $txId ? '.'.$txId : '' ) ];

	    if( !is_array( $txs ) ) $txs = array( $txs );

	    $return = array();

	    foreach( $txs as $tx )
	    {
		$txID = $tx['txID'];

		$result = false;

		if( !$options || !$options['rollback'] )
		    $result = self::call( 'commit', $context, $tx );

		if( !$result )
		    $result = self::call( 'rollback', $context, $tx );

		$return[ $txID ] = $result;

		unset( self::$transactions[ $txID ] );
	    }

	    return( $txId ? $return[ $txId ] : $return );
	}

	public static function rollback( $service, $txId = false, $options = array() )
	{
	    return self::commit( $service, $txId, self::context( $options, array( 'rollback' => true ) ) );
	}

	public static function fallback( $exception, $context ) // ver a melhor forma de tratar exceptions nesse caso
	{
	    if( !self::emmit( 'fallback', self::context( $context, array( 'exception' => $exception ) ), $exception ) )
		error_log( $exception->getMessage() );
 
	    return( true );
	}

	public static function format( $data, $service = false, $options = array() )
	{
	    return self::call( 'format', self::context( $options, array( 'service' => $service ) ), $data );
	}

	public static function parse( $data, $service = false, $options = array() )
	{
	    return self::call( 'parse', self::context( $options, array( 'service' => $service ) ), $data );
	}

	public static function before( $eventName, &$context, $extra = false )
	{
	    return self::emmit( 'before.'.$eventName, $context, $extra );
	}

	public static function after( $eventName, &$context, $extra = false )
	{
	    return self::emmit( 'after.'.$eventName, $context, $extra );
	}

	public static function emmit( $eventName, &$context, $extra = false )
	{
	    if( self::$listeners[ $eventName ] )
		return( false );

	    foreach( self::$listeners[ $eventName ] as $listen => $listener )
	    {
		 $return = $listener->$listen( $context, $extra );

		 if( $return === false )
		    return( true );

		 $context = self::context( $context, array( 'return' => $return ) );
	    }

	    return( $return );
	}

	//TODO: Compatibilizar as configs relativas aos modulos, adicionando os mesmos nas options passadas
	public static function call( $method, $options, $data = false ) //see how data fit in it
	{
	    try
	    {
		$context = self::context( $options, array( 'data' => $data ) );

		$service = $context['service'];

		if( $context['config'] )
		    self::connect( $service, $context['config'] );

		self::before( $service.'.'.$method, &$context, $options );

		if( self::$services[ $service ] )
		    switch( $method )
		    { 
			case 'find': $return = self::$services[ $service ]->find( $context['URI'], $context['properties'], $context['criteria'] ); break;

			case 'read': $return = self::$services[ $service ]->read( $context['URI'], $context['properties']/*, $criteria*/ ); break;

			case 'create': $return = self::$services[ $service ]->create( $context['URI'], $context['properties']/*, $criteria*/ ); break;

			case 'delete': $return = self::$services[ $service ]->delete( $context['URI'], $context['properties']/*, $criteria*/ ); break;

			case 'deleteAll': $return = self::$services[ $service ]->deleteAll( $context['URI'], $context['properties'], $context['criteria'] ); break;

			case 'update': $return = self::$services[ $service ]->update( $context['URI'], $context['properties']/*, $criteria*/ ); break;

			case 'replace': $return = self::$services[ $service ]->replace( $context['URI'], $context['properties'], $context['criteria'] ); break;

			case 'begin': $return = self::$services[ $service ]->begin( $context['URI'] ); break;

			case 'commit': $return = self::$services[ $service ]->commit( $context['URI'], $context['criteria'] ); break;

			case 'rollback': $return = self::$services[ $service ]->rollback( $context['URI'], $context['criteria'] ); break;

			case 'parse': $return = self::$services[ $service ]->parse( $context['properties'], $context['criteria'] ); break;

			case 'analize': $return = self::$services[ $service ]->analize( $context['properties'], $context['criteria'] ); break;

			case 'format': $return = self::$services[ $service ]->format( $context['properties'], $context['criteria'] ); break;

			default : $return = self::$services[ $service ]->$method( $context['properties'], $context['criteria'] );
		    }

		$context['return'] = $return;

		self::after( $service.'.'.$method, &$context, $options );
	    }
	    catch( Exception $e )
	    {
		if( !self::fallback( $e ) )
		    self::closeAll();

		return( false );
	    }

	    return( $context['return'] ); 
	}

// 	public static function URI( $className, $id = false, $service = false )
// 	{
// 	    return array( 'concept' => $className,
// 			  'service' => $service ? $service : false, 
// 			  'id' => $id ? $id : '' );
// 	}

	//TODO: Compatibilizar as configs relativas aos modulos, adicionando os mesmo nos parametros passados
// 	public static function links( $concept = false )
// 	{
// 	    if( !isset(self::$config[ $concept ]) )
// 	      self::$config[ $concept ] = self::loadConfig( $concept );
// 
// 	    return( isset(self::$config[ $concept ]['links']) ? 
// 			  self::$config[ $concept ]['links'] : array() );
// 	}

// 	public static function isConcept( $concept )
// 	{ 
// 	    if( isset( self::$config[ $concept ] ) && 
// 		self::$config[ $concept ] )
// 		return( true );
// 		else
// 		return file_exists( ROOTPATH."/config/$concept.ini" );
// 	}

// 	public static function getConcept( $concept, $moduleName = false )
// 	{
// 	    if( isset( self::$config[ $concept ] ) )
// 		return( self::$config[ $concept ] );
// 
// 	    return( self::$config[ $concept ] = self::loadConfig( $concept, $moduleName ) );
// 	}

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
		//// Hack //// TODO: passar o init da sessão no login do expresso
		Config::init();

                if(isset($_SESSION['wallet']))
                    self::$wallet = $_SESSION['wallet'];
		/////////////
	    }

	    return isset( self::$wallet[ $serviceName ] )? self::$wallet[ $serviceName ] : false;
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

// 	public static function interceptorCommit( $eventType, $commitList, $isService = false )
// 	{
// 	    $result = array( $eventType => array() );
//         
// 	    if( is_array( $commitList ) )
// 	        foreach( $commitList as $i => $tx )
// 	        {
// 		    $interceptors = self::interceptor( 'commit', $tx['concept'], $tx['service'], $isService );
//       
// 		    $result[$eventType] = array_merge( $result[$eventType], $interceptors[$eventType] );
// 	        }
// 
// 	    return( $result );
// 	}

// 	public static function fire( $eventType, $method, &$params, $original, $isService = false )
// 	{
// 	    if( $method === 'commit' )
// 		$interceptors = self::interceptorCommit( $eventType, $params['criteria'], $isService );
// 
// 	    else
// 		$interceptors = self::interceptor( $method,
// 						   isset($original['URI']['concept']) ? $original['URI']['concept'] : false,
// 						   isset($params['URI']['service']) ? $params['URI']['service'] : false, $isService );
// 
// 	    if( $interceptors && isset($interceptors[ $eventType ]) )
// 		foreach( $interceptors[ $eventType ] as $intercept => $interceptor )
// 		{
// 		    $return = $interceptor->$intercept( $params['URI'], $params['properties'], $params['criteria'], $original/*, $params['service']*/ );
// 
// 		    if( $return === false )
// 		return( false );
// 
// 		    if( isset($return) )
// 			$params['properties'] = $return;
// 		}
// 
// 	      return( $params );
// 	}

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
}

Controller::$cache = Controller::loadCache();
?>
