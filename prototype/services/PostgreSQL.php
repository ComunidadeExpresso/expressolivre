<?php
/**
 *
 * Copyright (C) 2012 Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY FUNAMBOL, FUNAMBOL DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT  OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program; if not, see www.gnu.org/licenses or write to
 * the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301 USA.
 *
 * This code is based on the OpenXchange Connector and on the Prognus pSync
 * Connector both developed by the community and licensed under the GPL
 * version 2 or above as published by the Free Software Foundation.
 *
 * You can contact Prognus Software Livre headquarters at Av. Tancredo Neves,
 * 6731, PTI, Edifício do Saber, 3º floor, room 306, Foz do Iguaçu - PR - Brasil or at
 * e-mail address prognus@prognus.com.br.
 *
 * Classe de abstração que implementa métodos de manipulação de banco de dados
 * executando instruções SQL a partir de parâmetros passados pelos métodos.
 *
 * @package    Prototype
 * @license    http://www.gnu.org/copyleft/gpl.html GPL
 * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
 * @version    2.4
 * @sponsor    Caixa Econômica Federal
 * @since      Arquivo disponibilizado na versão 2.4
 */

use prototype\api\Config as Config;

class PostgreSQL implements Service
{
    private $con; //Conexão com o banco de dados
    private $config; //Configuração
    public  $error = false; //Armazena um erro caso ocorra
    private $maps; //Cache de maps
    private $tables;

    public function find ( $uri, $justthese = false, $criteria = false )
    {
        $condition = '';

        if(!isset($this->maps[$uri['concept']]) || !isset($this->tables[$uri['concept']]))
        {
            $this->maps[$uri['concept']] = Config::get($uri['concept'], 'PostgreSQL.mapping');
            $this->tables[$uri['concept']] =  Config::get($uri['concept'],'PostgreSQL.concept');
        }

        $tables = $this->tables[$uri['concept']];

        $justthese = self::parseJustthese($justthese, $this->maps[$uri['concept']] , $this->tables[$uri['concept']]);

        if(isset($criteria['condition']))
        {
            $pc = $this->parseCondition($criteria['condition']);

            if(is_array($pc))
            {
                if(!in_array($this->tables[$uri['concept']], $pc['tables']))
                    $pc['tables'][] = $this->tables[$uri['concept']];

                $tables = implode(',', $pc['tables'] );
                $condition .= ' WHERE ' . $pc['conditions'];
            }

        }

        $criteria = ($criteria !== false) ? $this->parseCriteria ( $criteria , $this->maps[$uri['concept']] , $condition , $this->tables[$uri['concept']]) : $condition;

        return $this->execSql( 'SELECT '.$justthese['select'].' FROM '. $tables .' '.$criteria );
    }

   public function read ( $uri, $justthese = false , $criteria = false)
   {
       if(!isset($this->maps[$uri['concept']]) || !isset($this->tables[$uri['concept']]))
       {
           $this->maps[$uri['concept']] = Config::get($uri['concept'], 'PostgreSQL.mapping');
           $this->tables[$uri['concept']] =  Config::get($uri['concept'],'PostgreSQL.concept');
       }

       $condition = ' WHERE '.$this->tables[$uri['concept']].'.'.$this->maps[$uri['concept']]['id'].' = \''.addslashes( $uri['id'] ).'\'';
       $justthese = self::parseJustthese($justthese, $this->maps[$uri['concept']] , $this->tables[$uri['concept']]);
       $tables = $this->tables[$uri['concept']];

       if(isset($criteria['condition']))
       {
           $pc = $this->parseCondition($criteria['condition']);

           if(is_array($pc))
           {
               if(!in_array($this->tables[$uri['concept']], $pc['tables']))
                   $pc['tables'][] = $this->tables[$uri['concept']];

               $tables = implode(',', $pc['tables'] );
               $condition .= ' AND ' .  $pc['conditions'];
           }
       }

       $criteria = ($criteria !== false) ? $this->parseCriteria ( $criteria , $this->maps[$uri['concept']] , $condition , $this->tables[$uri['concept']]) : $condition;

       return $this->execSql( 'SELECT '.$justthese['select'].' FROM '. $tables . ' ' . $criteria , true );
    }
    
    public function deleteAll ( $uri,   $justthese = false, $criteria = false ){
            $map = Config::get($uri['concept'], 'PostgreSQL.mapping');
        if(!self::parseCriteria ( $criteria , $map)) return false; //Validador para não apagar tabela inteira
        return $this->execSql( 'DELETE FROM '.(Config::get($uri['concept'],'PostgreSQL.concept')).' '.self::parseCriteria ( $criteria ,$map) );
    }

