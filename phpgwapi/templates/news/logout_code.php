<?php	
	
	function check_logoutcode($code)
	{
		switch($code)
		{
			case 1:
				return lang('You have been successfully logged out');
				
			case 2:
				return lang('Sorry, your login has expired');
				
			case 4:
				return lang('Cookies are required to login to this site.');
				
			case 5:
				return '<font color="FF0000">' . lang('Bad login or password') . '</font>';

			case 6:
				return '<font color="FF0000">' . lang('Your password has expired, and you do not have access to change it') . '</font>';
				
			case 98:
				return '<font color="FF0000">' . lang('Account is expired') . '</font>';
				
			case 99:
				return '<font color="FF0000">' . lang('Blocked, too many attempts(%1)! Retry in %2 minute(s)',$GLOBALS['phpgw_info']['server']['num_unsuccessful_id'],$GLOBALS['phpgw_info']['server']['block_time']) . '</font>';
			case 200:
                            //return '<font color="FF0000">' . lang('Invalid code') . '</font>';
                return '<font color="FF0000">' . lang('Bad login or password') . '</font>';
			    break;
			case 10:
				$GLOBALS['phpgw']->session->phpgw_setcookie('sessionid');
				$GLOBALS['phpgw']->session->phpgw_setcookie('kp3');
				$GLOBALS['phpgw']->session->phpgw_setcookie('domain');

				//fix for bug php4 expired sessions bug
				if($GLOBALS['phpgw_info']['server']['sessions_type'] == 'php4')
				{
					$GLOBALS['phpgw']->session->phpgw_setcookie(PHPGW_PHPSESSID);
				}

				return '<font color="#FF0000">' . lang('Your session could not be verified.') . '</font>';
				
			default:
				return '';
		}
	}

?>