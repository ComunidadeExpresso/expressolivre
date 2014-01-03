<?php

use prototype\api\Config as Config;

class OpenLDAP implements Service
{
    var $con;
    var $config; 
    var $limit = 10;

    public function find  ( $uri, $justthese = false, $criteria = false )
    {
	$map = Config::get($uri['concept'], 'OpenLDAP.mapping'); 
	
	if( !isset($criteria["limit"]) )
		$criteria["limit"] = $this->limit;
      
	$sr =  @ldap_search( $this->con , $this->config['context'] , self::parseCriteria($criteria , $map) , self::parseJustthese($justthese, $map) , 0 , $criteria["limit"]); 
	if(!$sr) return false; 

	if( isset($criteria["order"]) )
	    ldap_sort( $this->con, $sr, $criteria["order"] ); 
	
	return  self::_formatEntries( ldap_get_entries( $this->con, $sr ) , $map);
    }

    public function read ( $uri, $justthese = false )
    {
        $map = Config::get($uri['concept'], 'OpenLDAP.mapping'); 
        
	if( $justthese === false || $justthese === null )
		$sr =  ldap_search( $this->con, $this->config['context'], '('.$map['id'].'='.$uri['id'].')' );
	else
		$sr =  ldap_search( $this->con, $this->config['context'], '('.$map['id'].'='.$uri['id'].')', self::parseJustthese($justthese, $map) );

	if(!$sr) return false; 

	$return =  self::_formatEntries( ldap_get_entries( $this->con, $sr ) , $map );
        
	return isset($return[0]) ? $return[0] : array();

    }

    public function deleteAll( $uri, $justthese = false, $criteria = false ){} 

    public function delete   ( $uri, $justthese = false )
    {
//		return ldap_delete ($this->con , $this->config['context'].','.$uri['id'] );
    }

    public function replace  ( $uri, $data, $criteria = false ){}

    public function update   ( $uri, $data ){}

    public function create   ( $uri, $data ){}

    public function open ( $config )
    {
	$this->config = $config;
		
	$this->con = ldap_connect( $config['host'] );

	ldap_set_option( $this->con,LDAP_OPT_PROTOCOL_VERSION,3 );

	if( isset( $config['user'] ) && isset( $config['password'] ) )
	    ldap_bind( $this->con, $config['user'], $config['password'] );

	return( $this->con );
    }

    public function close()
    {
	ldap_close($this->con);
    }

    public function setup(){}

    public function teardown(){}

    public function begin( $uri ){

    }

    public function commit( $uri ){
	return( true );
    }

    public function rollback( $uri ){
    }

    private static function _formatEntries ( $pEntries , &$map ) 
    {           
	if( !$pEntries ) return( false );  
            
        $newMap = array();
        foreach ($map as $i => &$v)
            $newMap[strtolower($v)] = $i;
         
	$return = array();
	for ($i=0; $i < $pEntries["count"]; ++$i)
	{
	      $entrieTmp = array();
	      foreach ($pEntries[$i] as $index => $value)
	      {
                  if(isset($newMap[$index]))
		  {
		      if(is_array($value))
		      {
			  if(count($value) == 2)
			      $entrieTmp[$newMap[$index]] = $value['0'];
			  else
			  {
			      foreach ($value as $index2 =>$value2)
			      {
				  if($index2 != 'count')
				      $entrieTmp[$newMap[$index]][$index2] = $value2;
			      }
			  }
		      }
		      else
			  $entrieTmp[$newMap[$index]] = $value;
		  }
	      }

	      $return[] = $entrieTmp;
	}

	return( $return );
    }

    private static function parseCriteria( $criteria  , &$map)
    {  
	$result = "";
	
	if( isset($criteria["filter"]) )
	{
		/*
		  * ex: array   ( 
		  *		  [0] 'OR',
		  *		  [1] array( 'OR', array( array( '=', 'campo', 'valor' ) ), 
		  *		  [2] array( '=', 'campo' , 'valor' ),
		  *		  [3] array( 'IN', 'campo', array( '1' , '2' , '3' ) )
		  *		)
		  * OR
		  *	    array( '=' , 'campo' , 'valor' )
		*/


		$result .= self::parseFilter( $criteria['filter'] , $map);
	}
                
	return $result;
    }

    private static function parseFilter( $filter , &$map)
    {
	$result = '';
        $as = array_shift( $filter );
	$op = self::parseOperator( $as );

	if( is_array($filter[0]) )
	{
	    $nested = '';

	    foreach( $filter as $i => $f )
		$nested .= self::parseFilter($f , $map);

	    $fil =  $op.$nested;
	}
	else  if( isset($map[$filter[0]]) )
        {   
            if($as === '*') $filter[1] = str_replace (' ', '* *', $filter[1]);
              
            $fil = $op[0].$map[$filter[0]].$op[1].$filter[1].$op[2];
        }
        else
            return '';

	return '('.$fil.')';
    }
    
    private static function parseOperator( $op )
    {
	switch( $op )
	{
	    case 'AND': return '&';
	    case 'OR': return '|';
	    case '^': return array('', '=*', ''  );
	    case '$': return array('', '=' , '*' );
	    case '*': return array('', '=*', '*' );
            case '!': return array('!(', '=', ')', );
	    default : return array('', $op , '' );
	}
    }
    
    private static function parseJustthese($justthese , &$map)
    {
        if(!is_array($justthese)) //Caso seja um full select pegar todas as keys
            $justthese = array_keys($map);

        $return = array();

        foreach ($justthese as &$value) 
            if(isset($map[$value]))
                $return[] = $map[$value];
            
        return $return;  
    }
}

?>
