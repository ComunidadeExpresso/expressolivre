<?php

/* * ************************************************************************\
 * phpGroupWare API - Accounts manager for LDAP                             *
 * Written by Joao Alfredo Knopik Junior <jakje@celepar.pr.gov.br>          *
 * View and manipulate account records using LDAP                           *
 * Copyright (C) 2000 - 2002 Joseph Engo, Lars Kneschke                     *
 * Copyright (C) 2003 Lars Kneschke, Bettina Gille                          *
 * ------------------------------------------------------------------------ *
 * This library is part of the phpGroupWare API                             *
 * http://www.phpgroupware.org                                              *
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
  \************************************************************************* */

include_once('class.common.inc.php');

class sector_search_ldap {

    var $common;
    var $ldap_connection;
    var $sector_name;
    var $sector_context;
    var $sector_level;
    var $sector_leaf;

    function sector_search_ldap($sector_name="", $sector_context="", $sector_level="", $sector_leaf="", $ldap_conn="") {
	if ($ldap_conn == "") {
	    $this->common = new common;
	    $this->ldap_connection = $this->common->ldapConnect();
	}
	else
	    $this->ldap_connection = $ldap_conn;

	$this->sector_name = $sector_name;
	$this->sector_context = $sector_context;
	$this->sector_level = $sector_level;
	$this->sector_leaf = $sector_leaf;
    }

    // All levels of ou´s, organizations (first level) and sectors (rest levels)
    function sector_search($ldap_context, $clear_static_vars=true) {
	static $sectors_list = array();
	static $level = 0;

	if ($clear_static_vars) {
	    $sectors_list = array();
	    $level = 0;
	}

	$filter = "objectClass=organizationalUnit";
	$justthese = array("ou");
	$sr = ldap_list($this->ldap_connection, $ldap_context, $filter, $justthese);
	ldap_sort($this->ldap_connection, $sr, "ou");
	$info = ldap_get_entries($this->ldap_connection, $sr);

	for ($i = 0; $i < $info["count"]; ++$i) {
	    ++$level;
	    $next_ldap_context[$i] = $info[$i]["dn"];

	    $obj = new sector_search_ldap($info[$i]["ou"][0], $next_ldap_context[$i], $level, 'False', $this->ldap_connection);
	    array_push($sectors_list, $obj);

	    $this->sector_search($next_ldap_context[$i], false);
	}
	$level--;
	return $sectors_list;
    }

    // Just the first level, or the organizations
    function organization_search($ldap_context) {
	$filter = "objectClass=organizationalUnit";
	$justthese = array("ou");

	$sr = ldap_list($this->ldap_connection, $ldap_context, $filter, $justthese);
	$info = ldap_get_entries($this->ldap_connection, $sr);

	if ($info["count"] == 0) {
	    $sectors_list[0] = $ldap_context;
	} else {

	    for ($i = 0; $i < $info["count"]; ++$i) {
		$sectors_list[$i] = $info[$i]["ou"][0];
	    }
	}
	sort($sectors_list);
	return $sectors_list;
    }

    // Retorna os organizações com os options prontos
    // Parametro master: realiza ldap_connect utilizando dados do Contact Center, permitindo buscas completas em todos os ldaps.
    function get_organizations($context, $selected='', $referral=false, $show_invisible_ou=false, $master=false) {
	if ($master) {
	    $ldap_cc_info = CreateObject('contactcenter.bo_ldap_manager');
	    $ldap_cc_info = $ldap_cc_info ? $ldap_cc_info->srcs[1] : null;
	    $dn = $ldap_cc_info['acc'];
	    $passwd = $ldap_cc_info['pw'];
	    $host = $ldap_cc_info['host'];
	} else {
	    $dn = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
	    $passwd = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
	    $host = $GLOBALS['phpgw_info']['server']['ldap_host'];
	}

	$ldap_conn = ldap_connect($host);

	ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);

