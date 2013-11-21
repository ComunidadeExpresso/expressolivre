
var current_value = "";

var default_limit = 0;

var sizelimit = 10;

function Element( id )
{
    return document.getElementById( id );
}

function optionFind( id, selectId, url, ctxId, labelId )
{
    var fillHandler = function( fill, raw ){

	return fillContentSelect( fill, selectId );

    };

    getAvailable( id, fillHandler, url, ctxId, labelId, true );
}

function getAvailable( id, fillHandler, url, ctxId, labelId, bypass )
{
    if( id )
    {
	var sentence = Element( id );

	sentence = sentence ? sentence.value : id;
	
	url += "&filter=" + sentence + "&sentence=" + sentence;
    }

    if( ctxId )
    {
	var ctx = Element( ctxId );
	
	ctx = ctx ? ctx.value : ctxId;

	url += "&context=" + ctx;
    }

    if( bypass )
	bypass = bypassParser;

    userFinder( sentence, fillHandler, url, bypass, labelId );
}

var default_field = "cn";

function userFinder( sentence, selectId, url, handler, labelId )
{
    if( sentence === current_value )
	return;

    //current_value = sentence;

    if( typeof limit === "undefined" )
	limit = default_limit;

    //TODO: tornar esse limite configuravel de acordo com a configuracao do expresso
    if( sentence.length < limit )
	return( false );

    if( typeof handler === "string" &&
	typeof labelId === "undefined" )
	labelId = handler,
	handler = undefined;

    if( typeof get_lang === "undefined" )
	get_lang = function( key ){
	    var translator = Element( "txt_" + key );

	    return translator ? translator.value : key;
	};

    var urlHandler = function(){

	    if( labelId )
		Element( labelId ).innerHTML = get_lang('searching') + '...';

	    if( typeof url === "function" )
		url = url();

	    return( url );
    };

    var defaultParser = function( data, raw ){

	if( labelId )
	    Element( labelId ).innerHTML = '&nbsp;';

	    var result = false;

	    if( handler )
		result = handler( data, raw );

	    if( result )
		return( result );

	if( typeof data === "string" )
	    return( data );

	return stackParser( data, default_field );
    };

    var fillHandler = function( fill, raw )
    {
	var sizeof = 0;

	if( typeof selectId === "string" )
	    sizeof = fillSelect( fill, selectId, default_field );
	else
	    sizeof = selectId( fill, raw );

	if( labelId && sizeof >= sizelimit )
	    Element( labelId ).innerHTML = 'Muitos resultados encontrados. Por favor, refine sua busca.';

	return( !sizeof );
    }

    return finder( sentence, fillHandler, urlHandler, defaultParser );
}

function finder( sentences, fillHandler, url, parser )
{
    //caso fillHandler nao seja uma funcao, usar a default
//     if( typeof fillHandler === "string" )
//     {
// 	var selectId = fillHandler;
// 
// 	fillHandler = function( fill ){
// 
// 	    return !fillSelect( fill, selectId, default_field );
// 
// 	};
//     }

    return lookup( sentences, fillHandler, url, parser, fillHandler );
}

function getExp( sentence )
{
   sentence = sentence.replace(/^\s*/, "").replace(/\s*$/, "");
 
   sentence = sentence.replace( / /gi, ".*" );

   sentence = new RegExp( ".*" + sentence + ".*", "i" );

    return( sentence );
}

var options_cache = {};

function setOptions( fill, select, field )
{
    for( var value in fill )
    {
	if( !options_cache[value] )
	     options_cache[value] = {};

	if( !options_cache[value][field] )
	     options_cache[value][field] = new Option( fill[value][field], value );

	select[select.length] = options_cache[value][field];
    }

    return( select );
}

function fillSelect( fill, selectId, field )
{
    //recupera as options do respectivo select
    var select = Element( selectId ).options;

    //Limpa todo o select
    select.length = 0;

    //Inclui usuario comecando com a pesquisa
    select = setOptions( fill, select, field );

    //chama o server side caso nao encontre resultado nenhum com essa sentenca
    return( select.length );
}

