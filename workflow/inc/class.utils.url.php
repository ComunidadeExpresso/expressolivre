<?php
require_once('class.utils.php');
/**
 * Contains useful methods for URL building and handling
 * @author Carlos Eduardo Nogueira Gonçalves
 * @author Marcos Pont
 * @version 1.0
 * @link http://workflow.celepar.parana/doc-workflow/classes/urlutils Complete reference
 * @package Workflow
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class UrlUtils extends Utils
{
	/**
	 * @var string $protocol
	 * @access public
	 */
	var $protocol;
	
	/**
	 * @var string $auth
	 * @access public
	 */
	var $auth;
			
	/**
	 * @var string $user
	 * @access public
	 */
	var $user;
	
	/**
	 * @var string $pass
	 * @access public
	 */		
	var $pass;		
	
	/**
	 * @var string $host
	 * @access public
	 */
	var $host;		
	
	/**
	 * @var string $port
	 * @access public
	 */
	var $port;
			
	/**
	 * @var string $path
	 * @access public
	 */
	var $path;
	
	/**
	 * @var string $file
	 * @access public
	 */		
	var $file;
	
	/**
	 * @var string $parameters
	 * @access public
	 */		
	var $parameters;
	
	/**
	 * @var string $fragment
	 * @access public
	 */
	var $fragment;	


	/**
     * Constructor
	 * @access public
	 * @return object
	 */
	function UrlUtils($url='') {		
		if ($url != '') {
			$this->set($url);			
		}
	}
	/**
	 * Set the current url
	 * @param string $url
	 * @access public
	 */
	function set($url) {
		$this->_parse($url);
	}
	
	/**
	 * 
	 * @access public
	 */
	function setFromCurrent() {
		$this->set($this->uri());
	}
	
	/**
	 * Get the current protocol
	 * @return string current protocol
	 * @access public
	 */
	function getProtocol() {
		return (isset($this->protocol) && !empty($this->protocol) ? $this->protocol : NULL);
	}
	
	/**
	 * Get the current protocol scheme
	 * @return string
	 * @access public
	 */
	function getScheme() {
		$protocol = $this->getProtocol();
		if (!$this->isNull($protocol))
			return strtolower($protocol) . '://';
		else
			return NULL;
	}
	
	/**
	 * get current auth
	 * @return string
	 * @access public
	 */
	function getAuth() {
		return (isset($this->auth) && !empty($this->auth) ? $this->auth : NULL);
	}
	/***
	 * Return the user if is set
	 * @access public
	 * @return string
	 */
	function getUser() {
		return (isset($this->user) && !empty($this->user) ? $this->user : NULL);
	}
	
	/**
	 * Get the password 
	 * @access public
	 * @return string 
	 */
	function getPass() {
		return (isset($this->pass) && !empty($this->pass) ? $this->pass : NULL);
	}
	
	/**
	 * Get the host
	 * @access public
	 * @return string
	 */
	function getHost() {
		if (!isset($this->host) || empty($this->host)) {			
			return NULL;
		} 			
		if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $this->host)) {
			return gethostbyaddr($this->host);
		} else {
			return strtolower($this->host);
		}
	}
	
	/**
	 * Get port
	 * @access public
	 * @return string
	 */
	function getPort() {
		return (isset($this->port) && !empty($this->port) ? $this->port : NULL);
	}
	
	/**
	 * Get path 
	 * @access public
	 * @return string
	 */
	function getPath() {
		return (isset($this->path) && !empty($this->path) ? $this->path : NULL);
	}
	/**
	 * Get file
	 * @access public
	 * @return sting
	 */
	function getFile() {
		return (isset($this->file) && !empty($this->file) ? $this->file : NULL);
	}
	/**
	 * Get query string
	 * @return string query string
	 * @access public
	 */
	function getQueryString($prefix=FALSE) {
		return (isset($this->parameters) && !empty($this->parameters) ? ($prefix ? '?' . $this->parameters : $this->parameters) : NULL);
	}
	
	/**
	 * Get query string array
	 * @access public
	 * @return array query string array
	 */
	function getQueryStringArray() {
		$queryString = $this->getQueryString();
		if (!$this->isNull($queryString)) {
			parse_str($queryString, $result);
			return $result;
		}
		return NULL;
	}
	
	
	/**
	 * Add a parameter 
	 * @param $name parameter name
	 * @param $value parameter value
	 * @access public
	 * @return void
	 */
	function addParameter($name, $value) {
		$queryString = $this->getQueryString();
		if (!$this->isNull($queryString)) {
			$result = '';
			parse_str($queryString, $params);			
			$params[$name] = $value;
			foreach ($params as $name => $value)
				$result .= ($result == '' ? "$name=$value" : "&$name=$value");
			$this->parameters = $result;
		} else {
			$this->parameters = "$name=$value";
		}
	}
	
	/**
	 * 
	 * Remove parameter
	 * @param string $name name of parameter
	 * @return void
	 */
	function removeParameter($name) {
		$query = $this->getQueryStringArray();
		if (!$this->isNull($query)) {
			unset($query[$name]);
			$tmp = array();
			foreach ($query as $k => $v)
				$tmp[] = "$k=$v";
			$this->parameters = implode("&", $tmp);
		}
	}
	
	
	/**
	 * Get fragment
	 * @access public
	 * @return string fragment
	 */
	function getFragment() {
		return (isset($this->fragment) && !empty($this->fragment) ? $this->fragment : NULL);
	}

	/**
	 * Get url 
	 * @access public
	 * @return string returns the full url  
	 */
	function getUrl() {
		return sprintf("%s%s%s%s%s%s%s",
			(isset($this->protocol) && !empty($this->protocol) ? "{$this->protocol}://" : ''),
			(isset($this->auth) && !empty($this->auth) ? "{$this->user}:{$this->pass}@" : ''),
			strtolower($this->host),
			(isset($this->port) && !empty($this->port) ? ":{$this->port}" : ''),
			$this->path,
			(isset($this->parameters) && !empty($this->parameters) ? "?{$this->parameters}" : ''),
			(isset($this->fragment) && !empty($this->fragment) ? "#{$this->fragment}" : '')
		);
	}
	
	/**
	 * Get anchor
	 * @access public  
	 * @param $caption
	 * 
	 */
	function getAnchor($caption, $statusBarText='', $cssClass='') {
		return parent::anchor($this->getUrl(), $caption, $statusBarText, $cssClass);
	}
	
	
	/**
	 * Reset the configuration
	 * @access public
	 * @return void 
	 */
	function reset() {
		unset($this->protocol);
		unset($this->auth);
		unset($this->user);
		unset($this->pass);
		unset($this->host);
		unset($this->port);
		unset($this->path);
		unset($this->file);
		unset($this->parameters);
		unset($this->fragment);
	}
	
	/**
	 * Encode the current url
	 * 
	 * @param string $url 
	 * @param string $varName name of variable
	 * @return string encoded url
	 * @access public
	 */
	function encode($url=NULL, $varName='p2gvar') {
		// utiliza como padrao a URL da classe
		if ($this->isNull($url))
			$url = $this->getUrl();
		// busca a string de parametros
		if (preg_match('/([^?#]+\??)?([^#]+)?(.*)/', $url, $matches)) {			
			if (!$this->isFalse($matches[2])) {
				// codifica os parametros
				$paramString = base64_encode(urlencode($matches[2]));
				$returnUrl = $this->parseString($matches[1]) . $varName . '=' . $paramString . $this->parseString($matches[3]);
			} else {
				$returnUrl = $url;
			}
		}
		return $returnUrl;
	}
	
	
	/**
	 * Decode the current url
	 * 
	 * @param string $url url string
	 * @param bool $resultAsArray result is array
	 * @return array parameters array
	 * @access public
	 */
	function decode($url=NULL, $returnAsArray=FALSE) {
		// utiliza como padrï¿½o a URL da classe
		if ($this->isNull($url))
			$url = $this->getUrl();
		// busca os parï¿½metros codificados		
		preg_match('/([^?#]+\??)?([^#]+)?(.*)/', $url, $matches);
		if (!$this->isFalse($matches[2])) {
			parse_str($matches[2], $vars);
			if (list(, $value) = each($vars)) {
				// decodifica o conjunto de parï¿½metros
				$paramString = urldecode(base64_decode($value));
				if ($returnAsArray) {
					parse_str($paramString, $varsArray);
					return $varsArray;
				} else {
					return $this->parseString($matches[1]) . $paramString . $this->parseString($matches[3]);
				}
			}			
		}	
		return FALSE;
	}
	
	/**
	 * Parse the currente url
	 * @param string $url
	 * @return void
	 * @access public 
	 */
	function _parse($url) {
        if (preg_match('!^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?!', $url, $matches)) {
			if (isset($matches[1]))
				$this->protocol = $matches[2];
			if (isset($matches[3]) && isset($matches[4])) {
				$atPos = strpos($matches[4], '@');
				if (!$this->isFalse($atPos)) {
					$this->auth = $this->left($matches[4], $atPos);
					$dotPos = strpos($this->auth, ':');
					if (!$this->isFalse($dotPos)) {
						$auth = explode(':', $this->auth);
						$this->user = $auth[0];
						$this->pass = $auth[1];
					} else {
						$this->user = $this->auth;
					}
					$matches[4] = substr($matches[4], $atPos+1);
				}
				$portPos = strrpos($matches[4], ':');
				if (!$this->isFalse($portPos)) {
					$this->port = $this->parseIntegerPositive(substr($matches[4], $portPos+1));
					if (!$this->port) {
						$this->port = NULL;
					}
				}
				$this->host = $portPos ? $this->left($matches[4], $portPos) : $matches[4];
			}
			if (isset($matches[5])) {
				$this->path = $matches[5];
				$slashPos = strrpos(substr($this->path, 1), '/');
				if (!$this->isFalse($slashPos)) {
					$this->file = substr($this->path, $slashPos + 2);
				}
			}
			$this->path = $matches[5] ? $matches[5] : '';
            if (isset($matches[6]) && $matches[6] != '') 
				$this->parameters = $matches[7];
            if (isset($matches[8]) && $matches[8] != '') 
				$this->fragment = $matches[9];
        }
	}
}
?>