	if ($referral)
	    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 1);
	else
	    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

	@ldap_bind($ldap_conn, $dn, $passwd);

	$justthese = array("dn");
	$filter = $show_invisible_ou ? "(objectClass=organizationalUnit)" : "(& (objectClass=organizationalUnit) (!(phpgwAccountVisible=-1)) )";
	$search = @ldap_search($ldap_conn, $context, $filter, $justthese);

	@ldap_sort($ldap_conn, $search, "ou");
	$info = @ldap_get_entries($ldap_conn, $search);
	ldap_close($ldap_conn);

	// Retiro o count do array info e inverto o array para ordenação.

	if ($info["count"] == 0) {
	    return $options = "<option value='$context' selected=\"selected\"> $context </option>";
	}

	for ($i = 0; $i < $info["count"]; ++$i) {
	    $dn = $info[$i]["dn"];

	    // Necessário, pq em uma busca com ldapsearch objectClass=organizationalUnit, traz tb o próprio ou.
	    if (strtolower($dn) == $context)
		continue;

	    $array_dn = ldap_explode_dn($dn, 1);

	    $array_dn_reverse = array_reverse($array_dn, true);

	    // Retirar o indice count do array.
	    array_pop($array_dn_reverse);

	    $inverted_dn[$dn] = implode("#", $array_dn_reverse);
	}

	if (is_array($inverted_dn)) {
	    // Ordenação
	    natcasesort($inverted_dn);

	    // Construção do select
	    $level = 0;
	    foreach ($inverted_dn as $dn => $invert_ufn) {
		$display = '';

		$array_dn_reverse = explode("#", $invert_ufn);
		$array_dn = array_reverse($array_dn_reverse, true);

		$level = count($array_dn) - (int) (count(explode(",", $GLOBALS['phpgw_info']['server']['ldap_context'])) + 1);

		if ($level == 0)
		    $display .= '+';
		else {
		    for ($i = 0; $i < $level; ++$i)
			$display .= '---';
		}

		reset($array_dn);
		$display .= ' ' . (current($array_dn) );

		$dn = trim(strtolower($dn));
		if ($dn == $selected)
		    $select = ' selected';
		else
		    $select = '';
		$options .= "<option value='$dn'$select>$display</option>";
	    }
	}

	// Cria a primeira entrada na combo
	$first_sector_ufn = ldap_dn2ufn($context);
	$first_sector_string = preg_split('/,/', $first_sector_ufn);

	if ($context == $selected)
	    $select_first_entrie = ' selected';
	$options = "<option value='$context'$select_first_entrie>+ " . strtoupper($first_sector_string[0]) . "</option>" . $options;

	return $options;
    }

    // Retorna os setores (somente OUs de primeiro nivel) com as options prontas
    function get_sectors($selected='', $referral=false, $show_invisible_ou=false) {
	$dn = $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
	$passwd = $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
	$context = $GLOBALS['phpgw_info']['server']['ldap_context'];
	$ldap_conn = ldap_connect($GLOBALS['phpgw_info']['server']['ldap_host']);

	ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);

	if ($referral)
	    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 1);
	else
	    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

	ldap_bind($ldap_conn, $dn, $passwd);

	$justthese = array("dn", "ou");
	$filter = $show_invisible_ou ? "(objectClass=organizationalUnit)" : "(&(objectClass=organizationalUnit)(!(phpgwAccountVisible=-1)))";

	$search = ldap_list($ldap_conn, $context, $filter, $justthese);

	ldap_sort($ldap_conn, $search, "ou");
	$info = ldap_get_entries($ldap_conn, $search);

	ldap_close($ldap_conn);
	$options = '';

	if ($info["count"] == 0) {
	    return $options = "<option value='$context' selected> $context </option>";
	}

	for ($i = 0; $i < $info["count"]; ++$i) {
	    $dn = trim(strtolower($info[$i]['dn']));
	    if ($dn == $selected)
		$select = ' selected';
	    else
		$select = '';
	    $display = strtoupper($info[$i]['ou'][0]);
	    $options .= "<option value='$dn'$select>$display</option>";
	}

	return $options;
    }

}

?>
