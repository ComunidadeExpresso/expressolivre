(function()
{
	var _conn		= '';
	var _keyTimeOut = false;
	var Xtools		= '';

	function addGroup()
	{
		if( arguments.length > 0 )
		{
			var select_from	= document.getElementById(arguments[0]);
			var select_to	= document.getElementById(arguments[1]);
			
			for(var i = 0; i < select_to.options.length; i++)
			{
				if(select_to.options[i].value === "")
				{
					select_to.options[i].parentNode.removeChild(select_to.options[i]);
					i--;
				}
			}
			
			for(var i = 0; i < select_from.options.length ; i++)
			{
				if( select_from.options[i].selected )
				{
					
					var flag = false;
					for(var j = 0; j < select_to.options.length ; j++ )
					{
						if( select_to.options[j].value === select_from.options[i].value )
							flag = true;
					}
					
					if ( !flag )
					{					
						var option = select_from.options[i].value.split(":");
						select_to.options[select_to.length] = new Option(option[0], select_from.options[i].value, false, true);
					}
				}
			}
		}
	}

	function createObject()
	{
		if ( typeof(_conn) != "object" )
			_conn   = new JITConnector(path_jabberit + 'jabberit_messenger/');				

		if ( typeof(Xtools) != "object" )
			Xtools = new xtools(path_jabberit + 'jabberit_messenger/');
	}

	function CompleteSelect(data)
	{
		var select_ldap = document.getElementById('groups_ldap_jabberit');
		data = Xtools.convert(data);

		while( select_ldap.hasChildNodes())
			select_ldap.removeChild(select_ldap.firstChild);
		
		try
		{
			if ( data && data.documentElement && data.documentElement.hasChildNodes() )
			{
				data = data.documentElement.firstChild;
				var label = "";
				var value = "";
				
				while(data)
				{
					var no = data.firstChild;
					while(no)
					{
						if( label == "" )
							var label = no.firstChild.nodeValue;
						else
							var value = no.firstChild.nodeValue;
						no = no.nextSibling;
					}
					select_ldap.options[select_ldap.options.length] = new Option(label,label + ":" + value, false, false);
					label = value = ""; 
					data = data.nextSibling;
				}
			}
		}catch(e){}
	}

	function groupsLdap()
	{
		var element			= null;
		var organization 	= "";
		var serverLdap		= null;

		createObject();
		
		if( arguments.length > 0 )
		{
			element		= arguments[0];
			serverLdap	= element.getAttribute('serverLdap');
			
			if( element.options.length > 0 )
			{
				for(var i = 0; i < element.options.length ; i++ )
				{
					if( element.options[i].selected )
					{		
						organization =  'ou=' + element.options[i].value;
						organization = ( serverLdap != null ) ? organization + "&serverLdap=" + serverLdap : organization ;
					}
				}
			}
			_conn.go('$this.ldap_im.getGroupsLdap', CompleteSelect, organization);
		}
	}

	function groupsQuickSearch()
	{
		if( arguments.length > 0 )
		{
			var key = [8,27,37,38,39,40];
			var ev = arguments[1];
			var elGroups 	= arguments[0];
			var labelGroups = "label_" + elGroups.id;
			var search		= "search="+elGroups.value+"&serverLdap=" + document.getElementById(labelGroups).getAttribute('serverLdap');
			
			var cleanLabel = function(Id)
			{
				document.getElementById(Id).innerHTML = "";
			}
			
			var getGroups = function(search, Id)
			{
				createObject();
				
				_conn.go('$this.ldap_im.getGroupsLdap',CompleteSelect , search);

				cleanLabel(Id);
			}

			for(var i in key)
				if( ev.keyCode == key[i])
					return false;

			if( elGroups.value.length < 4 )
			{
				document.getElementById(labelGroups).innerHTML = " ( Digite mais " + ( 4 - elGroups.value.length ) + " )";
				setTimeout(function(){cleanLabel(labelGroups);}, 2000);
			}
			else
			{
				document.getElementById(labelGroups).innerHTML = " ( Buscando aguarde .... )";
				
				if( _keyTimeOut )
					clearTimeout(_keyTimeOut);

				_keyTimeOut = setTimeout(function(){ getGroups(search, labelGroups); }, 1000);
			}	
		}
	}
	
	function Selected()
	{
		if( arguments.length > 0 )
		{
			var _select = document.getElementById(arguments[0]);
			
			for( var i = 0 ; i < _select.options.length; i++ )
				_select[i].selected = true;
		}
	}
	
	function removeGroup()
	{
		if( arguments.length > 0 )
		{
			var _select = document.getElementById(arguments[0]);
			
			for( var i = 0 ; i < _select.options.length; i++ )
			{
				if( _select.options[i].selected )
				{
					_select.options[i].parentNode.removeChild( _select.options[i] );
					i--;
				}
			}
		}
	}
	
	function groups_ldap(){}

	groups_ldap.prototype.add			= addGroup;
	groups_ldap.prototype.remove		= removeGroup;
	groups_ldap.prototype.groups		= groupsLdap;
	groups_ldap.prototype.quickSearch	= groupsQuickSearch;
	groups_ldap.prototype.selectAll		= Selected;
	window.groups_ldap					= new groups_ldap;
	
})();