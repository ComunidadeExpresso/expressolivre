<?php
  /**************************************************************************\
  * eGroupWare API - Browser detect functions                                *
  * This file written by Miles Lott <milosch@groupwhere.org>                 *
  * Majority of code borrowed from Sourceforge 2.5                           *
  * Copyright 1999-2000 (c) The SourceForge Crew - http://sourceforge.net    *
  * Browser detection functions for eGroupWare developers                    *
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


	class browser
	{
		var $BROWSER_AGENT;
		var $BROWSER_VER;
		var $BROWSER_PLATFORM;
		var $br;
		var $p;
		var $data;

		const PLATFORM_WINDOWS = 'Win';
		const PLATFORM_MAC = 'Mac';
		const PLATFORM_LINUX = 'Linux';
		const PLATFORM_BEOS = 'Beos';
		const PLATFORM_IPHONE = 'iPhone';
		const PLATFORM_IPOD = 'iPod';
		const PLATFORM_IPAD = 'iPad';
		const PLATFORM_BLACKBERRY = 'BlackBerry';
		const PLATFORM_NOKIA = 'Nokia';
		const PLATFORM_ANDROID = 'Android';
		const PLATFORM_UNIX = 'Unix';
		const PLATFORM_WINMOBILE = 'WinMobile';

		function browser()
		{
			$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
			/*
				Determine browser and version
			*/
			if(preg_match('/MSIE ([0-9].[0-9]{1,2})/',$HTTP_USER_AGENT,$log_version))
			{
				$this->BROWSER_VER = $log_version[1];
				$this->BROWSER_AGENT = 'IE';
			}
			elseif(preg_match('/Opera ([0-9].[0-9]{1,2})/',$HTTP_USER_AGENT,$log_version) ||
				preg_match('/Opera\/([0-9].[0-9]{1,2})/',$HTTP_USER_AGENT,$log_version))
			{
				$this->BROWSER_VER   = $log_version[1];
				$this->BROWSER_AGENT = 'OPERA';
			}
			elseif(preg_match('/iCab ([0-9].[0-9a-zA-Z]{1,4})/i',$HTTP_USER_AGENT,$log_version) ||
				preg_match('/iCab\/([0-9].[0-9a-zA-Z]{1,4})/i',$HTTP_USER_AGENT,$log_version))
			{
				$this->BROWSER_VER   = $log_version[1];
				$this->BROWSER_AGENT = 'iCab';
			} 
			elseif(preg_match('/Gecko/',$HTTP_USER_AGENT,$log_version))
			{
				if(isset($log_version[1]))
				$this->BROWSER_VER   = $log_version[1];
				$this->BROWSER_AGENT = 'MOZILLA';
			}
			elseif(preg_match('/Konqueror\/([0-9].[0-9].[0-9]{1,2})/',$HTTP_USER_AGENT,$log_version) ||
				preg_match('/Konqueror\/([0-9].[0-9]{1,2})/',$HTTP_USER_AGENT,$log_version))
			{
				$this->BROWSER_VER=$log_version[1];
				$this->BROWSER_AGENT='Konqueror';
			}
			else
			{
				$this->BROWSER_VER=0;
				$this->BROWSER_AGENT='OTHER';
			}

			/*
				Determine platform
			*/
			if(strstr($HTTP_USER_AGENT,'iPad'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_IPAD;
			}
			elseif(strstr($HTTP_USER_AGENT,'iPod'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_IPOD;
			}
			elseif(strstr($HTTP_USER_AGENT,'iPhone'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_IPHONE;
			}
			elseif(strstr($HTTP_USER_AGENT,'BlackBerry'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_BLACKBERRY;
			}
			elseif(strstr($HTTP_USER_AGENT,'Android'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_ANDROID;
			}
			elseif(strstr($HTTP_USER_AGENT,'Nokia'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_NOKIA;
			}			
			elseif(strstr($HTTP_USER_AGENT,'Mac'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_MAC;
			}
			elseif(strstr($HTTP_USER_AGENT,'IEMobile'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_WINMOBILE;
			}	
			elseif(strstr($HTTP_USER_AGENT,'Win'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_WINDOWS;
			}	
			elseif(strstr($HTTP_USER_AGENT,'Linux'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_LINUX;
			}
			elseif(strstr($HTTP_USER_AGENT,'Unix'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_UNIX;
			}
			elseif(strstr($HTTP_USER_AGENT,'Beos'))
			{
				$this->BROWSER_PLATFORM = self::PLATFORM_BEOS;
			}
			else
			{
				$this->BROWSER_PLATFORM='Other';
			}

			/*
			echo "\n\nAgent: $HTTP_USER_AGENT";
			echo "\nIE: ".browser_is_ie();
			echo "\nMac: ".browser_is_mac();
			echo "\nWindows: ".browser_is_windows();
			echo "\nPlatform: ".browser_get_platform();
			echo "\nVersion: ".browser_get_version();
			echo "\nAgent: ".browser_get_agent();
			*/

			// The br and p functions are supposed to return the correct
			// value for tags that do not need to be closed.  This is
			// per the xhmtl spec, so we need to fix this to include
			// all compliant browsers we know of.
			if($this->BROWSER_AGENT == 'IE')
			{
				$this->br = '<br/>';
				$this->p = '<p/>';
			}
			else
			{
				$this->br = '<br />';
				$this->p = '<p>';
			}
		}

		function return_array()
		{
			$this->data = array(
				'agent'    => $this->get_agent(),
				'version'  => $this->get_version(),
				'platform' => $this->get_platform()
			);

			return $this->data;
		}

		function get_agent()
		{
			return $this->BROWSER_AGENT;
		}

		function get_version()
		{
			return $this->BROWSER_VER;
		}

		function get_platform()
		{
			return $this->BROWSER_PLATFORM;
		}

		function is_linux()
		{
			return ($this->get_platform()==self::PLATFORM_LINUX);
		}

		function is_unix()
		{
			return ($this->get_platform()==self::PLATFORM_UNIX);
		}

        function isMobile( )
		{
			return $this -> is_ipad( )
				|| $this -> is_iphone( )
				|| $this -> is_nokia( )
				|| $this -> is_ipod( )
				|| $this -> is_blackberry( )
				|| $this -> is_android( );
		}

		function is_beos()
		{
			return ($this->get_platform()==self::PLATFORM_BEOS);
		}

		function is_mac()
		{
			return ($this->get_platform()==self::PLATFORM_MAC);
		}

		function is_windows()
		{
			return ($this->get_platform()==self::PLATFORM_WINDOWS);
		}
		
		function is_ipad()
		{
			return ($this->get_platform()==self::PLATFORM_IPAD);
		}
		
		function is_iphone()
		{
			return ($this->get_platform()==self::PLATFORM_IPHONE);
		}
		
		function is_nokia()
		{
			return ($this->get_platform()==self::PLATFORM_NOKIA);
		}
		
		function is_ipod()
		{
			return ($this->get_platform()==self::PLATFORM_IPOD);
		}
		
		function is_blackberry()
		{
			return ($this->get_platform()==self::PLATFORM_BLACKBERRY);
		}
		
		function is_android()
		{
			return ($this->get_platform()==self::PLATFORM_ANDROID);
		}

		function is_ie()
		{
			if($this->get_agent()=='IE')
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		function is_netscape()
		{
			if($this->get_agent()=='MOZILLA')
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		function is_opera()
		{
			if($this->get_agent()=='OPERA')
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		// Echo content headers for file downloads
		function content_header($fn='',$mime='',$length='',$nocache=True)
		{
			// if no mime-type is given or it's the default binary-type, guess it from the extension
			if(empty($mime) || $mime == 'application/octet-stream')
			{
				$mime_magic = createObject('phpgwapi.mime_magic');
				$mime = $mime_magic->filename2mime($fn);
			}
			if($fn)
			{
				if($this->get_agent() == 'IE') // && browser_get_version() == "5.5")
				{
					$attachment = '';
				}
				else
				{
					$attachment = ' attachment;';
				}

				// Show this for all
				header('Content-disposition:'.$attachment.' filename="'.$fn.'"');
				header('Content-type: '.$mime);

				if($length)
				{
					header('Content-length: '.$length);
				}

				if($nocache)
				{
					header('Pragma: no-cache');
					header('Pragma: public');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				}
			}
		}
	}
?>
