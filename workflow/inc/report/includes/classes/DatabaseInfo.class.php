<?php

class DatabaseInfo {
	private $_conn;
    private $_dbhost;
    private $_dbuser;
    private $_dbpassword;
    private $_dbname;
    var $_sqlerror;
    var $_parser;
    
    public function __construct($dbname,$dbhost,$dbuser,$dbpassword){
        $this->_dbhost     = $dbhost;
        $this->_dbuser     = $dbuser;
        $this->_dbpassword = $dbpassword;
        $this->_dbname     = $dbname;
        $this->_parser     = new SqlParser("");
        $this->_conn = $this->conectar();
    }
    
    public function conectar() {
        $connstring = "dbname=" . $this->_dbname . " host=" . $this->_dbhost . " user=" . $this->_dbuser . " password=" . $this->_dbpassword . "";
        //echo $connstring;
        $conn = pg_connect($connstring);
        $this->_conn = $conn;
        
        $this->_parser->bloquearPalavra("controle_acesso_banco",true);
        $this->_parser->bloquearPalavra("controle_acesso_banco_usuarios",true);
        $this->_parser->bloquearPalavra("controle_acesso_banco_historico",true);
        
        //$this->bloquearFuncoes(true);
        
    	return $conn;
    }
    
    public function bloquearFuncoes($bloqueio) {
    	$res_funcoes = $this->getFuncoes();
        while ($funcao = pg_fetch_object($res_funcoes)) {
            $nome_funcao = $funcao->proname;
            $this->_parser->bloquearPalavra($nome_funcao . "(",$bloqueio);
        }
    }
    
    public function getErro() {
    	return $this->_sqlerror;
    }
    
    public function executar($sql,$transaction = true) {
    		$error = "";
            $this->_sqlerror = ""; 

            $this->_parser->setSql($sql);
            
            $ret_parser = $this->_parser->verificaSql();
            
            if ($ret_parser === false) {
                $error = $this->_parser->getErro();
            }

            $sql = $this->_parser->getSql(); 
            if ($error == "") {
            	if ($transaction) {
                	pg_query($this->_conn,"begin;");
            	}
                $res = pg_query($this->_conn,$sql);
                $error = pg_last_error($this->_conn);
                if ($transaction) {
                	pg_query($this->_conn,"rollback;");
                }
                
            }
            if ($error == "") {
                return $res;
            } else {
                $this->_sqlerror = $error;
               	return false;
            }
    }
    
    public function fetch_array($recordset){
        return pg_fetch_array($recordset);
    }
    
    public function fetch_all($recordset) {
    	$process = array();
        while ($rsatt = $this->fetch_array($recordset)){
            $process[] = $rsatt;
        }
        return $process;
    }
    
    public function getDbIndices($schemaname,$tblname) {
        $sqlstr = "select pg_get_indexdef(i.oid) as indice, x.indisprimary FROM pg_index x JOIN pg_class c ON c.oid = x.indrelid JOIN pg_class i ON i.oid = x.indexrelid WHERE c.relname=(select relname from pg_stat_user_tables where relname = '$tblname' and schemaname = '$schemaname') ";
        $resu = $this->executar($sqlstr);
        return $resu;
    }
    
