/****************************************** Public variables *************************************************/
var debug_controller = false;
var files = new Array();
var progressBar;

  if (document.all)
	{
		navigator.userAgent.toLowerCase().indexOf('msie 5') != -1 ? is_ie5 = true : is_ie5 = false;
		is_ie = true;
		is_moz1_6 = false;
		is_mozilla = false;
		is_ns4 = false;
	}
	else if (document.getElementById)
	{
		navigator.userAgent.toLowerCase().match('mozilla.*rv[:]1\.6.*gecko') ? is_moz1_6 = true : is_moz1_6 = false;
		is_ie = false;
		is_ie5 = false;
		is_mozilla = true;
		is_ns4 = false;
	}
	else if (document.layers)
	{
		is_ie = false;
		is_ie5 = false
		is_moz1_6 = false;
		is_mozilla = false;
		is_ns4 = true;
	}	

/****************************************** Connector Class *************************************************/
	// Constructor
	function cConnector()
	{
		this.requests = new Array();
		this.oxmlhttp = null;
		this.isVisibleBar = false;
		this.tid = 0;
		this.progressBar = null;
		this.oldX = 0;
		this.oldY = 0;
	}
	
	cConnector.prototype.buildBar = function()
		{			
			var div = document.getElementById('divProgressBar');
		
			if(! div) {
			try{
				div = document.createElement("DIV");
				div.style.visibility	= "hidden";		
				div.style.width = "103px";
				div.id = 'divProgressBar';
				div.align = "center";
				div.innerHTML = '&nbsp;&nbsp;<font face="Verdana" size="2" color="WHITE">Carregando...</font>&nbsp;';
				div.style.background = "#cc4444";
				div.style.position = 'fixed';
				div.style.top = '0px';
				div.style.right = '0px';
				document.body.appendChild(div);																
				
				if(is_ie) {
					var elem = document.all[div.id]; 
					elem.style.position="absolute";
					var root = document.body;
					var posX = elem.offsetLeft-root.scrollLeft;
					var posY = elem.offsetTop-root.scrollTop;
					root.onscroll = function() {
						elem.style.right = '0px';
						elem.style.top = (posY + root.scrollTop) + "px";
					};					
				}
				
				if(debug_controller) {
					div = document.createElement("DIV");
					div.style.width	= "800px";
					div.style.height= "400px";
					div.id = "debug_controller";
					div.align='right';
					document.body.appendChild(div);																
				}
			}
			catch(e){}
			}								
	}	
