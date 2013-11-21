<?php
/**
* CPAINT (Cross-Platform Asynchronous INterface Toolkit)
*
* http://sf.net/projects/cpaint
* 
* released under the terms of the GPL
* see http://www.fsf.org/licensing/licenses/gpl.txt for details
* 
* $Id$
* $Log$
* Revision 1.5  2005/07/20 17:25:14  saloon12yrd
* updated file headers to contain proper licensing information as well as @package info
*
* Revision 1.3  2005/07/14 04:43:20  saloon12yrd
* - changed parameter cpaint_returnxml=true to cpaint_response_type=(OBJECT|TEXT|XML) to be futureproof when JSON or other formats get implemented
* - added toXML() method in cpaint2.inc.php
* not sure it response-type TEXT worked in earlier version. now it does
*
*
* proxy script to pass request on to remote servers
*
* @package    CPAINT
* @author     Paul Sullivan <wiley14@gmail.com>
* @author     Dominique Stender <dstender@st-webdevelopment.de>
* @copyright  Copyright (c) 2005-2006 Paul Sullivan, Dominique Stender - http://sf.net/projects/cpaint
* @version 		2.0.2
*/

//---- includes ----------------------------------------------------------------
	/**
	*	@include config
	*/
	require_once("cpaint2.config.php");
	
//---- main code ---------------------------------------------------------------

  error_reporting (E_ALL ^ E_NOTICE ^ E_WARNING); 
  set_time_limit(0);
  
  if ($_GET['cpaint_remote_url'] != "") {
    $cp_remote_url      = urldecode($_GET['cpaint_remote_url']);
    $cp_remote_method   = urldecode($_GET['cpaint_remote_method']);
    $cp_remote_query    = urldecode($_GET['cpaint_remote_query']);
    $cp_response_type   = strtoupper($_GET['cpaint_response_type']);
  }

  if ($_POST['cpaint_remote_url'] != "") {
    $cp_remote_url      = urldecode($_POST['cpaint_remote_url']);
    $cp_remote_method   = urldecode($_POST['cpaint_remote_method']);
    $cp_remote_query    = urldecode($_POST['cpaint_remote_query']);
    $cp_response_type   = strtoupper($_POST['cpaint_response_type']);
  }

  // propagate XML header if necessary
  if ($cp_response_type == 'XML'
    || $cp_response_type == 'OBJECT') {
    header("Content-type:  text/xml");
  }

  // transfer mode specifics
  if ($cp_remote_method == 'GET') {
    $cp_remote_url    .= '?' . $cp_remote_query;
    $cp_request_body  = '';

    // prepare parameters
    $url_parts  = parse_url($cp_remote_url);
  
    // build basic header
    $cp_request_header  = 'GET ' . $url_parts['path'] . '?' . str_replace(' ', '+', $url_parts['query']) . " HTTP/1.0\r\n"
                        . "Host: " . $url_parts['host'] . "\r\n";
  
  } elseif ($cp_remote_method == 'POST') {
    $cp_request_body  = '&' . $cp_remote_query;

    // prepare parameters
    $url_parts  = parse_url($cp_remote_url);
		
		// check against whitelist
		if ($cpaint2_config["proxy.security.use_whitelist"] == true) {
			$url_allowed = false;
			foreach($cpaint2_proxy_whitelist as $whitelistURL) {
				$whiteList_parts = parse_url("http://" . $whitelistURL);
				$url_parts_temp = parse_url("http://" . $cp_remote_url);
				if (array_key_exists("path", $whiteList_parts)) {
					if ((strtolower($whiteList_parts["path"]) == strtolower($url_parts_temp["path"])) && (strtolower($whiteList_parts["host"]) == strtolower($url_parts_temp["host"]))) $url_allowed = true;					
				} else {	// no path, check only host
					if (strtolower($whiteList_parts["host"]) == strtolower($url_parts_temp["host"]))	$url_allowed = true;
				}
			}
			if ($url_allowed == false) die("[CPAINT] The host or script cannot be accessed through this proxy.");
		}
    
    // build basic header
    $cp_request_header  = 'POST ' . $url_parts['path']  . " HTTP/1.0\r\n"
                        . "Host: " . $url_parts['host'] . "\r\n"
                        . "Content-Type:  application/x-www-form-urlencoded\r\n";
  }

  // add port if none exists
  if (!isset($url_parts['port'])) {
    $url_parts['port'] = 80;
  }

  // add content-length header
  $cp_request_header .= "Content-Length: " . strlen($cp_request_body) . "\r\n";

  // add authentication to header if necessary
  if ($url_parts['user'] != '') {
    $cp_request_header .= 'Authorization: Basic ' . base64_encode($url_parts['user'] . ':' . $url_parts['pass']) . "\r\n";
  }

  // open connection
  $cp_socket = @fsockopen($url_parts['host'], $url_parts['port'], $error, $errstr, 10);
  
  if ($cp_socket !== false) {
    // send headers
    @fwrite($cp_socket, $cp_request_header . "\r\n\r\n");
    
    // send body if necessary
    if ($cp_request_body != '') {
      @fwrite($cp_socket, $cp_request_body . "\r\n");
    }
    
    while (!feof($cp_socket)) {
      $http_data = $http_data . fgets($cp_socket);
    }

    list($http_headers, $http_body) = preg_split('/\r\n\r\n/', $http_data, 2);
    echo($http_body);
    @fclose($cp_socket);
  }

?>