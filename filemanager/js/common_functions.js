
var Xtools	= null;

function load(path,el)
{
	currentPath = path;
	contentFolders = document.getElementById('content_folders');
	for (i=0; i < contentFolders.childNodes.length; i++)
		if (contentFolders.childNodes[i].className == "sl")
			contentFolders.childNodes[i].className = "l";
	el.className = "sl";
	current_folder = currentPath;
        if(last_folder != current_folder){
                lastPage = 1;
                current_page = 1;
                last_folder = current_folder;
		offset = 0;
        }
	toolbar.control('reload');
}

function loadXtools()
{
	if( Xtools == null )
		Xtools = new xtools( path_filemanager + "tp/expressowindow/" );
}

function check(element)
{
	element.firstChild.firstChild.checked = true;
}

function configEvents(pObj, pEvent, pHandler)
{
	if ( typeof pObj == 'object' )
	{
		if ( pEvent.substring(0, 2) == 'on' )
			pEvent = pEvent.substring(2, pEvent.length );

		if ( arguments.length == 3 )
		{
			if ( pObj.addEventListener )
				pObj.addEventListener(pEvent, pHandler, false );
			else if ( pObj.attachEvent )
				pObj.attachEvent( 'on' + pEvent, pHandler );
		}
		else if ( arguments.length == 4 )
		{
			if ( pObj.removeEventListener )
				pObj.removeEventListener( pEvent, pHandler, false );
			else if ( pObj.detachEvent )
				pObj.detachEvent( 'on' + pEvent, pHandler );
		}
	}
}

function maxFileSize()
{
	if( arguments.length > 0 )
	{
		var _document		= arguments[0];
		var _maxFileSize		= "";
		
		for(var i = 0 ;  i < _document.forms[0].elements.length ; i++ )
		{
			if( _document.forms[0].elements[i].type == "text" )
			{	
				var _name = (_document.forms[0].elements[i].name).toLowerCase();
				
				if( _name.indexOf('filemanager_max_file_size') > - 1 )
					_maxFileSize = trim(_document.forms[0].elements[i].value);
			}
		}
		
		var handlerSubmit = function(data)
		{
			_document.forms[0].submit.click();
		}
		
		if( _maxFileSize != '' )
		{
			cExecute_( './index.php?menuaction=filemanager.uifilemanager.setFileMaxSize', handlerSubmit,'maxFileSize=' + _maxFileSize );
		}
		else
		{
			alert('É necessário informar um valor !');
			return false;
		}
	}
}

function trim(inputString)
{
	if ( typeof inputString != "string" )
		return inputString;

	var retValue	= inputString;
	var ch		= retValue.substring(0, 1);
	
	while (ch == " ") 
	{
		retValue = retValue.substring(1, retValue.length);
		ch = retValue.substring(0, 1);
	}
	
	ch = retValue.substring(retValue.length-1, retValue.length);
	
	while (ch == " ") 
	{
		retValue = retValue.substring(0, retValue.length-1);
		ch = retValue.substring(retValue.length-1, retValue.length);
	}
	
	while (retValue.indexOf("  ") != -1) 
	{
		retValue = retValue.substring(0, retValue.indexOf("  ")) + retValue.substring(retValue.indexOf("  ")+1, retValue.length);
	}
	
	return retValue;
}

function validateFileExtension(fileName)
{
	var error_flag = false;
	var fileExtension = fileName.split(".");
	fileExtension = fileExtension[(fileExtension.length-1)];
	for(var i=0; i<denyFileExtensions.length; i++)
	{
		if(denyFileExtensions[i] == fileExtension)
		{
			error_flag = true;
			break;
		}

	}

	if ( error_flag == true )
	{
		write_error(get_lang('File extension forbidden or invalid file') + '.');
		return false;
	}
	return true;
}

function get_lang(_key)
{
	var key = _key.toLowerCase();
	if(array_lang[key])
		var _value = array_lang[key];
	else
		var _value = _key+"*";

	if(arguments.length > 1)
		for(j = 1; typeof(arguments[j]) != 'undefined'; j++)
			_value = _value.replace("%"+j,arguments[j]);
	return _value;

}


