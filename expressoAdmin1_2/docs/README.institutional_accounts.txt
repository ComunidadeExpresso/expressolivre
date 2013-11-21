

On the file /etc/postfix/main.cf complete the above line:

virtual_alias_maps = ..., ldap:institutionalAccounts


And include block too:

# Institutional Accounts
institutionalAccounts_server_host   	= ldap://ldap.foo.com
institutionalAccounts_version       	= 3
institutionalAccounts_timeout       	= 10
institutionalAccounts_chase_referral	= 0 
institutionalAccounts_search_base   	= <YOUR_BASE_DN>
institutionalAccounts_query_filter  	= (&(mail=%s)(phpgwAccountType=i)(objectClass=phpgwAccount)(accountStatus=active))
institutionalAccounts_domain        	= hash:/etc/postfix/expresso-dominios
institutionalAccounts_result_attribute  = mailForwardingAddress
institutionalAccounts_bind      		= no