# $.store jQuery plugin #

<code>$.store</code> is a simple, yet easily extensible, plugin to persistently store data on the client side of things. It uses <code>window.localStore</code> where available. Older Internet Explorers will use <code>userData</code>. If all fails <code>$.store</code> will save your data to <code>window.name</code>.

*Note*: The <code>windowName</code> will only do JSON serialization. <code>windowName</code> is not persistent in the sense of making it accross a closed browser window. If you need that ability you should check <code>$.storage.driver.scope == "browser"</code>.

## Usage ##

<pre><code>
//initialize
$.storage = new $.store();

// save a value
$.storage.set( key, value );

// read a value
$.storage.get( key );

// deletes a value
$.storage.del( key );

// delete all values
$.storage.flush();
</code></pre>

## License ##

$.store is published under the [MIT license](http://www.opensource.org/licenses/mit-license.php).