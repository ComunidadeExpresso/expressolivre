var workflowUserInterfaceEmployeeInfoTimer = null;
var workflowUserInterfaceAreaInfoTimer = null;
var workflowUserInterfaceCurrentAreaID = 0;
var workflowUserInterfaceClickToCall = false;

String.prototype.repeat = function(l)
{
	return new Array(l+1).join(this);
};

/* define the orgchart layout */
function createOrgchartLayout()
{
	var content = '<div id="orgchartMenu"></div>';
	content += '<div class="orgchartAreas" id="orgchartAreas"></div>';
	content += '<div class="orgchartEmployees" id="orgchartEmployees"></div>';
	content += '<div class="orgchartFooter" id="orgchartFooter"></div>';
	content += '<div id="employeeInfo" class="employeeInfo" style="display: none;"></div>';
	content += '<div id="areaInfo" class="employeeInfo" style="display: none;"></div>';

	var div = $('content_id_4');
	div.innerHTML = content;

	draw_orgchart_folder();
}

/* generates the orgchart menu */
function createOrgchartMenu(organizationID, imagemURL)
{
	var enderecoImagem = '';
	if ((imagemURL != null) && (imagemURL != ''))
		enderecoImagem = imagemURL;
	else
		enderecoImagem = '../index.php?menuaction=workflow.ui_orgchart.graph&organizationID=' + organizationID;

	var content = '<ul class="horizontalMenu">';
	content += '<li><a onclick="return false;">Visualizar : <select onclick="this.parentNode.parentNode.parentNode.lastChild.style.display = (this.options[1].selected) ? \'block\' : \'none\' ">'
	content += '<option onclick="getAlphabeticalEmployees( )">Alfabética</option>'
	content += '<option onclick="getHierarchicalArea( );" selected="true">Áreas</option>'
	content += '<option onclick="getCostCenters( );">Centros de Custo</option>'
	content += '<option onclick="getManning( )">Localidades</option>'
	content += '<option onclick="getAreaWithSubtituteBoss( )">Substituição de Chefia</option>'
	content += '<option onclick="getUsefulPhones( );">Telefones Úteis</option>'
	content += '<option onclick="getCategoriesList( )">Vínculos</option>'
	content += '</select></a></li>';
	content += '<li><a href="#" onclick="window.open(\'' + enderecoImagem + '\', \'extwindow\'); return false;"><img src="templateFile.php?file=images/Process.gif">&nbsp;&nbsp;Gráfico</a></li>';
	content += '<li><a><input type="text" name="search_term" id="search_term" onkeypress="if (((event.which) ? event.which : event.keyCode) == 13) $(\'search_span\').onclick(); return true;" /><span id="search_span" style="cursor: pointer;" onclick="tmp = $$(\'div#orgchartAreas a.destaque\'); if (tmp[0]) tmp[0].removeClassName(\'destaque\'); orgchartSearchEmployee($F(\'search_term\')); return false;">&nbsp;busca&nbsp;</span><img src="templateFile.php?file=images/help.png" title="Informe o nome, área ou telefone a ser pesquisado." style="cursor: help" /></a></li>';
	content += '<li><a href="#" onclick="printArea(); return false;"><img src="templateFile.php?file=images/imprimir.png" width="16">&nbsp;&nbsp;Imprimir</a></li>';
	content += '</ul>';
	content += '<br/>';
	content += '<br/>';

	$('orgchartMenu').innerHTML = content;

	$( 'search_term' ).focus( );
}

/* load the initial data */
function draw_orgchart_folder()
{
	$('orgchartMenu').innerHTML = '';
	$('orgchartAreas').innerHTML = '';
	$('orgchartEmployees').innerHTML = '<br/><br/><br/><br/><center><i>&nbsp;&nbsp;carregando...<br/><img src="templateFile.php?file=images/loading.gif"></i></center>';
	$('orgchartFooter').innerHTML = '';
	$('orgchartMenu').innerHTML = '';

	cExecute("$this.bo_userinterface.isVoipEnabled", function( data )
	{
		if ( typeof data == 'string' && data == 'VoipIsEnabled' )
			workflowUserInterfaceClickToCall = true;
	}, "");

	cExecute("$this.bo_userinterface.orgchart", orgchart, "");
}

