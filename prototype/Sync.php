<?php


$accept = $_SERVER["HTTP_ACCEPT"];

if(!function_exists('getRealREQUEST'))
{
    function getRealREQUEST() {
        $vars = array();

        if(isset($_SERVER['REDIRECT_QUERY_STRING']))
            $input    = $_SERVER['REDIRECT_QUERY_STRING'];

        if(!empty($input)){
            $pairs    = explode("&", $input);
            foreach ($pairs     as $pair) {
                $nv                = explode("=", $pair);
                
                $name            = urldecode($nv[0]);
                $nameSanitize    = preg_replace('/([^\[]*)\[.*$/','$1',$name);
                
                $nameMatched    = str_replace('.','_',$nameSanitize);
                $nameMatched    = str_replace(' ','_',$nameMatched);
                
                $vars[$nameSanitize]    = $_REQUEST[$nameMatched];
            }
        }
        
        $input    = file_get_contents("php://input");
        if(!empty($input)){
            $pairs    = explode("&", $input);
            foreach ($pairs as $pair) {
                $nv                = explode("=", $pair);
                
                $name            = urldecode($nv[0]);
                $nameSanitize    = preg_replace('/([^\[]*)\[.*$/','$1',$name);
                
                $nameMatched    = str_replace('.','_',$nameSanitize);
                $nameMatched    = str_replace(' ','_',$nameMatched);
                
                $vars[$nameSanitize]    = $_REQUEST[$nameMatched];
            }
        }
        
        return $vars;
    }
}

if( !isset( $args ) )
    $args = getRealREQUEST();

if(!function_exists('parseURI'))
{
    function parseURI( $URI )
    {
    //     $regex = "#^([a-zA-Z0-9-_]+)\(([a-zA-Z0-9-_]+)\)://(.*)|([a-zA-Z0-9-_]+)://(.*)$#";//TODO: checar essa RegExp
        $regex = "#^([a-zA-Z0-9-_]+)://(.*)$#";

        preg_match( $regex, $URI, $matches );

        if( !$matches || empty($matches) )
            return( array(false, $URI, false) );

        return( $matches );
    }
}

if(!function_exists('formatURI'))
{
    function formatURI( $concept = false, $id = false, $service = false )
    {
        return $concept ? $id ? $service ?

               $concept.'://'.$id.'('.$service.')':

               $concept.'://'.$id:

               $concept:

               false;
    }
}

///Conversor Para utf8 ante de codificar para json pois o json so funciona com utf8
if(!function_exists('toUtf8'))
{
    function toUtf8($data)
    {
	if(!is_array($data))
	  return mb_convert_encoding( $data , 'UTF-8' , 'UTF-8 , ISO-8859-1' );

	$return = array();

	foreach ($data as $i => $v)
	  $return[toUtf8($i)] = toUtf8($v);

	return $return;
    }
}

require_once 'api/controller.php';

$mounted = array(); $synced = array();

if(!function_exists('prepare'))
{
    function prepare( $concept, $id, $dt, &$data, &$oldIds, &$mounted, &$synced )
    {
	$oldIds[] = $id;

        if( $dt === 'false' ||
            $dt ===  false )
            return( false );

        if( !preg_match( '#^([a-zA-Z0-9-_]+)\(.*\)$#', $id ) )
            $dt['id'] = $id;
        elseif( isset($dt['id']) && $dt['id'] === $id )
            unset($dt['id']);

        $links = Controller::links( $concept );

        foreach( $links as $linkName => $linkTarget )
        {
                    if( isset( $dt[$linkName] ) )
                    {
                            if( $notArray = Controller::hasOne( $concept, $linkName ) )
                                    $dt[$linkName] = array( $dt[$linkName] );

                            foreach( $dt[$linkName] as $i => $d )
                            {
                                    $currentURI = formatURI($links[$linkName], $d);

                                    if( isset( $mounted[ $currentURI ] ) )
                                    {
                                            unset( $dt[$linkName][$i] );
                                    }
                                    elseif( isset( $synced[ $d ] ) )
                                    {
                                            $dt[$linkName][$i] = $synced[ $d ];
                                    }
                                    elseif( isset($data[ $currentURI ]) )
                                    {
                                            $value = $data[$currentURI];
                                            unset( $data[ $currentURI ] );

                                            $mounted[ $currentURI ] = true;

                                            $dt[$linkName][$i] = prepare( $links[$linkName], $d, $value, $data, $oldIds, $mounted, $synced );
                                    }
                            }

                            if( empty( $dt[$linkName] ) )
                                    unset( $dt[$linkName] );
                            elseif( $notArray )
                                    $dt[$linkName] = $dt[$linkName][0];
                    }
        }

        return( $dt );
    }
}

