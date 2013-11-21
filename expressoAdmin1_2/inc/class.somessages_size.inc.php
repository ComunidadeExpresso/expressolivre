<?php
	/***********************************************************************************\
	* Expresso Administração															*
	* by Prognus Software Livre (prognus@prognus.com.br, airton@prognus.com.br)      	*
	* ----------------------------------------------------------------------------------*
	*  This program is free software; you can redistribute it and/or modify it			*
	*  under the terms of the GNU General Public License as published by the			*
	*  Free Software Foundation; either version 2 of the License, or (at your			*
	*  option) any later version.														*
	\***********************************************************************************/

define('PHPGW_INCLUDE_ROOT','../');	
define('PHPGW_API_INC','../phpgwapi/inc');
include_once(PHPGW_API_INC.'/class.db.inc.php');


    class somessages_size
    {
        var $db;

		/**
         * Construtor
         */
        function somessages_size()
        {
		    /*
		  	 * Faz a conexão com o banco de dados
		     */
            if (is_array($_SESSION['phpgw_info']['expresso']['server']))
                    $GLOBALS['phpgw_info']['server'] = $_SESSION['phpgw_info']['expresso']['server'];
            else
                    $_SESSION['phpgw_info']['expresso']['server'] = $GLOBALS['phpgw_info']['server'];

            $this->db = new db();
            $this->db->Halt_On_Error = 'no';

            $this->db->connect(
                            $_SESSION['phpgw_info']['expresso']['server']['db_name'],
                            $_SESSION['phpgw_info']['expresso']['server']['db_host'],
                            $_SESSION['phpgw_info']['expresso']['server']['db_port'],
                            $_SESSION['phpgw_info']['expresso']['server']['db_user'],
                            $_SESSION['phpgw_info']['expresso']['server']['db_pass'],
                            $_SESSION['phpgw_info']['expresso']['server']['db_type']
            );
			
        }

		
	    /**
		 * @abstract Insere a regra no banco de dados. 
		 * @param array $params - índice do array contém o nome do campo da tabela e o valor nesse índice é o valor do campo.
		 * @return bool - true se inseriu a regra com sucesso, caso contrário false.
         */
		function insert_rule_db($params) 
		{
            $fields = '';
            $fieldsValues = '';
            
            foreach($params as $key=>$value)
            {
                if($value)
                {
                    $fields .= $key.', ';
                    $fieldsValues .= '\''.$value.'\', ';
                }
            }

            $fields = substr($fields,0,-2);
            $fieldsValues = substr($fieldsValues,0,-2);

            $query = 'INSERT INTO phpgw_expressoadmin_configuration('.$fields.') VALUES ('.$fieldsValues.')';

             if($this->db->query($query))
                return  true;
             else
                return false;
        }

		
        /**
         * @param <int> $pId Pid da regra a qual quer alterar
         * @param array $pFields array index do array = ('Campo da tabela'), vaçpr do array = ('Valor do campo')
         * @return bool True or False
         */
        function updatetRuleInDb($pId, $pFields)
        {

            $fieldsSet = '';

            foreach($pFields as $key=>$value)
                    $fieldsSet .= $key.' = \''.$value.'\', ';

            

            $fieldsSet = substr($fieldsSet,0,-2);

            $query = 'UPDATE phpgw_expressoadmin_configuration' . ' SET '.$fieldsSet.' WHERE id = \''.$pId.'\'';

            if($this->db->query($query))
                return  true;
            else
                return false;


        }

		
	    /**
         * @abstract Remove uma regra do banco de dados
         * @param $rule_name - O nome da regra que deseja remover do banco
         * @return array Retorna A busca em um array
         */
        function remove_rule($rule_name)
        {
            $query = "DELETE FROM phpgw_expressoadmin_configuration WHERE email_recipient = '$rule_name'";

            if($this->db->query($query))
                return  true;
            else
                return false;   
        }

		
        /**
         * @abstract Busca regras no banco de dados
         * @param string $pFilter Filtro em linguagem sql
         * @param array $pFields array com os campos que você queira retornar
         * @return array Retorna A busca em um array
         */
        function getRuleInDb($pFilter = '',$pFields = '')
        {

            $fields = '';

            if($pFields)
            {
                foreach($pFields as $value)
                        $fields .= $value.', ';

                $fields = substr($fields,0,-2);
            }
            else
                 $fields = '*';

            $query = 'SELECT '.$fields.' FROM phpgw_expressoadmin_configuration' . ' ' .$pFilter;

            if(!$this->db->query($query))
                return false;

            $return = array();

            while($this->db->next_record())
                array_push($return, $this->db->row());

            return $return;
        }
		
		
		/**
         * @abstract Insere a regra padrão no banco.
         * @param max_size - o tamanho da regra padrão e default_rule_name o nome que foi configurado para a regra padrão.
         * @return retorna um valor booleano informando se a operação foi concluída com sucesso.
         */
		function insert_default_rule($max_size)
		{
			$fields = array(
                'email_user' => 'default',
                'configuration_type' => 'MessageMaxSize',
                'email_max_recipient' => $max_size,
                'email_user_type' => 'B',
				'email_recipient' => 'default'
            );
		
			$query = "SELECT email_user FROM phpgw_expressoadmin_configuration WHERE email_user = 'default'";
			$result = $this->db->query($query);
			if(count($result) < 1)
			{
				if(!$this->insert_rule_db($fields))
					return false;
			}
			else
			{		
				$query = "UPDATE phpgw_expressoadmin_configuration SET email_max_recipient=" . $max_size ." WHERE email_user='default'";			
				if(!$this->db->query($query))
					return false;
			}
			return true;
		}
		
		
		/**
         * @abstract Insere a regra padrão no banco (Vou inserir em outra tabela, agora na tabela config.
         * @param max_size - o tamanho da regra padrão e default_rule_name o nome que foi configurado para a regra padrão.
         * @return retorna um valor booleano informando se a operação foi concluída com sucesso.
         */
		function insert_default_rule_2($max_size)
		{
			/* Estou inserindo na tabela que guarda as configurações que vão pra sessão. */
			$query = "SELECT config_value FROM phpgw_config WHERE config_name='expressoAdmin_default_max_size'";
			$result = $this->db->query($query);
					
			$i=0;
			
			while($this->db->next_record())
                ++$i;

			if($i==0)
			{			
				/* Se não existem nenhum valor cadastrado ainda, é inserido esse valor na tabela de configuração. */
				$query = "INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES ('expressoMail1_2', 'expressoAdmin_default_max_size', '$max_size')";
				if(!$this->db->query($query))
					return false; 
			}
			else
			{

				/* Se já existe um valor padrão, só atualizo. */
				$query = "UPDATE phpgw_config SET config_value=$max_size WHERE config_name='expressoAdmin_default_max_size'";
				if(!$this->db->query($query))
					return false;
			}
			return true;
		}
		
		
		/**
         * @abstract Retorna a regra padrão.
         * @return retorna um array com a regra padrão e caso a busca não for realizada com sucesso retorna false.
         */
		function get_default_rule()
		{
			$query = "SELECT config_value FROM phpgw_config WHERE config_name = 'expressoAdmin_default_max_size'";
			if(!$this->db->query($query))
                return false;

            $return = array();
			
			while($this->db->next_record())
                array_push($return, $this->db->row());
			
			return $return;
		}
		
		
		/**
         * @abstract Retorna usuários de uma determinada regra.
         * @param name_rule - O nome da regra da qual deseja os participantes.
         * @return retorna um array com os participantes dessa regra e caso a busca não for realizada com sucesso retorna false.
         */
		function get_users_by_rule($name_rule)
		{
			$query = "SELECT email_user, email_max_recipient, email_recipient, email_user_type FROM phpgw_expressoadmin_configuration WHERE email_recipient='" . $name_rule . "'";
			
			if(!$this->db->query($query))
                return false;

            $return = array();

            while($this->db->next_record())
                array_push($return, $this->db->row());
            
			return $return;
		}		
		
		
		/**
         * @abstract Retorna as regras de acordo com um parâmetro de busca. Se $user for true, busca as regras de acordo com usuários.
         * @param input - parâmetro de busca passado pelo usuário e user controla se a busca será feita por nome de usuário ou por nome de regra.
         * @return retorna um array com os participantes dessa regra e caso a busca não for realizada com sucesso retorna false.
         */
		function get_rules($input, $user=false)
		{
			$rules = array();
			if($user)
				$query = "SELECT DISTINCT email_recipient, email_max_recipient FROM phpgw_expressoadmin_configuration WHERE email_user LIKE '%" . $input . "%'";
			else	
				$query = "SELECT DISTINCT email_recipient, email_max_recipient FROM phpgw_expressoadmin_configuration WHERE email_recipient LIKE '%" . $input . "%'";
		
			if(!$this->db->query($query))
                return false;

            $return = array();

            while($this->db->next_record())
                array_push($return, $this->db->row());
            
			return $return;	
		}
		
		
		/**
         * @abstract Retorna todas as regras cadastradas.
         * @return retorna um array com todas as regras e caso a busca não for realizada com sucesso retorna false.
         */
		function get_all_rules()
		{
			$rules = array();
			$query = "SELECT DISTINCT email_recipient, email_max_recipient FROM phpgw_expressoadmin_configuration WHERE configuration_type='MessageMaxSize' ORDER BY email_recipient";  //Seleciona todas as regras já cadastradas.
			
            if(!$this->db->query($query))
                return false;

            $return = array();

            while($this->db->next_record())
                array_push($return, $this->db->row());
            
			return $return;
		}
   	 
		
		/**
         * @abstract Retorna a regra de acordo com o parâmetro - Adaptação do método que retornava true caso a regra existisse e false caso contrário.
         * @param rule_name - Nome da regra.
         * @return retorna um array com os participantes dessa regra e caso a busca não for realizada com sucesso retorna false.
         */
		function get_rule($rule_name)
		{
			$query = 'SELECT email_recipient FROM phpgw_expressoadmin_configuration WHERE email_recipient = \'' . $rule_name . '\''; 
			
            $result = $this->db->query($query);
			
			if(!$this->db->query($query))
                return false;

            $return = array();

            while($this->db->next_record())
                array_push($return, $this->db->row());
            
			return $return;
		}  
    } //end class somessages_size
?>
