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
	this.updateVersion = "";
}
cConnector.prototype.buildBar = function()
{
	var div = document.getElementById('divProgressBar');

	if(! div) {
		div = document.createElement("DIV");
		//div.style.visibility	= "hidden";
		div.style.width = "103px";
		div.id = 'divProgressBar';
		div.align = "center";
		div.innerHTML = '&nbsp;&nbsp;<font face="Verdana" size="2" color="WHITE">'+get_lang('loading')+'...</font>&nbsp;';
		div.style.background = "#cc4444";
		div.style.position = 'fixed';
		div.style.top = '0px';
		div.style.right = '0px';
		document.getElementById('divAppboxHeader').appendChild(div);

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
	}
}

cConnector.prototype.hideProgressBar = function ()
{
	var div = document.getElementById('divProgressBar');
	if (div != null)
		div.style.visibility = 'hidden';
	else
		setTimeout('connector.hideProgressBar()',100);
	this.isVisibleBar = false;
}

cConnector.prototype.showProgressBar = function(){
	var div = document.getElementById('divProgressBar');
	if (! div){
		connector.buildBar();
		return;
	}

	div.style.visibility = 'visible';

	this.isVisibleBar = true;
}

	function XMLTools()
	{
		this.path = "";
	}
var connector = new cConnector();

function cExecuteForm_(form, handler){
	connector.showProgressBar();
	
	if( ! ( divUpload = document.getElementById('divUpload') ) )
	{
		divUpload		 = document.createElement('DIV');                
		divUpload.id		 = 'divUpload';
		document.body.appendChild(divUpload);
	}

	handlerExecuteForm = handler;
	
	var form_handler = function (data)
	{
		handlerExecuteForm(data);
		handlerExecuteForm = null;
	}
	
	divUpload.innerHTML= "<iframe onload=\"connector.hideProgressBar();cExecute_('./index.php/index.php?menuaction=filemanager.uifilemanager.getReturnExecuteForm',"+form_handler+");\"  style='display:none;width:0;height:0;' name='uploadFile'></iframe>";
	
	form.target ="uploadFile";
	form.submit();
}

function cExecute_( requestURL, handler, params)
{
	if (connector.isVisibleBar == true)
	{
		setTimeout('cExecute_("'+requestURL+'",'+handler+')',150);
		return;
	}
	
	connector.showProgressBar();
	
	var AjaxRequest = function () 
	{
		Ajax = false;
		if (window.XMLHttpRequest) //Gecko
			Ajax = new XMLHttpRequest();
		else
			if (window.ActiveXObject) //Other nav.
				try
				{
					Ajax = new ActiveXObject("Msxml12.XMLHTTP");
				} catch (e)
		{
			Ajax = new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	
	var responseRequest = function()
	{
		try
		{
			if ( Ajax.readyState == 4 )
			{
				switch ( Ajax.status )
				{
					case 200:
						if (typeof(handler) == 'function')
						{																
							connector.hideProgressBar();
							var data = Ajax.responseText;
							handler(data);
						}

						break;

					case 404:
						
						alert(get_lang('Page Not Found!'));
						break;

					default:												
				}
			}
		}
		catch (e)
		{			
			connector.hideProgressBar();
			// View Exception in Javascript Console
			throw(e);
		}
	}
	
	AjaxRequest();
	
	if (!Ajax){
		throw("No connection");
		return;
	}

	if( typeof(params) == 'undefined' )
	{
		Ajax.open('GET', requestURL, true);
		Ajax.onreadystatechange = responseRequest;
		Ajax.send(null);
	}	
	else
	{
		Ajax.open("POST", requestURL, true);
		Ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		Ajax.onreadystatechange = responseRequest;
		Ajax.send( params );
	}
}

function unserialize(str)
{

	var matchB = function (str, iniPos)
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
				nClose = matchB(str, nOpen);
			}
		} while (nOpen < nClose);

		return nClose;
	}

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
					var index = f(indexStr);
					pos = arrayContent.indexOf(';', pos)+1;

					/* Process Content */
					var part = null;
					switch (arrayContent.charAt(pos))
					{
						case 'a':
							var pos_ = matchB(arrayContent, arrayContent.indexOf('{', pos))+1;
							part = arrayContent.substring(pos, pos_);
							pos = pos_;
							data[index] = f(part);
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
							data[index] = f(part);
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
	unserialized = f(str);
	return unserialized;
}
var unserialized = new Object();

// Serialize Data Method
function serialize(data)
{
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
							str_data += 'i:' + i + ';' + f(data[i]);
							break;

						case 'string':
							str_data += 's:' + i.length + ':"' + i + '";' + f(data[i]);
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

		return f(data);
	}
