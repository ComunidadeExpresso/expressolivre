(function()
{
	function archive(data)
	{
		var returnVal = data.split('|');

		if (returnVal[0] == 'False')
		{
			write_error(get_lang('It was not possible to execute it'));
		}
		else

		if (returnVal[0] == 'wpasswd')
		{
			write_error(get_lang('Wrong password'));
			return;
		}
		else
			write_msg(get_lang('Your operation was successfully executed'));

		toolbar.control('reload');
	}

	function copyto(data)
	{
		var returnVal = data.split('|');

		if (returnVal[0] == 'NODIR')
			write_error(get_lang('Could not copy file because no destination directory is given'));
		else if(returnVal[0] == 'SOMEERROR')
		{
			write_error(get_lang('Could not copy %1 file(s)',returnVal[1]));
		}
		else  if(returnVal[0] == 'COPIED')
		{
			if (returnVal[1] == 1)
				write_msg(get_lang('File copied successfuly'));
			else
				write_msg(get_lang('%1 files copied successfuly', returnVal[1]));
		}
	}

	function del(data)
	{
		var _return = data;
		var deletedFiles = "";

		_return = _return.substring(0, ( _return.length - 1 ) );
		_return = _return.split("|");


		for (var i = 0 ; i < _return.length; i++)
		{	
			if ( _return[i] == 'False')
			{
				write_error(get_lang('Could not delete %1', _return[i+1]) );
				return;
			}
			else
			{
				if ( _return[i] != "" )
				{
					deletedFiles += ", " + _return[i];
				}

				if (i > 3) //to avoid big message
				{
					deletedFiles = "  " + _return.length + " " +get_lang("files");
					break;
				}
			}
		}

		write_msg(get_lang('Deleted %1',deletedFiles.substr(2)));

		toolbar.control('reload');
	}

	function draw_folders_list(data)
	{
		var contentFolders = document.getElementById('content_folders');
		
		toolbar.control('reload');
		folders_tree = new dFTree({name: 'main'});
		folders = unserialize(data);
		
		contentFolders.innerHTML = "";
		
		var rootFold = new dNode({id:'root', caption: get_lang("Directories")});
		
		folders_tree.add(rootFold,'root');

		for ( var i = 0 ; i < folders.length; i++ )
		{
			var lastIndex = folders[i].lastIndexOf('/');
			if (folders[i] != "/home/"+preferences.lid)
				var name = folders[i].substr(lastIndex+1,folders[i].length);
			else
				var name = get_lang("My Folder");
			var parentDir = folders[i].substr(0,lastIndex);

			if ( parentDir == '/home' )
				parentDir = 'root';

			var search_child = function( ListFolders, name )
			{
				for ( j = 0 ; j < ListFolders.length; j++ )
				{
					if ( ListFolders[j].indexOf( name + '/') > -1 )
						return true;
				}

				return false;
			}

			folder = new dNode({id:folders[i], caption:name, plusSign:search_child(folders,folders[i]), onClick:'load(\''+folders[i]+'\',this)'});

			if (i == 0)
				folders_tree.add(folder,'root');
			else
				folders_tree.add(folder,parentDir);
		}

		folders_tree.draw(contentFolders);

		folders_tree.openTo(currentPath);

		folders_tree.getNodeById(currentPath)._select();

	}

	function moveto(data) 
	{
		returnVal = data.split('|');
		if (returnVal[0] == 'NODIR')
			write_error(get_lang('Could not copy file because no destination directory is given'));
		else if(returnVal[0] == 'SOMEERROR'){
			write_error(get_lang('Could not move %1 file(s)',returnVal[1]));
		}
		else  if(returnVal[0] == 'MOVED'){
			if (returnVal[1] == 1)
				write_msg(get_lang('File moved successfuly'));
			else
				write_msg(get_lang('%1 files moved successfuly', returnVal[1]));
		}
		handler.refreshDir();
	}

	function refreshDir(data) 
	{
		if( data.toString() === "True" )
		{
			var _action = './index.php?menuaction=filemanager.uifilemanager.get_folders_list';
			
			cExecute_( _action , draw_folders_list );
		}
		else
		{
			write_msg( data );
		}
	}

	function rename() 
	{
		if( arguments.length > 1 )
		{	
			var _input	= arguments[0];
			var _span	= arguments[1]	;
			var _parent	= _input.parentNode;

			_input.style.height = ( parseInt(_input.style.height) - 4 );

			_span.style.className	= "fileLink";
			_span.innerHTML = _input.value;

			var _handlerRename =  function(data)
			{
				var _data = unserialize( data );

				for( var i = 0 ; i < _data.length; i++ )
				{	
					if( _data[i]['error'] )
					{	
						// Remove Input
						if( _input != null )
							_parent.removeChild( _input );

						// Add Span
						if( _span != null ) 
						{	
							_parent.appendChild( _span );
						}

						write_msg("ERROR : " + _data[i]['error'] );
					}

					if( _data[i]['true'] )
					{
						write_msg( _data[i]['true'] );
						toolbar.control('reload');
					}
				}
			}

			if( _parent != null )
			{	
				var url		= './index.php?menuaction=filemanager.vfs_functions.rename';
				var params	= 'file='+base64_encode(_input.id.substr(6))+'&to='+base64_encode(_input.value)+'&path='+base64_encode(currentPath);

				cExecute_(  url,  _handlerRename , params );
			}

		}
	}
	
	function restricted(data)
	{
		if (data.indexOf("True") == 0)
		{
			returnVal = data.split('|');
			var img_lock = document.getElementById('restrict_'+returnVal[1]);

			if (img_lock.style.backgroundImage.indexOf('button_unlock') > 0)
			{
				img_lock.style.backgroundImage = img_lock.style.backgroundImage.replace(/button_unlock/g,'button_lock');
				write_msg(get_lang('%1 marked as restricted',returnVal[1]));
			}
			else
			{
				img_lock.style.backgroundImage = img_lock.style.backgroundImage.replace(/button_lock/g,'button_unlock');
				write_msg(get_lang('%1 unmarked as restricted',returnVal[1]));
			}
		}
		else
			write_error("Could not mark as restricted");
	}

	function updateComment(data)
	{
		var returnVal = data.split('|');
		if (data.indexOf("True") == 0)
		{
			write_msg(get_lang('Updated comment for %1',returnVal[1]));
		}
		else
		{
			if (returnVal[1] == "badchar")
				write_error(get_lang('Comments cannot contain "%1"',returnVal[2]));
			else
				write_error(get_lang('You have no permission to access this file'));
		}

	}
	
	function upload(data)
	{
		var _inputs		= document.getElementsByTagName('input');
		var response	= unserialize(data);

		if ( response[ 'postsize' ] )
		{
			/*
			 * response['postize'] = ERRO POST;
			 * response['max_postsize] = diretiva do PHP para POST_MAX_SIZE;
			 */ 

			write_msg( get_lang("ERROR: Use the advanced file sending!") );
			return false;
		}

		if ( response[0] != "Ok" )
		{
			for( var i = 0; i < response.length; i++ )
			{
				for( var j = 0 ; j < _inputs.length ; j++ )
				{
					if( _inputs[j].getAttribute('type') == "file")
					{	
						var _indexOf = response[i]['file'].toUpperCase().indexOf(_inputs[j].value.toUpperCase() );

						if( response[i]['file'].toUpperCase() === _inputs[j].value.toUpperCase() && _indexOf > -1 )
						{	
							_inputs[j].parentNode.setAttribute("erroUpload", "true");

							var _parent	= _inputs[j].parentNode;
							var _div = _parent.firstChild;	
							_div.style.display		= "block";
							_div.style.color			= "red";
							_div.style.height			= "16px";
							_div.style.paddingLeft	= "17px";
							_div.style.background	= "url('"+path_filemanager+"images/warning.gif') no-repeat left top";
							_div.style.cursor			= "pointer";
							_div.onclick	= function(){this.style.display = 'none';} ;

							if( response[i]['size_max'] )
							{
								_div.innerHTML = "<span style='font-weight:bold' >Erro </span>:: Tamanho do arquivo " 
									+ borkb( response[i]['size'] ) + "  - Permitido  " + borkb( response[i]['size_max'] );
							}
							else if( response[i]['badchar'] )
							{	
								_div.innerHTML = "<span style='font-weight:bold' >Erro </span>:: "  + response[i]['badchar'];	
							}
							else if( response[i]['directory'] )
							{	
								_div.innerHTML = "<span style='font-weight:bold' >Erro </span>:: "  + response[i]['directory'];	
							}
							else if( response[i]['sendFile'] )
							{	
								_div.innerHTML = "<span style='font-weight:bold' >Erro </span>:: "  + response[i]['sendFile'];	
							}
							else if( response[i]['undefined'] )
							{
								_div.innerHTML = "<span style='font-weight:bold' >Erro </span>:: "  + response[i]['undefined'];	
							}
							else if( response[i]['filesize'] )
							{
								_div.innerHTML = "<span style='font-weight:bold' >Erro </span>:: "  + response[i]['filesize'];	
							}
						}
						else
						{
							write_msg( response[i]['undefined'] );
							_winBuild( "dwindownewUpload" , "remove" );	
						}
					}
				}	
			}

			for( var j = 0 ; j < _inputs.length ; j++ )
			{	
				if ( !_inputs[j].parentNode.getAttribute("erroUpload") && _inputs[j].getAttribute('type') == "file" )
				{
					_inputs[j].parentNode.parentNode.removeChild( _inputs[j].parentNode );
					j--;
				}
			}			
		}
		else
		{
			write_msg(get_lang('All files created sucessfuly'));
			_winBuild( "dwindownewUpload" , "remove" );	
		}	

		connector.hideProgressBar();		
		toolbar.control('reload');
	}

	function handler(){}

	handler.prototype.archive			= archive;
	handler.prototype.copyto			= copyto;
	handler.prototype.del				= del;
	handler.prototype.draw_folders_list	= draw_folders_list;
	handler.prototype.moveto			= moveto;
	handler.prototype.refreshDir		= refreshDir;
	handler.prototype.rename			= rename;
	handler.prototype.restricted		= restricted;
	handler.prototype.updateComment		= updateComment;
	handler.prototype.upload			= upload;

	window.handler = new handler;

})();