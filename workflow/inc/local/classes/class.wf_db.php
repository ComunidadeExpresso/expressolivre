<?php

	/**
	* Connection to workflow database
	* @license http://www.gnu.org/copyleft/gpl.html GPL
	* @package Workflow
	* @subpackage local
	**/
	class wf_db extends db
	{
	/**
	 * Construtor da classe wf_db Inicializa os dados da classe
	 * @access public
	 * @return object
 	 */
		function wf_db()
		{
			parent::db();

			$dbconf = array(
						'database_name'	=> '',
						'database_host'	=> '',
						'database_port'	=> '',
						'database_user'	=> '',
						'database_password'	=> '',
						'execute_activities_in_debug_mode' => 0
			);

			$conf_db_values = $GLOBALS['workflow']['wf_runtime']->getConfigValues($dbconf);

			$this->Database = $conf_db_values['database_name'];
			$this->Host     = $conf_db_values['database_host'];
			$this->Port		= $conf_db_values['database_port'];
			$this->User		= $conf_db_values['database_user'];
			$this->Password = $conf_db_values['database_password'];
			$this->Type		= $conf_db_values['database_type'];

			if ($conf_db_values['execute_activities_in_debug_mode'])
			{
				$this->Halt_On_Error = 'yes';
			}
			else
			{
				$this->Halt_On_Error = 'no';
			}
		}
	/**
	 * Conecta com o banco de dados
	 * @param string $Database Nome do banco de dados
	 * @param string $Host Nome do Servidor
	 * @param int $Port Porta do Servidor
	 * @param string $User Nome do usuário
	 * @param string $Password Senha do usuário
	 * @param string $Type Tipo do Banco de dados
	 * @return object
	 * @access public
 	 */
		function connect($Database = NULL, $Host = NULL, $Port = NULL, $User = NULL, $Password = NULL,$Type = NULL)
		{
			/* Handle defaults */
			if (!is_null($Database) && $Database)
			{
				$this->Database = $Database;
			}
			if (!is_null($Host) && $Host)
			{
				$this->Host     = $Host;
			}
			if (!is_null($Port) && $Port)
			{
				$this->Port     = $Port;
			}
			if (!is_null($User) && $User)
			{
				$this->User     = $User;
			}
			if (!is_null($Password) && $Password)
			{
				$this->Password = $Password;
			}
			if (!is_null($Type) && $Type)
			{
				$this->Type = $Type;
			}
			elseif (!$this->Type)
			{
				$this->Type = $GLOBALS['phpgw_info']['server']['db_type'];
			}

			if (strcasecmp($this->Database,$GLOBALS['phpgw_info']['server']['db_name']) == 0)
			{
				return null;
			}

			parent::connect();
		}

		/**
		 * Desconecta do banco de dados
		 * @return void
		 * @access public
		 */
		function disconnect()
		{
			$this->Link_ID->Close();
		}

		/**
		 * Utilizado para substituir "\\" e "'" nos campos passados como parametro.
		 * @param mixed (string ou array)
		 * @return mixed (string ou array)
		 * @access public
		 */
		function quote($fields){
		    if (is_array($fields)) {
		        foreach($fields as $key => $value) {
					$fields[$key] = str_replace("\\", "\\\\\\\\", str_replace("'", "''", $value));
		        }
		    } elseif (is_string($fields)){
				$fields = str_replace("\\", "\\\\\\\\", str_replace("'", "''", $fields));
		    }
		    return $fields;
		}

		/**
		 * Utilizado para escapar alguns caracteres de valores do tipo bytea (blob).
		 * @param string $byteaValue Variável com conteúdo binário
		 * @return string Valor com os caracteres já escapados
		 * @access public
		 */
		function escapeBytea($byteaValue)
		{
			return str_replace(array(chr(92), chr(0), chr(39)), array('\134', '\000', '\047'), $byteaValue);
		}

		/**
		 * Utilizado para executar queries com possibilidade de fazer bind dos valores.
		 * @param string $sqlStatement Comando SQL
		 * @param array $inputArray Array, seqüencial, com os valores que serão associados à query
		 * @return mixed ResultSet em caso de sucesso ou false/null caso contrário
		 * @access public
		 */
		function query($sqlStatement, $inputArray = false)
		{
			if (!is_array($inputArray))
				return parent::query($sqlStatement);

			if (!$this->Link_ID && !$this->connect())
				return false;
			return $this->Link_ID->query($sqlStatement, $inputArray);
		}

		/**
		 * Utilizado para executar queries retornando apenas algumas tuplas
		 * @param string $sqlStatement Comando SQL
		 * @param int $offset Indica a primeira tupla que será retornada da consulta
		 * @param int $line A linha do arquivo de código que está executando esta chamada. Normalmente, utiliza-se a variável mágica __LINE__
		 * @param string $file O arquivo de código que está fazendo a chamada. Normalmente, utiliza-se a variável mágica __FILE__
		 * @param int $numRows O número de tuplas que serão retornadas
		 * @return mixed ResultSet em caso de sucesso ou false/null caso contrário
		 * @access public
		 */
		function limit_query($sqlStatement, $offset, $line = '', $file = '', $numRows = '')
		{
			return parent::query($sqlStatement, $line, $file, $offset, $numRows);
		}

		/**
		 * Inicia uma transação
		 * @return void
		 * @access public
		 */
		function startTrans()
		{
			$this->Link_ID->StartTrans();
		}

		/**
		 * Finaliza uma transação. Quando $autoComplete é igual a true (valor padrão), é feito um commit se não forem encontrados erros SQL durante a transação e rollback caso ocorra erro.
		 * @param bool $autoComplete Se true (padrão), executa commit quando nenhum erro é encontrado e rollback caso contrário. Se seu valor for false, mesmo que não ocorram erros, o rollback é executado.
		 * @return bool True em caso de commit ou false em caso de rollback
		 * @access public
		 */
		function completeTrans($autoComplete = true)
		{
			return $this->Link_ID->CompleteTrans($autoComplete);
		}

		/**
		 * Indica a ocorrência de uma falha na transação (necessariamente, não executa o rollback)
		 * @return void
		 * @access public
		 */
		function failTrans()
		{
			$this->Link_ID->FailTrans();
		}
	}
?>
