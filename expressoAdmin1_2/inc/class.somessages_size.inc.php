<?php
	/***********************************************************************************\
	* Expresso Administra��o															*
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
		  	 * Faz a conex�o com o banco de dados
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
		 * @param array $params - �ndice do array cont�m o nome do campo da tabela e o valor nesse �ndice � o valor do campo.
		 * @return bool - true se inseriu a regra com sucesso, caso contr�rio false.
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
         * @param array $pFields array index do array = ('Campo da tabela'), va�pr do array = ('Valor do campo')
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
         * @param array $pFields array com os campos que voc� queira retornar
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
         * @abstract Insere a regra padr�o no banco.
         * @param max_size - o tamanho da regra padr�o e default_rule_name o nome que foi configurado para a regra padr�o.
         * @return retorna um valor booleano informando se a opera��o foi conclu�da com sucesso.
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
         * @abstract Insere a regra padr�o no banco (Vou inserir em outra tabela, agora na tabela config.
         * @param max_size - o tamanho da regra padr�o e default_rule_name o nome que foi configurado para a regra padr�o.
         * @return retorna um valor booleano informando se a opera��o foi conclu�da com sucesso.
         */
		function insert_default_rule_2($max_size)
		{
			/* Estou inserindo na tabela que guarda as configura��es que v�o pra sess�o. */
			$query = "SELECT config_value FROM phpgw_config WHERE config_name='expressoAdmin_default_max_size'";
			$result = $this->db->query($query);
					
			$i=0;
			
			while($this->db->next_record())
                ++$i;

			if($i==0)
			{			
				/* Se n�o existem nenhum valor cadastrado ainda, � inserido esse valor na tabela de configura��o. */
				$query = "INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES ('expressoMail1_2', 'expressoAdmin_default_max_size', '$max_size')";
				if(!$this->db->query($query))
					return false; 
			}
			else
			{

				/* Se j� existe um valor padr�o, s� atualizo. */
				$query = "UPDATE phpgw_config SET config_value=$max_size WHERE config_name='expressoAdmin_default_max_size'";
				if(!$this->db->query($query))
					return false;
			}
			return true;
		}
		
		
		/**
         * @abstract Retorna a regra padr�o.
         * @return retorna um array com a regra padr�o e caso a busca n�o for realizada com sucesso retorna false.
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
         * @abstract Retorna usu�rios de uma determinada regra.
         * @param name_rule - O nome da regra da qual deseja os participantes.
         * @return retorna um array com os participantes dessa regra e caso a busca n�o for realizada com sucesso retorna false.
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
         * @abstract Retorna as regras de acordo com um par�metro de busca. Se $user for true, busca as regras de acordo com usu�rios.
         * @param input - par�metro de busca passado pelo usu�rio e user controla se a busca ser� feita por nome de usu�rio ou por nome de regra.
         * @return retorna um array com os participantes dessa regra e caso a busca n�o for realizada com sucesso retorna false.
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
         * @return retorna um array com todas as regras e caso a busca n�o for realizada com sucesso retorna false.
         */
		function get_all_rules()
		{
			$rules = array();
			$query = "SELECT DISTINCT email_recipient, email_max_recipient FROM phpgw_expressoadmin_configuration WHERE configuration_type='MessageMaxSize' ORDER BY email_recipient";  //Seleciona todas as regras j� cadastradas.
			
            if(!$this->db->query($query))
                return false;

            $return = array();

            while($this->db->next_record())
                array_push($return, $this->db->row());
            
			return $return;
		}
   	 
		
		/**
         * @abstract Retorna a regra de acordo com o par�metro - Adapta��o do m�todo que retornava true caso a regra existisse e false caso contr�rio.
         * @param rule_name - Nome da regra.
         * @return retorna um array com os participantes dessa regra e caso a busca n�o for realizada com sucesso retorna false.
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
