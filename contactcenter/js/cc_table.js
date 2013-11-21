  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	/* ContactCenter Table - Provides visualization in Table Mode */

	function ccEntriesTable (params)
	{
		this.table = newTable('ccTable_table');
		this.columns = Array();
		this.rows = Array();

		this.actualIndex = 'a';
		this.actualLetter = 1;

		this.table.setSelectionVisible(true);

		if (params['fields'])
		{
			for (var i in params['fields'])
			{
				this._addColumn(params['fields'][i]);
			}
		}
	}

	ccEntriesTable.prototype.showEntries = function (index, page, extra)
	{
		
	}
	
	ccEntriesTable.prototype.addEntry = function ()
	{
	}

	ccEntriesTable.prototype.removeSelectedEntries = function ()
	{
		var selected = this.table.getRowsBySelection();

		for (var i in selected)
		{
		}
	}

	/****************************************************************************\
	 *                          Private Methods                                 *
	\****************************************************************************/
	
	// TODO: Check for errors
	ccEntriesTable.prototype._addColumn = function (params)
	{
		var type;

		switch (params['type'])
		{
			case 'number':
				type = 'numericCompare';
				break;

			case 'text':
				type = 'lexicalCompare';
				break;
		}
		
		this.columns[this.columns.length] = this.table.newTitle(params['caption'], type);
	}

	ccEntriesTable.prototype._addRow = function (params)
	{
		var row = this.table.newRow();
		
		row.table
		for (var i in params)
		{
			row.newCell().addText(params[i]);
		}
		
		this.rows[this.rows.length] = row;
	}

