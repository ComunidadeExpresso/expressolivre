<?php
require_once ROOTPATH.'/modules/calendar/constants.php';
use prototype\api\Config as Config;

class DAViCalAdapter { 
    
    static $deleted;
    
    static function initSessionVars($vars)
    {        
        session_start();
        $_SESSION['wallet'] = $vars;
        $_SESSION['config']['expressoCalendar']['useCaldav'] = FALSE;
        $_SESSION['flags']['currentapp'] = 'expressoCalendar';
        Config::regSet( 'noAlarm' , true );
    }

    /**
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
    * @sponsor    Caixa Econômica Federal
    * @author     Cristiano Corrêa Schmidt
    * @return     void
    * @access     public
    */  
    public function createCalendarToSchedulable(&$uri , &$result , &$data , $original)
    {                  
        foreach ($data as $i => $concept) 
        {
            if($concept['concept'] === 'calendarToSchedulable')
            {
                if(Config::module('useCaldav' , 'expressoCalendar'))
                {      
                    ob_start();
                    $calendarToschedulable = Controller::read( array( 'concept' => 'calendarToSchedulable' , 'id' => $concept['id'] ));
                    if($calendarToschedulable)
                    {
                        $schedulable = Controller::read( array( 'concept' => 'schedulable' , 'id' => $calendarToschedulable['schedulable'] ) , null , array('deepness' => '2') );
                        $calendar =  Controller::read( array( 'concept' => 'calendar' , 'id' => $calendarToschedulable['calendar'] ),array('timezone','name','location'));
                        $ical = Controller::format( array( 'service' => 'iCal' ) , array($schedulable) , array('defaultTZI' => $calendar['timezone']));          
                        DAViCalAdapter::putIcal($ical , array('uid' => $schedulable['uid'] , 'location' => $calendar['location'] ));
                    }
                    ob_end_clean();
                }
            }
            
        }
    } 
    
    public function createCollectionACL(&$uri ,&$params , &$criteria , $original)
    {      
        if( Config::module('useCaldav' , 'expressoCalendar') )
    {
        $calendar = Controller::read( array( 'concept' => 'calendar' , 'id' => $params['calendar'] ));
        
        if($params['user'] !== '0')
        {
        $user = Controller::read( array( 'concept' => 'user' , 'id' => $params['user'] ));
        $xmUser =   '<D:href>'.Config::service( 'CalDAV' , 'url' ).'/'.$user['uid'].'/</D:href>';
        }
        else
        $xmUser =   '<D:authenticated/>';

        $xml ="<?xml version=\"1.0\" encoding=\"utf-8\" ?>
           <D:acl xmlns:D=\"DAV:\">
             <D:ace>
               <D:principal>
            ".$xmUser."
               </D:principal>
               <D:grant>
            ".self::acltToXML($params['acl'])."
               </D:grant>
             </D:ace>
            </D:acl>";  
        
        ob_start();
        require_once ROOTPATH.'/plugins/davicalCliente/caldav-client-v2.php';
        $cal = new CalDAVClient( Config::service( 'CalDAV' , 'url' ).'/'.$calendar['location'].'/', Config::me( 'uid' ), Config::me( 'password' ));
        $cal->DoXMLRequest('ACL', $xml);
            self::setReadCurrentUserPrivilege(Config::service( 'CalDAV' , 'url' ).'/'.substr($calendar['location'] , 0 , (strpos (  $calendar['location'] ,  '/'  ) + 1 ) ));
        ob_end_clean();
    }
    }
    
