function editField(random, key)
{
	var tableCell = document.getElementById(random + '_' + key + '_td');
	tableCell.innerHTML = '<input type="text" id="' + random + '_' + key + '_edit" value="' + document.getElementById(random + '_' + key).value + '"/>';
	tableCell.nextSibling.innerHTML = '<a href="#" onclick="setField(\'' + random + '\', \'' + key + '\'); return false;">ok</a>';
	document.getElementById(random + '_' + key + '_edit').focus();
}

function setField(random, key)
{
	var tableCell = document.getElementById(random + '_' + key + '_td');
	document.getElementById(random + '_' + key).value = document.getElementById(random + '_' + key + '_edit').value;
	tableCell.innerHTML = document.getElementById(random + '_' + key + '_edit').value;
	tableCell.nextSibling.innerHTML = '<a href="#" onclick="editField(\'' + random + '\', \'' + key + '\'); return false;">editar</a>';
}

function toggleTableDisplay(tableElement)
{
	for (var i = 1; i < tableElement.rows.length; i++)
		tableElement.rows[i].style.display = (tableElement.rows[i].style.display == 'none') ? '' : 'none';
}
