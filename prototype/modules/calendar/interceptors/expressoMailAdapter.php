<?php

class expressoMailAdapter {
    
    public static function importFromMail( $ical )
    {
        $args =  Controller::parse( array( 'service' => 'iCal' ) , $data , $params);
       
        include ROOTPATH.'/Sync.php';
    }
    
    
    public static function getCalendars( $user )
    {
        

        //$args =  Controller::parse( array( 'service' => 'iCal' ) , $data , $params);
        //include ROOTPATH.'/Sync.php';
    }
}

?>
