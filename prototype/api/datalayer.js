
(function($){$.parseQuery=function(options){var config={query:window.location.search||""},params={};if(typeof options==='string'){options={query:options};}
$.extend(config,$.parseQuery,options);config.query=config.query.replace(/^\?/,'');$.each(config.query.split(config.separator),function(i,param){var pair=param.split('='),key=config.decode(pair.shift(),null).toString(),value=config.decode(pair.length?pair.join('='):null,key);if(config.array_keys(key)){params[key]=params[key]||[];params[key].push(value);}else{params[key]=value;}});return params;};$.parseQuery.decode=$.parseQuery.default_decode=function(string){return decodeURIComponent((string||"").replace('+',' '));};$.parseQuery.array_keys=function(){return false;};$.parseQuery.separator="&";}(jQuery));

internalUrl = /^([A-z0-9-_]+)(:[A-z0-9-_]+)?$/;
internalUri = /^([a-zA-Z0-9-_]+)\(([a-zA-Z0-9-_]+)\):\/\/(.*)|([a-zA-Z0-9-_]+):\/\/(.*)$/;
isGeneratedId = /^\d+\(javascript\)$/;
arrayName = /^([A-z0-9-_]+)\[\]$/;
startsDoubleDot = /^:/;
isIframe = /^iframe.*/;
FILE = 'files';
// cached_urls = {};

$.ajaxPrefilter(function( options, originalOptions, jqXHR ){

      if( options.url != 'undefined' && internalUrl.test( options.url ) ){

// 	  if( !cached_urls[options.url] )
// 	      return;
// 	  alert( options.url + " dentro" );

	   var callback = ( options.success || options.complete || $.noop );

	  if( isIframe.test( options.dataType ) || options.data instanceof FormData )
	  {
	      options.url = options.fileInput.parents( 'form' ).attr( 'action' );
	    
	      res = internalUrl.exec( options.url );

	      var data = {};

	      data[ res[1] ] = DataLayer.form( options.fileInput.parents( 'form' ), options.fileInput );

	      options.formData = DataLayer.serializeForm( data );

	      callback = function( data ){

		    //coutinho, escreva seu codigo aqui.

		    return callback( DataLayer.encode( res[2], data ) );

	      }

	      options.url = DataLayer.dispatchPath + 'post.php';

	      if( typeof FormData !== "undefined" && options.data instanceof FormData )
	      {
		  options.data = new FormData();

		  $.each(options.formData, function (index, field) {
			    options.data.append(field.name, field.value);
			});
		  
		  $.each(options.files, function (index, file) {
			    options.data.append(options.paramName, file);
			});
	      }

	      return( true );
	  }

	  jqXHR.abort();
	  
	  if( typeof options.data === "string" )
	      options.data = $.parseQuery( options.data );

	  switch( options.type.toUpperCase() )
	  {
	    case 'GET':
		  return callback( DataLayer.get( options.url, /*false,*/ options.data ) );

	    case 'POST':
		  return callback( DataLayer.put( options.url, options.data ) );
	  }

      }

});

$("body").on("submit","form",function(event){
    var $this = $(this), action = $this.attr('action'), res = false, method = $this.attr( 'method' ) || 'POST', fileInputs = $this.find('input[type="file"]');
    
    if( fileInputs.length && !$this.is('[enctype="multipart/form-data"]') )
    {
	event.preventDefault();

	    DataLayer.send( action, [ method, 'iframe json' ], {}, DataLayer.receive,
			false, { 'formData': $this.serializeArray(),  'fileInput': fileInputs, 'paramName': FILE + '[]' } );

	return( false );
    }
    
    if(res = internalUrl.exec( action )){
	event.preventDefault();

	var data = DataLayer.form( this );
	
	    switch(method.toUpperCase()){
	        case 'GET': DataLayer.get( res[0], data );
	        case 'POST': DataLayer.put( res[1], data );
	}

	return( false );
    }

    return( true );
});

this.storage = new $.store();