/* process the initial data */
function orgchart(data)
{
	if (_checkError(data))
		return;

	/* in case of any warning */
	if (data['warning'])
	{
		$('content_id_4').innerHTML = '<br/><br/><center><strong>' + data['warning'] + '</strong></center><br/><br/>';
		return;
	}

	if (data['areas'].length == 0)
	{
		$('content_id_4').innerHTML = "<br/><br/><center><strong>Nenhuma área cadastrada.</strong></center><br/><br/>";
		return;
	}

	/* continue displaying the data */
	displayHierarchicalAreas(data['areas']);
	createOrgchartMenu(data['organizacao_id'], data['url_imagem']);
	$('orgchartEmployees').innerHTML = '<br/><br/><br/><br/><center><i>faça uma busca ou clique em uma área para ver a lista de funcionários</i></center>';
}

function displayCostCenters(data)
{
	if (_checkError(data))
		return;

	var div = $('orgchartEmployees');
	if (data.length == 0)
	{
		div.innerHTML = '<br/><br/><center><strong>Nenhum registro encontrado</strong></center>';
		return;
	}

	var content = '<center><h2>Centros de Custo</h2></center>';
	content += '<table class="employeeList">';
	content += '<tr><th>Nome</th><th>Número</th><th>Grupo</th></tr>';
	var current;
	var costCentersCount = data.length;
	for (var i = 0; i < costCentersCount; i++)
	{
		current = data[i];
		content += '<tr class="linha'+ i%2 + '" onmouseover="this.className=\'highlight0\'" onmouseout="this.className=\'linha' + i%2 + '\'">';
		content += '<td>' + current['descricao'] + '</td>';
		content += '<td>' + current['nm_centro_custo'] + '</td>';
		content += '<td>' + current['grupo'] + '</td>';
		content += '</tr>';
	}
	content += '</table>';
	div.innerHTML = content;
}

function displayUsefulPhones( data )
{
	if ( _checkError( data ) )
		return;

	var div = $('orgchartEmployees');
	if (data.length == 0)
	{
		div.innerHTML = '<br/><br/><center><strong>Nenhum registro encontrado</strong></center>';
		return;
	}

	var content = '<center><h2>Telefones Úteis</h2></center>';
	content += '<table class="employeeList">';
	content += '<tr><th>Localidade</th><th>Número</th></tr>';
	var current;
	for (var i = 0; i < data.length; i++)
	{
		current = data[i];
		content += '<tr class="linha'+ i%2 + '" onmouseover="this.className=\'highlight0\'" onmouseout="this.className=\'linha' + i%2 + '\'">';
		content += '<td>' + current[ 'descricao' ] + '</td>';
		content += '<td>' + current[ 'numero' ] + '</td>';
		content += '</tr>';
	}
	content += '</table>';
	div.innerHTML = content;
}

function displayAreaWithSubtituteBoss( data )
{
	if ( _checkError( data ) )
		return;

	var div = $('orgchartEmployees');
	if (data.length == 0)
	{
		div.innerHTML = '<br/><br/><center><strong>Nenhum registro encontrado</strong></center>';
		return;
	}

	var content = '<center><h2>Substituição de Chefia</h2></center>';
	content += '<table class="employeeList">';
	content += '<tr><th>Área</th><th>Titular</th><th>Substituto</th><th>Data de início</th><th>Data de término</th></tr>';
	var current;
	for (var i = 0; i < data.length; i++)
	{
		current = data[i];
		content += '<tr class="linha'+ i%2 + '" onmouseover="this.className=\'highlight0\'" onmouseout="this.className=\'linha' + i%2 + '\'">';
		content += '<td>' + current['area'] + '</td>';
		content += '<td>' + current['titular'] + '</td>';
		content += '<td>' + current['substituto'] + '</td>';
		content += '<td>' + current['data_inicio'] + '</td>';
		content += '<td>' + current['data_fim'] + '</td>';
		content += '</tr>';
	}
	content += '</table>';
	div.innerHTML = content;
}

