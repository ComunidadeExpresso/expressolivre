
// -----------------------------------------------------------------------------
// CLASS NanoAjax
function NanoAjax()
{
    /* Instance Variables */

    // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Public variables

    this.asynchronousMode  = true;
    this.requestMethod     = 'POST';
    this.ajaxServerUrl     = '';

    this.statusTranslation = [ 'initializing...',
                               'connecting...',
                               'connection established',
                               'receiving data...',
                               'done.',
                               'decoding received data...',
                               'processing data...',
                               'done (processing)!' ];

    this.onStateChangeId   = null;

    this.errorReporting    = true;
    this.disableExceptionReporting = false;

    // only needed if request method is set to: GET
    this.preparedGetData   = '';

    // -------------------------------------------------------------------------
    // Private variables

    var _this              = this;
    var _xmlHttpRequest    = null;

    var _virtualRequest    = new Object();

    var _responseHeader    = '';
    var _responseBody      = '';


	// #########################################################################
    // Privileged Method (has public access and can access private vars & funcs)

    // event handler
    this.onStateChange     = null;
    this.onSuccess         = null;
    this.onError           = null;
    this.onException       = null;

    // public accessable methods
    this.addVirtualRequest   = _addVirtualRequest;
    this.isVirtualRequestSet = _isVirtualRequestSet;
    this.sendRequest         = _executeRequest;


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// PRIVATE Methods

    /**
     * checks if virtual request is set
     *
     * @access private
     */
	function _isVirtualRequestSet()
	{
        for ( virtualRequest in _virtualRequest )
        {
            return true;
        }

        return false;
	}


    /**
     * executes real AJAX request
     *
     * @access private
     */
    function _executeRequest()
    {
        _initializeXmlHttpRequest();

        if( typeof _xmlHttpRequest == 'object' )
        {
            this.requestMethod =  this.requestMethod.toUpperCase();

           	_xmlHttpRequest.open( this.requestMethod,      // POST or GET
           	       	              this.ajaxServerUrl +     // full url to Ajax server
           	       	              this.preparedGetData,    // with GET parameterdata
           	                      this.asynchronousMode ); // true = async

           	if( this.requestMethod == 'POST' )
           	{
               	// set various header parameter
                _setPostRequestHeader();
           	}

            // set function if state changes (handler)
        	_xmlHttpRequest.onreadystatechange = _readyStateHandler;

            // now realy send (execute) request to NanoAjax server (with JSON string)
        	_xmlHttpRequest.send( JSON.stringify(_virtualRequest) );

        	// release object preventing memory leaks
        	delete _xmlHttpRequest;
        }
        else
        {
            alert('ERROR while creating xmlhttprequest object!!!\n\n'+
                  'Your System is not able to create a xmlhttprequest object!');
        }
    }


    /**
     * ready state handler (called every time state changes)
     *
     * @access private
     */
    function _readyStateHandler()
    {
        if( _this.onStateChange )
        {
            _this.onStateChange( _xmlHttpRequest.readyState,
                                 _this.statusTranslation[_xmlHttpRequest.readyState],
                                 _this.onStateChangeId );
        }

        try
        {
            if ( // request finished
                 _xmlHttpRequest.readyState == 4
                 && // HTTP: OK                      HTTP: not modified
                 ( _xmlHttpRequest.status == 200 || _xmlHttpRequest.status == 304 )
               )
            {
                _responseHeader = _xmlHttpRequest.getAllResponseHeaders();
                _responseBody   = _xmlHttpRequest.responseText;

                if( -1 == _checkPhpError( _responseBody ) )
                {
                    if( _this.onSuccess )
                    {
                        var _json_decoded = _getJsonDecodedResponse(_responseBody);

                        if( true == (exception_response = _checkServerException(_json_decoded)) )
                        {
                            _this.onSuccess( _json_decoded );
                        }
                        else
                        {
                            ( _this.onException )
                                  ? _this.onException( _responseHeader,
                                                       _responseBody,
                                                       exception_response )

                                  : _defaultErrorHandler( 'SERVER-SIDE EXCEPTION',
                                                          exception_response );
                        }
                    }
                    else
                    {
                        alert( 'No \'onSuccess\' handler defined!!!\n\n'+_responseBody );
                    }
                }
                else
                {
                    ( _this.onError )
                        ? _this.onError( _responseBody )
                        : _defaultErrorHandler( 'SERVER-SIDE ERROR',
                                                'Request could NOT finished!!!' );
                }

                _clearVirtualRequestData();
            }
        }
        catch( exception )
        {
            _clearVirtualRequestData();

            ( _this.onError )
                ? _this.onError( exception )
                : _defaultErrorHandler( 'EXCEPTION', exception );
        }
    }


    /**
     * initializing xmlhttprequest object by iterate over all given methods,
     * to find correct browser implementation.
     *
     * @access private
     */
    function _initializeXmlHttpRequest()
    {
        if( _xmlHttpRequest == undefined || _xmlHttpRequest == null )
        {
            _xmlHttpRequest = Try.these
            (
                function() { return new ActiveXObject('Msxml2.XMLHTTP')   },
                function() { return new ActiveXObject('Microsoft.XMLHTTP')},
                function() { return new XMLHttpRequest()                  }
            ) || false;
        }
    }


    /**
     * sets POST request header
     *
     * @access private
     */
    function _setPostRequestHeader()
    {
        // set method, url and protocol / version
        _xmlHttpRequest.setRequestHeader( 'Method',
                                          this.requestMethod + ' ' +
                                          this.ajaxServerUrl + ' HTTP/1.1' );

        // set content type for POST data
        _xmlHttpRequest.setRequestHeader( 'Content-Type',
                                          'application/x-www-form-urlencoded' );

        // close connection after transfer
        _xmlHttpRequest.setRequestHeader( 'Connection' , 'close' );
    }


    /**
     * add a virtual request to request container (an object)
     *
     * @access private
     */
    function _addVirtualRequest( requestIdentifier, requestObject )
    {
        if( null != requestObject && undefined != requestObject )
        {
            _virtualRequest[requestIdentifier] = requestObject;
        }
    }


    /**
     * checks for server side PHP errors
     *
     * @access private
     */
    function _checkPhpError( response_string )
    {
        return (response_string.toLowerCase()).search(/((parse|fatal) error)|(fatal|warning|notice)/);
    }


    /**
     * checks for server side PHP exceptions
     *
     * @access private
     */
    function _checkServerException( decoded_json_data )
    {
        var request_exception = new Array();

        for( var requestIdentifier in decoded_json_data)
        {
            if( decoded_json_data[requestIdentifier]['exception'] )
            {
                request_exception.push('Exception happend in (virtual) request : '+
                                       requestIdentifier+' {request identifier}\n'+
                                       decoded_json_data[requestIdentifier]['exception'] );
            }
        }

        return (request_exception.length > 0 )
                   ? request_exception.join('\n')
                   : true;
    }


    /**
     * default error handler (if none is specified)
     *
     * @access private
     */
    function _defaultErrorHandler( type_header, message )
    {
        if( true == this.errorReporting )
        {
            alert( 'NanoAjax Class: '+type_header+' !!!\n'+
                   ('=').repeat(54)+  '\n'+
                   '\n'+message +   '\n\n'+
                   ('=').repeat(54)+'\n\n'+
                   'Response Header:   \n'+
                   ('-').repeat(100)+ '\n'+
                   _responseHeader +  '\n'+
                   'Response Body:     \n'+
                   ('-').repeat(100)+ '\n'+
                   _responseBody );
        }
    }


    /**
     *
     *
     * @access private
     */
    function _getJsonDecodedResponse( response )
    {
        if( _this.onStateChange )
        {
            _this.onStateChange( 5,
                                 ((_this.statusTranslation) ? _this.statusTranslation[5] : 5),
                                 _this.onStateChangeId );
        }
        return JSON.parse(response);
    }


    function _clearVirtualRequestData()
    {
        _virtualRequest = new Object();
    }

    /**
     * debugging, triggers AJAX requests / responses into div container
     *
     * @access private
     */
    function _triggerAjaxData( data )
    {
        if( $('id_AJAX_data') )
        {
            $('id_AJAX_data').innerHTML = data + $('id_AJAX_data').innerHTML;
        }
    }
}
