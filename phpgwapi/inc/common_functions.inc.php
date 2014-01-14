<?php
	 /**************************************************************************\
	 * eGroupWare API - phpgwapi loader                                         *
	 * This file written by Dan Kuykendall <seek3r@phpgroupware.org>            *
	 * and Joseph Engo <jengo@phpgroupware.org>                                 *
	 * Has a few functions, but primary role is to load the phpgwapi            *
	 * Copyright (C) 2000, 2001 Dan Kuykendall                                  *
	 * -------------------------------------------------------------------------*
	 * This library is part of the eGroupWare API                               *
	 * http://www.egroupware.org/api                                            * 
	 * ------------------------------------------------------------------------ *
	 * This library is free software; you can redistribute it and/or modify it  *
	 * under the terms of the GNU Lesser General Public License as published by *
	 * the Free Software Foundation; either version 2.1 of the License,         *
	 * or any later version.                                                    *
	 * This library is distributed in the hope that it will be useful, but      *
	 * WITHOUT ANY WARRANTY; without even the implied warranty of               *
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
	 * See the GNU Lesser General Public License for more details.              *
	 * You should have received a copy of the GNU Lesser General Public License *
	 * along with this library; if not, write to the Free Software Foundation,  *
	 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
	 \**************************************************************************/


	/****************************************************************************\
	 * Direct functions which are not part of the API classes                    *
	 * because they are required to be available at the lowest level.            *
	 \***************************************************************************/
	/*!
	 @collection_start direct functions
	 @abstract Direct functions which are not part of the API classes because they are required to be available at the lowest level.
	*/

	/*!
	 @function print_debug_subarray
	 @abstract Not to be used directly. Should only be used by print_debug()
	*/
	function print_debug_subarray($array)
	{
//		while(list($key, $value) = each($array))
		foreach($array as $key => $value)
		{
			if (is_array($value))
			{
				$vartypes[$key] = print_debug_subarray($value);
			}
			else
			{
				$vartypes[$key] = gettype($value);
			}
		}
		return $vartypes;
	}

	/*!
	 @function print_debug
	 @abstract print debug data only when debugging mode is turned on.
	 @author seek3r
	 @discussion This function is used to debugging data. 
	 @syntax print_debug('message', $somevar);
	 @example print_debug('this is some debugging data',$somevar);
	*/
	function print_debug($message,$var = 'messageonly',$part = 'app', $level = 3)
	{
//		if (($part == 'app' && EXP_DEBUG_APP == True) || ($part == 'api' && DEBUG_API == True))
		if (($part == 'app' && DEBUG_APP == True) || ($part == 'api' && DEBUG_API == True))
		{
			if (!defined('DEBUG_OUTPUT'))
			{
				define('DEBUG_OUTPUT', 1);
			}
			if ($level >= DEBUG_LEVEL)
			{
				if (!is_array($var))
				{
					if ($var != 'messageonly')
					{
						if (!DEBUG_DATATYPES)
						{
							$output = "$message\n$var";
						}
						else
						{
							$output = "$message\n$var is a ".gettype($var);
						}
					}
					else
					{
						$output = $message;
					}

					/* Bit 1 means to output to screen */
					if (!!(DEBUG_OUTPUT & 1))
					{
						echo "$output<br>\n";
					}
					/* Bit 2 means to output to sql */
					if (!!(DEBUG_OUTPUT & 2))
					{
						/* Need to flesh this out still. I dont have a table to dump this in yet.*/
						/* So the SQL statement will go here*/
					}

					/* Example of how this can be extended to output to other locations as well. This example uses a COM object */
					/*
					if (!!(DEBUG_OUTPUT & 32))
					{
						$obj_debug = new COM('Some_COM_App.Class','localhost');
						if (is_object($obj_debug))
						{
							$DebugMessage_return = $obj_debug->DebugMessage($output);
						}
					}
					*/
				}
				else
				{
					if (floor(phpversion()) > 3 && !!(DEBUG_OUTPUT & 2))
					{
						ob_start();
					}
					echo "<pre>\n$message\n";
					print_r($var);
					if (DEBUG_DATATYPES)
					{
//						while(list($key, $value) = each($var))
						foreach($var as $key => $value)
						{
							if (is_array($value))
							{
								$vartypes[$key] = print_debug_subarray($value);
							}
							else
							{
								$vartypes[$key] = gettype($value);
							}
						}
						echo "Data Types:\n";
						print_r($vartypes);
					}
					echo "\n<pre>\n";
					if (floor(phpversion()) > 3 && !!(DEBUG_OUTPUT & 2))
					{
						$output .= ob_get_contents();
						ob_end_clean();
						/* Need to flesh this out still. I dont have a table to dump this in yet.*/
						/* So the SQL statement will go here*/
						if (!!(DEBUG_OUTPUT & 1))
						{
							echo "$output<br>\n";
						}
					}
				}
			}
		}
	}

	/*!
	 @function safe_args
	 @abstract Allows for array and direct function params as well as sanatization.
	 @author seek3r
	 @discussion This function is used to validate param data as well as offer flexible function usage.
	 @syntax safe_args($expected_args, $recieved_args,__LINE__,__FILE__);
	 @example 
		function somefunc()
		{
			$expected_args[0] = Array('name'=>'fname','default'=>'joe', 'type'=>'string');
			$expected_args[1] = Array('name'=>'mname','default'=>'hick', 'type'=>'string');
			$expected_args[2] = Array('name'=>'lname','default'=>'bob', 'type'=>'string');
			$recieved_args = func_get_args();
			$args = safe_args($expected_args, $recieved_args,__LINE__,__FILE__);
			echo 'Full name: '.$args['fname'].' '.$args['fname'].' '.$args['lname'].'<br>';
			//default result would be:
			// Full name: joe hick bob<br>
		}
		
		Using this it is possible to use the function in any of the following ways
		somefunc('jack','city','brown');
		or
		somefunc(array('fname'=>'jack','mname'=>'city','lname'=>'brown'));
		or
		somefunc(array('lname'=>'brown','fname'=>'jack','mname'=>'city'));
		
		For the last one, when using named params in an array you dont have to follow any order
		All three would result in - Full name: jack city brown<br>
		
		When you use this method of handling params you can secure your functions as well offer
		flexibility needed for both normal use and web services use.
		If you have params that are required just set the default as ##REQUIRED##
		Users of your functions can also use ##DEFAULT## to use your default value for a param 
		when using the standard format like this:
		somefunc('jack','##DEFAULT##','brown');
		This would result in - Full name: jack hick brown<br>
		Its using the default value for the second param.
		Of course if you have the second param as a required field it will fail to work.
	*/
	function safe_args($expected, $recieved, $line='??', $file='??')
	{
		/* This array will contain all the required fields */
		$required = Array();

		/* This array will contain all types for sanatization checking */
		/* only used when an array is passed as the first arg          */
		$types = Array();
		
		/* start by looping thru the expected list and set params with */
		/* the default values                                          */
		$num = count($expected);
		for ($i = 0; $i < $num; ++$i)
		{
			$args[$expected[$i]['name']] = $expected[$i]['default'];
			if ($expected[$i]['default'] === '##REQUIRED##')
			{
				$required[$expected[$i]['name']] = True;
			}
			$types[$expected[$i]['name']] = $expected[$i]['type']; 
		}
		
		/* Make sure they passed at least one param */
		if(count($recieved) != 0)
		{
			/* if used as standard function we loop thru and set by position */
			if(!is_array($recieved[0]))
			{
			for ($i = 0; $i < $num; ++$i)
				{
					if(isset($recieved[$i]) && $recieved[$i] !== '##DEFAULT##')
					{
						if(sanitize($recieved[$i],$expected[$i]['type']))
						{
							$args[$expected[$i]['name']] = $recieved[$i];
							unset($required[$expected[$i]['name']]);
						}
						else
						{
							echo 'Fatal Error: Invalid paramater type for '.$expected[$i]['name'].' on line '.$line.' of '.$file.'<br>';
							exit;
						}
					}
				}
			}
			/* if used as standard function we loop thru and set by position */
			else
			{
				for ($i = 0; $i < $num; ++$i)
				{
					$types[$expected[$i]['name']] = $expected[$i]['type']; 
				}
				while(list($key,$val) = each($recieved[0]))
				{
					if($val !== '##DEFAULT##')
					{
						if(sanitize($val,$types[$key]) == True)
						{
							$args[$key] = $val;
							unset($required[$key]);
						}
						else
						{
							echo 'Fatal Error: Invalid paramater type for '.$key.' on line '.$line.' of '.$file.'<br>';
							exit;
						}
					}
				}
			}
		}
		if(count($required) != 0)
		{
			while (list($key) = each($required))
			{
				echo 'Fatal Error: Missing required paramater '.$key.' on line '.$line.' of '.$file.'<br>';
			}
			exit;
		}
		return $args;
	}

	/*!
	 @function sanitize
	 @abstract Validate data.
	 @author seek3r
	 @discussion This function is used to validate input data. 
	 @syntax sanitize('type', 'match string');
	 @example sanitize('number',$somestring);
	*/

	/*
	$GLOBALS['phpgw_info']['server']['sanitize_types']['number'] = Array('type' => 'preg_match', 'string' => '/^[0-9]+$/i');
	*/

	function sanitize($string,$type)
	{
		switch ($type)
		{
			case 'bool':
				if ($string == 1 || $string == 0)
				{
					return True;
				}
				break;
			case 'isprint':
				$length = strlen($string);
				$position = 0;
				while ($length > $position)
				{
					$char = substr($string, $position, 1);
					if ($char < ' ' || $char > '~')
					{
						return False;
					}
					$position = $position + 1;
				}
				return True;
				break;
			case 'alpha':
				if (preg_match("/^[a-z]+$/i", $string))
				{
					return True;
				}
				break;
			case 'number':
				if (preg_match("/^[0-9]+$/i", $string))
				{
					return True;
				}
				break;
			case 'alphanumeric':
				if (preg_match("/^[a-z0-9 -._]+$/i", $string))
				{
					return True;
				}
				break;
			case 'string':
				if (preg_match("/^[a-z]+$/i", $string))
				{
					return True;
				}
				break;
			case 'ip':
				if (preg_match('/^[0-9]{1,3}(\.[0-9]{1,3}){3}$/i',$string))
				{
					$octets = preg_split('/\./',$string);
					for ($i=0; $i != count($octets); ++$i)
					{
						if ($octets[$i] < 0 || $octets[$i] > 255)
						{
							return False;
						}
					}
					return True;
				}
				return False;
				break;
			case 'file':
				if (preg_match("/^[a-z0-9_]+\.+[a-z]+$/i", $string))
				{
					return True;
				}
				break;
			case 'email':
				if (preg_match('/^([[:alnum:]_%+=.-]+)@([[:alnum:]_.-]+)\.([a-z]{2,3}|[0-9]{1,3})$/i',$string))
				{
					return True;
				}
				break;
			case 'password':
				$password_length = strlen($string);
				$password_numbers = Array('0','1','2','3','4','5','6','7','8','9');
				$password_special_chars = Array(' ','~','`','!','@','#','$','%','^','&','*','(',')','_','+','-','=','{','}','|','[',']',"\\",':','"',';',"'",'<','>','?',',','.','/');

				if(@isset($GLOBALS['phpgw_info']['server']['pass_min_length']) && is_int($GLOBALS['phpgw_info']['server']['pass_min_length']) && $GLOBALS['phpgw_info']['server']['pass_min_length'] > 1)
				{
					$min_length = $GLOBALS['phpgw_info']['server']['pass_min_length'];
				}
				else
				{
					$min_length = 1;
				}

				if(@isset($GLOBALS['phpgw_info']['server']['pass_require_non_alpha']) && $GLOBALS['phpgw_info']['server']['pass_require_non_alpha'] == True)
				{
					$pass_verify_non_alpha = False;
				}
				else
				{
					$pass_verify_non_alpha = True;
				}
				
				if(@isset($GLOBALS['phpgw_info']['server']['pass_require_numbers']) && $GLOBALS['phpgw_info']['server']['pass_require_numbers'] == True)
				{
					$pass_verify_num = False;
				}
				else
				{
					$pass_verify_num = True;
				}

				if(@isset($GLOBALS['phpgw_info']['server']['pass_require_special_char']) && $GLOBALS['phpgw_info']['server']['pass_require_special_char'] == True)
				{
					$pass_verify_special_char = False;
				}
				else
				{
					$pass_verify_special_char = True;
				}
				
				if ($password_length >= $min_length)
				{
					for ($i=0; $i != $password_length; ++$i)
					{
						$cur_test_string = substr($string, $i, 1);
						if (in_array($cur_test_string, $password_numbers) || in_array($cur_test_string, $password_special_chars))
						{
							$pass_verify_non_alpha = True;
							if (in_array($cur_test_string, $password_numbers))
							{
								$pass_verify_num = True;
							}
							elseif (in_array($cur_test_string, $password_special_chars))
							{
								$pass_verify_special_char = True;
							}
						}
					}

					if ($pass_verify_num == False)
					{
						$GLOBALS['phpgw_info']['flags']['msgbox_data']['Password requires at least one non-alpha character']=False;
					}

					if ($pass_verify_num == False)
					{
						$GLOBALS['phpgw_info']['flags']['msgbox_data']['Password requires at least one numeric character']=False;
					}

					if ($pass_verify_special_char == False)
					{
						$GLOBALS['phpgw_info']['flags']['msgbox_data']['Password requires at least one special character (non-letter and non-number)']=False;
					}
					
					if ($pass_verify_num == True && $pass_verify_special_char == True)
					{
						return True;
					}
					return False;
				}
				$GLOBALS['phpgw_info']['flags']['msgbox_data']['Password must be at least '.$min_length.' characters']=False;
				return False;
				break;
			case 'any':
				return True;
				break;
			default :
				if (isset($GLOBALS['phpgw_info']['server']['sanitize_types'][$type]['type']))
				{
					if ($GLOBALS['phpgw_info']['server']['sanitize_types'][$type]['type']($GLOBALS['phpgw_info']['server']['sanitize_types'][$type]['string'], $string))
					{
						return True;
					}
				}
				return False;
		}
	}

	function reg_var($varname, $method='any', $valuetype='alphanumeric',$default_value='',$register=True)
	{
		if($method == 'any' || $method == array('any'))
		{
			$method = Array('POST','GET','COOKIE','SERVER','FILES','GLOBAL','DEFAULT');
		}
		elseif(!is_array($method))
		{
			$method = Array($method);
		}
		$cnt = count($method);
		for($i=0;$i<$cnt;++$i)
		{
			switch(strtoupper($method[$i]))
			{
				case 'DEFAULT':
					if($default_value)
					{
						$value = $default_value;
						$i = $cnt+1; /* Found what we were looking for, now we end the loop */
					}
					break;
				case 'GLOBAL':
					if(@isset($GLOBALS[$varname]))
					{
						$value = $GLOBALS[$varname];
						$i = $cnt+1;
					}
					break;
				case 'POST':
				case 'GET':
				case 'COOKIE':
				case 'SERVER':
					if(phpversion() >= '4.1.0')
					{
						$meth = '_'.strtoupper($method[$i]);
					}
					else
					{
						$meth = 'HTTP_'.strtoupper($method[$i]).'_VARS';
					}
					if(@isset($GLOBALS[$meth][$varname]))
					{
						$value = $GLOBALS[$meth][$varname];
						$i = $cnt+1;
					}
					if(get_magic_quotes_gpc() && isset($value))
					{
						// we need to stripslash 3 levels of arrays
						// because of the password function in preferences
						// it's named ['user']['variablename']['pw']
						// or something like this in projects
						// $values['budgetBegin']['1']['year']
						if(@is_array($value))
						{
							/* stripslashes on the first level of array values */
							foreach($value as $name => $val)
							{
								if(@is_array($val))
								{
									foreach($val as $name2 => $val2)
									{
										if(@is_array($val2))
										{
											foreach($val2 as $name3 => $val3)
											{
												$value[$name][$name2][$name3] = stripslashes($val3);
											}
										}
										else
										{
											$value[$name][$name2] = stripslashes($val2);
										}
									}
								}
								else
								{
									$value[$name] = stripslashes($val);
								}
							}
						}
						else
						{
							/* stripslashes on this (string) */
							$value = stripslashes($value);
						}
					}
					break;
				case 'FILES':
					if(phpversion() >= '4.1.0')
					{
						$meth = '_FILES';
					}
					else
					{
						$meth = 'HTTP_POST_FILES';
					}
					if(@isset($GLOBALS[$meth][$varname]))
					{
						$value = $GLOBALS[$meth][$varname];
						$i = $cnt+1;
					}
					break;
				default:
					if(@isset($GLOBALS[strtoupper($method[$i])][$varname]))
					{
						$value = $GLOBALS[strtoupper($method[$i])][$varname];
						$i = $cnt+1;
					}
					break;
			}
		}

		if (@!isset($value))
		{
			$value = $default_value;
		}

		if (@!is_array($value))
		{
			if ($value == '')
			{
				$result = $value;
			}
			else
			{
				if (sanitize($value,$valuetype) == 1)
				{
					$result = $value;
				}
				else
				{
					$result = $default_value;
				}
			}
		}
		else
		{
			reset($value);
			while(list($k, $v) = each($value))
			{
				if ($v == '')
				{
					$result[$k] = $v;
				}
				else
				{
					if (is_array($valuetype))
					{
						$vt = $valuetype[$k];
					}
					else
					{
						$vt = $valuetype;
					}

					if (sanitize($v,$vt) == 1)
					{
						$result[$k] = $v;
					}
					else
					{
						if (is_array($default_value))
						{
							$result[$k] = $default_value[$k];
						}
						else
						{
							$result[$k] = $default_value;
						}
					}
				}
			}
		}
		if($register)
		{
			$GLOBALS['phpgw_info'][$GLOBALS['phpgw_info']['flags']['currentapp']][$varname] = $result;
		}
		return $result;
	}

	/*!
	 @function get_var
	 @abstract retrieve a value from either a POST, GET, COOKIE, SERVER or from a class variable.
	 @author skeeter
	 @discussion This function is used to retrieve a value from a user defined order of methods. 
	 @syntax get_var('id',array('HTTP_POST_VARS'||'POST','HTTP_GET_VARS'||'GET','HTTP_COOKIE_VARS'||'COOKIE','GLOBAL','DEFAULT'));
	 @example $this->id = get_var('id',array('HTTP_POST_VARS'||'POST','HTTP_GET_VARS'||'GET','HTTP_COOKIE_VARS'||'COOKIE','GLOBAL','DEFAULT'));
	 @param $variable name
	 @param $method ordered array of methods to search for supplied variable
	 @param $default_value (optional)
	*/
	function get_var($variable,$method='any',$default_value='')
	{
		if(!@is_array($method))
		{
			$method = array($method);
		}
		return reg_var($variable,$method,'any',$default_value,False);
	}

	/*!
	 @function include_class
	 @abstract This will include the class once and guarantee that it is loaded only once.  Similar to CreateObject, but does not instantiate the class.
	 @author skeeter
	 @discussion This will include the API class once and guarantee that it is loaded only once.  Similar to CreateObject, but does not instantiate the class.
	 @syntax include_class('setup');
	 @example include_class('setup');
	 @param $included_class API class to load
	*/
	function include_class($included_class)
	{
		if (!isset($GLOBALS['phpgw_info']['flags']['included_classes'][$included_class]) ||
			!$GLOBALS['phpgw_info']['flags']['included_classes'][$included_class])
		{
			$GLOBALS['phpgw_info']['flags']['included_classes'][$included_class] = True;   
			include(PHPGW_SERVER_ROOT.'/phpgwapi/inc/class.'.$included_class.'.inc.php');
		}
	}

	/*!
	 @function personalize_include_path
	 @abstract return path to include a "ile.php"
	 @author Serpro
	 @author Antonio Carlos da SIlva
	 @discussion This function is used to generate a path with $app and $prefix paramameters.
	 @example include(personalize_include_path('phpgwapi','login');
         @example Will generate : /var/www/expresso/phpgwapi/templates/default/login_default.php
         @example if "default" is the 'login_template_set' .
	 @param $app : name of application
	 @param $prefix : value to affix in login_template_set
         */
        function personalize_include_path($app,$prefix)
        {
                $file_include = PHPGW_SERVER_ROOT . '/' . $app . '/templates/' . $GLOBALS['phpgw_info']['login_template_set'] . '/' . $prefix . '_' . $GLOBALS['phpgw_info']['login_template_set'] . '.php';
                if(!$file_include || !file_exists($file_include))
                {
                        $file_include = PHPGW_SERVER_ROOT . '/' . $app . '/templates/default/' . $prefix .'_default.php';
                }
        return $file_include;
        }

        /*!
	 @function nearest_to_me
	 @abstract return host nearest to client
	 @Include by Serpro ( Antonio Carlos da Silva).
         */
        function nearest_to_me()
        {
                $proxies=explode(',',$_SERVER['HTTP_X_FORWARDED_HOST']);
                return isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $proxies[0] : $_SERVER['HTTP_HOST'];
        }

	/*!
	 @function session_convert
	 @abstract cipher/decipher session id.
	 @Include by Serpro ( Antonio Carlos da Silva).
         @discussion This function cipher/decipher session id to captcha.
         */
        function session_convert($str,$ky='')
        {
            if($ky=='') return $str;
            $ky=str_replace(chr(32),'',$ky);
            if(strlen($ky)<8) return '';
            $kl=strlen($ky)<32?strlen($ky):32;
            $k=array();
            for($i=0;$i<$kl;++$i)
                {
                    $k[$i]=ord($ky{$i})&0x1F;
                }
            $j=0;
            for($i=0;$i<strlen($str);++$i)
                {
                    $e=ord($str{$i});
                    $str{$i}=$e&0xE0?chr($e^$k[$j]):chr($e);
                    ++$j;$j=$j==$kl?0:$j;
                }
            return $str;
        }

	/*!
	 @function CreateObject
	 @abstract Load a class and include the class file if not done so already.
	 @author mdean
	 @author milosch
	 @author (thanks to jengo and ralf)
	 @discussion This function is used to create an instance of a class, and if the class file has not been included it will do so. 
	 @syntax CreateObject('app.class', 'constructor_params');
	 @example $phpgw->acl = CreateObject('phpgwapi.acl');
	 @param $classname name of class
	 @param $p1-$p16 class parameters (all optional)
	*/
	function CreateObject($class,
		$p1='_UNDEF_',$p2='_UNDEF_',$p3='_UNDEF_',$p4='_UNDEF_',
		$p5='_UNDEF_',$p6='_UNDEF_',$p7='_UNDEF_',$p8='_UNDEF_',
		$p9='_UNDEF_',$p10='_UNDEF_',$p11='_UNDEF_',$p12='_UNDEF_',
		$p13='_UNDEF_',$p14='_UNDEF_',$p15='_UNDEF_',$p16='_UNDEF_')
	{
		global $phpgw_info, $phpgw;

		/*
		if(is_object(@$GLOBALS['phpgw']->log) && $class != 'phpgwapi.error' && $class != 'phpgwapi.errorlog')
		{
			$GLOBALS['phpgw']->log->write(array('text'=>'D-Debug, dbg: %1','p1'=>'This class was run: '.$class,'file'=>__FILE__,'line'=>__LINE__));
		}
		*/

		/* error_reporting(0); */
		list($appname,$classname) = explode('.', $class);
		$filename = PHPGW_INCLUDE_ROOT.'/'.$appname.'/inc/class.'.$classname.'.inc.php';
		$included_files = get_included_files();

		if(!isset($included_files[$filename]))
		{
			if(@file_exists($filename))
			{
				include_once($filename);
				$is_included = True;
			}
			else
			{
				$is_included = False;
			}
		}
		else
		{
			$is_included = True;
		}

		if($is_included)
		{
			if($p1 == '_UNDEF_' && $p1 != 1)
			{
				$obj = new $classname;
			}
			else
			{
				$input = array($p1,$p2,$p3,$p4,$p5,$p6,$p7,$p8,$p9,$p10,$p11,$p12,$p13,$p14,$p15,$p16);
				$i = 1;
				$code = '$obj = new ' . $classname . '(';
				foreach($input as $test)
				{
					if(($test == '_UNDEF_' && $test != 1 ) || $i == 17)
					{
						break;
					}
					else
					{
						$code .= '$p' . $i . ',';
					}
					++$i;
				}
				$code = substr($code,0,-1) . ');';
				eval($code);
			}
			/* error_reporting(E_ERROR | E_WARNING | E_PARSE); */
			return $obj;
		}
	}

	/*!
	 @function ExecMethod
	 @abstract Execute a function, and load a class and include the class file if not done so already.
	 @author seek3r
	 @discussion This function is used to create an instance of a class, and if the class file has not been included it will do so.
	 @syntax ExecObject('app.class', 'constructor_params');
	 @param $method to execute
	 @param $functionparams function param should be an array
	 @param $loglevel developers choice of logging level
	 @param $classparams params to be sent to the contructor
	 @example ExecObject('phpgwapi.acl.read');
	*/
	function ExecMethod($method, $functionparams = '_UNDEF_', $loglevel = 3, $classparams = '_UNDEF_')
	{
		/* Need to make sure this is working against a single dimensional object */
		$partscount = count(explode('.',$method)) - 1;
		if ($partscount == 2)
		{
			list($appname,$classname,$functionname) = explode(".", $method);
			if (!is_object($GLOBALS[$classname]))
			{
				if ($classparams != '_UNDEF_' && ($classparams || $classparams != 'True'))
				{
					$GLOBALS[$classname] = CreateObject($appname.'.'.$classname, $classparams);
				}
				else
				{
					$GLOBALS[$classname] = CreateObject($appname.'.'.$classname);
				}
			}

			if (!method_exists($GLOBALS[$classname],$functionname))
			{
				echo "<p><b>".function_backtrace()."</b>: no methode '$functionname' in class '$classname'</p>\n";
				return False;
			}
			if ((is_array($functionparams) || $functionparams != '_UNDEF_') && ($functionparams || $functionparams != 'True'))
			{
				return $GLOBALS[$classname]->$functionname($functionparams);
			}
			else
			{
				return $GLOBALS[$classname]->$functionname();
			}
		}
		/* if the $method includes a parent class (multi-dimensional) then we have to work from it */
		elseif ($partscount >= 3)
		{
			$GLOBALS['methodparts'] = explode(".", $method);
			$classpartnum = $partscount - 1;
			$appname = $GLOBALS['methodparts'][0];
			$classname = $GLOBALS['methodparts'][$classpartnum];
			$functionname = $GLOBALS['methodparts'][$partscount];
			/* Now we clear these out of the array so that we can do a proper */
			/* loop and build the $parentobject */
			unset ($GLOBALS['methodparts'][0]);
			unset ($GLOBALS['methodparts'][$classpartnum]);
			unset ($GLOBALS['methodparts'][$partscount]);
			reset ($GLOBALS['methodparts']);
			$firstparent = 'True';
//			while (list ($key, $val) = each ($GLOBALS['methodparts']))
			foreach($GLOBALS['methodparts'] as $val)
			{
				if ($firstparent == 'True')
				{
					$parentobject = '$GLOBALS["'.$val.'"]';
					$firstparent = False;
				}
				else
				{
					$parentobject .= '->'.$val;
				}
			}
			unset($GLOBALS['methodparts']);
			$code = '$isobject = is_object('.$parentobject.'->'.$classname.');';
			eval ($code);
			if (!$isobject)
			{
				if ($classparams != '_UNDEF_' && ($classparams || $classparams != 'True'))
				{
					if (is_string($classparams))
					{
						eval($parentobject.'->'.$classname.' = CreateObject("'.$appname.'.'.$classname.'", "'.$classparams.'");');
					}
					else
					{
						eval($parentobject.'->'.$classname.' = CreateObject("'.$appname.'.'.$classname.'", '.$classparams.');');
					}
				}
				else
				{
					eval($parentobject.'->'.$classname.' = CreateObject("'.$appname.'.'.$classname.'");');
				}
			}

			if ($functionparams != '_UNDEF_' && ($functionparams || $functionparams != 'True'))
			{
				eval('$returnval = '.$parentobject.'->'.$classname.'->'.$functionname.'('.$functionparams.');');
				return $returnval;
			}
			else
			{
				eval('$returnval = '.$parentobject.'->'.$classname.'->'.$functionname.'();');
				return $returnval;
			}
		}
		else
		{
			return 'error in parts';
		}
	}

	/*!
	 @function copyobj
	 @abstract duplicates the result of copying an object under php3/4 even when using php5
	 @author milosch
	 @discussion This is critical when looping on db object output and updating or inserting to the database using a copy of the db object.  This was first added to GroupWhere
	 @syntax copyobj($source_object,$target_object);
	 @example copyobj($GLOBALS['phpgw']->db,$mydb);
	 @param $a   - Source Object
	 @param $b   - Target Object (copy)
	*/
	function copyobj($a,&$b)
	{
		if(floor(phpversion()) > 4)
		{
			$b = clone($a);
		}
		else
		{
			$b = $a;
		}
		return;
	}

	/*!
	 @function get_account_id
	 @abstract Return a properly formatted account_id.
	 @author skeeter
	 @discussion This function will return a properly formatted account_id. This can take either a name or an account_id as paramters. If a name is provided it will return the associated id.
	 @syntax get_account_id($accountid);
	 @example $account_id = get_account_id($accountid);
	 @param $account_id either a name or an id
	 @param $default_id either a name or an id
	*/
	function get_account_id($account_id = '',$default_id = '')
	{
		if (gettype($account_id) == 'integer')
		{
			return $account_id;
		}
		elseif ($account_id == '')
		{
			if ($default_id == '')
			{
				return (isset($GLOBALS['phpgw_info']['user']['account_id'])?$GLOBALS['phpgw_info']['user']['account_id']:0);
			}
			elseif (is_string($default_id))
			{
				return $GLOBALS['phpgw']->accounts->name2id($default_id);
			}
			return (int)$default_id;
		}
		elseif (is_string($account_id))
		{
			if($GLOBALS['phpgw']->accounts->exists((int)$account_id) == True)
			{
				return (int)$account_id;
			}
			else
			{
				return $GLOBALS['phpgw']->accounts->name2id($account_id);
			}
		}
	}

	/*!
	 @function filesystem_separator
	 @abstract sets the file system seperator depending on OS
	 @result file system separator
	*/
	function filesystem_separator()
	{
		if(PHP_OS == 'Windows' || PHP_OS == 'OS/2' || PHP_OS == 'WINNT')
		{
			return '\\';
		}
		else
		{
			return '/';
		}
	}

	function _debug_array($array,$print=True)
	{
		$four = False;
		if(@floor(phpversion()) > 3)
		{
			$four = True;
		}
		if($four)
		{
			if(!$print)
			{
				ob_start();
			}
			echo '<pre>';
			print_r($array);
			echo '</pre>';
			if(!$print)
			{
				$v = ob_get_contents();
				ob_end_clean();
				return $v;
			}
		}
		else
		{
			return print_r($array,False,$print);
		}
	}

	/*
	@function alessthanb
	@abstract phpgw version checking, is param 1 < param 2 in phpgw versionspeak?
	@param	$a	phpgw version number to check if less than $b
	@param	$b	phpgw version number to check $a against
	#return	True if $a < $b
	*/
	function alessthanb($a,$b,$DEBUG=False)
	{
		$num = array('1st','2nd','3rd','4th');

		if ($DEBUG)
		{
			echo'<br>Input values: ' . 'A="'.$a.'", B="'.$b.'"';
		}
		$newa = str_replace('-','',str_replace('pre','.',$a));
		$newb = str_replace('-','',str_replace('pre','.',$b));
		$testa = explode('.',$newa);
		if(@$testa[1] == '')
		{
			$testa[1] = 0;
		}
		if(@$testa[3] == '')
		{
			$testa[3] = 0;
		}
		$testb = explode('.',$newb);
		if(@$testb[1] == '')
		{
			$testb[1] = 0;
		}
		if(@$testb[3] == '')
		{
			$testb[3] = 0;
		}
		$less = 0;

        $testa_count = count($testa);
		for ($i=0;$i<$testa_count;++$i)
		{
			if ($DEBUG) { echo'<br>Checking if '. (int)$testa[$i] . ' is less than ' . (int)$testb[$i] . ' ...'; }
			if ((int)$testa[$i] < (int)$testb[$i])
			{
				if ($DEBUG) { echo ' yes.'; }
				++$less;
				if ($i<3)
				{
					/* Ensure that this is definitely smaller */
					if ($DEBUG) { echo"  This is the $num[$i] octet, so A is definitely less than B."; }
					$less = 5;
					break;
				}
			}
			elseif((int)$testa[$i] > (int)$testb[$i])
			{
				if ($DEBUG) { echo ' no.'; }
				$less--;
				if ($i<2)
				{
					/* Ensure that this is definitely greater */
					if ($DEBUG) { echo"  This is the $num[$i] octet, so A is definitely greater than B."; }
					$less = -5;
					break;
				}
			}
			else
			{
				if ($DEBUG) { echo ' no, they are equal.'; }
				$less = 0;
			}
		}
		if ($DEBUG) { echo '<br>Check value is: "'.$less.'"'; }
		if ($less>0)
		{
			if ($DEBUG) { echo '<br>A is less than B'; }
			return True;
		}
		elseif($less<0)
		{
			if ($DEBUG) { echo '<br>A is greater than B'; }
			return False;
		}
		else
		{
			if ($DEBUG) { echo '<br>A is equal to B'; }
			return False;
		}
	}

	/*!
	@function amorethanb
	@abstract phpgw version checking, is param 1 > param 2 in phpgw versionspeak?
	@param	$a	phpgw version number to check if more than $b
	@param	$b	phpgw version number to check $a against
	#return	True if $a < $b
	*/
	function amorethanb($a,$b,$DEBUG=False)
	{
		$num = array('1st','2nd','3rd','4th');

		if ($DEBUG)
		{
			echo'<br>Input values: ' . 'A="'.$a.'", B="'.$b.'"';
		}
		$newa = str_replace('-','',str_replace('pre','.',$a));
		$newb = str_replace('-','',str_replace('pre','.',$b));
		$testa = explode('.',$newa);
		if($testa[3] == '')
		{
			$testa[3] = 0;
		}
		$testb = explode('.',$newb);
		if($testb[3] == '')
		{
			$testb[3] = 0;
		}
		$less = 0;

        $testa_count = count($testa);
		for ($i=0;$i<$testa_count;++$i)
		{
			if ($DEBUG) { echo'<br>Checking if '. (int)$testa[$i] . ' is more than ' . (int)$testb[$i] . ' ...'; }
			if ((int)$testa[$i] > (int)$testb[$i])
			{
				if ($DEBUG) { echo ' yes.'; }
				++$less;
				if ($i<3)
				{
					/* Ensure that this is definitely greater */
					if ($DEBUG) { echo"  This is the $num[$i] octet, so A is definitely greater than B."; }
					$less = 5;
					break;
				}
			}
			elseif((int)$testa[$i] < (int)$testb[$i])
			{
				if ($DEBUG) { echo ' no.'; }
				$less--;
				if ($i<2)
				{
					/* Ensure that this is definitely smaller */
					if ($DEBUG) { echo"  This is the $num[$i] octet, so A is definitely less than B."; }
					$less = -5;
					break;
				}
			}
			else
			{
				if ($DEBUG) { echo ' no, they are equal.'; }
				$less = 0;
			}
		}
		if ($DEBUG) { echo '<br>Check value is: "'.$less.'"'; }
		if ($less>0)
		{
			if ($DEBUG) { echo '<br>A is greater than B'; }
			return True;
		}
		elseif($less<0)
		{
			if ($DEBUG) { echo '<br>A is less than B'; }
			return False;
		}
		else
		{
			if ($DEBUG) { echo '<br>A is equal to B'; }
			return False;
		}
	}
	
	/*!
	 @function prepend_tables_prefix
	 @abstract prepend a prefix to an array of table names
	 @author Adam Hull (aka fixe) - No copyright claim
	 @param	$prefix	the string to be prepended
	 @param	$tables	and array of tables to have the prefix prepended to
	 @return array of table names with the prefix prepended
	*/
	function prepend_tables_prefix($prefix,$tables)
	{
		foreach($tables as $key => $value)
		{
			$tables[$key] = $prefix.$value;
		}
		return $tables;
	}

	/*!
	 @function function_backtrace
	 @abstract backtrace of the calling functions for php4.3+ else menuaction/scriptname
	 @author ralfbecker
	 @return function-names separated by slashes (beginning with the calling function not this one)
	*/
	function function_backtrace($remove=0)
	{
		if (function_exists('debug_backtrace'))
		{
			$backtrace = debug_backtrace();
			//echo "<pre>".print_r($backtrace,True)."</pre>\n";
			foreach($backtrace as $level)
			{
				if ($remove-- < 0)
				{
					$ret[] = (isset($level['class'])?$level['class'].'::':'').$level['function'];
				}
			}
			return implode(' / ',$ret);
		}
		return $_GET['menuaction'] ? $_GET['menuaction'] : str_replace(PHPGW_SERVER_ROOT,'',$_SERVER['SCRIPT_FILENAME']);
	}

	function _check_script_tag(&$var,$name='')
	{
		if (is_array($var))
		{
			foreach($var as $key => $val)
			{
				if (is_array($val))
				{
					_check_script_tag($var[$key],$name.'['.$key.']');
				}
				else
				{
					if (preg_match('/<\/?[^>]*(iframe|script|onabort|onblur|onchange|onclick|ondblclick|onerror|onfocus|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onselect|onsubmit|onunload|javascript)+[^>]*>/i',$val))
					{
						//echo "<p>*** _check_script_tag($name): unset($name [$key]) ***</p>\n";
						unset($var[$key]);
					}
				}
			}
			// in case some stupid old code expects the array-pointer to be at the start of the array
			reset($var);
		}
	}
		
	foreach(array('_GET','_POST','_REQUEST','HTTP_GET_VARS','HTTP_POST_VARS','HTTP_REQUEST_VARS') as $where)
	{
		$pregs = array(
			'order' => '/^[a-zA-Z0-9_]*$/',
			'sort'  => '/^(ASC|DESC|asc|desc|0|1|2|3|4|5|6|7){0,1}$/',
		);
		foreach(array('order','sort') as $name)
		{
			if (isset($GLOBALS[$where][$name]) && !is_array($GLOBALS[$where][$name]) && !preg_match($pregs[$name],$GLOBALS[$where][$name]))
			{
				$GLOBALS[$where][$name] = '';
			}
		}
		if (isset($GLOBALS[$where]) && is_array($GLOBALS[$where]))
		{
			_check_script_tag($GLOBALS[$where],$where);
		}
	}
	
	if(floor(phpversion()) <= 4)
	{
		/**
		 * clone function for php4, use as $new_obj = clone($old_obj);
		 */
		eval('
		function clone($obj)
		{
			return $obj;
		}
		');
	}
?>
