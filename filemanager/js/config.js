
function loadq_handler(data)
{
	document.getElementById('quota_size').value = data;
}
function load_quota(el)
{
	cExecute_('index.php?menuaction=filemanager.uiconfig.load_quota&name='+base64_encode(el.value),loadq_handler);
}

function set_owner()
{
	var dirName = document.getElementById('search1').value;
	var ownerName = document.getElementById('search2').value;
	cExecute_('index.php?menuaction=filemanager.uiconfig.set_owner&dir='+base64_encode(dirName)+'&owner='+ownerName,alert);
}

function set_permission()
{
	var dirName = document.getElementById('search1').value;
	var ownerName = document.getElementById('search2').value;
	var perms=new Array("egw_private","egw_delete","egw_edit","egw_add","egw_read");
	permission = 0;
	for (val in perms)
	{
		permission = permission*2;
		if(document.getElementById(perms[val]).checked)
			permission += 1;
	}

	cExecute_('index.php?menuaction=filemanager.uiconfig.set_permission&dir='+base64_encode(dirName)+'&perms='+permission+'&owner='+ownerName,alert);
}
function save_quota()
{
	var handler_save_quota = function(data)
	{
		var _response	= document.getElementById('result_folders');
		var _search1	= document.getElementById('search1');	
		var _quota		= document.getElementById('quota_size');
		
		_response.innerHTML	= data;
		_search1.innerHTML		= "";
		_quota					= "";
		
		setTimeout( function() { _response.innerHTML = ""; }, 3500 );
	};

	var dirName = document.getElementById('search1').value;
	
	var Qsize = document.getElementById('quota_size').value;
	
	cExecute_('index.php?menuaction=filemanager.uiconfig.update_quota', handler_save_quota,'dir='+base64_encode(dirName)+'&val='+Qsize );
}

function dir_handler(data)
{
	document.getElementById('search1').innerHTML = data;
}

function user_handler(data)
{
	document.getElementById('search2').innerHTML = data;
}

var timeO;

function searchDirOrUser()
{
	if( arguments.length  > 0 )
	{
		var el	= arguments[0];
		var ev	= arguments[1];
		var act	= arguments[2];
		var key	= [8, 27, 37, 38, 39, 40];
		var _search =  (act == "dir" ) ? document.getElementById("search1") : document.getElementById("search2");
		
		var dir_handler =  function(data)
		{
			_search.innerHTML = data;
		}
		
		for( var i in key )
			if( ev.keyCode == key[i] )
				return false;
		
		if( el.value.length < 4 )
		{
			if( act == 'dir' )
			{
				document.getElementById("span_searching1").innerHTML = "( Digite mais " + ( 4 - el.value.length ) + " ) ";
				setTimeout(function(){
					document.getElementById("span_searching1").innerHTML = "";
				},2000);
			}
			else
			{
				document.getElementById("span_searching2").innerHTML = "( Digite mais " + ( 4 - el.value.length ) + " ) ";
				setTimeout(function(){
					document.getElementById("span_searching2").innerHTML = "";
				},2000);
				
			}
		}
		else
		{
			if( act == 'dir' )
			{
				document.getElementById("span_searching1").innerHTML = "( Buscando aguarde .... )";

				if( timeO )
					clearTimeout( timeO );

				timeO = setTimeout( function()
				{
					cExecute_('index.php?menuaction=filemanager.uiconfig.search_dir', dir_handler, 'name='+el.value );
					document.getElementById('span_searching1').innerHTML = "";
				}, 700);
			}
			else
			{
				document.getElementById('span_searching2').innerHTML = "( Buscando aguarde .... )";
				
				if (timeO)
					clearTimeout(timeO);
				
				timeO = setTimeout(function()
				{
					cExecute_('index.php?menuaction=filemanager.uiconfig.search_user',dir_handler,'name='+el.value);
					document.getElementById('span_searching2').innerHTML ="";
				}, 700);
			}
		}
	}
}

function delete_folder()
{
	var handler_delete = function(data)
	{
		var _response	= document.getElementById('result_folders');
		var _search1	= document.getElementById('search1');
		
		_response.innerHTML	= data;
		_search1.innerHTML		= "";
		
		setTimeout( function() { _response.innerHTML = ""; }, 3500 );
	};
	
	var dirName = document.getElementById('search1').value;
	
	var ok2Del = confirm( get_lang('Are you sure you want to delete')+' '+dirName+'?' );
	
	if  ( ok2Del)
	{
		var randNum = parseInt((Math.random()*100));
		
		var ok2Del = prompt( get_lang('Please type the text "%1" to delete',randNum)+': '+dirName );
		
		if ( ok2Del == randNum )
			cExecute_('index.php?menuaction=filemanager.uiconfig.removeFolder', handler_delete, 'dir='+base64_encode(dirName) );
	}
}

function rename_folder()
{
	var dirName = document.getElementById('search1').value;
	var toName = prompt(get_lang('enter the name you want to move %1 to',dirName),dirName);
	if (toName.length > 1)
	{
		cExecute_('index.php?menuaction=filemanager.uiconfig.renameFolder&dir='+base64_encode(dirName)+'&to='+base64_encode(toName),alert);
	}
}

function create_folder()
{
	var toName = prompt( get_lang('Enter the name of folder you want to create'), '/home/' );
	
	if (toName.length > 1)
	{
		cExecute_('index.php?menuaction=filemanager.uiconfig.createFolder&name='+base64_encode(toName),alert);
	}
}

function reconstruct_folder()
{
	var handler_reconstructFolder =  function( data )
	{
		var _response	= document.getElementById('result_folders');
		var _search1	= document.getElementById('search1');
		
		_response.innerHTML	= data;
		_search1.innerHTML		= "";
		
		setTimeout( function() { _response.innerHTML = ""; }, 3500 );
	};
	
	var dirName = document.getElementById('search1').value;
	cExecute_('index.php?menuaction=filemanager.uiconfig.reconstructFolder', handler_reconstructFolder, 'dir='+base64_encode(dirName));
}