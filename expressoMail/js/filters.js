var rules_limit = 20000;

	function cfilterSh(){
		this.filter_Sh   = new Array;
		this.qvfaf		 = "";
		
		// Images
		this.grp_open_img = new Image();
		this.grp_open_img.src = 'templates/'+template+'/images/filtro/group_open.gif';
		this.grp_close_img = new Image();
		this.grp_close_img.src = 'templates/'+template+'/images/filtro/group_close.gif';
		this.filter_img = new Image();
		this.filter_img.src = 'templates/'+template+'/images/filtro/filters.gif';
		this.for_email_img = new Image();
		this.for_email_img.src = 'templates/'+template+'/images/filtro/answered.gif';
		this.telephone_voip = new Image();
		this.telephone_voip.src = 'templates/'+template+'/images/filtro/telephone_voip.jpg';
	}		

	cfilterSh.prototype.Forms = function(){
	
		var form = document.createElement("DIV");
		form.id  = "window_ffilter_ccform";
		form.style.visibility = "hidden";
		form.style.position = "absolute";
		form.style.left = "0px";
		form.style.top  = "0px"; 
		form.style.width = "0px";
		form.style.height = "0px";
		document.body.appendChild(form);

		var form_cont = document.createElement("SPAN");
		form_cont.id = "form_status";
		form_cont.style.display = "block";			
		form_cont.style.position = "absolute";
		form_cont.style.top = "10px";
		form_cont.style.left = "600px";
		form_cont.innerHTML = "";
		form.appendChild(form_cont);
		
		var form_buttons = document.createElement("DIV");
		form_buttons.id = "form_buttons";
		form_buttons.style.position = "absolute";
		form_buttons.style.display = "";
		form_buttons.style.top = "372px";
		form_buttons.style.left = "5px";
		form_buttons.style.width = "675px";
		form_buttons.innerHTML = "<input type='button' value="+get_lang("Enable")+" onclick='filter.enabled_disabled(\"ENABLED\");'>"+
								 "&nbsp;<input type='button' value="+get_lang("Disable")+" onclick='filter.enabled_disabled(\"DISABLED\");'>"+	
								 "&nbsp;<input type='button' value="+get_lang("Remove")+" onclick='filter.delete_r();'>"+
 								 "&nbsp;<input type='button' value="+get_lang("Close")+" onclick='filter.close_frm();'>";

		form.appendChild(form_buttons);
		
		var form_buttons2 = document.createElement("DIV");
		form_buttons2.id = "Edit_form_buttons";
		form_buttons2.style.position = "absolute";
		form_buttons2.style.display = "none";
		form_buttons2.style.top = "372px";
		form_buttons2.style.left = "5px";
		form_buttons2.style.width = "675px";
		form_buttons2.innerHTML = "<span align='right'><input type='button' value="+get_lang("Back")+" onclick='filter.form_m()'></span>"+
							      "<span align='right'><input type='button' value="+get_lang("Save")+" onclick='filter.saved_rules()'></span>";

		form.appendChild(form_buttons2);
		
		var form_body = document.createElement("DIV");
		form_body.id = "form_body";
		form_body.style.position = "absolute";
		form_body.style.left = "5px";
		form_body.style.top = "5px";
		form_body.style.width = "688px";
                form_body.style.height = "357px";
		form_body.style.borderStyle = "outset";
		form_body.style.borderColor = "black";
		form_body.style.borderWidth = "1px";
		form_body.style.overflow = "auto";
		form.appendChild(form_body);	

		this.showWindow(form);
		if ( filter.rulest.length > 0 ) 
			this.list_rules(); 
		else 
			filter.load_rules(this.list_rules); 
	}

	cfilterSh.prototype.list_rules = function(){
			if(filter.criticalError) 
				return false; 
			else 
				filters.mount_list(); 
	}

	cfilterSh.prototype.mount_list = function(){

		Element('form_status').innerHTML = "";
		var list = "";
	
		// rules
		if(filter.rulest.length == 0){
			list = "<img id='set_rules_img' src='" + this.grp_close_img.src + "' border='0' />&nbsp;<span><b>"+get_lang('list of the filters') + " - ( " + filter.rulest.length + " ) </b></span> - " + "<a href='javascript:filters.n_rule()'>" + get_lang("new rule") + "</a>";		
		}else{
			if ( filter.rulest.length < rules_limit ) //Limit of rules
				list = "<img id='set_rules_img' src='" + this.grp_open_img.src + "' onclick='visibleRulesFalse(\"set_rules\");' border='0' />&nbsp;<span><b>"+get_lang('list of the filters') + " - ( " + filter.rulest.length + " ) </b></span> - " + "<a href='javascript:filters.n_rule();'>" + get_lang("new rule") + "</a>";
			else
				list = "<img id='set_rules_img' src='" + this.grp_open_img.src + "' onclick='visibleRulesFalse(\"set_rules\");' border='0' />&nbsp;<span><b>"+get_lang('list of the filters') + " - ( " + filter.rulest.length + " ) </b></span> - " + get_lang("You have reached the maximum number of rules");
			
			list += "<div id='set_rules'>";
			
			for(var i= 0; i < filter.rulest.length; i++)
			{
				list += "<input id=rule_"+i+" type='checkBox'>&nbsp;<img src='"+this.filter_img.src+"' width='16' height='16' border='0' />&nbsp;" + get_lang("Rule")+" : " + parseInt(i+1) + " -- " + this.vl_rule(filter.rulest[i],i,'') + "<br>";
			}
			list += "</div>";			
		}
		
		list += "<br/>";
		
		// out office
		if(!filter.out_officeR){
			list += "<img id='set_out_img' src='"+this.grp_close_img.src+"' border='0' />&nbsp;";
			list += "<span><b>"+get_lang('out office') + " - ( 0 ) </b></span> " + "<a href='javascript:filters.n_out_office()'>" + get_lang("new rule") + "</a>";			
		}else{
			list += "<img id='set_out_img' src='"+this.grp_open_img.src+"' onclick='visibleRulesFalse(\"set_out\");' border='0' />&nbsp;";
			list += "<span><b>"+get_lang('out office') + " - ( 1 )</b></span>";
			list += "<div id='set_out'>";
			list += "<input id='out_0' type='checkBox'><img id='set_out_form_email_img' src='"+this.for_email_img.src+"' border='0'>";
			list += " " + this.vl_outOffice(filter.out_officeR) + " " ;
			list += "</div>";
		}
		list += "<br/>";
	
		// Voip
		if(preferences.voip_email_redirect)
		{
			if( filter.rulesVoip.length == 0)
			{
				list += "<img id='voip_rule_img' src='"+this.grp_close_img.src+"' border='0' />&nbsp;<span><b>" + get_lang('Phone Warnings List') + " - ( " + filter.rulesVoip.length + " ) </b></span> - " + "<a href='javascript:filters.n_voipFilter();'>" + get_lang("new rule") + "</a>";		
			}
			else
			{
				if ( filter.rulest.length < rules_limit ) //Limit of rules
					list += "<img id='voip_rule_img' src='"+this.grp_open_img.src+"' onclick='visibleRulesFalse(\"voip_rule\");' border='0'/>&nbsp;<span><b>" + get_lang('Phone Warnings List') + " - ( " + filter.rulesVoip.length + " ) </b></span> - " + "<a href='javascript:filters.n_voipFilter()'>" + get_lang("new rule") + "</a>";
				else
					list += "<img id='voip_rule_img' src='"+this.grp_open_img.src+"' onclick='visibleRulesFalse(\"voip_rule\");' border='0'/>&nbsp;<span><b>" + get_lang('Phone Warnings List') + " - ( " + filter.rulesVoip.length + " ) </b></span> - " + get_lang("You have reached the maximum number of rules");
					
				list += "<div id='voip_rule'>";
				for(var i= 0; i < filter.rulesVoip.length; i++)
				{
					list += "<input id='voip_rule_"+i+"' type='checkBox'>&nbsp;<img src='"+this.telephone_voip.src+"' width='16' height='16' border='0'/>&nbsp;" + get_lang("Rule")+" : " + parseInt(i+1) + " -- " + this.vl_rule(filter.rulesVoip[i],i,'voip') + "<br>";
				}
				list += "</div>";
			}
		}

		Element("form_body").innerHTML = list;
	}
	
	cfilterSh.prototype.vl_rule = function(rule,pos,type)
	{
		var fields = rule.split("&&");
		if(type == 'voip')
		{
			return " <a href='javascript:void(0)' onclick=filter.form_r('"+pos+"','voip')><b>" + get_lang("Status") + " : </b>" + "<font color='red'>" + get_lang(fields[2]) + "</font>" + " - " + fields[3] + " <b>" + get_lang("Subject") + ":</b> " + fields[5] + "</a>";
		}
		else
	    {
			var _criteria = " "; 
			if (fields[3].length > 0) 
				_criteria = "<b>"+get_lang("is from")+"</b>:"+fields[3]; 
			if (fields[4].length > 0) 
				_criteria += "<b>&nbsp;"+get_lang("is to")+"</b>:"+fields[4]; 
			if (fields[5].length > 0) 
				_criteria += "<b>&nbsp;"+get_lang("subject is")+"</b>:"+fields[5]; 
			if ( parseInt( fields[11] ) > 0 )
			{
	                    if( fields[8] == "2" || fields[8] == "10" )
	                        _criteria += "<b>&nbsp;"+get_lang("size is over than")+"</b>:"+fields[11];
	
	                    if( fields[8] == "0" || fields[8] == "8" )
	                        _criteria += "<b>&nbsp;"+get_lang("size is under than")+"</b>:"+fields[11];
	        }
	
			var _action = " ";
			
			if (fields[6] == 'folder') 
			{
				var _folderName = ( (fields[7].split(cyrus_delimiter))[1] != undefined ) ? (fields[7].split(cyrus_delimiter))[2] != undefined ? (fields[7].split(cyrus_delimiter))[2] : (fields[7].split(cyrus_delimiter))[1] : fields[7];
	            _action = get_lang("Store at") + " "+ lang_folder( _folderName );
			}
			else if (fields[6] == 'address') 
				_action = get_lang("Forward to")+ " "+(fields[7]);
			else 
				_action = get_lang(fields[6]); 
			
			return " <a href='javascript:void(0)' onclick=filter.form_r('"+pos+"','')><b>" + get_lang("Status") + " : </b>" + "<font color='red'>" + get_lang(fields[2]) + "</font>" + " - <b>" + get_lang("if email") + " </b>" + _criteria + " - <b>" + get_lang("Action") + ":</b> " + _action + "</a>"; 
	    } 
	}
	
	cfilterSh.prototype.vl_outOffice = function(outOffice)
	{
		var aux = outOffice.split("&&");
		return get_lang("Rule") + " - <a href='javascript:void(0)' onclick=filter.form_out()> " + "<b>" + get_lang("Status") + " : </b><font color='red'>" + (aux[4] == "off" ? get_lang("Disabled") : get_lang("Enabled")) + "</font></a>";
	}
	
	cfilterSh.prototype.n_rule = function()
	{
		Element('form_body').innerHTML = "";
		Element('form_body').innerHTML = filter.forms_();	
		Element('div_rule').style.display = "block";
		filter.ac_form = "new_rule";
		Element('form_buttons').style.display = 'none';
		Element('Edit_form_buttons').style.display = '';
		filter.sel_boxes();

	}

	cfilterSh.prototype.n_out_office = function()
	{
		Element('form_body').innerHTML = "";
		Element('form_body').innerHTML = filter.forms_();	
		Element('div_vacation').style.display = "block";			
		Element('form_buttons').style.display = 'none';
		Element('Edit_form_buttons').style.display = '';
		filter.ac_form = "new_out";	
	}

	cfilterSh.prototype.n_voipFilter = function()
	{
		Element('form_body').innerHTML = "";
		Element('form_body').innerHTML = filter.forms_();
		Element('div_voipFilter').style.display = "block";	
		Element('form_buttons').style.display = 'none';
		filter.ac_form = "new_voip";
	}

	function visibleRulesFalse(el)
	{
		Element(el).style.display = "none";
		Element(el+"_img").src = filters.grp_close_img.src;
		Element(el+"_img").onclick = function(){visibleRulesTrue(el);};
	}

	function visibleRulesTrue(el)
	{
		Element(el).style.display = "";
		Element(el+"_img").src = filters.grp_open_img.src;
		Element(el+"_img").onclick = function(){visibleRulesFalse(el);};
	}

	cfilterSh.prototype.showWindow = function (div)
	{
		if(! this.filter_Sh[div.id]) {
			div.style.width = "700px";
			div.style.height = "400px";
			div.style.visibility = "hidden";
			div.style.position = "absolute";
			div.style.zIndex = "10000";			
			var title = ':: ' + get_lang('Filters management') + ' - ' + get_lang('Filters maintenance') + ':: ';
			var wHeight = div.offsetHeight + "px";
			var wWidth =  div.offsetWidth   + "px";

			win = new dJSWin({			
				id: 'filter'+div.id,
				content_id: div.id,
				width: wWidth,
				height: wHeight,
				title_color: '#3978d6',
				bg_color: '#eee',
				title: title,						
				title_text_color: 'white',
				button_x_img: '../phpgwapi/images/winclose.gif',
				border: true });
			
			this.filter_Sh[div.id] = win;
			win.draw();
		}else{
			div.innerHTML = '';
			win = this.filter_Sh[div.id];
			filter.form_m();			
		}
		win.open();
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// build object
 	var filters;
	filters = new cfilterSh();
