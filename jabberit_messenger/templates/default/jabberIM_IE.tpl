<!-- BEGIN index -->
<html> 
	<head>
	<meta HTTP-EQUIV="Pragma" CONTENT="no-cache">
	<meta HTTP-EQUIV="Expires" CONTENT="-1">
	<meta HTTP-EQUIV="content-type" CONTENT="application/x-java-archive"> 
	<script type="text/javascript" src="../jabberit_messenger/js/changeStatus.js"></script>
	<script type="text/javascript">
		
		// Pop-up or Layer
		if( window.parent.loadscript )
			var element = window.parent.loadscript;
		else
			var element = self.opener.parent.loadscript;

		changestatus.setpath('{path}');
		
		function getArgumentsApplet()
		{
			try
			{
				if( arguments.length > 0 )
				{
				 	if( arguments[0] == "getArgumentsApplet" )
						changestatus.get(arguments[1], element.getElement());
	
					if( arguments[0] == "cleanStatus" )
						element.autoStatusIM();
				}
			}
			catch(e){}
		}
		
	</script>
	</head>
	<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
		<object classid='clsid:8AD9C840-044E-11D1-B3E9-00805F499D93' width='250px' height='400px'>
			<param name='name' value='jeti'>
			<param name='archive' value='{java_files}'>
			<param name='code' value='{value_codeBase}'>
			<param name='codebase' value='.'>	
			<param name='FIELD01' value='{value_cnname}'>
			<param name='FIELD02' value='{value_country}'>
			<param name='FIELD03' value='{value_expresso}'>
			<param name='FIELD04' value='javascript:window.close();'>			
			<param name='FIELD05' value='{value_host}'>
			<param name='FIELD06' value='{value_javaPlugins}'>
			<param name='FIELD07' value='{value_language}'>
			<param name='MAYSCRIPT' value='true'>
			<param name='FIELD08' value='{value_company}'>
			<param name='FIELD09' value='{value_password}'>
			<param name='FIELD10' value='{value_port}'>
			<param name='FIELD11' value='{value_resource}'>
			<param name='FIELD12' value='{value_server}'>			
			<param name='FIELD13' value='{value_ssl}'>
			<param name='FIELD14' value='{value_use_https}'>
			<param name='FIELD15' value='{value_userproxy}'>
			<param name='FIELD16' value='{value_user}'>
			<param name='FIELD17' value='{value_mc}'>			
		</object>
	</body>
</html>
<!-- END index -->