function displayHierarchicalAreas(data)
{
	if (_checkError(data))
		return;

	function recursivePrint(subdata)
	{
		for (var i = 0; i < subdata.length; i++)
		{
			div.innerHTML += '<br />' + '&nbsp;&nbsp;&nbsp;&nbsp;'.repeat(subdata[i]['depth']) + '<a href="javascript:void(0)" id="area_' + subdata[i]['area_id'] + '" onmouseover="getAreaInfoTimer(event, ' + subdata[i]['area_id'] + '); return false;" onmouseout="hideAreaInfo(); return false;" onclick="tmp = $$(\'div#orgchartAreas a.destaque\'); if (tmp[0]) tmp[0].removeClassName(\'destaque\'); this.addClassName(\'destaque\'); loadAreaEmployees(' + subdata[i]['area_id'] + ', \'' + subdata[i]['sigla'] + '\')">' + subdata[i]['sigla'] + '</a>';
			if (subdata[i]['children'].length > 0)
				recursivePrint(subdata[i]['children']);
		}
	}

	var div = $('orgchartAreas');
	div.innerHTML = "<center><strong>ÁREAS</strong></center>";
	recursivePrint(data);
}

function getUsefulPhones( )
{
	workflowUserInterfaceCurrentAreaID = 0;

	cExecute("$this.bo_userinterface.getUsefulPhones", displayUsefulPhones, "");
}

function getAreaWithSubtituteBoss( )
{
	workflowUserInterfaceCurrentAreaID = 0;

	cExecute("$this.bo_userinterface.getAreaWithSubtituteBoss", displayAreaWithSubtituteBoss, "");
}

function getCostCenters()
{
	workflowUserInterfaceCurrentAreaID = 0;

	cExecute("$this.bo_userinterface.getCostCenters", displayCostCenters, "");
}

function getHierarchicalArea()
{
	workflowUserInterfaceCurrentAreaID = 0;

	cExecute("$this.bo_userinterface.getHierarchicalArea", displayHierarchicalAreas, "");
}

function getAreaList()
{
	cExecute("$this.bo_userinterface.getAreaList", displayHierarchicalAreas, "");
}

function getCategoriesList()
{
	workflowUserInterfaceCurrentAreaID = 0;

	var div = $('orgchartEmployees');
	div.innerHTML = '';

	function resultGetCategoriesList(data)
	{
		if (_checkError(data))
			return;

		var content = '<center><strong>VÍNCULOS</strong></center>';
		for (var i = 0; i < data.length; i++)
			content += '<br/>' + '&nbsp;&nbsp;<a href="javascript:void(0)" id="categoria_' + data[i]['funcionario_categoria_id'] + '" onclick="tmp = $$(\'div#orgchartAreas a.destaque\'); if (tmp[0]) tmp[0].removeClassName(\'destaque\'); this.addClassName(\'destaque\'); loadCategoryEmployees(' + data[i]['funcionario_categoria_id'] + ', \'' + data[i]['descricao'] + '\')">' + data[i]['descricao'] + ' (' + data[i]['contagem'] + ')</a>';
		content += '<br/><br/>';
		$('orgchartAreas').innerHTML = content;
	}

	cExecute("$this.bo_userinterface.getCategoriesList", resultGetCategoriesList, "");
}

function getManning( )
{
	workflowUserInterfaceCurrentAreaID = 0;

	var div = $('orgchartEmployees');
	div.innerHTML = '';

	function resultGetManning( data )
	{
		if ( _checkError( data ) )
			return;

		var content = '<center><strong>Localidades</strong></center>';
		for ( var i = 0; i < data.length; i++ )
			content += '<br/>' + '&nbsp;<a href="javascript:void(0)" id="localidade_' + data[i]['localidade_id'] + '" onclick="tmp = $$(\'div#orgchartAreas a.destaque\'); if (tmp[0]) tmp[0].removeClassName(\'destaque\'); this.addClassName(\'destaque\');loadManningEmployees(' + data[i]['localidade_id'] + ', \'' + data[i]['descricao'] + '\')">' + data[i]['descricao'] + '</a>';

		content += '<br/><br/>';

		$('orgchartAreas').innerHTML = content;
	}

	cExecute("$this.bo_userinterface.getManning", resultGetManning, "");
}

