(function()
{
	var FOLDER	=	"";
	
	function foldersExpresso(){ }
	
	function make_tree()
	{
		var elementHtml = null;
		
		if( arguments.length > 0 )
		{
			elementHtml = arguments[0];
		}
		else
		{
			if( Element('dftree_treeFolders') != null )
			{
				elementHtml = Element('dftree_treeFolders').parentNode.parentNode;
				Element('dftree_treeFolders').innerHTML = "";
			}
		}
		
		var treeFolders = new dFTree({ name : 'treeFolders' });
		
		if( !expresso_offline )
		{
			var root	= new dNode({ id : 'root', caption: get_lang('My Folders'), onClick: "ttree.setFolder('root')" });
			
			treeFolders.add(root, 'root');

			for(var i = 0; i < folders.length ; i++ )
			{
				var nn = new dNode({ id:folders[i].folder_id, caption: lang_folder(folders[i].folder_name), plusSign:folders[i].folder_hasChildren, onClick: "ttree.setFolder('"+folders[i].folder_id+"')"});
				
				if ( folders[i].folder_parent == '' )
				{
					folders[i].folder_parent = 'root';
				}
	            else if ( folders[i].folder_parent.indexOf('user') != -1 )
	            {
	            	//if ( treeFolders.getNodeById('user') )
	            	{
	            		var n_root_shared_folders = new dNode({ id:'user', caption:get_lang("Shared Folders"), plusSign:true});
	                    treeFolders.add( n_root_shared_folders,'root');
	                }
	            }

				// Ver Ticket #1548
				if( folders[i].folder_parent != 'root')
				{
	                var node_parent = treeFolders.getNodeById(folders[i].folder_parent);
	                
	                if( typeof node_parent != 'undefined')
	                {
	                	node_parent.plusSign = true;
	                	treeFolders.alter(node_parent);
	                }
	            }
				
				treeFolders.add( nn, folders[i].folder_parent );
			}
		}	

		//Pastas locais
		if ( preferences.use_local_messages == 1 ) 
		{
			var n_root_local = new dNode({ id : "local_root", caption : get_lang("local folders"), onClick: "ttree.setFolder('root')" });
			
			treeFolders.add( n_root_local, 'root' );
			
			var local_folders = expresso_local_messages.list_local_folders();
			
			//Coloca as pastas locais.
			for (var i in local_folders)
			{ 
				var node_parent = "local_root";
				var new_caption = local_folders[i][0];
				
				if( local_folders[i][0].indexOf("/") != "-1" )
				{
					final_pos	= local_folders[i][0].lastIndexOf("/");
					node_parent = "local_"+local_folders[i][0].substr(0,final_pos);
					new_caption = local_folders[i][0].substr(final_pos+1);
				}
				            
				if ( local_folders[i][1] > 0 )
				{
					var nodeLocal = new dNode({
												id: "local_" + local_folders[i][0],
												caption: lang_folder(new_caption) + '<font style=color:red>&nbsp(</font><span id="local_unseen" style=color:red>' + local_folders[i][1] + '</span><font style=color:red>)</font>',
												plusSign: local_folders[i][2]
					});
				}
				else
				{
					var nodeLocal = new dNode({
												id: "local_" + local_folders[i][0],
												caption: lang_folder(new_caption),
												plusSign: local_folders[i][2]
					});
				}
				treeFolders.add(nodeLocal, node_parent);
			}
		}

		treeFolders.draw(elementHtml);
		treeFolders.getNodeById("root")._select();
		root.changeState();
		
	}
	
	function getFolder()
	{
		return FOLDER;
	}
	
	function setFolder()
	{
		FOLDER = arguments[0];
	}

	foldersExpresso.prototype.getFolder	= getFolder;
	foldersExpresso.prototype.setFolder	= setFolder;
	foldersExpresso.prototype.make_tree	= make_tree;
	
	window.ttree = new foldersExpresso;
	
})();