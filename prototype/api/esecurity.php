<?php

require_once(__DIR__.'/config.php');

use prototype\api\Config as Config;

class ESecurity 
{
   public function valid()
   {
        if(isset($_SESSION['wallet']['security']['REMOTE_ADDR']))
        {
            if($_SESSION['wallet']['security']['REMOTE_ADDR'] !== $_SERVER['REMOTE_ADDR'])
            {
                $sql = 'SELECT config_value FROM phpgw_config WHERE config_app = \'phpgwapi\' AND config_name = \'webserver_url\'';
                $config = Config::service('PostgreSQL' , 'config');

                $rs = '';
                $rs .= ( isset($config['host']) && $config['host'] )  ? ' host='.$config['host'] : '' ;
                $rs .= ( isset($config['user']) && $config['user'] )  ? ' user='.$config['user'] : '' ;
                $rs .= ( isset($config['password']) && $config['password'] )  ? ' password='.$config['password'] : '' ;
                $rs .= ( isset($config['dbname']) && $config['dbname'] )  ? ' dbname='.$config['dbname'] : '' ;
                $rs .= ( isset($config['port']) && $config['port'] )  ? ' port='.$config['port'] : '' ;

                $con = pg_connect( $rs );
                $rs = pg_query( $con, $sql );
                $row = pg_fetch_assoc( $rs );

                session_destroy();
                header( 'Location: '.$row['config_value'].'/login.php' );
                die();
            }
        }
        else
            $_SESSION['wallet']['security']['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
   }
    
}
?>
