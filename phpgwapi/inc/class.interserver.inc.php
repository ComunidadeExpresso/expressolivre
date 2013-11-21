<?php
  /**************************************************************************\
  * eGroupWare API - phpgw Inter-server communications class                 *
  * This file written by Miles Lott <milosch@groupwhere.org>                 *
  * Maintain list and provide send interface to remote phpgw servers         *
  * Copyright (C) 2001 Miles Lott                                            *
  * -------------------------------------------------------------------------*
  * This library is part of the eGroupWare API                               *
  * http://www.egroupware.org                                                *
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


	class interserver
	{
		var $db;
		var $accounts;
		var $table = 'phpgw_interserv';
		var $total = 0;
		var $result = '';

		var $servers = array();
		var $serverid = 0;
		var $security = '';
		var $mode = '';
		var $authed = False;
		var $sessionid = '';
		var $kp3 = '';

		/* These are now entered as defaults if the admin forgot to enter the full URL */
		var $urlparts = array();

		/*
		0/none == no access
		1/apps == read app data only
		99/all == read accounts and other api data
		Two servers must have each other setup as 'all' for full coop
		*/
		var $trust_levels = array(
			'none' => 0,
			'apps' => 1,
			'all'  => 99
		);

		/*
		0 - No trust, but they may trust us
		1 - Trust to make requests of us
		2 - Trust remote server's trusts also
		3 - We both trust each other
		4 - We both trust each other, and we trust the remote server's trusts also
		*/
		var $trust_relationships = array(
			'outbound'       => 0,
			'inbound'        => 1,
			'passthrough'    => 2,
			'bi-directional' => 3,
			'bi-dir passthrough' => 4
		);

		var $security_types = array(
			'standard' => '',
			'ssl'      => 'ssl'
		);

		var $server_modes = array(
			'XML-RPC' => 'xmlrpc',
			'SOAP'    => 'soap'
		);

		function interserver($serverid='')
		{
			$url = preg_replace('/https*:\/\/[^\/]*\//i','',$GLOBALS['phpgw_info']['server']['webserver_url']);
			$this->urlparts = array(
				'xmlrpc' => $url.'/xmlrpc.php',
				'soap'   => $url.'/soap.php'
			);

			$this->db = $GLOBALS['phpgw']->db;
			if($serverid)
			{
				$this->serverid = (int)$serverid;
				$this->setup();
			}
		}

		function debug($str,$debug=False)
		{
			if($debug)
			{
				$this->_debug($str);
			}
		}

		function _debug($err='')
		{
			if(!$err)
			{
				return;
			}
			echo $err . '&nbsp;';
		}

		function setup()
		{
			$this->read_repository();
			if($this->server['trust_level'])
			{
				$this->accounts = CreateObject('phpgwapi.accounts');
				$this->accounts->server = $this->serverid;
			}
			$this->security = $this->server['server_security'];
			$this->mode = $this->server['server_mode'];
		}

		/* send command to remote server */
		function send($method_name, $args, $url, $debug=True)
		{
			$cmd = '_send_' . ($this->mode ? $this->mode : 'xmlrpc') . '_' . $this->security;
			$this->$cmd($method_name, $args, $url, $debug);
			return $this->result;
		}

		function _split_url($url)
		{
			preg_match('/^(.*?\/\/.*?)(\/.*)/',$url,$matches);
			$hostpart = $matches[1];
			$hostpart = str_replace('https://','',$hostpart);
			$hostpart = str_replace('http://','',$hostpart);
			switch($this->mode)
			{
				case 'soap':
					if(!strstr($matches[2],'soap.php'))
					{
						$matches[2] .= $this->urlparts['soap'];
					}
					break;
				case 'xmlrpc':
					if(!strstr($matches[2],'xmlrpc.php'))
					{
						$matches[2] .= $this->urlparts['xmlrpc'];
					}
					break;
				default:
					break;
			}
			$uri = $matches[2];
			return array($uri,$hostpart);
		}

		function _send_xmlrpc_ssl($method_name, $args, $url, $debug=True)
		{
			list($uri,$hostpart) = $this->_split_url($url);
			if(!@is_array($args))
			{
				$arr[] = CreateObject('phpgwapi.xmlrpcval',$args,'string');
				$f = CreateObject('phpgwapi.xmlrpcmsg', $method_name, $arr,'string');
			}
			else
			{
				while(list($key,$val) = @each($args))
				{
					if(@is_array($val))
					{
						while(list($x,$y) = each($val))
						{
							$tmp[$x] = CreateObject('phpgwapi.xmlrpcval',$y, 'string');
						}
						$ele[$key] = CreateObject('phpgwapi.xmlrpcval',$tmp,'struct');
					}
					else
					{
						$ele[$key] = CreateObject('phpgwapi.xmlrpcval',$val, 'string');
					}
				}
				$arr[] = CreateObject('phpgwapi.xmlrpcval',$ele,'struct');
				$f = CreateObject('phpgwapi.xmlrpcmsg', $method_name, $arr,'struct');
			}

			$this->debug("<pre>" . htmlentities($f->serialize()) . "</pre>\n",$debug);
			$c = CreateObject('phpgwapi.xmlrpc_client',$uri, $hostpart, 443);
			$c->setCredentials($this->sessionid,$this->kp3);
			$c->setDebug(0);
			$r = $c->send($f,0,'https');
			if (!$r)
			{
				$this->debug('send failed');
			}
			$v = $r->value();
			if (!$r->faultCode())
			{
				$this->debug('<hr>I got this value back<br><pre>' . htmlentities($r->serialize()) . '</pre><hr>',$debug);
				$this->result = phpgw_xmlrpc_decode($v);
			}
			else
			{
				$this->debug('Fault Code: ' . $r->faultCode() . ' Reason "' . $r->faultString() . '"<br>',$debug);
				$this->result = htmlentities($r->serialize());
			}
			return $this->result;
		}

		function _send_xmlrpc_($method_name, $args, $url, $debug=True)
		{
			list($uri,$hostpart) = $this->_split_url($url);
			if(!@is_array($args))
			{
				$arr[] = CreateObject('phpgwapi.xmlrpcval',$args,'string');
				$f = CreateObject('phpgwapi.xmlrpcmsg', $method_name, $arr,'string');
			}
			else
			{
				while(list($key,$val) = @each($args))
				{
					if(@is_array($val))
					{
						while(list($x,$y) = each($val))
						{
							$tmp[$x] = CreateObject('phpgwapi.xmlrpcval',$y, 'string');
						}
						$ele[$key] = CreateObject('phpgwapi.xmlrpcval',$tmp,'struct');
					}
					else
					{
						$ele[$key] = CreateObject('phpgwapi.xmlrpcval',$val, 'string');
					}
				}
				$arr[] = CreateObject('phpgwapi.xmlrpcval',$ele,'struct');
				$f = CreateObject('phpgwapi.xmlrpcmsg', $method_name, $arr,'struct');
			}

			$this->debug('<pre>' . htmlentities($f->serialize()) . '</pre>' . "\n",$debug);
			$c = CreateObject('phpgwapi.xmlrpc_client',$uri, $hostpart, 80);
			$c->setCredentials($this->sessionid,$this->kp3);
//			_debug_array($c);
			$c->setDebug(0);
			$r = $c->send($f);
			if (!$r)
			{
				$this->debug('send failed');
			}
			$v = $r->value();
			if (!$r->faultCode())
			{
				$this->debug('<hr>I got this value back<br><pre>' . htmlentities($r->serialize()) . '</pre><hr>',$debug);
				$this->result = phpgw_xmlrpc_decode($v);
			}
			else
			{
				$this->debug('Fault Code: ' . $r->faultCode() . ' Reason "' . $r->faultString() . '"<br>',$debug);
				$this->result = htmlentities($r->serialize());
			}
			return $this->result;
		}

		function _send_soap_ssl($method_name, $args, $url, $debug=True)
		{
			$method_name = str_replace('.','_',$method_name);
			list($uri,$hostpart) = $this->_split_url($url);
			if(!@is_array($args))
			{
				$arr[] = CreateObject('phpgwapi.soapval','','string',$args);
			}
			else
			{
				while(list($key,$val) = @each($args))
				{
					if(@is_array($val))
					{
						while(list($x,$y) = each($val))
						{
							$tmp[] = CreateObject('phpgwapi.soapval',$x,'string',$y);
						}
						$arr[] = CreateObject('phpgwapi.soapval',$key, 'array',$tmp);
					}
					else
					{
						$arr[] = CreateObject('phpgwapi.soapval',$key, 'string',$val);
					}
				}
			}

			$soap_message = CreateObject('phpgwapi.soapmsg',$method_name,$arr);
			/* print_r($soap_message);exit; */
			$soap = CreateObject('phpgwapi.soap_client',$uri,$hostpart);
			$soap->username = $this->sessionid;
			$soap->password = $this->kp3;
			/* _debug_array($soap);exit; */
			if($r = $soap->send($soap_message,$method_name))
			{
				$this->debug('<hr>I got this value back<br><pre>' . htmlentities($r->serialize()) . '</pre><hr>',$debug);
				$this->result = $r->decode();
				return $this->result;
			}
			else
			{
				$this->debug('Fault Code: ' . $r->ernno . ' Reason "' . $r->errstring . '"<br>',$debug);
			}
		}

		function _send_soap_($method_name, $args, $url, $debug=True)
		{
			$method_name = str_replace('.','_',$method_name);
			list($uri,$hostpart) = $this->_split_url($url);

			if(!$args)
			{
				$arr = '';
			}
			elseif(@is_array($args))
			{
				while(list($key,$val) = @each($args))
				{
					if(@is_array($val))
					{
						while(list($x,$y) = each($val))
						{
							$tmp[] = CreateObject('phpgwapi.soapval',$x,'string',$y);
						}
						$ele[] = CreateObject('phpgwapi.soapval',$key, 'array',$tmp);
						$complex = True;
					}
					else
					{
						$ele[] = CreateObject('phpgwapi.soapval',$key, 'string',$val);
					}
				}
				$arr[] = CreateObject('phpgwapi.soapval','','struct',$ele);
			}
			else
			{
				$arr[] = CreateObject('phpgwapi.soapval','','string',$args);
			}
			$this->request = $arr;

			$soap_message = CreateObject('phpgwapi.soapmsg',$method_name,$this->request);
			$soap = CreateObject('phpgwapi.soap_client',$uri,$hostpart);
			$soap->username = $this->sessionid;
			$soap->password = $this->kp3;
			if($r = $soap->send($soap_message,$method_name))
			{
				_debug_array(htmlentities($soap->outgoing_payload));
				_debug_array(htmlentities($soap->incoming_payload));
				$this->debug('<hr>I got this value back<br><pre>' . htmlentities($r->serialize()) . '</pre><hr>',$debug);
				$this->result = $r->decode();
				return $this->result;
			}
			else
			{
				_debug_array($soap->outgoing_payload);
				$this->debug('Fault Code: ' . $r->ernno . ' Reason "' . $r->errstring . '"<br>',$debug);
			}
		}

		function build_request($_req,$recursed=False,$ext='')
		{
			if(@is_array($_req))
			{
				$ele = array();
				@reset($_req);
				while(list($key,$val) = @each($_req))
				{
					$ele[$key] = $this->build_request($val,True,$key);
				}
				$this->request[] = CreateObject('phpgwapi.soapval',$ext,'struct',$ele);
				$ext = '';
			}
			else
			{
				$_type = (is_long($_req) ? 'int' : gettype($_req));
				if($recursed)
				{
					return CreateObject('phpgwapi.soapval',$ext,$_type,$_req);
				}
				else
				{
					$this->request[$ext] = CreateObject('phpgwapi.soapval',$ext,$_type,$_req);
				}
			}
		}

		/* Following are for server list management and query */
		function read_repository($serverid='')
		{
			if(!$serverid)
			{
				$serverid = $this->serverid;
			}
			$sql = "SELECT * FROM $this->table WHERE server_id=" . (int)$serverid;
			$this->db->query($sql,__LINE__,__FILE__);
			if($this->db->next_record())
			{
				$this->server['server_name'] = $this->db->f('server_name');
				$this->server['server_url']  = $this->db->f('server_url');
				$this->server['server_mode'] = $this->db->f('server_mode');
				$this->server['server_security'] = $this->db->f('server_security');
				$this->server['trust_level'] = $this->db->f('trust_level');
				$this->server['trust_rel']   = $this->db->f('trust_rel');
				$this->server['username']    = $this->db->f('username');
				$this->server['password']    = $this->db->f('password');
				$this->server['admin_name']  = $this->db->f('admin_name');
				$this->server['admin_email'] = $this->db->f('admin_email');
			}
			return $this->server;
		}

		function save_repository($serverid='')
		{
			if(!$serverid)
			{
				$serverid = $this->serverid;
			}
			if((int)$serverid && @is_array($this->server))
			{
				$sql = "UPDATE $this->table SET "
					. "server_name='" . $this->server['server_name'] . "',"
					. "server_url='"  . $this->server['server_url']  . "',"
					. "server_mode='" . $this->server['server_mode']  . "',"
					. "server_security='" . $this->server['server_security']  . "',"
					. "trust_level="  . (int)$this->server['trust_level'] . ","
					. "trust_rel="    . (int)$this->server['trust_rel'] . ","
					. "username='"    . $this->server['username']  . "',"
					. "password='"    . $this->server['password']  . "',"
					. "admin_name='"  . $this->server['admin_name']  . "',"
					. "admin_email='" . $this->server['admin_email'] . "' "
					. "WHERE server_id=" . (int)$serverid;
				$this->db->query($sql,__LINE__,__FILE__);
				return True;
			}
			return False;
		}

		function create($server_info='')
		{
			if(!@is_array($server_info))
			{
				return False;
			}
			$sql = "INSERT INTO $this->table (server_name,server_url,server_mode,server_security,"
				. "trust_level,trust_rel,username,password,admin_name,admin_email) "
				. "VALUES('" . $server_info['server_name'] . "','" . $server_info['server_url'] . "','"
				. $server_info['server_mode'] . "','" . $server_info['server_security'] . "',"
				. (int)$server_info['trust_level'] . "," . (int)$server_info['trust_rel'] . ",'"
				. $server_info['username'] . "','" . $server_info['password'] . "','"
				. $server_info['admin_name'] . "','" . $server_info['admin_email'] . "')";
			$this->db->query($sql,__LINE__,__FILE__);

			$sql = "SELECT server_id FROM $this->table WHERE server_name='" . $server_info['server_name'] . "'";
			$this->db->query($sql,__LINE__,__FILE__);
			if($this->db->next_record())
			{
				$server_info['server_id'] = $this->db->f(0);
				$this->serverid = $server_info['server_id'];
				$this->server   = $server_info;
				return $this->serverid;
			}
			return False;
		}

		function delete($serverid='')
		{
			if(!$serverid)
			{
				$serverid = $this->serverid;
			}
			if($serverid)
			{
				$sql = "DELETE FROM $this->table WHERE server_id=$serverid";
				$this->db->query($sql,__LINE__,__FILE__);
				return True;
			}
			return False;
		}

		function get_list($start='',$sort='',$order='',$query='',$offset='',&$total)
		{
			if (!$sort)
			{
				$sort = 'DESC';
			}
			if ($query)
			{
				$whereclause = "WHERE server_name LIKE '%$query%'"
					. "OR server_url  LIKE '%$query%'"
					. "OR server_mode LIKE '%$query%'"
					. "OR admin_name  LIKE '%$query%'"
					. "OR admin_email LIKE '%$query%'";
			}
			if ($order)
			{
				$orderclause = 'ORDER BY ' . $order . ' ' . $sort;
			}
			else
			{
				$orderclause = 'ORDER BY server_name ASC';
			}

			$sql = "SELECT * FROM $this->table $whereclause $orderclause";
			$this->db->query($sql,__LINE__,__FILE__);
			
			while ($this->db->next_record())
			{
				$this->servers[$this->db->f('server_name')]['server_id']   = $this->db->f('server_id');
				$this->servers[$this->db->f('server_name')]['server_name'] = $this->db->f('server_name');
				$this->servers[$this->db->f('server_name')]['server_url']  = $this->db->f('server_url');
				$this->servers[$this->db->f('server_name')]['server_mode'] = $this->db->f('server_mode');
				$this->servers[$this->db->f('server_name')]['server_security'] = $this->db->f('server_security');
				$this->servers[$this->db->f('server_name')]['trust_level'] = $this->db->f('trust_level');
				$this->servers[$this->db->f('server_name')]['trust_rel']   = $this->db->f('trust_rel');
				$this->servers[$this->db->f('server_name')]['admin_name']  = $this->db->f('admin_name');
				$this->servers[$this->db->f('server_name')]['admin_email'] = $this->db->f('admin_email');
			}
			$this->total = $this->db->num_rows();
			$total = $this->total;
			return $this->servers;
		}

		function formatted_list($server_id=0,$java=False,$local=False)
		{
			if ($java)
			{
				$jselect = ' onChange="this.form.submit();"';
			}
			$select  = "\n" .'<select name="server_id"' . $jselect . ">\n";
			$select .= '<option value="0"';
			if(!$server_id)
			{
				$select .= ' selected';
			}
			$selectlang = $local ? lang('Local') : lang('Please Select');
			$select .= '>' . $selectlang . '</option>'."\n";

			$x = '';
			$slist = $this->get_list('','','','','',$x);
			while (list($key,$val) = each($slist))
			{
				$foundservers = True;
				$select .= '<option value="' . $val['server_id'] . '"';
				if ($val['server_id'] == $server_id)
				{
					$select .= ' selected';
				}
				$select .= '>' . $val['server_name'] . '</option>'."\n";
			}

			$select .= '</select>'."\n";
			$select .= '<noscript><input type="submit" name="server_id_select" value="' . lang('Select') . '"></noscript>' . "\n";
			if(!$foundservers)
			{
				$select = '';
			}

			return $select;
		}

		function name2id($server_name='')
		{
			if(!$server_name)
			{
				$server_name = $this->server['server_name'];
			}
			if($server_name)
			{
				$sql = "SELECT server_id FROM $this->table WHERE server_name='$server_name'";
				$this->db->query($sql,__LINE__,__FILE__);
				if($this->db->next_record())
				{
					$serverid = $this->db->f(0);
					return $serverid;
				}
			}
			return False;
		}

		function id2name($serverid='')
		{
			if(!$serverid)
			{
				$serverid = $this->serverid;
			}
			if($serverid)
			{
				$sql = "SELECT server_name FROM $this->table WHERE server_id=$serverid";
				$this->db->query($sql,__LINE__,__FILE__);
				if($this->db->next_record())
				{
					$server_name = $this->db->f(0);
					return $server_name;
				}
			}
			return False;
		}

		function exists($serverdata='')
		{
			if(!$serverdata)
			{
				return False;
			}
			if(is_int($serverdata))
			{
				$serverid = $serverdata;
				settype($server_name,'string');
				$server_name = $this->id2name($serverid);
			}
			else
			{
				$server_name = $serverdata;
			}
			$sql = "SELECT server_id FROM $this->table WHERE server_name='$server_name'";
			$this->db->query($sql,__LINE__,__FILE__);
			if($this->db->next_record())
			{
				return True;
			}
			return False;
		}

		/* TODO - Determine trust level here */
		function auth($serverdata='')
		{
			if(!$serverdata || !@is_array($serverdata))
			{
				return False;
			}
			$server_name = $serverdata['server_name'];
			$username    = $serverdata['username'];
			$password    = $serverdata['password'];

			$sql = "SELECT server_id,trust_rel FROM $this->table WHERE server_name='$server_name'";
			$this->db->query($sql,__LINE__,__FILE__);
			if($this->db->next_record())
			{
				if ($username == $GLOBALS['phpgw_info']['server']['site_username'] &&
					$password == $GLOBALS['phpgw_info']['server']['site_password'] &&
					$this->db->f('trust_rel') >= 1)
				{
					$this->authed = True;
					return True;
				}
			}
			return False;
		}
	}
?>
