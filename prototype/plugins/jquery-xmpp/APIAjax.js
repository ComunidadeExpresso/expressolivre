
var APIAjax = new function() {
	
	var _id;
	
	var _data = {};
	
	this.id = function(value) {
		if(value == undefined) return _id;
		var int = parseInt(String(value))
		_id = (isNaN(int))? 0 : int;
		_data[_id] = { stage:0 };
		this.url('').type('POST').params({}).done().always().fail();
		return this;
	};
	
	this.url = function(value) {
		if(value == undefined) return _data[_id].url;
		_data[_id].url = value;
		return this;
	};
	
	this.type = function(value) {
		if(value == undefined) return _data[_id].type;
		_data[_id].type = (value.toUpperCase() == 'GET')? 'GET' : 'POST';
		return this;
	};
	
	this.params = function(value) {
		if(value == undefined) return _data[_id].params;
		_data[_id].params = value;
		return this;
	};
	
	this.done = function(value) {
		_data[_id].done = value;
		return this;
	};
	
	this.fail = function(value) {
		_data[_id].fail = value;
		return this;
	};
	
	this.always = function(value) {
		_data[_id].always = value;
		return this;
	};
	
	this.options = function(value) {
		for (var method in value)
			if(this.hasOwnProperty(method))
				this[method](value[method]);
		return this;
	};

	this.length = function() {
		var size = 0, key;
	    for (key in _data) {
	        if (_data.hasOwnProperty(key) && _data[key].stage > 0) size++;
	    }
	    return size;
	};
	
	this.conf = function() {
		
		_data[_id].send = {};
		_data[_id].send.id = _id;
		_data[_id].send.params = this.params();
		
		var conf = {};
		conf.id		= _id;
		conf.type	= this.type();
		conf.url	= this.url();
		conf.data	= this.params();
		conf.dataType = 'text';
		return conf;
	};
	
	this.transport = function(){
		if ( window.XDomainRequest ) {
			jQuery.ajaxTransport(function( s, o ) {
				if ( s.crossDomain && s.async ) {
					if ( s.timeout ) {
						s.xdrTimeout = s.timeout;
						delete s.timeout;
					}
					var xdr;
					return {
						send: function( _, complete ) {
							function callback( status, statusText, responses, responseHeaders ) {
								xdr.onload = xdr.onerror = xdr.ontimeout = jQuery.noop;
								xdr = undefined;
								complete( status, statusText, responses, responseHeaders );
								
							}

							xdr = new XDomainRequest();
							
 							if (o.id != undefined) _data[o.id].xdr = xdr;

							xdr.onload = function() {
								callback( 200, "OK", { text: xdr.responseText }, "Content-Type: " + xdr.contentType );
							};
							xdr.onerror = function() {
								callback( 404, "Not Found" );
							};
							xdr.onprogress = function() {
								//console.log('onprogress');
							};
							xdr.ontimeout = function() {
								callback( 0, "timeout" );
							};

							xdr.timeout = s.xdrTimeout || Number.MAX_VALUE;
							xdr.open( s.type, s.url );
							xdr.send( ( s.hasContent && s.data ) || null );
						},
						abort: function() {
							if ( xdr ) {
								xdr.onerror = jQuery.noop;
								xdr.abort();
							}
						}
					};
				}
			});
		}
		return this;
	};
	
	this.execute = function() {
		var conf = this.conf();
		_data[_id].stage = 1;
		_data[_id].ajax = jQuery.ajax(conf).done(function(response) {
			if ( _data[this.id] == undefined ) return;
			_data[this.id].stage = -1;
			_data[this.id].send.response = response;
			if ( _data[this.id].done ) _data[this.id].done(response,_data[this.id].send);
			
		}).fail(function(response) {
			if ( _data[this.id] == undefined ) return;
			_data[this.id].stage = -1;
			if ( _data[this.id].fail) _data[this.id].fail(response,_data[this.id].send);
		}).always(function() {
			if ( _data[this.id] == undefined ) return;
			if ( _data[this.id].always ) _data[this.id].always(_data[this.id].send);
			delete _data[this.id];
		});
		this.id(_id+1);
		return this;
	};
	this.abortAll = function() {
	    var tempID = _data[_id];
	    _data = {};
		_data[_id] = tempID;
		return this;
	};
}
APIAjax.transport().id(0);
