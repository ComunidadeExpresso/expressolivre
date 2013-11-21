  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  - Jonas Goes <jqhcb@users.sourceforge.net>                               *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

/***********************************************\
*               INITIALIZATION                  *
\***********************************************/
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

/***********************************************\
 *                DATA FUNCTIONS               *
\***********************************************/
function randomString() {
	var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
	var string_length = 4;
	var randomstring = '';
	for (var i=0; i<string_length; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		randomstring += chars.substring(rnum,rnum+1);
	}
	return randomstring;
}

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
							str_data += 'i:' + i + ';' + serialize(data[i]);
							break;

						case 'string':
							str_data += 's:' + i.length + ':"' + i + '";' + serialize(data[i]);
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

function unserialize(str)
{
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
					var index = unserialize(indexStr);
					pos = arrayContent.indexOf(';', pos)+1;
					
					/* Process Content */
					var part = null;
					switch (arrayContent.charAt(pos))
					{
						case 'a':
							var pos_ = matchBracket(arrayContent, arrayContent.indexOf('{', pos))+1;
							part = arrayContent.substring(pos, pos_);
							pos = pos_;
							data[index] = unserialize(part);
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
							data[index] = unserialize(part);
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

function matchBracket(strG, iniPosG)
{
	var f = function (str, iniPos)
	{
		var nOpen, nClose = iniPos;
		var startIn = nClose + 1;

		do
		{
			startIn = nClose + 1;
			do
			{
				nOpen = str.indexOf( '{', startIn );
				var escapeEndIn = str.indexOf( '";', nOpen + 1 );
				var escapeBeginIn = str.indexOf( ':"', nOpen + 1 );
				if ( escapeEndIn < escapeBeginIn )
					startIn = nOpen + 1;
			}
			while ( escapeEndIn < escapeBeginIn );

			startIn = nClose + 1;
			do
			{
				nClose = str.indexOf( '}', startIn );
				var escapeEndIn = str.indexOf( '";', nClose + 1 );
				var escapeBeginIn = str.indexOf( ':"', nClose + 1 );
				if ( escapeEndIn < escapeBeginIn )
					startIn = nClose + 1;
			}
			while ( escapeEndIn < escapeBeginIn );

			if (nOpen == -1)
			{
				return nClose;
			}

			if (nOpen < nClose )
			{
				nClose = matchBracket(str, nOpen);
			}

		} while (nOpen < nClose);

		return nClose;
	}

	return f(strG, iniPosG);
}

/***********************************************\
*               AUXILIAR FUNCTIONS              *
\***********************************************/

function resizeIcon(id, action)
{
	var element = Element(id);
	
	if (action == 0)
	{
		CC_old_icon_w = element.style.width;
		CC_old_icon_h = element.style.height;

		element.style.zIndex = parseInt(element.style.zIndex) + 1;
		element.style.width = '36px';
		element.style.height = '36px';
		element.style.top = (parseInt(element.style.top) - parseInt(element.style.height)/2) + 'px';
		element.style.left = (parseInt(element.style.left) - parseInt(element.style.width)/2) + 'px';
	}
	else if (action == 1)
	{
		element.style.zIndex = parseInt(element.style.zIndex) - 1;
		element.style.top = (parseInt(element.style.top) + parseInt(element.style.height)/2) + 'px';
		element.style.left = (parseInt(element.style.left) + parseInt(element.style.width)/2) + 'px';
		element.style.width = CC_old_icon_w;
		element.style.height = CC_old_icon_h;
	}
}

function Element (element)
{
	/* IE OBJECTS */
	if (document.all)
	{
		return document.all[element];
	}
	/* MOZILLA OBJECTS */
	else if (document.getElementById)
	{
		return document.getElementById(element);
	}
	/* NS4 OBJECTS */
	else if (document.layers)
	{
		return document.layers[element];
	}
}

function removeHTMLCode(id)
{
	Element(id).parentNode.removeChild(Element(id));
}

function addHTMLCode(parent_id,child_id,child_code,surround_block_tag)
{
	var obj = document.createElement(surround_block_tag);
	Element(parent_id).appendChild(obj);
	obj.id = child_id;
	obj.innerHTML = child_code;
	return obj;
}

function addSlashes(code)
{
	for (var i = 0; i < code.length; i++)
	{
		switch(code.charAt(i))
		{
			case "'":
			case '"':
			case "\\":
				code = code.substr(0, i) + "\\" + code.charAt(i) + code.substr(i+1);
				i++;
				break;
		}
	}

	return code;
}

function htmlSpecialChars(str)
{
	// TODO: Not implemented!!!
	var pos = 0;
	
	for (var i = 0; i < str.length; i++)
	{
		
	}
}

function replaceComAnd(str, replacer)
{
	return escape( str );
	var oldPos = 0;
	var pos = 0;
	
	while ((pos = str.indexOf('&', pos)) != -1)
	{
		str = str.substring(oldPos, pos) + replacer + str.substring(pos+1);
	}

	return str;
}

function ccTimeout(control, code, maxCalls, actualCall, singleTimeout)
{
	if (eval(control))
	{
		eval(code);
		return true;
	}

	if (!actualCall)
	{
		actualCall = 1;
	}

	if (!maxCalls)
	{
		maxCalls = 100;
	}

	if (actualCall == maxCalls)
	{
		showMessage(Element('cc_msg_err_timeout').value);
		return false;
	}

	if (!singleTimeout)
	{
		singleTimeout = 100;
	}

	setTimeout('ccTimeout(\''+control+'\',\''+addSlashes(code)+'\','+maxCalls+','+(actualCall+1)+','+singleTimeout+')', singleTimeout);
}

function showMessage(msg, type)
{
	// TODO: Replace alert with 'loading' style div with Ok button

	switch(type)
	{
		case 'confirm':
			return confirm(msg);

		default:
			alert(msg);
			return;
	}
}

function formatPhone(obj){
	var key = window.event.keyCode;
	if (!Element("cc_conn_type_1").checked) {
		// if the user press backspace...
		if(key != 8){
			if(obj.value.length == 1)
			  obj.value = "(" + obj.value;
			else if(obj.value.length == 4)
			  obj.value = obj.value + ") ";
			else if(obj.value.length == 10)
			  obj.value += "-";
		 }
			obj.value = obj.value.replace(/[^\-\d\+\(\)\sx]/g, "");
	}  
}


/***********************************************\
*                   CONSTANTS                   *
\***********************************************/

var CC_url = Element('cc_server_root').value+'/index.php?menuaction=contactcenter.ui_data.data_manager&method=';

/***********************************************\
*               GLOBALS VARIABLES               *
\***********************************************/

var CC_loading_div = null;
var CC_loading_td  = null;
var CC_loading     = false;
var CC_loading_set = false;
