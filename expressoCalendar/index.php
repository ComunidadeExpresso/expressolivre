<?php

  $GLOBALS['phpgw_info']['flags'] = array(  'currentapp'    => 'expressoCalendar',
                                            'noheader'      => false,
                                            'nonavbar'      => false,
                                            'noappheader'   => true,
                                            'noappfooter'   => true,
                                            'nofooter'      => true  );
  	  
  require_once( dirname(__FILE__).'/../prototype/api/config.php' );

  require_once( dirname(__FILE__).'/../header.inc.php' );

  $_SESSION['flags']['currentapp'] = 'expressoCalendar';

  $accountInfo = $GLOBALS['phpgw']->accounts->read_repository();
  isset( $_COOKIE[ 'sessionid' ] ) ? session_id( $_COOKIE[ 'sessionid' ] ) : session_id(); 
  session_start();
  
  //Carregando na sessão configurações do usuario usado na nova API.  
  $_SESSION['wallet']['user']['uid']            =  $accountInfo['account_lid'];
  $_SESSION['wallet']['user']['uidNumber']      =  $accountInfo['account_id'];
  $_SESSION['wallet']['user']['password']       =  $_POST['passwd'];
  $_SESSION['wallet']['user']['cn']             =  $accountInfo['firstname'].' '.$accountInfo['lastname'];
  $_SESSION['wallet']['user']['mail']           =  $accountInfo['email'];

 
  define( 'MODULESURL' , '../prototype/modules/calendar' );
  define( 'PLUGINSURL' , '../prototype/plugins' );
  echo '<link rel="stylesheet" type="text/css" href="../prototype/modules/calendar/assetic_css.php"></link>';
  include ROOTPATH.'/modules/calendar/templates/index.ejs';
  echo '<script type="text/javascript" src="../prototype/modules/calendar/assetic.php"></script>';

?>
