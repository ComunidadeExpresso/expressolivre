<?php
/**************************************************************************\
* eGroupWare - Setup                                                       *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

class Config {
	
	protected static $_instance = null;
	protected $_action = null;
	protected $_tb_config = null;
	protected $_db = null;
	protected $_cur_vars = null;
	protected $_new_vars = array();
	protected $_old_vars = array();
	protected $_err_vars = array();
	protected $_val_section = array();
	protected $_tpl_root = null;
	
	public function __construct() {
		$GLOBALS['phpgw_info']['flags'] = array(
				'noapi'			=> true,
				'noheader'		=> true,
				'nonavbar'		=> true,
				'currentapp'	=> 'home',
		);
		include( './inc/functions.inc.php' );
	}
	
	public static function getInstance() {
		if ( null === self::$_instance ) self::$_instance = new self();
		return self::$_instance;
	}
	
	public function run() {
		
		if ( $this->_getAction() === 'cancel' ) $this->_redirect( 'index.php' );
		
		$this->_getDB();
		$GLOBALS['phpgw_setup']->hook('config','setup');
		
		$this->_loadCurrentVars();
		
		if ( $this->_getAction() === 'submit' && $this->_validate() ) $this->_save();
		
		$this->_render();
	}
	
	private function _getAction() {
		if ( $this->_action === null ) {
			// Authorize the user to use setup app and load the database
			// Does not return unless user is authorized
			$this->_action = ( (!$GLOBALS['phpgw_setup']->auth( 'Config' )) || isset($_POST['cancel']) )? 'cancel' : ( isset($_POST['submit'])? 'submit' : 'view' );
		}
		return $this->_action;
	}
	
	private function _getTplRoot() {
		if ( $this->_tpl_root === null )
			$this->_tpl_root = $GLOBALS['phpgw_setup']->html->setup_tpl_dir( 'setup' );
		return $this->_tpl_root;
	}
	
	private function _redirect( $url ) {
		header( 'Location: '.$url );
		exit;
	}
	
	// test if $path lies within the webservers document-root
	private function _not_in_docroot( $path ) {
		
		foreach ( array( PHPGW_SERVER_ROOT, $_SERVER['DOCUMENT_ROOT'] ) as $docroot )
		{
			$len = strlen( $docroot );
			
			if ( $docroot == substr( $path, 0, $len ) )
			{
				$rest = substr( $path, $len );
				
				if ( !strlen($rest) || $rest[0] == DIRECTORY_SEPARATOR ) return false;
			}
		}
		return true;
	}
	
	private function _getDB() {
		if ( $this->_db === null ) {
			$GLOBALS['phpgw_setup']->loaddb();
			$this->_db = $GLOBALS['phpgw_setup']->db;
		}
		return $this->_db;
	}
	
	public function getVar( $name ) {
		return ( isset( $this->_new_vars[$name] ) && !empty( $this->_new_vars[$name] ) )? $this->_new_vars[$name] : (
			isset( $this->_cur_vars[$name] )? $this->_cur_vars[$name] : null
		);
	}
	
	public function hasErr( $name ) {
		return isset( $this->_err_vars[$name] ) && $this->_err_vars[$name];
	}
	
	public function is_equal( $objA, $objB ) {
		return $objA === $objB;
	}
	
	public function is_not_equal( $objA, $objB ) {
		return !$this->is_equal($objA, $objB);
	}
	
	private function _loadCurrentVars() {
		if ( $this->_cur_vars === null ) {
			$this->_cur_vars = array();
			$this->_getDB()->query('SELECT * FROM '.$this->_getTableConfig().' where config_app=\'phpgwapi\'');
			while ( $this->_getDB()->next_record() ) $this->_cur_vars[$this->_getDB()->f('config_name')] = $this->_getDB()->f('config_value');
			$GLOBALS['current_config'] = $this->_cur_vars;
		}
		return $this;
	}
	
	private function _getTableConfig() {
		if ( $this->_tb_config === null ) {
			$this->_getDB();
			// Check api version, use correct table
			$setup_info = $GLOBALS['phpgw_setup']->detection->get_db_versions();
			$this->_tb_config = $GLOBALS['phpgw_setup']->alessthanb( $setup_info['phpgwapi']['currentver'], '0.9.10pre7' )? 'config' : 'phpgw_config';
		}
		return $this->_tb_config;
	}
	
	private function _validate() {
		$section = (func_num_args() > 0)? current( array_slice( func_get_args(), -1 ) ) : 'global';
		if ( !( isset($_POST['newsettings']) && method_exists( $this, '_conf_'.$section ) ) ) return false;
		if ( isset($this->_val_section[$section]) ) return $this->_val_section[$section];
		
		$result = true;
		foreach ( (array)$this->{'_conf_'.$section}() as $param => $conf ) {
			
			$value = isset($_POST['newsettings'][$param])? $_POST['newsettings'][$param] : '';
			if ( isset($this->_cur_vars[$param]) ) $this->_old_vars[$param] = $this->_cur_vars[$param];
			$this->_err_vars[$param] = !$this->_parse( $conf, $param, $value );
			if ( $this->hasErr( $param ) ) $result = false;
			
		}
		$this->_val_section[$section] = $result;
		return $result;
	}
	
	private function _parse( $conf, $param, $value ) {
		$req = isset($conf['required']) && $conf['required'] === true;
		
		$this->_new_vars[$param] = $value;
		if ( empty($value) && $req ) return false;
		
		if ( isset($conf['filters']) ) {
			foreach ( (array)$conf['filters'] as $method ) {
				$result = @$this->_exec( $method, $value );
				if ( is_string($result) ) $value = $result;
				else return false;
			}
		}
		
		if ( empty($value) && $req ) return false;
		
		if ( isset($conf['attributes']) ) {
			foreach ( (array)$conf['attributes'] as $method ) {
				if ( @$this->_exec( $method, $value ) === false ) return false;
			}
		}
		$this->_new_vars[$param] = $value;
		return true;
	}
	
	private function _exec( $method, $params ) {
		$params = (array)$params;
		$ignore_dep = false;
		if ( is_array($method) ) {
			if ( isset($method['if']) ) {
				if ( $this->_test( $method['if'], $params, $result ) === false ) return $result;
				unset( $method['if'] );
			}
			if ( isset( $method['dep_off']) ) {
				$ignore_dep = $method['dep_off'];
				unset( $method['dep_off']);
			}
			$params = array_merge( $params, array_splice( $method, 1 ) );
			$method = array_shift( $method );
		}
		$result = false;
		if ( method_exists( $this, $method ) ) $result = call_user_func_array( [ $this , $method ], $params );
		else if ( function_exists( $method ) ) $result = call_user_func_array( $method, $params );
		
		//Zend_Debug::dump($method.'( '.stripslashes(json_encode($params)).' ) '.str_replace(PHP_EOL,'',strip_tags(Zend_Debug::dump($ignore_dep? true : $result,'result',false))));
		return $ignore_dep? true : $result;
	}
	
	private function _test( $tests, $params, &$result ) {
		foreach ( (array)$tests as $test ) {
			if ( $this->_exec( $test['method'], $params ) === false ) {
				$result = ( $test['return'] === 'value' )? current($params) : $test['return'];
				return false;
			}
		}
		return true;
	}
	
	private function _testLdapConnect( $param ) {
		$conn = ldap_connect( $param );
		if ( $this->getVar('ldap_version3') === 'True' ) ldap_set_option( $conn, LDAP_OPT_PROTOCOL_VERSION, 3 );
		$anon = @ldap_bind( $conn, 'cn=test_connect', 'pass' );
		$result = ( ldap_errno( $conn ) == 49 );
		if ( $anon ) ldap_close( $conn );
		return $result;
	}
	
	private function _testLdapBind( $param, $conf ) {
		if ( $this->_validate( $conf ) ) {
			$keys = array_keys($this->{'_conf_'.$conf}());
			$user_vname = current( array_filter( $keys, create_function( '$a', 'return strstr( $a, \'root_dn\' );' ) ) );
			$pass_vname = current( array_filter( $keys, create_function( '$a', 'return strstr( $a, \'root_pw\' );' ) ) );
			$conn = ldap_connect( $param );
			if ( $this->getVar('ldap_version3') === 'True' ) ldap_set_option( $conn, LDAP_OPT_PROTOCOL_VERSION, 3 );
			$anon = @ldap_bind( $conn, $this->getVar( $user_vname ), $this->getVar( $pass_vname ) );
			if ( ldap_errno( $conn ) ) $this->_err_vars[$user_vname] = $this->_err_vars[$pass_vname] = true;
			if ( $anon ) ldap_close( $conn );
		}
		return true;
	}
	
	private function _testLdapContext( $param ) {
		if ( $this->hasErr('ldap_host') ) return false;
		$conn = ldap_connect( $this->getVar('ldap_host') );
		if ( $this->getVar('ldap_version3') === 'True' ) ldap_set_option( $conn, LDAP_OPT_PROTOCOL_VERSION, 3 );
		return (bool)@ldap_read( $conn, $param, '(objectClass=*)', array('dn'), 0, 1, 10 );
	}
	
	private function _fetchLdapContext() {
		if ( $this->hasErr('ldap_host') ) return false;
		$conn = ldap_connect( $this->getVar('ldap_host') );
		if ( $this->getVar('ldap_version3') === 'True' ) ldap_set_option( $conn, LDAP_OPT_PROTOCOL_VERSION, 3 );
		if ( ( $srch = ldap_read( $conn, '', '(objectClass=*)', array('namingContexts'), 0, 1, 10 ) ) === false ) return false;
		$info = ldap_get_entries( $conn, $srch );
		if ( $info === false || ( !isset($info['count']) || count($info['count']) < 1 ) )  return false;
		unset( $info[0]['namingcontexts']['count'] );
		return $info[0]['namingcontexts'];
	}
	
	private function _save() {
		
		if ( count( array_filter( $this->_err_vars, create_function( '$a', 'return $a;' ) ) ) ) return false;
		
		$datetime = CreateObject('phpgwapi.date_time');
		$this->_old_vars['tz_offset'] = isset($this->_cur_vars['tz_offset'])? $this->_cur_vars['tz_offset'] : '';
		$this->_new_vars['tz_offset'] = $datetime->getbestguess();
		
		$this->_old_vars['is_configured'] = isset($this->_cur_vars['is_configured'])? $this->_cur_vars['is_configured'] : '';
		$this->_new_vars['is_configured'] = 'true';
		
		$this->_getDB()->transaction_begin();
		
		foreach ( $this->_new_vars as $key => $value ) {
			if ( isset($this->_old_vars[$key]) ) {
				if ( $this->_old_vars[$key] === $value ) continue;
				if ( $value === '' ) {
					if ( strstr( $key, 'passwd' ) || strstr( $key, 'password' ) || strstr( $key, 'root_pw' ) ) continue;
					//Zend_Debug::dump('DELETE FROM '.$this->_getTableConfig().' '.'WHERE config_app=\'phpgwapi\' AND config_name=\''. $key .'\'');
					$this->_getDB()->query(
						'DELETE FROM '.$this->_getTableConfig().' '.
						'WHERE config_app=\'phpgwapi\' AND config_name=\''. $key .'\''
					);
				} else {
					//Zend_Debug::dump('UPDATE '.$this->_getTableConfig().' SET '.'config_value=\''.$this->_getDB()->db_addslashes( $value ).'\' '.'WHERE config_app=\'phpgwapi\' AND config_name=\''. $key .'\'');
					$this->_getDB()->query(
						'UPDATE '.$this->_getTableConfig().' SET '.
						'config_value=\''.$this->_getDB()->db_addslashes( $value ).'\' '.
						'WHERE config_app=\'phpgwapi\' AND config_name=\''. $key .'\''
					);
				}
			} else {
				if ( $value === '' ) continue;
				//Zend_Debug::dump('INSERT INTO '.$this->_getTableConfig().' ( config_app, config_name, config_value ) '.'VALUES (\'phpgwapi\',\''.$key.'\',\''.$this->_getDB()->db_addslashes( $value ).'\')');
				$this->_getDB()->query(
					'INSERT INTO '.$this->_getTableConfig().' ( config_app, config_name, config_value ) '.
					'VALUES (\'phpgwapi\',\''.$key.'\',\''.$this->_getDB()->db_addslashes( $value ).'\')'
				);
			}
		}
		
		$this->_getDB()->transaction_commit();
		
		/* Add cleaning of app_sessions per skeeter, but with a check for the table being there, just in case */
		if( in_array( 'phpgw_app_sessions', array_map( create_function( '$a', 'return $a[\'table_name\'];' ), $this->_getDB()->table_names() ) ) ) {
			$this->_getDB()->lock( array( 'phpgw_app_sessions' ) );
			$this->_getDB()->query( 'DELETE FROM phpgw_app_sessions WHERE sessionid=\'0\' AND loginid=\'0\' AND app=\'phpgwapi\' AND location=\'config\'', __LINE__, __FILE__ );
			$this->_getDB()->query( 'DELETE FROM phpgw_app_sessions WHERE app=\'phpgwapi\' AND location=\'phpgw_info_cache\'', __LINE__, __FILE__ );
			$this->_getDB()->unlock();
		}
		
		$this->_redirect( ( $this->getVar('auth_type') == 'ldap')?  $this->getVar('webserver_url').'/setup/ldap.php' : 'index.php' );
	}
	
	private function _conf_global() {
		return [
			'temp_dir' => [
				'required' => true,
				'filters' => [
					'trim',
					[ 'rtrim', '/', 'if' => [ [ 'method' => [ 'is_not_equal', '/' ], 'return' => 'value', ], ], ],
					'realpath',
				],
				'attributes' => [ 'is_dir', 'is_writable' ],
			],
			'files_dir' => [
				'required' => true,
				'filters' => [
					'trim',
					[ 'rtrim', '/', 'if' => [ [ 'method' => [ 'is_not_equal', '/' ], 'return' => 'value', ], ], ],
					'realpath',
				],
				'attributes' => [ 'is_dir', 'is_writable', '_not_in_docroot' ],
			],
			'webserver_url' => [
				'required' => false,
				'filters' => [ 'trim', [ 'rtrim', '/' ], ],
			],
			'image_type' => [
				'required' => false,
				'filters' => [ 'trim' ],
				'attributes' => [ [ 'in_array', [ '', '1', '2' ], ], ],
			],
			'auth_type' => [
				'required' => true,
				'attributes' => [
					[ 'in_array', [ 'ldap', 'sql', 'sqlssl', 'mail', 'http', 'nis', 'pam' ], ],
					[ '_validate', 'ldap', 'dep_off' => true, 'if' => [ [ 'method' => [ 'is_equal', 'ldap' ], 'return' => true, ], ], ],
				],
			],
			'account_repository' => [
				'required' => true,
				'attributes' => [
					[ 'in_array', [ 'ldap', 'sql' ], ],
					[ '_validate', 'ldap', 'dep_off' => true, 'if' => [ [ 'method' => [ 'is_equal', 'ldap' ], 'return' => true, ], ], ],
				],
			],
			'case_sensitive_username' => [
				'required' => false,
				'filters' => [ 'trim', ],
				'attributes' => [ [ 'in_array', [ '', 'True', ], ], ],
			],
			'auto_create_acct' => [
				'required' => false,
				'filters' => [ 'trim', ],
				'attributes' => [ [ 'in_array', [ '', 'True', ], ], ],
			],
			'auto_create_expire' => [
				'required' => true,
				'filters' => [ 'trim', 'strtolower', ],
				'attributes' => [ [ 'in_array', [ 'never', '604800', '1209600', '1209600', ], ], ],
			],
			'acl_default' => [
				'required' => true,
				'filters' => [ 'trim', 'strtolower', ],
				'attributes' => [ [ 'in_array', [ 'deny', 'grant', ], ], ],
			],
			'file_repository' => [
				'required' => true,
				'filters' => [ 'trim', 'strtolower', ],
				'attributes' => [ [ 'in_array', [ 'sql', 'dav', ], ], ],
			],
			'file_store_contents' => [
				'required' => true,
				'filters' => [ 'trim', 'strtolower', ],
				'attributes' => [ [ 'in_array', [ 'filesystem', 'sql', ], ], ],
			],
			'sql_encryption_type' => [
				'required' => true,
				'filters' => [ 'trim', ],
				'attributes' => [ [ 'in_array', (function_exists('sql_passwdhashes_arr')? sql_passwdhashes_arr() : []), ], ],
			],
			'mcrypt_algo' => [
				'required' => true,
				'filters' => [ 'trim', ],
				'attributes' => [ [ 'in_array', (function_exists('encryptalgo_arr')? encryptalgo_arr() : []), ], ],
			],
			'mcrypt_mode' => [
				'required' => true,
				'filters' => [ 'trim', ],
				'attributes' => [ [ 'in_array', (function_exists('encryptmode_arr')? encryptmode_arr() : []), ], ],
			],
			'hostname' => [ 'required' => false, ],
			'default_ftp_server' => [ 'required' => false, ],
			'ftp_use_mime' => [ 'required' => false, ],
			'httpproxy_server' => [ 'required' => false, ],
			'httpproxy_port' => [ 'required' => false, ],
			'httpproxy_server_username' => [ 'required' => false, ],
			'httpproxy_server_password' => [ 'required' => false, ],
			'server_memcache' => [ 'required' => false, ],
			'port_server_memcache' => [ 'required' => false, ],
			'account_min_id' => [ 'required' => false, ],
			'account_max_id' => [ 'required' => false, ],
			'account_prefix' => [ 'required' => false, ],
			'default_group_lid' => [ 'required' => false, ],
			'encryptkey' => [ 'required' => false, ],
		];
	}
	
	private function _conf_ldap() {
		return [
			'ldap_version3' => [
				'required' => false,
				'filters' => [ 'trim', ],
				'attributes' => [ [ 'in_array', [ '', 'True', ], ], ],
			],
			'ldap_extra_attributes' => [
				'required' => false,
				'filters' => [ 'trim', ],
				'attributes' => [
					[ 'in_array', [ '', 'True' ], ],
					[ '_validate', 'ldapExtra', 'dep_off' => true, 'if' => [ [ 'method' => [ 'is_equal', 'True' ], 'return' => true, ], ], ],
				],
			],
			'ldap_host' => [
				'required' => true,
				'filters' => [ 'trim', ],
				'attributes' => [
					'_testLdapConnect',
					[ '_testLdapBind', 'ldapBind' ],
				],
			],
			'ldap_encryption_type' => [
				'required' => true,
				'filters' => [ 'trim', ],
				'attributes' => [ [ 'in_array', (function_exists('passwdhashes_arr')? passwdhashes_arr() : []), ], ],
			],
			'ldap_master_host' => [
				'required' => false,
				'attributes' => [
					[ '_testLdapConnect', 'if' => [ [ 'method' => [ 'is_not_equal', '' ], 'return' => true, ], ], ],
					[ '_testLdapBind', 'ldapMasterBind', 'if' => [ [ 'method' => [ 'is_not_equal', '' ], 'return' => true, ], ], ],
				],
			],
			'ldap_context' => [ 'required' => true, 'attributes' => [ '_testLdapContext', ], ],
			'ldap_group_context' => [ 'required' => true, 'attributes' => [ '_testLdapContext', ], ],
			'ldap_search_filter' => [ 'required' => false, ],
		];
	}
	
	private function _conf_ldapExtra() {
		return [
			'ldap_account_home' => [ 'required' => true, 'filters' => [ 'trim', [ 'rtrim', '/' ], ], ],
			'ldap_account_shell' => [ 'required' => true, 'filters' => [ 'trim', ], ],
		];
	}
	
	private function _conf_ldapBind() {
		return [
			'ldap_root_dn' => [ 'required' => true, ],
			'ldap_root_pw' => [ 'required' => false, ],
		];
	}
	
	private function _conf_ldapMasterBind() {
		return [
			'ldap_master_root_dn' => [ 'required' => true, ],
			'ldap_master_root_pw' => [ 'required' => false, ],
		];
	}
	
	private function _render() {
		$GLOBALS['setup_tpl'] = CreateObject( 'setup.Template', $this->_getTplRoot() );
		
		$GLOBALS['setup_tpl']->set_file( array(
				'T_head'				=> 'head.tpl',
				'T_footer'				=> 'footer.tpl',
				'T_alert_msg'			=> 'msg_alert_msg.tpl',
				'T_config_pre_script'	=> 'config_pre_script.tpl',
				'T_config_post_script'	=> 'config_post_script.tpl',
		) );
		$title = $GLOBALS['phpgw_setup']->ConfigDomain . '(' . $GLOBALS['phpgw_domain'][$GLOBALS['phpgw_setup']->ConfigDomain]['db_type'] . ')';
		$GLOBALS['phpgw_setup']->html->show_header( lang('Configuration'), False, 'config', $title);
		
		$this->_renderErrors();
		
		$GLOBALS['setup_tpl']->pparse('out','T_config_pre_script');
		
		$this->_renderCurrentVars();
		
		$GLOBALS['setup_tpl']->set_var('more_configs',lang('Please login to egroupware and run the admin application for additional site configuration') . '.');
		
		$GLOBALS['setup_tpl']->set_var('lang_submit',lang('Save'));
		$GLOBALS['setup_tpl']->set_var('lang_cancel',lang('Cancel'));
		$GLOBALS['setup_tpl']->pparse('out','T_config_post_script');
		$GLOBALS['phpgw_setup']->html->show_footer();
	}
	
	private function _renderCurrentVars() {
		$t = CreateObject( 'setup.Template', $this->_getTplRoot() );
		$t->set_unknowns('keep');
		$t->set_file(array('config' => 'config.tpl'));
		$t->set_block('config','body','body');
		
		foreach ( $t->get_undefined('body') as $varname ) {
			$value = substr( $varname, strpos( $varname, '_' ) + 1 );
			switch ( substr( $varname, 0, strpos( $varname, '_' ) ) )
			{
				case 'lang':
					$t->set_var( $varname, lang( str_replace( '_', ' ', $value ) ) );
					break;
					
				case 'value':
					$isValid = ( $this->getVar( $value ) !== null ) && !( strstr($varname,'passwd') || strstr($varname,'password') || strstr($varname,'root_pw') );
					$t->set_var( $varname, $isValid? $this->getVar( $value ) : '' );
					break;
					
				case 'selected':
					$var = substr( $value, 0, strrpos( $value, '_' ) );
					$selected = substr( $value, strrpos( $value, '_' ) + 1 );
					$t->set_var( $varname, ( ($this->getVar( $var ) !== null ) && $this->getVar( $var ) == $selected )? ' selected' : '' );
					break;
					
				case 'hook':
					$t->set_var( $varname, $value( array_merge( $this->_cur_vars, $this->_new_vars ) ) );
					break;
					
				case 'errtag':
					$t->set_var( $varname, $this->hasErr($value)? '<font style="color:#FF0000;"> * </font>' : '' );
					break;
					
				default:
					$t->set_var( $varname, '' );
					break;
			}
		}
		
		$t->pfp('out','body');
	}
	
	private function _renderErrors() {
		
		if( !isset($GLOBALS['error']) ) return false;
		
		foreach ( (array)$GLOBALS['error'] as $error ) {
			switch ( $error ) {
				case 'badldapconnection': $msg = 'There was a problem trying to connect to your LDAP server. <br>please check your LDAP server configuration'; break;
				case 'indocroot': $msg = 'Path to user and group files HAS TO BE OUTSIDE of the webservers document-root!!!'; break;
				default: $msg = $error;
			}
			$GLOBALS['phpgw_setup']->html->show_alert_msg('Error', lang( $msg ).'.');
		}
		return true;
	}
}

Config::getInstance()->run();
