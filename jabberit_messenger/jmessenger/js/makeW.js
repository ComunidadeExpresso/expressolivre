(function()
{
	var _xtools		= null;
	var dragDrop	= null;
	var path		= path_jabberit;
	
	var _dialogs = [ ];
	
	function build(pObj)
	{
		var _window = getElement( pObj.id_window + '__parent');
		_xtools = new xtools(path);		
		
		if( _window == null )
		{
			pObj.width		= ( ( pObj.width > 160 ) ? pObj.width : 160 );
			pObj.height		= ( ( pObj.height > 80 ) ? pObj.height : 80 );
			pObj.onclick	= "_winBuild('" + pObj.id_window + "', '" + pObj.closeAction + "')";
			
			_window = document.createElement("div");
			_window.id	= pObj.id_window + "__parent";
			_window.setAttribute("onselectstart" , "return false");
			_window.style.width		= pObj.width + "px";
			_window.style.height	= pObj.height + "px";
			_window.style.top		= pObj.top + "px";
			_window.style.left		= pObj.left + "px";
			_window.style.position	= "absolute";
			_window.style.zIndex	= pObj.zindex;
			_window.innerHTML		= _xtools.parse( _xtools.xml('window_main'), 'window.xsl', pObj );

			if( pObj.closeAction == "hidden" )
				_window.setAttribute("leftOld", pObj.left + "px" );
			
			if( pObj.leftOld )
				_window.setAttribute("leftOld", pObj.leftOld + "px" );
			
			window.document.body.insertBefore( _window, document.body.lastChild );
			
			if ( pObj.content.constructor == String )
				getElement(pObj.id_window + '__content').innerHTML = pObj.content;
			else
				getElement(pObj.id_window + '__content').appendChild( pObj.content );
			
			_dialogs[ pObj.id_window ] = getElement(pObj.id_window + '__content').firstChild;
			
			if( pObj.draggable )
			{
				dragDrop = new _drag_drop();
				dragDrop.set( pObj.id_window );
			}
		}
		else
		{
	    	if ( pObj.barejid && loadscript.windowPOPUP( pObj.barejid ) )
	    		return false;

	    	load(pObj.id_window, "display");
		}
	}

	function getElement(pElement)
	{
		return document.getElementById(pElement);
	}
	
	function load( pId, pVisible )
	{
		var _window = document.getElementById( pId + '__parent')
		
		if( _window != null )
		{
			if ( pVisible == "display" )
			{
				if( _window.style.left == "-1500px" )
					_window.style.left = _window.getAttribute("leftOld");
			}
			
			if ( pVisible == "hidden")
			{
				_window.setAttribute("leftOld" , _window.style.left );
				_window.style.left = "-1500px";
			}
			
			if( pVisible == "remove" )
			{
				document.body.removeChild( _window );
			}
		}
	}
	
	function _window()
	{
		if( arguments.length > 0 )
		{
			var pId = null;
			if( arguments.length == 1 )
			{
				var obj = arguments[0];
				pId = obj.id_window
				build(obj);
			}
			
			if( arguments.length == 2 )
			{
				load( ( pId = arguments[0] ), arguments[1]);
			}	
		}

		return ( new function( )
		{
			this.content = function( )
			{
				if ( arguments.length )
					getElement( pId + '__content').appendChild( _dialogs[ pId ] );

				return _dialogs[ obj.id_window ];
			};
		} );
	}

	window.bWindow	= _window;

})();

function _winBuild()
{
	if( arguments.length > 0 )
	{	
		if( arguments.length == 1 )
			return bWindow( arguments[0] );
		
		if( arguments.length == 2 )
			return bWindow( arguments[0], arguments[1]);	
	}
	return false;
}