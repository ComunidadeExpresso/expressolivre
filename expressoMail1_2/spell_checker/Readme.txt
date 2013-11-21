Version 2.8

If you use this code, please give me credit.  I'd also like to see where you're using it.  So send me urls!


See http://www.broken-notebook.com/spell_checker for the change log and further documentation.


==============================HOW TO USE==================================

Just unzip all the contents to the same directory.  You also need to make 
sure the personal_dictionary.txt file in the personal_dictionary directory 
is chmodded to 646.

Just include cpaint2.inc.compressed.js, spell_checker.js, and spell_checker.css
in the head of your page.

The spell checker can be added to any text area on the page.  I chose to use
the title and accesskey attributes instead of custom attributes for two reasons. 
One, they're not used very frequently, and two, so that the code would still be 
valid XHTML.

Just add the title attribute to your text area and make it equal to "spellcheck" 
and set the accesskey attribute equal to the location of the spell_checker.php 
file and the Javascript will do the rest.

Make sure you include a width and a height as well as a name and an id.
Name and id should be unique.

<textarea title="spellcheck" accesskey="spell_checker.php" id="spell_checker1" type="text" name="comment1" style="width: 400px; height: 200px;" />Text of box check</textarea>

==========================================================================


Note - This code requires php and the pspell module to work correctly.
It has only been tested on Firefox and Internet Explorer so far, and I'm
told that it works fine in Safari and some versions of Opera too.


If you find any bugs or anything please email me and let me know.

Garrison Locke

gplocke@broken-notebook.com

http://www.broken-notebook.com