
var JSON =
{
    // transform [object/number/string/boolean] into JSON-String
    stringify: function (arg)
    {
        var c, i, l, s = '', v;

        switch (typeof arg)
        {
            case 'object':  if (arg)
                            {
                                if (arg.constructor == Array)
                                {
                                    for (i = 0; i < arg.length; ++i)
                                    {
                                        v = this.stringify(arg[i]);

                                        if (s)
                                        {
                                            s += ',';
                                        }

                                        s += v;
                                    }
                                    return '[' + s + ']';
                                }
                                else if (typeof arg.toString != 'undefined')
                                {
                                    for (i in arg)
                                    {
                                        v = arg[i];

                                        if (typeof v != 'undefined' && typeof v != 'function')
                                        {
                                            v = this.stringify(v);

                                            if (s)
                                            {
                                                s += ',';
                                            }
                                            s += this.stringify(i) + ':' + v;
                                        }
                                    }
                                    return '{' + s + '}';
                                }
                            }
                            return 'null';

            case 'number' : return isFinite(arg) ? String(arg) : 'null';

            case 'string' : l = arg.length;
                            s = '"';

                            for (i = 0; i < l; i += 1)
                            {
                                c = arg.charAt(i);
                                if (c >= ' ')
                                {
                                    if (c == '\\' || c == '"') {
                                        s += '\\';
                                    }
                                    s += c;
                                }
                                else
                                {
                                    switch (c)
                                    {
                                        case '\b':  s += '\\b';
                                                    break;

                                        case '\f':  s += '\\f';
                                                    break;

                                        case '\n':  s += '\\n';
                                                    break;

                                        case '\r':  s += '\\r';
                                                    break;

                                        case '\t':  s += '\\t';
                                                    break;

                                        default:    c  = c.charCodeAt();
                                                    s += '\\u00' + Math.floor(c / 16).toString(16) +
                                                        (c % 16).toString(16);
                                    }
                                }
                            }
                            return s + '"';

            case 'boolean': return String(arg);

            default       : return 'null';
        }
    },

    parse: function (text)
    {
        var at = 0;
        var ch = ' ';

        function error(m)
        {
            throw { name    : 'JSONError',
                    message : m,
                    at      : at - 1,
                    text    : text };
        }

        function next()
        {
            ch = text.charAt(at);
            at += 1;
            return ch;
        }

        function white()
        {
            while (ch)
            {
                if (ch <= ' ')
                {
                    next();
                }
                else if (ch == '/')
                {
                    switch (next())
                    {
                        case '/':   while (next() && ch != '\n' && ch != '\r') {}
                                    break;

                        case '*':   next();
                                    for (;;)
                                    {
                                        if (ch)
                                        {
                                            if (ch == '*')
                                            {
                                                if (next() == '/')
                                                {
                                                    next();
                                                    break;
                                                }
                                            }
                                            else
                                            {
                                                next();
                                            }
                                        }
                                        else
                                        {
                                            error("Unterminated comment");
                                        }
                                    }
                                    break;

                        default:    error("Syntax error");
                    }
                }
                else
                {
                    break;
                }
            }
        }

        function string()
        {
            var i, s = '', t, u;

            if (ch == '"')
            {
outer:          while (next())
                {
                    if (ch == '"')
                    {
                        next();
                        return s;
                    }
                    else if (ch == '\\')
                    {
                        switch (next())
                        {
                            case 'b':   s += '\b';
                                        break;

                            case 'f':   s += '\f';
                                        break;

                            case 'n':   s += '\n';
                                        break;

                            case 'r':   s += '\r';
                                        break;

                            case 't':   s += '\t';
                                        break;

                            case 'u':   u = 0;
                                        for (i = 0; i < 4; i += 1)
                                        {
                                            t = parseInt(next(), 16);
                                            if (!isFinite(t))
                                            {
                                                break outer;
                                            }
                                            u = u * 16 + t;
                                        }
                                        s += String.fromCharCode(u);
                                        break;

                            default:    s += ch;
                        }
                    }
                    else
                    {
                        s += ch;
                    }
                }
            }
            error("Bad string");
        }

        function array()
        {
            var a = [];

            if (ch == '[')
            {
                next();
                white();

                if (ch == ']')
                {
                    next();
                    return a;
                }

                while (ch)
                {
                    a.push(value());
                    white();

                    if (ch == ']')
                    {
                        next();
                        return a;
                    }
                    else if (ch != ',')
                    {
                        break;
                    }

                    next();
                    white();
                }
            }
            error("Bad array");
        }

        function object()
        {
            var k, o = {};

            if (ch == '{')
            {
                next();
                white();

                if (ch == '}')
                {
                    next();
                    return o;
                }

                while (ch)
                {
                    k = string();
                    white();

                    if (ch != ':')
                    {
                        break;
                    }

                    next();
                    o[k] = value();
                    white();

                    if (ch == '}')
                    {
                        next();
                        return o;
                    }
                    else if (ch != ',')
                    {
                        break;
                    }

                    next();
                    white();
                }
            }
            error("Bad object");
        }

        function number()
        {
            var n = '', v;

            if (ch == '-')
            {
                n = '-';
                next();
            }

            while (ch >= '0' && ch <= '9')
            {
                n += ch;
                next();
            }

            if (ch == '.')
            {
                n += '.';

                while (next() && ch >= '0' && ch <= '9')
                {
                    n += ch;
                }
            }

            if (ch == 'e' || ch == 'E')
            {
                n += 'e';
                next();

                if (ch == '-' || ch == '+')
                {
                    n += ch;
                    next();
                }

                while (ch >= '0' && ch <= '9')
                {
                    n += ch;
                    next();
                }
            }

            v = +n;

            if (!isFinite(v))
            {
                ////error("Bad number");
            }
            else
            {
                return v;
            }
        }

        function word()
        {
            switch (ch)
            {
                case 't':   if (next() == 'r' && next() == 'u' && next() == 'e')
                            {
                                next();
                                return true;
                            }
                            break;

                case 'f':   if (next() == 'a' && next() == 'l' && next() == 's' && next() == 'e')
                            {
                                next();
                                return false;
                            }
                            break;

                case 'n':   if (next() == 'u' && next() == 'l' && next() == 'l')
                            {
                                next();
                                return null;
                            }
                            break;
            }
            error("Syntax error");
        }

        function value()
        {
            white();

            switch (ch) {
                case '{': return object();

                case '[': return array();

                case '"': return string();

                case '-': return number();

                default : return ch >= '0' && ch <= '9' ? number() : word();
            }
        }

        return value();
    }
};