	public function getDbConstraints($schemaname,$tblname) {
        $sqlstr = "select pg_get_constraintdef(ct.oid) AS constraint FROM pg_class c inner join pg_catalog.pg_constraint ct ON (c.oid = ct.conrelid)  WHERE c.relname=(select relname from pg_stat_user_tables where relname = '$tblname' and schemaname = '$schemaname')";
        $resu = $this->executar($sqlstr);
        return $resu;
    }
    
    
    
    
    public function getAttTable($schemaname,$tblname){

        $sqlstr = "SELECT 
                        pg_attribute.attnum AS index,
                        attname AS field,
                        typname AS type,
                        atttypmod-4 as length,
                        NOT attnotnull AS null,
                        adsrc AS def,
                        (select coalesce(d.description,'') as desc from pg_description d where d.objoid=attrelid and d.objsubid=attnum) as descricao_coluna
                    FROM 
                        pg_attribute,
                        pg_class,
                        pg_type,
                        pg_attrdef
                    WHERE 
                        pg_class.oid=attrelid
                    AND pg_type.oid=atttypid
                    AND attnum>0
                    AND pg_class.oid=adrelid
                    AND adnum=attnum
                    AND atthasdef='t'
                    AND lower(relname)= (select relname from pg_stat_user_tables where relname = '$tblname' and schemaname = '$schemaname') 
                UNION
                    SELECT 
                        pg_attribute.attnum AS index,
                        attname AS field,
                        typname AS type,
                        atttypmod-4 as length,
                        NOT attnotnull AS null,
                        '' AS def,
                        (select coalesce(d.description,'') as desc from pg_description d where d.objoid=attrelid and d.objsubid=attnum) as descricao_coluna
                    FROM 
                        pg_attribute,
                        pg_class,
                        pg_type
                    WHERE 
                        pg_class.oid=attrelid
                    AND pg_type.oid=atttypid
                    AND attnum>0
                    AND atthasdef='f'
                    AND lower(relname)=(select relname from pg_stat_user_tables where relname = '$tblname' and schemaname = '$schemaname')  
                order by 
                    index;";
        
        //echo $sqlstr;
        $resu = $this->executar($sqlstr);
        //print_r($resu);
        return $resu;
    }
    
    public function getDbViews($schemaname = "",$viewname = ""){
    	$add = "";
        if ($schemaname != "") {
        	$add = " and schemaname ilike '%$schemaname%'";
        }
        if ($schemaname != "") {
            $add .= " and viewname ilike '%$viewname%'";
        }
        $sqlstr = "select schemaname, viewname, definition from pg_views where schemaname not in ('information_schema','pg_catalog') $add ";
        $resu = $this->executar($sqlstr);
        //print_r($resu);
        return $resu;
    }
    
