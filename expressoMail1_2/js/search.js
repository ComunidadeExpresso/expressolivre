	xhr = false;

    // Comentei a linha abaixo por ocorrer alguns problema na abertura de uma nova mensagem após uma pesquisa realizada.
    // Caso essa linha está sendo utilizada em algum lugar, por favor coloque algum tratamento ou aplique uma nova solução. 
	// var folder 		= {};
	
	function searchE()
	{
		this.totalPages			= 1; 
		this.numPages			= 5; 
		this.lastPage			= 0; 
		this.searchW   			= [];
		this.condition			= "";
		this.sort_type			= "";
		this.name_box_search	= "";
		this.all_boxes			= [];
		this.type				= "";
		this.txtfields 			= new Array("txt_ass","txt_de","txt_body","txt_para","txt_cc","txt_cco", "since_date", "before_date", "on_date");
		this.selectFields		= new Array("flagged", "seen", "answered", "recent");
		this.divElement 		= null;
		this.elementChecked 	= false;
		this.modal				= "";
	}
	
	//Monta os forms dentro da janela;
	searchE.prototype.showForms = function(value, data)
	{
		if( trim(value) != "" )
		{
			if (Element("check_all_msg"))	
				Element("check_all_msg").checked = true;
			
		   /* flag de controle. Evita o acesso ao elemento "check_all_msg" que será inserido no documento 
			* apenas quando o template for carregado
			*/
			
			this.elementChecked = true;	
			EsearchE.all_mailboxes();
			EsearchE.func_search( value, null, 'SORTDATE_REVERSE' );
		}
		else
		{
			var div		= document.createElement("div");
			var args	= null;
			
			args = 
			{
				"button_left"  				: "<<",
				"button_right" 				: ">>",
				"Inform_your_search_in_the_text_fields" : get_lang('Inform your search in the text fields'),
				"From" 						: get_lang('From'),
				"To" 						: get_lang('To'),
				"Cc" 						: get_lang('Cc'),
				"Subject" 					: get_lang('Subject'),
				"Message_body" 				: get_lang('Message body'),
				"Since_Date" 				: get_lang('Since Date'),
				"Before_Date" 				: get_lang('Before Date'),
				"On_Date" 					: get_lang('On Date'),
				"Flags" 					: get_lang('Flags'),
				"Flagged" 					: get_lang('Flagged'),
				"Unflagged" 				: get_lang('Unflagged'),
				"Seen" 						: get_lang('Seen'),
				"Unseen" 					: get_lang('Unseen'),
				"Answered_Forwarded" 		: get_lang('Answered/Forwarded'),
				"Unanswered_Unforwarded"	: get_lang('Unanswered/Unforwarded'),
				"Recent" 					: get_lang('Recent'),
				"Old" 						: get_lang('Old'),
				"Search_the_messages_in_these_folders" : get_lang('Search the messages in these folders'),
				"In_all_the_folders"		: get_lang('In all the folders'),
				"From_value" : (data != "undefined" ? data : "")
			}
				
			$(div).html(DataLayer.render("./templates/default/searchMails.ejs", args ));
			div.setAttribute( "style","overflow:hidden");
			
			this.modal = $(div).dialog(
			{
				resizable	: false,
				title		: get_lang("Search Email"),
				position	: 'center',
				width		: 795,
				height		: 450,
				modal		: true,
				buttons		: [
								{
									text: get_lang("Close"),
									click: function()
									{
										$(this).dialog("close");
									} 
								},
								{
									text: get_lang("Clean"),
									click: function()
									{
										EsearchE.func_clean();
									}
								},
								{
									text: get_lang("Search"),
									click: function()
									{
										EsearchE.func_search();
									}
								}
				],
				beforeClose	: function()
				{ 
					$("#sel_search_nm_box1")[0].parentNode.removeChild($("#sel_search_nm_box1")[0]);
					$("#divFoldersSearch")[0].parentNode.removeChild($("#divFoldersSearch")[0]);
					//$(this).dialog("destroy");
					$('fieldset', div).remove();
				},
                close:function(event, ui) 
                {
                    if(typeof(shortcut) != 'undefined') shortcut.disabled = false; 
                },
                open: function(event, ui) 
                {
                    if(typeof(shortcut) != 'undefined') shortcut.disabled = true; 
                }
			});
			
			this.divElement = div.parentNode;
			
			if( !Element("table_layer") )
			{
				var table_layer    = "";
			}
	
			// Cria as caixas postais;
			this.foldersTree();
			
			var dates = $("#since_date, #before_date").datepicker({
				onSelect: function( selectedDate ) {
					var option = this.id == "since_date" ? "minDate" : "maxDate",
						instance = $( this ).data( "datepicker" ),
						date = $.datepicker.parseDate(
							instance.settings.dateFormat ||
							$.datepicker._defaults.dateFormat,
							selectedDate, instance.settings );
					dates.not( this ).datepicker( "option", option, date );
				}
			});
			
			$("#on_date").datepicker();
		}
	}
	
	//draws folder list
	searchE.prototype.foldersTree = function()
	{
		/*Insere a árvore de diretórios*/

		var foldersTree = jQuery("#divFoldersSearch")
		.removeClass('empty-container')
		.html(DataLayer.render(BASE_PATH + 'api/templates/foldertree.ejs', {folders: [cp_tree1, cp_tree2, cp_tree3 ]}))
		.find("#foldertree").treeview()
		.click(function(event)
		{
			//request new selected folder messages
			var target = $(event.target);

			if( target.is('.collapsable-hitarea, .expandable-hitarea, .lastCollapsable, .lastExpandable, .treeview') )
			    return;

			if( !target.attr('id') )
			    target = target.parent();

	        if (target.attr('id') == "foldertree") return;
			
			folder 			= {};
			folder.id 		= target.attr('id');
			folder.child 	= target.find('.folder');
			folder.caption	= target.find('span').attr('title');			
			
			$('.filetree span.folder.selected').removeClass('selected');
			if(!target.is('#foldertree > .expandable, #foldertree > .collapsable'))
				$(target).children('.folder').addClass('selected');

			selectedFolder = {
			    id: folder.id, 
			    name: folder.child.attr('title'),
			    'class': folder.child.attr('class')
			};
		});

	}
	
	function openpage(data)
	{
		var _data			= [3];
		var _gears			= [];
    	var local_folders 	= [];

    	// Gears - local
		if ( preferences.use_local_messages == 1 )
		{
			temp = expresso_local_messages.list_local_folders();
			for (var x in temp)
			{
				local_folders.push(temp[x][0]);
			}
 		}
		
		if ( local_folders.length > 0 )
			_gears = expresso_local_messages.search( local_folders, expresso_local_messages.getFilter() );

		_data['data'] 			= data['data'];
		_data['num_msgs']		= data['num_msgs'];
		_data['gears_num_msgs']	= _gears.length;

		delete_border( data['currentTab'], false);
		
		EsearchE.mount_result(_data);
	}

	searchE.prototype.show_paging = function(size)
	{
		var span_pg = Element("span_paging"+currentTab);
		
		if( span_pg == null )
		{
			span_pg 	= document.createElement('span');
			span_pg.id	= "span_paging"+currentTab;
		}
		else
			span_pg.innerHTML = "";
 
		if(size > parseInt(preferences.max_email_per_page)) { 
			this.totalPages = Math.ceil(size/preferences.max_email_per_page); 
			if((size/preferences.max_email_per_page) > this.totalPages) 
			this.totalPages++; 
		} 
 
		if(this.page != 0 && this.page != null)
		{ 
			_link = document.createElement("A"); 
			if( this.value )
			  _link.href  = 'javascript:EsearchE.quickSearchMail( false, '+0+', false, "'+currentTab+'" )'; 
			else
			{
	            _link.href  = 'javascript:EsearchE.page=0;'; 
	            _link.href += 'cExecute("$this.imap_functions.search_msg",openpage,"condition='+this.condition+'&sort_type='+this.sort_type+'&page=0&current_tab='+currentTab+'");'; 
	        } 
        } 
        else
        { 
            _link = document.createElement("SPAN"); 
		} 
		
		span_pg.appendChild(_link); 
 
		_link.innerHTML	= "&lt;&lt;"; 
        _link.title		= get_lang("First"); 
        
        span_pg.innerHTML += "&nbsp;"; 
 
		if(this.page == this.lastPage + (this.numPages)) 
		{ 
			this.lastPage = this.page - 1; 
		} 
		else if((this.lastPage != 0 && this.lastPage == this.page) || this.page == (this.totalPages-1)) 
		{ 
			this.lastPage = this.page - (this.numPages - 1); 
		} 
		else if(this.page == 0) 
		{ 
			this.lastPage = 0; 
		} 
 
		if(this.lastPage < 0) 
			this.lastPage = 0; 
		else if(this.lastPage > 0 && (this.lastPage > (this.totalPages -(this.numPages - 1)))) 
			this.lastPage = this.totalPages -(this.numPages); 
 
		var hasMarked = false; 
		if(this.page == null){
			this.page = 0;
		}
		for(i = this.lastPage; i <= this.totalPages; i++) 
		{ 
			if( ( i * preferences.max_email_per_page ) > size) 
			{ 
				break; 
			} 
		 
			if( this.page == i || (i == this.totalPages && !hasMarked) ) 
			{ 
				var _link = document.createElement('span'); 
				_link.setAttribute("style", "font-weight:bold; color:red") 
				_link.innerHTML = ( this.page + 1 ) + "&nbsp;&nbsp;"; 
			} 
			else 
			{ 
				var _page = i; 
				var _link = document.createElement('A'); 
				_link.innerHTML = ( _page + 1 ) + "&nbsp;&nbsp;"; 
				if( this.value )
				_link.href = 'javascript: EsearchE.quickSearchMail( false, '+i+', false, "'+currentTab+'" )';
				else{
				_link.href  = 'javascript:EsearchE.page='+i+';'; 
				_link.href += 'cExecute("$this.imap_functions.search_msg",openpage,"condition='+this.condition+'&sort_type='+this.sort_type+'&page='+_page+'&current_tab='+currentTab+'");'; 
				} 
			} 
			_link.innerHTML = "&nbsp;...&nbsp;"; 
			if(i == (this.lastPage + this.numPages)) 
			{ 
				span_pg.appendChild( _link ); 
				break; 
			} 
			else if(this.lastPage == 0 || i != this.lastPage) 
			{ 
				_link.innerHTML = "&nbsp;"+( i + 1 )+"&nbsp;"; 
			} 
			
			span_pg.appendChild( _link ); 
		} 
 
			if(this.page != (this.totalPages - 1)) { 
				_link = document.createElement("A"); 
				if( this.value )
				_link.href = 'javascript: EsearchE.quickSearchMail( false, '+(this.totalPages-1)+', false, "'+currentTab+'" )';
				else{
				_link.href  = 'javascript:EsearchE.page='+(this.totalPages-1)+';'; 
				_link.href += 'cExecute("$this.imap_functions.search_msg",openpage,"condition='+this.condition+'&sort_type='+this.sort_type+'&page='+(this.totalPages-1)+'&current_tab='+currentTab+'");'; 
				} 
			} 
        	else { 
            	_link = document.createElement("SPAN"); 
			} 
 
			span_pg.innerHTML += "&nbsp;"; 
			span_pg.appendChild(_link); 
			 
			_link.title = get_lang("Last"); 
			_link.innerHTML += "&gt;&gt;"; 
 
			Element("div_menu_c3").appendChild(span_pg); 
 	}

	searchE.prototype.searchFor = function( borderID, sortType )
	{
		var border_id 	= borderID;
		var sort_type	= sortType;
		var is_local = border_id.match('.*_local_.*');
		
		if(!is_local)
		{
			if( this.value )
				return this.quickSearchMail( document.getElementsByName(currentTab)[0].value, false, sortType, border_id );
		}
 
		var args   = "$this.imap_functions.search_msg";
		var params = "condition="+EsearchE.condition+"&page="+EsearchE.page+"&sort_type="+sort_type;

		var handler = function( data )
		{
        	var allMsg			= [3];
    		var gears			= [];
    		var local_folders	= [];

    		if ( preferences.use_local_messages == 1 && is_local)
    		{
    			temp = expresso_local_messages.list_local_folders();
    			
    			for (var x in temp)
    			{
    				local_folders.push( temp[x][0] );
    			}

    		
    			if ( local_folders.length > 0 )
    			{
					var currentSearch = document.getElementsByName(currentTab);
                    expresso_local_messages.setSortType(sortType);
                    gears = expresso_local_messages.search( local_folders, "##ALL <=>"+currentSearch[0].value +"##" );
                }
    		}
        	if (!is_local)
            {
				if( data['num_msgs'])
				{
					allMsg['data'] 				= data['data'];
					allMsg['num_msgs']			= data['num_msgs'];
					
				}
			}

            if (gears.length > 0)
            {
                allMsg['data_gears']                    = gears;
            }
        	
			var currentSearch = document.getElementsByName(currentTab)[0].value;
			
        	delete_border( border_id, false );
			
			EsearchE.mount_result( allMsg , sort_type, null, null, null, currentSearch ); 
		};

        if (is_local)
        {
            eval("handler('none')");
        }
        else
        {
            cExecute(args,handler,params);
        }
	}
	
	searchE.prototype.viewLocalMessage = function()
	{
		/*
		var data		  	= [2];
		var gears			= [];
		var local_folders	= [];
		
    	// Gears - local
		if ( preferences.use_local_messages == 1 )
		{
			temp = expresso_local_messages.list_local_folders();
			
			for (var x in temp)
			{
				local_folders.push( temp[x][0] );
			}

			if ( local_folders.length > 0 ){
				var currentSearch = document.getElementsByName(currentTab);
				if(currentSearch[0].value != ''){
					gears = expresso_local_messages.search( local_folders, "##ALL <=>"+currentSearch[0].value +"##");
				}else{
					if(openTab.condition[currentTab]){
						var condit = openTab.condition[currentTab][0].split(',');
						var filter = condit[0].split('##');
						gears = expresso_local_messages.search( local_folders, '##'+filter[1]+'##');
						
					}
				
				}
			}

			data['data_gears']	= gears;
			data['num_msgs']	= gears.length;
	
			if(data['num_msgs'] != undefined)
				write_msg( data['num_msgs'] + " " + get_lang("results found") );
						
			EsearchE.mount_result( data, 'SORTDATE' );
		}
		*/

		var data		  	= [2];
		var gears			= [];
		var local_folders	= [];
         
    	//MailArchiver - local data
        if ( preferences.use_local_messages == 1 )
		{
        	data['data_gears']	= expresso_mail_archive.search_queryresult;
            data['num_msgs']	= expresso_mail_archive.search_queryresult.length;
	        write_msg( data['num_msgs'] + " " + get_lang("results found"));
			EsearchE.mount_result(data , 'SORTDATE');
		}
	}
	
	searchE.prototype.make_tr_message = function(aux,border_id,i) {
	/**
	 * Preenche a estrutura de cache de mensagens para posterior consulta de 
	 * informações sobre as mensagens no escopo global.
	 */
		if (!onceOpenedHeadersMessages[aux.boxname])
			onceOpenedHeadersMessages[aux.boxname] = {};
		onceOpenedHeadersMessages[aux.boxname][aux.uid] = aux;	
	
		var tr = document.createElement("TR");
		if(typeof(preferences.line_height) != 'undefined')
			tr.style.height = preferences.line_height;
			
		var msg_folder = get_current_folder(); 
				
		var mailbox = aux.boxname;
		var uid_msg = aux.uid;
		var subject = aux.subject;
		var labels = aux.labels;
		var followupflagged = aux.followupflagged;

		tr.id = uid_msg+"_s"+numBox;

		// Keep the two lines together please
		tr.setAttribute('name',mailbox);
		tr.name = mailbox;
		
		// set attribute role id_folder
		tr.setAttribute('role', uid_msg+'_'+mailbox); 
		tr.role = uid_msg+'_'+mailbox;
		
		if ( aux.flag.match("U") )
			add_className(tr,'tr_msg_unread');

		add_className(tr, i%2 != 0 ? 'tr_msg_read2' : 'tr_msg_read');

		var _onclick = function()
		{
			proxy_mensagens.get_msg(this.parentNode.id,url_encode(this.parentNode.getAttribute('name')),false,show_msg);
		};

		for(var j=0 ; j <= 11 ; j++)
		{
			var td = document.createElement("TD");
                        add_className(td, 'td_msg');
			if (j == 0)
			{
				td.setAttribute("width", colSizes[1][0]);
				var chk_onclick;
				if (is_ie)
					chk_onclick = "changeBgColor(window.event,"+uid_msg+");";
				else
					chk_onclick = "changeBgColor(event,'"+uid_msg+"');";				//'search_' + numBox
				var td1 = '<input type="checkbox" onclick="' + chk_onclick + '" id="' + border_id + '_check_box_message_'+uid_msg+'"></input>';

			}
			if (j == 1)
			{
				td.setAttribute("width", colSizes[1][1]);
				if (aux.flag.match('T'))
				{
					attachNum = parseInt(aux.flag.substr(aux.flag.indexOf('T')+1));
					td1 = '';
					$(td).addClass("expressomail-sprites-clip");
				}
				else
					td1 = '';
			}
			if (j == 2)
			{
				td.setAttribute("width", colSizes[1][2]);
				td.id = "td_message_answered_"+uid_msg;
				if (aux.flag.match('X'))
					td1 = '<img src=templates/'+template+'/images/forwarded.png title="'+get_lang('Forwarded')+'">';
				else
					if (aux.flag.match('A'))
						td1 = '<img src=templates/'+template+'/images/answered.png title="'+get_lang('Answered')+'">';
					else
						td1 = '';
			}
			if (j == 3)
			{
				td.setAttribute("width", colSizes[1][3]);
				td.id = "td_message_important_"+uid_msg;
				if (aux.flag.match("F"))
				{
					add_className(tr, 'flagged_msg');
					td1 = "<img src='templates/"+template+"/images/important.png' title='"+get_lang('Flagged')+"'>";
				}
				else
					td1 = '';
			}
			if (j == 4)
			{
				if (preferences['use_followupflags_and_labels'] == '1'){
					if(border_id.split("local").length == 1){
						td.setAttribute("width", colSizes[1][4]);
						td.id = "td_message_followup_search_"+uid_msg;
						td.setAttribute("class","search-result-item");
						td1 = '<div class="flag-edited" style="width:8px;height:6px;"><img src="../prototype/modules/mail/img/flagEditor.png"></div>';
								
						$(td).click(function(event, ui){	
							var messageClickedId = $(this).attr('id').match(/td_message_followup_search_([\d]+)/)[1];

							var loading = $('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited")
	                    		.find('img[alt=Carregando]');

	                    	//Verificar se está carregando a bandeira.
	                    	//Caso esteja ele sai da função até que seja carregado. 
	            			if( loading.length ) {
	                			return false;
	            			}

	 						var followupColor = $('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited").css('backgroundColor');

							
							var followupColor = $('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited").css('backgroundColor');
							
							$('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited")
							.html('<img alt="Carregando" title="Carregando" style="margin-left:-3px; margin-top:-4px; width:13px; height:13px;" src="../prototype/modules/mail/img/ajax-loader.gif" />');	
								
							$('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited").css("background", "transparent");
				
							/**TODO Alterar após melhorias no filtro da camada javascript*/
							DataLayer.remove('followupflagged', false);
							var flagged = DataLayer.get('followupflagged', {filter: [
								'AND', 
								['=', 'messageNumber', messageClickedId], 
								['=', 'folderName', mailbox]
							]});
							if(flagged == '' || flagged == [] || flagged == 'undefined'){
								/**
								* Aplica followupflag de Acompanhamento
								*/
								aux.followupflagged = {
									uid : User.me.id,
									folderName : mailbox, 
									messageNumber : messageClickedId, 
									alarmTime : false, 
									backgroundColor : '#FF2016',
									followupflagId: '1'
								};
						
								aux.followupflagged.id = DataLayer.put('followupflagged', aux.followupflagged);
								DataLayer.commit(false, false, function(data){
									var fail = false;
									$.each(data, function(index, value) {
										fail = false;
										if(typeof value === 'string'){
											fail = value;
										}
									});
									
									$('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited")
									.css({"background-image":"url(../prototype/modules/mail/img/flagEditor.png)"})
									.find('img').remove();;
									
									if (fail) {
									    
									    var isCurrentFolder = current_folder == mailbox ? '#td_message_followup_' + messageClickedId + ', ' : ''; 	
									    $(isCurrentFolder + 'tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited").css("background", "#CCCCCC");
									    
									    $('#td_message_followup_search_' + messageClickedId).find(".flag-edited")
									    .append("<img src='../prototype/modules/mail/img/flagEditor.png'/>");

									    MsgsCallbackFollowupflag[fail]();
									    return false;
									}
									
									if(current_folder == mailbox){
										$('#td_message_followup_' + messageClickedId + ', ' + 
										'tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).attr('title', get_lang('Follow up')).find(".flag-edited").css("background", aux.followupflagged.backgroundColor);

										$('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).attr('title', get_lang('Follow up')).find(".flag-edited").css("background", aux.followupflagged.backgroundColor)
										.append("<img src='../prototype/modules/mail/img/flagEditor.png'/>");	
									}else{								
										$('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).attr('title', get_lang('Follow up')).find(".flag-edited").css("background", aux.followupflagged.backgroundColor)
										.append("<img src='../prototype/modules/mail/img/flagEditor.png'/>");			
									}								
									updateCacheFollowupflag(messageClickedId, mailbox, true);
								});

								
							}else if(onceOpenedHeadersMessages[mailbox][messageClickedId]['followupflagged'].followupflag.name == 'Follow up'){
								/**
								* Remover followupflag de Acompanhamento (DFD0078:RI25)
								*/
								$(this).find(".flag-edited").css("background", "#cccccc");
								DataLayer.remove('followupflagged', flagged[0].id );
								DataLayer.commit(false, false, function(){
									$('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited").html('<img src="../prototype/modules/mail/img/flagEditor.png">')
									.css({"width":"8px","height":"6px"/*,"background-image":"url(../prototype/modules/mail/img/flagEditor.png)"*/});
									if(current_folder == mailbox){
										updateCacheFollowupflag(messageClickedId, mailbox, false);
									
										$('#td_message_followup_' + messageClickedId + ', ' + 
										  'tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).attr('title', '').find(".flag-edited").css("background", '#CCC');
										
										$('#td_message_followup_' + messageClickedId + ', ' + 
											'tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited")
											.css({"background-image":"url(../prototype/modules/mail/img/flagEditor.png)"});
									}else{
										updateCacheFollowupflag(messageClickedId, mailbox, false);
										
										$('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).attr('title', '').find(".flag-edited").css("background", '#CCC');
										
										$('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited")
											.css({"background-image":"url(../prototype/modules/mail/img/flagEditor.png)"})
											.append("<img src='../prototype/modules/mail/img/flagEditor.png'/>");
									}
								});

							} else {
								$('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited")
								.css({"background-image":"url(../prototype/modules/mail/img/flagEditor.png)"}).find('img').remove();
								
								$('tr[role="'+messageClickedId+'_'+mailbox+'"] #td_message_followup_search_' + messageClickedId).find(".flag-edited").css("background", followupColor)
								.append("<img src='../prototype/modules/mail/img/flagEditor.png'/>");

								//Pega id do checkbox
								var id = $(this).parents('[role="'+messageClickedId+'_'+mailbox+'"]').attr('class', 'selected_msg').find(':checkbox').attr('id');
								
								//verifica se o checkbox já está selecionada
								if($('#' + id).attr('checked') != 'checked')
									$(this).parents('[role="'+messageClickedId+'_'+mailbox+'"]').attr('class', 'selected_msg').find(':checkbox').trigger('click');
								
								updateSelectedMsgs(true,messageClickedId);
								configureFollowupflag();
							}
							//if(!){}

						});		
					}
				}else{
					td.setAttribute("width", colSizes[1][4]);
					td.innerHTML = '<div></div>';
				}			
			}
			if (j == 5)
			{
				if(border_id.split("local").length == 1){
					td.setAttribute("width", colSizes[1][5]);
					td.id = "td_message_labels_search_"+uid_msg;
					td.setAttribute("class","td-label-search");				
					
					if (aux.labels) {
						//td1 = '<img src="../prototype/modules/mail/img/tag.png">';
						$(td).css({'background-image':'url(../prototype/modules/mail/img/mail-sprites.png)','margin-left': '0px', 'margin-top':'3px', 'background-position': '0px -1706px', 'background-repeat':'no-repeat no-repeat'});
						updateLabelsColumn(aux)		
					} else {
						td1 = '';
					}
				}
			}
			if (j == 6)
			{
				td.setAttribute("width", colSizes[1][6]);
				td.id = "td_message_sent_"+uid_msg;
				td1 = '';
			}

			if ( j == 7 )
			{
				td.setAttribute("width", colSizes[1][7]);
				td.className = "td_resizable";
				td.onclick = _onclick;
				var nm_box = aux.boxname.split(cyrus_delimiter);
				var td1 = nm_box.pop();
				td.setAttribute("NoWrap","true");
				td.style.overflow = "hidden";
				td.style.color = "#42795b";
				td.style.fontWeight = "bold";

				var td1  = get_lang(td1).substr(get_lang(td1).length-1) == "*"?td1:get_lang(td1);
				td1 = translateFolder(td1);

				if (proxy_mensagens.is_local_folder(td1)) {
					var td1 = this.aux_local_folder_display(td1);
				}
			}

			if( j == 8 )
			{
				var name;
				if( aux.from.name != undefined){
					name = aux.from.name;
				}else{
					name = aux.from;
				}
				if ( name !== null && name.length > 29)
					name = name.substr(0,29) + "...";

				td.setAttribute("width", colSizes[1][8]);
				td.className = "td_resizable";
				td.onclick = _onclick;
				td.setAttribute("NoWrap","true");
									td.style.overflow = "hidden";
									
				var td1  =  '<div style="width:100%;overflow:hidden">'+name+"</div>";
			}

			if( j == 9 )
			{
				//var subject_encode = url_encode(subject);
				aux.subject = html_entities(aux.subject);
				if (aux.subject.length <= 1)
					aux.subject = "(" + get_lang("no subject") + ")";
				if (aux.subject.length > 70)
					aux.subject = aux.subject.substr(0,70) + "...";

				td.setAttribute("width", colSizes[1][9]);
				td.className = "td_resizable td_msg_search_subject";
				td.onclick = _onclick;
				td.setAttribute("NoWrap","true");
				td.style.overflow = "hidden";

				var td1  = aux.subject;
			}

			if( j == 10 )
			{
				td.setAttribute("width", colSizes[1][10]);
				td.className = "td_resizable";
				td.align		= "center";
				td.onclick		= _onclick;

				if(validate_date(new String(aux.udate))){
					var td1 = aux.udate;
				}
				else
				{
					var dt	= new Date( aux.udate * 1000 );
					var td1	 = dt.getDate() + "/";

					if( !( dt.getMonth() + 1 ).toString().match(/\d{2}/) )
						td1 += "0"+( dt.getMonth() + 1 ) + "/";
					else
						td1 += ( dt.getMonth() + 1 ) + "/";

					td1 += dt.getFullYear();
				}
			}

			if( j == 11 )
			{
				td.setAttribute("width", colSizes[1][11]);
				td.className = "td_resizable";
				td.align = "center";
				td.onclick = _onclick;
				if(aux.Size != undefined){
					var td1  = borkb(aux.Size);
				}
				else{
					var td1  = borkb(aux.size);
				}
			}

			if( j == 12 )
			{
				if (aux.flag.match("U"))
					add_className(tr, 'tr_msg_unread');
				if (aux.flag.match("F"))
					add_className(tr, 'flagged_msg');
				var td1 = '';
			}
			if (j<12) {
				td.innerHTML = td1;
				td1 = '';
				//Carregar os followupflag nos resultados.
				if (aux.followupflagged) {
					if(aux.followupflagged.followupflag.id < 7){
						var nameFollowupflag = get_lang(aux.followupflagged.followupflag.name);
					}else{
						var nameFollowupflag = aux.followupflagged.followupflag.name;
					}
					$(td).attr('title', nameFollowupflag)
					.find(".flag-edited").css("background",aux.followupflagged.backgroundColor);
					if(aux.followupflagged.isDone == "1"){
						$(td).find(".flag-edited").find("img")
						.attr("src", "../prototype/modules/mail/img/flagChecked.png")
						.css("margin-left","-3px");
					}
				} else {
					$(td).find(".flag-edited").css("background","#cccccc");
				}
				

				tr.appendChild(td);
			}
		}

		//_dragArea.makeDragged(tr, uid_msg, subject, true, mailbox);
		$(tr).draggable({
			start : function(){
				$('.upper, .lower').show();
        		$(".lower").css("top", ($("#content_folders").height()-18) + $("#content_folders").offset().top);
				if($(".shared-folders").length){
					$(".shared-folders").parent().find('.folder:not(".shared-folders")').droppable({
						over : function(a, b){						
							//SETA BORDA EM VOLTA DA PASTA
							$(b.helper).find(".draggin-folder,.draggin-mail").css("color", "green");
							over = $(this);
							$(this).addClass("folder-over");
							if(($(this)[0] != $(this).parent().find(".head_folder")[0]))
								if($(this).prev()[0])
									if($(this).parent().find(".expandable-hitarea")[0] == $(this).prev()[0]){
										setTimeout(function(){
											if(over.hasClass("folder-over"))
												over.prev().trigger("click");
										}, 500);
										
									}
						},
						out : function(a,b){
							//RETIRA BORDA EM VOLTA DA PASTA
							$(b.helper).find(".draggin-folder,.draggin-mail").css("color", "");
							$(this).removeClass("folder-over");
						},
						//accept: ".draggin_mail",
						drop : function(event, ui){
							$(this).css("border", "");
							if($(this).parent().attr('id') == undefined){
								var folder_to = 'INBOX';
								var to_folder_title = get_lang("Inbox");
							}else{
								var folder_to = $(this).parent().attr('id');
								var to_folder_title = $(this).attr('title');
							}		
							var folder_to_move = ui.draggable.parent().attr('id');
							var border_id = ui.draggable.find("input[type=hidden]").attr("name");
							// Mensagens : SE O DROP VIER DA LISTA DE MENSAGENS :
							if(ui.draggable.parents('[id^="content_id_"]')[0]){
								move_search_msgs("content_id_"+border_id, folder_to, to_folder_title);
								return refresh();
							}
						}
					});
				}
			},
			stop :function(){
				$('.upper, .lower').hide();
				$(".shared-folders").parent().find(".folder").droppable("destroy");
			},
			helper: function(event){
				if($(this).find("input:checkbox").attr("checked") != "checked"){
					$(this).find("input:checkbox").trigger('click');
					$(this).addClass("selected_msg");
				}
				if($("#content_id_"+border_id).find("tr input:checked").length > 1)
					return $(DataLayer.render('../prototype/modules/mail/templates/draggin_box.ejs', {texto : (($("#content_id_"+border_id).find("tr input:checked")).length+ " " + get_lang("featured messages")), type: "messages"}));
				if(	$(this).find(".td_msg_search_subject").text().length > 18 )
					return $(DataLayer.render('../prototype/modules/mail/templates/draggin_box.ejs', {texto : $(this).find(".td_msg_search_subject").text().substring(0,18) + "...", type: "messages"}));
				else
					return $(DataLayer.render('../prototype/modules/mail/templates/draggin_box.ejs', {texto : $(this).find(".td_msg_search_subject").text(), type: "messages"}));
			},
			cursorAt: {top: 5, left: 56},
			refreshPositions: true ,
			scroll: true, 
			scrollSensitivity: 100,
			scrollSpeed: 100,
			containment: "#divAppbox"
		}).bind("contextmenu", function(event){
			if(event.button == 2)
				if($(this).find("input:checkbox").attr("checked") != "checked"){
					$(this).find("input:checkbox").trigger('click');
					$(this).addClass("selected_msg");
			}
		});
		return tr;
	}

	// Form resultado
	searchE.prototype.mount_result = function( Data, sort_type, keep_border, keep_filled, division, actualSearch )
	{
		var data = ( Data['data'] ) ? Data['data'] : Data['data_gears'];
		if ( data == undefined )
			return;

		var msg_folder = get_current_folder(); 
		var messageNumbers = new Array();
		var messageFolders = new Array();
		for (var i=0; i<data.length; i++) {
			messageNumbers.push(data[i].uid);
			messageFolders.push(data[i].boxname);
		}
		


	
		var cont = parseInt(0);

		if ( typeof(sort_type) != 'undefined')
			this.sort_type = sort_type;
		else
			sort_type = this.sort_type;

		if ( keep_border ) {
			/*Recupera o id da ultima aba de pesquisa rápida aberta que não seja de pesquisa local
			para que continue o processamento na mesma aba caso a aba seja alternada durante o processamento da pesquisa.*/
			var border_id = $('#border_tr > [id*="_search"]:not([id*="_search_local"]):last').attr("id");
			border_id = border_id.split("border_id_").reverse()[0];
		}
		else {
			if(isNaN(numBox)){
				var aux = numBox.split("_");
				numBox = parseInt(aux[0]) + 1;
			}else{
				inc_abas_search++;
				numBox = inc_abas_search;
			}
			if( Data['data'] )
				if(!actualSearch)
					var border_id = create_border(get_lang("Server Results"), "search_" + numBox);
				else
					var border_id = create_border(get_lang("Server Results"), "search_" + numBox, actualSearch);
			if( Data['data_gears'])
				if(!actualSearch)
					var border_id = create_border(get_lang("Local Results"), "search_local_msg" + numBox);
				else
					var border_id = create_border(get_lang("Local Results"), "search_local_msg" + numBox, actualSearch);
		}

		if (!border_id)
            return;

        currentTab = border_id;
        openTab.content_id[currentTab] = Element('content_id_search_' + numBox);
        openTab.type[currentTab] = 1;
		openTab.condition[currentTab] = this.condition;

		if ( keep_border ) {
			var content_search =  Element('content_id_' + border_id);
			numBox = border_id.split("_")[1];
			var div_scroll_result = Element("divScrollMain_"+numBox);

			content_search.removeChild(div_scroll_result);

			if( !keep_filled )
			    div_scroll_result = false;
		}

		var table = document.createElement("TABLE");
			table.id    = "table_resultsearch_" + numBox;
			table.frame = "void";
			table.rules = "rows";
			table.cellPadding	= "0";
			table.cellSpacing	= "0";
			table.className		= "table_box";

		var tbody		= document.createElement("TBODY");
			tbody.id	= "tbody_box_" + numBox;

		for( var i=0; i < data.length; i++)
		{
			if(data[i] !== null){
			var tr = EsearchE.make_tr_message(data[i],border_id,i);
            tbody.appendChild(tr);
		}
		}
		
		//global_search++; //Tabs from search must not have the same id on its tr's // use numBox instead of this!
		
		table.appendChild(tbody);

		var colgr_element = buildColGroup(1);
		colgr_element.setAttribute("id","colgroup_main_"+numBox);
		table.appendChild(colgr_element);

		var content_search =  Element('content_id_' + border_id);
		
		if( !div_scroll_result )
		{
		var div_scroll_result = document.createElement("DIV");
		    div_scroll_result.id = "divScrollMain_"+numBox;
		div_scroll_result.style.overflowY = "scroll";
		div_scroll_result.style.overflowX = "hidden";
		div_scroll_result.style.width	="100%";
	
		if (is_mozilla){
			div_scroll_result.style.overflow = "-moz-scrollbars-vertical";
			div_scroll_result.style.width	="100%";
		}
		}
		if( division )
		{
		    var _div = document.createElement("div");
		    _div.className = 'local-messages-search-warning';
		    _div.innerHTML = division;
		    div_scroll_result.appendChild(_div);
		}

		if(is_ie)
			Element("border_table").width = "99.5%";

		// Put header
		var table_element = document.createElement("TABLE");
		var tbody_element = document.createElement("TBODY");
		if (is_ie)
		{
			table_element.attachEvent("onmousemove",changeCursorState);
			table_element.attachEvent("onmousedown",startColResize);
		}
		else {
			table_element.addEventListener("mousemove",changeCursorState,false);
			table_element.addEventListener("mousedown",startColResize,false);
		}
		table_element.setAttribute("id", "table_message_header_box_"+numBox);
		table_element.className = "table_message_header_box";
		if (!is_ie)
			table_element.style.width = "98.8%";
		table_element.emptyBody = false;

		tr_element = document.createElement("TR");
		tr_element.className = "message_header";
		td_element0 = createTDElement(1,0);
		chk_box_element = document.createElement("INPUT");
		if(border_id.indexOf('local') > 0)
			chk_box_element.id  = "chk_box_select_all_messages_search_local";
		else
			chk_box_element.id  = "chk_box_select_all_messages_search";
		chk_box_element.setAttribute("type", "checkbox");
		chk_box_element.className = "checkbox";
		chk_box_element.onclick = function(){select_all_search_messages(this.checked,content_search.id);};
		chk_box_element.onmouseover = function () {this.title=get_lang('Select all messages from this page.')};
		chk_box_element.onkeydown = function (e)
		{
			if (is_ie)
			{
				if ((window.event.keyCode) == 46)
					delete_msgs(current_folder,'selected','null');
			}
			else
			{
				if ((e.keyCode) == 46)
					delete_msgs(current_folder,'selected','null');
			}
		};

		td_element0.appendChild(chk_box_element);
		td_element01 = createTDElement(1,1);
		td_element02 = createTDElement(1,2);
		td_element03 = createTDElement(1,3);
		td_element04 = createTDElement(1,4);
		td_element05 = createTDElement(1,5);
		td_element06 = createTDElement(1,6);
		td_element1 = createTDElement(1,7,"th_resizable","left");
		
		var arrow_ascendant = function(Text)
		{
			return "<b>" + Text + "</b><img src='templates/"+template+"/images/arrow_ascendant.gif'>";
		}

		// Ordernar Pasta
		if ( sort_type == 'SORTBOX' /*|| sort_type == 'SORTBOX_REVERSE'*/ )
		{
			if( Data['data'] )
			{
				td_element1.onclick		= function(){EsearchE.searchFor(border_id, 'SORTBOX_REVERSE');};
				td_element1.innerHTML	= "<b>"+get_lang("Folder")+"</b><img src='templates/"+template+"/images/arrow_descendant.gif'>";
			}
			else
			{
                                td_element1.onclick		= function(){EsearchE.searchFor(border_id, 'SORTBOX_REVERSE');};
				td_element1.innerHTML	= "<b>"+get_lang("Folder")+"</b><img src='templates/"+template+"/images/arrow_descendant.gif'>";
			}
		}
		else
		{
			if( Data['data'] )
			{
				td_element1.onclick		= function(){EsearchE.searchFor(border_id, 'SORTBOX');};
			}
			else
			{
				td_element1.onclick		= function(){EsearchE.searchFor(border_id, 'SORTBOX');};
			}
			td_element1.innerHTML	= ( sort_type == 'SORTBOX_REVERSE' ) ? arrow_ascendant(get_lang("Folder")) : get_lang("Folder");
		}
		
		// Ordernar Quem
		td_element2 = createTDElement(1,8,"th_resizable","left");

		if (sort_type == 'SORTFROM' || sort_type == 'SORTWHO' /*|| sort_type == 'SORTWHO_REVERSE' || sort_type == 'SORTFROM_REVERSE'*/ )
		{
			if(Data['data'])
			{
				td_element2.onclick		= function(){EsearchE.searchFor(border_id, 'SORTFROM_REVERSE');};
				td_element2.innerHTML	= "<b>"+get_lang("From")+"</b><img src='templates/"+template+"/images/arrow_descendant.gif'>";
			}
			else
			{
                                td_element2.onclick		= function(){EsearchE.searchFor(border_id, 'SORTWHO_REVERSE');};
				td_element2.innerHTML	= "<b>"+get_lang("From")+"</b><img src='templates/"+template+"/images/arrow_descendant.gif'>";
			}
		}
		else
		{
			if( Data['data'] )
			{
				td_element2.onclick		= function(){EsearchE.searchFor(border_id, 'SORTWHO');};
			}
			else
			{
				td_element2.onclick		= function(){EsearchE.searchFor(border_id, 'SORTWHO');};
			}
			td_element2.innerHTML	= ( sort_type == 'SORTWHO_REVERSE' ) ? arrow_ascendant(get_lang("From")) : get_lang("From");
		}
		
		// Ordernar Subject
		td_element3 = createTDElement(1,9,"th_resizable","left");
		
		if (sort_type == 'SORTSUBJECT' /*|| sort_type == 'SORTSUBJECT_REVERSE'*/)
		{
			if( Data['data'])
			{
				td_element3.onclick		= function(){EsearchE.searchFor(border_id, 'SORTSUBJECT_REVERSE');};
				td_element3.innerHTML	= "<b>"+get_lang("subject")+"</b><img src='templates/"+template+"/images/arrow_descendant.gif'>";				
			}
			else
			{
				td_element3.onclick		= function(){EsearchE.searchFor(border_id, 'SORTSUBJECT_REVERSE');};
				td_element3.innerHTML	= "<b>"+get_lang("subject")+"</b><img src='templates/"+template+"/images/arrow_descendant.gif'>";
			}
		}
		else
		{
			if( Data['data'] )
			{
				td_element3.onclick		= function(){EsearchE.searchFor( border_id, 'SORTSUBJECT');};
			}
			else
			{
				td_element3.onclick		= function(){EsearchE.searchFor(border_id, 'SORTSUBJECT');};
			}
			td_element3.innerHTML	= ( sort_type == 'SORTSUBJECT_REVERSE' ) ? arrow_ascendant(get_lang("subject")) : get_lang("subject");
		}
		
		// Ordernar Data
		td_element4 = createTDElement(1,10,"th_resizable","center");
		
		if ( sort_type == 'SORTDATE' /*|| sort_type == 'SORTDATE_REVERSE'*/ )
		{
			if( Data['data'] )
			{
				td_element4.onclick		= function(){EsearchE.searchFor(border_id, 'SORTDATE_REVERSE');};
				td_element4.innerHTML	= "<b>"+get_lang("Date")+"</b><img src='templates/"+template+"/images/arrow_descendant.gif'>";
			}
			else
			{
                                td_element4.onclick		= function(){EsearchE.searchFor(border_id, 'SORTDATE_REVERSE');};
				td_element4.innerHTML	= "<b>"+get_lang("Date")+"</b><img src='templates/"+template+"/images/arrow_descendant.gif'>";
			}
		}
		else
		{
			if( Data['data'] )
			{
				td_element4.onclick		= function(){EsearchE.searchFor(border_id, 'SORTDATE');};
			}
			else
			{
				td_element4.onclick		= function(){EsearchE.searchFor(border_id, 'SORTDATE');};
			}
			td_element4.innerHTML	= ( sort_type == 'SORTDATE_REVERSE' ) ? arrow_ascendant(get_lang("Date")) : get_lang("Date");
		}			

		// Ordernar Tamanho
		td_element5 = createTDElement(1,11,"th_resizable","center");
		
		if ( sort_type == 'SORTSIZE' /*|| sort_type == 'SORTSIZE_REVERSE'*/ )
		{
			if( Data['data'] )
			{
				td_element5.onclick		= function(){EsearchE.searchFor(border_id, 'SORTSIZE_REVERSE');};
				td_element5.innerHTML	= "<b>"+get_lang("size")+"</b><img src='templates/"+template+"/images/arrow_descendant.gif'>";				
			}
			else
			{
                                td_element5.onclick		= function(){EsearchE.searchFor(border_id, 'SORTSIZE_REVERSE');};
				td_element5.innerHTML	= "<b>"+get_lang("size")+"</b><img src='templates/"+template+"/images/arrow_descendant.gif'>";
			}
		}
		else
		{
			if( Data['data'] )
			{	
				td_element5.onclick		= function(){EsearchE.searchFor(border_id, 'SORTSIZE');};
			}
			else
			{
				td_element5.onclick		= function(){EsearchE.searchFor(border_id, 'SORTSIZE');};
			}
			td_element5.innerHTML	= ( sort_type == 'SORTSIZE_REVERSE' ) ? arrow_ascendant(get_lang("size")) : get_lang("size");
		}
		
		//Abrir a Tela de de Configuracao de Acompanhamento
		
		
		tr_element.appendChild(td_element0);
		tr_element.appendChild(td_element01);
		tr_element.appendChild(td_element02);
		tr_element.appendChild(td_element03);
		tr_element.appendChild(td_element04);
		tr_element.appendChild(td_element05);
		tr_element.appendChild(td_element06);
		tr_element.appendChild(td_element1);
		tr_element.appendChild(td_element2);
		tr_element.appendChild(td_element3);
		tr_element.appendChild(td_element4);
		tr_element.appendChild(td_element5);
		tbody_element.appendChild(tr_element);
		table_element.appendChild(tbody_element);

		
		var colgr_element = buildColGroup(1);
		colgr_element.setAttribute("id","colgroup_head_"+numBox);
		table_element.appendChild(colgr_element);

		if( parseInt( Data['gears_num_msgs'] ) > 0 && !keep_filled)
		{
			var messagesWarning = document.getElementById("local-messages-search-warning_"+border_id);
				if(!messagesWarning){
					var _div_gears = document.createElement("div");
					_div_gears.id = "local-messages-search-warning_"+border_id;
					_div_gears.onclick = function(){EsearchE.viewLocalMessage();};
					_div_gears.className = 'local-messages-search-warning';
					_div_gears.innerHTML = get_lang("The search has% 1 messages stored locally. Want to see them ? Click here.", Data['gears_num_msgs']);
					content_search.appendChild(_div_gears);		
				}
		}		

		var _divScroll = document.getElementById("divScrollHead_"+numBox);
		
		if( _divScroll ){
			content_search.removeChild(_divScroll);
			_divScroll = false;
		}

			_divScroll = document.createElement("DIV");
			_divScroll.id = "divScrollHead_"+numBox;
			_divScroll.style.overflowY = "hidden";
			_divScroll.style.overflowX = "hidden";
			_divScroll.style.width	="100%";

			if (is_mozilla){
				_divScroll.style.width	="99.3%";
			}
			_divScroll.appendChild(table_element);
			content_search.appendChild(_divScroll);

		/*end of "put header"*/
		if ( !expresso_offline )
		{
			div_scroll_result.appendChild(table);
			content_search.appendChild(div_scroll_result);
		}
		else
		{
			div_scroll_result.appendChild(table);
			content_search.appendChild(div_scroll_result);
		}
		
		resizeWindow();
		if(typeof(Data.data_gears)=="undefined")
			EsearchE.show_paging( Data['num_msgs'] );

		Data = null;
		data = null;
	}

	searchE.prototype.open_msg = function(mailbox, uid_msg, subject)
	{
		var handler_get_msg = function(data)
		{
			if( Element("border_id_" + uid_msg + "_r") )
				alert(get_lang("This message is already opened!"));
			else
				draw_message( data, create_border(url_decode(subject), uid_msg + "_r") );
		}
		
		proxy_mensagens.get_msg(uid_msg,mailbox,false,handler_get_msg);
	}

	// Adiciona caixas postais na busca;
	searchE.prototype.add_mailboxes = function()
	{
		var sel = Element("sel_search_nm_box1");
		this.name_box_search = folder.id;
		
		if (!proxy_mensagens.is_local_folder(this.name_box_search))
		{
			var name_box     = this.name_box_search.split(cyrus_delimiter);
			if(this.name_box_search == "")
				return false;
			var name_box_def = "";
			if(name_box.length != 1){
				name_box_def = name_box[(name_box.length-1)];
			}else{
				name_box_def = get_lang("Inbox");
			}
		}
		else
		{
			if(this.name_box_search=='local_root')
				return;
			if(this.name_box_search=='local_Inbox')
				name_box_def = get_lang("Inbox");
			else if(this.name_box_search.indexOf("/")!="-1") {
				final_pos = this.name_box_search.lastIndexOf("/");
				name_box_def = this.name_box_search.substr(final_pos+1);
			}
			else {
				name_box_def = folder.caption + " (local)"; //this.name_box_search.substr(6);//Retira o 'local_'
			}
		}
		
		if( sel.length > 0)
		{
			for(var i=0; i < sel.options.length; i++)
			{
				if(sel.options[i].value == this.name_box_search)
				{
					alert(get_lang('This message is already selected!'));
					return false;
				}
			}
		}
		
		name_box_def 	= translateFolder(name_box_def);
		sel[sel.length] = new Option(lang_folder(name_box_def),this.name_box_search,false,true);
	}

	//	Remove as caixas postais na busca;
	searchE.prototype.del_mailboxes = function()
	{
		var sel = Element("sel_search_nm_box1");
		
		if( sel && ( sel.length > 0 ) )
		{
			for(var i=0; i < sel.options.length; i++)
			{
				if(sel.options[i].selected == true)
				{
					sel.options[i] = null;
					i--;
				}
			}
		}
	}

	// todas as caixas
	searchE.prototype.all_mailboxes = function()
	{
		var value = Element("check_all_msg") ? Element("check_all_msg").checked : this.elementChecked ;
		var cont = parseInt(0);
		if(value)
		{
			if(EsearchE.all_boxes.length > 0)
			{
				EsearchE.all_boxes.splice(0,(EsearchE.all_boxes.length));
			}
			for(var i=0; i < folders.length; i++)
			{
				EsearchE.all_boxes[cont++] = folders[i].folder_id;
			}
		}
		else
		{
			EsearchE.all_boxes.splice(0,(EsearchE.all_boxes.length));
		}
	}

	// Search;
	searchE.prototype.func_search_complex = function()
	{
		var fields = "##";
		// Verifica se os campos estão preenchidos;
		if(trim(Element("txt_ass").value) != ""){
			fields += "SUBJECT " +  "<=>" +url_encode(Element("txt_ass").value) + "##";
		}
		if(trim(Element("txt_body").value) != ""){
			fields += "BODY " + "<=>" + url_encode(Element("txt_body").value) + "##";
		}
		if(trim(Element("txt_de").value) != ""){
			fields += "FROM " + "<=>" + url_encode(Element("txt_de").value) + "##";
		}
		if(trim(Element("txt_para").value) != ""){
			fields += "TO " + "<=>" + url_encode(Element("txt_para").value) + "##";
		}
		if(trim(Element("txt_cc").value) != ""){
			fields += "CC " + "<=>" + url_encode(Element("txt_cc").value) + "##";
		}
        if (trim(Element("since_date").value) != "")
        {
            if (validate_date(Element("since_date").value))
            {
                fields += "SINCE " + "<=>" + url_encode(Element("since_date").value) + "##";
            }
            else
            {
            	alert(get_lang('Invalid date on field %1', get_lang('Since Date')));
            	return false;
            }
        }

        if (trim(Element("before_date").value) != "")
        {
            if (validate_date(Element("before_date").value))
            {
                fields += "BEFORE " + "<=>" + url_encode(Element("before_date").value) + "##";
            }
            else
                {
                    alert(get_lang('Invalid date on field %1', get_lang('Before Date')));
                    return false;
                }
        }
		
        if ((trim(Element("since_date").value) != "") && (trim(Element("before_date").value) != "")){
			if(!(validate_date_order(trim(Element("since_date").value), trim(Element("before_date").value)))){
				alert(get_lang('Invalid date on field %1', get_lang('Before Date')));
                return false;
			}
		}
		
        if(trim(Element("on_date").value) != "")
        {
            if (validate_date(Element("on_date").value))
            {
                fields += "ON " + "<=>" + url_encode(Element("on_date").value) + "##";
            }
            else
            {
            	alert(get_lang('Invalid date on field %1', get_lang('On Date')));
                return false;
            }

        }

        if(trim(Element("flagged").options[Element("flagged").selectedIndex].value) != "")
        {
            if (Element("flagged").options[Element("flagged").selectedIndex].value == "FLAGGED")
            {
                fields += "FLAGGED##";
            }
            else
            {
                fields += "UNFLAGGED##";
            }
        }

        if(trim(Element("seen").options[Element("seen").selectedIndex].value) != "")
        {
            if (Element("seen").options[Element("seen").selectedIndex].value == "SEEN")
            {
                fields += "SEEN##";
            }
            else
            {
                fields += "UNSEEN##";
            }
        }
            
        if(trim(Element("answered").options[Element("answered").selectedIndex].value) != "")
        {
            if (Element("answered").options[Element("answered").selectedIndex].value == "ANSWERED"){
                fields += "ANSWERED##";
            }
            else {
                fields += "UNANSWERED##";
            }
        }
            
        if(trim(Element("recent").options[Element("recent").selectedIndex].value) != "")
        {
            if (Element("answered").options[Element("answered").selectedIndex].value == "RECENT")
            {
                fields += "RECENT##";
            }
            else
            {
                fields += "OLD##";
            }
        }

		if(fields == "##")
		{
			alert(get_lang("Define some search parameters!"));
			return false;
		}
		
		var local_folders = new Array();
		var temp;

		if( Element("check_all_msg") ? Element("check_all_msg").checked : this.elementChecked )
		{
			this.all_mailboxes();
			var nm_box = new Array;
			for(var i=0; i < EsearchE.all_boxes.length; i++)
			{
				nm_box[i] = EsearchE.all_boxes[i] + fields;
			}
			if (preferences.use_local_messages == 1)
			{
				local_folders[0] = ""; //se local_folders[0] estiver vazio, busca em todas as pastas
			}
		}
		else
		{
			var nm_box = new Array;
			var sel_combo = Element("sel_search_nm_box1");
			
			if( sel_combo.options.length <= 0)
			{
				alert(get_lang("Define the boxes to search!"));
				return false;
			}

			for(var i=0; i < sel_combo.options.length; i++)
			{
				sel_combo.options[i].selected = true;
			}
			
			var get_children = function(folder, arr_folder){
				for(var y = 0; y < folder.children.length; y++){
					if(folder.children[y]){
						arr_folder[arr_folder.length] = folder.children[y].id;
						if (folder.children[y].children.length > 0)
							arr_folder = get_children(folder.children[y], arr_folder);
						
					}
				}
				return arr_folder;
			}
			
			for( var i=0; i < sel_combo.options.length; i++ )
			{
				if( sel_combo.options[i].selected == true )
				{
					var arr_folders = new Array();
					if(!proxy_mensagens.is_local_folder(sel_combo.options[i].value)){
						nm_box[nm_box.length] = sel_combo.options[i].value + fields;
						
						for (x in cp_tree1){
							if(cp_tree1[x].id == sel_combo.options[i].value){
								arr_folders = get_children(cp_tree1[x], arr_folders);
								for (index in arr_folders){
									nm_box[nm_box.length] = arr_folders[index] + fields;
								}
							}
						}
						for (x in cp_tree2){
							if(cp_tree2[x].id == sel_combo.options[i].value){
								arr_folders = get_children(cp_tree2[x], arr_folders);
								for (index in arr_folders){
									nm_box[nm_box.length] = arr_folders[index] + fields;
								}
							}
						}
						
					}else
						local_folders.push(sel_combo.options[i].value.substr(6));
				}
			}
		}
		
        var handler = function( data )
        {
        	var allMsg 	= [3];
			var count  	= ( data['num_msgs'] ) ?  data['num_msgs'] : "0";
			var tmp		= [];

			// Gears - local
			if ( local_folders.length > 0 )
			{
				expresso_local_messages.setSortType('SORTDATE');
				//tmp = expresso_local_messages.search( local_folders, fields );
				expresso_mail_archive.search(local_folders, fields);
				tmp = expresso_mail_archive.search_queryresult;
			}

			if ( data['num_msgs'] )
			{
				allMsg['data'] 		= data['data'];
				allMsg['num_msgs']	= data['num_msgs'];
			}

			if (tmp) {
				if ( tmp.length > 0 )
				{
					allMsg['gears_num_msgs'] = tmp.length ;
				}
			} else {
				allMsg['gears_num_msgs'] = 0;
			}

			if ( ( data['num_msgs'] ) == 0 )
			{
				alert( get_lang("None result was found.") );
			}
			else
			{
				if( ( tmp && tmp.length > 0) && ( !data['num_msgs'] ) ) 
				{
					EsearchE.viewLocalMessage();
				}
				else
				{
					if( count > 0 )
					{
						EsearchE.func_clean();
					}
					write_msg( count + " " + get_lang("results found") );
					
					EsearchE.mount_result( allMsg, 'SORTDATE' );
				}
			}
        };

		// Close Dialog
        if( this.divElement != null )
		{
			$(this.divElement.parentNode).dialog("close").dialog("destroy");
			this.modal.dialog("close");
			this.divElement.parentNode.removeChild(this.divElement);
			this.divElement = null;
		}
        
        this.condition	= nm_box;
        this.page		= 0;
        var args		= "$this.imap_functions.search_msg";
        var params		= "condition=" + nm_box+ "&page=0"+ "&sort_type=SORTDATE";

        if( expresso_offline )
        	handler('none');
        else
        	cExecute( args, handler, params);
	}
	
	searchE.prototype.func_search = function(value, page, sort, border_id)
	{
	  if( !value )
	  {
		  this.func_search_complex();
	  }
	  else
	  {
		  this.quickSearchMail( value, page, sort, border_id );
	  }
	}
	
	searchE.prototype.quickSearchAbort = function(){
	    xhr.abort();
	}
	
	/*
	 * Removido o cExecute e trocado pelo $.ajax do jquery que melhor implementa os controles ajax de requisições
	 * possibilitando o cancelamento real da requisição e liberando usabilidade das demais funcionalidades do módulo 
	 **/
	searchE.prototype.quickSearchMail = function(value, page, sort, border_id)
	{
		var local_folders = new Array;
		var temp;
		var not_found_corrent_folder = true;
		this.all_mailboxes();
		var nm_box = new Array;
		
		for(var i=0; i < EsearchE.all_boxes.length; i++)
		{
			nm_box[i] = EsearchE.all_boxes[i];
		}
		
		//Inserido valor vazio no array para representar todas as pastas locais
		if (preferences.use_local_messages == 1)
			local_folders.push("");

		this.sort		= sort || this.sort || "SORTDATE";
		this.page		= isNaN(page) ? ( value ? 0 : this.page ) : page;
		this.value		= value || this.value || false;
				
		if( !this.value )
		    return alert( "Busca sem caracteres." );

		var args		= "$this.imap_functions.quickSearchMail";
		var params		= {page: this.page, sortType: this.sort, search: this.value};

		var selection1 = [], selection2 = [];

		for( var i = 0; i < nm_box.length; i++ )
		    if( nm_box[i] === current_folder )
			continue;
		    else if( /^user/.test(nm_box[i]) )
			selection2[selection2.length] = nm_box[i];
		    else
			selection1[selection1.length] = nm_box[i];

		 if( expresso_offline )
			    handler('none');
		 else
		 {
			    var url = [], labels = [];

			    params['folder'] = current_folder;
			    url[0] = args + "&" +  $.param( params );
			    labels[0] = get_lang("messages in your current folder");

			    if(selection1.length)
			    {
					params['folder'] = selection1;
					url[1] = args + "&" +  $.param( params );
					labels[1] = get_lang("messages in your other folders");
				}
				if(selection2.length)
			    {
					params['folder'] = selection2;
					url[2] = args + "&" +  $.param( params );
					labels[2] = get_lang("messages in your shared folders");
				}
			    var link = ' <a href="#" style="position: relative; z-index: 10000" onclick="searchE.prototype.quickSearchAbort(); clean_msg(); return false;">'+ get_lang('cancel') +'<a/>';
			    write_msg( get_lang("researching") + " " + labels[0] + link, true );
			    var keepFilled = false;
			    //Inserida variável de controle para correta manipulação das mensagens locais
			    var local_messages_link = true;

			    var handler = function( data )
			    {
					data = $.parseJSON( Base64.decode( connector.unserialize(data)) );
					//data = $.parseJSON( Base64.decode( data) );
            
					var allMsg 	= {
						num_msgs: ( data['num_msgs'] || 0 ),
						data: ( data['data'] || data['msgs'] || data )
					};

					//MAILARCHIVER
                    if(preferences.use_local_messages != 0){
    	                if(local_messages_link){ //Código executado apenas na primeira vez.
    		                if ( local_folders.length > 0 ){
    		                    expresso_mail_archive.search_queryresult = null; //Limpa a variável global para não exibir resultados anteriores
    		                    expresso_mail_archive.search(local_folders, "##ALL <=>" + url_encode(value) + "##");
    		                    tmp = expresso_mail_archive.search_queryresult;
    		                    if(tmp == null){
    		                        tmp = new Object();
    		                        tmp.length = 0;
    		                    }
    		                }
    		                EsearchE.localResults = tmp.length;

    		                if( tmp.length > 0 )
    		                {
    		                    allMsg['gears_num_msgs'] = tmp.length ;
    		                }
    	                }
                    }

					if( allMsg['num_msgs'] )
						EsearchE.total = allMsg['num_msgs'] = Math.max( (EsearchE.total || 0), allMsg['num_msgs'] );	

					EsearchE.mount_result( allMsg, EsearchE.sort, ( keepFilled || border_id === currentTab ), keepFilled, get_lang("Were found")+ " " + data['msgs'].length + " "+labels.shift()+"." );
					write_msg( get_lang("researching") + " " + labels[0] + link, true );
					keepFilled = true;
					if( url.length ){
					    xhr = $.ajax({
							url: 'controller.php?action='+url.shift(),
							async: true,
							success: function(data){
								//Inserida variável de controle para correta manipulação das mensagens locais
								local_messages_link = false;
								handler(data);
							},
							beforeSend: function( jqXHR, settings ){
								connector.showProgressBar();
							},
							complete: function( jqXHR, settings ){
								connector.hideProgressBar();
							}
						});
					}else{
					    xhr = false;
					    clean_msg();
					}
					
			    }

			    xhr = $.ajax({
				url: 'controller.php?action='+url.shift(),
				async: true,
				success: function(data){handler(data);},
				beforeSend: function( jqXHR, settings ){
				  	connector.showProgressBar();
				},
				  complete: function( jqXHR, settings ){
				  	connector.hideProgressBar();
				}
			    });
		    }
	}
	
	// clean;
	searchE.prototype.func_clean = function()
	{
		// Limpa os campos;
		for( var i=0; i < this.txtfields.length; i++ )
		{
			if( Element(this.txtfields[i]) != null )
				Element(this.txtfields[i]).value = "";
		}

        for(i = 0; i < this.selectFields.length; i++)
        {
            if (Element(this.selectFields[i]))
                Element(this.selectFields[i]).selectedIndex = 0;
        }
        
	    if( Element("check_all_msg") != null )
	    	Element("check_all_msg").checked = false;
	    this.elementChecked = false;	

	    EsearchE.all_boxes.splice(0,(EsearchE.all_boxes.length));
	    EsearchE.del_mailboxes();
		
	  	$("#since_date, #before_date").datepicker("option", "minDate", "");
		$("#since_date, #before_date").datepicker("option", "maxDate", "");
	}

	// close
	searchE.prototype.func_close = function(type)
	{
		var _this = this;
		_this.name_box_search = "";
		EsearchE.all_boxes.splice(0,(EsearchE.all_boxes.length));
		_this.type = type;
		_this.searchW['window_search'].close();
		$("#since_date, #before_date, #on_date").datepicker( "destroy" );
	}

	searchE.prototype.aux_local_folder_display = function(folder)
	{
		if(!expresso_offline)
			return "(Local) " + lang_folder(folder.substr(6));
		else
			return lang_folder(folder.substr(6));
	}

	searchE.prototype.refresh = function(alert_new_msg){
		var handler_refresh = function(data){
			var allMsg 	= [3];
			var count  	= ( data['num_msgs'] ) ?  data['num_msgs'] : "0";

			if( data['num_msgs'] )
			{
				allMsg['data'] 		= data['data'];
				allMsg['num_msgs']	= data['num_msgs'];
			}

			if( ( data['num_msgs'] ) == 0 )
			{
				alert( get_lang("None result was found.") );
			}
			else
			{
				if( data['num_msgs'] )
				{
					write_msg( count + " " + get_lang("results found") );
					EsearchE.mount_result( allMsg, 'SORTDATE', true );
				}
			}
		}

		this.condition	= openTab.condition[currentTab];

		var sort_type = (this.sort_type ? this.sort_type : 'SORTDATE');

		if( expresso_offline )
			handler('none');
		else
            if(openTab.condition[currentTab] != '')
			    cExecute( "$this.imap_functions.search_msg", handler_refresh, "condition="+openTab.condition[currentTab]+"&page="+EsearchE.page+"&sort_type="+sort_type);
	}

// Cria o objeto
var EsearchE = new searchE();
var EsearchE = new searchE();
