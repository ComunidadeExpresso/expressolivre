
// class NanoAjaxSelectBox
function NanoAjaxSelectBox( selectbox_id )
{
    // Pre Check: is
    if( selectbox_id == null || !$(selectbox_id) )
    {
        alert( 'No SelectBox ID given!!!\nTerminating!' );
        return;
    }

    // -------------------------------------------------------------------------
    // Private variables

    var _mObjSelectbox = $(selectbox_id);

	// #########################################################################
    // Privileged Method (has public access and can access private vars & funcs)
    this.fillSelectBoxByArray = _fillSelectBoxByArray;


	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// PRIVATE Methods

    function _fillSelectBoxByArray( data_array, fields_to_add, pre_entries, index_selected )
    {
        var is_selected    = false;
        var new_data_array = [];

        if( _mObjSelectbox.length > 0)
        {
            _deleteAllEntries();
        }

        if(pre_entries.length > 0)
        {
            for(var i=0;i<pre_entries.length;i++)
            {
                new_data_array.push(pre_entries[i]);
            }
            for(var i=0;i<data_array.length;i++)
            {
                new_data_array.push(data_array[i]);
            }
        }
        else
        {
            new_data_array = data_array;
        }

        for( i=0; i<new_data_array.length; i++ )
        {
            _addSelectBoxEntry( (fields_to_add.length == 0) ? new_data_array[i] : new_data_array[i][fields_to_add[0]],
                                (fields_to_add.length == 0) ? new_data_array[i] : new_data_array[i][fields_to_add[1]],
                                ((i == index_selected) ? true : false),
                                ((i == index_selected) ? true : false) );
        }
    }

    function _addSelectBoxEntry(value, desc, default_entry, is_selected)
    {
        _mObjSelectbox.options[_mObjSelectbox.length] = new Option(desc, value, default_entry, is_selected);
    }

    function _deleteAllEntries()
    {
        var i = (_mObjSelectbox.length-1);

        while( _mObjSelectbox.length > 0 )
        {
            _mObjSelectbox.options[i] = null;
            --i;
        }
    }

    function _deleteFirstEntry()
    {
        _mObjSelectbox.options[0] = null;
    }

    function _deleteLastEntry()
    {
        _mObjSelectbox.options[_mObjSelectbox.length - 1] = null;
    }
}
