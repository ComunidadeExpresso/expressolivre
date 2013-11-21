// -----------------------------------------------------------------------------
// Try these: tries various function for success
var Try =
{
    these: function()
    {
        var returnValue;

        for (var i = 0; i < arguments.length; i++)
        {
            var lambda = arguments[i];

            try
            {
                returnValue = lambda();
                break;
            }
            catch (e) {}
        }

        return returnValue;
    }
};

// -----------------------------------------------------------------------------
// $() function (wraps document.getElementById)
function $()
{
    var elements = new Array();

    for (var i = 0; i < arguments.length; i++)
    {
        var element = arguments[i];

        if (typeof element == 'string')
        {
            element = document.getElementById(element);
        }

        if (arguments.length == 1)
        {
            return element;
        }

        elements.push(element);
    }

    return elements;
}

// -----------------------------------------------------------------------------
// $F() function
function $F(id)
{
    return $(id).value;
}