    public function delete ( $uri, $justthese = false, $criteria = false ){
            if(!isset($uri['id']) && !is_int($uri['id'])) return false; //Delete chamado apenas passando id inteiros
        $map = Config::get($uri['concept'], 'PostgreSQL.mapping');
        $criteria = ($criteria !== false) ? $this->parseCriteria ( $criteria , $map , ' WHERE '.$map['id'].' = \''.pg_escape_string( $uri['id'] ).'\'') : ' WHERE '.$map['id'].' = \''.pg_escape_string( $uri['id'] ).'\'';
        return $this->execSql('DELETE FROM '.(Config::get($uri['concept'],'PostgreSQL.concept')).$criteria);
    }

    public function replace ( $uri,  $data, $criteria = false ){
            $map = Config::get($uri['concept'], 'PostgreSQL.mapping');
        return $this->execSql('UPDATE '.(Config::get($uri['concept'],'PostgreSQL.concept')).' '. self::parseUpdateData( $data ,$map).' '.self::parseCriteria($criteria , $map));
    }

    public function update ( $uri,  $data, $criteria = false ){
            $map = Config::get($uri['concept'], 'PostgreSQL.mapping');
        $criteria = ($criteria !== false) ?
            $this->parseCriteria ( $criteria , $map , ' WHERE '.$map['id'].' = \''.pg_escape_string( $uri['id'] ).'\'') : ' WHERE '.$map['id'].' = \''.pg_escape_string( $uri['id'] ).'\'';

        return $this->execSql('UPDATE '.(Config::get($uri['concept'],'PostgreSQL.concept')).' '. self::parseUpdateData( $data ,$map).$criteria);
    }

    public function create ( $uri,  $data ){    
        return $this->execSql( 'INSERT INTO '.(Config::get($uri['concept'],'PostgreSQL.concept')).' '.self::parseInsertData( $data , $uri['concept'] ), true );
    }

    public function execSql( $sql, $unique = false )
    {
      if(!$this->con) $this->open( $this->config );

      if( $this->con )
      {
        $rs = @pg_query( $this->con, $sql );

        if( !$rs )
          return false;

        switch( pg_num_rows( $rs ) )
        {
          case -1: 
            $this->error = pg_last_error ( $this->con );
            return( false );

          case 0:
            return( pg_affected_rows( $rs ) ? true : array() );

          default:
            $return = array();

            while( $row = pg_fetch_assoc( $rs ) )
            {
              $return[] = $row;
            }
            return( $unique ? $return[0] : $return );
        }
      }
    }


    //@DEPRECATED
    public function execResultSql( $sql, $unique = false ){
        return $this->execSql( $sql, $unique );
    }

    public function begin( $uri ) {
   
    if(!$this->con)
        $this->open( $this->config );
        
        $this->error = false;
    pg_query($this->con, "BEGIN WORK"); 
    }

    public function commit($uri ) {
   
    if( $this->error !== false )
    {
        $error = $this->error;
        $this->error = false;

        throw new Exception( $error );
    }

    pg_query($this->con, "COMMIT");

    return( true );
    }

    public function rollback( $uri ){
    
    pg_query($this->con, "ROLLBACK");
    }

    public function open  ( $config ){
                
        $this->config = $config;
        
        $rs = '';
        $rs .= ( isset($this->config['host']) && $this->config['host'] )  ? ' host='.$this->config['host'] : '' ;
        $rs .= ( isset($this->config['user']) && $this->config['user'] )  ? ' user='.$this->config['user'] : '' ;
        $rs .= ( isset($this->config['password']) && $this->config['password'] )  ? ' password='.$this->config['password'] : '' ;
        $rs .= ( isset($this->config['dbname']) && $this->config['dbname'] )  ? ' dbname='.$this->config['dbname'] : '' ;
        $rs .= ( isset($this->config['port']) && $this->config['port'] )  ? ' port='.$this->config['port'] : '' ;

    if($this->con = pg_connect( $rs ))
        return $this->con;

    throw new Exception('It was not possible to enable the target connection!');
    //$this->con = pg_connect('host='.$config['host'].' user='.$config['user'].' password='.$config['password'].' dbname='.$config['dbname'].'  options=\'--client_encoding=UTF8\'');
    }

    public function close(){

        pg_close($this->con);
            
            $this->con = false;

    }

    public function setup(){}

    public function teardown(){}

