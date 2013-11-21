function checkError(data)
{
	if (data['error'])
	{
		alert(data['error']);
		return true;
	}

	return false;
}

function buildLayout()
{
	var content = '';
	content += '<table><tr><td valign="top"><select size="10" class="externalApplicationList" id="externalApplications" onchange="getExternalApplication(this.value);"></select>';
	content += '<br/><button onclick="clearApplicationData();">Novo</button>';
	content += '&nbsp;&nbsp;&nbsp;&nbsp;<button onclick="removeExternalApplication($F(\'externalApplications\'));">Excluir</button></td>';
	content += '<td valign="top"><div id="externalApplicationData" style="display: none;"><table>';
	content += '<tr><td><label for="name">Nome</label></td><td><input type="text" id="name" size="30" /></td></tr>';
	content += '<tr><td><label for="address">Endereço</label></td><td><input type="text" id="address" size="50" /></td></tr>';
	content += '<tr><td><label for="description">Descrição</label></td><td><textarea id="description" cols="40" rows="3"></textarea></td></tr>';
	content += '<tr><td valign="top" colspan="2"><label><input type="checkbox" id="intranet_only");"/>Acessível somente na Intranet?</label></td></tr>';
	content += '<tr><td valign="top"><label><input type="checkbox" id="authentication" onclick="if (this.checked) Effect.BlindDown(\'post_div\'); else Effect.BlindUp(\'post_div\');"/>Autentica?</label></td><td><div id="post_div""><label for="post">Post</label><br/><textarea id="post" cols="40" rows="3"></textarea></div></td></tr>';
	content += '<tr><td><label for="image">Imagem</label></td>';
	content += '<td><form action="' + _web_server_url + '/index.php?menuaction=workflow.ui_external_applications.upload_image" target="file_iframe" method="post" enctype="multipart/form-data" name="image_tmp_form" id="image_tmp_form"><input type="file" name="image_tmp" id="image_tmp"/></form>';
	content += '<div id="image_div"><label><input type="checkbox" id="remove_current_image">Remover imagem atual</label><br/><img id="current_image" src=""/></div>';
	content += '</td></tr>';
	content += '<tr><td colspan="2"><button id="buttonSave" onclick="sendContents();">Salvar</button></td></tr>';
	content += '<tr><td><label for=""></label></td><td></td></tr>';
	content += '</table></div>';
	content += '</td></tr></table>';
	content += '<input type="hidden" id="image" value=""/>';
	content += '<input type="hidden" id="external_application_id" value=""/>';

	content += '<iframe name="file_iframe" style="display: none;"></iframe>';
	$('conteudo').innerHTML = content;
	$('post_div').hide();
	$('image_div').hide();
	loadExternalApplications();
}

function clearApplicationData()
{
	$('name').value = '';
	$('description').value = '';
	$('address').value = '';
	$('authentication').checked = false;
	$('post').value = '';
	$('external_application_id').value = '';
	$('image').value = '';
	$('image_tmp').value = '';
	$('current_image').src = '';
	$('remove_current_image').checked = false;
	$('post_div').hide();
	$('image_div').hide();

	if (!$('externalApplicationData').visible())
		Effect.BlindDown('externalApplicationData');
}

function getExternalApplication(externalApplicationID)
{
	function resultGetExternalApplication(data)
	{
		if (checkError(data))
			return;

		if (!data)
			return;

		clearApplicationData();
		$('name').value = data['name'];
		$('description').value = data['description'];
		$('address').value = data['address'];
		$('intranet_only').checked = (data['intranet_only'] == '1')? true : false;
		$('authentication').checked = (data['authentication'] == '1')? true : false;
		if ($('authentication').checked)
			$('post_div').show();
		else
			$('post_div').hide();
		$('post').value = data['post'];
		$('external_application_id').value = data['external_application_id'];
		if (data['image'])
		{
			$('current_image').src = _web_server_url + '/workflow/redirect.php?file=/external_applications/' + data['image'];
			$('image_div').show();
		}
	}

	cExecute('$this.bo_external_applications.getExternalApplication', resultGetExternalApplication, 'external_application_id=' + externalApplicationID);
}

function loadExternalApplications()
{
	function resultLoadExternalApplications(data)
	{
		var output = '';
		for (var i = 0; i < data.length; i++)
			output += '<option value="' + data[i]['external_application_id'] + '">' + data[i]['name'] + '</option>';
		var obj = $('externalApplications');

		/* IE MAGIC */
		if (obj.outerHTML)
		{
			obj.innerHTML = '';
			obj.outerHTML = obj.outerHTML.match(/<select[^>]*>/gi) + output + '</select>';
		}
		else
			obj.innerHTML = output;
	}

	cExecute('$this.bo_external_applications.getExternalApplications', resultLoadExternalApplications);
}

function sendContents()
{
	if ($F('name') == '')
	{
		alert('É necessário informar um nome para a aplicação externa.');
		return;
	}

	if ($F('address') == '')
	{
		alert('É necessário informar um endereço para a aplicação externa.');
		return;
	}

	var address = $F('address');

	if (address.match(/^[a-zA-Z]+:\/\//) == null)
	{
		alert('Aparentemente a URL informada não está formatada corretamente.\nTente utilizar o endereço completo. Exemplo:\nhttp://expresso.pr.gov.br');
		return;
	}

	function resultSendContents(data)
	{
		if (checkError(data))
			return;

		Effect.BlindUp('externalApplicationData');
		loadExternalApplications();
	}

	if (($F('image_tmp') != '') && ($F('image') == ''))
		$('image_tmp_form').submit();
	else
	{
		var params = 'name=' + escape($F('name'));
		params += '&description=' + escape($F('description'));
		params += '&address=' + escape($F('address'));
		params += '&intranet_only=' + (($F('intranet_only') == 'on') ? '1' : '0');
		params += '&authentication=' + (($F('authentication') == 'on') ? '1' : '0');
		params += '&post=' + escape($F('post'));
		params += '&image=' + $F('image');
		if ($F('external_application_id') != '') // update
			cExecute('$this.bo_external_applications.updateExternalApplication', resultSendContents, params + '&external_application_id=' + $F('external_application_id') + '&remove_current_image=' + (($F('remove_current_image') == 'on') ? '1' : '0'));
		else //insert
			cExecute('$this.bo_external_applications.addExternalApplication', resultSendContents, params + '&external_application_id=' + $F('external_application_id'));
	}
}

function removeExternalApplication(externalApplicationID)
{
	if (externalApplicationID == '')
		return;

	function resultRemoveExternalApplication(data)
	{
		if (checkError(data))
			return;

		loadExternalApplications();
	}

	if (confirm('Tem certeza que deseja excluir a Aplicação Externa selecionada?'))
	{
		cExecute('$this.bo_external_applications.removeExternalApplication', resultRemoveExternalApplication, 'external_application_id=' + externalApplicationID);
		Effect.BlindUp('externalApplicationData');
	}
}

Event.observe(window, 'load', function() {
	buildLayout();
});
