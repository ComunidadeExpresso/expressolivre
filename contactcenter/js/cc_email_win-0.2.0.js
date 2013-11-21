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
	 * ContactCenter API - Email Gathering Window
	 */

	function ccEmailWinClass(params)
	{
		if (typeof(params) != 'object')
		{
			return false;
		}

		this.window = params['window'];
		this.search_win = params['search'];
		
		this.entries = Element('cc_email_win_entries');
		this.to  = Element('cc_email_win_to');
		this.cc  = Element('cc_email_win_cc');
		this.cco = Element('cc_email_win_cco');

		this.to_count  = 0;
		this.cc_count  = 0;
		this.cco_count = 0;

		this.contents = new Array();

		this.onOk = null;

		this.entries.style.overflow = 'auto';
	}

	ccEmailWinClass.prototype.setContents = function(data)
	{
		if (typeof(data) != 'object')
		{
			return false;
		}

		this.clearAll();
		
		if (data['entries'])
		{
			for (var i in data['entries'])
			{
				this.entries = new Option(data['entries'][i], i);
			}
		}
		
		if (data['to'])
		{
			for (var i in data['to'])
			{
				this.to = new Option(data['to'][i], i);
			}
		}

		if (data['cc'])
		{
			for (var i in data['cc'])
			{
				this.cc = new Option(data['cc'][i], i);
			}
		}

		if (data['cco'])
		{
			for (var i in data['cco'])
			{
				this.cco = new Option(data['cco'][i], i);
			}
		}
	}

	ccEmailWinClass.prototype.getContents = function()
	{
		var i;
		
		this.contents = new Array();
		this.contents['entries'] = new Array();
		this.contents['to'] = new Array();
		this.contents['cc'] = new Array();
		this.contents['cco'] = new Array();
		
		for (i = 0; i < this.entries.options.length; i++)
		{
			this.contents['entries'][this.contents['entries'].length] = this.entries.options[i].text;
		}

		for (i = 0; i < this.to.options.length; i++)
		{
			this.contents['to'][this.contents['to'].length] = this.to.options[i].text;
		}

		for (i = 0; i < this.cc.options.length; i++)
		{
			this.contents['cc'][this.contents['cc'].length] = this.cc.options[i].text;
		}
		
		for (i = 0; i < this.cco.options.length; i++)
		{
			this.contents['cco'][this.contents['cco'].length] = this.cco.options[i].text;
		}
		
		return this.contents;
	}

	ccEmailWinClass.prototype.open = function()
	{
		this.window.open()
	}
	
	ccEmailWinClass.prototype.ok = function()
	{
		if (this.onOk)
		{
			this.onOk(this.getContents());
		}

		this.clearAll();
		this.window.close();
	}
	
	ccEmailWinClass.prototype.clearAll = function()
	{
		this.clearEntries();
		this.clearTo();
		this.clearCC();
		this.clearCCO();
	}

	ccEmailWinClass.prototype.clearEntries = function()
	{
		var length;

		if (this.entries.options.length)
		{
			length = this.entries.options.length-1;
			for (var i = length; i >= 0; i--)
			{
				this.entries.removeChild(this.entries.options[i]);
			}
		}
	}

	ccEmailWinClass.prototype.clearTo = function()
	{
		var length;
		
		if (this.to.options.length)
		{
			length = this.to.options.length-1;
			for (var i = length; i >= 0; i--)
			{
				this.to.removeChild(this.to.options[i]);
			}
		}
	}
	
	ccEmailWinClass.prototype.clearCC = function()
	{
		var length;
		
		if (this.cc.options.length)
		{
			length = this.cc.options.length-1;
			for (var i = length; i >= 0; i--)
			{
				this.cc.removeChild(this.cc.options[i]);
			}
		}
	}

	ccEmailWinClass.prototype.clearCCO = function()
	{
		var length;
		
		if (this.cco.options.length)
		{
			length = this.cco.options.length-1;
			for (var i = length; i >= 0; i--)
			{
				this.cco.removeChild(this.cco.options[i]);
			}
		}
	}
	
	ccEmailWinClass.prototype.close = function()
	{
		this.clearAll();
		this.search_win.close();
		this.window.close();
	}

	ccEmailWinClass.prototype.search = function()
	{
/*		if (document.all)
		{
			this.search_win.onOpen  = this.window.close;
			this.search_win.onClose = this.window.open;
		}
*/		
		this.search_win.open();
		this.search_win.window.moveTo(this.window.x()+20, this.window.y()+20);

		var window = this.window;
		var entries = this.entries;
		var _this = this;
		
		this.search_win.onSearchFinish = function (result)
		{
			if (!result || typeof(result) != 'object')
			{
				showMessage('Error getting result from search');
				return false;
			}

			var sdata = new Array();
			
			sdata['ids'] = result;
			sdata['fields'] = new Array()
			sdata['fields']['names_ordered'] = true;
			sdata['fields']['connections'] = true;
			
			var str_data = 'data='+serialize(sdata);

			var obj = httpRequestObj();

			var handler = function ()
			{
				try
				{
					setLoadingState(obj.readyState);
					if (obj.readyState == 4 && obj.status == 200)
					{
						//document.all ? _this.window.open() : false;
						//Element('cc_email_win_debug').innerHTML = obj.responseText;
						var data = unserialize(obj.responseText);
						
						if (!data)
						{
							//showMessage(Element('cc_msg_err_invalid_catalog').value);
							showMessage('Error getting user Info');
							return false;
						}

						_this.clearEntries();

						if (data['status'] == 'empty')
						{
							showMessage(data['msg']);
							return false;
						}

						if (data['status'] != 'ok')
						{
							showMessage(data['msg']);
							return false;
						}

						//showMessage(data['msg']);

						var name = '';
						var emails = new Array();
						for (var i in data['data'])
						{
							emails_count = 0;
							for (var j in data['data'][i])
							{
								if (j == 'names_ordered')
								{
									name = '"'+data['data'][i][j]+'"';
								}
								else if (j == 'connections')
								{
									for (var k in data['data'][i][j])
									{
										if (data['data'][i][j][k]['id_type'] == Element('cc_email_id_type').value)
										{
											emails[emails.length] = ' <'+data['data'][i][j][k]['connection_value']+'>';
										}
									}
								}
							}

							if (name != '' && emails.length)
							{
								for (var j in emails)
								{
									entries.options[entries.options.length] = new Option(name+emails[j], i+'_'+j);
								}
							}
							
							name = '';
							emails = new Array();
						}
					}
				}
				catch (e)
				{
					showMessage(e);
				}
				
			};

			httpRequest(obj, CC_url+'get_multiple_entries', 'POST', handler, str_data);
		};
	}
	
	ccEmailWinClass.prototype.entries_to = function()
	{
		var i;
		var length = this.entries.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.entries.options[i].selected)
			{
				this.to.options[this.to.options.length] = new Option(this.entries.options[i].text, this.entries.options[i].value);
			}
		}
	}

	ccEmailWinClass.prototype.entries_cc = function()
	{
		var i;
		var length = this.entries.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.entries.options[i].selected)
			{
				this.cc.options[this.cc.options.length] = new Option(this.entries.options[i].text, this.entries.options[i].value);
			}
		}
	}

	ccEmailWinClass.prototype.entries_cco = function()
	{
		var i;
		var length = this.entries.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.entries.options[i].selected)
			{
				this.cco.options[this.cco.options.length] = new Option(this.entries.options[i].text, this.entries.options[i].value);
			}
		}
	}

	ccEmailWinClass.prototype.to_entries = function()
	{
		var i;
		var length = this.to.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.to.options[i].selected)
			{
				this.to.removeChild(this.to.options[i]);
			}
		}
	}

	ccEmailWinClass.prototype.cc_entries = function()
	{
		var i;
		var length = this.cc.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.cc.options[i].selected)
			{
				this.cc.removeChild(this.cc.options[i]);
			}
		}
	}

	ccEmailWinClass.prototype.cco_entries = function()
	{
		var i;
		var length = this.cco.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.cco.options[i].selected)
			{
				this.cco.removeChild(this.cco.options[i]);
			}
		}
	}

	/****************************************************************************\
	 *                        Auxiliar Functions                                *
	\****************************************************************************/