function printEmployeesHandler(data)
{
	/* check for errors */
	if (_checkError(data))
		return;

	var div = $('orgchartEmployees');

	/* no employee to list */
	if ( ( ! data['employees'] || data['employees'].length == 0 ) && 
		 ( ! data['bygroup'] || data['bygroup'].length == 0 ) &&
		 ( ! data['bytelephone'] || data['bytelephone'].length == 0 ) )
	{
		div.innerHTML = '<br/><br/><center><strong>Nenhum registro encontrado</strong></center>';
		return;
	}

	if ( data['employees'] && data['employees'].length )
	{
		/* initialize varivables */
		var content = '';
		var employees = data['employees'];
		var useCategories = false;
		var useArea = false;

		/* check the informations that will be displayed */
		if (data['categories'])
			if (data['categories'].length > 1)
				useCategories = true;
		if (employees[0]['area'])
			useArea = true;

		/* build the display table (headers)*/
		content += '<table id="employeeList" class="employeeList" style="clear: both">';
		content += '<tr class="message_header">';
		content += '<th>Nome</th>';
		if (useArea)
			content += '<th>Área</th>';
		content += '<th>Telefone</th>';
		content += '</tr>';

		/* if available, insert a menu to filter the categories */
		if (useCategories)
		{
			content += '<tr><td colspan="' + (useArea ? '3' : '2') + '">';
			content += '<ul class="horizontalMenu">';
			for (var i = 0; i < data['categories'].length; i++)
				content += '<li><a href="#" style="height: 2em; line-height: 2em;" onclick="highlightCategory(' + data['categories'][i]['funcionario_categoria_id'] + '); return false;">' + data['categories'][i]['descricao'] + ' (' + data['categories'][i]['contagem'] + ')</a></li>';
			content += '</ul>';
			content += '</td></tr>';
		}

		/* list the employees */
		var complement = '';
		var employeeNotFound = false;
		for (var i = 0; i < employees.length; i++)
		{
			if (employees[i]['chief'])
				complement = ' <strong>(' + ((employees[i]['chief'] == 1) ? 'Titular' : 'Substituto') + ')</strong>';
			else
				complement = '';
			if (employees[i]['removed'])
			{
				complement += ' (*)';
				employeeNotFound = true;
			}
			content += '<tr class="linha'+ i%2 + (useCategories ? ' categoria_' + employees[i]['funcionario_categoria_id'] : '') + '" onmouseover="this.className=\'highlight0\'" onmouseout="this.className=\'linha'+ i%2 + (useCategories ? ' categoria_' + employees[i]['funcionario_categoria_id'] : '') + '\'">';
			content += '<td><a href="javascript:void(0);" onmouseover="getEmployeeInfoTimer(event, ' + employees[i]['funcionario_id'] + '); return false;" onmouseout="hideEmployeeInfo(); return false;">' + employees[i]['cn'] + complement + '</a></td>';
			if (useArea)
				content += '<td><a href="javascript:void(0);" onclick="loadAreaEmployees(\''+employees[i]['area_id']+'\', \'' + employees[i]['area'] + '\')">' + employees[i]['area'] + '</a></td>';
			content += '<td align="center">';
			if ( ! workflowUserInterfaceClickToCall )
				content += employees[i]['telephoneNumber'];
			else
			{
				content += '<a href="javascript:void(0);" title="Discar para Telefone Comercial" onclick="callVoipConnect(\''+employees[i]['telephoneNumber']+'\')"';
				content += '>' + employees[i]['telephoneNumber'] + '</a>';
			}
			content += '</td></tr>';
		}
		content += '</table>';

		/* display a indication that some employees where not found */
		if (employeeNotFound)
		{
			content += '<hr><p>(*) = Usuários não localizados no catálogo do Expresso.</p>';
		}

		if ( arguments[ 1 ] != 'returnResult' )
		{
			/* display the employees list and go to the top of the page */
			div.innerHTML = content;
			window.scrollTo(0,0);
		}
		else
			return content;
	}
}

function highlightCategory(categoryID)
{
	var rows = $('employeeList').childNodes[0].childNodes;
	var categoryClass = 'categoria_' + categoryID;

	var highlightClass = '';
	var row;
	for (var i = 1; i < rows.length; i++)
	{
		row = $(rows[i]);
		/* in case alternated color rows are needed, just change the second 'highlight0' to something else (e.g. 'highlight1' which is alread defined) */
		highlightClass = row.hasClassName('linha0') ? 'highlight0' : 'highlight0';
		if (row.hasClassName(categoryClass))
			row.addClassName(highlightClass);
		else
			row.removeClassName(highlightClass);
	}
}