    public function getQueries() {
    	
    	$sql = "select datname, procpid, usename, query_start, 
                    (   CASE WHEN client_addr is null then 
                            'LOCAL' 
                        ELSE 
                            client_addr::text 
                        END) AS client_addr, 
                    current_query,
                    
(to_char(((timeofday()::TIMESTAMP)-query_start),'hh24:mi:ss')||' - 
Inicio: '||to_char(query_start,'hh24:mi:ss') ) AS duracao,
                    (   CASE WHEN timeofday()::TIMESTAMP-query_start > 
INTERVAL '30 seconds' THEN 
                            'LENTA' 
                        ELSE 
                            'NORMAL' 
                        END) AS lentas,
                    (   CASE WHEN waiting = 't' THEN 
                            'SIM' 
                        ELSE 
                            'NAO' 
                        END) AS waiting
                    from pg_stat_activity 
                    where current_query not ilike '<IDLE>' 
                    order by query_start asc";
    	
    	//$sql = "SELECT pg_stat_activity.datid, pg_stat_activity.datname, pg_stat_activity.procpid, pg_stat_activity.usesysid, pg_stat_activity.usename, pg_stat_activity.current_query, pg_stat_activity.query_start, pg_stat_activity.backend_start, pg_stat_activity.client_addr, pg_stat_activity.client_port FROM pg_stat_activity WHERE (pg_stat_activity.current_query <> '<IDLE>'::text) ORDER BY pg_stat_activity.query_start DESC;";
        $resu = $this->executar($sql);
        return $resu;
    }
    
    public function getDbTables() {
        
        $sqlstr = "SELECT c.relname as tablename,

                    pg_catalog.pg_get_userbyid(c.relowner) AS dono,
                    
                    pg_catalog.obj_description(c.oid, 'pg_class') AS comentario, reltuples::integer as registros,
                    
                    (SELECT spcname FROM pg_catalog.pg_tablespace pt WHERE pt.oid=c.reltablespace) AS tablespace,
                    'public' AS schemaname
                    
                    FROM pg_catalog.pg_class c 
                    
                    LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
                    
                    WHERE c.relkind = 'r'
                    
                    AND nspname='public'
                    
                    ORDER BY c.relname";
        
        $resu = $this->executar($sqlstr);
        return $resu;
    }
    
    public function getFuncoes($funnome = '') {
        
        if ($funnome != "") { $add .= " AND proname || '(' || pg_catalog.oidvectortypes(pg_proc.proargtypes) || ')' ilike '$funnome%' "; }
        
        
    	$sql = "select pg_proc.oid as oid, proname, nspname, pg_catalog.pg_get_userbyid(proowner) AS proowner, pg_catalog.obj_description(pg_proc.oid, 'pg_proc') AS procomment, pg_catalog.oidvectortypes(pg_proc.proargtypes) AS proarguments from pg_namespace,pg_proc where pg_proc.pronamespace=pg_namespace.oid and nspname in ('public','representacoes') $add order by nspname, proname; ";
        
        $resu = $this->executar($sql);
        return $resu;
    }
    
    public function getDbFuncao($funcao,$funnome = '') {
        
        if ($funcao != "") { $add = " AND pc.oid = $funcao "; }
        if ($funnome != "") { $add .= " AND proname || '(' || pg_catalog.oidvectortypes(pc.proargtypes) || ')' ilike '$funnome' "; }
        
    	$sqlstr = "SELECT
						pc.oid AS prooid,
						proname,
						lanname as prolanguage,
						pg_catalog.format_type(prorettype, NULL) as proresult,
						prosrc,
						probin,
						proretset,
						proisstrict,
						provolatile,
						prosecdef,
						pg_catalog.oidvectortypes(pc.proargtypes) AS proarguments,
						proargnames AS proargnames,
						pg_catalog.pg_get_userbyid(proowner) AS proowner,
						pg_catalog.obj_description(pc.oid, 'pg_proc') AS procomment,
                        proname || '(' || pg_catalog.oidvectortypes(pc.proargtypes) || ')' as nomefuncao
					FROM pg_catalog.pg_proc pc, pg_catalog.pg_language pl
					WHERE
					   pc.prolang = pl.oid
                       $add 
                    ";
        $resu = $this->executar($sqlstr);
        return $resu;
    }
    
    public function getDbFuncaoSrc($funoid = "",$funnome = "") {
        $this->bloquearFuncoes(false);
        $res_funcoes = $this->getDbFuncao($funoid,$funnome);
        
        $textcontent = "";
        
        if (pg_num_rows($res_funcoes)) {
        $dados = $this->fetch_array($res_funcoes);
        
        $textcontent = "
-- Function: " . $dados["proname"]. "(" . $dados["proarguments"]. ")
    
-- DROP FUNCTION " . $dados["proname"]. "(" . $dados["proarguments"]. ");
        
CREATE OR REPLACE FUNCTION " . $dados["proname"]. "(" . $dados["proarguments"]. ") RETURNS " . $dados["proresult"]. " AS
\$BODY\$";
    $textcontent .= $dados["prosrc"];
    $textcontent .= "\$BODY\$
  LANGUAGE 'pltcl' VOLATILE
  COST 100;
ALTER FUNCTION " . $dados["proname"]. "(" . $dados["proarguments"]. ") OWNER TO " . $dados["proowner"].";";
        
        if ($dados["procomment"] != "") {
            $textcontent .= "
COMMENT ON FUNCTION " . $dados["proname"]. "(" . $dados["proarguments"]. ") IS '" .  $dados["procomment"] . "';";
        }
        }
        
        return $textcontent;
        
    }
    
    public function getPrefixo($prefixo) {
    	 $sqlstr = "select 
                        relname AS tabela, 
                        attname AS valor 
                    from 
                        pg_attribute, 
                        pg_class 
                    where 
                        not attname in ('cmax','xmax','xmin','cmin','ctid','tableoid') 
                    and attrelid=relfilenode 
                    and relhaspkey 
                    and (attname ilike '%$prefixo%' or relname ilike '%$prefixo%') order by relname, attname";
         $resu = $this->executar($sqlstr);
         return $resu;
    }
    
    function getDBTriggers() {
    	$sql = "select tgname, tgfoid, tgrelid from pg_trigger where substring(trim(tgname),0,4) != 'RI_' and substring(trim(tgname),0,4) != 'pg_'  ";
        $resu = $this->executar($sql);
        return $resu;
    }
    
}

?>