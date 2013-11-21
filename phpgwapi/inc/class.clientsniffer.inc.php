<?php
  /**************************************************************************\
  * eGroupWare API - Client browser detection                                *
  * ------------------------------------------------------------------------ *
  * This is not part of eGroupWare, but is used by eGroupWare.               * 
  * http://www.egroupware.org/                                               * 
  * ------------------------------------------------------------------------ *
  * This program is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU General Public License as published by the    *
  * Free Software Foundation; either version 2 of the License, or (at your   *
  * option) any later version.                                               *
  \**************************************************************************/

  /******************************************
  ** Description   : PHPClientSniffer
  ** Version       : 1.0.0
  ** File Name     : PHPClientSniffer.php3
  ** Author        : Roger Raymond for PHyX8 studios
  ** Author Email  : roger.raymond@asphyxia.com
  ** Created       : Wednesday, August 23, 2000
  ** Last Modified : 
  ** Modified By   : 
  *'
     INFO:
     Returns client information based on HTTP_USER_AGENT
  
     BASED ON WORKS AND IDEAS BY:   
     Tim Perdue of PHPBuilder.com 
     http://www.phpbuilder.com/columns/tim20000821.php3
     
     The Ultimate JavaScript Client Sniffer by Netscape.
     http://developer.netscape.com/docs/examples/javascript/NAME_type.html
     
     ========================================================================   
     USAGE:
     ========================================================================
     include("PHPClientSniffer.php3");
     $is = new sniffer;
     ========================================================================
     VARIABLE NAMES    VALUES
     ========================================================================
     $is->UA           The HTTP USER AGENT String
     $is->NAME         Browser Name (Netscape, IE, Opera, iCab, Unknown)
     $is->VERSION      Browser Full Version
     $is->MAJORVER     Browser Major Version 
     $is->MINORVER     Browser Minor Version
     $is->AOL          True/False
     $is->WEBTV        True/False
     $is->JS           Assumed JavaScript Version Supported by Browser
     $is->PLATFORM     System Platform (Win16,Win32,Mac,OS2,Unix)
     $is->OS           System OS (Win98,OS2,Mac68k,linux,bsd,etc...) see code
     $is->IP           REMOTE_ADDR
     
     ========================================================================
  
   '****************************************/

	class clientsniffer
	{
		var $UA         =  '';
		var $NAME       =  'Unknown';
		var $VERSION    =  0;
		var $MAJORVER   =  0;
		var $MINORVER   =  0;
		var $AOL        =  false;
		var $WEBTV      =  false;
		var $JS         =  0.0;
		var $PLATFORM   =  'Unknown';
		var $OS         =  'Unknown';
		var $IP         =  'Unknown';

		/* START CONSTRUCTOR */
		function clientsniffer()
		{
			$this->UA = getenv(HTTP_USER_AGENT);

			// Determine NAME Name and Version      
			if ( preg_match( '/MSIE ([0-9].[0-9a-zA-Z]{1,4})/i',$this->UA,$info) ||
				preg_match( '/Microsoft Internet Explorer ([0-9].[0-9a-zA-Z]{1,4})/i',$this->UA,$info) ) 
			{
				$this->VERSION = $info[1];
				$this->NAME = 'IE';
			} 
			elseif ( preg_match( '/Opera ([0-9].[0-9a-zA-Z]{1,4})/i',$this->UA,$info) ||
				preg_match( '/Opera\/([0-9].[0-9a-zA-Z]{1,4})/i',$this->UA,$info) ) 
			{
				$this->VERSION = $info[1];
				$this->NAME = 'Opera';
			}
			elseif ( preg_match( '/iCab ([0-9].[0-9a-zA-Z]{1,4})/i',$this->UA,$info) ||
				preg_match( '/iCab\/([0-9].[0-9a-zA-Z]{1,4})/i',$this->UA,$info) ) 
			{
				$this->VERSION = $info[1];
				$this->NAME = 'iCab';
			}
			elseif ( preg_match( '/Netscape6\/([0-9].[0-9a-zA-Z]{1,4})/i',$this->UA,$info) ) 
			{
				$this->VERSION = $info[1];
				$this->NAME = 'Netscape';
			}
			elseif ( preg_match( '/Mozilla\/([0-9].[0-9a-zA-Z]{1,4})/i',$this->UA,$info) ) 
			{
				$this->VERSION = $info[1];
				$this->NAME = 'Netscape';
			}
			else 
			{
				$this->VERSION = 0;
				$this->NAME = 'Unknown';
			}

			// Determine if AOL or WEBTV
			if( preg_match( '/aol/i',$this->UA,$info))
			{
				$this->AOL = true;
			}
			elseif( preg_match( '/webtv/i',$this->UA,$info))
			{
				$this->WEBTV = true;
			}

			// Determine Major and Minor Version
			if($this->VERSION > 0)
			{
				$pos = strpos($this->VERSION,'.');
				if ($pos > 0)
				{
					$this->MAJORVER = substr($this->VERSION,0,$pos);
					$this->MINORVER = substr($this->VERSION,$pos,strlen($this->VERSION));
				}
				else
				{
					$this->MAJORVER = $this->VERSION; 
				}
			}

			// Determine Platform and OS

			// Check for Windows 16-bit
			if( preg_match('/Win16/i',$this->UA)           || 
			preg_match('/windows 3.1/i',$this->UA)     || 
			preg_match('/windows 16-bit/i',$this->UA)  || 
			preg_match('/16bit/i',$this->UA))
			{
				$this->PLATFORM = 'Win16';
				$this->OS = 'Win31';
			}

			// Check for Windows 32-bit     
			if(preg_match('/Win95/i',$this->UA) || preg_match('/windows 95/i',$this->UA)) 
			{
				$this->PLATFORM = 'Win32'; 
				$this->OS = 'Win95'; 
			}
			elseif(preg_match('/Win98/i',$this->UA) || preg_match('/windows 98/i',$this->UA)) 
			{
				$this->PLATFORM = 'Win32'; 
				$this->OS = 'Win98'; 
			}
			elseif(preg_match('/WinNT/i',$this->UA) || preg_match('/windows NT/i',$this->UA)) 
			{
				$this->PLATFORM = 'Win32'; 
				$this->OS = 'WinNT'; 
			}
			else
			{
				$this->PLATFORM = 'Win32'; 
				$this->OS = 'Win9xNT'; 
			}

			// Check for OS/2
			if( preg_match('/os\/2/i',$this->UA) || preg_match('/ibm-webexplorer/i',$this->UA))
			{
				$this->PLATFORM = 'OS2';
				$this->OS = 'OS2';  
			}

			// Check for Mac 68000
			if( preg_match('/68k/i',$this->UA) || preg_match('/68000/i',$this->UA))
			{
				$this->PLATFORM = 'Mac';
				$this->OS = 'Mac68k';
			}

			//Check for Mac PowerPC
			if( preg_match('/ppc/i',$this->UA) || preg_match('/powerpc/i',$this->UA))
			{
				$this->PLATFORM = 'Mac';
				$this->OS = 'MacPPC';
			}

			// Check for Unix Flavor

			//SunOS
			if(preg_match('/sunos/i',$this->UA)) 
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'sun';
			}
			if(preg_match('/sunos 4/i',$this->UA)) 
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'sun4';
			}
			elseif(preg_match('/sunos 5/i',$this->UA)) 
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'sun5';
			}
			elseif(preg_match('/i86/i',$this->UA)) 
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'suni86';
			}

			// Irix
			if(preg_match('/irix/',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'irix';
			}
			if(preg_match('/irix 6/i',$this->UA)) 
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'irix6';
			}
			elseif(preg_match('/irix 5/i',$this->UA)) 
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'irix5';
			}

			//HP-UX
			if(preg_match('/hp-ux/i',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'hpux';
			}
			if(preg_match('/hp-ux/i',$this->UA) && preg_match('/10./',$this-UA))  
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'hpux10';
			}
			elseif(preg_match('/hp-ux/i',$this->UA) && preg_match('/09./',$this-UA))  
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'hpux9';
			}

			//AIX
			if(preg_match('/aix/i',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'aix';
			}
			if(preg_match('/aix1/i',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'aix1';
			}
			elseif(preg_match('/aix2/i',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'aix2';
			}
			elseif(preg_match('/aix3/i',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'aix3';
			}
			elseif(preg_match('/aix4/i',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'aix4';
			}

			// Linux
			if(preg_match('/inux/i',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'linux';
			}

			//Unixware
			if(preg_match('/unix_system_v/i',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'unixware';
			}

			//mpras
			if(preg_match('/ncr/i',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'mpras';
			}

			//Reliant
			if(preg_match('/reliantunix/i',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'reliant';
			}

			// DEC
			if(preg_match('/dec/i',$this->UA)           ||  
			preg_match('/osfl/i',$this->UA)          || 
			preg_match('/alphaserver/i',$this->UA)   || 
			preg_match('/ultrix/i',$this->UA)        || 
			preg_match('/alphastation/i',$this->UA))
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'dec';
			}

			// Sinix
			if(preg_match('/sinix/i',$this->UA))    
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'sinix';
			}

			// FreeBSD
			if(preg_match('/freebsd/i',$this->UA))    
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'freebsd';
			}

			// BSD
			if(preg_match('/bsd/i',$this->UA))    
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'bsd';
			}

			// VMS
			if(preg_match('/vax/i',$this->UA) || preg_match('/openvms/i',$this->UA))    
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'vms';
			}

			// SCO
			if(preg_match('/sco/i',$this->UA) || preg_match('/unix_sv/i',$this->UA))    
			{
				$this->PLATFORM = 'Unix';
				$this->OS = 'sco';
			}

			// Assume JavaScript Version

			// make the code a bit easier to read
			$ie  = preg_match('/ie/i',$this->NAME);
			$ie5 = ( preg_match('/ie/i',$this->NAME) && ($this->MAJORVER >= 5) );
			$ie4 = ( preg_match('/ie/i',$this->NAME) && ($this->MAJORVER >= 4) );
			$ie3 = ( preg_match('/ie/i',$this->NAME) && ($this->MAJORVER >= 3) );

			$nav  = preg_match('/netscape/i',$this->NAME);
			$nav5 = ( preg_match('/netscape/i',$this->NAME) && ($this->MAJORVER >= 5) );
			$nav4 = ( preg_match('/netscape/i',$this->NAME) && ($this->MAJORVER >= 4) );
			$nav3 = ( preg_match('/netscape/i',$this->NAME) && ($this->MAJORVER >= 3) );
			$nav2 = ( preg_match('/netscape/i',$this->NAME) && ($this->MAJORVER >= 2) );

			$opera = preg_match('/opera/i',$this->NAME);

			// do the assumption
			// update as new versions are released

			// Provide upward compatibilty
			if($nav && ($this->MAJORVER > 5))
			{
				$this->JS = 1.4;
			}
			elseif($ie && ($this->MAJORVER > 5))
			{
				$this->JS = 1.3;
			}
			// check existing versions
			elseif($nav5)
			{
				$this->JS = 1.4;
			}
			elseif(($nav4 && ($this->VERSION > 4.05)) || $ie4)
			{
				$this->JS = 1.3;
			}
			elseif(($nav4 && ($this->VERSION <= 4.05)) || $ie4)
			{
				$this->JS = 1.2;
			}
			elseif($nav3 || $opera)
			{
				$this->JS = 1.1;
			}
			elseif(($nav && ($this->MAJORVER >= 2)) || ($ie && ($this->MAJORVER >=3)))
			{
				$this->JS = 1.0;
			}
			//no idea
			else
			{
				$this->JS = 0.0;
			}

			// Grab IP Address
			$this->IP = getenv('REMOTE_ADDR');
		}
	}