function loadAreaEmployees(areaID, areaName)
{
	workflowUserInterfaceCurrentAreaID = areaID;
	$('orgchartEmployees').innerHTML = '';
	cExecute('$this.bo_userinterface.getAreaEmployees', function( data )
	{
		var content = printEmployeesHandler( data, 'returnResult' );
		if ( content )
			$('orgchartEmployees').innerHTML = '<center><h2>Área: ' + areaName + '</h2></center>' + content;
	}, 'areaID=' + areaID);
}

function loadCategoryEmployees(categoryID, categoryName)
{
	workflowUserInterfaceCurrentAreaID = 0;
	$('orgchartEmployees').innerHTML = '';
	cExecute('$this.bo_userinterface.getCategoryEmployees', function( data )
	{
		var content = printEmployeesHandler( data, 'returnResult' );
		if ( content )
			$('orgchartEmployees').innerHTML = '<center><h2>Vínculo: ' + categoryName + '</h2></center>' + content;
	}, 'categoryID=' + categoryID);
}

function loadManningEmployees( locationID, locationName )
{
	workflowUserInterfaceCurrentAreaID = 0;
	$('orgchartEmployees').innerHTML = '';
	cExecute('$this.bo_userinterface.getManningEmployees', function( data )
	{
		var content = printEmployeesHandler( data, 'returnResult' );
		if ( content )
			$('orgchartEmployees').innerHTML = '<center><h2>Localidade: ' + locationName + '</h2></center>' + content;
	}, 'locationID=' + locationID);
}

function getAlphabeticalEmployees( )
{
	workflowUserInterfaceCurrentAreaID = 0;

	var div = $('orgchartEmployees');
	div.innerHTML = '';

	var p_page = 0;
	if ( arguments.length )
	{
		p_page = parseInt(arguments[ 0 ]);
		if ( isNaN( p_page ) )
			p_page = 0;
	}
	cExecute('$this.bo_userinterface.getAlphabeticalEmployees', function( data )
	{
		var pagingData = data['paging_links'];
		var output = '';
		if (pagingData)
		{
			var pagingSize = pagingData.length;
			for (var i = 0; i < pagingSize; i++)
			{
				if (pagingData[i].do_link == true)
					output += '<a style="font-size: 13px" href="#" onclick="getAlphabeticalEmployees(' + pagingData[i].p_page + ');">' + pagingData[i].name + '</a>&nbsp;';
				else
					output += '<strong style="font-size: 14px">' + pagingData[i].name + '</strong>&nbsp;';
			}
		}

		var content = '<center><h2>Vizualização Alfabética</h2></center>';
		content += printEmployeesHandler( data, 'returnResult' );
		content += '<br /><center>' + output + '</center>';
		div.innerHTML = content;

	}, 'p_page='+p_page);
}

function orgchartSearchEmployee(searchTerm)
{
	workflowUserInterfaceCurrentAreaID = 0;
	var div = $('orgchartEmployees');
	div.innerHTML = '';
	cExecute('$this.bo_userinterface.searchEmployee', function( data )
	{
		div.innerHTML = '<center><h2>Resultado da Busca</h2>';
		var content = printEmployeesHandler( data, 'returnResult' );
		if ( content )
			div.innerHTML += '</center><span style="color:red">Busca pelo nome: ' + searchTerm.toUpperCase( ) + '</span>' + content;

		// printing records found by group
		if ( data['bygroup'] && data['bygroup'].length )
		{
			employees = [ ];
			employees[ 'employees' ] = data['bygroup'];
			content = printEmployeesHandler( employees, 'returnResult' );
			if ( content )
				div.innerHTML += '<br/><br/><span style="color:red">Busca pelo setor: ' + searchTerm.toUpperCase( ) + '</span><br/>' + content;
		}

		// printing records found by telephoneNumber
		if ( data['bytelephone'] && data['bytelephone'].length )
		{
			employees = [ ];
			employees[ 'employees' ] = data['bytelephone'];
			content = printEmployeesHandler( employees, 'returnResult' );
			if ( content )
				div.innerHTML += '<br/><br/><span style="color:red">Busca pelo telefone: ' + searchTerm.toUpperCase( ) + '</span><br/>' + content;
		}

	}, 'searchTerm=' + searchTerm);
}

