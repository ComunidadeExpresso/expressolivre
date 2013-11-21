	function cIEContacts ()	{
		this.win;
		this.el;		
		this.wWidth = 610;
		this.wHeight = 255;
	}
	
	cIEContacts.prototype.changeOptions = function(type){	
		if(type == 'i') {
			Element('export_span').style.display = 'none';
			Element('import_span').style.display = '';
		}
		else{
			Element('import_span').style.display = 'none';
			Element('export_span').style.display = '';		
		}	
	}
	
	cIEContacts.prototype.showFailures = function(data){
		if (data == 'undefined')
			return;
		if (data){
			var lang_clean = Element('cc_msg_clean').value;
			var info_box = document.getElementById('s_info2');
			info_box.innerHTML = data;
			info_box.style.visibility = '';
			var cleanButton = document.createElement('input');
			cleanButton.type='button';
			cleanButton.value=lang_clean;
			cleanButton.onclick= function() { ccIEContacts.cleanInfo() };
			info_box.appendChild(cleanButton);
			}
	}

	cIEContacts.prototype.cleanInfo = function(){
	var info_box = document.getElementById('s_info2');
	info_box.innerHTML = '';
	}	
	
	cIEContacts.prototype.showList = function(){

		if (!this.el){		
			this.el = document.createElement("DIV");
			this.el.style.visibility = "hidden";
			this.el.style.position = "absolute";
			this.el.style.left = "0px";
			this.el.style.top = "0px";
			this.el.style.width = this.wWidth	+ 'px';
			this.el.style.height = this.wHeight + 100+'px';
			if(is_ie) {
				this.el.style.width = "650";
				this.el.style.overflowY = "auto";	
				this.el.style.overflowX = "hidden";
			}													
			else {									
				this.el.style.overflowY = "auto";
				this.el.style.overflow = "-moz-scrollbars-vertical";
			}
			this.el.id = 'cc_rectIEContacts';
			document.body.appendChild(this.el);

			var lang_import_contacts = Element('cc_msg_import_contacts').value;
            var lang_close_win = Element('cc_msg_close_win').value
            var lang_export_contacts = Element('cc_msg_export_contacts').value;
            var lang_expresso_info_csv = Element('cc_msg_expresso_info_csv').value;
            var lang_expresso_default = Element('cc_msg_expresso_default').value;
            var lang_choose_contacts_file	= Element('cc_msg_choose_contacts_file').value;
            var lang_msg_choose_type		= Element('cc_msg_choose_file_type').value;
			var lang_msg_expresso_info_csv	= Element('cc_msg_expresso_info_csv').value;
			var lang_msg_export_csv			= Element('cc_msg_export_csv').value;
			var lang_msg_automatic = Element('cc_msg_automatic').value;
            var lang_close = Element('cc_msg_close').value;
			var lang_moz_tb = Element('cc_msg_moz_thunderbird').value;
			var lang_outl_pt = Element('cc_msg_outlook_express_pt').value;
			var lang_outl_en = Element('cc_msg_outlook_express_en').value;
			var lang_outl2k_pt = Element('cc_msg_outlook_2k_pt').value;
			var lang_outl2k_en = Element('cc_msg_outlook_2k_en').value;
			var lang_outl03 = Element('cc_msg_outlook_2003').value;
			var lang_expresso_default_csv = Element('cc_msg_expresso_default_csv').value;

		
			this.el.innerHTML = 
			'<div align="left" id="divAppbox" width="90%" ><table width="100%" border=0>'+
			'<tr><td style="border-bottom:1px solid black"><input onclick="javascript:ccIEContacts.changeOptions(this.value)" id="type" type="radio" name="type" value="i" style="border:0" checked>'+lang_import_contacts+
			'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input onclick="javascript:ccIEContacts.changeOptions(this.value)" id="type" type="radio" name="type" style="border:0" value="e"/>'+lang_export_contacts+' <br></td></tr>'+
			'</table>'+
			'<table border=0 height="100%"  width="100%" id="import_span">'+
			'<tr><td>'+
			'<font color="DARKBLUE" size="2">'+lang_expresso_info_csv+'</font></td></tr>'+
			'<tr><td height="75px" valign="top">'+
			'<form name="formCSV" method="POST" enctype="multipart/form-data">'+ lang_msg_choose_type +
			':&nbsp;<select id="typeImport"><option value="auto" selected>'+lang_msg_automatic+'</option>'+
			'<option value="outlook">'+("Outlook Express")+'</option>'+
			'<option value="outlook2000">'+("Outlook 2000")+'</option>'+
			'<option value="outlook2003">'+("Outlook 2003")+'</option>'+
			'<option value="thunderbird">'+("Mozilla Thunderbird")+'</option>'+
			'<option value="expresso" selected>'+lang_expresso_default+'</option></select><br>'+
			'<br> Selecione um grupo:&nbsp;' + Element('cc_select_groups').value + '<br>' +
			'<br>'+lang_choose_contacts_file+'<br><br>'+		
			'<input id="import_file" type="file" name="import_file">'+
			'</form></td></tr>'+
			'<tr><td height="10px" align="center" nowrap><span style="visibility:hidden" id="s_info"></span></td></tr>'+
			'<tr><td height="10px" align="center"></td></tr>'+
			'<tr><td nowrap><center><input id="import_button" type="button" value='+lang_import_contacts+' onClick="javascript:ccIEContacts.importCSV(this)">&nbsp;&nbsp;&nbsp;&nbsp;'+
			'<input type="button" value='+lang_close_win+' onClick="javascript:ccIEContacts.close()"></center></td></tr>'+
			'<tr><td height="10px" align="center" nowrap><span style="visibility:hidden" id="s_info2"></span></td></tr></table>'+
			'<table border=0  height="100%"  width="100%" style="display:none" id="export_span">'+
			'<tr><td>'+						
			'<font color="DARKBLUE" size="2">'+ lang_msg_expresso_info_csv+'</font></td></tr>'+
			'<tr><td height="85px" valign="top">'+lang_msg_export_csv+'<br><br>'+
			'<select id="typeExport">'+
			'<option value="expresso" selected>'+lang_expresso_default_csv+'</option>'+
			'<option value="outlook_pt-BR">'+lang_outl_pt+'</option>'+
			'<option value="outlook_en">'+lang_outl_en+'</option>'+
			'<option value="outlook2000_pt-BR">'+lang_outl2k_pt+'</option>'+
			'<option value="outlook2000_en">'+lang_outl2k_en+'</option>'+
			'<option value="outlook2003">'+lang_outl03+'</option>'+
			'<option value="thunderbird">'+lang_moz_tb+'</option>'+
			'</select>'+			
			'</td></tr>'+
			'<tr><td nowrap><center><input id="export_button" type="button" value='+lang_export_contacts+ ' onClick="javascript:ccIEContacts.exportCSV(this)">&nbsp;&nbsp;&nbsp;&nbsp;'+
			'<input type="button" value='+lang_close_win+ ' onClick="javascript:ccIEContacts.close()"></center></td></tr>'+
			'</table></div>';
		}		
		this.showWindow();
		if(Element('s_info'))
			Element('s_info').style.visibility = 'hidden';
		ccIEContacts.cleanInfo();
	}
	
	cIEContacts.prototype.showWindow = function ()
	{						
		if(!this.win) {
	
				this.win = new dJSWin({			
				id: 'ccIEContacts',
				content_id: this.el.id,
				width: (this.wWidth +(is_ie ? 41 : 0))  +'px',
				height: this.wHeight +100+'px',
				title_color: '#3978d6',
				bg_color: '#eee',
				title: Element('cc_msg_ie_personal').value, 
				title_text_color: 'white',
				button_x_img: '../phpgwapi/images/winclose.gif',
				border: true });
			
			this.win.draw();			
		}
		
		this.win.open();
	}
	
	cIEContacts.prototype.importWriteStatus = function(args){

		// array args
		// args[0] - status (success, error ou importing)
		// args[1] - numero de contatos novos;
		// args[2] - numero de contatos falhos;
		// args[3] - numero de contatos sobrescritos;
		var form = document.formCSV;
		var status = '';

		var lang_import_fail = Element('cc_msg_import_fail').value;
		var lang_importing = Element('cc_msg_importing_contacts').value;
		var lang_import_finish = Element('cc_msg_import_finished').value;
		var lang_new = Element('cc_msg_new').value;
		var lang_failure = Element('cc_msg_failure').value;
		var lang_exists = Element('cc_msg_exists').value;
		var lang_show_more_info = Element('cc_msg_show_more_info').value;

		var l_1		= '<font face="Verdana" size="1" color="GREEN">['+args[1]+lang_new+']</font>';
		var l_2 	= '<font face="Verdana" size="1" color="RED">['+args[2]+lang_failure+']</font>';
		var l_3		= '<font face="Verdana" size="1" color="DARKBLUE">['+args[3]+lang_exists+']</font>';
		if(args[2])
		var l_4 	= '<br><a font face="Verdana" size="1" href="javascript:ccIEContacts.showFailures(\''+args[4]+'\')">'+lang_show_more_info+'</a>';
		var l_error	= '<span style="height:15px;background:#cc4444">&nbsp;&nbsp;<font face="Verdana" size="1" color="WHITE">'+lang_import_fail+ '&nbsp;</font></span>';
		var l_importing = '<span style="height:15px;background:rgb(250, 209, 99)">&nbsp;&nbsp;<font face="Verdana" size="1" color="DARKBLUE">'+lang_importing + '&nbsp;</font></span>';

		if(args[0] == 'success') {

			for(i = 1; i < 4; i++) {
				status += "&nbsp;"+eval('l_'+i);
			}
			if(args[2]){
				status += "&nbsp;"+eval('l_4');
			}
			Element('s_info').innerHTML = '&nbsp;&nbsp;<font face="Verdana" size="1" color="BLACK"><b>'+lang_import_finish+'</b></font><br>&nbsp;'+status;
		}
		else 
			Element('s_info').innerHTML = eval('l_'+args[0]);
		
		Element("s_info").style.visibility = '';

		var recreate_fileupload = function () {
			var import_file = document.createElement("INPUT");
			import_file.type = "FILE";
			import_file.name = "import_file";
			import_file.id = "import_file";
			form.appendChild(import_file);
			Element('import_button').disabled = false;
			form.style.visibility = '';
		}				

		if(args[0] != 'importing') {
			recreate_fileupload();
			setTimeout("Element('s_info').style.visibility = 'hidden'", 12000);
		}
	}
	
	cIEContacts.prototype.importCSV = function ()
	{		
		ccIEContacts.cleanInfo();
		var lang_msg_invalid_csv = Element('cc_msg_invalid_csv').value;
		var form = document.formCSV;
		if ((form.import_file.value.length < 10) || 
		(form.import_file.value.substring(form.import_file.value.length - 4, form.import_file.value.length).toLowerCase() != ".csv")){
			alert(lang_msg_invalid_csv);
			return;
		}
		
		var _this = this;
		Element('import_button').disabled = true;

		var handler_import = function (responseText){
			var args = new Array();

			var data = unserialize(responseText);
			if(data.error)
				args[0] = 'error';
			else {
				args[0] = 'success';
				args[1] = data._new ? data._new : 0;
				args[2] = data._failure ? data._failure : 0;
				args[3] = data._existing ? data._existing : 0;
				args[4] = data._failure_status
				if(args[1] > 0)
					ccTree.setCatalog("0.0");
			}
			_this.importWriteStatus(args);
		}		

		if(! (divUpload = Element('divUpload'))) {
			divUpload		= document.createElement('DIV');		
			divUpload.id	= 'divUpload';
			document.body.appendChild(divUpload);
		}

		divUpload.innerHTML= '<iframe style="display:none;width:0px;height:0px" id="importCSVFile" name="importCSVFile"></iframe>';

		var _onload = function(){
			var typeImport = Element('typeImport').value;
			var id_group = Element('id_group').value;
			
			Connector.newRequest('import_contacts','../index.php?menuaction=contactcenter.ui_data.data_manager&method=import_contacts&typeImport='+typeImport+'&id_group='+id_group,'GET',handler_import);
		}

		if (Element('importCSVFile').attachEvent)
			Element('importCSVFile').attachEvent("onload", _onload);
		else
			Element('importCSVFile').onload = _onload;
					
		form.action ="inc/cc_updown.php";
		form.target ="importCSVFile";		
		form.submit();

		form.removeChild(form.import_file);
		form.style.visibility = 'hidden';
		this.importWriteStatus(new Array('importing'));

	}
	
	cIEContacts.prototype.close = function() {
		this.win.close();
	}
	
	cIEContacts.prototype.exportCSV = function() {
		var lang_export_error = Element('cc_msg_export_error');
		var handler_export = function(data) {
			if(!data){
				alert(lang_export_error	);
				return;
			}				
			
			var div_download = document.getElementById("id_div_download");
	
			if (!div_download){
				div_download = document.createElement("DIV");
				div_download.id="id_div_download";
				document.body.appendChild(div_download);
			}		
			div_download.innerHTML="<iframe style='display:none;width:0;height:0' name='attachment' src='inc/cc_updown.php?&file_name=expresso.csv&file_path="+data+"'></iframe>";
			Element('export_button').disabled = false;
		}		
		var typeExport = Element("typeExport");
		Element('export_button').disabled = true;
		Connector.newRequest('export_contacts', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=export_contacts', 'POST', handler_export, 'typeExport='+typeExport.value);
	}

/* Build the Object */
	var	ccIEContacts = new cIEContacts();