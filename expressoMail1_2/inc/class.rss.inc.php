<?php

class rss
{

	function rss (){
	}
	// BEGIN of functions.
	function getContent($param){
		$daurl=$param['url'];
		if (preg_match('/http(s)?:\/\//i',$daurl,$matches) == 0)
			$daurl = 'http://'.$daurl;

		// Set your return content type
		header('Content-type: application/xml');

		// Get that website's content
		$handle = fopen($daurl, "r");

		// If there is something, read and return
		if ($handle) {
			$buffer = fgets($handle, 4096);
			$pattern = '/<\?xml version=".\.0"( encoding="[A-Z\-0-9]+")?\ *\?>/i';
			if (preg_match($pattern,$buffer,$matches) == 0)
				exit;
			echo $buffer;
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				echo $buffer;
			}
			fclose($handle);
		}
		exit;
	}
	function getChannels(){
		include('../header.inc.php');
		$GLOBALS['phpgw']->db->query('SELECT rss_url,name FROM phpgw_userrss WHERE uid = '.$_SESSION['phpgw_session']['account_id']);
		while($GLOBALS['phpgw']->db->next_record())
			$return[]=$GLOBALS['phpgw']->db->row();
		return $return;
	}
	function addChannel($param){
		include('../header.inc.php');
		$name = $GLOBALS['phpgw']->db->db_addslashes(htmlentities($param['name']));
		$url = $GLOBALS['phpgw']->db->db_addslashes($param['url']);
		$GLOBALS['phpgw']->db->query('INSERT INTO phpgw_userrss values('.$_SESSION['phpgw_session']['account_id'].',\''.$url.'\',\''.$name.'\');',__LINE__,__FILE__);
		if ($GLOBALS['phpgw']->db->Error)
			    return "Error";
		else
			    return "Success";

	}

	function removeChannel($param){
		include('../header.inc.php');
		$url = $GLOBALS['phpgw']->db->db_addslashes($param['url']);
		$GLOBALS['phpgw']->db->query('DELETE FROM phpgw_userrss where rss_url = \''.$url.'\';',__LINE__,__FILE__);
		if ($GLOBALS['phpgw']->db->Error)
			    return "Error";
		else
			    return "Success";
	}

}
?>
