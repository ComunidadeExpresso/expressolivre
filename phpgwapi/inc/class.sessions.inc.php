<?php
  /**************************************************************************\
  * eGroupWare API - Session management                                      *
  * This file written by Dan Kuykendall <seek3r@phpgroupware.org>            *
  * and Joseph Engo <jengo@phpgroupware.org>                                 *
  * and Ralf Becker <ralfbecker@outdoor-training.de>                         *
  * Copyright (C) 2000, 2001 Dan Kuykendall                                  *
  * Parts Copyright (C) 2003 Free Software Foundation Inc                    *
  * -------------------------------------------------------------------------*
  * This library is part of the eGroupWare API                               * 
  * http://www.egroupware.org/api                                            *  
  * ------------------------------------------------------------------------ *
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/


	/* sessions_type setup moved after the class below - milosch */

	/**
	* Session Management Libabray
	*
	* This allows eGroupWare to use php4 or database sessions
	*
	* @package phpgwapi
	* @subpackage sessions
	* @abstract
	* @author NetUSE AG Boris Erdmann, Kristian Koehntopp <br /> hacked on by phpGW
	* @copyright &copy; 1998-2000 NetUSE AG Boris Erdmann, Kristian Koehntopp <br /> &copy; 2003 FreeSoftware Foundation
	* @license LGPL
	* @link http://www.sanisoft.com/phplib/manual/DB_sql.php
	* @uses db
	*/

	class sessions_
	{
		/**
		* @var string current user login
		*/
		var $login;

		/**
		* @var string current user password
		*/
		var $passwd;

		/**
		* @var int current user db/ldap account id
		*/
		var $account_id;

		/**
		* @var string current user account login id - ie user@domain
		*/
		var $account_lid;

		/**
		* @var string previous page call id - repost prevention
		*/
		var $history_id;

		/**
		* @var string domain for current user
		*/
		var $account_domain;

		/**
		* @var session type flag, A - anonymous session, N - None, normal session
		*/
		var $session_flags;

		/**
		* @var string current user session id
		*/
		var $sessionid;

		/**
		* @var string not sure what this does, but it is important :)
		*/
		var $kp3;

		/**
		* @var string encryption key?
		*/
		var $key;

		/**
		* @var string iv == ivegotnoidea ;) (skwashd)
		*/
		var $iv;

		/**
		* @var session data
		*/
		var $data;
        
		/**
		* @var object holder for the database object
		*/
		var $db;
        
		/**
		* @var array publicly available methods
		*/
		var $public_functions = array(
			'list_methods' => True,
			'update_dla'   => True,
			'list'         => True,
			'total'        => True
		);

		/**
		* @var string domain for cookies
		*/
		var $cookie_domain;

		/**
		* @var name of XML-RPC/SOAP method called
		*/
		var $xmlrpc_method_called;

		/**
		* Constructor just loads up some defaults from cookies
		*/
		function sessions_()
		{
			$this->db = $GLOBALS['phpgw']->db;
			$this->sessionid = get_var('sessionid',array('GET','COOKIE'));
			$this->kp3       = get_var('kp3',array('GET','COOKIE'));
			/* Create the crypto object */
			$GLOBALS['phpgw']->crypto = CreateObject('phpgwapi.crypto');
			if ($GLOBALS['phpgw_info']['server']['usecookies'])
			{
				$this->phpgw_set_cookiedomain();
			}
			// verfiy and if necessary create and save our config settings
			//
			$save_rep = False;
			if (!isset($GLOBALS['phpgw_info']['server']['max_access_log_age']))
			{
				$GLOBALS['phpgw_info']['server']['max_access_log_age'] = 90;	// default 90 days
				$save_rep = True;
			}
			if (!isset($GLOBALS['phpgw_info']['server']['block_time']))
			{
				$GLOBALS['phpgw_info']['server']['block_time'] = 30;	// default 30min
				$save_rep = True;
			}
			if (!isset($GLOBALS['phpgw_info']['server']['num_unsuccessful_id']))
			{
				$GLOBALS['phpgw_info']['server']['num_unsuccessful_id']  = 3;	// default 3 trys per id
				$save_rep = True;
			}
			if (!isset($GLOBALS['phpgw_info']['server']['num_unsuccessful_ip']))
			{
				$GLOBALS['phpgw_info']['server']['num_unsuccessful_ip']  = $GLOBALS['phpgw_info']['server']['num_unsuccessful_id'];	// default same as for id
				$save_rep = True;
			}
			if (!isset($GLOBALS['phpgw_info']['server']['install_id']))
			{
				$GLOBALS['phpgw_info']['server']['install_id']  = md5($GLOBALS['phpgw']->common->randomstring(15));
				$save_rep = True;
			}
			if (!isset($GLOBALS['phpgw_info']['server']['sessions_timeout']))
			{
				$GLOBALS['phpgw_info']['server']['sessions_timeout'] = 14400;
				$save_rep = True;
			}
			if (!isset($GLOBALS['phpgw_info']['server']['sessions_app_timeout']))
			{
				$GLOBALS['phpgw_info']['server']['sessions_app_timeout'] = 86400;
				$save_rep = True;
			}
			if (!isset($GLOBALS['phpgw_info']['server']['max_history']))
			{
				$GLOBALS['phpgw_info']['server']['max_history'] = 20;
				$save_rep = True;
			}
			
			// jakjr: ? usando o hardcode, para evitar sempre 2 chamadas ao banco.
			/*
			if ($save_rep)
			{
				$config = CreateObject('phpgwapi.config','phpgwapi');
				$config->read_repository();
				$config->value('max_access_log_age',$GLOBALS['phpgw_info']['server']['max_access_log_age']);
				$config->value('block_time',$GLOBALS['phpgw_info']['server']['block_time']);
				$config->value('num_unsuccessful_id',$GLOBALS['phpgw_info']['server']['num_unsuccessful_id']);
				$config->value('num_unsuccessful_ip',$GLOBALS['phpgw_info']['server']['num_unsuccessful_ip']);
				$config->value('install_id',$GLOBALS['phpgw_info']['server']['install_id']);
				$config->value('sessions_timeout',$GLOBALS['phpgw_info']['server']['sessions_timeout']);
				$config->value('sessions_app_timeout',$GLOBALS['phpgw_info']['server']['sessions_app_timeout']);
				$config->save_repository();
				unset($config);
			}*/
		}
		
		private function _init_crypto()
		{
			/* init the crypto object before appsession call below */
			$encryptkey = isset($GLOBALS['phpgw_info']['server']['encryptkey'])? $GLOBALS['phpgw_info']['server']['encryptkey'] : '';
			$this->key = md5( $this->kp3 . $this->sessionid . $encryptkey );
			$this->iv  = isset($GLOBALS['phpgw_info']['server']['mcrypt_iv'])? $GLOBALS['phpgw_info']['server']['mcrypt_iv'] : '';
			$GLOBALS['phpgw']->crypto->init( array( $this->key, $this->iv ) );
		}

		/**
		* Introspection for XML-RPC/SOAP
		* Diabled - why??
		*
		* @param string $_type tpye of introspection being sought
		* @return array available methods and args
		*/
		function DONTlist_methods($_type)
		{
			if (is_array($_type))
			{
				$_type = $_type['type'];
			}

			switch($_type)
			{
				case 'xmlrpc':
					$xml_functions = array(
						'list_methods' => array(
							'function'  => 'list_methods',
							'signature' => array(array(xmlrpcStruct,xmlrpcString)),
							'docstring' => lang('Read this list of methods.')
						),
						'update_dla' => array(
							'function'  => 'update_dla',
							'signature' => array(array(xmlrpcBoolean)),
							'docstring' => lang('Returns an array of todo items')
						)
					);
					return $xml_functions;
					break;
				case 'soap':
					return $this->soap_functions;
					break;
				default:
					return array();
					break;
			}
		}

		function split_login_domain($both,&$login,&$domain)
		{
			$parts = explode('@',$both);
			$domain = count($parts) > 1 ? array_pop($parts) :
				$GLOBALS['phpgw_info']['server']['default_domain'];
			$login = implode('@',$parts);
		}

		/**
		* Check to see if a session is still current and valid
		*
		* @param string $sessionid session id to be verfied
		* @param string $kp3 ?? to be verified
		* @return bool is the session valid?
		*/
		function verify($sessionid='',$kp3='')
		{
			if(empty($sessionid) || !$sessionid)
			{
				$sessionid = get_var('sessionid',array('GET','COOKIE'));
				$kp3       = get_var('kp3',array('GET','COOKIE'));
			}

			$this->sessionid = $sessionid;
			$this->kp3       = $kp3;

			$session = $this->read_session();
			//echo "<pre>session::verify(id='$sessionid'): \n".print_r($session,True)."</pre>\n";
			/*
			$fp = fopen('/tmp/session_verify','a+');
			fwrite($fp,"session::verify(id='$sessionid'): \n".print_r($session,True)."\n\n");
			fclose($fp);
			*/
			if ($session['session_dla'] <= (time() - $GLOBALS['phpgw_info']['server']['sessions_timeout']))
			{
				$this->destroy($sessionid,$kp3);
				return False;
			}

			$this->session_flags = $session['session_flags'];

			sessions_::split_login_domain($session['session_lid'],$this->account_lid,$this->account_domain);

			$GLOBALS['phpgw_info']['user']['kp3'] = $this->kp3;

			$this->update_dla();
			if (isset($_SESSION['phpgw_session']['account_id']))
				$this->account_id =  $_SESSION['phpgw_session']['account_id'];
			else
				$this->account_id = $GLOBALS['phpgw']->accounts->name2id($this->account_lid);
			if (!$this->account_id)
			{
				return False;
			}
			$GLOBALS['phpgw_info']['user']['account_id'] = $this->account_id;
			$_SESSION['phpgw_session']['account_id'] = $this->account_id;

			/* init the crypto object */
			$this->_init_crypto();

			$this->read_repositories( isset($GLOBALS['phpgw_info']['server']['cache_phpgw_info'])? $GLOBALS['phpgw_info']['server']['cache_phpgw_info'] : null );
			if (strlen($this->user['expires']) == 0)
				$this->user['expires'] = $_SESSION['phpgw_session']['expires_account'];
			if ($this->user['expires'] != -1 && $this->user['expires'] < time())
			{
				if(is_object($GLOBALS['phpgw']->log))
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'W-VerifySession, account loginid %1 is expired',
						'p1'   => $this->account_lid,
						'line' => __LINE__,
						'file' => __FILE__
					));
					$GLOBALS['phpgw']->log->commit();
				}
				return False;
			}
			$_SESSION['phpgw_session']['expires_account'] = $this->user['expires'];


			$GLOBALS['phpgw_info']['user']  = $this->user;
			$GLOBALS['phpgw_info']['hooks'] = $this->hooks;

			$GLOBALS['phpgw_info']['user']['session_ip'] = $session['session_ip'];
			$GLOBALS['phpgw_info']['user']['passwd']     = base64_decode($this->appsession('password','phpgwapi'));

			if ($this->account_domain != $GLOBALS['phpgw_info']['user']['domain'])
			{
				if(is_object($GLOBALS['phpgw']->log))
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'W-VerifySession, the domains %1 and %2 don\'t match',
						'p1'   => $userid_array[1],
						'p2'   => $GLOBALS['phpgw_info']['user']['domain'],
						'line' => __LINE__,
						'file' => __FILE__
					));
					$GLOBALS['phpgw']->log->commit();
				}
				return False;
			}

			
			$GLOBALS['phpgw']->acl->acl($this->account_id);
			$GLOBALS['phpgw']->accounts->accounts($this->account_id);
			$GLOBALS['phpgw']->preferences->preferences($this->account_id);
			$GLOBALS['phpgw']->applications->applications($this->account_id);

			if (! $this->account_lid)
			{
				if(is_object($GLOBALS['phpgw']->log))
				{
					// This needs some better wording
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'W-VerifySession, account_id is empty',
						'line' => __LINE__,
						'file' => __FILE__
					));
					$GLOBALS['phpgw']->log->commit();
				}
				//echo 'DEBUG: Sessions: account_id is empty!<br />'."\n";
				return False;
			}
			return True;
		}

		/**
		* Functions for creating and verifying the session
		*/
        
		/**
		* Get the ip address of current users
		*
		* @return string ip address
		*/
		function getuser_ip()
		{
			$ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR']."," : "").$_SERVER['REMOTE_ADDR']; 
			if(strlen($ip)>30) {
				$ip_exploded = explode(",",$ip);
				$ip = "";
				for($i=0;$i<2;++$i)
					$ip .= isset($ip_exploded[$i])?(($i==1?",":"").trim($ip_exploded[$i])):("");
				if(strlen($ip)>30)
					$ip = $ip_exploded[0];
			}
			
			return $ip;
		}

		/**
		* Set the domain used for cookies
		*
		* @return string domain
		*/
		function phpgw_set_cookiedomain()
		{
			// Use HTTP_X_FORWARDED_HOST if set, which is the case behind a none-transparent proxy
			//$this->cookie_domain = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?  $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
			//Modificacao feita para que o Expresso redirecione para o primeiro proxy caso haja um encadeamento de mais de um proxy.
			$this->cookie_domain = nearest_to_me();

			// remove port from HTTP_HOST
			if (preg_match("/^(.*):(.*)$/",$this->cookie_domain,$arr))
			{
				$this->cookie_domain = $arr[1];
			}
			if (count(explode('.',$this->cookie_domain)) <= 1)
			{
				// setcookie dont likes domains without dots, leaving it empty, gets setcookie to fill the domain in
				$this->cookie_domain = '';
			}
			print_debug('COOKIE_DOMAIN',$this->cookie_domain,'api');

			$this->set_cookie_params($this->cookie_domain);	// for php4 sessions necessary
		}

		/**
		* Set a cookie
		*
		* @param string $cookiename name of cookie to be set
		* @param string $cookievalue value to be used, if unset cookie is cleared (optional)
		* @param int $cookietime when cookie should expire, 0 for session only (optional)
		*/
		function phpgw_setcookie($cookiename,$cookievalue='',$cookietime=0)
		{
			if (!$this->cookie_domain)
			{
				$this->phpgw_set_cookiedomain();
			}
			setcookie($cookiename,$cookievalue,$cookietime,'/',$this->cookie_domain,null,true);
		}

		/**
		* Create a new session
		*
		* @param string $login user login
		* @param string $passwd user password
		* @param string $passwd_type type of password being used, ie plaintext, md5, sha1
		* @return string session id
		*/
		function create($login,$passwd = '',$passwd_type = '')
		{
			if (is_array($login))
			{
				$this->login       = $login['login'];
				$this->passwd      = $login['passwd'];
				$this->passwd_type = $login['passwd_type'];
				$login             = $this->login;
			}
			else
			{
				$this->login       = $login;
				$this->passwd      = $passwd;
				$this->passwd_type = $passwd_type;
			}

			$this->clean_sessions();
			//sessions_::split_login_domain($login,$this->account_lid,$this->account_domain);
			// jakjr: allow uid with (@);
			$this->account_lid = $login;
			$this->account_domain = 'default';

			$now = time();

			//echo "<p>session::create(login='$login'): lid='$this->account_lid', domain='$this->account_domain'</p>\n";
			$user_ip = $this->getuser_ip();
				
			$this->account_id = $GLOBALS['phpgw']->accounts->name2id($this->account_lid);
			
			if (
				(
					isset($GLOBALS['phpgw_info']['server']['global_denied_users'][$this->account_lid]) &&
					$GLOBALS['phpgw_info']['server']['global_denied_users'][$this->account_lid]
				) ||
				( $blocked = $this->login_blocked( $login, $user_ip ) ) ||
				( !$GLOBALS['phpgw']->auth->authenticate( $this->account_lid, $this->passwd, $this->passwd_type ) ) ||
				( $this->account_id && $GLOBALS['phpgw']->accounts->get_type( $this->account_id ) == 'g' )
			)
			{
				$blocked = isset($blocked)? $blocked : false;
				$this->reason = $blocked ? 'blocked, too many attempts' : 'bad login or password';
				$this->cd_reason = $blocked ? 99 : 5;
				$this->log_access( $this->reason, $login, $user_ip, 0 );	// log unsuccessfull login
				return false;
			}
			
			// S� verifica tempo de inatividade do usu�rio, caso esteja configurado no Administrador.
			if (
				isset($GLOBALS['phpgw_info']['server']['time_to_account_expires']) &&
				$this->account_id != null &&
				$this->account_lid != 'expresso-admin'
			)
			{
				$last_access = $this->get_last_access_on_history($this->account_id);
				$this->read_repositories(false);
				if ( $last_access && ( $last_access + ( $GLOBALS['phpgw_info']['server']['time_to_account_expires'] * 86400 ) < time() ) )
				{
					if ( isset($GLOBALS['phpgw']->log) && is_object($GLOBALS['phpgw']->log) )
					{
						$GLOBALS['phpgw']->log->message( array(
							'text' => 'W-LoginFailure, account loginid %1 is expired for innativity',
							'p1'   => $this->account_lid,
							'line' => __LINE__,
							'file' => __FILE__
						) );
						$GLOBALS['phpgw']->log->commit();
					}
					$this->reason = 'account is expired';
					$this->cd_reason = 98;
					
					return false;
				}
			}

			/* jakjr: Expresso does not use auto-create account.
			if (!$this->account_id && $GLOBALS['phpgw_info']['server']['auto_create_acct'] == True)
			{
				$this->account_id = $GLOBALS['phpgw']->accounts->auto_add($this->account_lid, $passwd);
			}
			*/

			$GLOBALS['phpgw_info']['user']['account_id'] = $this->account_id;
			$GLOBALS['phpgw']->accounts->accounts( $this->account_id );
			$this->sessionid = $this->new_session_id();
			$this->kp3       = md5($GLOBALS['phpgw']->common->randomstring(15));

			//Carregando na sess�o configura��es do usuario usado na nova API.
			$accountInfo = $GLOBALS['phpgw']->accounts->read_repository();
			$_SESSION['wallet']['user']['uid']            = $this->account_lid;
			$_SESSION['wallet']['user']['uidNumber']      = $this->account_id;
			$_SESSION['wallet']['user']['password']       = $this->passwd;
			$_SESSION['wallet']['user']['cn']             = $accountInfo['firstname'].' '.$accountInfo['lastname'];
			$_SESSION['wallet']['user']['mail']           = $accountInfo['email'];
			$_SESSION['wallet']['user']['password']       = $this->passwd;
			$_SESSION['wallet']['Sieve']['user']          = $this->account_lid;
			$_SESSION['wallet']['Sieve']['password']      = $this->passwd;
			$_SESSION['wallet']['Cyrus']['user']          = $this->account_lid;
			$_SESSION['wallet']['Cyrus']['password']      = $this->passwd;
			
			if ( $GLOBALS['phpgw_info']['server']['usecookies'] )
			{
				$this->phpgw_setcookie( 'sessionid', $this->sessionid );
				$this->phpgw_setcookie( 'kp3', $this->kp3 );
				$this->phpgw_setcookie( 'domain', $this->account_domain );
			}
			
			if ($GLOBALS['phpgw_info']['server']['usecookies'] || isset($_COOKIE['last_loginid']))
			{
				$this->phpgw_setcookie('last_loginid', $this->account_lid ,$now+1209600); /* For 2 weeks */
				$this->phpgw_setcookie('last_domain',$this->account_domain,$now+1209600);
				$this->phpgw_setcookie('last_organization',(isset($_POST['organization'])?$_POST['organization']:""),$now+1209600);
			}
			unset($GLOBALS['phpgw_info']['server']['default_domain']); /* we kill this for security reasons */

			/* init the crypto object */
			$this->_init_crypto();

			$this->read_repositories(False);
			if ($this->user['expires'] != -1 && $this->user['expires'] < time())
			{
				if(is_object($GLOBALS['phpgw']->log))
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'W-LoginFailure, account loginid %1 is expired',
						'p1'   => $this->account_lid,
						'line' => __LINE__,
						'file' => __FILE__
					));
					$GLOBALS['phpgw']->log->commit();
				}
				$this->reason = 'account is expired';
				$this->cd_reason = 98;

				return False;
			}
			$GLOBALS['phpgw_info']['user']  = $this->user;
			$GLOBALS['phpgw_info']['hooks'] = $this->hooks;

			$this->appsession('password','phpgwapi',base64_encode($this->passwd));
			if ($GLOBALS['phpgw']->acl->check('anonymous',1,'phpgwapi'))
			{
				$session_flags = 'A';
			}
			else
			{
				$session_flags = 'N';
			}

			$GLOBALS['phpgw']->db->transaction_begin();
			$this->register_session($login,$user_ip,$now,$session_flags);
			if ($session_flags != 'A')		// dont log anonymous sessions
			{
				$this->log_access($this->sessionid,$login,$user_ip,$this->account_id);
			}
			$this->appsession('account_previous_login','phpgwapi',$GLOBALS['phpgw']->auth->previous_login);
			// Expresso
			//$GLOBALS['phpgw']->auth->update_lastlogin($this->account_id,$user_ip);
			$GLOBALS['phpgw']->db->transaction_commit();

			//if (!$this->sessionid) echo "<p>session::create(login='$login') = '$this->sessionid': lid='$this->account_lid', domain='$this->account_domain'</p>\n";

			return $this->sessionid;
		}

		/**
		 * Retorna o UNIX DATE do ultimo acesso dessa conta, baseado na tabela de hist�rico.
		 */
		function get_last_access_on_history($account_id) {
			
			$GLOBALS['phpgw']->db->query("select li from phpgw_access_log where account_id='$account_id' order by li desc limit 1",__LINE__,__FILE__);
			if(!$GLOBALS['phpgw']->db->next_record())
				return false;
			return $GLOBALS['phpgw']->db->f('li');
		}

		/**
        * Write or update (for logout) the access_log
		*
		* @param string $sessionid id of session or 0 for unsuccessful logins
		* @param string $login account_lid (evtl. with domain) or '' for settion the logout-time
		* @param string $user_ip ip to log
		* @param int $account_id numerical account_id
		*/
		function log_access($sessionid,$login='',$user_ip='',$account_id='')
		{
			$now = time();

			if ($login != '')
			{
				if (strlen($login) > 30)
				{
					$login = substr($login,0,30);
				}
				$GLOBALS['phpgw']->db->query('INSERT INTO phpgw_access_log(sessionid,loginid,ip,li,lo,account_id,browser)'
					. " VALUES ('" . pg_escape_string($sessionid) . "','" . pg_escape_string($login). "','"
					. pg_escape_string($user_ip) . "',$now,0," .  (int)$account_id .",'".pg_escape_string(substr($_SERVER[ 'HTTP_USER_AGENT' ],0,199))."')",__LINE__,__FILE__);
			}
			else if($sessionid != 'bad login or password')
			{
				$GLOBALS['phpgw']->db->query("UPDATE phpgw_access_log SET lo=" . $now . " WHERE sessionid='"
					. pg_escape_string($sessionid) . "'",__LINE__,__FILE__);
			}

			/* jakjr: Clean phpgw_access_log with a crontab event.
			if ($GLOBALS['phpgw_info']['server']['max_access_log_age'])
			{
				$max_age = $now - $GLOBALS['phpgw_info']['server']['max_access_log_age'] * 24 * 60 * 60;

				$GLOBALS['phpgw']->db->query("DELETE FROM phpgw_access_log WHERE li < $max_age");
			}
			*/
		}

		/**
		* Protect against brute force attacks, block login if too many unsuccessful login attmepts
        *
		* @param string $login account_lid (evtl. with domain)
		* @param string $ip ip of the user
		* @returns bool login blocked?
		*/
		function login_blocked( $login, $ip )
		{
			$blocked   = false;
			$blk_min   = isset($GLOBALS['phpgw_info']['server']['block_time'])? (int)$GLOBALS['phpgw_info']['server']['block_time'] : 30;
			$blk_time  = isset($GLOBALS['phpgw_info']['server']['login_blocked_mail_time'])? (int)$GLOBALS['phpgw_info']['server']['login_blocked_mail_time'] : 0;
			$num_fail  = isset($GLOBALS['phpgw_info']['server']['num_unsuccessful_id'])? (int)$GLOBALS['phpgw_info']['server']['num_unsuccessful_id'] : 3;
			$adm_mails = isset($GLOBALS['phpgw_info']['server']['admin_mails'])? $GLOBALS['phpgw_info']['server']['admin_mails'] : '';
			$domain    = isset($GLOBALS['phpgw_info']['server']['mail_suffix'])? $GLOBALS['phpgw_info']['server']['mail_suffix'] : 'localhost';
			
			$block_time = time() - $blk_min * 60;
			
			$login = pg_escape_string( $login );
			$this->db->query(
				'SELECT count(*) '.
				'FROM phpgw_access_log '.
				'WHERE account_id = 0 AND ( loginid = \''.$login.'\' OR loginid LIKE \''.$login.'@%\' ) AND li > '.$block_time,
				__LINE__,__FILE__
			);
			$this->db->next_record();
			
			if ( ( $false_id = $this->db->f(0) ) > $num_fail )
			{
				$blocked = true;
			}
			
			if ( $blocked && !empty($adm_mails) && $blk_time < ( time() - 5 * 60 ) ) // max. one mail each 5 mins
			{
				// notify admin(s) via email
				if ( !( isset($GLOBALS['phpgw']->send) && is_object($GLOBALS['phpgw']->send) ) )
				{
					$GLOBALS['phpgw']->send = CreateObject('phpgwapi.send');
				}
				
				$from    = 'eGroupWare@'.$domain;
				$body    = lang( 'Too many unsucessful attempts to login: %1 for the user \'%2\', %3 for the IP %4', $false_id, $login, $false_ip, $ip );
				$subject = $GLOBALS['phpgw']->send->encode_subject( lang( 'eGroupWare: login blocked for user \'%1\', IP %2', $login, $ip ) );
				
				foreach ( explode( ',', $adm_mails ) as $to )
				{
					$GLOBALS['phpgw']->send->msg( 'email', $to, $subject, $body, '', '', '', $from, $from );
				}
				
				// save time of mail, to not send to many mails
				$config = CreateObject( 'phpgwapi.config', 'phpgwapi' );
				$config->read_repository();
				$config->value( 'login_blocked_mail_time', time() );
				$config->save_repository();
			}
			return $blocked;
		}

		/**
		* Verfy a peer server access request
		*
		* @param string $sessionid session id to verfiy
		* @param string $kp3 ??
		* @return bool verfied?
		*/
		function verify_server($sessionid, $kp3)
		{
			$GLOBALS['phpgw']->interserver = CreateObject('phpgwapi.interserver');
			$this->sessionid = $sessionid;
			$this->kp3       = $kp3;

			$session = $this->read_session();
			$this->session_flags = $session['session_flags'];

			list($this->account_lid,$this->account_domain) = explode('@', $session['session_lid']);
			
			if ($this->account_domain == '')
			{
				$this->account_domain = $GLOBALS['phpgw_info']['server']['default_domain'];
			}

			$GLOBALS['phpgw_info']['user']['kp3'] = $this->kp3;
			$phpgw_info_flags = $GLOBALS['phpgw_info']['flags'];

			$GLOBALS['phpgw_info']['flags'] = $phpgw_info_flags;

			$this->update_dla();
			$this->account_id = $GLOBALS['phpgw']->interserver->name2id($this->account_lid);

			if (!$this->account_id)
			{
				return False;
			}

			$GLOBALS['phpgw_info']['user']['account_id'] = $this->account_id;

			$this->read_repositories(@$GLOBALS['phpgw_info']['server']['cache_phpgw_info']);

			/* init the crypto object */
			$this->_init_crypto();

			$GLOBALS['phpgw_info']['user']  = $this->user;
			$GLOBALS['phpgw_info']['hooks'] = $this->hooks;

			$GLOBALS['phpgw_info']['user']['session_ip'] = $session['session_ip'];
			$GLOBALS['phpgw_info']['user']['passwd'] = base64_decode($this->appsession('password','phpgwapi'));

			if ($userid_array[1] != $GLOBALS['phpgw_info']['user']['domain'])
			{
				if(is_object($GLOBALS['phpgw']->log))
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'W-VerifySession, the domains %1 and %2 don\t match',
						'p1'   => $userid_array[1],
						'p2'   => $GLOBALS['phpgw_info']['user']['domain'],
						'line' => __LINE__,
						'file' => __FILE__
					));
					$GLOBALS['phpgw']->log->commit();
				}

				if(is_object($GLOBALS['phpgw']->crypto))
				{
					$GLOBALS['phpgw']->crypto->cleanup();
					unset($GLOBALS['phpgw']->crypto);
				}
				return False;
			}

			if(@$GLOBALS['phpgw_info']['server']['sessions_checkip'])
			{
				if((PHP_OS != 'Windows') && (PHP_OS != 'WINNT') &&
					(!$GLOBALS['phpgw_info']['user']['session_ip'] || $GLOBALS['phpgw_info']['user']['session_ip'] != $this->getuser_ip())
				)
				{
					if(is_object($GLOBALS['phpgw']->log))
					{
						// This needs some better wording
						$GLOBALS['phpgw']->log->message(array(
							'text' => 'W-VerifySession, IP %1 doesn\'t match IP %2 in session table',
							'p1'   => $this->getuser_ip(),
							'p2'   => $GLOBALS['phpgw_info']['user']['session_ip'],
							'line' => __LINE__,
							'file' => __FILE__
						));
						$GLOBALS['phpgw']->log->commit();
					}

					if(is_object($GLOBALS['phpgw']->crypto))
					{
						$GLOBALS['phpgw']->crypto->cleanup();
						unset($GLOBALS['phpgw']->crypto);
					}
					return False;
				}
			}

			$GLOBALS['phpgw']->acl->acl($this->account_id);
			$GLOBALS['phpgw']->accounts->accounts($this->account_id);
			$GLOBALS['phpgw']->preferences->preferences($this->account_id);
			$GLOBALS['phpgw']->applications->applications($this->account_id);

			if (! $this->account_lid)
			{
				if(is_object($GLOBALS['phpgw']->log))
				{
					// This needs some better wording
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'W-VerifySession, account_id is empty',
						'line' => __LINE__,
						'file' => __FILE__
					));
					$GLOBALS['phpgw']->log->commit();
				}

				if(is_object($GLOBALS['phpgw']->crypto))
				{
					$GLOBALS['phpgw']->crypto->cleanup();
					unset($GLOBALS['phpgw']->crypto);
				}
				return False;
			}
			else
			{
				return True;
			}
		}

		/**
		* Validate a peer server login request
		*
		* @param string $login login name
		* @param string $password password
		* @return bool login ok?
		*/
		function create_server($login,$passwd)
		{
			$GLOBALS['phpgw']->interserver = CreateObject('phpgwapi.interserver');
			$this->login  = $login;
			$this->passwd = $passwd;
			$this->clean_sessions();
			$login_array = explode('@', $login);
			$this->account_lid = $login_array[0];
			$now = time();

			if ($login_array[1] != '')
			{
				$this->account_domain = $login_array[1];
			}
			else
			{
				$this->account_domain = $GLOBALS['phpgw_info']['server']['default_domain'];
			}

			$serverdata = array(
				'server_name' => $this->account_domain,
				'username'    => $this->account_lid,
				'password'    => $passwd
			);
			if (!$GLOBALS['phpgw']->interserver->auth($serverdata))
			{
				return False;
				exit;
			}

			if (!$GLOBALS['phpgw']->interserver->exists($this->account_lid))
			{
				$this->account_id = $GLOBALS['phpgw']->interserver->name2id($this->account_lid);
			}
			$GLOBALS['phpgw_info']['user']['account_id'] = $this->account_id;
			$GLOBALS['phpgw']->interserver->serverid = $this->account_id;

			$this->sessionid = md5($GLOBALS['phpgw']->common->randomstring(10));
			$this->kp3       = md5($GLOBALS['phpgw']->common->randomstring(15));

			/* re-init the crypto object */
			$this->_init_crypto();

			//$this->read_repositories(False);

			$GLOBALS['phpgw_info']['user']  = $this->user;
			$GLOBALS['phpgw_info']['hooks'] = $this->hooks;

			$this->appsession('password','phpgwapi',base64_encode($this->passwd));
			$session_flags = 'S';

			$user_ip = $this->getuser_ip();

			$GLOBALS['phpgw']->db->transaction_begin();
			$this->register_session($login,$user_ip,$now,$session_flags);

			$this->log_access($this->sessionid,$login,$user_ip,$this->account_id);

			$this->appsession('account_previous_login','phpgwapi',$GLOBALS['phpgw']->auth->previous_login);
			$GLOBALS['phpgw']->auth->update_lastlogin($this->account_id,$user_ip);
			$GLOBALS['phpgw']->db->transaction_commit();

			return array($this->sessionid,$this->kp3);
		}

		/**
		* Functions for appsession data and session cache
		*/

		/**
		* Is this also useless?? (skwashd)
		*/
		function read_repositories($cached='',$write_cache=True)
		{
			$GLOBALS['phpgw']->acl->acl($this->account_id);
			$GLOBALS['phpgw']->accounts->accounts($this->account_id);
			$GLOBALS['phpgw']->preferences->preferences($this->account_id);
			$GLOBALS['phpgw']->applications->applications($this->account_id);

			if(@$cached)
			{
				$this->user = $this->appsession('phpgw_info_cache','phpgwapi');
				if(!empty($this->user))
				{
					$GLOBALS['phpgw']->preferences->data = $this->user['preferences'];
					if (!isset($GLOBALS['phpgw_info']['apps']) || !is_array($GLOBALS['phpgw_info']['apps']))
					{
						$GLOBALS['phpgw']->applications->read_installed_apps();
					}
				}
				else
				{
					$this->setup_cache($write_cache);
				}
			}
			else
			{
				$this->setup_cache($write_cache);
			}
			$this->hooks = $GLOBALS['phpgw']->hooks->read();
		}

		/**
		* Is this also useless?? (skwashd)
		*/
		function setup_cache($write_cache=True)
		{
			$this->user                = $GLOBALS['phpgw']->accounts->read_repository();
			$this->user['acl']         = $GLOBALS['phpgw']->acl->read_repository();
			$this->user['preferences'] = $GLOBALS['phpgw']->preferences->read_repository();
			$this->user['apps']        = $GLOBALS['phpgw']->applications->read_repository();
			//@reset($this->data['user']['apps']);

			$this->user['domain']      = $this->account_domain;
			$this->user['sessionid']   = $this->sessionid;
			$this->user['kp3']         = $this->kp3;
			$this->user['session_ip']  = $this->getuser_ip();
			$this->user['session_lid'] = $this->account_lid.'@'.$this->account_domain;
			$this->user['account_id']  = $this->account_id;
			$this->user['account_lid'] = $this->account_lid;
			$this->user['userid']      = $this->account_lid;
			$this->user['passwd']      = @$this->passwd;
			if(@$GLOBALS['phpgw_info']['server']['cache_phpgw_info'] && $write_cache)
			{
				$this->delete_cache();
				$this->appsession('phpgw_info_cache','phpgwapi',$this->user);
			}
		}
        
		/**
		* This looks to be useless
		* This will capture everything in the $GLOBALS['phpgw_info'] including server info,
		* and store it in appsessions.  This is really incompatible with any type of restoring
		* from appsession as the saved user info is really in ['user'] rather than the root of
		* the structure, which is what this class likes.
		*/
		function save_repositories()
		{
			$phpgw_info_temp = $GLOBALS['phpgw_info'];
			$phpgw_info_temp['user']['kp3'] = '';
			$phpgw_info_temp['flags'] = array();

			if ($GLOBALS['phpgw_info']['server']['cache_phpgw_info'])
			{
				$this->appsession('phpgw_info_cache','phpgwapi',$phpgw_info_temp);
			}
		}

		function restore()
		{
			$sessionData = $this->appsession('sessiondata');

			if (!empty($sessionData) && is_array($sessionData))
			{
				foreach($sessionData as $key => $value)
				{
					global $$key;
					$$key = $value;
					$this->variableNames[$key] = 'registered';
					// echo 'restored: '.$key.', ' . $value . '<br />';
				}
			}
		}

		/**
		* Save the current values of all registered variables
		*/
		function save()
		{
			if (is_array($this->variableNames))
			{
				reset($this->variableNames);
				while(list($key, $value) = each($this->variableNames))
				{
					if ($value == 'registered')
					{
						global $$key;
						$sessionData[$key] = $$key;
					}
				}
				$this->appsession('sessiondata','',$sessionData);
			}
		}

		/**
		* Create a list a variable names, which data needs to be restored
		*
		* @param string $_variableName name of variable to be registered
		*/
		function register($_variableName)
		{
			$this->variableNames[$_variableName]='registered';
			#print 'registered '.$_variableName.'<br />';
		}

		/**
		* Mark variable as unregistered
		*
		* @param string $_variableName name of variable to deregister
		*/
		function unregister($_variableName)
		{
			$this->variableNames[$_variableName]='unregistered';
			#print 'unregistered '.$_variableName.'<br />';
		}

		/**
		* Check if we have a variable registred already
		*
		* @param string $_variableName name of variable to check
		* @return bool was the variable found?
		*/
		function is_registered($_variableName)
		{
			if ($this->variableNames[$_variableName] == 'registered')
			{
				return True;
			}
			else
			{
				return False;
			}
		}
		/**
		* Additional tracking of user actions - prevents reposts/use of back button
		*
		* @author skwashd
		* @return string current history id
		*/
		function generate_click_history()
		{
			if(!isset($this->history_id))
			{
				$this->history_id = md5($this->login . time());
				$history = $this->appsession($location = 'history', $appname = 'phpgwapi');
				
				if(count($history) >= $GLOBALS['phpgw_info']['server']['max_history'])
				{
					array_shift($history);
					$this->appsession($location = 'history', $appname = 'phpgwapi', $history);
				}
			}
			return $this->history_id;
		}
		
		/**
		* Detects if the page has already been called before - good for forms
		*
		* @author skwashd
		* @param bool $diplay_error when implemented will use the generic error handling code
		* @return True if called previously, else False - call ok
		*/
		function is_repost($display_error = False)
		{
			$history = $this->appsession($location = 'history', $appname = 'phpgwapi');
			if(isset($history[$_GET['click_history']]))
			{
				if($display_error)
				{
					$GLOBALS['phpgw']->redirect_link('/error.php', 'type=repost');//more on this later :)
				}
				else
				{
					return True; //handled by the app
				}
			}
			else
			{
				$history[$_GET['click_history']] = True;
				$this->appsession($location = 'history', $appname = 'phpgwapi', $history);
				return False;
			}
		}

		/**
		* Generate a url which supports url or cookies based sessions
		*
		* @param string $url a url relative to the egroupware install root
		* @param array $extravars query string arguements
		* @return string generated url
		*/
		function link($url, $extravars = '')
		{
			//echo "<p>session::link(url='".print_r($url,True)."',extravars='".print_r($extravars,True)."')";
			/* first we process the $url to build the full scriptname */
			$full_scriptname = True;

			$url_firstchar = substr($url ,0,1);
			if ($url_firstchar == '/' && $GLOBALS['phpgw_info']['server']['webserver_url'] == '/')
			{
				$full_scriptname = False;
			}

			if ($url_firstchar != '/')
			{
				$app = $GLOBALS['phpgw_info']['flags']['currentapp'];
				if ($app != 'home' && $app != 'login' && $app != 'logout')
				{
					$url = $app.'/'.$url;
				}
			}

			if($full_scriptname)
			{
				$webserver_url_count = strlen($GLOBALS['phpgw_info']['server']['webserver_url'])-1;
				if(substr($GLOBALS['phpgw_info']['server']['webserver_url'] ,$webserver_url_count,1) != '/' && $url_firstchar != '/')
				{
					$url = $GLOBALS['phpgw_info']['server']['webserver_url'] .'/'. $url;
				}
				else
				{
					$url = $GLOBALS['phpgw_info']['server']['webserver_url'] . $url;
				}
			}

			if(@isset($GLOBALS['phpgw_info']['server']['enforce_ssl']) && $GLOBALS['phpgw_info']['server']['enforce_ssl']) // && !$_SERVER['HTTPS']) imho https should always be a full path - skwashd
			{
				if(substr($url ,0,4) != 'http')
				{
					$url = 'https://'.$GLOBALS['phpgw_info']['server']['hostname'].$url;
				}
				else
				{
					$url = str_replace ( 'http:', 'https:', $url);
				}
			}

			/* Now we process the extravars into a proper url format */
			/* if its not an array, then we turn it into one */
			/* We do this to help prevent any duplicates from being sent. */
			if (!is_array($extravars) && $extravars != '')
			{
				$new_extravars = Array();

				$a = explode('&', $extravars);
				$i = 0;
				while ($i < count($a))
				{
					$b = preg_split('/=/', $a[$i],2);
					// Check if this value doesn't already exist in new_extravars
					if(array_key_exists($b[0], $new_extravars))
					{
						// print "Debug::Error !!! " . $b[0] . " ($i) already exists<br />";
						if( preg_match('/\[\]/i', $b[0]) )
						{
							$b[0] = preg_replace('/\[\]/i', "[$i]", $b[0]);
						}
					}

					$new_extravars[$b[0]] = $b[1];
					++$i;
				}
				$extravars = $new_extravars;
				unset($new_extravars);
			}

			/* if using frames we make sure there is a framepart */
			if(@defined('PHPGW_USE_FRAMES') && PHPGW_USE_FRAMES)
			{
				if (!isset($extravars['framepart']))
				{
					$extravars['framepart']='body';
				}
			}

			/* add session params if not using cookies */
			if (@!$GLOBALS['phpgw_info']['server']['usecookies'])
			{
				$extravars['sessionid'] = $this->sessionid;
				$extravars['kp3'] = $this->kp3;
				$extravars['domain'] = $this->account_domain;
			}

			//used for repost prevention
//			$extravars['click_history'] = $this->generate_click_history();

			/* if we end up with any extravars then we generate the url friendly string */
			if (is_array($extravars))
			{
				$new_extravars = '';
				foreach($extravars as $key => $value)
				{
					if (!empty($new_extravars))
					{
						$new_extravars .= '&';
					}
					$new_extravars .= $key.'='.urlencode($value);
				}
				$url .= '?' . $new_extravars;
			}
			//echo " = '$url'</p>\n";
			return $url;
		}

		/**
		* The remaining methods are abstract - as they are unique for each session handler
		*/

		/**
		* Load user's session information
		*
		* The sessionid of the session to read is passed in the class-var $this->sessionid
		*
		* @return mixed the session data
		*/
		function read_session()
		{}

		/**
		* Remove stale sessions out of the database
		*/
		function clean_sessions()
		{}

		/**
		* Set paramaters for cookies - only implemented in PHP4 sessions
		*
		* @param string $domain domain name to use in cookie
		*/

		function set_cookie_params($domain)
		{}

		/**
		* Create a new session id
		*
		* @return string a new session id
		*/
		function new_session_id()
		{}

		/**
		* Create a new session
		*
		* @param string $login user login
		* @param string $user_ip users ip address
		* @param int $now time now as a unix timestamp
		* @param string $session_flags A = Anonymous, N = Normal
		*/
		function register_session($login,$user_ip,$now,$session_flags)
		{}

		/**
		* Update the date last active info for the session, so the login does not expire
		*
		* @return bool did it suceed?
		*/
		function update_dla()
		{}

		/**
		* Terminate a session
		*
		* @param string $sessionid the id of the session to be terminated
		* @param string $kp3 - NOT SURE
		* @return bool did it suceed?
		*/
		function destroy($sessionid, $kp3)
		{}

		/**
		* Functions for appsession data and session cache
		*/
        
		/**
		* Delete all data from the session cache for a user
		*
		* @param int $accountid user account id, defaults to current user (optional)
		*/
		function delete_cache($accountid='')
		{}

		/**
		* Stores or retrieves information from the sessions cache
		*
		* @param string $location identifier for data
		* @param string $appname name of app which is responsbile for the data
		* @param mixed $data data to be stored, if left blank data is retreived (optional)
		* @return mixed data from cache, only returned if $data arg is not used
		*/
		function appsession($location = 'default', $appname = '', $data = '##NOTHING##')
		{}

		/**
		* Get list of normal / non-anonymous sessions
		* Note: The data from the session-files get cached in the app_session phpgwapi/php4_session_cache
		*
		* @author ralfbecker
		* @param int $start session to start at
		* @param string $order field to sort on
		* @param string $sort sort order
		* @param bool $all_no_sort list all with out sorting (optional) default False
		* @return array info for all current sessions
		*/
		function list_sessions($start,$order,$sort,$all_no_sort = False)
		{}
		
		/**
		* Get the number of normal / non-anonymous sessions
		* 
		* @author ralfbecker
		* @return int number of sessions
		*/
		function total()
		{}
	}

	if(empty($GLOBALS['phpgw_info']['server']['sessions_type']))
	{
		$GLOBALS['phpgw_info']['server']['sessions_type'] = 'php4';	// the more performant default
	}
	// for php4 sessions, check if the extension is loaded, try loading it and fallback to db sessions if not
	if ($GLOBALS['phpgw_info']['server']['sessions_type'] == 'php4' && !extension_loaded('session'))
	{
		// some constanst for pre php4.3
		if (!defined('PHP_SHLIB_SUFFIX'))
		{
			define('PHP_SHLIB_SUFFIX',strtoupper(substr(PHP_OS, 0,3)) == 'WIN' ? 'dll' : 'so');
		}
		if (!defined('PHP_SHLIB_PREFIX'))
		{
			define('PHP_SHLIB_PREFIX',PHP_SHLIB_SUFFIX == 'dll' ? 'php_' : '');
		}
		if (!function_exists('dl') || !@dl(PHP_SHLIB_PREFIX.'session'.'.'.PHP_SHLIB_SUFFIX))
		{
			$GLOBALS['phpgw_info']['server']['sessions_type'] = 'db';	// fallback if we have no php4 sessions support
		}
	}
	include_once(PHPGW_API_INC.'/class.sessions_'.$GLOBALS['phpgw_info']['server']['sessions_type'].'.inc.php');
