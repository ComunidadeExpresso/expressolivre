<?php
/**************************************************************************\
* Copyright (C) 2003  Gary White                                           *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
*                                                                          *
*  This program is distributed in the hope that it will be useful,         *
*  but WITHOUT ANY WARRANTY; without even the implied warranty of          *
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           *
*  GNU General Public License for more details at:                         *
*  http://www.gnu.org/copyleft/gpl.html                                    *
\**************************************************************************/

/**
 * Interpreta informações enviadas pelo navegador do usuário
 * @author Gary White
 * @author Sidnei Augusto Drovetto Jr. - drovetto@gmail.com (revision)
 * @version 1.0
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class BrowserInfo
{
	/**
	 * @var string $name O nome do navegador
	 * @access private
	 */
	private $name = "Unknown";

	/**
	 * @var string $version A versão do navegador
	 * @access private
	 */
	private $version = "Unknown";

	/**
	 * @var string $platform O sistema operacional do usuário
	 * @access private
	 */
	private $platform = "Unknown";

	/**
	 * @var string $userAgent O agente do navegador do usuário
	 * @access private
	 */
	private $userAgent = "Not reported";

	/**
	 * @var bool Indica se o MSIE é uma versão modificada pela AOL
	 * @access private
	 */
	private $AOL = false;

	/**
	 * Construtor da classe
	 * @return object
	 * @access public
	 */
	function BrowserInfo()
	{
		$agent = $_SERVER['HTTP_USER_AGENT'];

		// initialize properties
		$bd['platform'] = "Unknown";
		$bd['browser'] = "Unknown";
		$bd['version'] = "Unknown";
		$this->userAgent = $agent;

		// find operating system
		if (preg_match('/win/i', $agent))
			$bd['platform'] = "Windows";
		elseif (preg_match('/mac/i', $agent))
			$bd['platform'] = "MacIntosh";
		elseif (preg_match('/linux/i', $agent))
			$bd['platform'] = "Linux";
		elseif (preg_match('/OS\/2/i', $agent))
			$bd['platform'] = "OS/2";
		elseif (preg_match('/BeOS/i', $agent))
			$bd['platform'] = "BeOS";

		// test for Opera
		if (preg_match('/opera/i',$agent))
		{
			$val = stristr($agent, "opera");
			if (preg_match('/\//i', $val))
			{
				$val = explode("/",$val);
				$bd['browser'] = $val[0];
				$val = explode(" ",$val[1]);
				$bd['version'] = $val[0];
			}
			else
			{
				$val = explode(" ",stristr($val,"opera"));
				$bd['browser'] = $val[0];
				$bd['version'] = $val[1];
			}

		// test for WebTV
		}
		elseif (preg_match('/webtv/i',$agent))
		{
			$val = explode("/",stristr($agent,"webtv"));
			$bd['browser'] = $val[0];
			$bd['version'] = $val[1];

		// test for MS Internet Explorer version 1
		}
		elseif (preg_match('/microsoft internet explorer/i', $agent))
		{
			$bd['browser'] = "MSIE";
			$bd['version'] = "1.0";
			$var = stristr($agent, "/");
			if (preg_match('/308|425|426|474|0b1/', $var))
				$bd['version'] = "1.5";

		// test for NetPositive
		}
		elseif (preg_match('/NetPositive/i', $agent))
		{
			$val = explode("/",stristr($agent,"NetPositive"));
			$bd['platform'] = "BeOS";
			$bd['browser'] = $val[0];
			$bd['version'] = $val[1];

		// test for MS Internet Explorer
		}
		elseif (preg_match('/msie/i',$agent) && !preg_match('/opera/i',$agent))
		{
			$val = explode(" ",stristr($agent,"msie"));
			$bd['browser'] = $val[0];
			$bd['version'] = $val[1];

		// test for MS Pocket Internet Explorer
		}
		elseif (preg_match('/mspie/i',$agent) || preg_match('/pocket/i', $agent))
		{
			$val = explode(" ",stristr($agent,"mspie"));
			$bd['browser'] = "MSPIE";
			$bd['platform'] = "WindowsCE";
			if (preg_match('/mspie/i', $agent))
				$bd['version'] = $val[1];
			else
			{
				$val = explode("/",$agent);
				$bd['version'] = $val[1];
			}

		// test for Galeon
		}
		elseif (preg_match('/galeon/i',$agent))
		{
			$val = explode(" ",stristr($agent,"galeon"));
			$val = explode("/",$val[0]);
			$bd['browser'] = $val[0];
			$bd['version'] = $val[1];

		// test for Konqueror
		}
		elseif (preg_match('/Konqueror/i',$agent))
		{
			$val = explode(" ",stristr($agent,"Konqueror"));
			$val = explode("/",$val[0]);
			$bd['browser'] = $val[0];
			$bd['version'] = $val[1];

		// test for iCab
		}
		elseif (preg_match('/icab/i',$agent))
		{
			$val = explode(" ",stristr($agent,"icab"));
			$bd['browser'] = $val[0];
			$bd['version'] = $val[1];

		// test for OmniWeb
		}
		elseif (preg_match('/omniweb/i',$agent))
		{
			$val = explode("/",stristr($agent,"omniweb"));
			$bd['browser'] = $val[0];
			$bd['version'] = $val[1];

		// test for Phoenix
		}
		elseif (preg_match('/Phoenix/i', $agent))
		{
			$bd['browser'] = "Phoenix";
			$val = explode("/", stristr($agent,"Phoenix/"));
			$bd['version'] = $val[1];

		// test for Firebird
		}
		elseif (preg_match('/firebird/i', $agent))
		{
			$bd['browser']="Firebird";
			$val = stristr($agent, "Firebird");
			$val = explode("/",$val);
			$bd['version'] = $val[1];

		// test for Firefox
		}
		elseif (preg_match('/Firefox/i', $agent))
		{
			$bd['browser']="Firefox";
			$val = stristr($agent, "Firefox");
			$val = explode("/",$val);
			$bd['version'] = $val[1];

	  // test for Mozilla Alpha/Beta Versions
		}
		elseif (preg_match('/mozilla/i',$agent) && preg_match('/rv:[0-9].[0-9][a-b]/i',$agent) && !preg_match('/netscape/i',$agent))
		{
			$bd['browser'] = "Mozilla";
			$val = explode(" ",stristr($agent,"rv:"));
			preg_match('/rv:[0-9].[0-9][a-b]/i',$agent,$val);
			$bd['version'] = str_replace("rv:","",$val[0]);

		// test for Mozilla Stable Versions
		}
		elseif (preg_match('/mozilla/i',$agent) && preg_match('/rv:[0-9]\.[0-9]/i',$agent) && !preg_match('/netscape/i',$agent))
		{
			$bd['browser'] = "Mozilla";
			$val = explode(" ",stristr($agent,"rv:"));
			preg_match('/rv:[0-9]\.[0-9]\.[0-9]/i',$agent,$val);
			$bd['version'] = str_replace("rv:","",$val[0]);

		// test for Lynx & Amaya
		}
		elseif (preg_match('/libwww/i', $agent))
		{
			if (preg_match('/amaya/i', $agent))
			{
				$val = explode("/",stristr($agent,"amaya"));
				$bd['browser'] = "Amaya";
				$val = explode(" ", $val[1]);
				$bd['version'] = $val[0];
			}
			else
			{
				$val = explode("/",$agent);
				$bd['browser'] = "Lynx";
				$bd['version'] = $val[1];
			}

		// test for Safari
		}
		elseif (preg_match('/safari/i', $agent))
		{
			$bd['browser'] = "Safari";
			$bd['version'] = "";

		// remaining two tests are for Netscape
		}
		elseif (preg_match('/netscape/i',$agent))
		{
			$val = explode(" ",stristr($agent,"netscape"));
			$val = explode("/",$val[0]);
			$bd['browser'] = $val[0];
			$bd['version'] = $val[1];
		}
		elseif (preg_match('/mozilla/i',$agent) && !preg_match('/rv:[0-9]\.[0-9]\.[0-9]/i',$agent))
		{
			$val = explode(" ",stristr($agent,"mozilla"));
			$val = explode("/",$val[0]);
			$bd['browser'] = "Netscape";
			$bd['version'] = $val[1];
		}

		// clean up extraneous garbage that may be in the name
		$bd['browser'] = preg_replace('/[^a-z,A-Z]/', '', $bd['browser']);
		// clean up extraneous garbage that may be in the version
		$bd['version'] = preg_replace('/[^0-9,.,a-z,A-Z]/', '', $bd['version']);

		// check for AOL
		if (preg_match('/AOL/i', $agent))
		{
			$var = stristr($agent, "AOL");
			$var = explode(" ", $var);
			$bd['aol'] = preg_replace('/[^0-9,.,a-z,A-Z]/', '', $var[1]);
		}

		// finally assign our properties
		$this->name = $bd['browser'];
		$this->version = $bd['version'];
		$this->platform = $bd['platform'];
		$this->AOL = $bd['aol'];
	}

	/**
	 * Checa se a versão do navegador é igual o superior que a requisitada
	 * @param string $version A versão requisitada
	 * @return bool true se a versão atual é igual ou superior que a requisitada ou false caso contrário
	 * @access private
	 */
	private function checkVersion($version)
	{
		/* in case the version is unknown, return false */
		if ($this->getBrowserVersion() == 'Unknown')
			return false;

		$required = explode('.', $version);
		$current = explode('.', $this->getBrowserVersion());
        $required_count = count($required);
		for ($i = 0; $i < $required_count; ++$i)
		{
			$subRequired = (int) $required[$i];
			$subCurrent = (int) (isset($current[$i]) ? $current[$i] : 0);
			if ($subCurrent < $subRequired)
				return false;
			if ($subCurrent > $subRequired)
				return true;
		}
		return true;
	}

	/**
	 * Retorna o nome do navegador
	 * @return string O nome do navegador
	 * @access public
	 */
	public function getBrowserName()
	{
		return $this->name;
	}

	/**
	 * Retorna a versão do navegador
	 * @return string A versão do navegador
	 * @access public
	 */
	public function getBrowserVersion()
	{
		return $this->version;
	}

	/**
	 * Retorna o sistema operacional do usuário
	 * @return string O sistema operacional do usuário
	 * @access public
	 */
	public function getOperatingSystem()
	{
		return $this->platform;
	}

	/**
	 * Retorna o agente do navegador do usuário
	 * @return string O agente do navegador do usuário
	 * @access public
	 */
	public function getUserAgent()
	{
		return $this->userAgent;
	}

	/**
	 * Checa o tipo e a versão do navegador de acordo com as informações requisitadas
	 * @param string $browser O tipo requisitado
	 * @param string $version A versão requisitada (opcional)
	 * @return bool true se o navegador atual bate com os requisitos ou false caso contrário
	 * @access private
	 */
	private function isBrowser($browser, $version = null)
	{
		if (strpos(strtolower($browser), strtolower($this->getBrowserName())) === 0)
		{
			if ($version == null)
				return true;
			else
				if ($this->checkVersion($version))
					return true;
		}
		return false;
	}

	/**
	 * Checa se o navegador é o Firefox
	 * @param string $version A versão requisitada (opcional)
	 * @return bool true se o navegador atual bate com os requisitos ou false caso contrário
	 * @access private
	 */
	function isFirefox($version = null)
	{
		return $this->isBrowser('firefox', $version);
	}

	/**
	 * Checa se o navegador é o Opera
	 * @param string $version A versão requisitada (opcional)
	 * @param string $version The required version (optional)
	 * @return bool true se o navegador atual bate com os requisitos ou false caso contrário
	 * @access private
	 */
	function isOpera($version = null)
	{
		return $this->isBrowser('opera', $version);
	}

	/**
	 * Checa se o navegador é o Internet Explorer
	 * @param string $version A versão requisitada (opcional)
	 * @return bool true se o navegador atual bate com os requisitos ou false caso contrário
	 * @access private
	 */
	function isIE($version = null)
	{
		return $this->isBrowser('msie', $version);
	}
}
?>