    public function updateCollectionACL(&$uri ,&$params , &$criteria , $original)
    {      
        if( Config::module('useCaldav' , 'expressoCalendar') )
    {    
        $permision = Controller::read( array( 'concept' => 'calendarToPermission' , 'id' => $params['id'] ));
        $calendar = Controller::read( array( 'concept' => 'calendar' , 'id' => $permision['calendar'] ));
        
        if($permision['user'] !== '0')
        {
        $user = Controller::read( array( 'concept' => 'user' , 'id' => $permision['user'] ));
        $xmUser =   '<D:href>'.Config::service( 'CalDAV' , 'url' ).'/'.$user['uid'].'/</D:href>';
        }
        else
        $xmUser =   '<D:authenticated/>';

        $xml ="<?xml version=\"1.0\" encoding=\"utf-8\" ?>
               <D:acl xmlns:D=\"DAV:\">
             <D:ace>
               <D:principal>
                ".$xmUser."
               </D:principal>
               <D:grant>
                ".self::acltToXML($params['acl'])."
               </D:grant>
             </D:ace>
            </D:acl>";
        
        ob_start();
        require_once ROOTPATH.'/plugins/davicalCliente/caldav-client-v2.php';
        $cal = new CalDAVClient( Config::service( 'CalDAV' , 'url' ).'/'.$calendar['location'].'/', Config::me( 'uid' ), Config::me( 'password' ));
            $cal->DoXMLRequest('ACL', $xml);
            self::setReadCurrentUserPrivilege(Config::service( 'CalDAV' , 'url' ).'/'.substr($calendar['location'] ,  0 ,(strpos (  $calendar['location'] ,  '/'  ) + 1 ) ));
        ob_end_clean();
        
    }
    }
    
    
    private static function setReadCurrentUserPrivilege( $principalURL )
    {
        //Compatibilidade com o Thundebird e outros Clientes externos.
        //Esses clientes nescessitam ter acesso para ler suas permissões no nivel de prinvcipal 
        
        $xml ="<?xml version=\"1.0\" encoding=\"utf-8\" ?>
               <D:acl xmlns:D=\"DAV:\">
             <D:ace>
               <D:principal>
                <D:authenticated />
               </D:principal>
               <D:grant>
                <D:privilege><D:read-current-user-privilege-set/></D:privilege>
               </D:grant>
             </D:ace>
            </D:acl>";
        ob_start();
        require_once ROOTPATH.'/plugins/davicalCliente/caldav-client-v2.php';
        $cal = new CalDAVClient( $principalURL , Config::me( 'uid' ) , Config::me( 'password' ) );
        $cal->DoXMLRequest('ACL', $xml);  
        ob_end_clean();  
    }
    
    private static function acltToXML($acls)
    {
    $array = array();
    $acls = str_split($acls);
    
    foreach ($acls as &$acl)
        switch ($acl) 
        {
        case CALENDAR_ACL_WRITE:
            $array[] = "<D:privilege><D:bind/></D:privilege>";
            $array[] = "<D:privilege><D:write-properties/></D:privilege>";
            $array[] = "<D:privilege><D:write-content/></D:privilege>";
            $array[] = "<D:privilege><D:unlock/></D:privilege>";
            $array[] = "<D:privilege><D:schedule-deliver/></D:privilege>";
            $array[] = "<D:privilege><D:schedule-send/></D:privilege>";
            break;
        case CALENDAR_ACL_READ:
            $array[] = "<D:privilege><D:read /></D:privilege>";
            $array[] = "<D:privilege><D:schedule-query-freebusy /></D:privilege>";
            $array[] = "<D:privilege><D:read-free-busy /></D:privilege>";
            break;
        case CALENDAR_ACL_REMOVE:
            $array[] = "<D:privilege><D:unbind/></D:privilege>";
            break;
        case CALENDAR_ACL_SHARED:
            $array[] = "<D:privilege><D:write-acl/></D:privilege>";
            $array[] = "<D:privilege><D:read-acl/></D:privilege>";
            $array[] = "<D:privilege><D:read-current-user-privilege-set/></D:privilege>";
            break;
        case CALENDAR_ACL_BUSY:
            $array[] = "<D:privilege><D:schedule-query-freebusy/></D:privilege>";
            $array[] = "<D:privilege><D:read-free-busy/></D:privilege>";
            break;
        }      
    return implode("\n", $array);
    }
    
    static function import($data , $params = false)
    {                  
        $user =  Controller::find(array('concept' => 'user' , 'service' => 'OpenLDAP' ),false,array('filter' => array('=','uid',$params['owner']) , 'notExternal' => true));  
        $params['owner'] = $user[0]['id'];
        
        self::initSessionVars(array('user' => array('uidNumber' => $params['owner'] )));
        
        //Busca as Agendas do usuario
        $sig = Controller::find(array('concept' => 'calendarSignature'), array('user','calendar'), array('filter' => array( '=' , 'user' , $params['owner'])));
        
        //TODO: RESGATAR AGENDA
        foreach ($sig as $i => $v)
        {
          $cal =  Controller::read(array('concept' => 'calendar' , 'id' => $v['calendar'] ), array('location', 'timezone'));      
          if($cal['location'] === $params['calendarName'] )
          {              
              $params['calendar'] = $v['calendar'];
              $params['calendar_timezone'] = $cal['timezone'];
          }
        }
       
        if(isset($params['calendar']))
        {  
            $args =  Controller::parse( array( 'service' => 'iCal' ) , $data , $params);
            ob_start();
            include ROOTPATH.'/Sync.php';
            ob_end_clean();
        }
        
    }
    
