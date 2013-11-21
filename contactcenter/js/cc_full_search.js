/**
 * @author diogenes
 */
function ccFullSearch() {
	var win;
	var fields = null;
}
	
ccFullSearch.prototype.showForm = function() {
	div = document.getElementById('cc_searchDiv');
// 	if(this.fields==null)
// 		this.fields = new Array(Element('cc_qa_given_names').value, Element('cc_corporate').value+":", Element('cc_qa_email').value, Element('cc_qa_phone').value);
	if(div)
		this.showWindow(div);
	else {
		
		var el = document.createElement("DIV");									
		el.style.visibility = "hidden";									
		el.style.position = "absolute";
		el.style.left = "0px";
		el.style.top = "0px";
		el.style.width = "0px";
		wHeight = Element('ccQAWinHeight').value;
		el.style.height = wHeight + 'px';
		el.className = "div_cc_rectQuickAddContact";
		el.id = 'cc_searchDiv';
		document.body.appendChild(el);																
		el.innerHTML = "";								

		var fieldsTop = 10;
		var fieldsSpace = 30;
		for (i=0; i<this.fields.length; i++) {
			el.innerHTML += '<span id="ccSearchSpan' + i + '" style="position: absolute; top: ' +  (fieldsTop+i*fieldsSpace) + 'px; left: 5px; width: 100px; text-align: right; border: 0px solid #999;">' + this.fields[i] + '</span>';			
			el.innerHTML += '<input id="ccSearchInp' + i + '" type="text" maxlength="30" style="position: absolute; top: ' + (fieldsTop + i * fieldsSpace) + 'px; left: 110px; width: 135px;">';
		}
		el.innerHTML +='<div id="ccSearchFunctions" style="border: 0px solid black; width: 220px; height: 20px">' +
			'<input title="' + Element('cc_panel_search_text').value + '"  type="button" onclick="ccFullSearchVar.go();" style="position: absolute; top: ' + (fieldsTop+i*fieldsSpace) + 'px; left: 35px; width: 60px" value="'+Element('cc_panel_search_text').value+'"/>' +
			'<input title="' + Element('cc_msg_clean').value + '" type="button" onclick="ccFullSearchVar.clean();" value="' + Element('cc_msg_clean').value + '" style="position: absolute; top: ' + (fieldsTop+i*fieldsSpace) + 'px; left: 100px; width: 60px" />' +
			'<input title="' + Element('cc_qa_close').value + '" type="button" onclick="ccFullSearchVar.close();" value="' + Element('cc_qa_close').value + '" style="position: absolute; top: ' + (fieldsTop+i*fieldsSpace) + 'px; left: 165px; width: 60px" />' +
			'</div>';
		el.innerHTML +=	"<br>";

		this.showWindow(el);
	}
		
}

ccFullSearch.prototype.showWindow = function (div)
{		
	if(! this.win) {
		win = new dJSWin({			
			id: 'ccSearch_'+div.id,
			content_id: div.id,
			width: '255px',
			height: wHeight+'px',
			title_color: '#3978d6',
			bg_color: '#eee',
			title: Element('cc_cs_title').value,						
			title_text_color: 'white',
			button_x_img: Element('cc_phpgw_img_dir').value+'/winclose.gif',
			border: true });
		
		this.win = win;
		win.draw();			
	}
	if((ccTree.actualCatalog=='bo_people_catalog') ||
		(ccTree.actualCatalog=='bo_shared_people_manager')) //Habilita empresa apenas para catálogos que não sejam do ldap.
		Element('ccSearchInp1').disabled = false;
	else
		Element('ccSearchInp1').disabled = true;
	this.win.open();
}

ccFullSearch.prototype.go = function () {

	var _this = this;
	var data = new Array();
	var type = Element('cc_type_contact').value;
	
	data['fields'] = new Array();
	
        data['search_for'] = Element('ccSearchInp0').value + " "
        				   + Element('ccSearchInp1').value + " "
        				   + Element('ccSearchInp2').value + " "
        				   + Element('ccSearchInp3').value + " ";

        var invalidChars = /[\%\?]/;
	if(invalidChars.test(data['search_for']) || invalidChars.test(data['search_for_area'])){
		showMessage(Element('cc_msg_err_invalid_serch').value);
		return;
	}

        var search_for = data['search_for'].split(' ');
        var greaterThanMin = false;
        var use_length = v_min;

        for (i = 0; i < search_for.length; i++)
	{
		if (search_for[i].length >= use_length)
		{
			greaterThanMin = true;
		}
	}

	if (!greaterThanMin){
		alert("Favor fazer a consulta com pelo menos " + v_min + " caracteres!");
		return;
	}

        if (Element('ccSearchInp0').value == "")
		data['search_for'] = "";
		
	if (type=='groups') {
		data['fields']['id']     = 'group.id_group';			
		data['fields']['search'] = 'group.title';
	}		
	else {			
		data['fields']['id']     = 'contact.id_contact';		
		data['fields']['search'] = 'contact.names_ordered';					
	}
	//data['search_for'] = Element('ccSearchInp0').value;
	data['full_search'] = new Array();
	
	data['full_search']['corporate'] = Element('ccSearchInp1').disabled?'':Element('ccSearchInp1').value;
	data['full_search']['mail'] = Element('ccSearchInp2').value ;
	data['full_search']['phone'] = Element('ccSearchInp3').value ;
	var handler = function(responseText) {
		ccSearch.mount_handler(responseText);
		_this.close();		
	} 
	Connector.newRequest('fullSearch',CC_url+'search&data='+serialize(data), 'GET',handler);
}

ccFullSearch.prototype.close = function() {
		this.win.close();
}

ccFullSearch.prototype.clean = function() {
	for (i=0; i<this.fields.length; i++) {
		Element('ccSearchInp'+i).value = '';
	}
}

var ccFullSearchVar = new ccFullSearch();
