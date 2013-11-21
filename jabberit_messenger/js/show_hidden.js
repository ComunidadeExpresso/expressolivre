(function()
{
	var _delay;
	var _event_show 	= false;
	var _event_hidden	= false;

	var _last_displayed;
	var _timeout 		= false;

	function _config(pObj, pEvent, pHandler)
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

	function hdObject()
	{
		if( _last_displayed )
		{
			_last_displayed.style.display = 'none';
			_last_displayed = false;
		}
	}

	function _hidden()
	{
		if( arguments.length > 0 )
		{
			if( arguments[0] )
				clearTimeout( _timeout );
			else
			{
				_timeout = setTimeout( hdObject, _delay);
			}
		}
	}

	function _show(_element)
	{
		if( !(_last_displayed && ( _last_displayed.id == _element.id )))
		{
			_element.style.display = 'block';
			_last_displayed = _element;
		}
	}

	function _view()
	{
		if ( arguments.length == 3 )
		{
			var _event = [
				'onclick',
				'onmousedown',
				'onmouseout',
				'onmouseover',
				'onmouseup'
			];

			_event_show = false;
			_event_hidden = false;

			for ( var i in _event )
			{
				if ( _event[i] == arguments[0] )
					_event_show = arguments[0];

				if ( _event[i] == arguments[1] )
					_event_hidden = arguments[1];
			}

			if ( _event_show && _event_hidden )
			{
				var _element = false;
				
				switch ( typeof arguments[2] )
				{
					case 'object' :
						_element = arguments[2];
					break;
					
					case 'string' :
						_element = document.getElementById(arguments[2]);
					break;
				}

				if ( _element )
					_show(_element);
			}
		}
	}

	function ShowHidden()
	{
		_delay = ( (arguments.length > 0) && !isNaN(arguments[0]) ) ? arguments[0] : 3000;
	}

	
	ShowHidden.prototype.action  		= _view;
	ShowHidden.prototype.hiddenObject	= _hidden;
    
	window.ShowHidden					= ShowHidden;
    
})();