    static function delete($data , $params = false)
    {   

        $user =  Controller::find(array('concept' => 'user' , 'service' => 'OpenLDAP' ),false,array('filter' => array('=','uid',$params['owner']) , 'notExternal' => true));

        $params['owner'] = $user[0]['id'];
        
        self::initSessionVars(array('user' => array('uidNumber' => $params['owner'] )));

        //Busca as Agendas do usuario
        $sig = Controller::find(array('concept' => 'calendarSignature'), array('user','calendar'), array('filter' => array( '=' , 'user' , $params['owner'])));
        
        //TODO: RESGATAR AGENDA
        foreach ($sig as $i => $v)
        {  
          $cal =  Controller::read(array('concept' => 'calendar' , 'id' => $v['calendar'] ), array('location'));      
          if($cal['location'] === $params['calendarName'] )
              $params['calendar'] = $v['calendar'];
        }
        if(isset($params['calendar']))
        {
            require_once ROOTPATH.'/plugins/icalcreator/iCalcreator.class.php';
            
            $vcalendar = new icalCreatorVcalendar( );
            $vcalendar->parse($data); 
            $vcalendar->sort();
            $toDelete = array();
            
            while ($component = $vcalendar->getComponent())
            {
                switch (strtoupper($component->objName)) {
                    case 'VEVENT':
                        $toDelete[] = $component->getProperty( 'uid' , false , false );
                        break;
                   case 'VTIMEZONE':
                        break;
                }
            }
            
            self::$deleted = $toDelete;
            
            foreach ($toDelete as $v)
            {
                 $even = Controller::find(array('concept' => 'schedulable') , false , array ( 'filter' => array('AND', array('=' , 'calendar' , $params['calendar']) , array('=' , 'uid' , $v ) ) ) );            
                 if(is_array($even) && count($even) > 0 )
                       Controller::delete(array('concept' => 'schedulable' , 'id' => $even[0]['id']));
            }
       }
    }
    
    static function move($origem , $destino , $owner)
    {  
        $user =  Controller::find(array('concept' => 'user' , 'service' => 'OpenLDAP' ),false,array('filter' => array('=','uid',$owner) , 'notExternal' => true));  
        $owner = $user[0]['id'];
        
        self::initSessionVars(array('user' => array('uidNumber' => $owner)));

        //Busca as Agendas do usuario
        $sig = Controller::find(array('concept' => 'calendarSignature'), array('user','calendar'), array('filter' => array('AND',array( '=' , 'user' , $params['owner']),array( '=' , 'isOwner' , '1')   )));
        
        //TODO: RESGATAR AGENDA
        foreach ($sig as $i => $v)
        {
          $cal =  Controller::read(array('concept' => 'calendar' , 'id' => $v['calendar'] ), array('local'));
          if($cal['local'] == $origem )
            Controller::update (array('concept' => 'calendar' , 'id' => $v['calendar'] ), array('local' => $destino));
        }
    }
    
    static function format($data , $params = false)
    {        
        return  Controller::format( array( 'service' => 'iCal' ) , $data , $params);      
    }
      
    static function deleteEvent($data , $params = false)
    {   
            $event = Controller::read( array( 'concept' => 'schedulable' , 'id' => $data ) , array('uid') );
            
            if(!is_array(self::$deleted) || !in_array($event['uid'], self::$deleted))
                self::deleteIcal($event['uid'] , array('uid' => $event['uid'] , 'location' => $params['location'] ));

    }
    
