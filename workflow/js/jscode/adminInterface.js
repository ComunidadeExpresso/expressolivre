function toggleTableVisibility(tableID)
{
	var rows = document.getElementById(tableID).rows;
	var visibility = (rows[1].style.display == '') ? 'none' : '';
	for (var i = 1; i < rows.length; i++)
		rows[i].style.display = visibility;
	document.getElementById('toggleLink').innerHTML = (visibility == 'none') ? 'Expandir' : 'Contrair';
}

function toggleDefaultUserVisibility(divIndex)
{
	document.getElementById('div_default_user_option_0').style.display = (divIndex==0)? 'block':'none';
	document.getElementById('div_default_user_option_1').style.display = (divIndex==1)? 'block':'none';

	if (divIndex == 1){
		document.getElementById('default_user_temp').value = document.getElementById('default_user').value;
		document.getElementById('default_user').value = document.getElementById('default_roles').value;
	} else if (divIndex == 0){
		document.getElementById('default_user').value = document.getElementById('default_user_temp').value;
	}
}
