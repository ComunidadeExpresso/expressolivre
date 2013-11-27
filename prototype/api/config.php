<?php

namespace prototype\api;

class Config 
{
	static $register;

	static function module($config , $module = false)
	{
		//Todo: registrar na nova api o currentapp
		if(!$module)
				$module =  $_SESSION['flags']['currentapp'];
  
		if( !isset( $_SESSION['config'][$module] ) || !isset( $_SESSION['config'][$module][$config] ))
				$_SESSION['config'][$module] = parse_ini_file( ROOTPATH."/config/$module.ini", true );
        
		return isset($_SESSION['config'][$module][$config]) ? $_SESSION['config'][$module][$config] : false;
     
	}
    
	static function me($config)
	{
        
		return isset($_SESSION['wallet']['user'][$config]) ? $_SESSION['wallet']['user'][$config] : false;
	}
    
	static function service( $service , $config )
	{
		if( !isset( $_SESSION['wallet'][$service] ) || !isset( $_SESSION['wallet'][$service][$config] ))
				$_SESSION['wallet'][$service] = parse_ini_file( ROOTPATH."/config/$service.srv", true );
       
		return (isset($_SESSION['wallet'][$service][$config])) ? $_SESSION['wallet'][$service][$config] : false;
	}
    
	static function get( $concept , $config = false , $module = false )
	{
		$load = parse_ini_file( ROOTPATH."/config/$concept.ini", true );
        
		if($config === false) return $load;
        
		return (isset($load[$config])) ? $load[$config] : false;
	}
    
	static function regSet( $name , $value)
	{
		self::$register[$name] = $value;
	}
	static function regGet ($name )
	{
		return (isset(self::$register[$name]) ? self::$register[$name] : false );
	}

	static function init( )
	{
    
		if( !defined( 'ROOTPATH' ) )
				define( 'ROOTPATH', dirname(__FILE__).'/..' );
        
		if ( isset( $_COOKIE[ 'sessionid' ] ) ) 
		{
				session_id( $_COOKIE[ 'sessionid' ] );
				if ( isset($GLOBALS['phpgw']) && !is_null($GLOBALS['phpgw']->session) )
					$GLOBALS['phpgw']->session->sessionid = $_COOKIE[ 'sessionid' ];
		}
		
	    if( !isset($_SESSION) )
            session_start();
		
	}

    
	public static function writeIniFile($assoc_arr, $path, $has_sections)
	{
		$content = '';	
		self::_writeIniFile($content, $assoc_arr, $has_sections);
		if( file_put_contents($path, $content) === false)
		{
			trigger_error("Permission failure when trying to write in the file: $path ", E_USER_WARNING);
			return false;
		}
		return true;
	}

	private static function _writeIniFile(&$content, $assoc_arr, $has_sections)
	{
		foreach ($assoc_arr as $key => $val)
		{
			if (is_array($val))
			{
				if($has_sections)
				{
					$content .= "[$key]\n";
					self::_writeIniFile($content, $val, false);
				}
				else				
					foreach($val as $iKey => $iVal)
					{
						if (is_int($iKey))
							$content .= $key ."[] = $iVal\n";
						else
							$content .= $key ."[$iKey] = $iVal\n";
					}
			}
			else
				$content .= "$key = $val\n";
		}
	}

}

	Config::init();
	

?>
