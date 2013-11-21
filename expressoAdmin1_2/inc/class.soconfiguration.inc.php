<?php
define('PHPGW_INCLUDE_ROOT','../');	
define('PHPGW_API_INC','../phpgwapi/inc');
include_once(PHPGW_API_INC.'/class.db.inc.php');

    class soconfiguration
    {

        var $db;

        function soconfiguration()
        {
            /// Conecta ao banco de dados
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
            /// Fim Conecta Banco de dados
        }

        /**
         *  Insere Regra de bloqueio no banco de dados
         * @param array $pFileds array index do array = ('Campo da tabela'), vaçpr do array = ('Valor do campo')
         * @return bool true or false
         */
        function insertRuleInDb($pFileds)
        {

            $fields = '';
            $fieldsValues = '';
            
            foreach($pFileds as $key=>$value)
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
         * Altera Regras  no banco de dados
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

            $query = 'UPDATE phpgw_expressoadmin_configuration SET '.$fieldsSet.' WHERE id = \''.$pId.'\'';

            if($this->db->query($query))
                return  true;
            else
                return false;


        }

        /**
         * Remove regra do banco de dados
         * @param int $pId
         * @return bool true or false
         */
        function removeRuleInDb($pId)
        {

            $query = 'DELETE FROM phpgw_expressoadmin_configuration WHERE id = \''.$pId.'\'';

            if($this->db->query($query))
                return  true;
            else
                return false;
            
        }

        /**
         * Busca regras no banco de dados
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

            $query = 'SELECT '.$fields.' FROM phpgw_expressoadmin_configuration '.$pFilter;

            if(!$this->db->query($query))
                return false;

            $return = array();

            while($this->db->next_record())
                array_push($return, $this->db->row());

            return $return;
        }

         /**
         *  Insere Regra de bloqueio Global no banco de dados
         * @param array $pFileds array index do array = ('Campo da tabela'), valpr do array = ('Valor do campo')
         * @return bool true or false
         */
        function insertRuleGlobalInDb($pFileds)
        {

            $fields = '';
            $fieldsValues = '';

            foreach($pFileds as $key=>$value)
            {
                if($value)
                {
                    $fields .= $key.', ';
                    $fieldsValues .= '\''.$value.'\', ';
                }
            }

            $fields = substr($fields,0,-2);
            $fieldsValues = substr($fieldsValues,0,-2);

            $query = 'INSERT INTO phpgw_config('.$fields.') VALUES ('.$fieldsValues.')';

             if($this->db->query($query))
                return  true;
             else
                return false;


        }

         /**
         *  Altera Regras Globais no banco de dados
         * @param <int> $pOid oid da regra a qual quer alterar
         * @param array $pFields array index do array = ('Campo da tabela'), valpr do array = ('Valor do campo')
         * @return bool True or False
         */
        function updatetRuleGlobalInDb($pOid, $pFields)
        {

            $fieldsSet = '';

            foreach($pFields as $key=>$value)
                    $fieldsSet .= $key.' = \''.$value.'\', ';



            $fieldsSet = substr($fieldsSet,0,-2);

            $query = 'UPDATE phpgw_config SET '.$fieldsSet.' WHERE oid = \''.$pOid.'\'';

            if($this->db->query($query))
                return  true;
            else
                return false;


        }

        /**
         * Busca regras Globais no banco de dados
         * @param string $pFilter Filtro em linguagem sql
         * @param array $pFields array com os campos que você queira retornar
         * @return array Retorna A busca em um array
         */
        function getRuleGlobalInDb($pFilter = '',$pFields = '')
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

            $query = 'SELECT '.$fields.' FROM phpgw_config '.$pFilter;

            if(!$this->db->query($query))
                return false;

            $return = array();

            while($this->db->next_record())
                array_push($return, $this->db->row());

            return $return;
        }
        
    }

?>