    private static function parseInsertData( $data , $concept){
     
            $map = Config::get($concept, 'PostgreSQL.mapping');
        
        $ind = array();
        $val = array();
        
        foreach ($data as $i => $v){
                    if(!isset($map[$i])) continue;
                
            $ind[] = $map[$i];
            $val[] = '\''.pg_escape_string($v).'\'';
        }
        return '('.implode(',', $ind).') VALUES ('.implode(',', $val).') RETURNING '.$map['id'].' as id';       
    }
    
    private static function parseUpdateData( $data , &$map){

        $d = array();
        foreach ($data as $i => $v)
            {
                if(!isset($map[$i])) continue;
                
                $d[] = $map[$i].' = \''.pg_escape_string ($v).'\'';
            }
        
        return 'SET '.implode(',', $d);
    }

    private static function parseCriteria( $criteria  , &$map , $query = '' ){

        if( isset($criteria["filter"]) && $criteria["filter"] !== NULL )
        {
            /*
          * ex: array   ( 
          *       [0] 'OR',
          *       [1] array( 'OR', array( array( '=', 'campo', 'valor' ) ), 
          *       [2] array( '=', 'campo' , 'valor' ),
          *       [3] array( 'IN', 'campo', array( '1' , '2' , '3' ) )
          *     )
          * OR
          *     array( '=' , 'campo' , 'valor' )
        */
               if($fc = self::parseFilter( $criteria['filter'] , $map))
                    $query .= ($query === '') ?  'WHERE ('.$fc.')' : ' AND ('.$fc.')';
        }
        /*
          * ex: array( 'table1' => 'table2' ,  'table1' => 'table2')
          *     
          */
        if( isset($criteria["join"]) )
        {
        foreach ($criteria["join"] as $i => $v)
            $query .= ' AND '.$i.' = '.$v.' ';
        }
        
        if( isset($criteria["group"]) )
        {
            $query .= ' GROUP BY '.( is_array($criteria["group"]) ? implode(', ', $criteria["group"]) : $criteria["group"] ).' ';
        }
   
        if( isset($criteria["order"]) )
        {
            //Verificar se os atributos para o ORDER BY serao ordenados em ordem decrescente [DESC]
            $orderDesc = ( isset($criteria["orderDesc"]) && count($criteria["order"]) == count($criteria["orderDesc"]) ) ? $criteria["orderDesc"] : false;
        
            $query .= ' ORDER BY '.self::parseOrder( $criteria["order"], $map, $orderDesc ).' ';
        
        }

        if( isset($criteria["limit"]) )
        {
            $query .= ' LIMIT '. $criteria["limit"] .' ';
        }
        if( isset($criteria["offset"]) )
        {
            $query .= ' OFFSET '. $criteria["offset"] .' ';
        }
        
        return $query;
    }
    
    private static function parseFilter( $filter ,$map)
    {
      if( !is_array( $filter ) || count($filter) <= 0) return null;
                  
      $op = self::parseOperator( array_shift( $filter ) );
          
      if( is_array($filter[0]) )
      {
          $nested = array();

          foreach( $filter as $i => $f )
                  if( $n = self::parseFilter( $f , $map))
                      $nested[] = $n; 

                  
          return (count($nested) > 0 ) ? '('.implode( ' '.$op.' ', $nested ).')' : '';
      }

      if(!isset($map[$filter[0]])) return '';
                
      $filter[0] = $map[$filter[0]];
          
      $igSuffix = $igPrefix = '';
                  
      if( strpos( $op[0], 'i' ) === 0 )
      {
          $op[0] = substr( $op[0], 1 );
          $filter[0] = 'upper("'.$filter[0].'")';
          $igPrefix = 'upper(';
          $igSuffix = ')';
      }

      if( isset($filter[1]) && is_array($filter[1]) )
      {
        return ( $filter[0].' '.$op[0]." ($igPrefix'".implode( "'$igSuffix,$igPrefix'", array_map("pg_escape_string" , $filter[1]) )."'$igSuffix)" );
      }
      
      return ($filter[0].' '.$op[0]." $igPrefix'".$op[1].(isset($filter[1])?pg_escape_string($filter[1]):"").$op[2]."'$igSuffix" );
    }

    private static function parseOperator( $op ){
    
    switch(strtolower($op))
    {
        case 'and':
        case 'or': return( $op );
        case 'in': return array( $op );
        case '!in': return array( 'NOT IN' );
        case '^': return array( 'like', '%',  '' );
        case '$': return array( 'like',  '', '%' );
        case '*': return array( 'like', '%', '%' );
        case 'i^': return array( 'ilike', '%',  '' );
        case 'i$': return array( 'ilike',  '', '%' );
        case 'i*': return array( 'ilike', '%', '%' );
        default : return array( $op,  '',  '' );
    }
    }

