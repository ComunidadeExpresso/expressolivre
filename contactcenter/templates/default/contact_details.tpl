{cc_api}

<input id="cc_contact_details_title" type="hidden" value="{cc_contact_details_title}">
<input id="cc_contact_details_attr_name" type="hidden" value="{cc_contact_details_attr_name}">
<input id="cc_contact_details_attr_value" type="hidden" value="{cc_contact_details_attr_value}">
<input id="cc_contact_details_no_fields" type="hidden" value="{cc_contact_details_no_fields}">

<div id="cc_contact_details_content" style="position: absolute; visibility: hidden">
	<table border="0"  cellpadding="0" cellspacing="0" width="100%">
		<tr>
		<td>
		<div id="id_cc_contact_details_fields" style="position: center; width:500px;height:270px; border: 1px solid #999;overflow:auto; background:#EEE;">
		</div>
		</td>
		</tr>
		<tr>
		<td>
		<div id="ccCDFuncitons" style="border: 0px solid black; width: 220px; height: 20px">
			<input title="{cc_contact_details_close}" type="button" onclick="javascript:closeContactDetails()" value="{cc_contact_details_close}" style="position: absolute; left: 185px; width: 60px" />
		</div>
		</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
<!--
//	Overloading some methods for fix cursor problem in Firefox.
	if(!is_ie) { 
		dJSWin.prototype.close = function() {		
			dJSWin.state = 0;
			dd.elements[this.title.id].hide();
			if ( dd_div = document.getElementById('divScrollMain'))	
				Element("divScrollMain").style.overflow = 'auto';	
		}
		dJSWin.prototype.open = function() {
			this.moveTo(window.innerWidth/2 + window.pageXOffset - dd.elements[this.title.id].w/2,
			    window.innerHeight/2 + window.pageYOffset - dd.elements[this.clientArea.id].h/2);
			dd.elements[this.title.id].maximizeZ();
			dd.elements[this.title.id].show();
			if ( dd_div = document.getElementById('divScrollMain'))
				dd_div.style.overflow = 'hidden';
		}
	}	
		
	var contactdetails_onload = document.body.onload;
	var contactdetailsWin;

	__cdWin = function(e)
	{
		
		contactdetailsWin = new dJSWin({'id': 'cc_contact_details_window',
		             'content_id': 'cc_contact_details_content',
					 'win_class': 'row_off',
					 'width': '500px',
					 'height': '295px',
					 'title_color': '#3978d6',
					 'title': Element('cc_contact_details_title').value,
					 'title_text_color': 'white',
					 'button_x_img': Element('cc_phpgw_img_dir').value+'/winclose.gif',
					 'border': true});

		contactdetailsWin.draw();

	};

	if (is_ie) // || is_moz1_6)
	{
			
		document.body.onload = function(e) { setTimeout('__cdWin()', 10); contactdetails_onload ? setTimeout('contactdetails_onload()'): false;};
	}
	else
	{
//		__cdWin();
	}

//-->
</script>
