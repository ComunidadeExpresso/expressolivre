(function()
{
	
	function addApplication()
	{
		var select_appList = document.getElementById('apps_list');
		for(var i = 0; i < select_appList.options.length ; i++)
			if( select_appList.options[i].selected )
			{
				var select_appEnabled =  document.getElementById('apps_enabled');
				var flag = false;
				for(var j = 0; j < select_appEnabled.options.length ; j++ )
				{
					if( select_appEnabled.options[j].value === select_appList.options[i].value )
						flag = true;
				}
				
				if ( !flag )
					select_appEnabled.options[select_appEnabled.length] = new Option(select_appList.options[i].text, select_appList.options[i].value, false, true);
			}
	}
	
	function removeApplication()
	{
		var select_appEnabled = document.getElementById('apps_enabled');
		
		for(var i = 0 ; i < select_appEnabled.options.length; i++ )
			if( select_appEnabled.options[i].selected )
			{
				select_appEnabled.options[i].parentNode.removeChild(select_appEnabled.options[i]);
				i--;
			}
	}
	
	function Selected()
	{
		var select_appEnabled = document.getElementById('apps_enabled');
		for(var i = 0 ; i < select_appEnabled.options.length; i++ )
			select_appEnabled.options[i].selected = true;
	}
	
	function AppJabberit(){}

	AppJabberit.prototype.add		= addApplication;
	AppJabberit.prototype.remove	= removeApplication;
	AppJabberit.prototype.select_	= Selected; 
	window.App = new AppJabberit;
	
})();