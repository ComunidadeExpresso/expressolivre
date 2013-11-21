/**
* Show / Hide all employees
*/
function toggleEmployeesVisibility()
{
	if ($('#employeesVisibility').attr('checked'))
		$('tr.employees').show();
	else
		$('tr.employees').hide();
}


/**
* Highlight supervisor names?
*/
function toggleHighlightSupervisor()
{
	if ($('#highlightSupervisor').attr('checked'))
		$('span.employeesupervisor').css('font-weight', 'bold');
	else
		$('span.employeesupervisor').css('font-weight', 'normal');
}

/**
* Show / Hide orgchart area path visibility
*/
function toggleOrgchartPathVisibility()
{
	if ($('#orgchartPathVisibility').attr('checked'))
		$('span.orgchartPath').css('visibility', 'visible').show();
	else {
		if ($('#orgchartPathIndentation').attr('checked'))
			$('span.orgchartPath').show().css('visibility', 'hidden');
		else
			$('span.orgchartPath').hide();
	}
}

/**
* Group by area or show a single list alphabetically ordered
* For large sets of data this function may be potencially slow
*/
function toggleGroupByArea()
{
	/* remove the table and compute it again */
	$('#employee_table').remove();

	if ($('#groupByArea').attr('checked'))
		showGroupedByArea();
	else
		showUngrouped();

	/* updating supervisor highlight and orgchart path visibility */
	toggleHighlightSupervisor();
	toggleOrgchartPathVisibility();
}


/**
* Show / Hide all photo employees
*/
function togglePhotoVisibility()
{
	$('#employee_table').remove();

	if ($('#groupByArea').attr('checked'))
		showGroupedByArea();
	else
		showUngrouped();
}

/**
* Centralize the creation of table rows for employees.
* 'showAreaColumn' specifies whether the second column will be shown
*/
function createEmployeeRow(area_id, user_id, showAreaColumn, showUserPhoto)
{
	/* set a special 'class' if the employee is a supervisor one */
	class_name = 'employee';
	if (areas[area_id].titular_funcionario_id == areas[area_id].employees[user_id].funcionario_id)
		class_name += 'supervisor';

	/* creating the row. */
	element = $('<tr></tr>');


	/* photo: zero (optional) column */
	if (showUserPhoto){
		var content = '<img src="workflow/showUserPicture.php?userID=' + areas[area_id].employees[user_id].funcionario_id + '"/>';
		element.append($('<td valign="top">' + content + '</td>').css('width', '8%'));
	}

	/* name: first column */
	element.append(
				$('<td valign="top"></td>')
					.append(
						$('<span></span>')
							.addClass(class_name)
							.append(areas[area_id].employees[user_id].cn)
						)
							.css('width', '23.5%')
			);

	/* area: second (optional) column */
	if (showAreaColumn)
		element.append(
						$('<td>' + areas[area_id].sigla + '</td>')
					);

	/* login: show uid attribute */
	element.append(
					$('<td valign="top">' + areas[area_id].employees[user_id].uid + '</td>')
						.css('width', '11%')
					)
					
	
	/* telephone: last column */
	element.append(
					$('<td valign="top">' + areas[area_id].employees[user_id].telephoneNumber + '</td>')
						.css('width', '9.75%')
					)
					
	/* Vínculo: show cargo vínculo */
	element.append(
					$('<td valign="top">' + areas[area_id].employees[user_id].vinculo + '</td>')
						.css('width', '9%')
					)

	/* Cargo: show cargo attribute */
	element.append(
					$('<td valign="top">' + areas[area_id].employees[user_id].cargo + '</td>')
						.css('width', '12.5%')
					)
					
		/* data_admissao: show data_admissao attribute */
	element.append(
					$('<td valign="top">' + areas[area_id].employees[user_id].data_admissao + '</td>')
						.css('width', '6.75%')
					)
					
	/* Funcao: show funcao attribute */
	element.append(
				$('<td valign="top"> ' + areas[area_id].employees[user_id].funcao + ' </td>')
						.css('width', '26%')
					)


				.addClass('employees');

	return element;
}

/**
* Creating a employee table grouped by area
*/
function showGroupedByArea()
{
	var table = $('<table></table>').css('width', '90%').attr('id', 'employee_table');
	var i, j, photo;

	if ($('#photoVisibility').attr('checked'))
	   photo = true;
	else
	   photo = false;

	/* iterating over areas */
	for (i=0; i < areas.length; i++) {

		/* inserting area header */
		table.append(
				$('<tr></tr>')
				.append(
					$('<td colspan="2"></td>')
						.css('font-weight', 'bold')
						.css('text-align', 'left')
						.css('height', '30')
						.append(
							$('<span></span>')
								.addClass('orgchartPath')
								.append(areas[i].orgchartPath)
								)
						.append(areas[i].sigla)
					)
		);

		/* creating employee rows */
		for (j=0; j < areas[i].employees.length; j++)
			table.append(createEmployeeRow(i, j, false, photo));
	}
	$('#areas_content').append(table);
}

/**
* Creating employess ordered alphabetically and ungrouped. In this
* function we implemented a 'merge' of all area's employee arrays.
*
* Be careful if you are going to update this code... =)
*/
function showUngrouped()
{
	var table = $('<table></table>').css('width', '90%').attr('id', 'employee_table');
	var i, less, end, photo;

	if ($('#photoVisibility').attr('checked'))
	   photo = true;
	else
	   photo = false;

	/* creating and reseting indexes */
	for (i=0; i < areas.length; i++)
		areas[i].index = 0;

	/* */
	while (true) {
		less = -1;
		end = true;

		/* searching the area with smallest employee name */
		for (i=0; i < areas.length; i++) {

			/* if this area have employees left */
			if (areas[i].employees.length > areas[i].index) {

				/* if it's the first area reached in this iteration */
				if (less == -1)
					less = i;

				/* updating less */
				if (areas[i].employees[areas[i].index].cn < areas[less].employees[areas[less].index].cn)
					less = i;

				/* so, we are not done */
				end = false;
			}
		}
		/* if we are done */
		if (end) break;

		/* inserting the row */
		table.append(createEmployeeRow(less, areas[less].index, true, photo));
		areas[less].index++;
	}
	$('#areas_content').append(table);
}

/**
* Print me!
*/
function printAction()
{
	window.print();
}

/**
* Binding events to HTML elements
*/
function bindEvents()
{
	$('#employeesVisibility').click(toggleEmployeesVisibility);
	$('#photoVisibility').click(togglePhotoVisibility);
	$('#groupByArea').click(toggleGroupByArea);
	$('#highlightSupervisor').click(toggleHighlightSupervisor);
	$('#orgchartPathVisibility').click(toggleOrgchartPathVisibility);
	$('#printButton').click(printAction);
}

function initialSetup()
{
	toggleGroupByArea();
	togglePhotoVisibility();
	toggleEmployeesVisibility();
	toggleHighlightSupervisor();
	toggleOrgchartPathVisibility();
}

/**
* Call setup functions on body onload.
*/
function pageLoad()
{
	bindEvents();
	initialSetup();
}

$(window).load(pageLoad);
