<?php
$_SESSION['rootPath'] =  ROOTPATH.'/..';
//if(isset($_SESSION['rootPath']) ) $_SESSION['rootPath'] = ROOTPATH;
require_once ROOTPATH.'/../library/Net/Sieve.php';


class Sieve extends Net_Sieve implements Service
{
    var $config;
	
    public function open( $config )
    {	
		$this->config = $config;
			
		if( PEAR::isError( $error = $this->connect( $config['host'] , $config['port'] , $config['options'] , $config['useTLS'] ) ) ) {
			return $error->toString();
		}
		
		if( PEAR::isError( $error = $this->login( $config['user'] , $config['password'] , $config['loginType'] ) ) ) {
			return $error->toString();
		}
		
		if( PEAR::isError( $error = $this->getError() ) ) {
			return $error->toString();
		}	
    }

    public function find( $URI, $justthese = false, $criteria = false )
    {
	$return = $this->listScripts();

	if( !is_array($return) )
	    throw new Exception( $return->toString() );

	$array_return = array();

	foreach( $return as $i => $id )
		$array_return[] = $this->read( array( 'id' => $id ) );
	return $array_return;
    }

    

    public function read( $URI, $justthese = false )
    {
		return array( 'name' => $URI['id'], 
		      'content' => $this->getScript( $URI['id'] ),
		      'active' => ($this->getActive() === $URI['id']) );
    }
	
    public function create( $URI, $data )
    {	
		if( $this->installScript( $data['name'], $data['content'], $data['active'] ) )
			return array('id' => $data['name']);

		return false;
    }

    public function delete( $URI, $justthese = false, $criteria = false )
    {
	return $this->removeScript( $URI['id'] );
    }

    public function update( $URI, $data, $criteria = false )
    {
	$this->delete( $URI );
	return $this->create($URI , $data);
    }


    public function close()
    {
	$this->disconnect();
    }

    public function replace( $URI, $data, $criteria = false )
    {}

    public function deleteAll( $URI, $justthese = false, $criteria = false )
    {}

    public function setup()
    {}

    public function teardown()
    {}

    public function begin( $uri )
    {}

    public function commit( $uri )
    {
	return( true );
    }

    public function rollback( $uri )
    {}
}
