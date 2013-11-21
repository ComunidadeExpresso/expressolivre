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
	 * ContactCenter API - Search for Entries Window
	 */

	function ccSearchClass(params)
	{
		if (!params || typeof(params) != 'object')
		{
			return false;
		}

		this.mount_handler = function (responseText)
		{
			var data = new Array();
			data = unserialize(responseText);
			
			if( !data )
				return false;

			if( data[0] == 0 )
			{
				if (_this.onSearchFinish)
					_this.onSearchFinish(null);
				return false;
			}
			
			if (data[3].length > 300)
			{
				alert("Mais de 300 resultados foram retornados! \n Favor refinar sua busca.");

				if (_this.onSearchFinish)
					_this.onSearchFinish(null);

				return false;
			}
			
			ccSearchUpdate();
	
			letter = 'search';

			if ( letter != CC_actual_letter )
			{
				CC_actual_page = '1';
			}
			else
			{
				CC_actual_page = parseInt(data[1]);
			}
	
			CC_actual_letter = letter;
	
			if (CC_max_cards[0] == 0)
			{
				if(CC_visual == 'cards')
					drawCards(0);
				else if(CC_visual == 'table')
					drawTable(0);
	
				setPages(0,0);
				return;
			}
	
			if (data[0] == '0')
			{
				Element('cc_type_contact').value = data[1];
				CC_npages = 0;
				CC_actual_page = 1;
				if(CC_visual == 'cards')
					drawCards(0);
				else if(CC_visual == 'table')
					drawTable(0);
				setPages(0,0);
				return;
			}
			else
			{
				Element('cc_type_contact').value = data[10];
			}
	
			if (typeof(data) != 'object')
			{
				showMessage(Element('cc_msg_err_contacting_server').value);
				return;
			}
	
			if (typeof(data[3]) == 'object')
			{
				CC_npages = parseInt(data[0]);
				CC_actual_page = parseInt(data[1]);
				if(CC_visual == 'cards')
					drawCards(data[3].length, data[10]);
				else if(CC_visual == 'table')
					drawTable(data[3].length, data[10]);
				resizeWindow();
				populateCards(data, data[10]);
				setPages(data[0], data[1]);
			}
			else if (data['error'])
			{
				showMessage(data['error']);
			}
			else
			{
				showMessage(Element('cc_msg_err_contacting_server').value);
				return;
			}
		};


		/* Attributes */
		this.onSearchFinish = null;
		this.onClose = null;
		this.onOpen = null;
		this.DOMholder = params['holder'];
		this.DOMdiv = document.createElement('div');
		this.DOMfields = document.createElement('select');
		this.DOMinput = document.createElement('input');
		this.DOMbtn = document.createElement('input');
		this.DOMAdv = document.createElement('input');
                this.DOMbtn2 = document.createElement('input');
                this.DOMbtn3 = document.createElement('input');
                this.DOMinputx = document.createElement('input');
		this.DOMprogHold = document.createElement('div');
		this.DOMresult = document.createElement('div');

		this.Connector = params['Connector'];
		this.Connector.setProgressContent(1, params['conn_1_msg']);
		this.Connector.setProgressContent(2, params['conn_2_msg']);
		this.Connector.setProgressContent(3, params['conn_3_msg']);
		this.Connector.setProgressHolder(this.DOMprogHold);

		/* Initialization */
		var _this = this;
		var spacer = document.createTextNode(' ');

		this.DOMdiv.style.position = 'relative';
		this.DOMdiv.style.display = 'inline';
		this.DOMdiv.style.width    = params['total_width'] ? params['total_width'] : params['input_width'] ? parseInt(params['input_width'])+210 + 'px' : '300px';
		//this.DOMdiv.style.height   = '25px';

		this.DOMfields.style.width = '50px';
		this.DOMfields.style.display = 'none';
		this.DOMfields.style.position = 'absolute';
		this.DOMfields.style.visibility = 'hidden';
		//this.DOMfields.style.height = parseInt(this.DOMdiv.style.height)/2 + 'px';

		this.DOMinput.type = 'text';
		this.DOMinput.value = params['value'] ? params['value'] : '';
                this.DOMinput.id = 'DOMinput';
		this.DOMinput.style.width = params['input_width'] ? params['input_width'] : '200px';
		this.DOMinput.onkeypress = function (e) { 
				if (is_ie)
				{
					if (window.event.keyCode == 13) _this.go();
				}
				else
				{
					if (e.which == 13) _this.go();
				}
			};

		this.DOMinputx.type = 'text';
		this.DOMinputx.value = params['value'] ? params['value'] : '';
                this.DOMinputx.id = 'DOMinputx';
		this.DOMinputx.style.width = '100px';
		this.DOMinputx.onkeypress = function (e) {
				if (is_ie)
				{
					if (window.event.keyCode == 13) _this.go();
				}
				else
				{
					if (e.which == 13) _this.go();
				}
			};

		//this.DOMinput.style.height = parseInt(this.DOMdiv.style.height)/2 + 'px';

		this.DOMbtn.type = 'button';
		//this.DOMbtn.style.height = parseInt(this.DOMdiv.style.height)/2 + 'px';
// 		this.DOMbtn.style.width = '60px';
		this.DOMbtn.value = params['button_text'];
		this.DOMbtn.onclick = function () {_this.go();};

		this.DOMbtn2.type = 'text';
		//this.DOMbtn2.style.height = parseInt(this.DOMdiv.style.height)/2 + 'px';
		this.DOMbtn2.style.width = '60px';
                this.DOMbtn2.disabled = 'disabled';
		this.DOMbtn2.value = 'Nome:';

		this.DOMbtn3.type = 'text';
		//this.DOMbtn3.style.height = parseInt(this.DOMdiv.style.height)/2 + 'px';
		this.DOMbtn3.style.width = '60px';
                this.DOMbtn3.disabled = 'disabled';
		this.DOMbtn3.value =  v_label + ':';

		var btn = this.DOMbtn.cloneNode(true);

		this.DOMAdv.type = 'button';
// 		this.DOMAdv.style.padding = '1px';
		this.DOMAdv.value = 'Busca Avancada';
		this.DOMAdv.id = 'advanced';
		this.DOMAdv.style.display = 'none';
		this.DOMAdv.onclick = function()
		{
		    var target = document.getElementById( 'Layer2' );

		    if( !target.innerHTML )
		    {
			var items = [];
			target.style.width = '';

			var config = [ { "cn":"Nome", "uid":"Matricula" }, advanced_search_fields ];

			for( var i = 0, counter = 2; i < config.length; i++ )
			    for( attr in config[i] )
				items[counter++] = "<tr><td style='padding: 3px'>"+config[i][attr]+"</td><td style='padding: 5px;'><input type='text' name='advanced_search' label='"+attr+"'><br></td></tr>";

			var itemH = document.createElement('DIV');

			itemH.innerHTML = items ? '<table>' + items.join("") + '</table>' : '';
			target.appendChild( itemH );
			target.onkeydown = function (e){
				e = e || window.event;
				var key = e.keyCode || e.witch;
				if (key == 13){
					_this.go();
					e.stopPropagation();
				}
			}
			target.style.left = "507px";

			var holder = document.createElement('DIV');
			holder.setAttribute('align','center');

			var exact_search = document.createElement('input');
			exact_search.type = 'checkbox';
			exact_search.id = 'exact_search';
			exact_search.setAttribute( "checked", "checked" );

			var label_exact_search = document.createElement('label');
			label_exact_search.setAttribute( "rel", exact_search.id );
			label_exact_search.innerHTML = 'Busca Exata';

			btn.onclick = function(){ _this.go(); };
			var closeBtn = document.createElement('input');
			closeBtn.value = 'Fechar';
			closeBtn.type = 'button';
			closeBtn.onclick = function(){ 
			    win.close();
			    document.getElementById('overlay').style.visibility = 'hidden';
			};
			
			holder.appendChild( exact_search );
			holder.appendChild( label_exact_search );
			holder.appendChild( spacer.cloneNode(false) );
			holder.appendChild( btn );
			holder.appendChild( spacer.cloneNode(false) );
			holder.appendChild( closeBtn );
			holder.appendChild( spacer.cloneNode(false) );

			holder.style.padding = '5px';
			target.style.padding = '5px';

			target.appendChild( holder );
			
			this.win = win = new dJSWin({
					id: 'ccSearch_Layer2',
					content_id: 'Layer2',
					width: '255px',
					height: ( counter * 31 ) +'px',
					title_color: '#3978d6',
					bg_color: '#eee',
					title: Element('cc_cs_title').value,						
					title_text_color: 'white',
					button_x_img: Element('cc_phpgw_img_dir').value+'/winclose.gif',
					border: true });

			this.win.draw();
		    }

		    this.win.open();
		};

		this.DOMprogHold.style.position = 'absolute';
		this.DOMprogHold.style.top = params['progress_top'] ? params['progress_top'] : '0px';
		this.DOMprogHold.style.left = params['progress_left'] ? params['progress_left'] : '0px';
		this.DOMprogHold.style.fontWeight = 'bold';
		this.DOMprogHold.style.width = params['progress_width'] ? params['progress_width'] : '200px';

		if (params['progress_color'])
			this.DOMprogHold.style.color = params['progress_color'];
		
		this.DOMresult.style.position = 'absolute';
		this.DOMresult.style.top = params['progress_top'] ? params['progress_top'] : '0px';
		this.DOMresult.style.left = params['progress_left'] ? params['progress_left'] : '0px';
		this.DOMresult.style.fontWeight = 'bold';
		this.DOMresult.style.width = params['progress_width'] ? params['progress_width'] : '200px';

		if (params['progress_color'])
			this.DOMresult.style.color = params['progress_color'];

		this.DOMholder.appendChild(this.DOMdiv);	
		this.DOMdiv.appendChild(this.DOMfields);
                this.DOMdiv.appendChild(this.DOMbtn2);
		this.DOMdiv.appendChild(this.DOMinput);
                if(v_label != false &  v_atrib != false)
                    {
                        this.DOMdiv.appendChild(this.DOMbtn3)
                        this.DOMdiv.appendChild(this.DOMinputx);
                    }
		this.DOMdiv.appendChild(spacer);
		this.DOMdiv.appendChild(this.DOMbtn);
		this.DOMdiv.appendChild(spacer.cloneNode(false));
		this.DOMdiv.appendChild(this.DOMAdv);
		this.DOMdiv.appendChild(this.DOMprogHold);
		this.DOMdiv.appendChild(this.DOMresult);
	}
	
	ccSearchClass.prototype.go = function()
	{
		var data = new Array();
		
		this.DOMresult.innerHTML = '';

		//TODO: Make Generic!
		var type = Element('cc_type_contact').value;
		
		data['fields']           = new Array();
		
		if (type == 'groups') {
			data['fields']['id']     = 'group.id_group';			
			data['fields']['search'] = 'group.title';
		}		
		else {			
			data['fields']['id']     = 'contact.id_contact';		
			data['fields']['search'] = 'contact.names_ordered';					
		}
		
		data['search_for']       = this.DOMinput.value;

//                 var customFilter = '(mail=*@mail.prognus*)(uid=*c120455*)';

		var customFilterDiv = document.getElementById( 'Layer2' );

                if( customFilterDiv && customFilterDiv.style.visibility !== 'hidden' )
		{
		    var customFilter = cn = '';
		
		    var values = document.getElementsByName('advanced_search');

		    for( var i = 0; i < values.length; i++ )
		    {
			if( !values[i].value ) continue;
			
			if( values[i].getAttribute('label') === 'cn' )
			{
			    cn = values[i].value;
			    continue;
			}

			customFilter += '(' + values[i].getAttribute('label') + '=*' + values[i].value.replace( / /g, '*' ) + '*)';
		    }

		    if( customFilter )
		    {
			if( document.getElementById('exact_search').checked )
			    data['exact'] = 'true';

			data['search_for'] = customFilter;
			data['CN'] = cn;
			data['custom_filter'] = true;
		    }
		    else
			data['search_for'] = cn;

		    win.close();
		    document.getElementById('overlay').style.visibility = 'hidden';
		}
		
//		var search_for = data['search_for'].split(' ');
//		var greaterThan4 = false;
//                var use_length = v_min;

//                if (search_for.length == 1)
//                    {
//                        if(search_for[0].length == 0)
//                            {
//                                search_for[0] = "*";
//                                data['search_for'] = "*";
//                                use_length = 1;
//                            }
//                    }

//		for (i = 0; i < search_for.length; i++)
//		{
//			if (search_for[i].length >= use_length)
//			{
//				greaterThan4 = true;
//			}
//		}

//		if (!greaterThan4){
//			alert("Favor fazer a consulta com pelo menos " + v_min + " caracteres!");
//			return;
//		}

		var _this = this;
		
		var handler = this.mount_handler;
        this.Connector.newRequest('search', CC_url+'search&data='+serialize(data), 'GET', handler);
	}
