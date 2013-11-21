<?php
 /***************************************************************************\
  *  Expresso - Expresso Messenger                                            *
  *  	- Alexandre Correia / Rodrigo Souza							          *
  *  	- JETI - http://jeti-im.org/										  *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

class fileDefine
{
	public final function ldapExternal($pConfLdap)
	{
		$file = "confLDAPExternal.php";
		$writeFile = "<?php $"."LDAP_EXTERNAL="."\"".base64_encode(serialize($pConfLdap))."\""." ?>";
		
		return $this->writeFile($writeFile, $file);
	}

	public final function ldapInternal($pParam)
	{
		$array_values = explode("\n", $pParam['val']);	
		$file = "confLDAPInternal.php";
		$infoServer = array();
		
		foreach($array_values as $tmp )
		{
			$nvalue = explode(";", $tmp);
			
			switch(trim($nvalue[0]))
			{
				case "JETTI_NAME_JABBERIT" :
						$infoServer['jabberName'] = $nvalue[1];				
						break;
						
				case "JETTI_SERVER_LDAP_JABBERIT" :
						$infoServer['serverLdap'] = $nvalue[1];							
						break;			
			
				case "JETTI_CONTEXT_LDAP_JABBERIT" :	
						$infoServer['contextLdap'] = $nvalue[1];
						break;
			
				case "JETTI_USER_LDAP_JABBERIT" :
						$infoServer['user'] = $nvalue[1];
						break;
			
				case "JETTI_PASSWORD_LDAP_JABBERIT" :
						$infoServer['password'] = $nvalue[1];				
						break;
			}
		}
		
		$writeFile = "<?php $"."LDAP_INTERNAL="."\"".base64_encode(serialize($infoServer))."\""." ?>";
		
		return $this->writeFile( $writeFile, $file );
	}

	private final function writeFile($pContent, $pfile)
    {
		$filename = dirname(__FILE__).'/'.$pfile;
		$content = $pContent;
			
		if ( !$handle = fopen($filename, 'w+') )
			return false;
		
		if (fwrite($handle, $content) === FALSE)
			return false;

		fclose($handle);
		
		return true;
    }
}
?>