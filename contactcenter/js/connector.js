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

	function cConnector()
	{
		/* Public Attributes */
		this.requests = new Array();
		this.progressContents = new Array();
		this.visible = false;

		var _this = this;

		/* Private Attributes */
		this._progressBox = null;
		this._progressHolder = document.createElement('span');
		this._progressBlank   = null;

		this._progressHolder.style.visibility = 'hidden';
//		this._progressHolder.style.backgroundColor = '#db7e22';
	}

	cConnector.prototype.newRequest = function (id, target, method, handler, data)
	{
		var _this = this;
		
		if (this.requests[id] && this.requests[id] != null)
		{
			
			//this.requests[id].abort();
			//delete this.requests[id];
			//this.requests[id] = null;
			
			//setTimeout(function() { _this.newRequest(id, target, method, handler, data); }, 100);

			return;
		}

		var oxmlhttp = null;
		
		try
		{ 
			oxmlhttp = new XMLHttpRequest();
			oxmlhttp.overrideMimeType('text/xml');
		}
		catch (e)
		{ 
			try
			{
				oxmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
			}
			catch (e1)
			{ 
				try
				{
					oxmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
				}
				catch (e2)
				{
					oxmlhttp = null;
				}
			}
		}
		
		if (!oxmlhttp)
		{
			return false;
		}
		
		this.requests[id] = oxmlhttp;

		var _this = this;
		
		var sub_handler = function ()
		{
			try
			{
				_this._setProgressState(oxmlhttp.readyState);
				
				if (oxmlhttp.readyState == 4 )//&& oxmlhttp.channel.status == 0)
				{
					switch (oxmlhttp.status)
					{
						case 200:
							if (typeof(handler) == 'function')
							{
								// Session expired, redirect to login page
								if(oxmlhttp.responseText.match(/login\.php\?phpgw_forward/))
									location.href="../login.php?cd=10&phpgw_forward=%2Fcontactcenter%2Findex.php";
								else
									handler(oxmlhttp.responseText);
							}
							delete _this.requests[id];
							_this.requests[id] = null;
							break;

						case 404:
							alert('Page Not Found!');
							break;

						default:
							//alert('Some problem while accessing the server. The status is '+oxmlhttp.status);
					}
				}
			}
			catch (e)
			{
				//showMessage(e);
			}
		}

		try
		{ 
			if (method == '' || method == 'GET')
			{
				if (typeof(handler) == 'function')
				{
					oxmlhttp.onreadystatechange = sub_handler;
				}
				oxmlhttp.open("GET",target,true);
				oxmlhttp.send(null);
			}
			else if (method == 'POST')
			{
				if (typeof(handler) == 'function')
				{
					oxmlhttp.onreadystatechange = sub_handler;
				}
				oxmlhttp.open("POST",target, true);
				oxmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				oxmlhttp.send(data);
				//oxmlhttp.setRequestHeader('Content-Type','multipart/form-data; boundary=-----------------------------1156053686807595044986274307');
				//oxmlhttp.setRequestHeader('Accept', 'text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5');
			}
		}
		catch(e)
		{ 
			//showMessage(e);
		}
		
		return true;
	}

	cConnector.prototype.cancelRequest = function (id)
	{
		if (!this.requests[id])
		{
			return false;
		}

		this.requests[id].abort();
	}

	cConnector.prototype.setProgressContent = function (state, content)
	{
		switch (state)
		{
			case 0:
			case 'UNINITIALIZED':
				this.progressContents[0] = content;
				break;

			case 1:
			case 'LOADING':
				this.progressContents[1] = content;
				break;

			case 2:
			case 'LOADED':
				this.progressContents[2] = content;
				break;

			case 3:
			case 'INTERACTIVE':
				this.progressContents[3] = content;
				break;

			case 4:
			case 'COMPLETED':
				this.progressContents[4] = content;
				break;

			default:
				throw('INVALID STATE!');
		}
	}

	cConnector.prototype.setProgressHolder = function (holder)
	{
		var objHolder;
		
		if (typeof(holder) == 'string')
		{
			objHolder = Element(holder);
		}
		else if (typeof(holder) == 'object')
		{
			objHolder = holder;
		}
		else
		{
			return false;
		}

		objHolder.appendChild(this._progressHolder);
	}

	cConnector.prototype.setProgressBox = function (box, auto)
	{
		var objBox;

		if (typeof(box) == 'string')
		{
			objBox = Element(box);
		}
		else if (typeof(box) == 'object')
		{
			objBox = box;
		}
		else
		{
			return false;
		}

		this._progressBox = objBox;
		this._progressBoxAuto = auto ? true : false;
	}

	cConnector.prototype.setVisible = function (visible)
	{
		this.visible = visible;
		if (!visible)
		{
			this._progressHolder.style.visibility = 'hidden';
		}
	}

	/****************************************************************************\
	 *                          Private Methods                                 *
	\****************************************************************************/
	
	cConnector.prototype._setProgressState = function (state)
	{
		switch (state)
		{
			case 0:
			case 4:
				if (this._progressBox != null)
				{
					this._progressBox.style.visibility = 'hidden';
					this._progressBox.style.zIndex = '-1';
				
					if (is_ie && this._progressBlank)
						this._progressBlank.style.visibility = 'hidden';
				}
				
				this._progressHolder.style.visibility = 'hidden';
				this._progressHolder.style.zIndex = '-1';
				
				if (is_ie && this._progressBlank)
					this._progressBlank.style.visibility = 'hidden';
				
				break;

			default:
				if (this.visible && Element('cc_connector_visible').value == 'true')
				{
					if (this._progressBox != null)
					{
						if (this._progressBoxAuto)
						{
							if (is_ie)
							{
								this._progressBox.style.top = parseInt(document.body.offsetHeight)/2 + 'px';
								this._progressBox.style.left = parseInt(document.body.offsetWidth)/2 + 'px';
								this._progressBlank = document.getElementById('divBlank');								
								if(! this._progressBlank ) {
									document.body.insertAdjacentHTML("beforeEnd", '<iframe id="divBlank" src="about:blank" style="position:absolute" scrolling="no" frameborder="0"></iframe>');
									this._progressBlank = document.getElementById('divBlank');
									this._progressBlank.style.top = parseInt(document.body.offsetHeight)/2 + 'px';
									this._progressBlank.style.left = parseInt(document.body.offsetWidth)/2 + 'px';
									this._progressBlank.style.height = "36px";
									this._progressBlank.style.width = "130px";
								}
							}
							else
							{
								this._progressBox.style.top = parseInt(window.innerHeight)/2 + parseInt(window.pageYOffset) - this._progressBox.style.height/2 + 'px';
								this._progressBox.style.left = parseInt(window.innerWidth)/2 + parseInt(window.pageXOffset) - this._progressBox.style.width/2 + 'px';
							}
						}
						this._progressBox.style.visibility = 'visible';
						this._progressBox.style.zIndex = '1000';
					}
					
					this._progressHolder.style.visibility = 'visible';
					this._progressHolder.style.zIndex = '1000';
					
					this._progressHolder.innerHTML = this.progressContents[state] ? this.progressContents[state] : '';

					if(is_ie) {
						this._progressBlank.style.visibility = 'visible';
						this._progressBlank .style.zIndex = '999';
					}					
					
				}
		}
	}