//------------------------------------ BEGIN: Functions for Connector HTTPRequest  -------------------------------------------------//	
	// Serialize Data Method
	cConnector.prototype.serialize = function(data)
	{	var _thisObject = this;		
		var f = function(data)
		{
			var str_data;
	
			if (data == null || 
				(typeof(data) == 'string' && data == ''))
			{
				str_data = 'N;';
			}
	
			else switch(typeof(data))
			{
				case 'object':
					var arrayCount = 0;
	
					str_data = '';
	
					for (i in data)
					{
						if (i == 'length')
						{
							continue;
						}
						
						arrayCount++;
						switch (typeof(i))
						{
							case 'number':
								str_data += 'i:' + i + ';' + _thisObject.serialize(data[i]);
								break;
	
							case 'string':
								str_data += 's:' + i.length + ':"' + i + '";' + _thisObject.serialize(data[i]);
								break;
	
							default:
								showMessage(Element('cc_msg_err_serialize_data_unknown').value);
								break;
						}
					}
	
					if (!arrayCount)
					{
						str_data = 'N;';	
					}
					else
					{
						str_data = 'a:' + arrayCount + ':{' + str_data + '}';
					}
					
					break;
			
				case 'string':
					str_data = 's:' + data.length + ':"' + data + '";';
					break;
					
				case 'number':
					str_data = 'i:' + data + ';';
					break;
	
				case 'boolean':
					str_data = 'b:' + (data ? '1' : '0') + ';';
					break;
	
				default:
					showMessage(Element('cc_msg_err_serialize_data_unknown').value);
					return null;
			}
	
			return str_data;
		}
	
		var sdata = f(data);
		return sdata;
	}
	cConnector.prototype.matchBracket = function(strG, iniPosG)
	{
		_thisObject = this;
		var f = function (str, iniPos)
		{
			var nOpen, nClose = iniPos;
		
			do 
			{
				nOpen = str.indexOf('{', nClose+1);
				nClose = str.indexOf('}', nClose+1);

				if (nOpen == -1)
				{
					return nClose;
				}
			
				if (nOpen < nClose )
				{
					nClose = _thisObject.matchBracket(str, nOpen);
				}
			
			} while (nOpen < nClose);

			return nClose;
		}

		return f(strG, iniPosG);
	}
	
		
	//Unserialize Data Method
	cConnector.prototype.unserialize = function(str)
	{
		
		_thisObject = this;
		
		var f = function (str)
		{
			switch (str.charAt(0))
			{
				case 'a':
					
					var data = new Array();
					var n = parseInt( str.substring( str.indexOf(':')+1, str.indexOf(':',2) ) );
					var arrayContent = str.substring(str.indexOf('{')+1, str.lastIndexOf('}'));
				
					for (var i = 0; i < n; i++)
					{
						var pos = 0;
	
						/* Process Index */
						var indexStr = arrayContent.substr(pos, arrayContent.indexOf(';')+1);
						var index = _thisObject.unserialize(indexStr);
						pos = arrayContent.indexOf(';', pos)+1;
						
						/* Process Content */
						var part = null;
						switch (arrayContent.charAt(pos))
						{
							case 'a':
								var pos_ = _thisObject.matchBracket(arrayContent, arrayContent.indexOf('{', pos))+1;
								part = arrayContent.substring(pos, pos_);
								pos = pos_;
								data[index] = _thisObject.unserialize(part);
								break;
						
							case 's':
								var pval = arrayContent.indexOf(':', pos+2);
								var val  = parseInt(arrayContent.substring(pos+2, pval));
								pos = pval + val + 4;
								data[index] = arrayContent.substr(pval+2, val);
								break;
	
							default:
								part = arrayContent.substring(pos, arrayContent.indexOf(';', pos)+1);
								pos = arrayContent.indexOf(';', pos)+1;
								data[index] = _thisObject.unserialize(part);
								break;
						}
						arrayContent = arrayContent.substr(pos);
					}
					break;
					
				case 's':
					var pos = str.indexOf(':', 2);
					var val = parseInt(str.substring(2,pos));
					var data = str.substr(pos+2, val);
					str = str.substr(pos + 4 + val);
					break;
	
				case 'i':
				case 'd':
					var pos = str.indexOf(';');
					var data = parseInt(str.substring(2,pos));
					str = str.substr(pos + 1);
					break;
				
				case 'N':
					var data = null;
					str = str.substr(str.indexOf(';') + 1);
					break;
	
				case 'b':
					var data = str.charAt(2) == '1' ? true : false;
					break;
			}
			
			return data;
		}
	
		return f(str);
	}

	//Create XMLHTTP object Method
	cConnector.prototype.createXMLHTTP = function ()
	{	
		try
		{ 
			this.oxmlhttp = new XMLHttpRequest();
			this.oxmlhttp.overrideMimeType('text/xml');
		}
		catch (e)
		{ 
			try
			{
				this.oxmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
			}
			catch (e1)
			{ 
				try
				{
					this.oxmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
				}
				catch (e2)
				{
					this.oxmlhttp = null;
				}
			}
		}
	
	}
	
	// Request Constructor Connector	
	cConnector.prototype.newRequest = function (id, target, method, handler, data)
	{
		this.tid = id;
		if (this.requests[id]) {
			return false;
		}
	
		this.createXMLHTTP();
		var oxmlhttp = this.oxmlhttp;
		var _thisObject = this;		
		
		if (! oxmlhttp)		
			return false;
				
		this.requests[id] = oxmlhttp;
		this.buildBar();		
		this.showProgressBar();
		
		var sub_handler = function ()
		{			
			var progressBar = _thisObject.progressBar;
			
			try
			{
				if (oxmlhttp.readyState == 4 )
				{
					
					switch (oxmlhttp.status)
					{
					
						case 200:
							if (typeof(handler) == 'function')
							{																
								_thisObject.hideProgressBar();																
								var data = _thisObject.unserialize(oxmlhttp.responseText);
								if(debug_controller) {
									try{
										document.getElementById("debug_controller").innerHTML += oxmlhttp.responseText;
										document.getElementById("debug_controller").innerHTML += "<br>-------------------------------------------------------------------------------------<br>";
									}
									catch(e){}
								}									
								handler(data);
								delete _thisObject.requests[id];								
								_thisObject.requests[id] = null;
							}

							break;

						case 404:
							
							alert('Page Not Found!');
							break;

						default:												
					}
				}
			}
			catch (e)
			{
				_thisObject.hideProgressBar();
				if(debug_controller)
					alert(e+"\n"+oxmlhttp.responseText);
				// View Exception in Javascript Console
				throw(e);
			}
						
		}

		try
		{ 
			
			if (method == '' || method == 'GET')
			{								
				oxmlhttp.open("GET",target,true);
				if (typeof(handler) == 'function')
				{	
					oxmlhttp.onreadystatechange =  sub_handler;					
					oxmlhttp.send(null);					
				}		
				
			}
			else if (method == 'POST')
			{
				oxmlhttp.open("POST",target, true);
				oxmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				if (typeof(handler) == 'function')
				{
					oxmlhttp.onreadystatechange = sub_handler;
					oxmlhttp.send(data);
				}				
				
			}
		}
		catch(e)
		{	
			_thisObject.hideProgressBar();
			if(debug_controller)
				alert(e);
			// View Exception in Javascript Console	 
			throw(e);
		}
						
		return true;
	}
	// Cancel Request Connector
	cConnector.prototype.cancelRequest = function (){
		if (!this.requests[this.tid]){
			return false;
		}
		this.oxmlhttp.onreadystatechange = null;
		this.requests[this.tid].abort();
		this.hideProgressBar();
	}