function printArea()
{
	if (workflowUserInterfaceCurrentAreaID == 0)
		if (!confirm('Tem certeza de que deseja imprimir todo o Organograma?'))
			return false;
	var endereco = '../index.php?menuaction=workflow.ui_userinterface.printArea&areaID=' + workflowUserInterfaceCurrentAreaID;
	window.open(endereco, 'extwindow');
}

function getEmployeeInfoTimer(e, employeeID)
{
	var div = $('employeeInfo');
	div.style.left = (Event.pointerX(e) - 50) + 'px';
	div.style.top = (Event.pointerY(e) + 14) + 'px';
	div.hide();

	if (workflowUserInterfaceEmployeeInfoTimer != null)
	{
		workflowUserInterfaceEmployeeInfoTimer = clearTimeout(workflowUserInterfaceEmployeeInfoTimer);
		workflowUserInterfaceEmployeeInfoTimer = null;
	}

	workflowUserInterfaceEmployeeInfoTimer = setTimeout('getEmployeeInfo(' + employeeID + ')', 500);
}

function getEmployeeInfo(employeeID)
{
	function resultGetEmployeeInfo(data)
	{
		if (workflowUserInterfaceEmployeeInfoTimer == null)
			return;

		workflowUserInterfaceEmployeeInfoTimer = clearTimeout(workflowUserInterfaceEmployeeInfoTimer);
		workflowUserInterfaceEmployeeInfoTimer = null;

		var card_data = [ ];

		for (var i = 0; i < data['info'].length; i++)
			card_data[ data[ 'info' ][ i ][ 'name' ] ] = data[ 'info' ][ i ][ 'value' ];

		var card = document.createElement( 'div' );
		card.style.fontSize = '12px';
		card.style.padding = '5px';
		card.style.marginRight = '70px';

		card.onmouseover = function( )
		{
			workflowUserInterfaceEmployeeInfoTimer = clearTimeout(workflowUserInterfaceEmployeeInfoTimer);
			workflowUserInterfaceEmployeeInfoTimer = null;
		}

		card.onmouseout = function( )
		{
			workflowUserInterfaceEmployeeInfoTimer = setTimeout( "$('employeeInfo').hide( )", 1000 );
		}

		var photo = document.createElement( 'img' );
		photo.src = 'showUserPicture.php?userID=' + employeeID;
		photo.style.position = 'absolute';
		photo.style.right = '10px';

		card.appendChild( photo );

		if ( card_data[ 'Nome' ] )
		{
			var name = document.createElement( 'span' );
			name.style.fontWeight = 'bold';
			name.appendChild( document.createTextNode( card_data[ 'Nome' ] ) );
			card.appendChild( name );
			card.appendChild( document.createElement( 'br' ) );
		}

		if ( card_data[ 'Título' ] )
			var role = card.appendChild( document.createTextNode( card_data[ 'Título' ] ) );

		if ( card_data[ 'Área' ] )
		{
			if ( role )
				card.appendChild( document.createTextNode( ' - ' ) );

			var area = document.createElement( 'a' );
			area.href = "javascript:void(0)";
			area.appendChild( document.createTextNode( card_data[ 'Área' ] ) );
			area.onclick = function( )
			{
				loadAreaEmployees( card_data[ 'ÁreaID' ], card_data[ 'Área' ] );
				$('employeeInfo').hide( );
			};
			card.appendChild( area );
		}

		if ( card_data[ 'Matrícula' ] )
		{
			if ( role || area )
				card.appendChild( document.createElement( 'br' ) );
			card.appendChild( document.createTextNode( 'Matrícula : ' + card_data[ 'Matrícula' ] ) );
		}

		card.appendChild( document.createElement( 'br' ) );
		card.appendChild( document.createElement( 'br' ) );

		if ( card_data[ 'Empresa' ] )
		{
			var company = document.createElement( 'span' );
			company.style.fontWeight = 'bold';
			company.appendChild( document.createTextNode( card_data[ 'Empresa' ] ) );
			card.appendChild( company );
			card.appendChild( document.createElement( 'br' ) );
		}

		if ( card_data[ 'Endereço' ] )
			var address = card.appendChild( document.createTextNode( card_data[ 'Endereço' ] ) );

		if ( card_data[ 'Complemento' ] )
		{
			if ( address )
				card.appendChild( document.createTextNode( ' - ' ) );
			var complement = card.appendChild( document.createTextNode( card_data[ 'Complemento' ] ) );
		}

		if ( address || complement )
			card.appendChild( document.createElement( 'br' ) );

		if ( card_data[ 'Cep' ] )
			var zipcode = card.appendChild( document.createTextNode( card_data[ 'Cep' ] ) );

		if ( card_data[ 'Bairro' ] )
		{
			if ( zipcode )
				card.appendChild( document.createTextNode( ' - ' ) );
			var district = card.appendChild( document.createTextNode( card_data[ 'Bairro' ] ) );
		}

		if ( zipcode || district )
			card.appendChild( document.createElement( 'br' ) );

		if ( card_data[ 'Cidade' ] )
			var city = card.appendChild( document.createTextNode( card_data[ 'Cidade' ] ) );

		if ( card_data[ 'UF' ] )
		{
			if ( city )
				card.appendChild( document.createTextNode( ' - ' ) );
			card.appendChild( document.createTextNode( card_data[ 'UF' ] ) );
		}

		card.appendChild( document.createElement( 'br' ) );
		card.appendChild( document.createElement( 'br' ) );

		if ( card_data[ 'Telefone' ] )
		{
			var phone = document.createElement( ( workflowUserInterfaceClickToCall ) ? 'a' : 'span' );
			phone.appendChild( document.createTextNode( card_data[ 'Telefone' ] ) );
			phone.style.paddingLeft = '20px';
			phone.style.whiteSpace = 'nowrap';
			phone.style.background = 'url(templateFile.php?file=images/phone.png) no-repeat 0 0';

			var phoneNumber = card_data[ 'Telefone' ];
			if ( workflowUserInterfaceClickToCall )
			{
				phone.title = "Discar para Telefone Comercial"
				phone.onclick = function( )
				{
					callVoipConnect( phoneNumber );
				}
			}

			card.appendChild( phone );
		}
		
		var mobiles = card_data[ 'Mobile' ];
		//var arr_mobiles = mobiles.split(',');
		
		for (var mob = 0; mob < mobiles.length; mob++) {
			if ( card_data[ 'Mobile' ] ) {
				card.appendChild( document.createElement( 'br' ) );
				var phone = document.createElement('span');
				phone.appendChild( document.createTextNode( mobiles[mob] ) );
				phone.style.paddingLeft = '20px';
				phone.style.whiteSpace = 'nowrap';
				phone.style.background = 'url(templateFile.php?file=images/mobile.png) no-repeat 0 0';
				card.appendChild( phone );
			}
		}

		
		if ( card_data[ 'homePhone' ] )	{
			card.appendChild( document.createElement( 'br' ) );
			var phone = document.createElement( ( workflowUserInterfaceClickToCall ) ? 'a' : 'span' );
			phone.appendChild( document.createTextNode( card_data[ 'homePhone' ] ) );
			phone.style.paddingLeft = '20px';
			phone.style.whiteSpace = 'nowrap';
			phone.style.background = 'url(templateFile.php?file=images/homePhone.png) no-repeat 0 0';

			card.appendChild( phone );
		}

		card.appendChild( document.createElement( 'br' ) );
		card.appendChild( document.createElement( 'br' ) );

		if ( card_data[ 'e-mail' ] )
		{
			var mail = document.createElement( 'a' );
			mail.appendChild( document.createTextNode( card_data[ 'e-mail' ] ) );
			mail.href = '../expressoMail1_2/index.php?to=' + card_data[ 'e-mail' ];
			mail.style.paddingLeft = '20px';
			mail.style.whiteSpace = 'nowrap';
			mail.style.background = 'url(templateFile.php?file=images/mail.png) no-repeat 0 0';
			card.appendChild( mail );
		}

		card.appendChild( document.createElement( 'br' ) );

		if ( card_data[ 'sitio' ] )
		{
			var sitio = document.createElement( 'a' );
			sitio.target = '_blank';
			sitio.href = card_data[ 'sitio' ];
			sitio.appendChild( document.createTextNode( card_data[ 'sitio' ] ) );
			sitio.style.paddingLeft = '20px';
			sitio.style.background = 'url(templateFile.php?file=images/sitio.png) no-repeat 0 2px';
			card.appendChild( sitio );
		}

		var pageYLimit = document.body.scrollTop + document.body.clientHeight;
		var div = $('employeeInfo');

		div.innerHTML = '';
		div.appendChild( card );

		if ((parseInt(div.style.top.replace(/px/g, '')) + div.getHeight()) > pageYLimit)
			div.style.top = (parseInt(div.style.top.replace(/px/g, '')) - (div.getHeight() - 50)) + 'px';
		else
			div.style.top = ( parseInt(div.style.top.replace(/px/g, '')) - 50 ) + 'px';

		div.show();
	}
	cExecute('$this.bo_userinterface.getEmployeeInfo', resultGetEmployeeInfo, 'funcionario_id=' + employeeID);
}

