<?php
  /**************************************************************************\
  * eGroupWare API - Accounts manager for LDAP                               *
  * This file written by Miles Lott <milosch@groupwhere.org>                 *
  * View and manipulate contact records using LDAP                           *
  * ------------------------------------------------------------------------ *
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


	/*!
	 @class contacts
	 @abstract Contact List System
	 @discussion Author: jengo/Milosch <br>
	 This class provides a contact database scheme. <br>
	 It attempts to be based on the vcard 2.1 standard, with mods as needed to make for more reasonable sql storage. <br>
	 The LDAP schema used here may require installation of schema files available in the phpgwapi/doc/ldap dir.
	 Please see the README file there.
	 Syntax: CreateObject('phpgwapi.contacts'); <br>
	 Example1: $contacts = CreateObject('phpgwapi.contacts');
	*/
	class contacts_
	{
		var $db = '';
		var $ldap = '';
		var $nextid = '';
		var $std_table = '';
		var $ext_table = 'phpgw_addressbook_extra';

		var $account_id;
		var $adr_types;
		var $total_records;
		var $grants;

		/* The left side are the array elements used throughout phpgw, right side are the ldap attributes */
		var $stock_contact_fields = array(
			'fn'                  => 'cn',
			'n_given'             => 'givenname',
			'n_family'            => 'sn',
			'n_middle'            => 'phpgwmiddlename',
			'n_prefix'            => 'phpgwprefix',
			'n_suffix'            => 'phpgwsuffix',
			'sound'               => 'phpgwaudio',
			'bday'                => 'phpgwbirthday',
			'note'                => 'description',
			'tz'                  => 'phpgwtz',
			'geo'                 => 'phpgwgeo',
			'url'                 => 'phpgwurl',
			'pubkey'              => 'phpgwpublickey',

			'org_name'            => 'o',
			'org_unit'            => 'ou',
			'title'               => 'title',

			'adr_one_street'      => 'street',
			'adr_one_locality'    => 'l',
			'adr_one_region'      => 'st',
			'adr_one_postalcode'  => 'postalcode',
			'adr_one_countryname' => 'co',
			'adr_one_type'        => 'phpgwadronetype',
			'label'               => 'phpgwaddresslabel',

			'adr_two_street'      => 'phpgwadrtwostreet',
			'adr_two_locality'    => 'phpgwadrtwolocality',
			'adr_two_region'      => 'phpgwadrtworegion',
			'adr_two_postalcode'  => 'phpgwadrtwopostalcode',
			'adr_two_countryname' => 'phpgwadrtwocountryname',
			'adr_two_type'        => 'phpgwadrtwotype',

			'tel_work'            => 'telephonenumber',
			'tel_home'            => 'homephone',
			'tel_voice'           => 'phpgwvoicetelephonenumber',
			'tel_fax'             => 'facsimiletelephonenumber',
			'tel_msg'             => 'phpgwmsgtelephonenumber',
			'tel_cell'            => 'phpgwcelltelephonenumber',
			'tel_pager'           => 'phpgwpagertelephonenumber',
			'tel_bbs'             => 'phpgwbbstelephonenumber',
			'tel_modem'           => 'phpgwmodemtelephonenumber',
			'tel_car'             => 'phpgwmobiletelephonenumber',
			'tel_isdn'            => 'phpgwisdnphonenumber',
			'tel_video'           => 'phpgwvideophonenumber',
			'tel_prefer'          => 'phpgwpreferphone',
			'email'               => 'mail',
			'email_type'          => 'phpgwmailtype',
			'email_home'          => 'phpgwmailhome',
			'email_home_type'     => 'phpgwmailhometype'
		);

		var $non_contact_fields = array(
			'id'     => 'uidnumber',
			'lid'    => 'uid',
			'tid'    => 'phpgwcontacttypeid',
			'cat_id' => 'phpgwcontactcatid',
			'access' => 'phpgwcontactaccess',
			'owner'  => 'phpgwcontactowner'
		);

		/* Used to set preferphone field */
		var $tel_types = array(
			'work'  => 'work',
			'home'  => 'home',
			'voice' => 'voice',
			'fax'   => 'fax',
			'msg'   => 'msg',
			'cell'  => 'cell',
			'pager' => 'pager',
			'bbs'   => 'bbs',
			'modem' => 'modem',
			'car'   => 'car',
			'isdn'  => 'isdn',
			'video' => 'video'
		);

		/* Used to set mail_type fields */
		var $email_types = array(
			'INTERNET'   => 'INTERNET',
			'CompuServe' => 'CompuServe',
			'AOL'        => 'AOL',
			'Prodigy'    => 'Prodigy',
			'eWorld'     => 'eWorld',
			'AppleLink'  => 'AppleLink',
			'AppleTalk'  => 'AppleTalk',
			'PowerShare' => 'PowerShare',
			'IBMMail'    => 'IBMMail',
			'ATTMail'    => 'ATTMail',
			'MCIMail'    => 'MCIMail',
			'X.400'      => 'X.400',
			'TLX'        => 'TLX'
		);

		function contacts_()
		{
			$this->db = $GLOBALS['phpgw']->db;
			$this->ldap = $GLOBALS['phpgw']->common->ldapConnect(
				$GLOBALS['phpgw_info']['server']['ldap_contact_host'],
				$GLOBALS['phpgw_info']['server']['ldap_contact_dn'],
				$GLOBALS['phpgw_info']['server']['ldap_contact_pw']
			);
			$this->account_id = $GLOBALS['phpgw_info']['user']['account_id'];
			$this->grants     = $GLOBALS['phpgw']->acl->get_grants('addressbook');

			/* Used to flag an address as being:
			   domestic OR  international(default)
			   parcel(default)
			   postal(default)
			   work(default) OR home
			*/
			$this->adr_types = array(
				'dom'    => lang('Domestic'),
				'intl'   => lang('International'),
				'parcel' => lang('Parcel'),
				'postal' => lang('Postal')
			);
		}

		/* send this the id and whatever fields you want to see */
		function read_single_entry($id,$fields='')
		{
			if(!$fields || empty($fields))
			{
				$fields = $this->stock_contact_fields;
			}
			list($stock_fields,$stock_fieldnames,$extra_fields) = $this->split_stock_and_extras($fields);

			if(count($stock_fieldnames))
			{
				$t_fields = ',' . implode(',',$stock_fieldnames);
				if($t_fields == ',')
				{
					unset($t_fields);
				}
			}

			$sri = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_contact_context'], 'uidnumber=' . (int)$id);
			$ldap_fields = ldap_get_entries($this->ldap, $sri);

			$return_fields[0]['id']     = $ldap_fields[0]['uidnumber'][0];
			$return_fields[0]['lid']    = $ldap_fields[0]['uid'][0];
			$return_fields[0]['tid']    = $ldap_fields[0]['phpgwcontacttypeid'][0];
			$return_fields[0]['owner']  = $ldap_fields[0]['phpgwcontactowner'][0];
			$return_fields[0]['access'] = $ldap_fields[0]['phpgwcontactaccess'][0];
			$return_fields[0]['cat_id'] = $ldap_fields[0]['phpgwcontactcatid'][0];
			$return_fields[0]['rights'] = (int)$this->grants[$return_fields[0]['owner']];
			if(@is_array($stock_fieldnames))
			{
				foreach($stock_fieldnames as $name => $value)
				{
					$return_fields[0][$name] = utf8_decode($ldap_fields[0][$value][0]);
				}
			}

			/* Setup address type fields */
			if($return_fields[0]['adr_one_type'])
			{
				$one_type = $return_fields[0]['adr_one_type'];
				foreach($this->adr_types as $name => $val)
				{
					eval("if(strstr(\$one_type,\$name)) { \$return_fields[0][\"one_\$name\"] = \"on\"; }");
				}
			}
			if($return_fields[0]['adr_two_type'])
			{
				$two_type = $return_fields[0]['adr_two_type'];
				foreach($this->adr_types as $name => $val)
				{
					eval("if(strstr(\$two_type,\$name)) { \$return_fields[0][\"two_\$name\"] = \"on\"; }");
				}
			}

			$this->db->query("SELECT contact_name,contact_value FROM $this->ext_table WHERE contact_id='"
				. (int)$id . "'",__LINE__,__FILE__);
			while($this->db->next_record())
			{
				if($extra_fields[$this->db->f('contact_name')])
				{
					$return_fields[0][$this->db->f('contact_name')] = $this->db->f('contact_value');
				}
			}
			return $return_fields;
		}

		function read_last_entry($fields = '')
		{
			if(!$fields || empty($fields))
			{
				$fields = $this->stock_contact_fields;
			}
			list($stock_fields,$stock_fieldnames,$extra_fields) = $this->split_stock_and_extras($fields);

			if(count($stock_fieldnames))
			{
				$t_fields = ',' . implode(',',$stock_fieldnames);
				if($t_fields == ',')
				{
					unset($t_fields);
				}
			}

			$id = $this->nextid;
			if($id == -1)
			{
				$id = 1;
			}

			$sri = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_contact_context'], 'uidnumber=' . (int)$id);
			$ldap_fields = ldap_get_entries($this->ldap, $sri);

			$return_fields[0]['id']     = $ldap_fields[0]['uidnumber'][0];
			$return_fields[0]['lid']    = $ldap_fields[0]['uid'][0];
			$return_fields[0]['tid']    = $ldap_fields[0]['phpgwcontacttypeid'][0];
			$return_fields[0]['owner']  = $ldap_fields[0]['phpgwcontactowner'][0];
			$return_fields[0]['access'] = $ldap_fields[0]['phpgwcontactaccess'][0];
			$return_fields[0]['cat_id'] = $ldap_fields[0]['phpgwcontactcatid'][0];
			$return_fields[0]['rights'] = (int)$this->grants[$return_fields[0]['owner']];

			if(@is_array($stock_fieldnames))
			{
				foreach($stock_fieldnames as $name => $value)
				{
					$return_fields[0][$name] = utf8_decode($ldap_fields[0][$value][0]);
				}
			}

			/* Setup address type fields */
			if($return_fields[0]['adr_one_type'])
			{
				$one_type = $return_fields[0]['adr_one_type'];
				foreach($this->adr_types as $name => $val)
				{
					eval("if(strstr(\$one_type,\$name)) { \$return_fields[0][\"one_\$name\"] = \"on\"; }");
				}
			}
			if($return_fields[0]['adr_two_type'])
			{
				$two_type = $return_fields[0]['adr_two_type'];
				foreach($this->adr_types as $name => $val)
				{
					eval("if(strstr(\$two_type,\$name)) { \$return_fields[0][\"two_\$name\"] = \"on\"; }");
				}
			}

			$this->db->query("SELECT contact_name,contact_value FROM $this->ext_table WHERE contact_id='" . (int)$id . "'",__LINE__,__FILE__);
			while($this->db->next_record())
			{
				if($extra_fields[$this->db->f('contact_name')])
				{
					$return_fields[0][$this->db->f('contact_name')] = $this->db->f('contact_value');
				}
			}
			return $return_fields;
		}

		/* send this the range, query, sort, order and whatever fields you want to see */
		function read($start=0,$limit=0,$fields='',$query='',$filter='',$sort='',$order='', $lastmod=-1,$cquery='')
		{
			if(!$start)  { $start  = 0; }
			if(!$limit)  { $limit  = 0; }
			if(!$filter) { $filter = 'tid=n'; }

			if(!$fields || empty($fields))
			{
				$fields = $this->stock_contact_fields;
			}
			$DEBUG = 0;

			list($stock_fields,$stock_fieldnames,$extra_fields) = $this->split_stock_and_extras($fields);

			$filterfields = array();
			/* turn filter's a=b,c=d OR a=b into an array */
			if($filter)
			{
				if($DEBUG) { echo 'DEBUG - Inbound filter is: #'.$filter.'#'; }
				$filterarray = preg_split('/,/',$filter);
				if($filterarray[1])
				{
					$i=0;
                    $filterarray_count = count($filterarray);
					for($i=0;$i<$filterarray_count;++$i)
					{
						list($name,$value) = preg_split('/=/',$filterarray[$i]);
						if($name)
						{
							if($DEBUG) { echo '<br>DEBUG - Filter strings: #'.$this->non_contact_fields[$name].'# => #'.$value.'#'; }
							$filterfields[$this->non_contact_fields[$name]] = $value;
						}
					}
				}
				else
				{
					list($name,$value) = preg_split('/=/',$filter);
					if($DEBUG)
					{
						echo '<br>DEBUG - Filter strings: #'.$this->non_contact_fields[$name].'# => #'.$value.'#';
					}
					$filterfields = array($this->non_contact_fields[$name] => $value);
				}
			}
			else
			{
				$filterfields += array('phpgwcontacttypeid' => 'n');
				if($DEBUG) { echo "<br>DEBUG - Filter strings: #phpgwcontacttypeid=n#"; }
			}

			/*
			need some way of using the lastmod arg in the filter like this:
			if($lastmod >= 0)
			{
				$filterfields += array('last_mod' => (int)$lastmod;
			}
			or maybe not like this - i am not sure what i am doing :)
			*/

			if(@is_array($this->grants))
			{
				$filterfields['phpgwcontactowner'] = array();
				/* this was not listing private entries when show all was selected */
				/* $filterfields += array('phpgwcontactaccess' => 'public'); */
				if($DEBUG) { echo '<br>DEBUG - My user id is: ' . $this->account_id; }
				foreach($this->grants as $user => $right)
				{
					if($DEBUG) { echo '<br>DEBUG - Grant from owner: ' . $user; }
					$filterfields['phpgwcontactowner'][] = array('phpgwcontactowner' => $user);
				}
			}
			/*
			if($DEBUG)
			{
				while(list($name,$value) = each($filterfields))
				{
					echo '<br>DEBUG - Filter strings: #' . $name . ',' . $value . '#';
				}
			}
			*/

			$sort  = $sort  ? $sort  : 'ASC';
			$order = $order ? $order : 'n_family';

			if($DEBUG && $order)
			{
				echo "<br>DEBUG - ORDER by $order";
			}

			$ldap_fields = array();
			$myfilter = '';

			if($cquery)
			{
				$search_filter = array(
					'fn'       => 'cn',
					'n_family' => 'sn',
					'org_name' => 'o'
				);
				$myfilter = $this->makefilter($filterfields,$search_filter,"$cquery*",$DEBUG);
			}
			elseif($query)
			{
				// the old code was searching about all fields
				// this was very slow
				#reset($this->stock_contact_fields);
				#$myfilter = $this->makefilter($filterfields,$this->stock_contact_fields,$query,$DEBUG);

				if(is_array($query))
				{
					// must be fixed somehow Milosch????
					$myfilter = $this->makefilter($filterfields,$query,'',$DEBUG);
				}
				else
				{
					// don't search about any fields any more
					$search_filter = array(
						'fn'		=> 'cn',
						'n_given'	=> 'givenname',
						'n_family'	=> 'sn',
						'email'		=> 'mail',
						'org_name'	=> 'o',
						'org_unit'	=> 'ou'
					);
					$myfilter = $this->makefilter($filterfields,$search_filter,$query,$DEBUG);
				}
			}
			else
			{
				$myfilter = $this->makefilter($filterfields,'','',$DEBUG);
			}

			$sri = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_contact_context'], $myfilter);

			$ldap_fields = ldap_get_entries($this->ldap, $sri);
			/* _debug_array($ldap_fields);exit; */

			$this->total_records = ldap_count_entries($this->ldap, $sri);
			/* echo '<br>total="'.$this->total_records.'"'; */
			if($DEBUG) { echo '<br>Query returned "'.$this->total_records.'" records.'; }

			/* Use shared sorting routines, based on sort and order */
			@set_time_limit(0); /* Try not to die, this can take some time on slow machines... */
			if($sort == 'ASC')
			{
				$ldap_fields = $this->asortbyindex($ldap_fields, $this->stock_contact_fields[$order]);
			}
			else
			{
				$ldap_fields = $this->arsortbyindex($ldap_fields, $this->stock_contact_fields[$order]);
			}

			/*
			This logic allows you to limit rows, or not.
			The export feature, for example, does not limit rows.
			This way, it can retrieve all rows at once.
			*/
			if($start && $limit)
			{
				$limit = $start + $limit;
			}
			elseif($start && !$limit)
			{
				$limit = $start;
			}
			elseif(!$start && !$limit)
			{
				$limit = $this->total_records;
			}
			else
			{
				$start = 0;
				$limit = $limit;
			}
			/* echo '('.$start.','.$limit.')'; */

			@reset($ldap_fields);
			$j = 0;
			for($i=$start;$i<$limit;++$i)
			{
				if($i<$this->total_records && $ldap_fields[$i]['uid'][0])
				{
					$return_fields[$j]['id']     = $ldap_fields[$i]['uidnumber'][0];
					$return_fields[$j]['lid']    = $ldap_fields[$i]['uid'][0];
					$return_fields[$j]['tid']    = $ldap_fields[$i]['phpgwcontacttypeid'][0];
					$return_fields[$j]['owner']  = $ldap_fields[$i]['phpgwcontactowner'][0];
					$return_fields[$j]['access'] = $ldap_fields[$i]['phpgwcontactaccess'][0];
					$return_fields[$j]['cat_id'] = $ldap_fields[$i]['phpgwcontactcatid'][0];
					$return_fields[$j]['rights'] = (int)$this->grants[$return_fields[$j]['owner']];

					if(@is_array($stock_fieldnames))
					{
						foreach($stock_fieldnames as $f_name => $f_value)
						{
							$return_fields[$j][$f_name] = utf8_decode($ldap_fields[$i][$f_value][0]);
						}
					}
					$this->db->query("SELECT contact_name,contact_value FROM $this->ext_table WHERE contact_id='"
						. (int)$ldap_fields[$i]['uidnumber'] . "'",__LINE__,__FILE__);
					while($this->db->next_record())
					{
						if($extra_fields[$this->db->f('contact_name')])
						{
							$return_fields[$j][$this->db->f('contact_name')] = $this->db->f('contact_value');
						}
					}
					++$j;
				}
			}
			return $return_fields;
		}

		/* Used by read() above to build the ldap filter string */
		function makefilter($qarray,$extra='',$query='', $DEBUG=False)
		{
			if(!@is_array($qarray))
			{
				return $qarray;
			}

			$first = $last = "*";
			if(strstr($query,"*"))
			{
				if(substr($query,-1) == "*")
				{
					$last = '';
				}
				if(substr($query,1) == "*")
				{
					$first = '';
				}
			}

			if(@is_array($extra))
			{
				if($DEBUG) { echo '<br>Searching...'; }
				foreach($extra as $name => $value)
				{
					$qarray[] = array($value => $query);
				}
			}
			elseif($extra)
			{
				$tmp = preg_split('/=/',$extra);
				$qarray[] = array($tmp[0] => $tmp[1]);
			}

			@ksort($qarray);

			$aquery = '(&';
			$oquery = '(|';
			$hasor = False;

			foreach($qarray as $name => $value)
			{
				if(@is_array($value))
				{
					foreach($value as $x => $y)
					{
						if($y == '*')
						{
							$oquery .= '(' . $x . '=*)';
							$hasor = True;
						}
						elseif(@is_array($y))
						{
							/* This was most likely created from acl grants in read() above */
							foreach($y as $a => $b)
							{
								$tmp .= '(' . $a . '=' . $b . ')';
							}
						}
						else
						{
							$oquery .= '(' . $x . '=' . $first . $y . $last . ')';
							$hasor = True;
						}
					}
				}
				elseif($value == $query)
				{
					/* searching */
					$oquery .= '(' . $name . '=' . $first . $value . $last . ')';
					$hasor = True;
				}
				else
				{
					/* exact value (filtering based on tid, etc...) */
					if($name == 'phpgwcontactcatid')
					{
						if (!is_object($GLOBALS['phpgw']->categories))
						{
							$GLOBALS['phpgw']->categories = CreateObject('phpgwapi.categories');
						}
						$cats = $GLOBALS['phpgw']->categories->return_all_children((int)$value);

						$aquery .= '(|';
						foreach($cats as $cat)
						{
							$aquery .= '(' . $name . '=*,' . $cat . ',*)(' . $name . '=' . $cat . ')';
						}
						$aquery .= ')';
					}
					else
					{
						$aquery .= '(' . $name . '=' . $value . ')';
					}
				}

				if($tmp)
				{
					if(strstr($tmp,')('))
					{
						$aquery .= '(|' . $tmp . ')';
					}
					else
					{
						$aquery .= $tmp;
					}
					unset($tmp);
				}
			}
			$aquery .= ')';
			$oquery .= ')';
			if(!$hasor)
			{
				$oquery = '';
				$fquery = $aquery;
			}
			else
			{
				$fquery = '(&' . $aquery . $oquery . ')';
			}

			if($DEBUG)
			{
				echo '<br>AND query:  "' . $aquery . '"';
				echo '<br>OR query:   "' . $oquery . '"';
				echo '<br>Full query: "' . $fquery . '"';
				echo '<br>Will search in "' . $GLOBALS['phpgw_info']['server']['ldap_contact_context'] . '"';
			}

//			echo $fquery;
			return $fquery;
		}

		function add($owner,$fields,$access=NULL,$cat_id=NULL,$tid=NULL)
		{
			// access, cat_id and tid can be in $fields now or as extra params
			foreach(array('access','cat_id','tid') as $extra)
			{
				if(!is_null($$extra))
				{
					$fields[$extra] = $$extra;
				}
			}
			if(empty($fields['tid']))
			{
				$fields['tid'] = 'n';
			}

			if(!$GLOBALS['phpgw_info']['server']['ldap_contact_context'])
			{
				return False;
			}

			list($stock_fields,$stock_fieldnames,$extra_fields) = $this->split_stock_and_extras($fields);

			$free = 0;
			$this->nextid = $GLOBALS['phpgw']->common->last_id('contacts');
			/* Loop until we find a free id */
			while(!$free)
			{
				$ldap_fields = '';
				$sri = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_contact_context'], 'uidnumber='.$this->nextid);
				$ldap_fields = ldap_get_entries($this->ldap, $sri);
				if($ldap_fields[0]['dn'][0])
				{
					$this->nextid = $GLOBALS['phpgw']->common->next_id('contacts');
				}
				else
				{
					$free = True;
				}
			}

			$ldap_fields = '';
			if(@is_array($stock_fieldnames))
			{
				foreach($stock_fieldnames as $name => $value)
				{
					if($stock_fields[$name] != '')
					{
						$ldap_fields[$value] = utf8_encode($stock_fields[$name]);
					}
				}
			}

			$time = gettimeofday();
			$ldap_fields['uid'] = time().$time['usec'].':'.$ldap_fields['givenname'];

			$dn = 'uid=' . $ldap_fields['uid'].',' . $GLOBALS['phpgw_info']['server']['ldap_contact_context'];
			$ldap_fields['phpgwcontacttypeid']    = $fields['tid'];
			$ldap_fields['phpgwcontactowner']     = $owner;
			if(!isset($fields['access']))
			{
				$fields['access'] = 'private';
			}
			$ldap_fields['phpgwcontactaccess'] = $fields['access'];
			$ldap_fields['phpgwcontactcatid']  = $fields['cat_id'] ? $fields['cat_id'] : '0';
			$ldap_fields['uidnumber']      = $this->nextid;
			/* $ldap_fields['objectclass'][0] = 'person'; */
			$ldap_fields['objectclass'][0] = 'organizationalPerson';
			$ldap_fields['objectclass'][1] = 'inetOrgPerson';
			$ldap_fields['objectclass'][2] = 'phpgwContact';
			//$ldap_fields['last_mod'] = $GLOBALS['phpgw']->datetime->gmtnow;

			$err = $this->validate($ldap_fields);
			if(@is_array($err) && @isset($err[0]))
			{
				return $err;
			}
			// _debug_array($ldap_fields); exit;
			$err = ldap_add($this->ldap, $dn, $ldap_fields);
			if(!$err)
			{
				return False;
			}

			if(count($extra_fields))
			{
				foreach($extra_fields as $name => $value)
				{
					$this->db->query("INSERT INTO $this->ext_table VALUES ('".$this->nextid."','" . $this->account_id . "','"
						. addslashes($name) . "','" . addslashes($value) . "')",__LINE__,__FILE__);
				}
			}
			return $this->nextid;
		}

		/* LDAP syntaxes require some testing prior to add */
		function validate(&$entry)
		{
			$errors = array();
			foreach($entry as $field => $value)
			{
				if(strstr($field,'phone'))
				{
					/* Regex for testing valid international phone number entries.
					 * LDAP may reject bad values here, such as an email address in a phone number.
					 * This format is somewhat loose, allowing for optional parenthesis, + sign,
					 * and 0-7 numbers between separators.
					 */
					$regex = "/^[-0-9\+\(\)\/]/";
					if(!preg_match($regex,$value))
					{
						$errors[] = array($field => $value);
					}
				}
				elseif(strstr($field,'mailtype') || strstr($field,'mailhometype'))
				{
					/* Check for valid mail type */
					if(!@isset($this->email_types[$value]))
					{
						$errors[] = array($field => $value);
					}
				}
				elseif(strstr($field,'mail'))
				{
					/* Check for valid email address - TODO - should depend on mail type */
					$regex = "/[ |\t|\r|\n]*\"?([^\"]+\"?@[^ <>\t]+\.[^ <>\t][^ <>\t]+)[ |\t|\r|\n]*/x";
					if(!preg_match($regex,$value))
					{
						$errors[] = array($field => $value);
					}
				}
			}
			/* Verify sn/cn attrs set */
			if(empty($entry['sn']) && !empty($entry['cn']))
			{
				$entry['sn'] = $entry['cn'];
			}
			if(empty($entry['cn']) && !empty($entry['sn']))
			{
				$entry['cn'] = $entry['sn'];
			}
			$entry['cn'] = $entry['cn'] ? $entry['cn'] : '-';
			$entry['sn'] = $entry['sn'] ? $entry['sn'] : '-';

			return $errors;
		}

		function field_exists($id,$field_name)
		{
			$this->db->query("SELECT COUNT(*) FROM $this->ext_table where contact_id='" . (int)$id . "' AND contact_name='"
			. addslashes($field_name) . "'",__LINE__,__FILE__);
			$this->db->next_record();
			return $this->db->f(0);
		}

		function add_single_extra_field($id,$owner,$field_name,$field_value)
		{
			$this->db->query("INSERT INTO $this->ext_table VALUES (" . (int)$id . ",'$owner','" . addslashes($field_name)
				. "','" . addslashes($field_value) . "')",__LINE__,__FILE__);
		}

		function delete_single_extra_field($id,$field_name)
		{
			$this->db->query("DELETE FROM $this->ext_table WHERE contact_id='" . (int)$id . "' AND contact_name='"
				. addslashes($field_name) . "'",__LINE__,__FILE__);
		}

		function update($id,$owner,$fields,$access=NULL,$cat_id=NULL,$tid=NULL)
		{
			// access, cat_id and tid can be in $fields now or as extra params
			foreach(array('access','cat_id','tid') as $extra)
			{
				if(!is_null($$extra))
				{
					$fields[$extra] = $$extra;
				}
				if(isset($fields[$extra]))
				{
					$stock_fields[$extra] = $fields[$extra];
				}
			}
			$nonfields = $this->non_contact_fields;

			if(!$GLOBALS['phpgw_info']['server']['ldap_contact_context'])
			{
				return False;
			}

			/* First make sure that id number exists */
			$sri = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_contact_context'], 'uidnumber=' . (int)$id);
			$ldap_fields = ldap_get_entries($this->ldap, $sri);

			if($ldap_fields[0]['dn'])
			{
				$dn = $ldap_fields[0]['dn'];
				list($stock_fields,$stock_fieldnames,$extra_fields) = $this->split_stock_and_extras($fields);
				if(@is_array($stock_fieldnames))
				{
					/*
					Check each value, add our extra attributes if they are missing, and
					otherwise fix the entry while we can.
					*/
					/* Verify uidnumber */
					$stock_fields['id']   = $id;
					if(empty($ldap_fields[0]['uidnumber']))
					{
						$err = ldap_modify($this->ldap,$dn,array('uidnumber'  => $stock_fields['uidnumber']));
					}
					elseif(!$ldap_fields[0]['uidnumber'])
					{
						$err = ldap_mod_add($this->ldap,$dn,array('uidnumber' => $stock_fields['uidnumber']));
					}

					/* Verify uid */
					$uids = preg_split('/,/',$dn);
					$stock_fields['lid'] = $uids[0];
					if(empty($ldap_fields[0]['uid']))
					{
						$err = ldap_modify($this->ldap,$dn,array('uid'  => $stock_fields['lid']));
					}
					elseif(!$ldap_fields[0]['uid'])
					{
						$err = ldap_mod_add($this->ldap,$dn,array('uid' => $stock_fields['lid']));
					}

					/* Verify objectclasses are there */
					if(empty($ldap_fields[0]['objectclass']))
					{
						/* $stock_fields['objectclass'][0] = 'person'; */
						$stock_fields['objectclass'][0] = 'organizationalPerson';
						$stock_fields['objectclass'][1] = 'inetOrgPerson';
						$stock_fields['objectclass'][2] = 'phpgwContact';
						$err = ldap_modify($this->ldap,$dn,array('objectclass'  => $stock_fields['objectclass']));
					}
					elseif(!$ldap_fields[0]['objectclass'])
					{
						/* $stock_fields['objectclass'][0] = 'person'; */
						$stock_fields['objectclass'][0] = 'organizationalPerson';
						$stock_fields['objectclass'][1] = 'inetOrgPerson';
						$stock_fields['objectclass'][2] = 'phpgwContact';
						$err = ldap_mod_add($this->ldap,$dn,array('objectclass'  => $stock_fields['objectclass']));
					}

					/* Verify owner */
					$stock_fields['owner']  = $owner;
					if(empty($ldap_fields[0]['phpgwcontactowner']))
					{
						$err = ldap_modify($this->ldap,$dn,array('phpgwcontactowner'  => $stock_fields['owner']));
					}
					elseif(!$ldap_fields[0]['phpgwcontactowner'])
					{
						$err = ldap_mod_add($this->ldap,$dn,array('phpgwcontactowner' => $stock_fields['owner']));
					}

					/* Verify access */
					$stock_fields['access'] = $fields['access'];
					if(empty($ldap_fields[0]['phpgwcontactaccess']))
					{
						$err = ldap_modify($this->ldap,$dn,array('phpgwcontactaccess'  => $stock_fields['access']));
					}
					elseif(!$ldap_fields[0]['phpgwcontactaccess'])
					{
						$err = ldap_mod_add($this->ldap,$dn,array('phpgwcontactaccess' => $stock_fields['access']));
					}

					/* Verify cat_id */
					$stock_fields['cat_id']  = $fields['cat_id'] ? $fields['cat_id'] : ' ';
					if(empty($ldap_fields[0]['phpgwcontactcatid']))
					{
						$err = ldap_modify($this->ldap,$dn,array('phpgwcontactcatid'  => $stock_fields['cat_id']));
					}
					elseif(!$ldap_fields[0]['phpgwcontactcatid'])
					{
						$err = ldap_mod_add($this->ldap,$dn,array('phpgwcontactcatid' => $stock_fields['cat_id']));
					}

					/* Verify tid */
					$stock_fields['tid'] = $fields['tid'];
					if(empty($ldap_fields[0]['phpgwcontacttypeid']))
					{
						$err = ldap_modify($this->ldap,$dn,array('phpgwcontacttypeid'  => $stock_fields['tid']));
					}
					elseif(!$ldap_fields[0]['phpgwcontacttypeid'])
					{
						$err = ldap_mod_add($this->ldap,$dn,array('phpgwcontacttypeid' => $stock_fields['tid']));
					}

					/* OK, just mod the data already */
					$allfields = $stock_fieldnames + $nonfields;
					/* Don't try to modify the uid, since this affects the dn */
					unset($allfields['lid']);
					foreach($allfields as $fname => $fvalue)
					{
						if($ldap_fields[0][$fvalue] && $stock_fields[$fname] && $ldap_fields[0][$fvalue][0] != $stock_fields[$fname] )
						{
							//echo "<br>".$fname." => ".$fvalue." was there";
							$err = ldap_modify($this->ldap,$dn,array($fvalue => utf8_encode($stock_fields[$fname])));
						}
						elseif(!$ldap_fields[0][$fvalue] && $stock_fields[$fname])
						{
							//echo "<br>".$fname." not there - '".$fvalue."'";
							$err = ldap_mod_add($this->ldap,$dn,array($fvalue => utf8_encode($stock_fields[$fname])));
						}
						elseif($ldap_fields[0][$fvalue] && !$stock_fields[$fname])
						{
							//echo "<br>".$fname." gone...  deleting - '".$fvalue."'";
							/*
							NOTE: we use the ldap_fields because we need to send the
							_ORIGINAL_ contents as the value. see:
							http://www.php.net/manual/en/function.ldap-mod-del.php
							*/
							$err = ldap_mod_del($this->ldap,$dn,array($fvalue => $ldap_fields[0][$fvalue][0]));
						}
						/* Else we have nothing to do. */
					}
				}

				//something here to update the last_mod from $GLOBALS['phpgw']->datetime->gmtnow

				foreach($extra_fields as $x_name => $x_value)
				{
					if($this->field_exists($id,$x_name))
					{
						if(!$x_value)
						{
							$this->delete_single_extra_field($id,$x_name);
						}
						else
						{
							$this->db->query("UPDATE $this->ext_table SET contact_value='" . addslashes($x_value)
								. "',contact_owner='$owner' WHERE contact_name='" . addslashes($x_name)
								. "' AND contact_id='" . (int)$id . "'",__LINE__,__FILE__);
						}
					}
					else
					{
						$this->add_single_extra_field($id,$owner,$x_name,$x_value);
					}
				}
			}
			else
			{
				return False;
			}
		}

		/* Used by admin to change ownership on account delete */
		function change_owner($old_owner='',$new_owner='')
		{
			if(!($new_owner && $old_owner))
			{
				return False;
			}

			$sri = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_contact_context'], 'phpgwcontactowner='.$old_owner);
			$ldap_fields = ldap_get_entries($this->ldap, $sri);

			$entry = '';
			foreach($ldap_fields as $nul => $entry)
			{
				$err = ldap_modify($this->ldap,$entry['dn'],array('phpgwcontactowner' => $new_owner));
			}

			$this->db->query("UPDATE $this->ext_table SET contact_owner='$new_owner' WHERE contact_owner=$owner",__LINE__,__FILE__);
			return;
		}

		/* This is where the real work of delete() is done, shared class file contains calling function */
		function delete_($id)
		{
			if(!$GLOBALS['phpgw_info']['server']['ldap_contact_context'])
			{
				return False;
			}

			$sri = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_contact_context'], 'uidnumber='.$id);
			$ldap_fields = ldap_get_entries($this->ldap, $sri);

			if($ldap_fields[0]['dn'])
			{
				$err = ldap_delete($this->ldap,$ldap_fields[0]['dn']);

				$this->db->query("DELETE FROM $this->ext_table WHERE contact_id='" . (int)$id . "' AND contact_owner='"
					. $this->account_id . "'",__LINE__,__FILE__);
			}
			else
			{
				return False;
			}
		}

		// This is for the admin script deleteaccount.php
		function delete_all($owner=0)
		{
			if(!$GLOBALS['phpgw_info']['server']['ldap_contact_context'])
			{
				return False;
			}

			if($owner)
			{
				$sri = ldap_search($this->ldap, $GLOBALS['phpgw_info']['server']['ldap_contact_context'], 'phpgwcontactowner='.$owner);
				$ldap_fields = ldap_get_entries($this->ldap, $sri);

				$entry = '';
				foreach($ldap_fields as $nul => $entry)
				{
					$err = ldap_delete($this->ldap,$entry['dn']);
				}

				$this->db->query("DELETE FROM $this->ext_table WHERE contact_owner=$owner",__LINE__,__FILE__);
			}
			return;
		}
	}
?>
