<?php
/**
*
* Copyright (C) 2011 Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
*
*  This program is free software; you can redistribute it and/or 
*  modify it under the terms of the GNU General Public License 
*  as published by the Free Software Foundation; either version 2 
*  of the License, or (at your option) any later version. 
*   
*  This program is distributed in the hope that it will be useful, 
*  but WITHOUT ANY WARRANTY; without even the implied warranty of 
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
*  GNU General Public License for more details. 
*   
*  You should have received a copy of the GNU General Public License 
*  along with this program; if not, write to the Free Software 
*  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  
*
* You can contact Prognus Software Livre headquarters at Av. Tancredo Neves,
* 6731, PTI, Bl. 05, Esp. 02, Sl. 10, Foz do Iguaçu - PR - Brasil or at
* e-mail address prognus@prognus.com.br.
*
* @package    expressoMail
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @since      Arquivo disponibilizado na versão 2.2
*/

//Helper function and defines that must be moved to a common area
/////////////////////////////////////////////////////
if(!function_exists('define_once') )
{
    function define_once( $constant, $value )
    {
        if( !defined( $constant ) )
        {
            define( $constant, $value );
        }
    }
}

define_once( 'ROOT', dirname(__FILE__).'/..' );

define_once( 'SERVICES', ROOT.'/services/' );

define_once( 'LIBRARY', ROOT.'/library/' );

define_once( 'API', ROOT.'/API/' );

if( !class_exists('ServiceLocator') ){

/////////////////////////////////////////////////////
/**
* Faz a localização dos serviços que serão utilizados pela aplicação e que estão localizados na API
*
* @package    expressoMail
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @version    1.0
*/
class ServiceLocator
{
    static $locators = array();

    static $cache = array();

    //$service = null;

    //$serviceName = null;

//     static $empty_locator = new ServiceLocator( 'empty' );

    
	/**
	* Carrega a configuração
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $configuration
	*/
	static function load( $configuration )
    {
	$configuration = SERVICES.$configuration;

	if( !file_exists ( $configuration ) )
	return( false );

	$configuration = parse_ini_file( $configuration );

	foreach( $configuration as $serviceType => $serviceName )
	{
	    self::deploy( $serviceType, $serviceName );
	}
    }

	
	/**
	* Deploy
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $serviceType
	* @param      $serviceName
	*/
    static function deploy( $serviceType, $serviceName = null )
    {
	require_once( SERVICES."class.".$serviceType.".php" );

	if( $serviceName )
	{
	    self::register( $serviceType, new $serviceName() );
	}

	return( self::$locators[ $serviceType ] );
    }


	/**
	* make all the treatment of
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $service
	* @param      $object
	*/
    static function register( $service, $object )
    {
	self::$locators[ $service ] = new ServiceLocator( $service, $object );

	self::configure( $service );

	return( self::$locators[ $service ] );
    }

	
	/**
	* unregister service
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $service
	*/
    static function unregister( $service )
    {
	$old = self::$locators[ $service ];

	unset( self::$locators[ $service ] );

	return( $old );
    }

    /**
	* configure
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $service
	*/
    static function configure( $service )
    {
	return( null );
    }

	/**
	* locate
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $service
	* @param      $arguments
	* @param      $target
	*/
    static function locate( $service, $arguments = array(), $target = false )
    {
	if( !$target )
	{
	    list( $target, $service ) = explode( ".", $service );
	}

	if( !is_array( $arguments ) )
	{
	    $arguments = array( $arguments );
	}

	$locator = self::$locators[ $target ];

	if( !$locator )
	{
	    $locator = self::deploy( $target );
	}
	if( !$locator )
	{
	    return( false );
	}

	try
	{
	    return $locator->proxy( $service, $arguments );
	}
	catch( Exception $e )
	{
	    //Implement the exception stack to the fallback handlers treat correctly
	    //by now - fly it
	}
	
	return( null );
    }

	
	/**
	* construct
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $serviceName
	* @param      $object
	*/
    function __construct( $serviceName, $object )
    {
	$this->service = $object;
	$this->serviceName = $serviceName;
    }

	
	/**
	* proxy
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $method
	* @param      $arguments
	*/
    function proxy( $method, $arguments )
    {
	//handle here cases of miss of methods
	if( !method_exists( $this->service, $method ) ) return( false );

	$many = count( $arguments );

        if(isset(self::$cache[$many]))
	$proxy = self::$cache[$many];

        
	if( !isset($proxy) || !$proxy )
	{
	    $params = array();

	    for( $i = 0; $i < $many; $params[] = '$params['.$i++.']' );

	    $proxy = create_function( '$method, $params, $obj', 'return $obj->$method('.implode( ', ', $params ).');' );

	    self::$cache[$many] = $proxy;
	}

	if( !isset( $arguments ) ) return( $proxy );

	return $proxy( $method, $arguments, $this->service );
    }

	
	/**
	* call
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	*/
    function call()
    {
	$arguments = func_get_args();

	$service = array_pop( $arguments );

	//handle here dispatch with invalid services
	if( $this )
	{
	    $service = strrpos( $service, "." ) ? $service : $this->serviceName.".".$service;
	}

	return self::locate( $service, $arguments );
    }

	
	/**
	* dispatch
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $service
	* @param      $arguments
	*/
    function dispatch( $service, $arguments )
    {
	//handle here dispatch with invalid services
	if( $this )
	{
	    $service = strrpos( $service, "." ) ? $service : $this->serviceName.".".$service;
	}

	return self::locate( $service, $arguments );
    }

	
	/**
	* call
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $method
	* @param      $arguments
	*/
    function __call( $method, $arguments )
    {
	return self::locate( $method, $arguments, $this->serviceName );
    }

	
	/**
	* get
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $name
	*/
    function __get( $name )
    {
	return $this->service->$name;
    }

	
	/**
	* call static
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $method
	* @param      $arguments
	*/
    static function __callStatic( $method, $arguments )
    {
	return self::locate( $method, $arguments );
    }

	
	/**
	* get service
	*
	* @license    http://www.gnu.org/copyleft/gpl.html GPL
	* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
	* @param      $service
	*/
    static function getService( $service )
    {
	if( !isset(self::$locators[ $service ]) )
	{
	    self::deploy( $service );
	}

	return( clone self::$locators[ $service ] );
    }
}

}

//estudar uma forma mais elegante de carregar os servicos
//ServiceLocator::load( "services.conf" );

?>
