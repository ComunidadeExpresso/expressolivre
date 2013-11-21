<?php

class Helpers {    

    public static function futureEvent( $startTime , $rangeEnd, $idSchedulable )
    {	
        //Verifica data  
	$range = new DateTime( '@'.(int)($rangeEnd / 1000) , new DateTimeZone('UTC') );
        list( $y1  , $m1 , $d1) = explode( '-' , $range->format('y-m-d')); 
                
        $rangeEndMicrotime = gmmktime(0, 0, 0, $m1 , $d1, $y1); 
        $nowMicrotime =   gmmktime(0, 0, 0); 
        
        if($rangeEndMicrotime < $nowMicrotime ) 
            return self::futureEventDecodedRepeat($startTime , $idSchedulable, $nowMicrotime);
        
        if($rangeEndMicrotime === $nowMicrotime ) //caso seja o mesmo dia verifica a hora do evento.
        {
            $sTime = new DateTime( '@'.(int)($startTime / 1000) , new DateTimeZone('UTC') );            
            $eventHour = (date_format( $sTime , 'H') * 3600) + (date_format( $sTime , 'i') * 60) + date_format( $sTime , 's');
            $nowHour = (gmdate('H') * 3600) + (gmdate('i') * 60) + gmdate('s');
            
            if( $eventHour  <  $nowHour )
                    return self::futureEventDecodedRepeat($startTime , $idSchedulable, $nowMicrotime);
        }
       return true; 
    }
    
    public static function futureEventDecodedRepeat( $startTime , $idSchedulable, $nowMicrotime )
    {	

	$sql = 'SELECT calendar_repeat_occurrence.occurrence as "occurrence" '
	.'FROM calendar_repeat, calendar_repeat_occurrence WHERE calendar_repeat_occurrence.occurrence >= \'' . $startTime . '\' '
	.'AND calendar_repeat.object_id = \'' . $idSchedulable . '\' '
	.'AND calendar_repeat.id = calendar_repeat_occurrence.repeat_id AND '
	.'calendar_repeat_occurrence.exception != 1';
	
	$ocurrences = Controller::service('PostgreSQL')->execResultSql($sql);
	
	if($ocurrences){
	    $valid = FALSE;
	    foreach($ocurrences as $value)
		if(($value['occurrence'] / 1000) > $nowMicrotime){
		    $valid = true;
		    break;
		}	
		return $valid;
	}  else
	    return false;
    }
    
    
    
    
    /**
    * Resgata o organizador do evento
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
    * @sponsor    Caixa Econômica Federal
    * @author     Cristiano Corrêa Schmidt
    * @access     public
    */
    protected static function getOrganizer( &$schedulable ) {
         $f = Controller::find(array('concept' => 'participant') , false , array('deepness' => '1' , 'filter' => array('AND' , array('=', 'schedulable' , $schedulable ) , array('=', 'isOrganizer' , '1'))));
         return ( isset( $f[0] ) ) ? $f[0] : false;
    }
    
    public static function lg($print, $name = ''){
	ob_start();
	print "\n";
	print $name . ": ";
        print_r( $print );
        $output = ob_get_clean();
        file_put_contents( "/tmp/prototype.log", $output , FILE_APPEND );
                
    }

}

?>
