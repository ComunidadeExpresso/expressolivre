<?php 
/* 
 CPAINT Backend Debug Interface

 released under the terms of the GPL
 see http://www.fsf.org/licensing/licenses/gpl.txt for details
 
 @package    CPAINT
 @access     public
 @author     Paul Sullivan  <wiley14@gmail.com>
 @author     Stephan Tijink <stijink@googlemail.com>
 @copyright  Copyright (c) 2005-2006 Paul Sullivan - http://sf.net/projects/cpaint 
 @version    2.0.2
*/

if (!(isset($_GET["cpaint_function"]) || isset($_POST["cpaint_function"]))) {
	$debug_html_start = '
	<html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>CPAINT Debug Interface</title>
	<style type="text/css">body { background-color: #9999CC; margin-top: 0px; }
		.style3 { font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
		.style4 { font-size: 20px; color: #FFFFFF; font-family: Arial, Helvetica, sans-serif;} 
		.style5 { font-size: 16px;	color: #FFFFFF;	font-family: Arial, Helvetica, sans-serif;	font-weight: bold; } 
		.style6 { font-size: 12px;	color: #FFFFFF;	font-family: Arial, Helvetica, sans-serif;	font-weight: bold; } 
		div, iframe {	margin: 0px; border: 1px solid #9999CC; }
	</style>
	<script type="text/javascript">
		function showForm(divId) {
			if (document.getElementById(divId).style.display == "block") { 
				document.getElementById(divId).style.display = "none"; 
			} else { 
				document.getElementById(divId).style.display = "block";
			}
		}
	</script>
	</head>
	<body>
		<table width="100%"  border="0" cellspacing="0" cellpadding="0">
		<tr><td bgcolor="#9999CC"><div align="justify"><span class="style4">CPAINT Debug Interface</span></div></td></tr>
		<tr><td bgcolor="#9999CC"><div align="justify"><span class="style6" style="color:#FFFFFF;">backend filename: '.$_SERVER["SCRIPT_NAME"].'</span></div></td></tr>
		<tr><td height="10" bgcolor="#9999CC" class="style3"></td></tr>
		<tr><td height="10" bgcolor="#9999CC" class="style3"></td></tr>
		<tr><td bgcolor="#FFFFFF" class="style3"><blockquote>';
	$debug_html_end = "</blockquote></td></tr></table><br /><iframe name=\"results\" class=\"style3\" width=\"100%\" height=\"100%\" scrolling=\"yes\" allowtransparency=\"false\" style=\"background-color:  #FFFFFF\"></iframe></body></html>";
	
	// get function names and function variables/values
	$functionArray = getCallableCode();
	
	$debug_body = "";
	if (count($functionArray) > 0) {
		foreach ($functionArray as $func_name=>$func_variables) {
			$debug_body = $debug_body . "<form method=\"get\" target=\"results\"><a href=\"javascript:showForm('" . $func_name . "_form');\">" . $func_name . "</a><div id=\"" . $func_name . "_form\" style=\"display:  none;\">";
			
			$debug_body = $debug_body . '<table border="0">';
			if ( count($func_variables) > 0) {
				foreach ($func_variables as $var_name=>$var_preset) {
					$debug_body = $debug_body . '<tr><td class="style3">Parameter (<strong>$'.$var_name.'</strong>):</td><td><input type="text" name="cpaint_argument[]"></td>';
					if (strlen($var_preset) > 0) { $debug_body = $debug_body . "<td class=\"style3\"> &nbsp; default value is <strong>".$var_preset." &nbsp;</strong></td>"; }
					$debug_body = $debug_body . '</tr>';
				}
			}
			$debug_body = $debug_body . "<tr><td class=\"style3\">Response Type:</td><td><select name=\"cpaint_response_type\"><option>TEXT</option><option>XML</option><option>OBJECT</option></select></td></tr>";
			$debug_body = $debug_body . "<tr><td colspan=\"3\"><input type=\"hidden\" name=\"cpaint_function\" value=\"" . $func_name . "\"><input type=\"submit\"></td></tr></table></div></form>";
		}
	}
	
	print($debug_html_start . $debug_body . $debug_html_end);
	die();
}

function getCallableCode() {
	$scriptName = $_SERVER['SCRIPT_FILENAME'];
	$fileLines = file($scriptName);
	for ($i=0; $i < sizeof($fileLines); ++$i) {
		$line = trim($fileLines[$i]);
		if (substr($line, 0, 9) == "FUNCTION " || substr($line,0,9) == "function ") {
			$match[] = $line;
		}		
	}
	for ($i = 0; $i < sizeof($match); ++$i) {
		$line = str_replace("function ", "", $match[$i]);
		$line = str_replace("FUNCTION ", "", $line);
		$line = str_replace("{", "", $line);
		$parts = explode("(", $line);
		$func_name = trim($parts[0]);
		
		$Tempargs = explode(")", $parts[1]);
		$args = explode(",", $Tempargs[0]);
		$argSize = sizeof($args);
		
		// check args for preset values
		if ($argSize > 0) {
			foreach ($args as $arg) {
				$arg 		= trim ($arg);
				$varArray 	= explode ("=", $arg);
				$var_name 	= trim (str_replace ("$", "", $varArray["0"]));
				$var_value 	= trim ($varArray["1"]);
				$resultArray[$func_name][$var_name] = $var_value;
			}
		}
	}
	return $resultArray;
}
?>