(function()
{
	//Load Images
	var arrowImage		 = path_jabberit + 'templates/default/images/select_arrow.gif';				// Regular arrow
	var arrowImageOver	 = path_jabberit + 'templates/default/images/select_arrow_over.gif';		// Mouse over
	var arrowImageDown	 = path_jabberit + 'templates/default/images/select_arrow_down.gif';		// Mouse down

	var activeOption;
	var selectBoxIds = 0;
	var currentlyOpenedOptionBox = false;
	var editableSelect_activeArrow = false;
	
	function configEvents(pObj, pEvent, pHandler)
	{
		if ( typeof pObj == 'object' )
		{
			if ( pEvent.substring(0, 2) == 'on' )
				pEvent = pEvent.substring(2, pEvent.length);

			if ( pObj.addEventListener )
				pObj.addEventListener(pEvent, pHandler, false);
			else if ( pObj.attachEvent )
				pObj.attachEvent('on' + pEvent, pHandler);
		}
	}
	
	function createEditableSelect()
	{
		var dest 	= "";
		var _left	= "";
		var _top	= "";
		
		if( arguments.length > 0 )
		{
			dest 	= arguments[0];
			_top	= arguments[1];
			_left	= arguments[2];			
		}
		else
		{
			return false;
		}

		dest.className='selectBoxInput';
		
		var div = document.createElement('DIV');
		div.id	= 'selectBox' + selectBoxIds;
		div.style.top		= _top + "px";
		div.style.left		= _left + "px";
		div.style.width		= dest.offsetWidth;
		div.style.position	= 'absolute';
		
		var parent = dest.parentNode;
		parent.insertBefore(div,dest);

		div.appendChild(dest);	
		div.className='selectBox';
		
		var img = document.createElement('IMG');
		img.src = arrowImage;
		img.className = 'selectBoxArrow';
		
		img.onclick = selectBox_showOptions;
		img.id = 'arrowSelectBox' + selectBoxIds;

		div.appendChild(img);
		
		var optionDiv = document.createElement('DIV');
			optionDiv.id = 'selectBoxOptions' + selectBoxIds;
			optionDiv.className='selectBoxOptionContainer';
			optionDiv.style.width = div.offsetWidth-2 + 'px';
		
		div.appendChild(optionDiv);
		
		if(dest.getAttribute('selectBoxOptions'))
		{
			var options = dest.getAttribute('selectBoxOptions').split(';');
			var optionsTotalHeight = 0;
			var optionArray = new Array();
			for(var no = 0 ; no < options.length ; no++)
			{
				var anOption = document.createElement('DIV');
					anOption.innerHTML = options[no];
					anOption.className='selectBoxAnOption';
					anOption.onclick = selectOptionValue;
					anOption.style.width = optionDiv.style.width.replace('px','') - 2 + 'px'; 
					anOption.onmouseover = highlightSelectBoxOption;
					
				
				optionDiv.appendChild(anOption);	
				optionsTotalHeight = optionsTotalHeight + anOption.offsetHeight;
				optionArray.push(anOption);
			}
		
			if(optionsTotalHeight > optionDiv.offsetHeight)
			{				
				for(var no = 0; no < optionArray.length ; no++)
				{
					optionArray[no].style.width = optionDiv.style.width.replace('px','') - 22 + 'px';
				} 	
			}		
			
			optionDiv.style.display		= 'none';
			optionDiv.style.visibility	= 'visible';
			optionDiv.style.zIndex 		= loadscript.getZIndex();
		}

		configEvents(dest,
					'onkeydown',
					function(e)
					{
						switch(e.keyCode)
						{
							case 13:
							case 27:							
								dest.value = dest.value;
						        dest.focus();
        						dest.select();
    							break;
						}
					});
					
		configEvents(dest,
					 'onclick',
					 function(e)
					 {	
						dest.value = dest.value;
						dest.focus();
        				dest.select();
						document.getElementById('selectBoxOptions0').style.display='none';
						document.getElementById('arrowSelectBox0').src = arrowImageOver;														
					 });		
	}

	function highlightSelectBoxOption()
	{
		if( this.style.backgroundColor == '#316AC5' )
		{
			this.style.backgroundColor = '';
			this.style.color = '';
		}
		else
		{
			this.style.backgroundColor = '#316AC5';
			this.style.color = '#FFF';			
		}	
		
		if( activeOption )
		{
			activeOption.style.backgroundColor='';
			activeOption.style.color='';			
		}
		activeOption = this;
	}

	function selectOptionValue()
	{
		var parentNode = this.parentNode.parentNode;
		var textInput = parentNode.getElementsByTagName('INPUT')[0];
		textInput.value = this.innerHTML;
		this.parentNode.style.display='none';
		document.getElementById('arrowSelectBox' + parentNode.id.replace(/[^\d]/g,'')).src = arrowImageOver;
	}

	function selectBox_showOptions()
	{
		if( editableSelect_activeArrow && editableSelect_activeArrow!=this )
			editableSelect_activeArrow.src = arrowImage;

		editableSelect_activeArrow = this;
		
		var numId = this.id.replace(/[^\d]/g,'');
		var optionDiv = document.getElementById('selectBoxOptions' + numId);
		if(optionDiv.style.display=='block')
		{
			optionDiv.style.display='none';
			this.src = arrowImageOver;	
		}
		else
		{			
			optionDiv.style.display='block';
			this.src = arrowImageDown;	
			
			if( currentlyOpenedOptionBox && currentlyOpenedOptionBox!=optionDiv)
				currentlyOpenedOptionBox.style.display='none';	
			
			currentlyOpenedOptionBox= optionDiv;			
		}
	}

	function SelectEditable(){}
	
	SelectEditable.prototype.create = createEditableSelect;
	window.SelectEditable			= SelectEditable;
		
})();