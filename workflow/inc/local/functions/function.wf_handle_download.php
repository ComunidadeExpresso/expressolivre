<?php
/**
 * Handles download requests based on logic provided by the callback function.
 * @param array $request Information about HTTP request
 * @param string $callback Function that provides the logic for the download process.
 * @return void
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package Workflow 
 * @subpackage local 
 * @access public
 */
function wf_handle_download($request, $callback)
{
	/* check if it is in download mode */
	if (!isset($request['download_mode']))
		return;
	else
		if ($request['download_mode'] != "true")
			return;
	
	/* verify if the callback function exists and is callable */
	if ((function_exists($callback)) && (is_callable($callback)))
	{
		/* prepare the parameters */
		$params = array();
		foreach ($request as $key => $value)
			$params[$key] = $value;
		
		/* call the callback function and await the result */
		$fileResult = call_user_func($callback, $params);
		
		/* if everything is ok, then proceed with the download of the file */
		if (!is_null($fileResult))
		{
			if (isset($fileResult['filename']) && isset($fileResult['content']))
			{
					if (preg_match('/Opera(\/| )([0-9].[0-9]{1,2})/', $_SERVER['HTTP_USER_AGENT']))
					$UserBrowser = "Opera";
				elseif (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $_SERVER['HTTP_USER_AGENT']))
					$UserBrowser = "IE";
					else
						$UserBrowser = '';
				
				$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
				header("Pragma: public");
				header("Cache-Control: cache, must-revalidate");
				header("Content-Type: application/force-download");
				header("Content-Disposition: attachment; filename=\"" . $fileResult['filename'] . "\"");
				header('Pragma: no-cache');
				header("Expires: 0");
		        header("Content-length: " . strlen($fileResult['content']));
		        echo $fileResult['content'];
				die();
			}
		}
		else
			die();
	}
} 
?>