function newEmptyFile()
{
	var name = prompt(get_lang('Enter with the name of new file/directory'), '');
	var input_text = document.getElementById('newfile_or_dir');
	if (name != null && name != '' && validateFileExtension(name))
	{
		var fileExtension = name.split(".");
		fileExtension = fileExtension[1];
		if (typeof(fileExtension) == 'undefined')
			input_text.value = name+".html";
		else
			input_text.value = name;
		address = document.location.toString();
		address = address.split("&");
		document.location = address[0]+"&newfile.x=1&newfile_or_dir="+input_text.value;

	}
}

function newUpload( )
{
	if( document.getElementById("dwindownewUpload__parent") == null )
	{
		var _newUpload = function(data)
		{
			loadXtools();
			
			var pArgs = unserialize(data);
				pArgs.lang_click_here	= get_lang("Click here");
				pArgs.lang_more_files	= get_lang("More files");
				pArgs.lang_send_email	= get_lang("Send email");
				pArgs.height 			= 210;
				pArgs.path_filemanager	= path_filemanager;
				pArgs.width 			= 500;
		
			var _html = Xtools.parse( Xtools.xml("upload_files"), "upload.xsl", pArgs );
			
			draw_window( _html, 550, 350, get_lang("upload files"), "newUpload" );
		
			if( pArgs.emails_to != null )
				sendNotification( pArgs.emails_to );
		}
		
		address = document.location.toString();
		address = address.split("?");
		var url = address[0]+"?menuaction=filemanager.uifilemanager.showUploadboxes&path="+base64_encode(currentPath);
		cExecute_( url, _newUpload );
	}
}

function newAdvancedUpload()
{
	for ( var i = 0 ; i < navigator.plugins.length; i++ )
	{
		if ( navigator.plugins[i].name.match('Java') || navigator.plugins[i].name.match('libnpjp2') )
		{
			_winBuild( "dwindownewUpload" , "remove" );

			loadXtools();
			
			var pArgs = 
			{
				'iframe_width'		: 515,
				'iframe_height'		: 320,
				'iframe_src'		: path_filemanager + "inc/uploadApp.php?id="+parseInt(Math.random()*Math.pow(10,15))
			};
			
			var _html = Xtools.parse( Xtools.xml("upload_files_advanced"), "uploadAdvanced.xsl", pArgs);
			
			draw_window( _html, 530, 345, get_lang("Advanced Upload") );
			
			return;
		}
	}
	
	alert( get_lang("You do not have Java installed, plugin not loaded") + "!" );
}


(function( )
{
	// TODO: use DES, RSA, PGP, or something strong
	var sec_key = null;
	function encode( data )
	{
		if (data == null)
			return null;
		ret = "";
		for ( var i=0;(i < data.length && data.charCodeAt(i) > 31); i++ )
		{
			ret += String.fromCharCode(data.charCodeAt(i) ^ sec_key.charCodeAt(i));
		}
		return ret;
	}

	function crypt( input )
	{
		sec_key = input;
	}

	crypt.prototype.encode = encode;
	window.crypt = crypt;
})( );

/*
 * base64.js - Base64 encoding and decoding functions
 *
 * Copyright (c) 2007, David Lindquist <david.lindquist@gmail.com>
 * Released under the MIT license
 */

function base64_encode(str) {
	var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	var encoded = [];
	var c = 0;
	try { var slen = str.length; } catch (e) { write_error(get_lang('you do not have access to %1',currentPath)); return; };
        while (c < slen) {
		var b0 = str.charCodeAt(c++);
		var b1 = str.charCodeAt(c++);
		var b2 = str.charCodeAt(c++);
		var buf = (b0 << 16) + ((b1 || 0) << 8) + (b2 || 0);
		var i0 = (buf & (63 << 18)) >> 18;
		var i1 = (buf & (63 << 12)) >> 12;
		var i2 = isNaN(b1) ? 64 : (buf & (63 << 6)) >> 6;
		var i3 = isNaN(b2) ? 64 : (buf & 63);
		encoded[encoded.length] = chars.charAt(i0);
		encoded[encoded.length] = chars.charAt(i1);
		encoded[encoded.length] = chars.charAt(i2);
		encoded[encoded.length] = chars.charAt(i3);
	}
	var retBuff = escape(encoded.join(''));
	return retBuff.replace(/\+/g,"%2B");
}

