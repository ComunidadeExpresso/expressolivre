<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  sponsored by Thyamad - http://www.thyamad.com
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	/*
	  Set a global flag to indicate this file was found by admin/config.php.
	  config.php will unset it after parsing the form values.
	*/
	$GLOBALS['phpgw_info']['server']['found_validation_hook'] = True;

	/* Check all settings to validate input.  Name must be 'final_validation' */
	function final_validation(&$value='')
	{
		$error = false;
		
/*     	if($value['cc_global_source0'] == 'ldap')
		{
			if (!$value['cc_ldap_host0'])
			{
				$error[] = '<br>LDAP host must be set!';
			}
			
			if(!$value['cc_ldap_context0'])
			{
				$error[] = '<br>There must be a Context';
			}
		
			if(!$value['cc_ldap_browse_dn0'])
			{
				$error[] = '<br>The Browse Account must be set';
			}	

			if ($value['cc_ldap_pw0'] != $_POST['cc_ldap_pw0']) 
			{
				$error[] = '<br>Invalid LDAP Password!';
			}

		} */		

		/* Check if the password field is blank, then discard changes */ 
		foreach ($value as $attr_name => $attr_value) {
			if ($attr_value == '') {
				$v      = explode("_", $attr_name);
				$nums[] = $v[3];
			}
		}
		
		foreach ($nums as $num) {
			//Deleta toda a tupla que contem o atributo vazio
			foreach ($value as $attr_name_x => $attr_value_x) {	
				if (strpos($attr_name_x, $num) !== false) {
					if (strpos($attr_name_x, 'cc_attribute_name') !== false)
						//$error[] = "<br/>" . lang("Could not find the LDAP attribute pointed by") . "  " . $attr_value_x;	
					
					unset($value[$attr_name_x]);	
					
				}
			}
		}
		
		if ($error)
		{ 
			$GLOBALS['config_error'] = implode("\n", $error);
		}
	}

	
	/*
	 * @function cc_allow_details
	 * @abstract Recebe o valor do checkbox e força a saída para TRUE ou FALSE
	 * @author Prognus software livre - http://www.prognus.com.br | prognus@prognus.com.br
	 * @param $value
	 */
	function cc_allow_details(&$value) {
		if ($value == 'details') {
			$value = "true";
		} else {
			$value = "false";
		}
	}
	
	
	
	/*
	 * @function cc_attribute_ldapname
	 * @abstract valida campo "Correspondente LDAP" se é um campo ldap válido
	 * @author Prognus Software Livre - http://www.prognus.com.br | prognus@prognus.com.br
	 * @param $value
	 */
	function cc_attribute_ldapname(&$value) {
		/*
		//Retirada a validação dos campos correspondentes no LDAP.
		$ldap = CreateObject('contactcenter.bo_ldap_manager');
		$ds = $GLOBALS['phpgw']->common->ldapConnect($ldap->srcs[1]['host'], $ldap->srcs[1]['acc'], $ldap->srcs[1]['pw'], true);				
		$dn=$ldap->srcs[1]['dn'];
		$justThese = array('uid', 'cn', $value);
		$sr = ldap_search($ds, $dn, "($value=*)", $justThese, 0, 1);
		$info = ldap_get_entries($ds, $sr);
		
		if (!$info)  
			$value = "";
		ldap_close($ds);
		*/
	}
?>
