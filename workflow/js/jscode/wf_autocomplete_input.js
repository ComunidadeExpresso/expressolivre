var _cache = new Array();
var autocompleterObjs = new Array();
var myTimer;

/* Verifica se o componente j· foi populado (usado quando o tipo do componente È POPULATE_ON_LOAD) */
function checkDataLoaded(elementId)
{
	if (_cache['elements'][elementId]['populated'] !== true
		&& _cache['requests'][_cache['elements'][elementId]['hash']]['populated'] == true)
	{
		autocompletePopulate(elementId);
	}
}

function showResult(elementId)
{
	var index = $(elementId).value;
	var textResponse = (index == -1)? "Nenhum resultado encontrado!" : "OK";
	$('response' + elementId).innerHTML = textResponse;
	$('response' + elementId).className = (index == -1)? "span_message_error" : "span_message_success";
}

/* Faz a verificaÁ„o se o conte˙do digitado tem um correspondente na lista */
function selectAutocompleteElement(elementId)
{
	if ($('input' + elementId) == null) return -1;
	var value = $('input' + elementId).value;
	var items = new Object();
	if(_cache['requests'][_cache['elements'][elementId]['hash']] != undefined)
		items = _cache['requests'][_cache['elements'][elementId]['hash']]['data'];
	var index = autocompleteIndexOf(items, value);
	$(elementId).value = index;

	$('response' + elementId).innerHTML = typeof(items);
	showResult(elementId);
}

/* Percorre o objeto comparando cada item com o valor digitado no componente, e retorna o Ìndice, se encontrar */
function autocompleteIndexOf(items, value)
{
	for (key in items){
		if(items.hasOwnProperty(key)){
			if (items[key].toLowerCase() == value.toLowerCase()){
				return key;
			}
		}
	}
	return -1;
}

function arrayValues(items)
{
	var i = 0;
	var arr = Array();
	for (key in items){
		arr[i++] = items[key];
	}
	return arr;
}

function removeAccents(text) {
	var accents = '·‡„‚‰ÈËÍÎÌÏÓÔÛÚıÙˆ˙˘˚¸Á¡¿√¬ƒ…» ÀÕÃŒœ”“’÷‘⁄Ÿ€‹«';
	var normalLetters = 'aaaaaeeeeiiiiooooouuuucAAAAAEEEEIIIIOOOOOUUUUC';
	var newText = '';

	for (i = 0; i < text.length; i++) {
		if (accents.search(text.substr(i, 1)) >= 0) {
			newText += normalLetters.substr(accents.search(text.substr(i, 1)), 1);
		}
		else {
			newText += text.substr(i, 1);
		}
	}

	return newText;
}

/* Cria, ou recria, o objeto autocompleter do componente, carregando a lista com os valores que j· devem estar no cache */
function autocompletePopulate(elementId)
{
	if (_cache['elements'][elementId]['populated'] == null){
		var items = _cache['requests'][_cache['elements'][elementId]['hash']]['data'];
		var values = arrayValues(items);
		autocompleterObjs[elementId] = new Autocompleter.Local('input' + elementId, 'list' + elementId, values, {
			'choices':9,
			'partialChars': _cache['elements'][elementId]['minLength']
		});
		_cache['elements'][elementId]['populated'] = true;
	}
}

/* Cria array para armazenar os elementos */
function createCacheElementsArray(){
	if(_cache['elements'] == null)
		_cache['elements'] = new Array();
}

/* Cria array para armazenar os valores das requisiÁıes ajax */
function createCacheRequestsArray(){
	if(_cache['requests'] == null)
		_cache['requests'] = new Array();
}

/* Seta os valores do componente no _cache */
function createCacheElement(elementId, ajaxClass, ajaxMethod, methodParam, componentMode, extraParams){
	// se o par‚metro for um objeto, transforma em uma string para gerar o hash
	var _param = (typeof(methodParam) == 'object')? JSON.stringify(methodParam) : methodParam;

	// se o componente tiver que ser populado em sua criaÁ„o, calcula o hash
	if (componentMode == 'POPULATE_ON_LOAD'){
		var str = ajaxClass + ajaxMethod + _param;
		// Cria hash que identifica classe, mÈtodo e par‚metro.
		// Componente verifica se hash j· existe para n„o fazer requisiÁıes ajax desnecess·rias
		var hash = new SHA1(str).hexdigest();
	}
	// sen„o, n„o h· a necessidade de calcular o hash, pois a lista ser· carregada posteriormente
	else
		var hash = "";

	if(extraParams['minLength'] == null){
		extraParams['minLength'] = 1;
	}

	if (extraParams['idValue'] != null){
		if (extraParams['textValue'] != null){
			// se texto do input j· foi preenchido no momento da montagem do componente, n„o escreve via javascript
			if ($('input' + elementId).value.lenght == 0)
				$('input' + elementId).value = extraParams['textValue'];

			$(elementId).value = extraParams['idValue'];
			showResult(elementId);
		}
	}

	createCacheElementsArray();

	if(_cache['elements'][elementId] == null){
		_cache['elements'][elementId] = new Array();
		_cache['elements'][elementId]['hash'] = hash;
		_cache['elements'][elementId]['minLength'] = extraParams['minLength'];
	} else {
		// por algum motivo componente j· existe.
		// Se o _cache na posiÁ„o hash n„o for null, requisiÁ„o ajax j· foi feita.
		// Se o objeto j· foi populado e est· sendo escrito novamente, manda popular denovo
		//   (isso pode acontecer quando o componente È escrito atravÈs de javascript)
		if(_cache['requests'][hash] != null)
			if (_cache['requests'][hash]['populated'] == true){
				_cache['elements'][elementId]['populated'] = null;
				autocompletePopulate(elementId);
			}
	}
}

