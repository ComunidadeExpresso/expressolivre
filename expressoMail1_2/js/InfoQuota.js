function emInfoQuota ()
	{
		this.win;
		this.el;	
		this.preLoad();
	}
	
	emInfoQuota.prototype.preLoad = function(){
		if(Element("table_quota")) {
			Element("table_quota").style.cursor = "pointer";
			Element("table_quota").title = get_lang("View Quota Usage in Folders");			
			Element("table_quota").onclick = function (){
				InfoQuota.showList();
			}		
		}
	}

	emInfoQuota.prototype.showList = function()
	{
		var _this = this;		
		var el = Element("window_InfoQuota");
		if(!el) {
			el = document.createElement("DIV");
			el.style.visibility = "hidden";
			el.style.position = "absolute";
			el.style.left = "0px";
			el.style.top = "0px";
			el.style.overflowY = "auto";
			el.align = "center";			
			div.style.height = "400px";			
			el.id = 'window_InfoQuota';
			document.body.appendChild(el);
		}		
		
		var handler_buildQuota = function(data){			
			el.appendChild(InfoQuota.buildQuota(data));			
			el.innerHTML += '<br><input style="margin-bottom:10px" type="button" value=' + get_lang("Close")+ 
			' id="InfoQuota_button_close" onClick="InfoQuota.close_window();">';			
			_this.showWindow(el);
		}
		
		cExecute ("expressoMail1_2.imap_functions.get_quota_folders", handler_buildQuota);		
	}

	emInfoQuota.prototype.showWindow = function (div)
	{							
		
		var newWidth = ((parseInt(document.body.clientWidth,10)*0.7)^0);
		if(! this.win) {
			div.style.height = "350px";
			div.style.width = newWidth + "px";
			var title = get_lang("View Quota Usage in Folders");			
			var wHeight = div.offsetHeight + "px";
			var wWidth =  div.offsetWidth   + "px";			

			this.win = new dJSWin({			
				id: 'win_'+div.id,
				content_id: div.id,
				width: wWidth,
				height: wHeight,
				title_color: '#3978d6',
				title_align: 'center',
				bg_color: '#eee',
				title: title,						
				title_text_color: 'white',
				button_x_img: '../phpgwapi/images/winclose.gif',
				border: true });
						
			this.win.draw();
			this.win.title.align = "center";
		}
		
		this.win.open();
	}

	emInfoQuota.prototype.close_window = function() {		
		this.win.close();
	}
	
	emInfoQuota.prototype.buildQuota = function (data){
		if(Element("window_InfoQuota")){
			Element("window_InfoQuota").innerHTML = '';
		}
		var content = '';
		var q_limit = borkb(data.quota_root.quota_limit*1024);		
		
		var value = '';
		var q_used = '';
		var table = document.createElement("TABLE");
		table.id = "table_quota";
		table.style.border="1px solid #CCC";
		table.style.marginTop = "10px";
		table.style.width="100%";
		if (navigator.userAgent.toLowerCase().indexOf('chrome') == -1)// chrome == > -1
		table.style.height="80%";
		table.style.background = "#FFF";
		table.cellSpacing = 5;
		table.cellPadding = 0;
		if (is_ie)
			table.setAttribute('class','table-info-quota');
				 		
		var thead = document.createElement("THEAD");
		var tbody = document.createElement("TBODY");
		var tfoot = document.createElement("TFOOT");
		table.appendChild(thead);
		table.appendChild(tbody);
		table.appendChild(tfoot);		
		thead.style.background = "#FFF";
		var tr_thead = document.createElement("TR");
		tr_thead.style.fontSize = "10pt";
		tr_thead.style.height = '15px';
		tr_thead.style.background = "#3978d6";
		tr_thead.style.color = "white";
		tr_thead.style.fontWeight = "bold";
		thead.appendChild(tr_thead);
		var th_thead = document.createElement("TH");
		th_thead.style.paddingRight = '5px';
		th_thead.style.paddingLeft = '5px';		
		th_thead.innerHTML = get_lang('Folder');
		tr_thead.appendChild(th_thead);		
		th_thead = document.createElement("TH");
		th_thead.style.paddingRight = '5px';
		th_thead.style.paddingLeft = '5px';
		th_thead.style.align = "center";
		th_thead.colSpan = "2"; 
		th_thead.noWrap = "true";		
		th_thead.innerHTML = get_lang('% used');
		tr_thead.appendChild(th_thead);		
		th_thead = document.createElement("TH");
		th_thead.style.paddingRight = '5px';
		th_thead.style.paddingLeft = '5px';
		th_thead.noWrap = "true";
		th_thead.innerHTML = get_lang("Size")+" (bytes)";
		tr_thead.appendChild(th_thead);
		tbody.style.overflowY = "auto";
		tbody.style.overflowX = "hidden";
		tbody.style.width = "50%";		
		var last_folder = 'null';
		for(var x in data) {
			if(x == 'quota_root') continue;				
			q_used = borkb(data[x]['quota_used']);
			value = data[x]['quota_percent'];
			td01 = document.createElement("TD");
			td01.align="left";			
			if(x.indexOf(last_folder+"/") == -1){
				last_folder = x;
				td01.innerHTML = "&nbsp;"+x;
				td01.setAttribute('title',x);
			}
			else {
				var a_folder = x.split('/');
				for (var i =0; i< a_folder.length;i++)
					td01.innerHTML  += "&nbsp;&nbsp;";				
				td01.innerHTML  += a_folder[a_folder.length-1];
				td01.setAttribute('title',a_folder[a_folder.length - 1]);
			}
			
			var user_max_width = ((parseInt(document.body.clientWidth,10)*0.7)^0); 
			var max_len_permited = ((user_max_width*(1/6)*(0.5))^0);//tamanho maximo do nome de pasta
						
			td01.width="40%";
			td01.style.maxWidth = max_len_permited + "px";
			td01.style.borderBottom = "1px dashed #DDD";
			td01.setAttribute('class','td-info-quota');						
			td02 = document.createElement("TD");
			td02.align="center";
			td02.width="5%";
			td02.setAttribute("noWrap","true");			
			td02.innerHTML = value+"%";
			td11 = document.createElement("TD");
			td11.width="10%";
			td11.align="center";
			td11.setAttribute("noWrap","true");
			td11.style.borderBottom = "1px dashed #DDD";
			td11.innerHTML += '&nbsp;<span class="boxHeaderText">'+q_used+"</span>";
			tr2 = document.createElement("TR");
			td21 = document.createElement("TD");
			td21.setAttribute("noWrap","true");			
			td21.height="15px";
			td22 = document.createElement("TD");	
			td21.setAttribute("background","../phpgwapi/templates/"+template+"/images/dsunused.gif");
			table221 = document.createElement("TABLE");
			tbody221 = document.createElement("TBODY");
			table221.appendChild(tbody221);
			table221.style.width=value+"%";	
			td21.width="30%";
			table221.cellSpacing = 0;
			table221.cellPadding = 0;
			tr221 = document.createElement("TR");
			td221 = document.createElement("TD");
			td221.height="15px";
			td221.className = 'dsused';
			td221.style.width = '100%';			
			tr221.appendChild(td221);
			tbody221.appendChild(tr221);
			td21.appendChild(table221);
			tr2.appendChild(td01);
			tr2.appendChild(td02);
			tr2.appendChild(td21);
			tr2.appendChild(td11);	
			tbody.appendChild(tr2);
		}	
		var tr_tfoot = document.createElement("TR");
		tr_tfoot.style.fontSize = "10pt";		
		tr_tfoot.style.color = "#bbb";
		tr_tfoot.style.fontWeight = "bold";
		tfoot.appendChild(tr_tfoot);		
		var th_tfoot = document.createElement("TH");
		th_tfoot.style.align = "center";
		th_tfoot.colSpan = "4";
		th_tfoot.style.paddingRight = '5px';
		th_tfoot.style.paddingLeft = '5px';		
		th_tfoot.innerHTML = get_lang("You are currently using %1 (%2%).",borkb(data.quota_root.quota_used*1024), data.quota_root.quota_percent);
		tr_tfoot.appendChild(th_tfoot);		
		
		return table;
	}
	
/* Build the Object */	
	var InfoQuota;
	InfoQuota = new emInfoQuota();
	
	/* Override function :: refresh and auto_refresh */	
	var __build_quota = build_quota;
	build_quota = function (data) {
		__build_quota(data);
		if(InfoQuota.win)
			InfoQuota.win.close();
		InfoQuota.preLoad();
	};