    static function putIcal($data , $params = false)
    {  
      ob_start();
      require_once ROOTPATH.'/plugins/davicalCliente/caldav-client-v2.php';
      $cal = new CalDAVClient( Config::service( 'CalDAV' , 'url' ).'/'.$params['location'].'/', Config::me( 'uid' ), Config::me( 'password' ) );
      $cal->DoPUTRequest( Config::service( 'CalDAV' , 'url' ).'/'.$params['location'].'/'.$params['uid'].'.ics', $data  );  
      ob_end_clean();
    }
    
    static function deleteIcal($data , $params = false)
    {
       ob_start();
       require_once ROOTPATH.'/plugins/davicalCliente/caldav-client-v2.php';
       $cal = new CalDAVClient( Config::service( 'CalDAV' , 'url' ).'/'.$params['location'].'/', Config::me( 'uid' ), Config::me( 'password' ));
       $cal->DoDELETERequest( Config::service( 'CalDAV' , 'url' ).'/'.$params['location'].'/'.$data.'.ics' );
       ob_end_clean();
    }
     
    static function rmcalendar($data , $params = false)
    {
       ob_start();
       require_once ROOTPATH.'/plugins/davicalCliente/caldav-client-v2.php';       
       $cal = new CalDAVClient( Config::service( 'CalDAV' , 'url' ).'/', Config::me( 'uid' ), Config::me( 'password' ));
       $cal->DoDELETERequest(Config::service( 'CalDAV' , 'url' ).'/'.Config::me( 'uid' ).'/'.$data.'/');
       ob_end_clean();
    }
    
    static function mvcalendar($origem , $destination)
    {
       ob_start();
       require_once ROOTPATH.'/plugins/davicalCliente/caldav-client-v2.php';
       $cal = new CalDAVClient( Config::service( 'CalDAV' , 'url' ).'/'.Config::me( 'uid' ).'/', Config::me( 'uid' ), Config::me( 'password' ));       
       $cal->DoMOVERequest( $origem.'/' , $destination.'/' );
       ob_end_clean();
    }
    
    static function findCalendars()
    {
       ob_start();
       require_once ROOTPATH.'/plugins/davicalCliente/caldav-client-v2.php';
       $cal = new CalDAVClient( Config::service( 'CalDAV' , 'url' ).'/', Config::me( 'uid' ), Config::me( 'password' ));       
       ob_end_clean();
       return $cal->FindCalendars();
    }
    
    static function mkcalendar($location , $name, $description )
    {   
        ob_start();
        require_once ROOTPATH.'/plugins/davicalCliente/caldav-client-v2.php';
        $cal = new CalDAVClient( Config::service( 'CalDAV' , 'url' ).'/', Config::me( 'uid' ), Config::me( 'password' ));
 
        $xml ="<?xml version=\"1.0\" encoding=\"utf-8\" ?>
      
                    <C:mkcalendar xmlns:D=\"DAV:\" xmlns:C=\"urn:ietf:params:xml:ns:caldav\">
                     <D:set>
                       <D:prop>
                         <D:displayname>$name</D:displayname>
                         <C:calendar-description xml:lang=\"en\">$description</C:calendar-description>
                         <C:supported-calendar-component-set>
                           <C:comp name=\"VEVENT\"/>
                         </C:supported-calendar-component-set>      
                       </D:prop>
                     </D:set>
                   </C:mkcalendar>";
          
          
        $cal->DoXMLRequest('MKCALENDAR', $xml, Config::service( 'CalDAV' , 'url' ).'/'.$location.'/');
        ob_end_clean();
    }
    
    static function importCollection ($url , $calendar)
    {
        ob_start();
        require_once ROOTPATH.'/plugins/davicalCliente/caldav-client-v2.php';
        $cal = new CalDAVClient( Config::service( 'CalDAV' , 'url' ).'/', Config::me( 'uid' ), Config::me( 'password' ));
         
        $events = $cal->GetCollectionETags($url) ;
        $args = array();
        foreach ($events as $ie => $ve)
        {
            $cal->DoGETRequest($ie);
            $sync =  Controller::parse( array( 'service' => 'iCal' ) , $cal->GetResponseBody() , array('calendar' => $calendar , 'owner' => Config::me( 'uidNumber' )));
            
            if( is_array( $sync ) )
                $args = array_merge( $args , $sync );
        }
        
        include ROOTPATH.'/Sync.php';
        ob_end_clean();
    }
    
}

?>
