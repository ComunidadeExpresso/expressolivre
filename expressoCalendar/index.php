<?php

        $GLOBALS['phpgw_info']['flags'] = Array(   'currentapp'    =>      'expressoCalendar',
                                                   'noheader'      =>      false,
                                                   'nonavbar'      =>      false,
                                                   'noappheader'   =>      true,
                                                   'noappfooter'   =>      true,
                                                   'nofooter'      =>      true  );
						  
        require_once( dirname(__FILE__).'/../prototype/api/config.php' );
        
        require_once (dirname(__FILE__).'/../header.inc.php');
        
        $_SESSION['flags']['currentapp'] = 'expressoCalendar';
 
	define( 'MODULESURL' , '../prototype/modules/calendar' );
	define( 'PLUGINSURL' , '../prototype/plugins' );
  echo '<link rel="stylesheet" type="text/css" href="../prototype/modules/calendar/assetic_css.php"></link>';
  include ROOTPATH.'/modules/calendar/templates/index.ejs';
  echo '<script type="text/javascript" src="../prototype/modules/calendar/assetic.php"></script>';      
?>
