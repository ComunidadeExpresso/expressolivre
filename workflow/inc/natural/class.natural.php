<?php
require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'natural'.SEP.'class.natural_resultset.php');
require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'natural'.SEP.'pos_string.php');
require_once(PHPGW_SERVER_ROOT.SEP.'workflow'.SEP.'inc'.SEP.'natural'.SEP.'nat_types.php');

/**
 * Código ascii do caracter espaço
 * @name SPACE
 */
define (SPACE, ' ');

/**
 * Reader mainframe data class (PHP NatAPI)
 *
 * @author Everton Flávio Rufino Seára - rufino@celepar.pr.gov.br
 * @version 1.0
 * @package Workflow
 * @subpackage natural
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
class Natural
{
	/**
	 * @var string $protocol Protocolo a ser utilizado
	 * @access private
	 */
	private $protocol;
	/**
	 * @var string $endOfProtocol string delimitadora do protocolo
	 * @access private
	 */
	private $endOfProtocol;
	/**
	 * @var string $strSubProgram  Sub-programa
	 * @access private
	 */
	private $strSubProgram;
	/**
	 * @var string $strInputParameter Parametro de entrada
	 * @access private
	 */
	private $strInputParameter;
	/**
	 * @var string $acronym  acronym of sub-program
	 * @access private
	 */
	private $acronym;
	/**
	 * @var boolean $isDBGateway Flag utilizado para determinar se será utilizado o protocolo DBGateway ou DBCON
	 * @access private
	 */
	private $isDBGateway;
	/**
	 * @var int $numRows Número de linhas
	 * @access private
	 */
	private $numRows;
	/**
	 * @var string $IPAddress Endereço IP do Mainframe e.g:"10.15.60.20"
	 * @access private
	 */
	private $IPAddress;
	/**
	 * @var int $serverPort Porta do servidor
	 * @access private
	 */
	private $serverPort;
	/**
	 * @var string $key USU (8 bytes) - user => N000 + 3 positions for key + 1 space byte
	 * @access private
	 */
	private $key;
	/**
	 * @var string $password Senha de acesso ao mainframe (8 bytes)
	 * @access private
	 */
	private $password;
	/**
	 * @var string $application APLIC (4 bytes)  application => 3 bytes for system acronym AND 1 byte to environment 'P' or 'D'
	 * @access private
	 */
	private $application;
	/**
	 * @var string $system Sistema (4 bytes)
	 * @access private
	 */
	private $system;
	/**
	 * @var string $logon LOGON (8 bytes)
	 * @access private
	 */
	private $logon;
	/**
	 * @var string $rc  PA_RC - Código de Retorno (4 bytes)
	 * @access private
	 */
	private $rc;
	/**
	 * @var string $msg PA_MSG - Mensagem de retorno (60 bytes)
	 * @access private
	 */
	private $msg;
	/**
	 * @var string $dataParameter Return of MAINFRAME environment
	 * @access private
	 */
	private $dataParameter;
 	/**
	 * @var object $resultSet Armazena o resultado das consulta
	 * @access public
	 */
 	public $resultSet;


	/**
	 * @var object $obj Objeto de Classe que contém a especificação e estrutura do programa natural acessado
	 * @access protected
	 */
	protected $obj;

	/**
	 * Construtor da clase Natural
	 * @access public
	 */
	public function Natural()
	{
		$this->protocol = "/cics/cwba/jaspin?";
		$this->endOfProtocol = "!jaspin_fim_de_dados!";
		$this->isDBGateway = false;
	}

	/**
	 * Carrega atributos da classe com valores iniciais
	 * @param String - Name of sub-program
	 * @param String - Input parameters to sub-program
	 * @access protected
	 * @return void
	 */
	protected function initialize($strSubProgram/*, $strInputParameter*/)
	{
		$this->strSubProgram = strtoupper($strSubProgram);
//		$this->strInputParameter = strtoupper($strInputParameter);

		// get the acronym of the sub-program
		$this->acronym = strtoupper(substr($strSubProgram, 0, 3));

		// set the system
		$this->system = str_repeat("X", 4);

		// set the static logon
		$this->logon = "N000" . $this->acronym . SPACE;

		// initialize the return code
		$this->rc = "9999";

		// initialize the return message
		$this->msg = str_repeat(SPACE, 60);

		// initialize the data parameter
		$this->dataParameter = "0000000000";
	}

	/**
	 * Determina o protocolo a ser usado pelo Gateway
	 *
	 * In DBGateway protocol the size of returned data does not have any bound.
	 * if you does not set DBGataway, PHP NatAPI will use DBCON. Using DBCon you can transfer only 32Kb of data.
	 * @param String $dbGateway set BDGateway protocol
	 * @access public
	 * @return void
	 */
	public function setDBGatewayProtocol($dbGateway = true)
	{
		$this->isDBGateway = $dbGateway;
		if ($dbGateway){
			$this->protocol = "/cics/cwba/jasppion?";
			$this->endOfProtocol = "!jasppion_fim_de_dados!";
		} else {
			$this->protocol = "/cics/cwba/jaspin?";
			$this->endOfProtocol = "!jaspin_fim_de_dados!";
		}
	}

	/**
	 * Access and retrieve data from mainframe
	 *
	 * @return boolean
	 * @access protected
	 */
	protected function execute($inputParams = "")
	{

		if (!empty($this->application) && !empty($this->strSubProgram) && !empty($this->key) && !empty($this->password) &&
			!empty($this->IPAddress) && !empty($this->serverPort))
		{
			try
			{
				$str = Factory::newInstance('PosString', Factory::newInstance('NatType'));
				$this->strInputParameter = $str->mountString(array_merge($this->obj->input, $this->obj->output), $inputParams);

				$url = $this->protocol . ($this->acronym . $this->application) . $this->system . $this->logon . $this->strSubProgram . $this->key . $this->password . $this->rc . $this->msg . $this->strInputParameter . $this->endOfProtocol;

				$link = "http://" . $this->IPAddress . ":". $this->serverPort . $url;
				// echo $link . "<br><br>";

				$fp = @fsockopen($this->IPAddress, $this->serverPort, $errnum, $errstr);
				$line = "";
				$size = 0;

				if ($fp) {
					@fputs($fp, "GET " . $url . " HTTP/1.1\r\nHost: " . $this->IPAddress . "\r\nConnection: close\r\n\r\n");
					$line = "";
					while (!feof($fp)) {
						$temp = fread($fp, 128);
						$line = $line . $temp;
					}
					@fclose($fp);

					$pos1 = @strpos($line, "<html><body>");
					if ($pos1) {
						$pos1 = $pos1 +12;
						$pos2 = @strpos($line, "</body></html>");
						if ($pos2) {
							$size = $pos2 - $pos1;
							$line = substr($line, $pos1, $size);
							$this->rc = substr($line, 40, 4);
							$this->msg = substr($line, 44, 60);

							// Verify if user chose DBGateway or DBCon format.
							// In DBGateway, the record count is also returned
							if ($this->isDBGateway){
								$this->numRows = (int)substr($line, 104, 5);
								$this->dataParameter = substr($line, 109, ($size -109));
							} else {
								$this->dataParameter = substr($line, 104, ($size -104));
							}
						} else {
							$this->msg = $line;
						}
					} else {
						$this->msg = $line;
					}
				} else {
					$this->rc = $this->rc . $errnum . "-" . $errstr;
					$this->msg = $errnum . "-" . $errstr;
					return false;
				}
				return $str->mountResult(array_merge($this->obj->input, $this->obj->output), $this->dataParameter);
				// return true;
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Returns the Return Code of the natural sub-program
	 * @return int  Return Code
	 * @access public
	 */
	public function getRC()
	{
		return $this->rc;
	}

	/**
	 * Returns the return message of the natural sub-program
	 * @return string $msg message from mainframe
	 * @access public
	 */
	public function getMSG()
	{
		return $this->msg;
	}

	/**
	 * Retorna os dados recuperados do mainframe
	 * @return string $dataParameter  data recovered
	 * @access public
	 */
	public function getDataParameter()
	{
		return $this->dataParameter;
	}

	/**
	 * Sets the key (N000 + $key + space)
	 * @param int $key  Key
	 * @access protected
	 */
	protected function setKey($key)
	{
		if (strlen($key) < 7)
			$this->key = "N000" . $key . SPACE;
		else
			$this->key = $key . SPACE;
	}

	/**
	 * Sets the logon
	 * @param int $logon  Logon
	 * @access protected
	 */
	protected function setLogon($logon)
	{
		$this->logon = $logon;
	}

	/**
	 * Sets the password
	 * @param string $password Senha de acesso
	 * @access protected
	 * @return void
	 */
	protected function setPassword($password)
	{
		$this->password = $password . str_repeat(SPACE, (8 - strlen($password)));
	}

	/**
	 * Sets the application environment
	 * @param char $app Defines if the app. is in the production or development environment 'D' (development) or 'P' (production)
	 * @access protected
	 * @return void
	 */
	protected function setApplication($app)
	{
		$this->application = $app;
	}

	/**
	 * Sets the IP address to access the mainframe
	 * @param string $ip Endereço Ip do Mainframe e.g 10.15.60.20
	 * @access protected
	 * @return void
	 */
	protected function setIPAddress($ip)
	{
		$this->IPAddress = $ip;
	}

	/**
	 * Seta a porta do servidor para acessar o Mainframe
	 * @param int $portNumber Porta do Servidor  e.g 103
	 * @access protected
	 * @return void
	 */
	protected function setServerPort($portNumber)
	{
		$this->serverPort = $portNumber;
	}

	/**
	 * Seta o sistema oara acesso
	 * @param int $system 4 dígitos em caixa alta
	 * @access protected
	 * @return void
	 */
	protected function setSystem($system)
	{
		$this->system = $system;
	}

	/**
	 * Seta o código de retorno
	 * @param int $rc código de retorno com 4 dígitos
	 * @access protected
	 * @return void
	 */
	protected function setRC($rc)
	{
		$this->rc = $rc;
	}


	/**
	 * Build a result set ($this->resultSet) to mainframe data. The result is returnd in array format
	 *
	 * <code>
	 * $rowConf example - array (name_of_field => size_of_field)
	 * $array = array("id" => 5, "name" => 10);
	 * </code>
	 * @param int $lineSize Size of each line return from mainframe
	 * @param array $rowConf Configuration of the field's name and size of each fild
	 * @return void
	 * @access public
	 */
	 public function configureResultSet($lineSize, $rowConf)
	 {
	 	//$rows = explode("%NAT_SEP%", wordwrap($this->dataParameter, $lineSize, "%NAT_SEP%"));
	 	for ($i=0; $i < strlen($this->dataParameter)/$lineSize; ++$i){
	 		$rows[] = substr($this->dataParameter, $lineSize*$i , $lineSize);
	 	}

	 	$result = array();
	 	// for each row returned from mainframe
	 	foreach ($rows as $id => $values){
	 		$last_id = 0;
	 		// get configuration array to build the result
	 		foreach ($rowConf as $id_conf => $value_conf)
	 		{
	 			$result[$id][$id_conf] = substr($values, $last_id, $value_conf);
	 			$last_id += (int)$value_conf;
	 		}
	 	}

	 	if (!empty($this->resultSet))
	 		unset($this->resultSet);
	 	$this->resultSet = &Factory::newInstance('NaturalResultSet', $result);
	 }
}
?>