function fillContentSelect( fill, selectId )
{
    if( typeof fill === "string" )
    {
	var select = Element( selectId );

	select.innerHTML = fill;

	return( select.options.length );
    }

    var content = "";

    for( var section in fill )
    {
	if( !fill[section] || fill[section] === "" )
	    continue;

	var entry = entryTag( section );

	content += entry.outerHTML;
	content += fill[section];
    }

    return fillContentSelect( content, selectId );
}


function entryTag( label, select )
{
    var line = '-------------------';

    var option = new Option( line + ' ' + get_lang(label) + ' ' + line + ' ', -1 );
    option.disabled = true;

    if( typeof select === "undefined" )
	return( option );

    if( typeof select === "string" )
	select = Element( select ).options;

    if( select.options )
	select = select.options;

    select[select.length] = option;
}

function fillGroupableSelect( fill, selectId, groupHandler, field )
{
    var groups = {};

    var select = Element( selectId ).options;

    var sizeof = select.length = 0;

    for( value in fill )
    {
	var target = fill[value];

	var group = groupHandler( target );

	if( !groups[ group ] )
	    groups[ group ] = {};

	groups[ group ][ value ] = target;

	sizeof++;
    }

    for( groupId in groups )
    {
	var group = groups[ groupId ];

	 entryTag( groupId, select );

	select = setOptions( group, select, field );
    }

    return( sizeof );
}

function flipParser( data )
{
    var result = {};

    for( var section in data )
    {
	var target = data[section];

	for ( var key in target )
	{
	    if( !result[key] )
		result[key] = {};

	    result[key][section] = target[key];
	}
    }

    return( result );
}

function stackParser( data, field )
{
    if( !data ) return( false );

    for( var section in data )
	data[section] = normalize( data[section], field );

    return( data );
}

function bypassParser( x ){
    return( x );
}

var userData = {};

function lookup( matchers, fillHandler, url, parser, callback )
{
    var serverCallback = false;

    if( url )
    {
	//handler chamado pelo callback do servidor para repopular.
	serverCallback = function( filters, handler ){

	    var refill = function( userd, data ){

		//no caso de existir um custom callback
		if( callback )
		    if( !callback( data, userd ) )
			return;

		//filter( filters, handler );

	    };

	    search( url, refill, parser );
	};
    }

    serverCallback( matchers, fillHandler, serverCallback );
}

function filter( filters, fillHandler, emptinessHandler )
{
    filters = normalize( filters );

    var fill = {};

    //varrer todas as sentencas e secoes especificas
    for( key in userData )
    {
	var user = userData[key];

	if( !user ) continue;

	var filtered = false;

	//populando o mapa filtrando pela determinada sentenca
	for( section in filters )
	{
	    if( filtered ) break;

	    //filtro para a secao especifica.
	    var criteria = filters[section];

	    var target = user[section] || user;

	    if( !criteria( target ) )
		filtered = true;
	}

	if( !filtered )
	    fill[key] = user;
    }

    //tenta chamar o handler para popular, caso nao consiga chama o server side
    if( fillHandler )
	if( !fillHandler( fill ) && emptinessHandler )
	    return emptinessHandler( filters, fillHandler );

    return( fill );
}

function search( url, callback, parser )
{
    var handler = function( data )
    {
	if( !cExecute )
	    data = unserialize( data );

	var dt = false;

	if( typeof data == "string" && data.charAt(0) === '{' )
	{
	    try{
		dt = data;
		data = (new Function("return " + data))();
	    }
	    catch(e){
	    }
	}

// 	if( data && typeof data !== "string" && data.nosession )
// 	    window.location.reload( false );

	if( parser )
	    data = parser( data, dt );

	if( callback )
	    callback( userData, data );
    }

    if( typeof url === "function" )
	url = url();

    if( typeof cExecute !== "undefined" )
	return cExecute( url, handler );

    return this.Connector.newRequest( 'search', url, 'GET', handler );
}

function normalize( raw, field )
{
    if( raw instanceof RegExp )
    {
	var exp = raw;

	raw = function( match ){
	    return exp.test( match );
	}
    }

    if( typeof field !== "undefined" && raw !== "object" )
    {
	var content = raw;

	raw = {};
	
	raw[field] = content;
    }

    if( typeof raw === "object" )
	for( var key in raw )
	    raw[key] = normalize( raw[key] );

    return( raw );
}
