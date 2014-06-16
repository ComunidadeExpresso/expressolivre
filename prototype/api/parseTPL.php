<?php
use prototype\api\Config;

class ParseTPL
{
	
	static function load_tpl(&$data, $file)
    {
        include_once __DIR__ . '/../library/fileDuck/FileDuck.php';

        $config = array();
        $config['lang'] = 'pt_BR';
        $sql  = "SELECT * FROM phpgw_preferences where preference_app = 'common' AND preference_owner IN ( '-2' , '-1' , " . Config::me('uidNumber') . " ) ORDER BY preference_owner";
        $preferences =  Controller::service('PostgreSQL')->execResultSql($sql);
        foreach( $preferences as $preference){
            $values = unserialize($preference['preference_value']);
            if(isset( $values['lang'] ))
                $config['lang'] = $values['lang'];
        }

        $config['provider'] = 'expresso';
        $config['YUICompressor'] = false;

        $configProvider = array();
        $configProvider['module'] = 'expressoCalendar';

        if( preg_match('/\/modules\/([a-z\_\-]+)\//i' , $file , $matches))
        {
            $moduleMap =  parse_ini_file( __DIR__ ."/../config/moduleMap.ini", true );
            $configProvider['module'] = isset( $moduleMap[$matches[1]] ) ?  $moduleMap[$matches[1]] : 'phpgwapi' ;
        }

        $fileDuck = new FileDuck( $config , $configProvider );
        $fileDuck->add( $file , 'ISO-8859-1' );
        $tpl = $fileDuck->renderContent();

        foreach($data as $i => $v)
            $tpl = str_replace('['.$i.']',$v,$tpl);

        return $tpl;
    }
}

?>
