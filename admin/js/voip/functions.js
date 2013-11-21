(function()
{
	var _conn = '';
	var _xtools = '';

	function addGroup()
	{
		var select_ldap = document.getElementById('groups_ldap');
		for(var i = 0; i < select_ldap.options.length ; i++)
			if( select_ldap.options[i].selected )
			{
				var select_voip =  document.getElementById('groups_voip');
				var flag = false;
				for(var j = 0; j < select_voip.options.length ; j++ )
				{
					if( select_voip.options[j].value === select_ldap.options[i].value )
						flag = true;
				}
				if ( !flag ) {					
					var option = select_ldap.options[i].value.split(";");
					select_voip.options[select_voip.length] = new Option(option[0], select_ldap.options[i].value, false, true);
				}
			}
	}
	
	function createObject()
	{
		if ( typeof(_conn) != "object")
			_conn = new ADMConnector(path_adm + 'admin' );	

		if ( typeof(_xtools) != "object" )
			_xtools = new ADMXTools(path_adm + 'admin');
	}

	function CompleteSelect(data)
	{
		var select_ldap = document.getElementById('groups_ldap');
		data = _xtools.convert(data);

		while( select_ldap.hasChildNodes())
			select_ldap.removeChild(select_ldap.firstChild);
		
		try
		{
			if ( data && data.documentElement && data.documentElement.hasChildNodes() )
			{
				data = data.documentElement.firstChild;
				
				while(data)
				{
					var option = data.firstChild.nodeValue.split(";");
					select_ldap.options[select_ldap.options.length] = new Option(option[0],data.firstChild.nodeValue, false, false); 
					data = data.nextSibling;
				}
			}
		}catch(e){}

		styleVisible('hidden');
	}
	
	function LTrim(value)
	{
		var w_space = String.fromCharCode(32);
		var strTemp = "";
		var iTemp = 0;
		
		if(v_length < 1)
			return "";
	
		var v_length = value ? value.length : 0;
		
		while(iTemp < v_length)
		{
			if(value && value.charAt(iTemp) != w_space)
			{
				strTemp = value.substring(iTemp,v_length);
				break;
			}
			iTemp++;
		}	
		return strTemp;
	}
	
	
	function SearchOu()
	{
		createObject();
		var organization = "";
		
		if( arguments.length > 0 )
		{
			var element = arguments[0];
			styleVisible('visible');
		}

		if( element.options.length > 0 )
			for(var i = 0; i < element.options.length ; i++ )
				if( element.options[i].selected )
					organization = 'ou=' + element.options[i].value;

		_conn.go('$this.bovoip.getGroupsLdap', CompleteSelect, organization);
	}
	
	function Selected()
	{
		var select_voip = document.getElementById('groups_voip');
		for( var i = 0 ; i < select_voip.options.length; i++ )
			select_voip.options[i].selected = true;
	}

	function styleVisible(pVisible)
	{
		document.getElementById('admin_span_loading').style.visibility = pVisible;
	}
	
	function removeGroup()
	{
		var select_voip = document.getElementById('groups_voip');
		
		for(var i = 0 ; i < select_voip.options.length; i++ )
			if( select_voip.options[i].selected )
			{
				select_voip.options[i].parentNode.removeChild(select_voip.options[i]);
				i--;
			}
	}
	
	function validateEmail()
	{
		if( arguments.length > 0 )
		{
			var element = arguments[0];
			var validate = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			
			if(LTrim(element.value) != "" && element.value != "")
			{
				if(!validate.test(element.value))
				{
					alert('Email field is not valid' + '.');
					element.focus();
					return false;
				}
			}
		}
	}

	function Voip()
	{
	
	}

	Voip.prototype.search	= SearchOu;
	Voip.prototype.add		= addGroup;
	Voip.prototype.remove	= removeGroup;
	Voip.prototype.select_	= Selected;
	Voip.prototype.validateEmail = validateEmail;
	window.voip = new Voip;

})();
