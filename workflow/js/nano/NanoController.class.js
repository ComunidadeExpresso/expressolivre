// -----------------------------------------------------------------------------
// Class NanoController
function NanoController()
{
    /* Instance Variables */

    // PRIVATE Variables
    // create NanoAjax Object (executes AJAX)
    var _mObjNanoAjax              = new NanoAjax();

    _mObjNanoAjax.onSuccess        = _defaultSuccessHandler;
    _mObjNanoAjax.onError          = _defaultErrorHandler;


	// #########################################################################
    // Privileged Method (has public access and can access private vars & funcs)
    this.setServerUrl              = setServerUrl;
    this.setWfUrl                  = setWfUrl;
    this.setStatusHandler          = setStatusHandler;
    this.setStatusTranslation      = setStatusTranslation;
    this.setStateChangeId          = setStateChangeId;
    this.setSuccessHandler         = setSuccessHandler;
    this.setExceptionHandler       = setExceptionHandler;
    this.setErrorHandler           = setErrorHandler;

    this.disableErrorReporting     = _disableErrorReporting;

    this.addVirtualRequest         = _addVirtualRequest;
    this.addVirtualAutoloadRequest = _addVirtualAutoloadRequest;

    this.sendRequest               = _sendRequest;


    function setServerUrl( url )
    {
        if( isNaN(url) && url != '' )
        {
            _mObjNanoAjax.ajaxServerUrl = url;
        }
    }

    function setWfUrl( )
    {
		/* encontra o endereço do próprio javascript */
		var address = $A(document.getElementsByTagName("script")).findAll(
			function(s)
			{
				return (s.src && s.src.match(/js\/nano\/NanoController\.class\.js(\?.*)?$/));
			}).first().src;
		/* pega só até o /workflow/ */
		address = address.replace(/js\/nano\/NanoController\.class\.js(\?.*)?$/, '');

		/* completa o endereço */
		address = address + location.href.match(/index\.php\?menuaction=workflow\.run_activity\.go.*$/);
		address = address.replace(/index\.php\?menuaction=workflow\.run_activity\.go/g, 'index.php?menuaction=workflow.run_activity.goAjax');
        this.setServerUrl(address);
    }

    function setStatusHandler( status_handler, div_id )
    {
        var div_id = (div_id) ?  div_id : null;

        if( status_handler != '' )
        {
            _mObjNanoAjax.onStateChange    = status_handler;
            _mObjNanoAjax.onStateChangeDiv = div_id;

        }
    }

    function setStatusTranslation( status_translation )
    {
        if( status_translation != '' )
        {
            _mObjNanoAjax.statusTranslation = status_translation;
        }
    }

    function setStateChangeId( status_change_id )
    {
        if( status_change_id != '' && $(status_change_id) )
        {
            _mObjNanoAjax.onStateChangeId = status_change_id;
        }
    }

    function setSuccessHandler( success_handler )
    {
        if( success_handler != '' )
        {
            _mObjNanoAjax.onSuccess = success_handler;
        }
    }

    function setExceptionHandler( exception_handler )
    {
        if( exception_handler != '' )
        {
            _mObjNanoAjax.onException = exception_handler;
        }
    }

    function setErrorHandler( error_handler )
    {
        if( error_handler != '' )
        {
            _mObjNanoAjax.onError = error_handler;
        }
    }

    function _disableErrorReporting()
    {
        _mObjNanoAjax.errorReporting = false;
    }

    function _addVirtualRequest( identifier, parameter, data )
    {
        var req = new NanoRequest();

        // (virtual) request parameter
        req.requestParameter = parameter;

        // (virtual) request data
        req.requestData      = data;

        _mObjNanoAjax.addVirtualRequest( identifier, req.getAssembledRequest() );
    }

    function _addVirtualAutoloadRequest( parameter, form, field_prefix)
    {
        var req = new NanoRequest();

        // (virtual) request parameter
        req.requestParameter = parameter;

        // (virtual) request auto load data from HTML form
        req.autoLoadData( form, field_prefix );

        _mObjNanoAjax.addVirtualRequest( req.getAssembledRequest() );
    }

    function _sendRequest()
    {
        if( _mObjNanoAjax.ajaxServerUrl != '' && _mObjNanoAjax.ajaxServerUrl.length > 2 )
        {
            if( _mObjNanoAjax.isVirtualRequestSet() )
            {
                _mObjNanoAjax.sendRequest();
            }
            else
            {
                alert('No (virtual) Request is set!!!');
            }
        }
        else
        {
            alert( 'Server URL is not set correctly!!!');
        }
    }

    function _defaultSuccessHandler( decode_json_data )
    {
        var output      = '';
        var row_width   = 90;

        for( i= 0; i < decode_json_data.length; i++)
        {
            for( var row in decode_json_data[i] )
            {
                output += '(virtual) Request No.: '+row+'\n'+unescape(decode_json_data[i][row])+
                          '\n'+(('-').repeat(row_width))+'\n';
            }
        }

        alert( 'Default \'onSuccess\' handler was called!\n'+
               'Please define your own handler to fit your individual needs.\n\n\n'+
               'AJAX Response returns (JSON decoded):\n\n'+
               (('-').repeat(row_width))+'\n'+output);
    }

    function _defaultErrorHandler( decode_json_data )
    {
        alert( "NanoController ERROR:\n"+decode_json_data );
    }
}
