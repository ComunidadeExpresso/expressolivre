(function()
{
	var _THREADS = [];
	var _ie;

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

	// xhr = XMLHttpRequest
	function _xhr()
	{
		try
		{
			return new XMLHttpRequest();
		}
		catch (_e)
		{
			_ie = true;
			try
			{
				return new ActiveXObject('Msxml2.XMLHTTP');
			}
			catch (_e1)
			{
				try
				{
					return new ActiveXObject('Microsoft.XMLHTTP');
				}
				catch (_e2)
				{
					return false;
				}
			}
		}
	}

	function _HANDLER()
	{
		var _ID = arguments[0];

		if  ( _THREADS[_ID] )
		{
			if ( _ie && _THREADS[_ID]._XHR.readyState != 4 )
				return false;

			switch ( _THREADS[_ID]._XHR.readyState )
			{
				case 3 :
					if ( _THREADS[_ID]._HANDLER.stream )
					{
						var _data = _THREADS[_ID]._XHR.responseText.substr(_THREADS[_ID]._index).replace(/^ +| +$/g, '');
						//alert(_data);
						_THREADS[_ID]._rtlen = _THREADS[_ID]._XHR.responseText.length;

						if ( _THREADS[_ID]._index < _THREADS[_ID]._rtlen && _data.length )
							try
							{
								_THREADS[_ID]._HANDLER.stream(_data);
							}
							catch(_e)
							{
								//alert("#stream\n\n" + _e + "\n\n" + _e.description);
							}

						if ( _THREADS[_ID] )
							_THREADS[_ID]._index = _THREADS[_ID]._rtlen;
					}
				break;
				case 4 :
					try
					{
						switch ( _THREADS[_ID]._XHR.status )
						{
							case 200:
								var _data = ( _THREADS[_ID]._MODE == 'XML' ) ?
									_THREADS[_ID]._XHR.responseXML :
									_THREADS[_ID]._XHR.responseText;

								if ( _ie && _THREADS[_ID]._HANDLER.stream )
									_THREADS[_ID]._HANDLER.stream(_data);

								var _request = ( _THREADS[_ID]._HANDLER.request ) ?
									_THREADS[_ID]._HANDLER.request : false;

								delete _THREADS[_ID];

								if ( _request )
									try
									{
										_request(_data);
									}
									catch(_e)
									{
										//alert("#request\n\n" + _e + "\n\n" + _e.description);
									}

							break; // [case : status 200]
							case 404:
								delete _THREADS[_ID];
								alert('Page Not Found!');
							break; // [case : status 404]
							default:
								delete _THREADS[_ID];
						}
					}
					catch(e)
					{
					}
				break;
				default :
			}
		}
	}

	function _execute()
	{
		var _ID = arguments[0];
		var _ACTION = 'act=' + _ID;
		var _TARGET = this._PATH;
		var _SEND = null;

		if ( _TARGET != '' && _TARGET.lastIndexOf('/') != (_TARGET.length - 1) )
			_TARGET += '/';

		_TARGET += ( this._CONTROLLER ) ?
			this._CONTROLLER  : 'controller.php';

		if ( _THREADS[_ID]._METHOD == 'GET' )
			_TARGET += '?' + _ACTION;

		_THREADS[_ID]._XHR.open(_THREADS[_ID]._METHOD, _TARGET, true);

		if ( _THREADS[_ID]._METHOD == 'POST' )
		{
			_THREADS[_ID]._XHR.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			_THREADS[_ID]._XHR.setRequestHeader('Cache-Control',  'no-store, no-cache, must-revalidate');
			_THREADS[_ID]._XHR.setRequestHeader('Cache-Control', 'post-check=0, pre-check=0');
			_THREADS[_ID]._XHR.setRequestHeader('Pragma', 'no-cache');
			_SEND = _ACTION + '&' + arguments[1];
		}

		_THREADS[_ID]._XHR.onreadystatechange = function(){_HANDLER(_ID);};
		_THREADS[_ID]._XHR.send(_SEND);
	}

	function usage()
	{
		return ""+
			"Description:\n"+
			"\t<obj>.go(string access, [mixed handler[, mixed post]])\n\n"+
			"Parameters:\n"+
			"\taccess : assinatura de acesso à camada de controle.\n"+
			"\thandler : uma função a ser executada no fim da requisição\n"+
			"\t\tou um objeto que poderá conter dois índices sendo\n"+
			"\t\tque ambos deverão ser uma função que será executada\n"+
			"\t\tconforme o status do objeto xhr, sendo que na posição\n"+
			"\t\t'stream' será a função a ser executada a cada iteração\n"+
			"\t\tdo objeto xhr e na posição 'request' será a função\n"+
			"\t\ta ser executada no fim da requisição.\n"+
			"\tpost : se especificado deverá ser uma query string ou um\n"+
			"\tXML bem formatado.\n\n";
	}

	// @PARAM arguments[0] string :
	//		assinatura de acesso à camada de controle
	//
	// @PARAM arguments[1] object :
	//		OBS : neste caso a conexão assumirá que se trata de uma stream
	//		objeto contendo dois duas funções, sendo,
	//		no índice stream deverá conter uma função que será execultada
	//		a cada mudança de status do objeto xhr
	//	or
	// @PARAM arguments[1] function : funcão a ser executada no retorno da
	//		requisição
	//		OBS : neste caso a conexão assumirá que se trata de uma
	//		requisição função que será execultada no final da requisição
	//
	// @PARAM arguments[2] string :
	//		este parâmetro define se a conexão é via GET ou POST
	//		caso o parâmetro não esteja presente a conexão será execultada
	//		via GET, por outro lado, caso ele exista deverá ser uma query
	//		string válida ou um xml bem formatado
	//
	function go()
	{
		var _argv = arguments;
		var _argc = _argv.length;
		var _ID = _argv[0];
		var _POST;
		if ( _argc < 1 || _argc > 3 )
			return {'error' : "#0\n\n" + usage()};

		if ( typeof _ID != 'string' )
			return {'error' : "#1\n\n" + usage()};

		//if ( _THREADS[_ID] )
		//	return {'error' : '#2 - there is a equal request running'};

		_THREADS[_ID] = {
			'_HANDLER'	: {},
			'_METHOD'	: ( _argv[2] ) ? 'POST' : 'GET',  // [GET | POST]
			'_MODE'		: null,	// [XML | TEXT]
			'_TYPE'		: null,	// [4 for request | 3 for stream]
			'_XHR'		: null	// [4 for request | 3 for stream]
		};

		if ( _argv[2] )
			_POST = _argv[2];

		if ( _argv[1] )
			switch ( typeof _argv[1] )
			{
				case 'function' :
					_THREADS[_ID]._HANDLER = {'request' : _argv[1]};
				break;
				case 'object' :
					for ( var i in _argv[1] )
						if ( i != 'stream' && i != 'request' )
						{
							delete _THREADS[_ID];
							return {'error' : "#3\n\n" + usage()};
						}
						else if ( i == 'stream' )
						{
							_THREADS[_ID]._index = 0;
							_THREADS[_ID]._rtlen = 0;
						}
						_THREADS[_ID]._HANDLER = _argv[1];
				break;
				case 'string' :
					if ( _argc == 2 )
					{
						_THREADS[_ID]._METHOD = 'POST';
						_POST = _argv[1];
					}
				break;
				default :
					//delete _THREADS[_ID];
					//return {'error' : "#4\n\n" + usage()};
			}

		if ( !(_THREADS[_ID]._XHR = _xhr()) )
			return {'error' : "#4 it cannot make a xhr object"};

		( _THREADS[_ID]._METHOD == 'GET' ) ?
			_execute.call(this, _ID) : _execute.call(this, _ID, _POST);
		return {'success' : "your thread is running and the response "+
							"will be manipulated by the handler"};
	}

	function _abort()
	{
		for ( var _ID in _THREADS )
		{
			// @TODO
			// try/catch for unknown error of IE.
			// Check, store and retrieve the try/catch.
			try
			{
				if ( _THREADS[_ID] && _THREADS[_ID]._XHR && _THREADS[_ID]._XHR.abort )
					_THREADS[_ID]._XHR.abort();

				delete _THREADS[_ID];
			}
			catch(e){}
		}
	}

	function Connector()
	{
		var _argv = arguments;
		this._PATH = ( _argv.length > 0 ) ?
			_argv[0] : '';
		this._CONTROLLER = ( _argv.length == 2 ) ?
			_argv[1] : false;
	}

	Connector.prototype.go = go;
	Connector.prototype.abort = _abort;
	window.ADMConnector = Connector;

	_config(window, 'onbeforeunload', _abort);
}
)();
