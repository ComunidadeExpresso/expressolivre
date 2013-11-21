<?php
  /**************************************************************************\
  * eGroupWare API - HTTP protocol class                                     *
  * http://www.egroupware.org/api                                            *
  * ------------------------------------------------------------------------ *
  * This is not part of eGroupWare, but is used by eGroupWare.               * 
  * ------------------------------------------------------------------------ *
  * This program is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU General Public License as published by the    *
  * Free Software Foundation; either version 2 of the License, or (at your   *
  * option) any later version.                                               *
  \**************************************************************************/


	class http
	{
		var $host_name = '';
		var $host_port = 80;
		var $proxy_host_name = '';
		var $proxy_host_port = 80;

		var $request_method = 'GET';
		var $user_agent = 'Manuel Lemos HTTP class test script';
		var $request_uri = '';
		var $protocol_version = '1.0';
		var $debug = 0;
		var $support_cookies = 1;
		var $cookies = array();

		/* private variables - DO NOT ACCESS */

		var $state = 'Disconnected';
		var $connection = 0;
		var $content_length = 0;
		var $read_length = 0;
		var $request_host = '';
		var $months = array(
			'Jan' => '01',
			'Feb' => '02',
			'Mar' => '03',
			'Apr' => '04',
			'May' => '05',
			'Jun' => '06',
			'Jul' => '07',
			'Aug' => '08',
			'Sep' => '09',
			'Oct' => '10',
			'Nov' => '11',
			'Dec' => '12'
		);

		/* Private methods - DO NOT CALL */

		function OutputDebug($message)
		{
			echo $message,"\n";
		}

		function GetLine()
		{
			for($line='';;)
			{
				if(feof($this->connection) || !($part=fgets($this->connection,100)))
				{
					return(0);
				}
				$line.=$part;
				$length=strlen($line);
				if($length>=2 && substr($line,$length-2,2)=="\r\n")
				{
					$line=substr($line,0,$length-2);
					if($this->debug)
					{
						$this->OutputDebug("< $line");
					}
					return($line);
				}
			}
		}

		function PutLine($line)
		{
			if($this->debug)
			{
				$this->OutputDebug("> $line");
			}
			return(fputs($this->connection,"$line\r\n"));
		}

		function PutData($data)
		{
			if($this->debug)
			{
				$this->OutputDebug("> $data");
			}
			return(fputs($this->connection,$data));
		}

		function Readbytes($length)
		{
			if($this->debug)
			{
				if(($bytes=fread($this->connection,$length))!="")
				{
					$this->OutputDebug("< $bytes");
				}
				return($bytes);
			}
			else
			{
				return(fread($this->connection,$length));
			}
		}

		function EndOfInput()
		{
			return(feof($this->connection));
		}

		function Connect($host_name,$host_port)
		{
			if($this->debug)
			{
				$this->OutputDebug("Connecting to $host_name...");
			}
			if(($this->connection=fsockopen($host_name,$host_port,&$error))==0)
			{
				switch($error)
				{
					case -3:
						return('-3 socket could not be created');
					case -4:
						return('-4 dns lookup on hostname "'.$host_name.'" failed');
					case -5:
						return('-5 connection refused or timed out');
					case -6:
						return('-6 fdopen() call failed');
					case -7:
						return('-7 setvbuf() call failed');
					default:
						return($error.' could not connect to the host "'.$host_name.'"');
				}
			}
			else
			{
				if($this->debug)
				{
					$this->OutputDebug("Connected to $host_name");
				}
				$this->state='Connected';
				return("");
			}
		}

		function Disconnect()
		{
			if($this->debug)
			{
				$this->OutputDebug('Disconnected from '.$this->host_name);
			}
			fclose($this->connection);
			return('');
		}

		/* Public methods */

		function Open($arguments)
		{
			if($this->state!='Disconnected')
			{
				return('1 already connected');
			}
			if(IsSet($arguments['HostName']))
			{
				$this->host_name=$arguments['HostName'];
			}
			if(IsSet($arguments['HostPort']))
			{
				$this->host_port=$arguments['HostPort'];
			}
			if(IsSet($arguments['ProxyHostName']))
			{
				$this->proxy_host_name=$arguments['ProxyHostName'];
			}
			if(IsSet($arguments['ProxyHostPort']))
			{
				$this->proxy_host_port=$arguments['ProxyHostPort'];
			}
			if(strlen($this->proxy_host_name)==0)
			{
				if(strlen($this->host_name)==0)
				{
					return('2 it was not specified a valid hostname');
				}
				$host_name = $this->host_name;
				$host_port = $this->host_port;
			}
			else
			{
				$host_name = $this->proxy_host_name;
				$host_port = $this->proxy_host_port;
			}
			$error = $this->Connect($host_name,$host_port);
			if(strlen($error)==0)
			{
				$this->state = 'Connected';
			}
			return($error);
		}

		function Close()
		{
			if($this->state == 'Disconnected')
			{
				return('1 already disconnected');
			}
			$error = $this->Disconnect();
			if(strlen($error) == 0)
			{
				$this->state = 'Disconnected';
			}
			return($error);
		}

		function SendRequest($arguments)
		{
			switch($this->state)
			{
				case 'Disconnected':
					return('1 connection was not yet established');
				case 'Connected':
					break;
				default:
					return('2 can not send request in the current connection state');
			}
			if(IsSet($arguments['RequestMethod']))
			{
				$this->request_method = $arguments['RequestMethod'];
			}
			if(IsSet($arguments['User-Agent']))
			{
				$this->user_agent = $arguments['User-Agent'];
			}
			if(strlen($this->request_method) == 0)
			{
				return('3 it was not specified a valid request method');
			}
			if(IsSet($arguments['RequestURI']))
			{
				$this->request_uri = $arguments['RequestURI'];
			}
			if(strlen($this->request_uri) == 0 || substr($this->request_uri,0,1) != '/')
			{
				return('4 it was not specified a valid request URI');
			}
			$request_body = '';
			$headers=(IsSet($arguments['Headers']) ? $arguments['Headers'] : array());
			if($this->request_method == 'POST')
			{
				if(IsSet($arguments['PostValues']))
				{
					$values = $arguments['PostValues'];
					if(!@is_array($values))
					{
						return('5 it was not specified a valid POST method values array');
					}
                    $values_count = count($values);
					for($request_body = '',Reset($values),$value=0;$value<$values_count;Next($values),$value++)
					{
						if($value>0)
						{
							$request_body .= '&';
						}
						$request_body.=Key($values).'='.UrlEncode($values[Key($values)]);
					}
					$headers['Content-type'] = 'application/x-www-form-urlencoded';
				}
			}
			if(strlen($this->proxy_host_name) == 0)
			{
				$request_uri = $this->request_uri;
			}
			else
			{
				$request_uri = 'http://'.$this->host_name.($this->host_port==80 ? '' : ':'.$this->host_port).$this->request_uri;
			}
			if(($success = $this->PutLine($this->request_method.' '.$request_uri.' HTTP/'.$this->protocol_version)))
			{
				if(($body_length = strlen($request_body)))
				{
					$headers['Content-length'] = $body_length;
				}
                $headers_count = count($headers);
				for($host_set=0,Reset($headers),$header=0;$header<$headers_count;Next($headers),$header++)
				{
					$header_name  = Key($headers);
					$header_value = $headers[$header_name];
					if(@is_array($header_value))
					{
                        $header_value_count = count($header_value);
						for(Reset($header_value),$value=0;$value<$header_value_count;Next($header_value),$value++)
						{
							if(!$success = $this->PutLine("$header_name: ".$header_value[Key($header_value)]))
							{
								break 2;
							}
						}
					}
					else
					{
						if(!$success = $this->PutLine("$header_name: $header_value"))
						{
							break;
						}
					}
					if(strtolower(Key($headers)) == 'host')
					{
						$this->request_host = strtolower($header_value);
						$host_set = 1;
					}
				}
				if($success)
				{
					if(!$host_set)
					{
						$success = $this->PutLine('Host: '.$this->host_name);
						$this->request_host = strtolower($this->host_name);
					}
					if(count($this->cookies) && IsSet($this->cookies[0]))
					{
						$now = gmdate('Y-m-d H-i-s');
                        $cookies_count = count($this->cookies[0]);
						for($cookies = array(),$domain=0,Reset($this->cookies[0]);$domain<$cookies_count;Next($this->cookies[0]),$domain++)
						{
							$domain_pattern = Key($this->cookies[0]);
							$match = strlen($this->request_host)-strlen($domain_pattern);
							if($match >= 0 &&
								!strcmp($domain_pattern,substr($this->request_host,$match)) &&
								($match == 0 || $domain_pattern[0] == '.' || $this->request_host[$match-1] == '.'))
							{
                                $cookies_count_domain_pattern = count($this->cookies[0][$domain_pattern]);
								for(Reset($this->cookies[0][$domain_pattern]),$path_part=0;$path_part<$cookies_count_domain_pattern;Next($this->cookies[0][$domain_pattern]),$path_part++)
								{
									$path = Key($this->cookies[0][$domain_pattern]);
									if(strlen($this->request_uri) >= strlen($path) && substr($this->request_uri,0,strlen($path)) == $path)
									{
                                        $cookies_count_path = count($this->cookies[0][$domain_pattern][$path]);
										for(Reset($this->cookies[0][$domain_pattern][$path]),$cookie = 0;$cookie<$cookies_count_path;Next($this->cookies[0][$domain_pattern][$path]),$cookie++)
										{
											$cookie_name = Key($this->cookies[0][$domain_pattern][$path]);
											$expires     = $this->cookies[0][$domain_pattern][$path][$cookie_name]['expires'];
											if($expires == '' || strcmp($now,$expires)<0)
											{
												$cookies[$cookie_name] = $this->cookies[0][$domain_pattern][$path][$cookie_name];
											}
										}
									}
								}
							}
						}
                        $cookies_count = count($cookies);
						for(Reset($cookies),$cookie=0;$cookie<$cookies_count;Next($cookies),$cookie++)
						{
							$cookie_name = Key($cookies);
							if(!($success = $this->PutLine('Cookie: '.UrlEncode($cookie_name).'='.$cookies[$cookie_name]['value'].';')))
							{
								break;
							}
						}
					}
					if($success)
					{
						if($success)
						{
							$success = $this->PutLine('');
							if($body_length && $success)
							{
								$success = $this->PutData($request_body);
							}
						}
					}
				}
			}
			if(!$success)
			{
				return('5 could not send the HTTP request');
			}
			$this->state = 'RequestSent';
			return('');
		}

		function ReadReplyHeaders(&$headers)
		{
			switch($this->state)
			{
				case 'Disconnected':
					return('1 connection was not yet established');
				case 'Connected':
					return('2 request was not sent');
				case 'RequestSent':
					break;
				default:
					return('3 can not get request headers in the current connection state');
			}
			$headers = array();
			$this->content_length = $this->read_length = 0;
			$this->content_length_set = 0;
			for(;;)
			{
				$line = $this->GetLine();
				if(!is_string($line))
				{
					return('4 could not read request reply');
				}
				if($line == '')
				{
					$this->state = 'GotReplyHeaders';
					return('');
				}
				$header_name  = strtolower(strtok($line,':'));
				$header_value = Trim(Chop(strtok("\r\n")));
				if(IsSet($headers[$header_name]))
				{
					if(is_string($headers[$header_name]))
					{
						$headers[$header_name] = array($headers[$header_name]);
					}
					$headers[$header_name][] = $header_value;
				}
				else
				{
					$headers[$header_name] = $header_value;
				}
				switch($header_name)
				{
					case 'content-length':
					$this->content_length = (int)$headers[$header_name];
					$this->content_length_set = 1;
					break;
					case 'set-cookie':
					if($this->support_cookies)
					{
						$cookie_name  = trim(strtok($headers[$header_name],'='));
						$cookie_value = strtok(';');
						$domain = $this->request_host;
						$path = '/';
						$expires = '';
						$secure = 0;
						while(($name=strtolower(trim(strtok('=')))) != '')
						{
							$value=UrlDecode(strtok(';'));
							switch($name)
							{
								case 'domain':
									if($value == '' || !strpos($value,'.',$value[0] == '.'))
									{
										break;
									}
									$domain = strtolower($value);
									break;
								case 'path':
									if($value != '' && $value[0] == '/')
									{
										$path = $value;
									}
									break;
								case 'expires':
									if(preg_match('/^((Mon|Monday|Tue|Tuesday|Wed|Wednesday|Thu|Thursday|Fri|Friday|Sat|Saturday|Sun|Sunday), )?([0-9]{2})\\-(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\\-([0-9]{2,4}) ([0-9]{2})\\:([0-9]{2})\\:([0-9]{2}) GMT$/',$value,$matches))
									{
										$year = (int)$matches[5];
										if($year<1900)
										{
											$year += ($year<70 ? 2000 : 1900);
										}
										$expires = "$year-".$this->months[$matches[4]].'-'.$matches[3].' '.$matches[6].':'.$matches[7].':'.$matches[8];
									}
									break;
								case 'secure':
									$secure = 1;
									break;
							}
						}
						$this->cookies[$secure][$domain][$path][$cookie_name] = array(
							'name'    => $cookie_name,
							'value'   => $cookie_value,
							'domain'  => $domain,
							'path'    => $path,
							'expires' => $expires,
							'secure'  => $secure
						);
					}
				}
			}
		}

		function ReadReplyBody(&$body,$length)
		{
			switch($this->state)
			{
				case 'Disconnected':
					return('1 connection was not yet established');
				case 'Connected':
					return('2 request was not sent');
				case 'RequestSent':
					if(($error = $this->ReadReplyHeaders(&$headers)) != '')
					{
						return($error);
					}
					break;
				case 'GotReplyHeaders':
					break;
				default:
					return('3 can not get request headers in the current connection state');
			}
			$body = '';
			if($this->content_length_set)
			{
				$length = min($this->content_length-$this->read_length,$length);
			}
			if($length>0 && !$this->EndOfInput() && ($body = $this->ReadBytes($length)) == '')
			{
				return('4 could not get the request reply body');
			}
			return('');
		}

		function GetPersistentCookies(&$cookies)
		{
			$now = gmdate('Y-m-d H-i-s');
			$cookies = array();
            $cookies_count = count($this->cookies);
			for($secure_cookies = 0,Reset($this->cookies);$secure_cookies<$cookies_count;Next($this->cookies),$secure_cookies++)
			{
				$secure = Key($this->cookies);
                $cookies_count_secure = count($this->cookies[$secure]);
				for($domain = 0,Reset($this->cookies[$secure]);$domain<$cookies_count_secure;Next($this->cookies[$secure]),$domain++)
				{
					$domain_pattern = Key($this->cookies[$secure]);
                    $cookies_count_domain_pattern = count($this->cookies[$secure][$domain_pattern]);
					for(Reset($this->cookies[$secure][$domain_pattern]),$path_part=0;$path_part<$cookies_count_domain_pattern;Next($this->cookies[$secure][$domain_pattern]),$path_part++)
					{
						$path=Key($this->cookies[$secure][$domain_pattern]);
                        $cookies_count_path = count($this->cookies[$secure][$domain_pattern][$path]);
						for(Reset($this->cookies[$secure][$domain_pattern][$path]),$cookie=0;$cookie<$cookies_count_path;Next($this->cookies[$secure][$domain_pattern][$path]),$cookie++)
						{
							$cookie_name = Key($this->cookies[$secure][$domain_pattern][$path]);
							$expires     = $this->cookies[$secure][$domain_pattern][$path][$cookie_name]['expires'];
							if($expires != '' && strcmp($now,$expires)<0)
							{
								$cookies[$secure][$domain_pattern][$path][$cookie_name] = $this->cookies[$secure][$domain_pattern][$path][$cookie_name];
							}
						}
					}
				}
			}
		}
	}
