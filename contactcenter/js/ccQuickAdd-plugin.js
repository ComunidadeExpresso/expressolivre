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
	 * ContactCenter API - Quick Add Plugin
	 *
	 *
	 * USAGE INSTRUCTIONS
	 *
	 *  Just insert in your PHP code the following statement:
	 * 
	 *  $ccContents = ExecMethod('contactcenter.ui_api.get_quick_add_plugin');
	 *
	 *  and $ccContents becomes a valid HTML that inserts everything that is needed
	 *  to use the JS Objects that Contact Center provides. Just remember that you
	 *  need to insert it before your JS code, so all objects are instantiated 
	 *  correctly.
	 *
	 * Provides Objects:
	 *
	 *	ccQuickAdd
	 *
	 * Provides Classes:
	 *
	 *  dJSWin, dTabsManager, dFTree
	 *
	 */

	function cQuickAdd ()
	{
		// Private
		this._card;
		this._button = new Array();
		this._fields;

		// Public
		this.window;
		this.afterSave;
		
		// Constructor
		var wHeight = 0;
		this._nFields = Element('ccQAnFields').value;
		this._fields = new Array();

		for (var i = 0; i < this._nFields; i++)
		{
			this._fields[i] = Element('ccQuickAddI'+i);
		}

		//wHeight = (i+1)*25+5;
		wHeight = Element('ccQAWinHeight').value;

		
		this.window = new dJSWin({
			id: 'ccQuickAddDOM',
			content_id: 'ccQuickAddContent',
			width: '262px',
			height: wHeight+'px',
			title_color: '#3978d6',
			bg_color: '#eee',
			title: Element('ccQATitle').value,
			title_text_color: 'white',
			button_x_img: Element('cc_phpgw_img_dir').value+'/winclose.gif',
			border: true });

		this.window.draw();
	}

	/*!
		@method associateAsButton
		@abstract Associates the button functions with the spacified DOM Element
		@author Raphael Derosso Pereira

		@param div DOMElement The HTML DOM element that will "host" the
			plugin button.

		@param func function The function that returns the data to be used
			to pre-populate the quickAdd fields. The return format MUST be
			an Array like:

				var return_data = new Array();
				return_data[0] = <value>;  // Value for the first field
				return_data[1] = <value>;  // Value for the second field
				...
		
	 */
	cQuickAdd.prototype.associateAsButton = function (div, func)
	{
		var _this = this;
		
		if (func)
		{
			div.onclick = function() { _this.window.open(); _this.setValues(func()); };
		}
		else
		{
			div.onclick = function() { _this.window.open(); };
		}
	}

	/*!

		@method setValues
		@abstract Set the contents of the QuickAdd window with the specified
			data
		@author Raphael Derosso Pereira

		@param data Array The data to be used

	*/
	cQuickAdd.prototype.setValues = function (data)
	{
		for (var i in data)
		{ 
			this._fields[i].value = data[i];
		}

		this.data = data;
	}

	/*!
	
		@method send
		@abstract Sends data to server
		@author Raphael Derosso Pereira

	*/
	cQuickAdd.prototype.send = function ()
	{
		var _this = this;
		var handler = function (responseText)
		{
			Element('cc_debug').innerHTML = responseText;

			var data = unserialize(responseText);

			if (!data || typeof(data) != 'object')
			{
				showMessage(Element('cc_msg_err_contacting_server').value);
				return;
			}
			else if (data['status'] == 'alreadyExists')
			{
				showMessage(data['msg']);
				return;
			}
			else if (data['status'] != 'ok')
			{
				return;
			}

			_this.clear();
			_this.window.close();

			if (_this.afterSave)
			{
				switch (typeof(_this.afterSave))
				{
					case 'function':
						_this.afterSave();
						break;

					case 'string':
						eval(_this.afterSave);
						break;
				}
			}
		}

		var sdata = new Array();
		var empty = true;
		
		for (var i in this._fields)
		{
			sdata[i] = this._fields[i].value;
			if (sdata[i] != '')
			{
				empty = false;
			}
		}

		if(!$.trim(sdata[1])){ 
 	        alert("O campo nome é obrigatório(a)!"); 
 	        return false; 
 	    }
		
		if (empty) return false;

		if(this._fields[1].value=='') {
			alert(Element('cc_msg_name_mandatory').value);
			return false;
		}
		//Utiliza expressão regular para validar email
		if (this._fields[4].value != ''){
			var reEmail = /^[a-zA-Z0-9][_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]{1,})*$/;
			
			if (sdata[4] !== "" && !reEmail.test(this._fields[4].value)) {
				alert("O endereço de email '" + this._fields[4].value + "' não é válido!\n" +
				"Por favor informe um endereço válido.");
				return false;
			}
		}
		sdata[4] = sdata[4].toLowerCase();
		var sdata = 'add='+escape(serialize(sdata));

		Connector.newRequest('cQuickAdd.Send', CC_url+'quick_add', 'POST', handler, sdata);
	}

	/*!

		@method clear
		@abstract Clear all Plugin Fields
		@author Raphael Derosso Pereira

	*/
	cQuickAdd.prototype.clear = function ()
	{
		for (var i in this._fields)
		{
			this._fields[i].value = '';
		}
	}


	/* Build the Object */
	var ccQuickAdd;
	var cQuickAdd_pre_load = document.body.onload;

	if (is_ie)
	{ 
		document.body.onload = function (e) 
		{ 
			cQuickAdd_pre_load();
			ccQuickAdd = new cQuickAdd();
			
		};
	}
	else
	{
		ccQuickAdd = new cQuickAdd();
	}