DataLayer = {

    links: {},
    nestedLinks: {},
    concepts: {},
    notArray: {},
    listeners: {},
    encoders: {},
    decoders: {},
    templates: {},
    criterias: {},
    tasks: {},
    
    /**
     * A função render é responsável pela integração dos templates com os dados provenientes do conceito, ou mesmo dados custom.
     * 
     * Use:
     * 
     * DataLayer.render	( 
     *			 'template/listaEmArvore', 								//aqui se passa a URL do template em questão
     *			 'folder:tree' OR { 'node': ['1', '2', '3'] },						//aqui se passa o conceito da qual o template vai ser compilado, juntamente com seu respectivo codec
     *			 '123' OR [ 'AND', [ '=', 'name', 'dedeu' ], [ '=', 'icon', 'folder.png' ] ] OR false   //aqui se passa um id ou filtro quando for nescessário filtrar os conceitos que vão ser usados no render. 
     *														//Se for suprimido, ou receber false, são trazidos todos os conceitos sem filtro
     *			);
     */

    render: function( templateName, data, filter, formatter, force ){

	if( $.isFunction( filter ) )
	{
	    force = formatter;
	    formatter = filter;
	    filter = false;
	}

	if( typeof data === "string" )
	{
	    data = this.get( data, filter, force ) || {};
	}
	
	var formatting = function( template ){

	      if( template === false ) return( false );

	      if( template )
		  DataLayer.templates[ templateName ] = new EJS({ text: template, cache: false });

	      var html = DataLayer.templates[ templateName ].render( { data: data } );

	      if( !formatter )
		  return( html );

	      return formatter( html );
	}

	if( this.templates[ templateName ] )
	{
	    return formatting();
	}


    var path = DataLayer.templatePath + templateName;
    var pathParams = path.match('(.+)(\/modules\/([a-zA-Z\_\-]+).*\/([^\/]+\.ejs))');
    var params = {};
    params['lang'] = !!User.me.lang ? User.me.lang : 'pt_BR';
    params['module'] = pathParams[3];
    params['template'] = pathParams[4];
    params['path'] = pathParams[2] ;

	return this.send(pathParams[1] + '/template.php' , 'get', params, formatting, !!!formatter );
    },
    
    send: function( url, type, data, callback, sync, extraOptions ){
      
	  var result = false, fired = false;
      
	  var envelope = {

	      'async': ( typeof sync !== "undefined" ? !sync : !!callback ),
	      'url': url,
	      'success': function( dt, textStatus, jqXHR ){

		    if( callback )
		    {
			fired = true;
			result = callback( dt, textStatus, jqXHR );
		    }
		    else
			result = dt;

		},
	      'complete': function( jqXHR, textStatus ){

		  if( !fired && callback )
		      result = callback( false, textStatus, jqXHR );

	      },

	      'type': $.isArray( type ) ? type[0] : type,
	      'data': data

	    };

	  if( $.isArray( type ) && type[1] )
	      envelope['dataType'] = type[1];

	  if( extraOptions )
	      envelope = $.extend( envelope, extraOptions );

	  $.ajax( envelope );
      
	  return( result );
    },
    
    dispatch: function( dispatcher, data, callback, isPost, dataType ){
      
      return this.send( this.dispatchPath + dispatcher + ".php", 
			[ ( isPost ? 'post' : 'get' ), dataType || 'json' ],
			data,
			callback );

//       $.ajax({
// 	      'async': !!callback,
// 	      'url': this.dispatchPath + dispatcher + ".php",
// 	      'type': ( isPost ? 'post' : 'get' ),
// 	      'dataType': 'json',
// 	      'data': data,
// 	      'success': function( dt, textStatus, jqXHR ){
// 
// 		    if( callback )
// 		    {
// 			fired = true;
// 			callback( dt, textStatus, jqXHR );
// 		    }
// 		    else
// 			result = dt;
// 
// 		},
// 	      'complete': function( jqXHR, textStatus ){
// 
// 		  if( !fired && callback )
// 		      callback( false, textStatus, jqXHR );
// 
// 	      }/*,
// 	      'processData': false*/
// 	  });

      //return( result );
    },

    form: function( target, fileInputs ){

	var params = {}, $this = $(target), inputArray = $this.serializeArray();

	if( !$this.is( "form" ) )
	    $this = $this.parents( "form" );
		
	if( fileInputs )
		fileInputs.each( function( i, el ){

	      inputArray[ inputArray.length ] = { name: $(this).prop("name"), value: FILE + i };

		});

	$.each( inputArray, function( i, el ){

	    if( newName = arrayName.exec( el.name ) )
		el.name = newName[1];
	    else if( !params[ el.name ] )
		return( params[ el.name ] = el.value );

	    params[ el.name ] = params[ el.name ] || [];

	    if( $.type(params[ el.name ]) !== "array" )
		params[ el.name ] = [ params[ el.name ] ];

	    params[ el.name ].push( el.value );
	});

// 	alert(dump(params));

	return this.decode( $this.attr( "action" ), params );
    },
	
	serializeForm: function( data, level ){
	
		var formData = [];
	
		for( key in data )
		{
			var value = data[key];

			if( level !== undefined )
				key = level+'['+key+']';

			if( $.isArray(value) || $.isPlainObject(value) )
				formData = formData.concat( this.serializeForm( value, key ) );
			else
				formData[ formData.length ] = { name: key, value: value };
		}
		
		return( formData );
	},

    blend: function( action, data ){

// 	if( notArray = (!$.isArray(data)) )
// 	    data = [ data ];

	var form = $('form[action="'+action+'"]');

	form.get(0).reset();

	var named = form.find( 'input[name]' );

	for( var name in data )
	{
	    named.filter( '[name="'+name+'"]' ).val( data[name] );
	}
    },

    /**
     * A função put é responsável pela inserção de dados no DataLayer.
     * 
     * Use:
     * 
     * DataLayer.put	( 
     *			 'folder' OR 'folder:tree', 								//aqui se passa o conceito a ser armazenado. Caso o :codec seja declarado, os dados passam pelo decode do mesmo antes de ser armazenado
     *			 '123' OR [ 'AND', [ '=', 'name', 'dedeu' ], [ '=', 'icon', 'folder.png' ] ] OR false,	//aqui se passa um id ou filtro quando for nescessário setar especificamente quais são os conceitos que vão ser atualizados. Caso se passe false ou mesmo o suprima o DataLayer cria um novo elemento e retorna o novo ID.
     *			  { 'node': [ '1', '2', '3' ] }								//aqui se passa a estrutura de dados a serem armazenados. Caso venha um id na mesma, o conceito e armazenado e atualizado seguindo o mesmo.
     *			);
     */
    
    put: function( concept, filter, data, oneSide ){
      
      ///////////////////////////// normalize ////////////////////////////////
	if( arguments.length == 2 )
	{
	    data = filter;
	    filter = false;
	}
	    if( typeof data === "undefined" || $.type(data) === "boolean" )
	{
	    oneSide = data;
	    data = filter;
	    filter = false;
	}
	
	if( !concept || !data )
	    return( false );

	var decoder = "", id = false, bothSides = (typeof oneSide === "undefined"), notArray, res;
	
	if( $.type(filter) === "string" )
	{
	    id = filter;
	    filter = false;
	}

	if( id )
	    data.id = id;

	if( notArray = ( $.type( data ) !== "array" ) )
	    data = [ data ];

	    if(res = internalUrl.exec( concept )){
	    //TODO: verificar se a decodificaçao deve ser feita em cada item do array
	    data = this.decode( concept, data );
	    concept = res[1];
	    decoder = res[2];
	}

      ////////////////////////////////////////////////////////////////////////

	    if( bothSides || !oneSide ){
	    var result = false, links = this.links( concept ), nestedLinks = this.links( concept, true ), 
	    current = this.check( concept ) || {}, ids = [];

	    for( var i = 0; i < data.length; i++ )
	    {
		var key = ids[ ids.length ] = data[i].id || this.generateId( concept ), updateSet = {};

		////////////////////////////// linkage /////////////////////////////////    
		for( var link in links )
		{
		    if( data[i][link] )
		    {
			var notArray2 = false;

			if( notArray2 = this.hasOne( concept, link ) )
			    data[i][link] = [ data[i][link] ];

			var _this = this, dependency = this.isDependency( concept, link );

			$.each( data[i][link], function( ii, el ){

				var isRef = false;

				if( isRef = ($.type(el) === "string") )
				    el = { id: el };

				//removido pois o mesmo esta gerando inconsistencia em tudo
// 				if( DataLayer.isConcept( links[link], nestedLinks[link] ) )
				if( !DataLayer.hasOne( links[link], nestedLinks[link] ) )
				{
				    el[ nestedLinks[link] ] = el[ nestedLinks[link] ] || [];
				    el[ nestedLinks[link] ].push( key );
				}
				else
				    el[ nestedLinks[link] ] = key;

				if( isRef && ( !current[ key ] || !current[ key ][ link ] || 
				               (notArray2 ? current[ key ][ link ] !== el.id : !$.inArray( el.id, current[ key ][ link ] )) ) )
				{
				    updateSet[ links[link] ] = updateSet[ links[link] ] || [];
				    updateSet[ links[link] ].push( el );
				}
				else if( !isRef )
				    data[i][link][ii] = _this.put( links[link], el, oneSide );
			    });

			if( notArray2 )
			    data[i][link] = data[i][link][0];
		    }
		}
		//////////////////////////////////////////////////////////////////////////

		if( data[i].id )
		    data[i] = this.merge( current[ data[i].id ], data[i] );

		 current[ key ] = data[i];

		if( bothSides )
		  this.report( concept, key, data[i] );
	    }

	    this.store( concept, current );

	    for( var setKey in updateSet )
	    {
		if( bothSides )
		    for( var i = 0; i < updateSet[ setKey ].length; i++ )
		      this.report( setKey, updateSet[ setKey ][i].id, updateSet[ setKey ][i] );
		    
		DataLayer.put( setKey, updateSet[ setKey ], false );
	    }
	}

	if( oneSide ) 
	    this.commit( concept, ids/*, true */);

	this.broadcast( concept, oneSide ? 'server' : bothSides ? 'serverclient' : 'client', true );

	return( notArray ? ids[0] : ids );

    },
    
     /**
     * A função remove é responsável pela remoção de dados no DataLayer.
     * 
     * Use:
     * 
     * DataLayer.remove	( 
     *			 'folder', 	 //aqui se passa o conceito a ser removido.
     *			 '123' OR false, //aqui se passa um id quando for nescessário remover especificamente alguém. Caso se passe false ou mesmo o suprima o DataLayer remove o conceito inteiro.
     *			);
     */
    
    remove: function( concept, id, oneSide ){
	
	if( arguments.length === 2 && typeof id === "boolean" )
	{
		oneSide = id;
		id = false;
	}

	var bothSides = (typeof oneSide === "undefined"),

	links = this.links( concept ), nestedLinks = this.links( concept, true ), ids = [],

	current = this.check( concept, id );

	if( !current ) return;
	
	if( typeof id === "string" )
	{
	    current.id = id;
	    current = [ current ];
	}

	$.each( current, function(i, o)
	{
	    var currentId = ids[ ids.length ] = current[i].id;

	    if( bothSides )
	      DataLayer.report( concept, currentId, false );

	    if( bothSides || !oneSide )
	      DataLayer.del( concept, currentId );

	    for( var link in links )
	    {
			if( !current[i][link] )
				continue;

			if( DataLayer.hasOne( concept, link ) )
				current[i][link] = [ current[i][link] ];

			$.each( current[i][link], function( ii, el ){

				el = DataLayer.storage.cache[links[link]][el];

				if( notArrayNested = ( $.type( el[ nestedLinks[link] ] ) !== "array" ) )
					el[ nestedLinks[link] ] = [ el[nestedLinks[link]] ];

				el[ nestedLinks[link] ] = $.grep( el[ nestedLinks[link] ], function( nested, iii ){
					return ( currentId !== nested );
				});

				if( notArrayNested )
					el[ nestedLinks[link] ] = el[ nestedLinks[link] ][0] || false;
				if(!el[ nestedLinks[link] ] || !el[ nestedLinks[link] ].length)
					delete el[ nestedLinks[link] ];
			});
	    }
	});

	if( oneSide )
	    this.commit( concept, ids );

	this.broadcast( concept, oneSide ? 'server' : bothSides ? 'serverclient' : 'client', false );
    },
    
    /*
     * RemoveFilter = método para remoção de objetos por critério, funcionalidade não implementada no método remove
     * TODO - A remoção é feira em tempo real, onde ainda o mesmo não suporta remoção apenas na camada do cliente
     * caso necessária tao funcionalidade a mesma será implementada no futuro
     **/
    removeFilter: function( concept, filter, oneSide ){
	//remover
	oneSide = true;

	if(filter)
	    filter = this.criteria(concept, filter);
	else
	    return;
	
	if ( $.type(filter) === "array" )
	    filter = { filter: filter, criteria: false };

	var toRemove = {};

	toRemove[concept] = [];
	
	toRemove[concept][toRemove[concept].length] = {filter: filter.filter, criteria: filter.criteria, method: 'delete'};

	this.dispatch( 'call', toRemove, false, true );

	this.broadcast( concept, oneSide ? 'server' : bothSides ? 'serverclient' : 'client', false );
    },
    
    report: function( concept, id, data )
    {      
	var current = this.check( ':current', concept ) || {};

	if( !current[ id ] )
	    current[ id ] = this.check( concept, id ) || {};
	
	this.store( ':current', concept, current );

	var diff = this.diff( current[ id ], data );

	var diffs = this.check( ':diff', concept ) || {};

	if( diffs[ id ] )
	    diff = this.merge( diffs[ id ], diff );

	if( !diff || !$.isEmptyObject( diff ) )
	    diffs[ id ] = diff;

	this.store( ':diff', concept, diffs );
    },

//     enqueue: function( queueName, concept, id, data ){
// 
// 	var queue = this.check( ':' + queueName, concept ) || {};
// 
// 
//     },
//     
//     dequeue: function( queueName, concept, id ){
// 
// 	
// 
//     },
    
    
    
    rollback: function( concept, ids ){
    if(!DataLayer.storage.cache[':diff'])
		return false;
	if(concept){
		if(ids){
			ids = !$.isArray(ids) ? [ids] : ids;
			for (var i in ids)
				delete DataLayer.storage.cache[':diff'][concept][ids[i]];
		}else
			delete DataLayer.storage.cache[':diff'][concept];
	}else{

		var queue = this.prepareQ( 'current', concept, ids );

		ids = [];

		for( var id in queue )
		{
			 this.put( concept, id, queue[id], false );

			 ids[ ids.length ] = id;
		}

		for(var link in ids)
			delete DataLayer.storage.cache[':diff'][ids[link]];

	}
     if( typeof DataLayer.storage.cache[':diff'] != 'object')
         DataLayer.storage.cache[':diff'] = {};
    },
    
    prepareQ: function( queueName, concept, ids ){
      
      var notArray = false;
      
      if( notArray = ($.type(concept) !== "array") )
	  concept = [ concept ];
      
      var q = {};
      
      for( var i = 0; i < concept.length; i++ )
      {
	  var queue = this.check( ':' + queueName, concept[i] || false );
	  
	  if( !queue ) continue;

	  if( ids )
	  {
	      if( $.type(ids) !== "array" )
		  ids = [ ids ];

	      var filtered = {};

	      for( var ii = 0; ii < ids.length; ii++ )
	      {
		  filtered[ ids[ii] ] = queue[ ids[ii] ];
	      }

	      queue = filtered;
	  }

	  q[ concept[i] ] = queue;
      }
      
      return( notArray ? q[ concept[0] ] : q );
    },
    
    clearQ: function( concept, ids ){
      
      	var current = this.check( ':current', concept || false );
	var diffs = this.check( ':diff', concept || false );

	if( !ids )
	    current = diffs = {};
	else
	{
	    if( notArray = ($.type(ids) !== "array") )
	      ids = [ ids ];

	    for( var i = 0; i < ids.length; i++ )
	    {
 		delete current[ ids[i] ];
		delete diffs[ ids[i] ];
	    }
	}

 	this.store( ':current', concept, current );
	this.store( ':diff', concept, diffs );
    },

    commit: function( concept, ids, callback ){
      
	var queue = this.prepareQ( 'diff', concept, ids );

	this.sync( queue, !$.isArray(concept) && concept || false, callback );
    },
    
    sync: function( queue, concept, callback ){

	if( !queue || $.isEmptyObject( queue ) )
	    return;

	if( concept )
	{
	  var helper = {}; 
	  helper[concept] = queue; 
	  queue = helper;
	}

	var data = {}, URIs = {};

	for( var concept in queue )
	    for( var id in queue[concept] )
	    {
		data[ this.URI( concept, id ) ] = queue[concept][id];
		URIs[ this.URI( concept, id ) ] = { concept: concept, id: id };
	    }

	if( $.isEmptyObject( data ) )
	    return;

	this.dispatch( "Sync", data, function( data, status, jqXHR ){

// 	    switch( status )
// 	    {
// 	      case "error":
// 	      case "parsererror":
// 		return DataLayer.rollback( concept, URI );
// 	      case "success":
// 		return DataLayer.commit();
// 	      case "timeout":
// 	      case "notmodified":
// 	    }

	    var received = DataLayer.receive( data );

	    for( var URI in URIs )
		if( typeof received[URI] !== "undefined" )
		    DataLayer.clearQ( URIs[URI].concept, URIs[URI].id );

	    if( callback )
		callback( received );

// 	    for( var URI in data )
// 	    { 
// 		var parsed = DataLayer.parseURI( URI ),
//    
// 		concept = parsed[1], id = parsed[3];
// 
// 		if( $.type(data[URI]) === "string" )
// 		{
// 		  //TODO:threat the exception thrown
// 		  DataLayer.rollback( concept, id );
// 		  delete URIs[ URI ];
// 		  continue;
// 		}
// 
// 		if( data[URI] === false ){
// 		  DataLayer.remove( concept, id, false );
// 		  continue;
// 		}
// 
// 		if( id !== data[URI].id )
// 		  DataLayer.move( concept, id, data[URI].id );
// 		
// 		DataLayer.put( concept, id, data[URI], false );
// 	    }
// 	    
// 	    for( var URI in URIs )
// 		 DataLayer.clearQ( URIs[URI].concept, URIs[URI].id );
// 	    
// 	    if( callback )
// 		callback();

	}, true );

    },
    
    receive: function( data ){
      
	var received = {};
	
	    for( var URI in data )
	    { 
		var parsed = DataLayer.parseURI( URI ),
   
	    concept = parsed[4], id = parsed[5];

	    received[ URI ] = data[ URI ];

		if( $.type(data[URI]) === "string" )
		{
		  //TODO:threat the exception thrown
		  DataLayer.rollback( concept, id );
		  continue;
		}

		if( data[URI] === false ){
		  DataLayer.remove( concept, id, false );
		  continue;
		}

		if( id !== data[URI].id )
		  DataLayer.move( concept, id, data[URI].id );
		
		DataLayer.put( concept,  data[URI].id || id, data[URI], false );
	    }
	    
	return( received );
	    
    },
    
    unique: function( origArr ){ 

	var newArr = [];
      
	for ( var x = 0; x < origArr.length; x++ )
	{
		var found = false;
	    for ( var y = 0; !found && y < newArr.length; y++ ) 
		if ( origArr[x] === newArr[y] )  
		  found = true;

	    if ( !found ) 
		newArr[ newArr.length ] = origArr[x];
	}

	return newArr;
    },

    merge: function( current, data ){
      
	return this.copy(  data, current );

// 	return $.extend( current, data );

    },
    
    // clone objects, skip other types.
    clone: function(target) {
	    if ( typeof target == 'object' ) {
		    Clone.prototype = target;
		    return new Clone();
	    } else {
		    return target;
	    }
    },
      
    // Shallow Copy 
    shallowCopy: function(target) {
	    if (typeof target !== 'object' ) {
		    return target;  // non-object have value sematics, so target is already a copy.
	    } else {
		    var value = target.valueOf();
		    if (target != value) { 
			    // the object is a standard object wrapper for a native type, say String.
			    // we can make a copy by instantiating a new object around the value.
			    return new target.constructor(value);
		    } else {
			    // ok, we have a normal object. If possible, we'll clone the original's prototype 
			    // (not the original) to get an empty object with the same prototype chain as
			    // the original.  If just copy the instance properties.  Otherwise, we have to 
			    // copy the whole thing, property-by-property.
			    if ( target instanceof target.constructor && target.constructor !== Object ) { 
				    var c = clone(target.constructor.prototype);
      
				    // give the copy all the instance properties of target.  It has the same
				    // prototype as target, so inherited properties are already there.
				    for ( var property in target) { 
					    if (target.hasOwnProperty(property)) {
						    c[property] = target[property];
					    } 
				    }
			    } else {
				    var c = {};
				    for ( var property in target ) c[property] = target[property];
			    }
			    
			    return c;
		    }
	    }
    },

    // entry point for deep copy.
    // source is the object to be deep copied.
    // depth is an optional recursion limit. Defaults to 256.
    // deep copy handles the simple cases itself: non-objects and object's we've seen before.
    // For complex cases, it first identifies an appropriate DeepCopier, then delegate the details of copying the object to him.
    copy: function(source, result, depth) {
      
	    // null is a special case: it's the only value of type 'object' without properties.
	    if ( source === null ) return null;

	    // All non-objects use value semantics and don't need explict copying.	  
		if ( typeof source !== 'object' ) return source;

	    if( !depth || !(depth instanceof RecursionHelper) ) depth = new RecursionHelper(depth);

	    var cachedResult = depth.getCachedResult(source);

	    // we've already seen this object during this deep copy operation
	    // so can immediately return the result.  This preserves the cyclic
	    // reference structure and protects us from infinite recursion.
	    if ( cachedResult ) return cachedResult;

	    // objects may need special handling depending on their class.  There is
	    // a class of handlers call "DeepCopiers"  that know how to copy certain
	    // objects.  There is also a final, generic deep copier that can handle any object.
	    for ( var i=0; i<this.comparators.length; i++ ) {

		    var comparator = this.comparators[i];

		    if ( comparator.can(source) ) {
	
			    // once we've identified which DeepCopier to use, we need to call it in a very
			    // particular order: create, cache, populate.  This is the key to detecting cycles.
			    // We also keep track of recursion depth when calling the potentially recursive
			    // populate(): this is a fail-fast to prevent an infinite loop from consuming all
			    // available memory and crashing or slowing down the browser.
      
			    if( !result )
				// Start by creating a stub object that represents the copy.
				result = comparator.create(source);
			    else if( !comparator.can(result) )
				throw new Error("can't compare diferent kind of objects.");

			    // we now know the deep copy of source should always be result, so if we encounter
			    // source again during this deep copy we can immediately use result instead of
			    // descending into it recursively.  
			    depth.cacheResult(source, result);

			    // only DeepCopier.populate() can recursively deep copy.  So, to keep track
			    // of recursion depth, we increment this shared counter before calling it,
			    // and decrement it afterwards.
			    depth.depth++;
			    if ( depth.depth > depth.maxDepth ) {
				    throw new Error("Exceeded max recursion depth in deep copy.");
			    }

			    // It's now safe to let the comparator recursively deep copy its properties.
			    var returned = comparator.populate( function(source, result) { return DataLayer.copy(source, result, depth); }, source, result );
	
				if(returned)
					result = returned;

			    depth.depth--;

			    return result;
		    }
	    }
	    // the generic copier can handle anything, so we should never reach this line.
	    throw new Error("no DeepCopier is able to copy " + source);
    },

    // publicly expose the list of deepCopiers.
    comparators: [],

    // make deep copy() extensible by allowing others to 
    // register their own custom Comparators.
    registerComparator: function(comparatorOptions) {

	  // publicly expose the Comparator class.
	  var comparator = {

	      // determines if this Comparator can handle the given object.
	      can: function(source) { return false; },
    
	      // starts the deep copying process by creating the copy object.  You
	      // can initialize any properties you want, but you can't call recursively
	      // into the copy().
	      create: function(source) { },

	      // Completes the deep copy of the source object by populating any properties
	      // that need to be recursively deep copied.  You can do this by using the
	      // provided deepCopyAlgorithm instance's copy() method.  This will handle
	      // cyclic references for objects already deepCopied, including the source object
	      // itself.  The "result" passed in is the object returned from create().
	      populate: function(deepCopyAlgorithm, source, result) {}
	  };

	  for ( var key in comparatorOptions ) comparator[key] = comparatorOptions[key];

	  this.comparators.unshift( comparator );
    },
    
    escapedJSON: function(text)
    {
	  return JSON.stringify( text ).replace( /[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&" );
    },
 
    diff: function( base, toDiff ){

	if( typeof base === 'undefined' || $.isEmptyObject(base) )
	    return( toDiff );

	if( toDiff === false )
	    return( false );

	toDiff = $.extend( {}, toDiff );

	for( var key in toDiff )
	{
	    switch( $.type(toDiff[key]) )
	    {
	      case 'object': 
		if( $.isEmptyObject(toDiff[key] = this.diff( base[key], toDiff[key] )) )
		  delete toDiff[key];
	      break;
	      case 'array':
		if( base[key] && !(toDiff[key] = $.grep( toDiff[key], function( el, i ){ return( $.isArray( el ) || $.isPlainObject( el ) ? !RegExp( DataLayer.escapedJSON( el ) ).test( JSON.stringify(base[key]) ) : $.inArray( el, base[key] ) === -1 ); } )).length )
		  delete toDiff[key];
	      break;
	      default:
		if( base[key] == toDiff[key] )
		  delete toDiff[key];
	    }
	}

	return( toDiff );

    },
    
    links: function( concept, reverse ){

	if( !this.links[ concept ] )
	{
	    var result = this.dispatch( "links", { concept: concept } ) || false;

	    if( !result )
		return( false );

	    this.concepts[ concept ] = $.extend( this.concepts[ concept ] || {}, 
						 result['concepts'] || {} );

	    this.links[ concept ] =  result['links'] || {};
	    
	    this.notArray[ concept ] = result['hasOne'] || {};

	   this.nestedLinks[ concept ] = result['nestedLinks'] || {};
	}

	if( reverse )
	{
	    return( this.nestedLinks[ concept ] );
	}

	return( this.links[ concept ] );

    },
    
    isDependency: function( concept, attr ){
      
	if( typeof this.concepts[concept] === "undefined" )
	{
	    this.links( concept );
	}

	return !!this.concepts[ concept ][ attr ];
    },
    
    hasOne: function( concept, attr ){
      
	if( typeof this.notArray[concept] === "undefined" )
	{
	    this.links( concept );
	}

	return !!this.notArray[ concept ][ attr ];
    },
    
    URI: function( concept, URI, context ){
      
	if( res = internalUrl.exec( concept ) )
	    concept = res[1];
	
	context = context ? "(" + context + ")" : "";
      
	if( URI )
	    return( concept + context + "://" + URI );
	else
	    return( concept );
      
    },
    
    parseURI: function( URI ){

	return internalUri.exec( URI ) || false;

    },
    
    
   
    
    generateId: function( concept ){
      
	var newId = this.counter + "(javascript)";
      
	this.store( ":counter", (this.counter++) + "" );
	
	return( newId );
    },
   

   

    get: function( concept, /*URI, */filter, oneSide ){

	///////////////////////////// normalize ////////////////////////////////
	if( arguments.length == 2 && $.type(filter) === "boolean" )
	{
	    oneSide = filter;
	    filter = false;
	}
	
	var encoder = false, id = false, bothSides = (typeof oneSide === 'undefined'), res;
	
	if( $.type(filter) === "string" )
	{
	    id = filter;
	    filter = false;
	}

	filter = filter || false;

	if( !concept )
	    return( false );

	if( res = internalUrl.exec( concept ) )
	{
	    encoder = concept;
	    concept = res[1];

	    if( filter )
		filter = this.criteria( encoder, filter );
	}
	
	if ( $.type(filter) === "array" )
	{
	    filter = { filter: filter, criteria: false };
	}
	
	//////////////////////////////////////////////////////////////////////////
	
	var result = false;
	
	if( bothSides || !oneSide )
	    result = this.check( concept, id || filter );
	
	if (bothSides && filter.filter)
		result = false;

		
	if( !result && (bothSides || oneSide) )
	{
	    result = this.request( concept, id || filter.filter, filter.criteria );

	    if( result && bothSides && (!filter || 
					!filter.criteria || 
					!filter.criteria.format) )
	    {
	      var newResult = [];
	    
	      $.each( result, function( i, res ){
			newResult[ i ] = $.extend( {}, res );
		  });

	      this.put( concept, id, newResult, false );
	    }
	}

	if( /*result &&*/ encoder )
	    result = this.encode( encoder, result, filter ); //TODO: retirar o filtro no método encode

	return( result );
    },
    
    filter: function( base, filter, criteria ){
      
	if( !$.isArray( filter || [] ) )
	    filter = filter.filter || false;

	if( !filter )
	    return( base );

	var filtered = [];
      
	for( var key in base )
	    if( this.storage.filter( base[key], filter ) )
		filtered[ filtered.length ] = key;

	return( filtered );
    },

	converterType: function( filter ){

			return isNaN(parseInt(filter)) ? filter : parseInt(filter);
		
	},

    
    compare: function( operator, base, test ){
      
	base = this.converterType(base);
	test = this.converterType(test);
	 
    switch( operator ){
	
	case '*':  return RegExp( ".*" + test + ".*" ).test( base );
	case '^':  return RegExp( "^" + test +  ".*" ).test( base );
	case '$':  return RegExp( ".*"  + test + "$" ).test( base );

	case '&':  return ( base && test );
	case '|':  return ( base || test );

	case '=':  return ( base == test );
	case '<=': return ( base <= test );
	case '>=': return ( base >= test );
	case '>':  return ( base <  test );
	case '<':  return ( base >  test );
	
	default: return true;
	}
      
    },
    
//     clone: function( object ){
// 
// 	new { prototype: object };
// 
//     },

    check: function( namespace, filter ){

	if( !namespace )
	    return( false );

	var result = this.storage.get( namespace );

	if( !filter || !result )
	  return( result || false );

	var keys = DataLayer.copy( filter );

	if( notArray = $.type(keys) === "string" )
	    keys = [ keys ];
	else if( $.type(keys) !== "array" )
		keys = this.filter( result, keys.filter, keys.criteria );

	var res = [];

	for( var i = 0; i < keys.length; i++ )
	    res[ res.length ] = result[keys[i]];

	return( notArray ? res[0] || false : res.length ? res : false );
    },

    storage: {
      
	cache: {},
      
	set: function( key, value ){

	    this.cache[key] = value;

	},
	get: function( key ){

	    return DataLayer.copy( this.cache[key] );

	},
	del: function( key ){

	    delete this.cache[key];

	},
	
	filter: function( base, filter ){
      
	    var bool, op = filter.shift();

	    switch( op )
	    {
		case 'IN':
		  for( var i = 0, f = []; i < filter[1].length || !(filter = f); i++ )
		      f[i] = [ '=', filter[0], filter[1][i] ];
		case 'OR':
		    op = '|';
		    bool = false;
		break;
		case 'AND': 
		    op = '&';
		    bool = true;
		break;
		default : return DataLayer.compare( op, base[ filter[0] ], filter[1] );
	    }
	    
	    for( var strict = bool; 

		filter.length && ( strict ? bool : !bool ); 
	    
		bool = DataLayer.compare( op, bool, this.filter( base, filter.shift() ) ) );

	    return( bool );
	}
    },

    flush: function(){

    },
    
    restore: function(){
      
    },

    store: function( namespace, key, data ){

	if( !data )
	  return this.storage.set( namespace, key );

	var res = this.check( namespace ) || {};

	res[key] = data;

	return this.storage.set( namespace, res );
    },

    del: function( namespace, key ){
      
	if( !key )
	  return this.storage.del( namespace );

	var res = this.check( namespace ) || {};

	delete res[key];
	
	if( !$.isEmptyObject( res ) )
	    return this.storage.set( namespace, res );

	this.storage.del( namespace );
      
    },
    
     move: function( concept, oldId, newId ){

	this.put( concept, newId, this.check( concept, oldId ), false );

	this.remove( concept, oldId, false );
    },
    

    
    
    
    request: function( concept, filter, criteria ){

      var id = false, criteria = criteria || {};

      if( $.type(filter) === "string" )
      {
	  id = filter;
	  filter = false;
      }

      return this.dispatch( "request", { 

	  concept: concept || '',
	  id: id || '',
	  filter: filter || '',
	  criteria: criteria || '',
	  service: criteria.service || '',
	  properties: criteria.properties || ''

      }, false, true );
    },
    
    generateURI: function( concept ){
      
	return this.URI( concept, this.generateId( concept ), "javascript" );

    },
    

    broadcast: function( concept, status, diff ){

	if( this.listeners[ concept ] )
	    for( var i = 0; 
		i < this.listeners[ concept ].length; 
		this.listeners[ concept ][ i++ ]( status, diff ) );
    },

    listen: function( concept, listener ){

	this.register( "listeners", concept, listener );

    },

    codec: function( concept, namespace, codec ){

	if( codec.encoder )
	    this.encoder( concept, namespace, codec.encoder );
	if( codec.decoder )
	    this.decoder( concept, namespace, codec.decoder );
	if( codec.criteria )
	    this.register( "criterias", concept + ":" + namespace, codec.criteria );

    },

    encoder: function( concept, namespace, encoder ){

	this.register( "encoders", concept + ":" + namespace, encoder );

    },

    encode: function( encoder, data, filter ){

	if( this.encoders[ encoder ] )
	    for( var i = 0;
		i < this.encoders[ encoder ].length;
		data = this.encoders[ encoder ][ i++ ]( data, filter ) );

	return( data );
    },

    decoder: function( concept, namespace, decoder ){

	this.register( "decoders", concept + ":" + namespace, decoder );

    },

    decode: function( decoder, data ){

	if( this.decoders[ decoder ] )
	    for( var i = 0;
		i < this.decoders[ decoder ].length;
		data = this.decoders[ decoder ][ i++ ]( data ) );

	return( data );
    },

    criteria: function( codec, filter ){

	if( this.criterias[ codec ] )
	    for( var i = 0;
		i < this.criterias[ codec ].length;
		filter = this.criterias[ codec ][ i++ ]( filter ) );

	return( filter );

    },

    register: function( kind, concept, deployable ){

      if( arguments.length < 3 )
      {
	  deployable = concept;
	  concept = kind;
	  kind = 'global';
      }

      if( !this[ kind ][ concept ] )
	    this[ kind ][ concept ] = [];

	this[ kind ][ concept ][ this[ kind ][ concept ].length ] = deployable;

    },
    
    start: function(){

	var timer = function(){

	      setTimeout( timer, 1000 );

	      var now = parseInt( $.now() / 1000 );

	      var tasks = DataLayer.tasks[ now ];

	      if( !tasks ) return;

	      for( var i = 0; i < tasks.length; i++ )
	      {
		  var result = tasks[i].task( now );

		  if( tasks[i].factor )
		  DataLayer.schedule( tasks[i].task, tasks[i].factor );
	      }
      
	      delete DataLayer.tasks[ now ];
	};

	setTimeout( timer, 1000 );
    },
    
    task: function( timestamp, task, factor )
    {
	if( !this.tasks[ timestamp ] )
	    this.tasks[ timestamp ] = [];

	this.tasks[ timestamp ][ this.tasks[ timestamp ].length ] = { task: task, factor: factor || false };
    },

    schedule: function( task, time ){

	time = time || 1;
	
	var index = parseInt( $.now() / 1000 ) + time;

	this.task( index, task, time );
    },
    
    poll: function( concept, time ){
      
      this.schedule( function( now ){
  
	  DataLayer.commit( concept );

	}, time || 5 );
    },
    
    init: function(){
      
	this.counter = parseInt( this.get( ":counter", false ) ) || 0;

	if( !this.dispatchPath )
	    this.dispatchPath = "../../";

	if( !this.templatePath )
	    this.templatePath = "";

	if( !this.basePath )
	    this.basePath = this.dispatchPath + "REST.php?q=";

	this.schedule( function( now ){

	    DataLayer.flush();

	});

	this.start();
    }
}

// the re-usable constructor function used by clone().
function Clone() {}

//Recursion Helper
function RecursionHelper(){ this.clear(); };

RecursionHelper.prototype = {
  
	constructor: RecursionHelper,

	// copiedObjects keeps track of objects already copied by this
	// deepCopy operation, so we can correctly handle cyclic references.
	copiedObjects: [],

	depth: 0,

	maxDepth: 256,

	//reset the recursion helper cache
	clear: function(){
		this.copiedObjects = [];
		this.depth = 0;
	},

	// add an object to the cache.  No attempt is made to filter duplicates;
	// we always check getCachedResult() before calling it.
	cacheResult: function(source, result) {
		this.copiedObjects.push([source, result]);
	},

	// Returns the cached copy of a given object, or undefined if it's an
	// object we haven't seen before.
	getCachedResult: function(source) {

		for ( var i=0; i<this.copiedObjects.length; i++ ) {
			if ( this.copiedObjects[i][0] === source ) {
				return this.copiedObjects[i][1];
			}
		}

		return undefined;
	}
};

// Generic Object copier
// the ultimate fallback DeepCopier, which tries to handle the generic case.  This
// should work for base Objects and many user-defined classes.
DataLayer.registerComparator({
	can: function(source) { return true; },

	create: function(source) {
		if ( source instanceof source.constructor ) {
			return DataLayer.clone(source.constructor.prototype);
		} else {
			return {};
		}
	},

	populate: function(deepCopy, source, result) {
		for ( var key in source ) {
			if ( source.hasOwnProperty(key) ) {
				result[key] = deepCopy(source[key], result[key]);
			}
		}
		return result;
	}
});

// Array copier
DataLayer.registerComparator({
	can: function(source) {
		return ( source instanceof Array );
	},

	create: function(source) {
		return new source.constructor();
	},

	populate: function(deepCopy, source, result) {
		for ( var i=0; i<source.length; i++) {
			result.push( deepCopy(source[i], result[i]) );
		}
		result =  DataLayer.unique( result )
		return result;
	}
});

// Date copier
DataLayer.registerComparator({
	can: function(source) {
		return ( source instanceof Date );
	},

	create: function(source) {
		return new Date(source);
	}
});

// HTML DOM Node copier
DataLayer.registerComparator({

	// function to detect Nodes.  In particular, we're looking
	// for the cloneNode method.  The global document is also defined to
	// be a Node, but is a special case in many ways.
	can: function(source) { 
	  
	  if ( window.Node ) {
		  return source instanceof Node;
	  } else {
		  // the document is a special Node and doesn't have many of
		  // the common properties so we use an identity check instead.
		  if ( source === document ) return true;
		  return (
			  typeof source.nodeType === 'number' &&
			  source.attributes &&
			  source.childNodes &&
			  source.cloneNode
		  );
	  } 
      },

      create: function(source) {
	      // there can only be one (document).
	      if ( source === document ) return document;

	      // start with a shallow copy.  We'll handle the deep copy of
	      // its children ourselves.
	      return source.cloneNode(false);
      },
      
      diff: function(base, source){
	
      },

      populate: function(deepCopy, source, result) {
	      // we're not copying the global document, so don't have to populate it either.
	      if ( source === document ) return document;

	      // if this Node has children, deep copy them one-by-one.
	      if ( source.childNodes && source.childNodes.length ) {
		      for ( var i=0; i<source.childNodes.length; i++ ) {
			      var childCopy = deepCopy(source.childNodes[i], result.childNodes[i] || false );
			      result.appendChild(childCopy);
		      }
	      }
		return result;
      }
});

DataLayer.init();

// setTimeout(function(){
//  
//     
// 
// }, 1000 );

// var DataLayer = {
// 
//     get: function( concept, filter ){
// 
// 	var data = this.storage.get( concept );
// 
// 	if( !filter )
// 	    return( data );
// 
// 	return this.filter( data, filter );
//     },
//     
//     filter:function( data, filter ){
//       
// 	if( filter.charAt )
// 	    filter = { URI: filter };
// 	
// 	var filtered = [];
// 
// 	$.each(data, function(i, obj){
// 	  
// 	    for( var attr in filter )
// 		if( filter[attr] !== obj[attr] )
// 		    return( true );
// 
// 	    filtered[i] = obj;
// 	});
// 
// 	return( filtered );
//     },
// 
//     find: function( concept, filter, callback ){
// 
// 	var data = this.get( concept, filter ); 
// 
// 	if( data )
// 	    return callback( data );
// 
// 	//TODO: register callback like a weak listener
// 
// // 	$.ajax({ 
// // 	      type: 'GET',
// // 	      data: $.param( filter ),
// // 	      success: callback,  
// // 	      url: BASE_PATH + filter.URI || concept
// // 	});
// 	this.report( concept, filter );
//     },
// 
//     put: function( concept, data, filter ){
// 
// 	var beforeDiff = this.store( concept, $.extend({},data) );
// 
// 	this.report( concept, data, filter, beforeDiff );
//     },
//     
//     
//     /*var data = {
// 			startTime: $.now(),
// 			endTime: $.now() + 1800000,
// 			summary: "meu querido evento",
// 			description: "desc do evento",
// 			location: "prognus software livre",
// 			class: 1,
// 			calendar: 1,
// 			category: 1,
// 			participants: [ { 
// 					   user: { isExternal: true, mail: "user7@prognus.org", name: "user7" }
// 				      },{ 
// 					   user: "1003"
// 				      } ]
// 
// 		  };*/
//     
// 
//     merge:function( data, target ){
//       
// 	var diff = { New: {}, Update:{}, Delete: {} };
//       
// 	for( var key in data )
// 	{
// 	    if( !target[ key ] )
// 		diff.New[ key ] = target[ key ] = data[ key ];
// 
// 	    
// 	  
// 	}
//       
//     }
// 
//     store: function( concept, data, filter ){
// 
// 	if( !data.spline )
// 	    data = [ data ];
// 
// 	var target = this.storage.get( concept );
// 	
// 	var diff = { New: {}, Update:{}, Delete: {} };
// 
// 	for( var i = 0; i < data.length; i++ )
// 	{
// 	    if( data[i].URI && target[ data[i].URI ] )
// 	    {
// 		diff.Update[ data[i].URI ] = this.merge( target[ data[i].URI ], data[i] );
// 	    }
// 	    else
// 	    {
// 		diff.New[] = data[i];
// 	    }
// 	}
// 
// 	
// 
// 	this.broadcast( concept, data );
// 
// 	if( filter )
// 	    target = this.filter( target, filter );
// 
// 	if( target )
// 	    data = $.extend( target, data );
// 
// 	this.storage.set( concept, data );
// 
// // 	return;
//     },
//     
//     set: function( concept, data, filter ){
// 
//       
// 
//     },
// 
//     post: function( concept, data, filter, isNew ){
// 
// 	var callback = function(  ){ DataLayer.store( concept, data, filter ) };
// 
// 	//TODO: register callback like a weak listener
// 
// 	this.report( concept, data, filter, isNew );
//     },
//     
//     report: function( concept, filter, postData, isNew ){
//       
// 	$.ajax({ 
// 		type: postData ? isNew ? 'POST' : 'PUT' : 'GET',
// 		data: postData || $.param( filter ),
// 		success: function( data ){ DataLayer.broadcast( concept ) },
// 		url: BASE_PATH + filter.URI || concept
// 	  });
//     },
//     
//     del:function( concept, filter ){
// 
//       
// 
//     }
//     
//     broadcast: function( concept, data ){
// 
// 	
// 
//     },
// 
//     pool: function(){
//       
//     },
//
//     refresh: function(){
//       
//     }
// };

// 
// DataLayer = {
//   
//     get: function( concept, filter ){
// 
// 	var data = this.storage.get( concept );
// 
// 	if( !filter )
// 	    return( data );
// 
// 	if( filter.charAt )
// 	    filter = { URI: filter };
// 	
// 	var filtered = [];
// 
// 	$.each(data, function(i, obj){
// 	  
// 	    for( var attr in filter )
// 		if( filter[attr] !== obj[attr] )
// 		    return( true );
// 
// 	    filtered[i] = obj;
// 	});
// 
// 	return( filtered );
//     },
// 
//     find: function( concept, filter, callback ){
// 
// 	var data = this.get( concept, filter ); 
// 
// 	if( data )
// 	    return callback( data );
// 
// 	 $.ajax({ 
// 	      type: 'GET', 
// 	      data: $.param( filter ),
// 	      success: callback,  
// 	      url: filter.URI || concept
// 	});
//     },
// 
//     put: function( concept, data, filter ){
// 
// 	var target = this.get( concept, filter );
// 
// 	if( target )
// 	    data = $.extend( target, data );
//       
// 	this.storage.set( concept, data );
// 	
// 	//diff
//     },
//     
//     post: function( concept, data, filter ){
// 
// 	
// 
//     },
//     
//     pool: function(){
//       
//     },
//     
//     queue: function(){
//       
//     },
//     
//     dequeue: function(){
//       
//     },
//     
//     refresh: function(){
//       
//     }
// }

// var DataLayer = {
  
//       cache: {},
  
//       get: function( concept, location ){
	
	   /* if( location )
	    {*/
// 		var schema = $.data( this.cache, concept + ':schema' );
// 		var uri = [];
// 
// 		$.each( schema, function( i, addrs ){
// 		      uri[ uri.length ] = location[addrs];
// 		});

		/*var filter = [], result = false;

		while( !(result = $.data( this.cache, uri.join( '.' ) )) || !(uri = uri.join('.')) )
		  filter[ filter.length ] = uri.pop();
  
		if( !filter.length )
		{
		    var indexes = $.data( this.cache, uri + ':indexes' );

		    if( indexes )
			Array.prototype.concat.apply( result, indexes );
		    
		    return( result );
		}

		for( var i = 0; i < result.length; i++ )
		{
		    
		}

		if( result.length )
		    return( result );
	    }*/

// 	    var data = $.data( this.cache, concept );

// 	    if( !data )
// 		$.ajax( );

// 	    return( data );
//       },
      
//       data: function(){
// 
// 	  
// 
//       }
//       
//       search: function( concept, filter ){
// 
// 	  var schema = $.data( this.cache, concept + ':schema' );
// 	  var uri = [];
// 
// 	  $.each( schema, function( i, addrs ){
// 		uri[ uri.length ] = location[addrs];
// 	  });
//       }
//       put: function( concept, data, location ){

// 	    if( location )
// 	    {
// 		var schema = $.data( this.cache, concept + ':schema');
// 		var uri = [];

// 		$.each( schema, function( i, addrs ){
// 		      uri[ uri.length ] = location[addrs];
// 		});

// 		var result = false, filter = [];

// 		while( !(result = $.data( this.cache, uri.join('.')) )
// 		    filter[ filter.length ] = uri.pop();

// 		$.data( this.cache, '

// 	    }

// 		var model = this.storage.get( concept );
// 
// 		$.each( model, function( i, o ){
// 		    $.each( location, function( ii, attr ){
// 			 if( o[ii] === attr )
// 			    return( false );
// 		    }); 
// 		});

// 	    return $.data( this.cache, concept, data );

//       },
//       del: function( concept, location ){
// 
// 	    if( location )
// 	    {
// 		var schema = $.data( this.cache, 'concepts', concept );
// 		var uri = [];
// 
// 		$.each( schema, function( i, addrs ){
// 		      uri[ uri.length ] = location[addrs];
// 		});
// 
// 		concept = uri.join( '.' );

// 		var model = this.storage.get( concept );
// 
// 		$.each( model, function( i, o ){
// 		    $.each( location, function( ii, attr ){
// 			 if( o[ii] === attr )
// 			    return( false );
// 		    }); 
// 		});
// 	    }
// 	    
// 	
// 	    $.removeData( this.cache, concept );
//       }
// }

// internalUrl = /^([A-z0-9-_]+)(:[A-z0-9-_]+)?$/;
// internalUri = /^([a-zA-Z0-9-_]+)\(([a-zA-Z0-9-_]+)\):\/\/(.*)|([a-zA-Z0-9-_]+):\/\/(.*)$/;
// isGeneratedId = /^\d+\(javascript\)$/;
// arrayName = /^([A-z0-9-_]+)\[\]$/;
// startsDoubleDot = /^:/;
// FILE = 'files';
// // cached_urls = {};
// 
// $.ajaxPrefilter(function( options, originalOptions, jqXHR ){
// 
//       if( options.url != 'undefined' && internalUrl.test( options.url ) ){
// 
// // 	  if( !cached_urls[options.url] )
// // 	      return;
// // 	  alert( options.url + " dentro" );
// 	  jqXHR.abort(); 
// 
// 	  var callback = ( options.success || options.complete || $.noop );
// 
// 	  switch( options.type.toUpperCase() )
// 	  {
// 	    case 'GET':
// 		  return callback( DataLayer.get( options.url, /*false,*/ options.data ) );
// 
// 	    case 'POST':
// 		  return callback( DataLayer.put( options.url, options.data ) );
// 	  }
// 
// 	  //return( false );
// 
// // 	  options.url = params[1];
// // 	  options.data = ( options.data || "" ) + "&" + params[2];
//       }
// 
// });
// 
// // $("a").live("click", function( event ){
// // 
// //     event.preventDefault();
// // 
// //     $.ajax({
// // 
// // 	
// // 
// //     });
// // 
// // });
// 
// $("form").live( "submit", function( event ){
// 
//     var $this = $(this), action = $this.attr('action'), res = false,
//     
//     method = $this.attr( 'method' ),
//     
//     fileInputs = $this.find('input[type="file"]');
//     
//     if( fileInputs.length && !$this.is('[enctype="multipart/form-data"]') )
//     {
// 	event.preventDefault();
// 	
// 	var formData = $this.serializeArray(), callback = DataLayer.receive;
//       
// 	if( res = internalUrl.exec( action ) )
// 	{
// 	    var data = {}, action = res[1];
// 
// 	    data[action] = DataLayer.form( this, fileInputs );
// 
// 	    formData = DataLayer.serializeForm( data );
// 	       
// 		action = DataLayer.dispatchPath + 'post.php';
// 	    callback = $.noop;
// 	}
// 
// 	DataLayer.send( action, 
// 			[ method, 'iframe json' ], {}, 
// 			//TODO: check the type for conversion
// 			callback, 
// 			false, { 'formData': formData,  'fileInput': fileInputs, 'paramName': FILE + '[]' } );
// 
// 	return( false );
//     }
//     
//     if( res = internalUrl.exec( action ) )
//     {
// 	event.preventDefault();
// 
// 	var data = DataLayer.form( this );
// 	
// 	switch( method.toUpperCase() )
// 	{
// 	  case 'GET':
// 		DataLayer.get( res[0], data );
// 
// 	  case 'POST':
// 		DataLayer.put( res[1], data );
// 	}
// 
// 	return( false );
//     }
// 
//     return( true );
// });
// 
// this.storage = new $.store();
// 
// DataLayer = {
// 
//     links: {},
//     concepts: {},
//     listeners: {},
//     encoders: {},
//     decoders: {},
//     templates: {},
//     criterias: {},
//     tasks: [],
// 
//     render: function( templateName, data, filter, formatter, force ){
// 
// 	if( $.isFunction( filter ) )
// 	{
// 	    force = formatter;
// 	    formatter = filter;
// 	    filter = false;
// 	}
// 
// 	if( typeof data === "string" )
// 	{
// 	    data = this.get( data, filter, force ) || {};
// 	}
// 	
// 	var formatting = function( template ){
// 
// 	      if( template === false ) return( false );
// 
// 	      if( template )
// 		  DataLayer.templates[ templateName ] = new EJS({ text: template, cache: false });
// 
// 	      var html = DataLayer.templates[ templateName ].render( { data: data } );
// 
// 	      if( !formatter )
// 		  return( html );
// 
// 	      return formatter( html );
// 	}
// 
// 	if( this.templates[ templateName ] )
// 	{
// 	    return formatting();
// 	}
// 
// 	return this.send( DataLayer.templatePath + templateName, 'get', false, formatting, !!!formatter );
//     },
//     
//     send: function( url, type, data, callback, sync, extraOptions ){
//       
// 	  var result = false, fired = false;
//       
// 	  var envelope = {
// 
// 	      'async': ( typeof sync !== "undefined" ? !sync : !!callback ),
// 	      'url': url,
// 	      'success': function( dt, textStatus, jqXHR ){
// 
// 		    if( callback )
// 		    {
// 			fired = true;
// 			result = callback( dt, textStatus, jqXHR );
// 		    }
// 		    else
// 			result = dt;
// 
// 		},
// 	      'complete': function( jqXHR, textStatus ){
// 
// 		  if( !fired && callback )
// 		      result = callback( false, textStatus, jqXHR );
// 
// 	      },
// 
// 	      'type': $.isArray( type ) ? type[0] : type,
// 	      'data': data
// 
// 	    };
// 
// 	  if( $.isArray( type ) && type[1] )
// 	      envelope['dataType'] = type[1];
// 
// 	  if( extraOptions )
// 	      envelope = $.extend( envelope, extraOptions );
// 
// 	  $.ajax( envelope );
//       
// 	  return( result );
//     },
//     
//     dispatch: function( dispatcher, data, callback, isPost, dataType ){
//       
//       return this.send( this.dispatchPath + dispatcher + ".php", 
// 			[ ( isPost ? 'post' : 'get' ), dataType || 'json' ],
// 			data,
// 			callback );
// 
// //       $.ajax({
// // 	      'async': !!callback,
// // 	      'url': this.dispatchPath + dispatcher + ".php",
// // 	      'type': ( isPost ? 'post' : 'get' ),
// // 	      'dataType': 'json',
// // 	      'data': data,
// // 	      'success': function( dt, textStatus, jqXHR ){
// // 
// // 		    if( callback )
// // 		    {
// // 			fired = true;
// // 			callback( dt, textStatus, jqXHR );
// // 		    }
// // 		    else
// // 			result = dt;
// // 
// // 		},
// // 	      'complete': function( jqXHR, textStatus ){
// // 
// // 		  if( !fired && callback )
// // 		      callback( false, textStatus, jqXHR );
// // 
// // 	      }/*,
// // 	      'processData': false*/
// // 	  });
// 
//       //return( result );
//     },
// 
//     form: function( target, fileInputs ){
// 
// 	var params = {}, $this = $(target), inputArray = $this.serializeArray();
// 
// 	if( !$this.is( "form" ) )
// 	    $this = $this.parents( "form" );
// 		
// 	if( fileInputs )
// 		fileInputs.each( function( i, el ){
// 
// 	      inputArray[ inputArray.length ] = { name: $(this).prop("name"), value: FILE + i };
// 
// 		});
// 
// 	$.each( inputArray, function( i, el ){
// 
// 	    if( newName = arrayName.exec( el.name ) )
// 		el.name = newName[1];
// 	    else if( !params[ el.name ] )
// 		return( params[ el.name ] = el.value );
// 
// 	    params[ el.name ] = params[ el.name ] || [];
// 
// 	    if( $.type(params[ el.name ]) !== "array" )
// 		params[ el.name ] = [ params[ el.name ] ];
// 
// 	    params[ el.name ].push( el.value );
// 	});
// 
// // 	alert(dump(params));
// 
// 	return this.decode( $this.attr( "action" ), params );
//     },
// 	
// 	serializeForm: function( data, level ){
// 	
// 		var formData = [];
// 	
// 		for( key in data )
// 		{
// 			var value = data[key];
// 
// 			if( level !== undefined )
// 				key = level+'['+key+']';
// 
// 			if( $.isArray(value) || $.isPlainObject(value) )
// 				formData = formData.concat( this.serializeForm( value, key ) );
// 			else
// 				formData[ formData.length ] = { name: key, value: value };
// 		}
// 		
// 		return( formData );
// 	},
// 
//     blend: function( action, data ){
// 
// // 	if( notArray = (!$.isArray(data)) )
// // 	    data = [ data ];
// 
// 	var form = $('form[action="'+action+'"]');
// 
// 	form.get(0).reset();
// 
// 	var named = form.find( 'input[name]' );
// 
// 	for( var name in data )
// 	{
// 	    named.filter( '[name="'+name+'"]' ).val( data[name] );
// 	}
//     },
// 
//  
//     
//     put: function( concept, filter, data, oneSide ){
//       
//       ///////////////////////////// normalize ////////////////////////////////
// 	if( arguments.length == 2 )
// 	{
// 	    data = filter;
// 	    filter = false;
// 	}
// 	if( typeof data === "undefined" ||
// 	    $.type(data) === "boolean" )
// 	{
// 	    oneSide = data;
// 	    data = filter;
// 	    filter = false;
// 	}
// 	
// 	if( !concept || !data )
// 	    return( false );
// 
// 	var decoder = "", id = false, bothSides = (typeof oneSide === "undefined"), notArray, res;
// 	
// 	if( $.type(filter) === "string" )
// 	{
// 	    id = filter;
// 	    filter = false;
// 	}
// 
// 	if( id )
// 	    data.id = id;
// 
// 	if( notArray = ( $.type( data ) !== "array" ) )
// 	    data = [ data ];
// 
// 	if( res = internalUrl.exec( concept ) )
// 	{
// 	    //TODO: verificar se a decodificaçao deve ser feita em cada item do array
// 	    data = this.decode( concept, data );
// 	    concept = res[1];
// 	    decoder = res[2];
// 	}
// 
//       ////////////////////////////////////////////////////////////////////////
// 
// 	if( bothSides || !oneSide )
// 	{
// 	    var result = false, links = this.links( concept ), 
// 	    current = this.check( concept ) || {}, ids = [];
// 
// 	    for( var i = 0; i < data.length; i++ )
// 	    {
// 		var key = ids[ ids.length ] = data[i].id || this.generateId( concept ), updateSet = {};
// 
// 		////////////////////////////// linkage /////////////////////////////////    
// 		for( var link in links )
// 		{
// 		    if( data[i][link] )
// 		    {
// 			var isConcept = false;
// 		      
// 			if( isConcept = this.isConcept( concept, link ) )
// 			    data[i][link] = [ data[i][link] ];
// 
// 			var _this = this;
// 
// 			$.each( data[i][link], function( ii, el ){
// 
// 				var isRef = false;
// 
// 				if( isRef = ($.type(el) === "string") )
// 				    el = { id: el };
// 
// 				var nestedLinks = _this.links( links[link], true );
// 				//removido pois o mesmo esta gerando inconsistencia em tudo
// 				//if( DataLayer.isConcept( links[link], nestedLinks[concept] ) )
// 				if( isConcept )
// 				{
// 				    el[ nestedLinks[link] ] = el[ nestedLinks[link] ] || [];
// 				    el[ nestedLinks[link] ].push( key );
// 				}
// 				else
// 				    el[ nestedLinks[link] ] = key;
// 
// 				if( isRef && ( !current[ key ] || !current[ key ][ link ] || 
// 				               (isConcept ? current[ key ][ link ] !== el.id : !$.inArray( el.id, current[ key ][ link ] )) ) )
// 				{
// 				    updateSet[ links[link] ] = updateSet[ links[link] ] || [];
// 				    updateSet[ links[link] ].push( el );
// 				}
// 				else if( !isRef )
// 				    data[i][link][ii] = _this.put( links[link], el, oneSide );
// 			    });
// 
// 			if( isConcept )
// 			    data[i][link] = data[i][link][0];
// 		    }
// 		}
// 		//////////////////////////////////////////////////////////////////////////
// 
// 		if( data[i].id )
// 		    data[i] = this.merge( current[ data[i].id ], data[i] );
// 
// 		 current[ key ] = data[i];
// 
// 		if( bothSides )
// 		  this.report( concept, key, data[i] );
// 	    }
// 
// 	    this.store( concept, current );
// 
// 	    for( var setKey in updateSet )
// 	    {
// 		if( bothSides )
// 		    for( var i = 0; i < updateSet[ setKey ].length; i++ )
// 		      this.report( setKey, updateSet[ setKey ][i].id, updateSet[ setKey ][i] );
// 		    
// 		DataLayer.put( setKey, updateSet[ setKey ], false );
// 	    }
// 	}
// 
// 	if( oneSide ) 
// 	    this.commit( concept, ids/*, true */);
// 
// 	this.broadcast( concept, oneSide ? 'server' : bothSides ? 'serverclient' : 'client', true );
// 
// 	return( notArray ? ids[0] : ids );
// 
//     },
//     
//     remove: function( concept, id, oneSide ){
//       
// 	var bothSides = (typeof oneSide === "undefined"),
// 
// 	links = this.links( concept ), ids = [],
// 
// 	current = this.check( concept, id );
// 
// 	if( !current ) return;
// 	
// 	if( id )
// 	    current.id = id;
// 
// 	if( notArray = ( $.type( current ) !== "array" ) )
// 	    current = [ current ];
// 
// 	for( var i = 0; i < current.length; i++ )
// 	{
// 	    var currentId = ids[ ids.length ] = current[i].id;
// 
// 	    if( bothSides )
// 	      this.report( concept, currentId, false );
// 
// 	    if( bothSides || !oneSide )
// 	      this.del( concept, currentId );
// 
// 	    for( var link in links )
// 	    {
// 		if( !current[i][link] )
// 		    continue;
// 
// 		var nestedLinks = this.links( links[link], true );
// 
// 		if( isConcept = this.isConcept( concept, link ) )
// 		    current[i][link] = [ current[i][link] ];
// 
// 		$.each( current[i][link], function( ii, el ){
// 
// 			el = DataLayer.storage.cache[links[link]][el];
// 
// 			if( notArrayNested = ( $.type( el[ nestedLinks[link] ] ) !== "array" ) )
// 			    el[ nestedLinks[link] ] = [ el[nestedLinks[link]] ];
// 
// 			el[ nestedLinks[link] ] = $.grep( el[ nestedLinks[link] ], function( nested, iii ){
// 			    return ( currentId !== nested );
// 			});
// 
// 			if( notArrayNested )
// 			    el[ nestedLinks[link] ] = el[ nestedLinks[link] ][0] || false;
// 			if(!el[ nestedLinks[link] ] || !el[ nestedLinks[link] ].length)
// 				delete el[ nestedLinks[link] ];
// 		});
// 	    }
// 	}
// 
// 	if( oneSide )
// 	    this.commit( concept, ids );
// 
// 	this.broadcast( concept, oneSide ? 'server' : bothSides ? 'serverclient' : 'client', false );
//     },
//     
//     report: function( concept, id, data )
//     {      
// 	var current = this.check( ':current', concept ) || {};
// 
// 	if( !current[ id ] )
// 	    current[ id ] = this.check( concept, id ) || {};
// 	
// 	this.store( ':current', concept, current );
// 
// 	var diff = this.diff( current[ id ], data );
// 
// 	var diffs = this.check( ':diff', concept ) || {};
// 
// 	if( diffs[ id ] )
// 	    diff = this.merge( diffs[ id ], diff );
// 
// 	if( !diff || !$.isEmptyObject( diff ) )
// 	    diffs[ id ] = diff;
// 
// 	this.store( ':diff', concept, diffs );
//     },
// 
// //     enqueue: function( queueName, concept, id, data ){
// // 
// // 	var queue = this.check( ':' + queueName, concept ) || {};
// // 
// // 
// //     },
// //     
// //     dequeue: function( queueName, concept, id ){
// // 
// // 	
// // 
// //     },
//     
//     
//     
//     rollback: function( concept, ids ){
//       
// 	var queue = this.prepareQ( 'current', concept, ids );
// 
// 	ids = [];
// 
// 	for( var id in queue )
// 	{
// 	     this.put( concept, id, queue[id], false );
// 
// 	     ids[ ids.length ] = id;
// 	}
// 
// 	this.clearQ( concept, ( ids.length ? ids : false ) );
// 
// 	this.broadcast( concept, 'revert' );
//       
//     },
//     
//     prepareQ: function( queueName, concept, ids ){
//       
//       var notArray = false;
//       
//       if( notArray = ($.type(concept) !== "array") )
// 	  concept = [ concept ];
//       
//       var q = {};
//       
//       for( var i = 0; i < concept.length; i++ )
//       {
// 	  var queue = this.check( ':' + queueName, concept[i] || false );
// 	  
// 	  if( !queue ) continue;
// 
// 	  if( ids )
// 	  {
// 	      if( $.type(ids) !== "array" )
// 		  ids = [ ids ];
// 
// 	      var filtered = {};
// 
// 	      for( var ii = 0; ii < ids.length; ii++ )
// 	      {
// 		  filtered[ ids[ii] ] = queue[ ids[ii] ];
// 	      }
// 
// 	      queue = filtered;
// 	  }
// 
// 	  q[ concept[i] ] = queue;
//       }
//       
//       return( notArray ? q[ concept[0] ] : q );
//     },
//     
//     clearQ: function( concept, ids ){
//       
//       	var current = this.check( ':current', concept || false );
// 	var diffs = this.check( ':diff', concept || false );
// 
// 	if( !ids )
// 	    current = diffs = {};
// 	else
// 	{
// 	    if( notArray = ($.type(ids) !== "array") )
// 	      ids = [ ids ];
// 
// 	    for( var i = 0; i < ids.length; i++ )
// 	    {
//  		delete current[ ids[i] ];
// 		delete diffs[ ids[i] ];
// 	    }
// 	}
// 
//  	this.store( ':current', concept, current );
// 	this.store( ':diff', concept, diffs );
//     },
// 
//     commit: function( concept, ids, callback ){
//       
// 	var queue = this.prepareQ( 'diff', concept, ids );
// 
// 	this.sync( queue, !$.isArray(concept) && concept || false, callback );
//     },
//     
//     sync: function( queue, concept, callback ){
// 
// 	if( !queue || $.isEmptyObject( queue ) )
// 	    return;
// 
// 	if( concept )
// 	{
// 	  var helper = {}; 
// 	  helper[concept] = queue; 
// 	  queue = helper;
// 	}
// 
// 	var data = {}, URIs = {};
// 
// 	for( var concept in queue )
// 	    for( var id in queue[concept] )
// 	    {
// 		data[ this.URI( concept, id ) ] = queue[concept][id];
// 		URIs[ this.URI( concept, id ) ] = { concept: concept, id: id };
// 	    }
// 
// 	if( $.isEmptyObject( data ) )
// 	    return;
// 
// 	this.dispatch( "Sync", data, function( data, status, jqXHR ){
// 
// // 	    switch( status )
// // 	    {
// // 	      case "error":
// // 	      case "parsererror":
// // 		return DataLayer.rollback( concept, URI );
// // 	      case "success":
// // 		return DataLayer.commit();
// // 	      case "timeout":
// // 	      case "notmodified":
// // 	    }
// 
// 	    var received = DataLayer.receive( data );
// 
// 	    for( var URI in URIs )
// 		if( typeof received[URI] !== "undefined" )
// 		    DataLayer.clearQ( URIs[URI].concept, URIs[URI].id );
// 
// 	    if( callback )
// 		callback( received );
// 
// // 	    for( var URI in data )
// // 	    { 
// // 		var parsed = DataLayer.parseURI( URI ),
// //    
// // 		concept = parsed[1], id = parsed[3];
// // 
// // 		if( $.type(data[URI]) === "string" )
// // 		{
// // 		  //TODO:threat the exception thrown
// // 		  DataLayer.rollback( concept, id );
// // 		  delete URIs[ URI ];
// // 		  continue;
// // 		}
// // 
// // 		if( data[URI] === false ){
// // 		  DataLayer.remove( concept, id, false );
// // 		  continue;
// // 		}
// // 
// // 		if( id !== data[URI].id )
// // 		  DataLayer.move( concept, id, data[URI].id );
// // 		
// // 		DataLayer.put( concept, id, data[URI], false );
// // 	    }
// // 	    
// // 	    for( var URI in URIs )
// // 		 DataLayer.clearQ( URIs[URI].concept, URIs[URI].id );
// // 	    
// // 	    if( callback )
// // 		callback();
// 
// 	}, true );
// 
//     },
//     
//     receive: function( data ){
//       
// 	var received = {};
// 	
// 	    for( var URI in data )
// 	    { 
// 		var parsed = DataLayer.parseURI( URI ),
//    
// 	    concept = parsed[4], id = parsed[5];
// 
// 	    received[ URI ] = data[ URI ];
// 
// 		if( $.type(data[URI]) === "string" )
// 		{
// 		  //TODO:threat the exception thrown
// 		  DataLayer.rollback( concept, id );
// 		  continue;
// 		}
// 
// 		if( data[URI] === false ){
// 		  DataLayer.remove( concept, id, false );
// 		  continue;
// 		}
// 
// 		if( id !== data[URI].id )
// 		  DataLayer.move( concept, id, data[URI].id );
// 		
// 		DataLayer.put( concept, id, data[URI], false );
// 	    }
// 	    
// 	return( received );
// 	    
//     },
//     
//     unique: function( origArr ){ 
// 
// 	var newArr = [];
//       
// 	for ( var x = 0; x < origArr.length; x++ )
// 	{
// 		var found = false;
// 	    for ( var y = 0; !found && y < newArr.length; y++ ) 
// 		if ( origArr[x] === newArr[y] )  
// 		  found = true;
// 
// 	    if ( !found ) 
// 		newArr[ newArr.length ] = origArr[x];
// 	}
// 
// 	return newArr;
//     },
// 
//     merge: function( current, data ){
//       
// 	return this.copy(  data, current );
// 
// // 	return $.extend( current, data );
// 
//     },
//     
//     // clone objects, skip other types.
//     clone: function(target) {
// 	    if ( typeof target == 'object' ) {
// 		    Clone.prototype = target;
// 		    return new Clone();
// 	    } else {
// 		    return target;
// 	    }
//     },
//       
//     // Shallow Copy 
//     shallowCopy: function(target) {
// 	    if (typeof target !== 'object' ) {
// 		    return target;  // non-object have value sematics, so target is already a copy.
// 	    } else {
// 		    var value = target.valueOf();
// 		    if (target != value) { 
// 			    // the object is a standard object wrapper for a native type, say String.
// 			    // we can make a copy by instantiating a new object around the value.
// 			    return new target.constructor(value);
// 		    } else {
// 			    // ok, we have a normal object. If possible, we'll clone the original's prototype 
// 			    // (not the original) to get an empty object with the same prototype chain as
// 			    // the original.  If just copy the instance properties.  Otherwise, we have to 
// 			    // copy the whole thing, property-by-property.
// 			    if ( target instanceof target.constructor && target.constructor !== Object ) { 
// 				    var c = clone(target.constructor.prototype);
//       
// 				    // give the copy all the instance properties of target.  It has the same
// 				    // prototype as target, so inherited properties are already there.
// 				    for ( var property in target) { 
// 					    if (target.hasOwnProperty(property)) {
// 						    c[property] = target[property];
// 					    } 
// 				    }
// 			    } else {
// 				    var c = {};
// 				    for ( var property in target ) c[property] = target[property];
// 			    }
// 			    
// 			    return c;
// 		    }
// 	    }
//     },
// 
//     // entry point for deep copy.
//     // source is the object to be deep copied.
//     // depth is an optional recursion limit. Defaults to 256.
//     // deep copy handles the simple cases itself: non-objects and object's we've seen before.
//     // For complex cases, it first identifies an appropriate DeepCopier, then delegate the details of copying the object to him.
//     copy: function(source, result, depth) {
//       
// 	    // null is a special case: it's the only value of type 'object' without properties.
// 	    if ( source === null ) return null;
// 
// 	    // All non-objects use value semantics and don't need explict copying.
// 	    if ( typeof source !== 'object' ) return source;
// 
// 	    if( !depth || !(depth instanceof RecursionHelper) ) depth = new RecursionHelper(depth);
// 
// 	    var cachedResult = depth.getCachedResult(source);
// 
// 	    // we've already seen this object during this deep copy operation
// 	    // so can immediately return the result.  This preserves the cyclic
// 	    // reference structure and protects us from infinite recursion.
// 	    if ( cachedResult ) return cachedResult;
// 
// 	    // objects may need special handling depending on their class.  There is
// 	    // a class of handlers call "DeepCopiers"  that know how to copy certain
// 	    // objects.  There is also a final, generic deep copier that can handle any object.
// 	    for ( var i=0; i<this.comparators.length; i++ ) {
// 
// 		    var comparator = this.comparators[i];
// 
// 		    if ( comparator.can(source) ) {
// 	
// 			    // once we've identified which DeepCopier to use, we need to call it in a very
// 			    // particular order: create, cache, populate.  This is the key to detecting cycles.
// 			    // We also keep track of recursion depth when calling the potentially recursive
// 			    // populate(): this is a fail-fast to prevent an infinite loop from consuming all
// 			    // available memory and crashing or slowing down the browser.
//       
// 			    if( !result )
// 				// Start by creating a stub object that represents the copy.
// 				result = comparator.create(source);
// 			    else if( !comparator.can(result) )
// 				throw new Error("can't compare diferent kind of objects.");
// 
// 			    // we now know the deep copy of source should always be result, so if we encounter
// 			    // source again during this deep copy we can immediately use result instead of
// 			    // descending into it recursively.  
// 			    depth.cacheResult(source, result);
// 
// 			    // only DeepCopier.populate() can recursively deep copy.  So, to keep track
// 			    // of recursion depth, we increment this shared counter before calling it,
// 			    // and decrement it afterwards.
// 			    depth.depth++;
// 			    if ( depth.depth > depth.maxDepth ) {
// 				    throw new Error("Exceeded max recursion depth in deep copy.");
// 			    }
// 
// 			    // It's now safe to let the comparator recursively deep copy its properties.
// 			    var returned = comparator.populate( function(source, result) { return DataLayer.copy(source, result, depth); }, source, result );
// 	
// 				if(returned)
// 					result = returned;
// 
// 			    depth.depth--;
// 
// 			    return result;
// 		    }
// 	    }
// 	    // the generic copier can handle anything, so we should never reach this line.
// 	    throw new Error("no DeepCopier is able to copy " + source);
//     },
// 
//     // publicly expose the list of deepCopiers.
//     comparators: [],
// 
//     // make deep copy() extensible by allowing others to 
//     // register their own custom Comparators.
//     registerComparator: function(comparatorOptions) {
// 
// 	  // publicly expose the Comparator class.
// 	  var comparator = {
// 
// 	      // determines if this Comparator can handle the given object.
// 	      can: function(source) { return false; },
//     
// 	      // starts the deep copying process by creating the copy object.  You
// 	      // can initialize any properties you want, but you can't call recursively
// 	      // into the copy().
// 	      create: function(source) { },
// 
// 	      // Completes the deep copy of the source object by populating any properties
// 	      // that need to be recursively deep copied.  You can do this by using the
// 	      // provided deepCopyAlgorithm instance's copy() method.  This will handle
// 	      // cyclic references for objects already deepCopied, including the source object
// 	      // itself.  The "result" passed in is the object returned from create().
// 	      populate: function(deepCopyAlgorithm, source, result) {}
// 	  };
// 
// 	  for ( var key in comparatorOptions ) comparator[key] = comparatorOptions[key];
// 
// 	  this.comparators.unshift( comparator );
//     },
//  
//     diff: function( base, toDiff ){
// 
// 	if( typeof base === 'undefined' || $.isEmptyObject(base) )
// 	    return( toDiff );
// 
// 	if( toDiff === false )
// 	    return( false );
// 
// 	toDiff = $.extend( {}, toDiff );
// 
// 	for( var key in toDiff )
// 	{
// 	    switch( $.type(toDiff[key]) )
// 	    {
// 	      case 'object': 
// 		if( $.isEmptyObject(toDiff[key] = this.diff( base[key], toDiff[key] )) )
// 		  delete toDiff[key];
// 	      break;
// 	      case 'array':
// 		if( base[key] && !(toDiff[key] = $.grep( toDiff[key], function( el, i ){ return( $.inArray( el, base[key] ) === -1 ); } )).length )
// 		  delete toDiff[key];
// 	      break;
// 	      default:
// 		if( base[key] == toDiff[key] )
// 		  delete toDiff[key];
// 	    }
// 	}
// 
// 	return( toDiff );
// 
//     },
//     
//     links: function( concept, reverse ){
// 
// 	if( !this.links[ concept ] )
// 	{
// 	    var result = this.dispatch( "links", { concept: concept } ) || false;
// 
// 	    if( !result )
// 		return( false );
// 
// 	    this.concepts[ concept ] = $.extend( this.concepts[ concept ] || {}, 
// 						 result['concepts'] || {} );
// 
// 	    this.links[ concept ] =  result['links'] || {};
// 	    this.nestedLinks[ concept ] = result['nestedLinks'] || {};
// 	}
// 
// 	if( reverse )
// 	{
// 	    return( this.nestedLinks[ concept ] );
// // 	    var reverted = {}, llinks = this.links[ concept ];
// //     
// // 	    for( var key in llinks )
// // 		reverted[ llinks[key] ] = key;
// // 
// // 	    return( reverted );
// 	}
// 
// 	return( this.links[ concept ] );
// 
//     },
//     
//     isConcept: function( concept, attr ){
//       
// 	if( typeof this.concepts[concept] === "undefined" )
// 	{
// 	    this.links( concept );
// 	}
// 
// 	return !!this.concepts[ concept ][ attr ];
//     },
//     
//     URI: function( concept, URI, context ){
//       
// 	if( res = internalUrl.exec( concept ) )
// 	    concept = res[1];
// 	
// 	context = context ? "(" + context + ")" : "";
//       
// 	if( URI )
// 	    return( concept + context + "://" + URI );
// 	else
// 	    return( concept );
//       
//     },
//     
//     parseURI: function( URI ){
// 
// 	return internalUri.exec( URI ) || false;
// 
//     },
//     
//     
//    
//     
//     generateId: function( concept ){
//       
// 	var newId = this.counter + "(javascript)";
//       
// 	this.store( ":counter", (this.counter++) + "" );
// 	
// 	return( newId );
//     },
//    
// 
//    
// 
//     get: function( concept, /*URI, */filter, oneSide ){
// 
// 	///////////////////////////// normalize ////////////////////////////////
// 	if( arguments.length == 2 && $.type(filter) === "boolean" )
// 	{
// 	    oneSide = filter;
// 	    filter = false;
// 	}
// 	
// 	var encoder = false, id = false, bothSides = (typeof oneSide === 'undefined'), res;
// 	
// 	if( $.type(filter) === "string" )
// 	{
// 	    id = filter;
// 	    filter = false;
// 	}
// 
// 	filter = filter || false;
// 
// 	if( !concept )
// 	    return( false );
// 
// 	if( res = internalUrl.exec( concept ) )
// 	{
// 	    encoder = concept;
// 	    concept = res[1];
// 
// 	    if( filter )
// 		filter = this.criteria( encoder, filter );
// 	}
// 	
// 	if ( $.type(filter) === "array" )
// 	{
// 	    filter = { filter: filter, criteria: false };
// 	}
// 	
// 	//////////////////////////////////////////////////////////////////////////
// 	
// 	var result = false;
// 
// 	if( bothSides || !oneSide )
// 	    result = this.check( concept, id || filter );
// 
// 	if( !result && (bothSides || oneSide) )
// 	{
// 	    result = this.request( concept, id || filter.filter, filter.criteria );
// 
// 	    if( result && bothSides && (!filter || 
// 					!filter.criteria || 
// 					!filter.criteria.format) )
// 	    {
// 	      var newResult = [];
// 	    
// 	      for( var i = 0; i < result.length; i++ )
// 		  newResult[i] = $.extend( {}, result[i] );
// 
// 	      this.put( concept, id, newResult, false );
// 	    }
// 	}
// 
// 	if( /*result &&*/ encoder )
// 	    result = this.encode( encoder, result, filter ); //TODO: retirar o filtro no método encode
// 
// 	return( result );
//     },
//     
//     filter: function( base, filter, criteria ){
//       
// 	var filtered = [];
//       
// 	for( var key in base )
// 	{
// // 	    if( !noGroup )
// // 		for( var i = 0, current = original; i < filter.length && ( current === original ); i++ )
// // 		    current = this.compare( operator, current, this.compare( base[key], filter[i] ) );
// 
// 	    if( this.storage.filter( base[key], filter ) )
// 		filtered[ filtered.length ] = key;
// 	}
// 
// 	return( filtered );
//     },
//     
//     compare: function( operator, base, test ){
//       
//       switch( operator )
//       {
// 	  case '*':  return RegExp( ".*" + test + ".*" ).test( base );
// 	  case '^':  return RegExp( "^" + test +  ".*" ).test( base );
// 	  case '$':  return RegExp( ".*"  + test + "$" ).test( base );
// 
// 	  case '&':  return ( base && test );
// 	  case '|':  return ( base || test );
// 
// 	  case '=':  return ( base == test );
// 	  case '<=': return ( base <= test );
// 	  case '>=': return ( base >= test );
// 	  case '>':  return ( base <  test );
// 	  case '<':  return ( base >  test );
//       }
//       
//     },
//     
// //     clone: function( object ){
// // 
// // 	new { prototype: object };
// // 
// //     },
// 
//     check: function( namespace, keys ){
// 
// 	if( !namespace )
// 	    return( false );
// 
// 	var result = this.storage.get( namespace );
// 
// 	if( !keys || !result )
// 	  return( result || false );
// 
// 	if( notArray = $.type(keys) === "string" )
// 	    keys = [ keys ];
// 	else if( $.type(keys) !== "array" )
// 	    keys = this.filter( result, keys.filter, keys.criteria );
// 
// 	var res = [];
// 
// 	for( var i = 0; i < keys.length; i++ )
// 	    res[ res.length ] = result[keys[i]];
// 
// 	return( notArray ? res[0] || false : res.length ? res : false );
//     },
// 
//     storage: {
//       
// 	cache: {},
//       
// 	set: function( key, value ){
// 
// 	    this.cache[key] = value;
// 
// 	},
// 	get: function( key ){
// 
// 	    return DataLayer.copy( this.cache[key] );
// 
// 	},
// 	del: function( key ){
// 
// 	    delete this.cache[key];
// 
// 	},
// 	
// 	filter: function( base, filter ){
//       
// 	    var bool, op = filter.shift();
// 
// 	    switch( op )
// 	    {
// 		case 'IN':
// 		  for( var i = 0, f = []; i < filter[1].length || !(filter = f); i++ )
// 		      f[i] = [ '=', filter[0], filter[1][i] ];
// 		case 'OR':
// 		    op = '|';
// 		    bool = false;
// 		break;
// 		case 'AND': 
// 		    op = '&';
// 		    bool = true;
// 		break;
// 		default : return DataLayer.compare( op, base[ filter[0] ], filter[1] );
// 	    }
// 	    
// 	    for( var strict = bool; 
// 
// 		filter.length && ( strict ? bool : !bool ); 
// 	    
// 		bool = DataLayer.compare( op, bool, this.filter( base, filter.shift() ) ) );
// 
// 	    return( bool );
// 	}
//     },
// 
//     flush: function(){
// 
//     },
//     
//     restore: function(){
//       
//     },
// 
//     store: function( namespace, key, data ){
// 
// 	if( !data )
// 	  return this.storage.set( namespace, key );
// 
// 	var res = this.check( namespace ) || {};
// 
// 	res[key] = data;
// 
// 	return this.storage.set( namespace, res );
//     },
// 
//     del: function( namespace, key ){
//       
// 	if( !key )
// 	  return this.storage.del( namespace );
// 
// 	var res = this.check( namespace ) || {};
// 
// 	delete res[key];
// 
// 	return this.storage.set( namespace, res );
//       
//     },
//     
//      move: function( concept, oldId, newId ){
// 
// 	this.put( concept, newId, this.check( concept, oldId ), false );
// 
// 	this.remove( concept, oldId, false );
//     },
//     
// 
//     
//     
//     
//     request: function( concept, filter, criteria ){
// 
//       var id = false, criteria = criteria || {};
// 
//       if( $.type(filter) === "string" )
//       {
// 	  id = filter;
// 	  filter = false;
//       }
// 
//       return this.dispatch( "request", { 
// 
// 	  concept: concept || '',
// 	  id: id || '',
// 	  filter: filter || '',
// 	  criteria: criteria || '',
// 	  service: criteria.service || '',
// 	  properties: criteria.properties || ''
// 
//       } );
//     },
// 
//     
//     //         sync: function( data, callback ){
// // 
// // 	if( !data || $.isEmptyObject( data ) )
// // 	    return;
// //       
// // 	this.send( "Sync", data, function( data, status, jqXHR ){
// // 
// // // 	    switch( status )
// // // 	    {
// // // 	      case "error":
// // // 	      case "parsererror":
// // // 		return DataLayer.rollback( concept, URI );
// // // 	      case "success":
// // // 		return DataLayer.commit();
// // // 	      case "timeout":
// // // 	      case "notmodified":
// // // 	    }
// // 
// // 	    if( callback )
// // 	    {
// // 		var result = callback( data, status, jqXHR );
// // 
// // 		if( result === false )
// // 		    return;
// // 		else if( typeof result != "undefined" )
// // 		    data = result;
// // 	    }
// // 
// // 	    for( var URI in data )
// // 	    { 
// // 		var parsed = DataLayer.parseURI( URI ), 
// //    
// // 		concept = parsed[1], /*URI = parsed[3],*/
// // 
// // 		links = DataLayer.links( concept );
// // 
// // 		for( var linkName in links )
// // 		{
// // 		    var subURI = data[URI][linkName];
// // 
// // 		    if( subURI && data[subURI] )
// // 		    {
// // 			data[URI][linkName] = DataLayer.put( linkName, subURI, data[subURI], false );
// // 
// // 			delete( data[subURI] );
// // 		    }
// // 		}
// // 
// // 		DataLayer.put( concept, URI, data[URI], false );
// // 	    }
// // 	}, true );
// // 
// //     },
// 
// //     report: function( concept, URI, data, sync )
// //     {
// // 	var current = this.dequeue( 'current', concept, URI );
// // 
// // 	if( !current )
// // 	    this.enqueue( 'current', concept, URI, ( current = this.check( concept, URI ) || {} ) );
// // 
// // 	var diff = this.diff( current, data );
// // 
// // 	if( !diff )
// // 	    this.dequeue( 'current', concept, URI, true );
// // 	else
// // 	    this.enqueue( 'diff', concept, URI, diff );
// // 	
// // 	if( sync )
// // 	    this.commit( concept, URI, function(){ 
// // 
// // 		DataLayer.set( concept, URI, data, false );
// // 
// // 	    });
// //     },
//     
// //     enqueue: function( type, concept, URI, obj ){
// //       
// // 	//var newURI = this.URI( concept, URI );
// // 	
// // 	if( !this.queue[type] )
// // 	    this.queue[type] = {};
// // 
// // 	if( !this.queue['all'] )
// // 	    this.queue['all'] = {};
// // 	
// // 	if( !this.queue[type][concept] )
// // 	    this.queue[type][concept] = {};
// // 	
// // 	if( !this.queue['all'][type] )
// // 	    this.queue['all'][type] = {};
// // 	
// // 	if( !this.queue['all'][type][/*new*/URI] )
// // 	    this.queue[type][concept][URI] = this.queue['all'][type][/*new*/URI] = obj;
// // 
// // 	this.store( ':queue', this.queue );
// //     },
// //     
// //     dequeue: function( type, concept, URI, remove ){
// //       
// //       ///////////////////////////// normalize ////////////////////////////////
// // 	if( arguments.length < 4 && $.type(URI) === 'boolean' )
// // 	{
// // 	    remove = URI;
// // 	    URI = false;
// // 	}
// // 	if( arguments.length < 3 && $.type(concept) === 'boolean' )
// // 	{
// // 	    remove = concept;
// // 	    concept = false;
// // 	}
// //       //////////////////////////////////////////////////////////////////////////
// //       
// // 	if( !this.queue[type] || !this.queue['all'] )
// // 	    return( false );
// // 	
// // 	if( !concept )
// // 	{
// // 	    var obj = this.queue['all'][type];
// // 	    
// // 	    if( remove )
// // 	    {
// // 		delete this.queue['all'][type];
// // 		delete this.queue[type];
// // 	    }
// // 
// // 	    this.store( ':queue', this.queue );
// // 	    return( obj );
// // 	}
// // 
// // 	if( !this.queue[type][concept] )
// // 	    return( false );
// // 	
// // 	if( !URI )
// // 	{
// // 	    var obj = this.queue[type][concept];
// // 
// // 	    if( remove )
// // 	    {
// // 		var URIs = this.queue[type][concept];
// // 
// // 		for( var subURI in URIs )
// // 		     delete this.queue['all'][type][subURI];
// // 
// // 		delete this.queue[type][concept];
// // 	    }
// // 
// // 	    this.store( ':queue', this.queue );
// // 	    return( obj );
// // 	}
// // 
// // // 	var newURI = URI ? this.URI( concept, URI ) : concept;
// // 	
// // 	var obj = this.queue['all'][type][/*new*/URI];
// //   
// // 	if( remove )
// // 	{
// // 	    delete this.queue['all'][type][/*new*/URI];
// // 	    delete this.queue[type][concept][URI];
// // 	}
// // 
// // 	this.store( ':queue', this.queue );
// // 	return( obj );
// //     },
//     
//            //TODO: definir a 'usage' desta função e refatora-la
// //     set: function( concept, filter, data, oneSide ){
// // 
// // 	///////////////////////////// normalize ////////////////////////////////
// // 	if( arguments.length == 2 )
// // 	{
// // 	    data = filter;
// // 	    filter = false;
// // 	}
// // 	if( $.type(data) === "boolean" )
// // 	{
// // 	    oneSide = data;
// // 	    data = filter;
// // 	    filter = false;
// // 	}
// // 	
// // 	if( !concept || !data )
// // 	    return( false );
// // 
// // 	var decoder = "", URI = false, bothSides = (typeof oneSide === "undefined");
// // 	
// // 	if( $.type(filter) === "string" )
// // 	{
// // 	    URI = filter;
// // 	    filter = false;
// // 	}
// // 
// // 	if( res = internalUrl.exec( concept ) )
// // 	{
// // 	    //TODO: verificar se a decodificaçao deve ser feita em cada item do array
// // 	    data = this.decode( concept, data );
// // 	    concept = res[1];
// // 	    decoder = res[2];
// // 	}
// // 	///////////////////////////////////////////////////////////////////////////
// // 
// // 	if( bothSides || oneSide )
// // 	    this.report( concept, URI, data, !bothSides );
// // 
// // 	if( bothSides || !oneSide )
// // 	{
// // 	    if( URI )
// // 	    {
// // 	      var helper = {}; 
// // 	      helper[URI] = data; 
// // 	      data = helper;
// // 	    }
// // 
// // 	    for( var URI in data )
// // 	    {
// // 		var current = this.check( concept, URI ) || {};
// // 
// // 		data[URI] = this.merge( current, data[URI] );
// // 
// // 		this.store( concept, URI, data[URI] );
// // 	    }
// // 
// // 	}
// // 
// // 	this.broadcast( concept, oneSide ? 'client' : 'server' );
// // 
// // 	return( true );
// //     },
// //     put: function( concept, URI, data, oneSide ){
// //       
// //       ///////////////////////////// normalize ////////////////////////////////
// // 	if( $.type(URI) !== "string" && arguments.length < 4 )
// // 	{
// // 	    oneSide = data;
// // 	    data = URI;
// // 	    URI = false;
// // 	}
// //       ////////////////////////////////////////////////////////////////////////
// //       
// //       ////////////////////////////// linkage /////////////////////////////////
// // 	var result = false, links = this.links( concept );
// // 
// // 	for( var link in links )
// // 	{
// // 	    if( data[link] )
// // 	    {
// // 		if( $.isArray( data[link] ) )
// // 		{
// // 		    data[link] = this.put( links[link], data[link].URI, data[link], oneSide );
// // 		}
// // 		else if( $.isObject( data[link] ) )
// // 		{
// // 		    $.each( data[link], function( i, el ){
// // 
// // 			  data[link][i] = this.put( links[link], el.URI, el, oneSide );
// // 
// // 		    });
// // 		}
// // 	    }
// // 	}
// //       //////////////////////////////////////////////////////////////////////////
// //     
// // 	if( typeof data.URI === "undefined" )
// // 	{
// // 	    URI = this.add( concept, data, oneSide );
// // 	}
// // 	else if( data.URI === false )
// // 	{
// // 	    status = this.remove( concept, URI, oneSide );
// // 	}
// // 	else
// // 	{
// // 	    status = this.set( concept, URI, data, oneSide );
// // 	}
// // 
// // 	if( URI && data.URI && URI !== data.URI )
// // 	    this.move( concept, URI, data.URI );
// // 
// // 	return( data.URI || URI );
// // 
// //     },
//     
//     //     add: function( concept, data, oneSide ){
// //       
// //       ///////////////////////////// normalize ////////////////////////////////
// // 	if( !concept || !data )
// // 	    return( false );
// // 
// // 	if( res = internalUrl.exec( concept ) )
// // 	{
// // 	    //TODO: verificar se a decodificaï¿