    static function parseJustthese($justthese , &$map , $table = '')
    {

        if(!is_array($justthese)) //Caso seja um full select pegar todas as keys
            $justthese = array_keys($map);

        $return = array();

        if($table)
            $table .= '.';

        foreach ($justthese as &$value)
        {
            if(!isset($map[$value])) continue; //Escapa itens não existentes no mapa

            if(is_array($map[$value]))
                $return['deepness'][$value] = $map[$value];
            else
                $return['select'][] = $table . $map[$value] .' as "'. $value. '"';
        }

        $return['select'] = implode(', ', $return['select']);
        return $return;
    }

    private function parseCondition( $condition )
    {
        $tables = array();
        $conditions = '';

            $matches = array();
            if(preg_match_all('/\s*(AND|^)\s*([a-z]+)\.([a-z]+)\s+\=\s+([a-z]+)\.([a-z]+)(\s|$)+/i', $condition ,$matches,PREG_SET_ORDER))
            {
               foreach ($matches as $i => $v)
               {
                   if(!isset($this->maps[$v[2]]) || !isset($this->tables[$v[2]]))
                   {
                       $this->maps[$v[2]] = Config::get($v[2], 'PostgreSQL.mapping');
                       $this->tables[$v[2]] =  Config::get($v[2],'PostgreSQL.concept');
                   }
                   if(!isset($this->maps[$v[4]]) || !isset($this->tables[$v[4]]))
                   {
                       $this->maps[$v[4]] = Config::get($v[4], 'PostgreSQL.mapping');
                       $this->tables[$v[4]] =  Config::get($v[4],'PostgreSQL.concept');
                   }

                   if(isset($this->maps[$v[2]][$v[3]]) && isset($this->maps[$v[4]][$v[5]]))
                       $conditions .= ' '. $v[1] .' '. $this->tables[$v[2]] . '.' . $this->maps[$v[2]][$v[3]] .' = '. $this->tables[$v[4]] . '.' . $this->maps[$v[4]][$v[5]];
                   else
                       continue;

                   if(!in_array( $this->tables[$v[2]], $tables))
                       $tables[] = $this->tables[$v[2]];

                   if(!in_array( $this->tables[$v[4]], $tables))
                       $tables[] = $this->tables[$v[4]];
               }

            }

            if(preg_match_all('/\s*(AND|OR|^)\s*([a-z]+)\.([a-z]+)\s+([\=\>\<\!]+|like)+\s+([a-z0-9\/\+\=]+)(\s|$)+/i', $condition , $matches ,PREG_SET_ORDER))
            {
                foreach ($matches as $i => $v)
                {
                    if(!isset($this->maps[$v[2]]) || !isset($this->tables[$v[2]]))
                    {
                        $this->maps[$v[2]] = Config::get($v[2], 'PostgreSQL.mapping');
                        $this->tables[$v[2]] =  Config::get($v[2],'PostgreSQL.concept');
                    }

                    if(isset($this->maps[$v[2]][$v[3]]))
                        $conditions .= ' '. $v[1] .' '. $this->tables[$v[2]] . '.' . $this->maps[$v[2]][$v[3]] .' '.$v[4].' \''. pg_escape_string(base64_decode($v[5])) .'\'';
                    else
                        continue;

                    if(!in_array( $this->tables[$v[2]], $tables))
                        $tables[] = $this->tables[$v[2]];
                }
            }

        return (count($tables) > 0 && count($conditions ) > 0) ? array('tables' => $tables , 'conditions' => $conditions ) : '' ;
    }

    private static function parseOrder($order , &$map, $orderDesc=false)
    {

        if($notArray = !is_array($order)) //Caso seja um full select pegar todas as keys
            $order = array( $order );

    //Caso seja feita ordenacao em ordem descrescente
    //concatenar DESC em cada atributo
    if($orderDesc !== false){
        if(!is_array($orderDesc)){
            $orderDesc = array( $orderDesc );
        }
        $order_count = count($order);
        for($i=0; $i<$order_count; ++$i){
            $order[$i] .= ($orderDesc[$i] === true) ? ' DESC' : '';
        }
    }

        $return = array();

        foreach ($order as &$value) 
        {
            if(!isset($map[$value])) continue; //Escapa itens não existentes no mapa

            $value = $map[$value];
        }

        return ( $notArray ?  $order[0] : implode(', ', $order) );
    }

}

?>
