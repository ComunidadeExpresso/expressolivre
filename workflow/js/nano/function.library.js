
// -----------------------------------------------------------------------------
// NanoAjax function library
//

String.prototype.repeat = function( number )
{
	return new Array( number + 1 ).join( this );
};

String.prototype.ucFirst = function ()
{
    return this.substr(0,1).toUpperCase() + this.substr(1,this.length);
};

String.prototype.nl2br = function ()
{
    return this.replace(/\n/g,'<br/>');
};

String.prototype.ltrim = function ()
{
    return (this.replace(/^\s+/,""));
};

String.prototype.rtrim = function ()
{
    return (this.replace(/\s+$/,""));
};

//combines "leftTrim" and "rightTrim";
String.prototype.trim = function ()
{
    return (this.replace(/\s+$/,"").replace(/^\s+/,""));
};

//removes n spaces in string to 1 (one) space
String.prototype.superTrim = function ()
{
    return(this.replace(/\s+/g," ").replace(/\s+$/,"").replace(/^\s+/,"")); //"
};

// removes all spaces from string
String.prototype.removeWhiteSpaces = function ()
{
    return (this.replace(/\s+/g,""));
};

// fills string with given char and count
String.prototype.fillChar = function ( fill_char, fill_count, fill_position )
{
    var fill_position = (fill_position) ? fill_position : 'left';
    var str_len       = this.length;
    var tmp_str       = this;

    if(str_len < fill_count)
    {
        while(str_len < fill_count)
        {
            tmp_str = ( 'left' == fill_position )
                            ? (fill_char + tmp_str)
                            : (tmp_str   + fill_char);

            ++str_len;
        }
    }

    return tmp_str;
};

// like PHP' in_array
Array.prototype.contains = function( element )
{
    for ( var idx = 0; idx < this.length; idx++ )
    {
         if( element == this[idx] )
         {
             return true;
         }
    }

    return false;
};