function catchJsonError(dados){ return false; }

/* Controle de timeout para chamar a funÁ„o updateCacheRequests quando o usu·rio ficar 0.3 segundos sem digitar. (chamada no onkeyup do componente do tipo REPOPULATE_ON_CHANGE) */
function updateCacheRequestsTimeout(elementId, ajaxClass, ajaxMethod, methodParam, componentMode){
	clearTimeout(myTimer);
	myTimer = setTimeout("updateCacheRequests('" + elementId + "', '" + ajaxClass + "', '" + ajaxMethod + "', '" + methodParam + "', '" + componentMode + "')", 300);
}

/* FunÁ„o que faz o gerenciamento das chamadas ajax e atualiza a lista de opÁıes para seleÁ„o. */
var updateCacheRequests = function(elementId, ajaxClass, ajaxMethod, methodParam, componentMode){
	// se o par‚metro for um objeto, transforma em uma string para gerar o hash
	var _param = (typeof(methodParam) == 'object')? JSON.stringify(methodParam) : methodParam;
	var str = ajaxClass + ajaxMethod + _param;
	// Cria hash que identifica classe, mÈtodo e par‚metro.
	// Componente verifica se hash j· existe para n„o fazer requisiÁıes ajax desnecess·rias
	var hash = new SHA1(str).hexdigest();

	if(_cache['requests'][hash] == null){
		_cache['requests'][hash] = new Array();
		_cache['requests'][hash]['populated'] = null;
		_cache['requests'][hash]['data'] = new Array();

		if (componentMode == 'POPULATE_ON_LOAD'){
			func = function (dados)
			{
				var result = dados[ajaxMethod]['data'];
				if (result !== false){
					// guarda valores localmente
					_cache['requests'][hash]['data'] = result;
					_cache['requests'][hash]['populated'] = true;
					// Envia dados para o componente
					autocompletePopulate(elementId);
				}
			};

			// Faz a requisiÁ„o ajax/Json
			var nc = new NanoController();
			nc.setWfUrl();
			nc.setSuccessHandler(func);
			nc.setExceptionHandler(catchJsonError);
			nc.setErrorHandler(catchJsonError);
			nc.addVirtualRequest(ajaxMethod,
				{
					action: ajaxClass,
					mode:   ajaxMethod
				}, methodParam);
			nc.sendRequest();
		}
		else{
			if(_param.length >= 3){
				func = function (dados)
				{
					var result = dados[ajaxMethod]['data'];
					if(typeof(result) == "object" && result.length != 0){
						// guarda valores localmente
						_cache['requests'][hash]['data'] = result;
						_cache['requests'][hash]['populated'] = true;
						_cache['elements'][elementId]['hash'] = hash;
						var values = arrayValues(result);
						autocompleterObjs[elementId].options.array = values;
						autocompleterObjs[elementId].getUpdatedChoices();
					}
				};

				// Faz a requisiÁ„o ajax/Json
				var nc = new NanoController();
				nc.setWfUrl();
				nc.setSuccessHandler(func);
				nc.setExceptionHandler(catchJsonError);
				nc.setErrorHandler(catchJsonError);
				nc.addVirtualRequest(ajaxMethod,
					{
						action: ajaxClass,
						mode:   ajaxMethod
					}, removeAccents(methodParam));
				nc.sendRequest();
			}
		}
	}
	else{
		// Se for do modo que deve repopular a lista a cada tecla pressionada e o _cache referente ‡ entrada digitada j· estiver populada
		if (componentMode == 'REPOPULATE_ON_CHANGE' && _cache['requests'][hash]['populated']){
			_cache['elements'][elementId]['hash'] = hash;
			var values = arrayValues(_cache['requests'][hash]['data']);
			autocompleterObjs[elementId].options.array = values;
		}
	}
}

/* FunÁ„o que prepara o _cache, criando e setando os valores para cada componente */
function autocompleteSelect(elementId, ajaxClass, ajaxMethod, methodParam, componentMode, extraParams)
{
	createCacheElement(elementId, ajaxClass, ajaxMethod, methodParam, componentMode, extraParams);
	createCacheRequestsArray();

	// Se o componente È do tipo que deve repopular a lista a cada tecla, cria um objeto autocompleter com a lista vazia
	if(componentMode == 'REPOPULATE_ON_CHANGE')
		autocompleterObjs[elementId] = new Autocompleter.Local('input' + elementId, 'list' + elementId, new Array(), {
			'choices':9,
			'partialChars': _cache['elements'][elementId]['minLength']
		});

	updateCacheRequests(elementId, ajaxClass, ajaxMethod, methodParam, componentMode);
}