function base64_decode(str) {
	var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	var invalid = {
	strlen: (str.length % 4 != 0),
	chars:  new RegExp('[^' + chars + ']').test(str),
	equals: (/=/.test(str) && (/=[^=]/.test(str) || /={3}/.test(str)))
	};
	if (invalid.strlen || invalid.chars || invalid.equals)
		throw new Error('Invalid base64 data');
	var decoded = [];
	var c = 0;
	while (c < str.length) {
		var i0 = chars.indexOf(str.charAt(c++));
		var i1 = chars.indexOf(str.charAt(c++));
		var i2 = chars.indexOf(str.charAt(c++));
		var i3 = chars.indexOf(str.charAt(c++));
		var buf = (i0 << 18) + (i1 << 12) + ((i2 & 63) << 6) + (i3 & 63);
		var b0 = (buf & (255 << 16)) >> 16;
		var b1 = (i2 == 64) ? -1 : (buf & (255 << 8)) >> 8;
		var b2 = (i3 == 64) ? -1 : (buf & 255);
		decoded[decoded.length] = String.fromCharCode(b0);
		if (b1 >= 0) decoded[decoded.length] = String.fromCharCode(b1);
		if (b2 >= 0) decoded[decoded.length] = String.fromCharCode(b2);
	}
	return decoded.join('');
}


function setRestricted(name)
{
	var continue_set = confirm(get_lang('This property will change the visibility of all users that have access to this file, continue?'));
	
	if ( continue_set )
	{
		cExecute_('./index.php?menuaction=filemanager.vfs_functions.setRestricted&file='
				+ base64_encode(name)+'&path='+base64_encode(currentPath),handler.restricted);
	}
}

function presetComments(el)
{
	if (permissions['edit'] == 0){
		el.blur();
		write_error(get_lang('You have no permission to access this file'));
	}
	oldValue = el.value;
}

function setComments(el)
{
	if ( el.value == oldValue )
		return;
	
	var filename = base64_encode(el.id);
	
	cExecute_('./index.php?menuaction=filemanager.vfs_functions.editComment&file='
				+ filename+'&comment='+base64_encode(el.value), handler.updateComment);
}

function enterComments(e,el)
{
	if ( e.keyCode == KEY_ENTER )
	{
		el.blur();
	}
}

function EditColumns( args )
{
	if( args == 'close' )
	{
		_winBuild("window_tools_view","remove");
	}
	else if( args == 'save')
	{
		var checkBoxes = document.getElementById('menu_col_pref').getElementsByTagName("input");

		for ( var i = 0 ; i < checkBoxes.length; i++)
		{
			if( checkBoxes[i].checked === true )
				preferences[checkBoxes[i].value] = '1';
			else
				preferences[checkBoxes[i].value] = '0';
		}

		cExecute_('./index.php?menuaction=filemanager.user.save_preferences',function () { toolbar.control('reload'); EditColumns('close'); }, 'preferences='+serialize(preferences));
	}
	else
	{
		loadXtools();
		
		var pTools = 
		{
			'checkList'	:	preferences['viewList'],	
			'checkIcons':	preferences['viewIcons'],
			'check_created'			: preferences['created'],
			'check_createdby_id'	: preferences['createdby_id'],
			'check_comment'			: preferences['comment'],
			'check_mime_type'		: preferences['mime_type'],
			'check_modified'		: preferences['modified'],
			'check_modifiedby_id'	: preferences['modifiedby_id'],
			'check_owner'			: preferences['owner'],
			'check_size'			: preferences['size'],
			'check_version'			: preferences['version'],
			'lang_cancel'			: get_lang('cancel'),
			'lang_created_by'		: get_lang('created by'),			
			'lang_created'			: get_lang('created'),
			'lang_comment'			: get_lang('comment'),
			'lang_modified_by'		: get_lang('modified by'),
			'lang_modified'			: get_lang('modified'),
			'lang_owner'			: get_lang('owner'),
			'lang_save'				: get_lang('save'),
			'lang_size'				: get_lang('size'),
			'lang_type'				: get_lang('type'),
			'lang_version'			: get_lang('version'),
			'lang_view_as_list'		: get_lang('view as list'),
			'lang_view_as_icons'	: get_lang('view as icons'),
			'onclickCancel'			: "EditColumns(\'close\')",
			'onclickSave'			: "EditColumns(\'save\')"
		}
		
		var winTools =
		{	
			 id_window		: "window_tools_view",
			 width			: 250,
			 height			: 290,
			 top				: 100,
			 left				: 400,
			 draggable		: true,
			 visible			: "display",
			 resizable		: true,
			 zindex			: zIndex++,
			 title			: "Expresso FileManager - " + get_lang('View'),
			 closeAction		: "remove",
			 content		: Xtools.parse(Xtools.xml("view_config"), "view.xsl", pTools)	
		};
	
		_winBuild( winTools );
	}
}


