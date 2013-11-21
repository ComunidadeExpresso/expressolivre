(function()
{
	var _element	= null;
	var _elementA	= null;
	var _elementB	= null;
	var _parent		= null;

	function _configEvent( pObj, pEvent, pHandler )
	{
		if ( pObj && typeof pObj == 'object' )
		{
			if ( pEvent.substring(0, 2) == 'on' )
				pEvent = pEvent.substring(2, pEvent.length);
	
			if ( arguments.length == 3 )
			{
				if ( pObj.addEventListener )
					pObj.addEventListener(pEvent, pHandler, false);
				else if ( pObj.attachEvent )
					pObj.attachEvent('on' + pEvent, pHandler);
			}
			else if ( arguments.length == 4 )
			{
				if ( pObj.removeEventListener )
				{
					pObj.removeEventListener( pEvent, pHandler, false );
				}
				else if ( pObj.detachEvent )
				{
					pObj.detachEvent( 'on' + pEvent, pHandler );
				}
			}
		}
	}

	function _drag(e)
	{
		if (typeof e.preventDefault != 'undefined')
			e.preventDefault();
		else
			e.onselectstart = new Function("return false;");

		_element = ( e.target ) ? e.target : e.srcElement;

		if ( _element )
		{
			_configEvent(_element, 'onmousemove', _mouseMove);
			_configEvent(top.document, 'onmousemove', _mouseMove);

			_configEvent(_element, 'onmouseup', _mouseUp);
			_configEvent(top.document, 'onmouseup', _mouseUp);
		}
	}

	function _elementShadow( pId )
	{
		if( _elementA )
		{
			var _elShadow 			= document.createElement("div");
			_elShadow.id 			= _elementA.id + "__Shadow";
			_elShadow.setAttribute("onselectstart" , "return false");
			_elShadow.style.width	= _elementA.style.width;
			_elShadow.style.height	= _elementA.style.height;
			_elShadow.style.top		= _elementA.style.top;
			_elShadow.style.left	= _elementA.style.left;
			_elShadow.style.zIndex	= _elementA.style.zIndex;
			_elShadow.className		= "x-shadow_Div";
			
			return _elShadow;
		}
	}

	function _load()
	{
		if( arguments.length > 0 )
		{
			var pId = arguments[0];
			
			_configEvent(document.getElementById( pId + "__draggable"), 'onmousedown', _mouseDownShadow);
		}
	}

	function _getMouseOffset(e, el)
	{
		var docPos = _getPosition(el);
		var mousePos = _mouseCoords(e);
		return {
			'x' : mousePos.x - docPos.x,
			'y' : mousePos.y - docPos.y
		};
	}

	function _getPosition(_pObject)
	{
		var left = 0;
		var top  = 0;

		while ( _pObject.offsetParent )
		{
			left += _pObject.offsetLeft;
			top  += _pObject.offsetTop;
			_pObject = _pObject.offsetParent;
		}

		left += _pObject.offsetLeft;
		top  += _pObject.offsetTop;

		return {
			'x' : left,
			'y' : top
		};
	}

	function _mouseCoords(ev)
	{
		var CoordX = "";
		var CoordY = "";

		if ( ev.pageX || ev.pageY )
			return {
				'x' : ev.pageX,
				'y' : ev.pageY
			};

		CoordX = ev.clientX + document.body.scrollLeft - document.body.clientLeft;
		CoordY = ev.clientY + document.body.scrollTop  - document.body.clientTop;
		
		return {
			'x' : CoordX,
			'y' : CoordY
		};
	}

	function _mouseMove(e)
	{
		if ( _element )
		{
			if ( _element.mouseOffset == null )
				_element.mouseOffset = _getMouseOffset(e, _element);

			var mousePos = _mouseCoords(e);

			var x = mousePos.x - _element.mouseOffset.x;
			var y = mousePos.y - _element.mouseOffset.y;
			_element.style.left = (( x < 0 ) ? 0 : x) + 'px';
			_element.style.top  = (( y < 0 ) ? 0 : y) + 'px';
		}
	}

	function _mouseUp()
	{
		if ( _element )
		{
			_configEvent(_element, 'onmousemove', _mouseMove, 'remove');
			_configEvent(top.document, 'onmousemove', _mouseMove, 'remove');

			_configEvent(_element, 'onmouseup', _mouseUp, 'remove');
			_configEvent(top.document, 'onmouseup', _mouseUp, 'remove');

			_element.mouseOffset = null;
			_mouseUpShadow();
		}
	}

	function _mouseDownShadow(e)
	{
		try
		{
			if( arguments.length > 0 )
			{
				var _el = ( e.target ) ? e.target : e.srcElement;
				var _id = (_el.id.substring(0, _el.id.indexOf("__draggable")));	
				
				_elementA = document.getElementById( _id + "__parent");
				_elementB = _elementShadow( _id + "__parent");
				
				_elementA.style.left = '-1500px';
				_parent	  = _elementA.parentNode;
				
				var _B = _parent.appendChild(_elementB);
				
				_configEvent( _elementB, 'onmouseup', _mouseUpShadow);
				_configEvent( _elementB, 'onmousemove', _drag);
			}
			
		}catch(e){}
	}

	function _mouseUpShadow()
	{
		try
		{
			_elementA.style.top		= _elementB.style.top;
			_elementA.style.left	= _elementB.style.left;
			
			var _B = _parent.removeChild(_elementB);

			_elementA = null;
			_elementB = null;
			_element = null;
		}
		catch(e){}
	}
	
	function _drag_drop(){}

	_drag_drop.prototype.set 	= _load;
	window._drag_drop = _drag_drop;
	
})();