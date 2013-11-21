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
	 * ContactCenter API - Add Group
	 *
	 * USAGE INSTRUCTIONS
	 */

	function cAddGroup ()
	{
		// Private
		this._card;
		this._button = new Array();		

		// Public
		this.window;
		this.afterSave;
		this.load;
		
		// Constructor
		var wHeight = 0;
		
		// Elements
		this.title = Element('title');
		this.contact_in_list = Element('contact_in_list');
		this.group_id = Element('group_id');
				
		this.old_contacts_in_list = new Array();
		
		ccAGWinHeight = 'ccAGWinHeightMO';

		if (is_ie)
			ccAGWinHeight = 'ccAGWinHeightIE';

		wHeight = Element(ccAGWinHeight).value;

		
		this.window = new dJSWin({
			id: 'ccAddGroup DOM',
			content_id: 'ccAddGroupContent',
			width: '700px',
			height: wHeight+'px',
			title_color: '#3978d6',
			bg_color: '#eee',
			title: Element('ccAGTitle').value,
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
	cAddGroup.prototype.associateAsButton = function (div)
	{
		var _this = this;		
		div.onclick = function() {
					
			if (_this.load)	{
				switch (typeof(_this.load)) {
				
					case 'function':
						_this.load();
						break;

					case 'string':
						eval(_this.load);
						break;
				}
			} 
		};
		
	}
		
	/*!
	
		@method send
		@abstract Sends data to server
		@author Raphael Derosso Pereira

	*/
	cAddGroup.prototype.send = function ()
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

			//showMessage(data['msg']);

			if (data['status'] != 'ok' && data['status'] != 'warning')
			{
				showMessage(Element('cc_msg_err_duplicate_group').value);
				return;
			}

			if(data['status'] == 'warning')
				showMessage(data['msg']);

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
		
		sdata[0] = this.title.value;
		var contacts = new Array();				
		var old_contacts = new Array();
		
		for (j =0; j < this.contact_in_list.length; j++)
			contacts[j] = this.contact_in_list.options[j].value;			
		for (i = 0; i < this.old_contacts_in_list.length; i++)
			old_contacts[i] = this.old_contacts_in_list[i];
		
		if(!this.title.value) {
			alert(Element('cc_msg_fill_field_name').value);
			this.title.focus();
			return false;
		}
				
		if(! contacts.length) {
			alert(Element('cc_msg_add_contact_to_group').value);
			return false;
		}
		
		//contatos sem email
		var user = "";
		for(i=0;i<this.contact_in_list.length;i++){
			user = this.contact_in_list[i].text;
			user = user.substr(user.indexOf("("),user.indexOf(")"));
			if (user.length == 2 && old_contacts.length != contacts.length){
				showMessage('Contato(s) adicionado(s),embora alguns não poderão enviar ou receber mensagens.');
				break;
			}	
		}
		sdata[1] = contacts;		
		sdata[2] = this.group_id.value == 'undefined' ? 	sdata[2] = 0 : sdata[2]  = this.group_id.value; 						
		sdata[3] = old_contacts;
		var sdata = 'add='+escape(serialize(sdata));
		Connector.newRequest('cAddGroup.Send', CC_url+'add_group', 'POST', handler, sdata);
	}

	/*!

		@method clear
		@abstract Clear all Plugin Fields
		@author Raphael Derosso Pereira

	*/
	cAddGroup.prototype.clear = function (reload)
	{
		for (j =0; j < this.contact_in_list.options.length; j++) {
			this.contact_in_list.options[j].selected = false;
			this.contact_in_list.options[j--] = null;
		}
		
		if(reload) {
			if(Element("contact_list"))
			for (j =0; j < Element("contact_list").options.length; j++) {
					Element("contact_list").options[j].selected = false;
					Element("contact_list").options[j--] = null;
			}
		}
			
		this.title.value = '';				
		ccAGSearchTerm.value = '';
	}
	
	cAddGroup.prototype.openEditWindow = function()
	{
		var list_old_contacts = Element('contact_in_list');
		for (var i = 0; i < list_old_contacts.length; i++)
		{
			this.old_contacts_in_list[i] = list_old_contacts.options[i].value;
		}
		this.window.open();
	};
	
	/* Função para remover contato da lista */	
	
	cAddGroup.prototype.remUser = function(){
		
		select_in = this.contact_in_list;								

		for(var i = 0;i < select_in.options.length; i++)				
			if(select_in.options[i].selected)
				select_in.options[i--] = null;
	}	
 	
	/* Função para adicionar contato na lista */	
	cAddGroup.prototype.addUser = function(){

		var select = Element("contact_list");
		var select_in = this.contact_in_list;
		
		for (i = 0 ; i < select.length ; i++) {				

			if (select.options[i].selected) {
				isSelected = false;

				for(var j = 0;j < select_in.options.length; j++) {																			
					if((select_in.options[j].value == select.options[i].value) && (select_in.options[j].textContent == select.options[i].textContent)){
						isSelected = true;						
						//break;	
					}
				}

				if(!isSelected){

					option = document.createElement('option');
					option.value =select.options[i].value;
					option.text = select.options[i].text;
					option.selected = true;
					select_in.options[select_in.options.length] = option;
											
				}
												
			}
		}
		
		for (j =0; j < select.options.length; j++)
			select .options[j].selected = false;		
	} 	

        cAddGroup.prototype.setSelectedSourceLevel = function(sourceLevel)
        {
            var ccAGSourceSelect = Element('ccAGSourceSelect');
            var selectedLevel = '';
            for (i = 0; i < ccAGSourceSelect.length; i++)
            {
                if (ccAGSourceSelect[i].selected == true)
                {
                    selectedLevel = ccAGSourceSelect[i].value;
                }
            }

            if (selectedLevel != sourceLevel)
            {
                for (i = 0; i < ccAGSourceSelect.length; i++)
                {
                    if (ccAGSourceSelect[i].value == sourceLevel)
                    {
                        ccAGSourceSelect[i].selected = true;
                    }
                }
            }
            this.setCatalog();
        }

        cAddGroup.prototype.loadPersonalContacts = function(){
            handler = function(data)
            {
                if (data)
                {
                    data = unserialize(data);
                    if (data.result == 'ok')
                    {
                        var options_contact_list = Element('span_contact_list');
                        var select_contact_list = '<select id="contact_list" multiple name="contact_list[]" style="width:280px" size="10">';
                        select_contact_list += data['contact_list'] + "</select>";
                        options_contact_list.innerHTML = select_contact_list;
                    }
                    return;
                }

                alert(get_lang('Erro ao carregar contatos pessoais.'));


            }

            Connector.newRequest('cAddGroup.loadPersonalContacts', CC_url+'get_group', 'GET', handler);

        }

        cAddGroup.prototype.clearSourceList = function(){
            var contact_list = Element("contact_list");
            var options = contact_list.options;
            for (j =0; j < options.length; j++) {
                contact_list.options[j].selected = false;
                contact_list.options[j--] = null;
            }
        }

        // usar ui_data.get_cards_data('all', 1);
        cAddGroup.prototype.search = function(){

            var type = Element('cc_type_contact').value;

            var data = new Array();
            data['fields'] = new Array();

            // never search groups
            data['fields']['id']     = 'contact.id_contact';
            data['fields']['search'] = 'contact.names_ordered';

            var ccAGSearchTerm = Element('ccAGSearchTerm');

            data['search_for'] = ccAGSearchTerm.value;
            data['ccAddGroup'] = true;

            var invalidChars = /[\%\?]/;
            if(invalidChars.test(data['search_for']) || invalidChars.test(data['search_for_area'])){
                showMessage(Element('cc_msg_err_invalid_serch').value);
                return;
            }

            var search_for = data['search_for'].split(' ');
            //var greaterThan4 = false;
            //var use_length = v_min;

            //for (i = 0; i < search_for.length; i++)
            //{
            //    if (search_for[i].length >= use_length)
            //    {
            //        greaterThan4 = true;
            //    }
            //}

            // if (!greaterThan4){
            //     alert("Favor fazer a consulta com pelo menos " + v_min + " caracteres!");
			//     return;
            // }

            var handler = function(data)
            {
                data = unserialize(data);

                if( !data || data[0] == 0){
                    
                     var contact_list = Element('contact_list');
                     for (var i = contact_list.options.length - 1; i >= 0; i--){
                         contact_list.options[i] = null;
                     }
                     contact_list.selectedIndex = -1;
                     return false;
                }
                   
                ccAddGroup.clearSourceList();
                ccAGSearchTerm.value = '';
                if (typeof(data) != 'object')
                {
                    showMessage(Element('cc_msg_err_contacting_server').value);
                    return false;
                }

                if (data[3].length > 300)
                {
                    alert("Mais de 300 resultados foram retornados! \n Favor refinar sua busca.");

                    return false;
                }

                var contact_list = Element('contact_list');
                for (var i=0; i < data[3].length; i++)
                {

                   var item = data[3][i];
                   if (data[8] == 'bo_shared_people_manager' || data[8] == 'bo_people_catalog'){
                       var id = data[3][i][13];
                   }
                   else
                   {
                       var id = 'ldap:'+data[11]+':'+item[6];
                   }
                   var option = document.createElement('OPTION');
                   option.value = id;
                   option.text = item[1]+' ('+item[4]+')';
                   contact_list.options[contact_list.options.length] = option;
                }

            }

            Connector.newRequest('ccAGSearch', CC_url+'search&data='+serialize(data), 'GET', handler);

        }

        cAddGroup.prototype.setCatalog = function(){
            var select = Element('ccAGSourceSelect');
            var catalogLevel = '0.0';
            for (i = 0 ; i < select.length ; i++) {
                if (select.options[i].selected)
                {
                    catalogLevel = select.options[i].value;
                    break;
                }

            }
            ccTree.select(catalogLevel);

            if (catalogLevel == '0.0')
            {
                ccAddGroup.loadPersonalContacts();
            }
            else
                {
                   ccAddGroup.clearSourceList();
				   if (catalogLevel == '0.2')
						ccAddGroup.search();
                }

            //eval(refresh);

        }
        
	/* Build the Object */
	var ccAddGroup ;
	var cAddGroup_pre_load = document.body.onload;
	/* Se for IE, modifica a largura da coluna dos botoes.*/	
	if(document.all)
		document.getElementById('buttons').width = 140;	

	if (is_ie)
	{ 
		document.body.onload = function (e) 
		{ 
			cAddGroup_pre_load();
			ccAddGroup = new cAddGroup();
			
		};
	}
	else
	{
		ccAddGroup = new cAddGroup();
	}