//------------------------------------  END: Functions for Connector HTTPRequest  -------------------------------------------------//

//	----------------------------------- BEGIN: Functions for build Bar Progress ---------------------------------------------------------//
	cConnector.prototype.hideProgressBar = function ()
	{
		div = document.getElementById('divProgressBar');
		if ( (div) && (this.isVisibleBar) )
		{
			div.style.visibility = 'hidden';
			this.isVisibleBar = false;
		}
	}
	
	cConnector.prototype.showProgressBar = function()
	{
		div = document.getElementById('divProgressBar');
		if(div)
		{
			div.style.visibility = 'visible';
			this.isVisibleBar = true;
		}
		else
			this.isVisibleBar = false;
	}

	cConnector.prototype.loadScript = function(scriptPath)	{
      	if (document.getElementById('uploadscript_'+scriptPath)) {
      		return;
      	}
  	
		this.oxmlhttp.open("GET", "js/"+scriptPath+".js", false);
        this.oxmlhttp.setRequestHeader('Content-Type','text/plain');
		this.oxmlhttp.send(null);
		if(this.oxmlhttp.status != 0 && this.oxmlhttp.status != 200 || 	this.oxmlhttp.status == 0 && this.oxmlhttp.responseText.length == 0)
			throw new Error("Error " + this.oxmlhttp.status + "("+this.oxmlhttp.statusText+") when loading script file '"+scriptPath+"'");
		
		var head = document.getElementsByTagName("head")[0];
		var script = document.createElement("SCRIPT");
		script.id = 'uploadscript_'+scriptPath;
		script.type = 'text/javascript';		
		script.text = this.oxmlhttp.responseText;
		head.appendChild(script);
		return;	
	}
//------------------------------------  END: Functions for Progress Bar  -------------------------------------------------//
	// Default Controller File
	var DEFAULT_URL = 'expressoAdmin1_2/controller.php?action=';
	// connector object
	var admin_connector = new cConnector();
	var _onmouseup = document.onmouseup;
	var isExecuteForm = false;
	var id = null;

	// 	Function executes AJAX
	// 	cExecute (url, handler, params)
	//	url: 'module.class.method'
	//  handle: function handle() receive response.
	//  params: parameters for POST method
	//	form: form element (for upload files)	
	function cExecute(url, handler, params, form) {
		if(isExecuteForm){
			isExecuteForm = false;
			document.onmouseup = _onmouseup;
		}
		if(form) {
			cExecuteForm(url, form);
			return;
		}
		
		url = DEFAULT_URL + url;
			
		if(params)		 
			method = "POST";
			 	
		 else 
			method = "GET";
			 
		id = url;
		admin_connector.newRequest(id, url, method, handler, params);
	}
	
	// This function executes submit values to Controller (POST)
	// The return is void.
	// 	cExecuteForm (url, form)
	//	url: 'module.class.method'
	//	form: form element (for upload files)	
	function cExecuteForm(url, form, handler){
		admin_connector.buildBar();
		isExecuteForm = true;		
		document.onmouseup = alertBut;
		
		admin_connector.showProgressBar();
		if(! (divUpload = document.getElementById('divUpload'))) {
			divUpload		= document.createElement('DIV');		
			divUpload.id	= 'divUpload';
			document.body.appendChild(divUpload);
		}
		
		if(! (el = document.getElementById('_action'))) {			
			el			= document.createElement('input');
			el.type	= 'hidden';
			el.id		= '_action';	
			el.name	= '_action';
			form.appendChild(el);
		}
		
		if(countFiles) {			
			el			= document.createElement('input');
			el.type	= 'hidden';	
			el.name	= 'countFiles';
			el.value 	= countFiles;
			form.appendChild(el);						
		}		
		
		form._action.value = url;
		divUpload.innerHTML= "<iframe onload=\"cExecute('$this.functions.getReturnExecuteForm',"+handler+");\"  style='display:"+(debug_controller ? "" : "none")+";width:"+(debug_controller ? 800 : 0)+";height:"+(debug_controller ? 800 : 0)+";' name='uploadFile'></iframe>";

		form.action ="expressoAdmin1_2/controller.php";
		form.target ="uploadFile";
		
		form.submit();
	}	
	
	
	function alertBut(e) {
	   	if(!e)
			e = window.event;

	    if(_onmouseup)
			_onmouseup(e);

		if(isExecuteForm) {
	    	if(confirm("There's an action processing. Do you want abort it?")) {
		    	admin_connector.hideProgressBar();
		    	isExecuteForm = false;
		    	delete admin_connector.requests[id];								
				admin_connector.requests[id] = null;
	    		stop();		    		    		
	    		return;
	    	}
    		else
    			return false;
	    }
	}	
