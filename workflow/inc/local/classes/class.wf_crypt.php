<?php
/**
* Criptografia simples e segura baseada em funções hash   
* @author	Marc Wöhlken, woehlken@quadracom.de
* @author	Carlos Eduardo Nogueira Gonçalves
* @version 1.0
* @license http://www.gnu.org/copyleft/gpl.html GPL
* @package Workflow 
* @subpackage local
**/	
class wf_crypt {
	/**
	 * @var	string $hash_key Versão embaralhada da chave de criptografia fornecida pelo usuário
	 * @access public
	 **/
	var $hash_key;
	/**
	 * @var	int $hash_length Comprimento da string dos valores criptografados usando o algoritmo atual
	 * @access public
	 **/	
	var $hash_length;
	/**
	 * @var	boolean $base64 Usar codificação base64 
	 * @access public
	 **/	
	var $base64;
	/**
	 * @var	string $salt Valor secreto que randomiza a saída e protege a chave fornecida pelo usuário	
	 * @access public
	 **/	
	var $salt = 'd41d8cd98f00b204e9800998ecf8427e';
	
	/**
	 * Construtor 
	 * @return object
	 * @access public
	 */
	function wf_crypt() {
		$moduleConf = parse_ini_file(PHPGW_SERVER_ROOT . SEP . 'workflow' . SEP . 'inc' . SEP . 'config' . SEP . 'module.ini', true);
		$this->setKey($moduleConf['classes']['wf_crypt'], true);
	}
	
	/**
	 * Usado para definir a chave de criptografia e descriptografia
	 * @param	string	$key	Chave secreta usada para criptografia e descriptografia
	 * @param	boolean	$base64	Usar codificação base64
	 * @return void 
	 * @access public
	 */
	function setKey($key, $base64 = true) {
		$this->base64 = $base64;		
		$this->hash_key = $this->_hash($key);		
		$this->hash_length = strlen($this->hash_key);
	}
		
	/**
	 * Criptografa dados
	 * @param	string	$string	Informação a ser criptografada
	 * @return string	Informação criptografada
	 * @access public
	 */
	function encrypt($string) {
		$iv = $this->_generate_iv();		
		$out = '';		
		for($c=0;$c < $this->hash_length;++$c) {
			$out .= chr(ord($iv[$c]) ^ ord($this->hash_key[$c]));
		}
		$key = $iv;
		$c = 0;
		while($c < strlen($string)) {
			if(($c != 0) and ($c % $this->hash_length == 0)) {
				$key = $this->_hash($key . substr($string,$c - $this->hash_length,$this->hash_length));
			}
			$out .= chr(ord($key[$c % $this->hash_length]) ^ ord($string[$c]));
			++$c;
		}
		if($this->base64) $out = base64_encode($out);
		return $out;
	}
	
	/**
	 * Descriptografa
	 * @param	string	$string Informação a ser descriptografada	
	 * @return string	Informação descriptografada
	 * @access public
	 */
	function decrypt($string) {
		if($this->base64) $string = base64_decode($string);
		$tmp_iv = substr($string,0,$this->hash_length);
		$string = substr($string,$this->hash_length,strlen($string) - $this->hash_length);
		$iv = $out = '';
		for($c=0;$c < $this->hash_length;++$c) {
			$iv .= chr(ord($tmp_iv[$c]) ^ ord($this->hash_key[$c]));
		}
		$key = $iv;
		$c = 0;
		while($c < strlen($string)) {
			if(($c != 0) and ($c % $this->hash_length == 0)) {
				$key = $this->_hash($key . substr($out,$c - $this->hash_length,$this->hash_length));
			}
			$out .= chr(ord($key[$c % $this->hash_length]) ^ ord($string[$c]));
			++$c;
		}
		return $out;
	}

	/**
	 * Função de hash usada para criptografar
	 * @access private
	 * @param	string	$string	Informação a ser criptografada
	 * @return string	Valor criptografado dos dados de entrada
	 */
	function _hash($string) {
		if(function_exists('sha1')) {
			$hash = sha1($string);
		} else {
			$hash = md5($string);
		}
		$out ='';
		for($c=0;$c<strlen($hash);$c+=2) {
			$out .= $this->_hex2chr($hash[$c] . $hash[$c+1]);
		}
		return $out;
	}
	
	/**
	 * Gera uma string aleatória para inicializar criptografia
	 * @access private
	 * @return string	String pseudo-aleatória
	 **/
	function _generate_iv() {
		srand ((double)microtime()*1000000);
		$iv  = $this->salt;
		$iv .= rand(0,getrandmax());
		$iv .= serialize($GLOBALS);
		return $this->_hash($iv);
	}
	
	/**
	 * Converte valor hexadecimal para string binária
	 * @access private
	 * @param	string	Número hexadecimal entre 00 e ff
	 * @return	string	Caracter representando o valor de entrada
	 **/
	function _hex2chr($num) {
		return chr(hexdec($num));
	}
}
?>