  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	/*
	 * ContactCenter API - Tree Management Class
	 */

	if (!dFTree)
	{
		throw('dFTree lib must be loaded!');
	}
	
	function ccCatalogTree(params)
	{
		if (!params || !params['name'] || !params['id_destination'])
		{
			throw('Must specify the tree name!');
		}

		var _tree = this;
		
		/* This is the property that holds the Tree Object */
		this.name = params['name'];
		this.tree = new dFTree({'name': params['name']+'_tree'});
		this.actualLevel = null;
		this.actualCatalog = null;
		this.actualCatalogIsExternal = null;
		this.treeAvailable = false;
		this.afterSetCatalog = params['afterSetCatalog'];
		this.catalog_perms = -1;
		this.Connector = params['connector'];

		/* Build the Inicial Tree */ 
		//this._getActualLevel();
		setTimeout(function(){ _tree._updateTree('0', true);}, 100);
		this.tree.draw(Element(params['id_destination']));
	}

	ccCatalogTree.prototype.setCatalog = function(catalog) {
		var _this = this;
                var _catalog = catalog;
			
		if(catalog.length >= 2) {
			if( parseInt( catalog.charAt(2) ) < 2 ){
				document.getElementById( 'advanced' ).style.display = 'none';
			}
			else
				document.getElementById( 'advanced' ).style.display = '';
		}

		var handler = function (responseText)
		{
			Element('cc_debug').innerHTML = responseText;
			var data = unserialize(responseText);

			if (!data)
			{
				showMessage(Element('cc_msg_err_contacting_server').value);
				return;
			}
			_this.actualCatalog = data['catalog'];//Atribuir o catalogo atual...
			_this.actualCatalogIsExternal = data['external'];
			if (data['status'] != 'ok')
			{
				showMessage(data['msg']);
				_this._getActualLevel(true);
				return;
			}
                        _this.actualLevel = _catalog;

			_this.catalog_perms = parseInt(data['perms']);
			
			if(_this.catalog_perms == 0){
				if(CC_visual == 'cards')
					drawCards(0);
				else if(CC_visual == 'table')
					drawTable(0);
				setPages(0,0);
			}
			else if (_this.afterSetCatalog)
			{
				typeof(_this.afterSetCatalog) == 'function' ? _this.afterSetCatalog() : eval(_this.afterSetCatalog);
			}

                        if(data['resetCC_actual_letter'] == 1)
                        {

                            CC_actual_letter = null;
                        }
		};
                
		Connector.newRequest(this.name+'catalog', CC_url+'set_catalog&catalog='+catalog, 'GET', handler);
	}
	
	ccCatalogTree.prototype.setCatalogSearch = function (catalog) {
	
		var _this = this;
                var _catalog = catalog;

		var handler = function (responseText)
		{
			Element('cc_debug').innerHTML = responseText;
			var data = unserialize(responseText);

			if (!data)
			{
				showMessage(Element('cc_msg_err_contacting_server').value);
				return;
			}
			
			if (data['status'] != 'ok')
			{
				showMessage(data['msg']);
				_this._getActualLevel(true);
				return;
			}
                        _this.actualLevel = _catalog;

			_this.catalog_perms = parseInt(data['perms']);

			this.afterSetCatalog = ccEmailWin.search.go();
			
		};
		Connector.newRequest(this.name+'catalog', CC_url+'set_catalog&catalog='+catalog, 'GET', handler);
	}
		
	
	ccCatalogTree.prototype.select = function(level, search)
	{
		if (!search) { 
			this.tree.openTo(level);
			this.tree.getNodeById(level)._select();
		
			if (level != this.actualLevel)
			{
				this.setCatalog(level);
				return;
			}
		} else {
			this.tree.openTo(level);
			this.tree.getNodeById(level)._select();
			this.setCatalogSearch(level);
			return;
		}
	}

	/*************************************************************************\
	 *                    Methods For Internal Use                           *
	\*************************************************************************/
	
	ccCatalogTree.prototype._waitForTree = function(level, rlevel)
	{
		if (this.treeAvailable)
		{
			this.setCatalog(level);
			return;
		}

		if (rlevel >= 100)
		{
			return;
		}
		
		setTimeout(this.name+'._waitForTree(\''+level+'\', '+rlevel+')', 100);
	}

	ccCatalogTree.prototype._getActualLevel = function(set)
	{
		var _this = this;
		this.treeAvailable = false;

		var handler = function (responseText)
		{
			Element('cc_debug').innerHTML = responseText;
			var data = unserialize(responseText);

			if (!data)
			{
				showMessage(Element('cc_msg_err_contacting_server').value);
				return;
			}

			if (data['status'] != 'ok')
			{
				showMessage(data['msg']);
				return;
			}

			_this.actualLevel = data['data'];

			if (set)
			{
			//	_this.select(data['data']);
				_this.tree.openTo(_this.actualLevel);
				_this.tree.getNodeById(_this.actualLevel)._select();
				_this.setCatalog(_this.actualLevel);
				_this.expandTree();

			}
		};
		
		Connector.newRequest(this.name+'actual', CC_url+'get_actual_catalog', 'GET', handler);
	}

	ccCatalogTree.prototype.expandTree = function() {
		for (i=0; i < this.tree._aNodes.length; i++)
			this._expandSubTree(this.tree._aNodes[i]);
	}

	ccCatalogTree.prototype._expandSubTree = function(node) {
		if ( node._children == '' ) {
			return;
		}
		if (node._children != '') {
			for (i = 0; i <= node._children.length; i++) {
				if ( node._io == false )
					node.changeState();
				this._expandSubTree(node._children[i]);
			}
		}
	}


	ccCatalogTree.prototype._updateTree = function(level, open)
	{
		var _this = this;
		this.treeAvailable = false;

		var handler = function (responseText)
		{
			var data = unserialize(responseText);
			var treeData;

			if (!data)
			{
				showMessage(Element('cc_msg_err_contacting_server').value);
				return;
			}

			if (data['status'] != 'ok')
			{
				showMessage(data['msg']);
				return;
			}

			treeData = data['data'];
			
			var timeout = 10;
			var limit = 0;
			for (var i in treeData)
			{
				if (i == 'length')
				{
					continue;
				}

				switch (treeData[i]['type'])
				{
					case 'unknown':
						setTimeout(_this.name+".tree.add(new dNode({id: '"+treeData[i]['id']+"', caption: '"+treeData[i]['caption']+"', onFirstOpen: '"+_this.name+"._updateTree(\\'"+treeData[i]['id']+"\\')', onClick: '"+_this.name+"._updateTree(\\'"+treeData[i]['id']+"\\');"+_this.name+"._waitForTree(\\'"+treeData[i]['id']+"\\',0)'}), '"+treeData[i]['pid']+"');", timeout);
						break;

					case 'catalog_group':
						//setTimeout(_this.name+".tree.add(new dNode({id: '"+treeData[i]['id']+"', caption: '"+treeData[i]['caption']+"', onFirstOpen: '', onClick: '"+_this.name+".tree.getNodeById(\\'"+treeData[i]['id']+"\\').open();"+_this.name+"._getActualLevel(true);'}), '"+treeData[i]['pid']+"');", timeout);
						setTimeout(_this.name+".tree.add(new dNode({id: '"+treeData[i]['id']+"', caption: '"+treeData[i]['caption']+"', onFirstOpen: '', onClick: '"+_this.name+".setCatalog(\\'"+treeData[i]['id']+"\\');'}), '"+treeData[i]['pid']+"');", timeout);
						break;

					case 'catalog':
						setTimeout(_this.name+".tree.add(new dNode({id: '"+treeData[i]['id']+"', caption: '"+treeData[i]['caption']+"', onFirstOpen: '', onClick: '"+_this.name+".setCatalog(\\'"+treeData[i]['id']+"\\');'}), '"+treeData[i]['pid']+"');", timeout);
						break;

					case 'mixed_catalog_group':
						setTimeout(_this.name+".tree.add(new dNode({id: '"+treeData[i]['id']+"', caption: '"+treeData[i]['caption']+"', onFirstOpen: '', onClick: '"+_this.name+".setCatalog(\\'"+treeData[i]['id']+"\\');'}), '"+treeData[i]['pid']+"');", timeout);
						break;

					case 'empty':
						setTimeout(_this.name+".tree.add(new dNode({id: '"+treeData[i]['id']+"', caption: '"+treeData[i]['caption']+"', onFirstOpen: '', onClick: '"+_this.name+".setCatalog(\\'"+treeData[i]['id']+"\\');'}), '"+treeData[i]['pid']+"');", timeout);
				}

				timeout += 5;
			}
			
			_this.treeAvailable = true;

			if (open)
			{
				setTimeout(_this.name+"._getActualLevel(true)", timeout+10);
				//setTimeout(_this.name+".tree.openTo("+_this.name+".actualLevel);", timeout+100);
				//setTimeout(_this.name+".tree.getNodeById("+_this.name+".actualLevel)._select();", timeout+100);
			}
		};
		Connector.newRequest(this.name+'update', CC_url+'get_catalog_tree&level='+level, 'GET', handler);
	}