function hideEmployeeInfo()
{
	if (workflowUserInterfaceEmployeeInfoTimer != null)
	{
		workflowUserInterfaceEmployeeInfoTimer = clearTimeout(workflowUserInterfaceEmployeeInfoTimer);
		workflowUserInterfaceEmployeeInfoTimer = null;
	}
	workflowUserInterfaceEmployeeInfoTimer = setTimeout( "$('employeeInfo').hide()", 1000 );
}

function getAreaInfoTimer(e, areaID)
{
	var div = $('areaInfo');
	div.style.left = (Event.pointerX(e) + 20) + 'px';
	div.style.top = (Event.pointerY(e) + 14) + 'px';

	if (workflowUserInterfaceAreaInfoTimer != null)
	{
		workflowUserInterfaceAreaInfoTimer = clearTimeout(workflowUserInterfaceAreaInfoTimer);
		workflowUserInterfaceAreaInfoTimer = null;
	}

	workflowUserInterfaceAreaInfoTimer = setTimeout('getAreaInfo(' + areaID + ')', 500);
}

function getAreaInfo(areaID)
{
	function resultGetAreaInfo(data)
	{
		if (workflowUserInterfaceAreaInfoTimer == null)
			return;

		workflowUserInterfaceAreaInfoTimer = clearTimeout(workflowUserInterfaceAreaInfoTimer);
		workflowUserInterfaceAreaInfoTimer = null;

		var content = '';
		content += '<table><tr>';
		content += '<td valign="top" style="padding-left: 12px;">';
		for (var i = 0; i < data['info'].length; i++)
			content += '<strong>' + data['info'][i]['name'] + '</strong>: ' + data['info'][i]['value'] + '<br/>';

		content += '</td></tr></table>';
		var pageYLimit = document.body.scrollTop + document.body.clientHeight;
		var div = $('areaInfo');
		div.innerHTML = content;

		if ((parseInt(div.style.top.replace(/px/g, '')) + div.getHeight()) > pageYLimit)
			div.style.top = (parseInt(div.style.top.replace(/px/g, '')) - (div.getHeight())) + 'px';
		div.show();
	}
	cExecute('$this.bo_userinterface.getAreaInfo', resultGetAreaInfo, 'area_id=' + areaID);
}

function hideAreaInfo()
{
	if (workflowUserInterfaceAreaInfoTimer != null)
	{
		workflowUserInterfaceAreaInfoTimer = clearTimeout(workflowUserInterfaceAreaInfoTimer);
		workflowUserInterfaceAreaInfoTimer = null;
	}
	$('areaInfo').hide();
}

function callVoipConnect( phoneNumber )
{
	var handler_connectVoip = function(data){
		if(!data) {
			alert("Error contacting VoIP server.");
		}
		else{
			alert("Requesting a VoIP call"+":\n"+data);
		}
	}

	cExecute( '$this.bo_userinterface.callVoipConnect&to='+phoneNumber+"&typePhone=com", handler_connectVoip );
}
