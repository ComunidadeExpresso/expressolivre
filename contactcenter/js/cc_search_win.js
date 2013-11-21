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

	/*
	 * ContactCenter API - Search for Entries Window
	 */

	function ccSearchWinClass(params)
	{
		if (!params || typeof(params) != 'object')
		{
			return false;
		}

		this.window = params['window'];
		this.catalogs_area = Element('cc_search_catalogues');
		this.search_for = Element('cc_search_for');
		//this.recursive = Element('cc_search_recursive');

		this.onSearchFinish = null;
		this.onClose = null;
		this.onOpen = null;

		/* Populate Catalogues */
		this.catalogues = new ccCatalogTree({name: 'ccSearchWin.catalogues',
		                                     id_destination: 'cc_search_catalogues'});
	}
	
	ccSearchWinClass.prototype.open = function()
	{
		if (this.onOpen)
		{
			this.onOpen();
		}

		this.window.open();
	}

	ccSearchWinClass.prototype.close = function()
	{
		if (this.onClose)
		{
			this.onClose();
		}
		this.window.close();
	}

	ccSearchWinClass.prototype.go = function()
	{
		var data = new Array();

		//TODO: Make Generic!
		data['fields']           = new Array();
		data['fields']['id']     = 'contact.id_contact';
		data['fields']['search'] = 'contact.names_ordered';
		data['search_for']       = this.search_for.value;
		//data['recursive']        = this.recursive.checked ? true : false;
		
		var obj = httpRequestObj();
		var win = this;

		var handler = function ()
		{
			try
			{
				setLoadingState(obj.readyState);
				if (obj.readyState == 4 && obj.status == 200)
				{
					Element('cc_debug').innerHTML = obj.responseText;
					var data = unserialize(obj.responseText);
					
					if (!data || !data['status'])
					{
						showMessage('Error Contacting Server');
						return false;
					}

					if (data['status'] == 'empty')
					{
						showMessage(data['msg']);
						win.close();

						if (win.onSearchFinish)
						{
							win.onSearchFinish(null);
						}
						return false;
					}

					if (data['status'] != 'ok')
					{
						showMessage(data['msg']);
						return false;
					}

					//showMessage(data['msg']);
					win.close();

					if (win.onSearchFinish)
					{
						win.onSearchFinish(data['data']);
					}
				}
			}
			catch (e)
			{
				//showMessage(e);
			}
			
		};

		httpRequest(obj, CC_url+'search&data='+serialize(data), 'GET', handler);
	}