function searchFile(){
	var inputText = document.getElementById('em_message_search');
	if (inputText.value.length < 4)
	{
		alert(get_lang('Your search must have at least 4 characters'));
		return;
	}
	cExecute_('./index.php?menuaction=filemanager.uifilemanager.search&text='+inputText.value,folderList.drawSearch);
}
function selectAll(el){
        checkBoxes = document.getElementsByName('fileman');
        if (el.checked)
                for (i=0; i < checkBoxes.length; i++)
                        checkBoxes[i].checked = true;
        else
                for (i=0; i < checkBoxes.length; i++)
                        checkBoxes[i].checked = false;

}

function borkb(size)
{
	var kbyte = 1024;
	var mbyte = kbyte*1024;
	var gbyte = mbyte*1024;
	
	if (!size)
	{
		size = 0;
	}

	if (size < kbyte)
	{
		return size + ' B';
	}
	else if (size < mbyte)
	{
		return parseInt(size/kbyte) + ' KB';
	}
	else if (size < gbyte)
	{
		if ( size/mbyte > 100)
			return (size/mbyte).toFixed(0) + ' MB';
		else
			return (size/mbyte).toFixed(1) + ' MB';
	}
	else
	{
		return (size/gbyte).toFixed(1) + ' GB';
	}
}

function addNewInput()
{
	var newElement = document.createElement('div');
		newElement.innerHTML  =	'<div></div>' +
									'<input maxlength="255" name="upload_file[]" type="file" style="margin-right:5px;" />' +
									'<input name="upload_comment[]" type="text" style="margin-right:2px;" />' +
									'<span style="color:red; cursor:pointer;" onclick="removeInput(this);">'+get_lang('delete')+'</span>';
	
	document.getElementById('uploadOption').parentNode.appendChild(newElement);
}

function removeInput()
{
	if( arguments.length > 0 )
	{
		var _parent = arguments[0].parentNode;
		
		_parent.parentNode.removeChild(_parent);
	}
}

function sendNotification()
{
	var _div = document.getElementById('sendNotifcation');
	
	var _SendNotification = function()
	{
		var pArgs = 
		{
			'lang_delete' 						: get_lang('delete'),
			'lang_send_notification_email_to'	: get_lang("Send Notification email to:"),
			'value_email' 						: ( ( arguments.length > 0 ) ? arguments[0] : "" )
		};

		loadXtools();
		
		_div.innerHTML += Xtools.parse( Xtools.xml("send_notification"), "send_notification.xsl", pArgs);
	}
	
	if( arguments.length > 0 )
	{
		var emailsTo = arguments[0].split(",");
		
		_div.innerHTML += "<div style='margin:4 2 2 4px;'>" +
					      "<label style='font-weight: bold;'>" + get_lang('The following addresses will be notified') + " : </label>" +
					      "</div>";
		for( var i = 0 ;  i < emailsTo.length ; i++ )
		{
			_SendNotification( emailsTo[i] );
			_div.innerHTML += "<div style='margin:1 2 1 4px;'> - " + emailsTo[i] + "</div>";
		}
		_div.innerHTML += "<br/>";
	}
	else
		_SendNotification();
}

function sendFiles()
{
	var _formUp			= document.getElementById('form_up');
	var _uploadFiles		= document.getElementsByTagName('input');
	var _flagSend		= true;
	
	for( var i = 0 ; i < _uploadFiles.length ; i++ )
	{
		if( _uploadFiles[i].name.indexOf("upload_file") > -1 )
		{
			if( _uploadFiles[i].value == "" )
			{
				removeInput( _uploadFiles[i] );
				_flagSend = false;
			}
		}
	}
	
	if( _flagSend )
		cExecuteForm_( _formUp , handler.upload );
	else
		write_msg(get_lang("No file(s) to send") + "!");
}