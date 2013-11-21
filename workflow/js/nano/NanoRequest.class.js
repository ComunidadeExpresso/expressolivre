
// -----------------------------------------------------------------------------
// CLASS NanoRequest
function NanoRequest()
{
    /* Instance Variables */

    // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Public variables

    this.requestParameter = null;
    this.requestData      = null;

    this.escapeKeys       = false;
    this.escapeValues     = true;


	// #########################################################################
    // Privileged Method (has public access and can access private vars & funcs)

    this.autoLoadData        = _autoLoadRequestData;
    this.getAssembledRequest = _assembleRequestData;


	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// PRIVATE Methods

	function _autoLoadRequestData( form_id, unique_element_id_string, disabled_element_types )
	{
	    this.requestData = new Object();

        for( var i = 0; i < $(form_id).elements.length; i++ )
        {
            if( unique_element_id_string == ($(form_id).elements[i].id).substr(0,unique_element_id_string.length) )
            {
                this.requestData[_escapeKey($(form_id).elements[i].id)] = _getDataByElementType( $(form_id).elements[i].id );
            }
        }
	}

    function _assembleRequestData()
    {
        var tmp = new Object();

        if( null != this.requestParameter && undefined != this.requestParameter )
        {
            tmp['parameter'] = this.requestParameter;
        }

        if( null != this.requestData && undefined != this.requestData )
        {
            tmp['data'] = this.requestData;
        }

        return tmp;
    }

    function _getDataByElementType( id )
    {
        if( $(id) )
        {
            switch( $(id).type )
            {
                case 'hidden'     :
                case 'radio'      :
                case 'select-one' :
                case 'button'     : return $F(id);
                                    break;

                case 'password'   :
                case 'text'       :
                case 'textarea'   : return _escapeValue( $F(id) );
                                    break;

                case 'checkbox'   : return ( true == $(id).checked )
                                                ? $F(id).value
                                                : 0;
                                    break;

                default           : alert( 'Form field type: ' + $(id).type +
                                           ' is NOT defined!!!' );
            }
        }
        else
        {
            return '';
        }
    }

    function _escapeKey( data )
    {
        return ( true  == this.escapeKeys)
                    ? encodeURIComponent(data)
                    : data;
    }

    function _escapeValue( data )
    {
        return ( true  == this.escapeValues)
                    ? encodeURIComponent(data)
                    : data;
    }

}