$return = array();

if( !isset( $args[0] ) )
    $args = array( $args );

Controller::addFallbackHandler( 0, function($e, $URI){

    throw new Exception( $e->getMessage(), 100, $e );

} );

Controller::addFallbackHandler( 100, function( $e, $URI ){

    Controller::rollback( $URI );
    throw $e;

});


foreach( $args as $i => $data )
{
    foreach( $data as $uri => $dt )
    {
	  if( !isset($data[$uri]) )
		  continue;

        // it fix a bug - import some event with repetition.
        if( isset( $dt['byday'][0] ) )
        {
            $count = strlen( $dt['byday'][0] );

            $days = $dt['byday'][0];

            $dt['byday'] = $dt['byday']['DAY'];

            $i = 0;
            while($i < $count) {
                $str = substr($days, $i, 2);
                $dt['byday'] .= ",{$str}";
                $i += 2;
            }
        }

        // it fix a bug - randomly a repetition event imported come to sunday.
        if( !isset($dt['byday']) && ($dt['frequency'] || $dt['interval']) )
            $dt['byday'] = strtoupper(substr(date('D', substr($dt['startTime'], 0, 10 )), 0 , 2 ));


	  list( , $concept, $id ) = parseURI( $uri );

	  unset( $data[$uri] );
	  $mounted[$uri] = true;

	  $oldIds = array();

	  $dt = prepare( $concept, $id, $dt, $data, $oldIds, $mounted, $synced );

        // it fix a bug - randomly a repetition event created come to sunday.
        if( is_array($dt['schedulable']['repeat']) )
            if( ( !isset($dt['schedulable']['repeat']['byday']) || empty($dt['schedulable']['repeat']['byday']) ) && ($dt['schedulable']['repeat']['frequency'] || $dt['schedulable']['repeat']['interval']) )
                $dt['schedulable']['repeat']['byday'] = strtoupper(substr(date('D', strtotime(substr($dt['schedulable']['repeat']['startTime'], 0, 10 ))), 0 , 2 ));

	  try{
	      $result = Controller::put( array( 'concept' => $concept, 'id' => $id ), $dt );
	  }
	  catch( Exception $e ){
	      $return[ $uri ] = toUtf8( $e->getMessage() );
	      unset( $data[$uri] );
	      continue;
	  }

	  if( !$result )
	  {
	      $return[ $uri ] = 'ROLLBACK';
	      unset( $data[$uri] );
	      continue;
	  }

	  foreach( $result as $ii => $tx )
	  {
              if( !isset($tx['order']) )
                    continue;

		  $oldId = $oldIds[ $tx['order'] ];

		  if( isset( $oldId ) && $oldId )
		  {
		      $synced[ $oldId ] = $tx['id'];
		      unset( $oldIds[ $tx['order'] ] );
		  }

		  $oldURI = formatURI( $tx['concept'], $oldId );
		  unset( $data[$oldURI] );

		  $return[ $oldURI ] = !$tx['rollback'] ? $dt ?
				       array( 'id' => $tx['id'] ) : false : 'ROLLBACK';
	  }
    }

    foreach( $data as $oldURI => $oldVal )
      $return[ $oldURI ] = 'ROLLBACK';
}


echo json_encode( $return );

Controller::closeAll();

//         ob_start();
//         print "\n";
//         print "result: ";
//         print_r( $result );
//         $output = ob_get_clean();
//         file_put_contents( "/tmp/prototype.log", $output , FILE_APPEND );
