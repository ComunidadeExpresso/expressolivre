<?php
/**********************************************************************************************
 * AJAX Spell Checker - Version 2.8
 * (C) 2005 - Garrison Locke
 *
 * This spell checker is built in the style of the Gmail spell
 * checker.  It uses AJAX to communicate with the backend without
 * requiring the page be reloaded.  If you use this code, please
 * give me credit and a link to my site would be nice.
 * http://www.broken-notebook.com.
 *
 * Copyright (c) 2005, Garrison Locke
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *   * Neither the name of the http://www.broken-notebook.com nor the names of its
 *     contributors may be used to endorse or promote products derived from this
 *     software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
 * OF SUCH DAMAGE.
 *
 ***********************************************************************************************/

// User-configurable list of allowed HTML tags and attributes.
// Thanks to Jake Olefsky for this little addition
//$allowed_html = '<strong><small><p><br /><a><b><u><i><img><code><ul><ol><li>';  //Removed. Accept alll HTML tags.

// Set the max number of suggestions to return at a time.
define('MAX_SUGGESTIONS', 50);

// Set whether to use a personal dictionary.
$usePersonalDict = false;

//Set whether users are allowed to update the personal dictionary.
$editablePersonalDict = false;

// If using a personal dictionary, set the path to it.  Default is in the
// personal_dictionary subdirectory of the location of spell_checker.php.
$path_to_personal_dictionary = dirname(__FILE__) . "/personal_dictionary/personal_dictionary.txt";

//If pspell doesn't exist, then include the pspell wrapper for aspell.
if(!function_exists('pspell_suggest'))
{
	// Set the path to aspell if you need to use it.
	define('ASPELL_BIN','/usr/bin/aspell');
	require_once ("pspell_comp.php");
}

// Create and configure a link to the pspell module.

//$pspell_config = pspell_config_create("en");
pspell_config_mode($pspell_config, PSPELL_FAST);

if($usePersonalDict)
{
	// Allows the use of a custom dictionary (Thanks to Dylan Thurston for this addition).
	pspell_config_personal($pspell_config, $path_to_personal_dictionary);
}

//$pspell_link = pspell_new_config($pspell_config);


require_once("cpaint/cpaint2.inc.php"); //AJAX library file

$cp = new cpaint();
$cp->register('showSuggestions');
$cp->register('spellCheck');
$cp->register('switchText');
$cp->register('addWord');
$cp->start();
$cp->return_data();


/*************************************************************
 * showSuggestions($word, $id)
 *
 * The showSuggestions function creates the list of up to 10
 * suggestions to return for the given misspelled word.
 *
 * $word - The misspelled word that was clicked on
 * $id - The id of the span containing the misspelled word.
 *
 *************************************************************/
function showSuggestions($word, $id, $language)
{
	global $editablePersonalDict; //bool to set editability of personal dictionary
	//global $pspell_link; //the global link to the pspell module
        $pspell_link = pspell_new($language);
	global $cp; //the CPAINT object

	$retVal = "";


        $suggestions = pspell_suggest($pspell_link, $word);  //an array of all the suggestions that psepll returns for $word.
        $orig = metaphone($word);

        $final = array();

        foreach($suggestions as $suggestion){
          if(metaphone($suggestion) == $orig){
              array_unshift($final, $suggestion);
          } else {
              array_push($final, $suggestion);
          }
        }

        $suggestions = $final;

	// If the number of suggestions returned by pspell is less than the maximum
	// number, just use the number of suggestions returned.
	$numSuggestions = count($suggestions);
	$tmpNum = min($numSuggestions, MAX_SUGGESTIONS);

	if($numSuggestions > 0)
	{
		//this creates the table of suggestions.
		//in the onclick event it has a call to the replaceWord javascript function which does the actual replacing on the page
		foreach($suggestions as $suggestion)
		{
			$retVal .= "<div style class=\"suggestion\" onclick=\"replaceWord('" . addslashes_custom($id) . "', '" . addslashes(utf8_encode($suggestion)) . "'); return false;\">" . utf8_encode($suggestion) . " </div>";
		}

		if($editablePersonalDict)
		{
			$retVal .= "<div class=\"addtoDictionary\" onclick=\"addWord('" . addslashes_custom($id) . "'); return false;\">Add To Dictionary</div>";
		}

                //Ignore the suggestion - Added by Nathalie
                $retVal .= "<div class=\"ignore\" onclick=\"ignore('" .  addslashes_custom($id)  . "', '" .   addslashes($word)   . "'); return false;\"> Ignorar </div>";


	}
	else
	{
		$retVal .= "Sem sugestão";
                $retVal .= "<div class=\"ignore\" onclick=\"ignore('" .  addslashes_custom($id)  . "', '" .   addslashes($word)   . "'); return false;\"> Ignorar </div>";
	}

	$cp->set_data($retVal);  //the return value - a string containing the table of suggestions.

} // end showSuggestions


/*************************************************************
 * spellCheck($string)
 *
 * The spellCheck function takes the string of text entered
 * in the text box and spell checks it.  It splits the text
 * on anything inside of < > in order to prevent html from being
 * spell checked.  Then any text is split on spaces so that only
 * one word is spell checked at a time.  This creates a multidimensional
 * array.  The array is flattened.  The array is looped through
 * ignoring the html lines and spell checking the others.  If a word
 * is misspelled, code is wrapped around it to highlight it and to
 * make it clickable to show the user the suggestions for that
 * misspelled word.
 *
 * $string - The string of text from the text box that is to be
 *           spell checked.
 *
 *************************************************************/
function spellCheck($string, $varName, $language, $browser)
{
        //replace href for href# to block the redirection - Nathalie
        $string = str_replace("href=\"", "href=\"#", $string);
        $string = str_replace("href='", "href='#", $string);

        $string = utf8_decode($string);

        //global $pspell_link; //the global link to the pspell module
        $pspell_link = pspell_new($language);
	global $cp; //the CPAINT object
	$retVal = "";

   	$string = stripslashes_custom($string); //we only need to strip slashes if magic quotes are on

   	$string = remove_word_junk($string);

        //make all the returns in the text look the same
	$string = preg_replace("/\r?\n/", "\n", $string);

   	//splits the string on any html tags, preserving the tags and putting them in the $words array
   	$words = preg_split('/(<[^<>]*>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);

   	$numResults = count($words); //the number of elements in the array.

	$misspelledCount = 0;


        /* There is a problem with innerHTML of IE browsers - It removes the spaces.
         * If there are two misspelled words side by side, it makes then together (no space
         * between then). This will be checked using this variable.
         * */
        $difference = 0;

	//this loop looks through the words array and splits any lines of text that aren't html tags on space, preserving the spaces.
	for($i=0; $i<$numResults; ++$i){
		// Words alternate between real words and html tags, starting with words.
		if(($i & 1) == 0) // Even-numbered entries are word sets.
		{
			$words[$i] = preg_split('/(\s+|\&nbsp;)/', $words[$i], -1, PREG_SPLIT_DELIM_CAPTURE); //then split it on the spaces

			// Now go through each word and link up the misspelled ones.
			$numWords = count($words[$i]);
			for($j=0; $j<$numWords; ++$j)
			{
                               // $word = utf8_decode($words[$i][$j]);
                                $word = $words[$i][$j];
                                $reg_expr = utf8_decode('A-ZáàâãäéèêëíìïîóòôõöúùûüýÿçñÁÀÂÃÄÉÈÊËÍÌÏÎÓÒÔÕÖÚÙÛÜÝÇÑ');

                               // $reg_expr = 'A-ZáàâãäéèêëíìïîóòôõöúùûüýÿçñÁÀÂÃÄÉÈÊËÍÌÏÎÓÒÔÕÖÚÙÛÜÝÇÑ';
                                preg_match("/[$reg_expr]*/i", $word , $tmp); //get the word that is in the array slot $i

                                $tmpWord = $tmp[0]; //should only have one element in the array anyway, so it's just assign it to $tmpWord
                        //        $words[$i][$j] = utf8_decode($words[$i][$j]);
                                //
                                //And we replace the word in the array with the span that highlights it and gives it an onClick parameter to show the suggestions.
				if(!pspell_check($pspell_link, $tmpWord)) // Adicionar Nathalie
				{
                                        $onClick = "onclick=\"setCurrentObject(" . $varName . "); showSuggestions('" . addslashes($tmpWord) . "', '" . $varName . "_" . $misspelledCount . "_" . addslashes($tmpWord) . "'); return false;\"";

                                        $difference =  $misspelledCount + 1;
                                        if (($difference - $misspelledCount) == 1 and $browser == "ie") //if IE, adds a html space before Span tag
                                            $words[$i][$j] = str_replace($tmpWord, "<span " . $onClick . " id=\"" . $varName . "_" . $misspelledCount . "_" . $tmpWord . "\" class=\"highlight\">" . stripslashes($tmpWord) . "</span>", $words[$i][$j]);
                                        else
                                            $words[$i][$j] = str_replace($tmpWord, "<span " . $onClick . " id=\"" . $varName . "_" . $misspelledCount . "_" . $tmpWord . "\" class=\"highlight\">" . stripslashes($tmpWord) . "</span>", $words[$i][$j]); 

                                        ++$misspelledCount;

				}

				$words[$i][$j] = str_replace("\n", "<br />", $words[$i][$j]); //replace any breaks with <br />'s, for html display

			}//end for $j
		}//end if

		else //otherwise, we wrap all the html tags in comments to make them not displayed
		{
			$words[$i] = str_replace("<", "<!--<", $words[$i]);
			$words[$i] = str_replace(">", ">-->", $words[$i]);
		}
	}//end for $i

	$words = flattenArray($words); //flatten the array to be one dimensional.
	$numResults = count($words); //the number of elements in the array after it's been flattened.

	$string = ""; //return string

	//if there were no misspellings, start the string with a 0.
	if($misspelledCount == 0)
	{
		$string = "0";
	}

	else //else, there were misspellings, start the string with a 1.
	{
   		$string = "1";
   	}

	// Concatenate all the words/tags/etc. back into a string and append it to the result.
	$string .= implode('', $words);

        $string = preg_replace("/<!--</i", "<", $string);  //Retira os comentários das tags HTML
        $string = preg_replace("/>-->/i", ">", $string);

	//$cp->set_data(utf8_encode($string));  //return value - string containing all the markup for the misspelled words.
        //$cp->basenode->set_enconding('ISO-8859-1');

       // echo $string;
        $cp->set_encoding('ISO-8859-1');
        $cp->set_data($string);

} // end spellCheck


/*************************************************************
 * addWord($str)
 *
 * This function adds a word to the custom dictionary
 *
 * @param $str The word to be added
 *************************************************************/
function addWord($str)
{
	global $editablePersonalDict;
	//global $pspell_link; //the global link to the pspell module
        $pspell_link = pspell_new("pt_BR");
	global $cp; //the CPAINT object
	$retVal = "";
	pspell_add_to_personal($pspell_link, $str);
	if($editablePersonalDict && pspell_save_wordlist($pspell_link))
	{
		$retVal = "Save successful!";
	}

	else
	{
		$retVal = "Save Failed!";
	}

	$cp->set_data($retVal);
} // end addWord



/*************************************************************
 * flattenArray($array)
 *
 * The flattenArray function is a recursive function that takes a
 * multidimensional array and flattens it to be a one-dimensional
 * array.  The one-dimensional flattened array is returned.
 *
 * $array - The array to be flattened.
 *
 *************************************************************/
function flattenArray($array)
{
	$flatArray = array();
	foreach($array as $subElement)
	{
    	if(is_array($subElement))
		{
			$flatArray = array_merge($flatArray, flattenArray($subElement));
		}
		else
		{
			$flatArray[] = $subElement;
		}
	}

	return $flatArray;
} // end flattenArray


/*************************************************************
 * stripslashes_custom($string)
 *
 * This is a custom stripslashes function that only strips
 * the slashes if magic quotes are on.  This is written for
 * compatibility with other servers in the event someone doesn't
 * have magic quotes on.
 *
 * $string - The string that might need the slashes stripped.
 *
 *************************************************************/
function stripslashes_custom($string)
{
	if(get_magic_quotes_gpc())
	{
		return stripslashes($string);
	}
	else
	{
		return $string;
	}
} // end stripslashes_custom

/*************************************************************
 * addslashes_custom($string)
 *
 * This is a custom addslashes function that only adds
 * the slashes if magic quotes are off.  This is written for
 * compatibility with other servers in the event someone doesn't
 * have magic quotes on.
 *
 * $string - The string that might need the slashes added.
 *
 *************************************************************/
function addslashes_custom($string)
{
	if(!get_magic_quotes_gpc())
	{
		return addslashes($string);
	}
	else
	{
		return $string;
	}
} // end addslashes_custom


/*************************************************************
 * remove_word_junk($t)
 *
 * This function strips out all the crap that Word tries to
 * add to it's text in the even someone pastes in code from
 * Word.
 *
 * $t - The text to be cleaned
 *
 *************************************************************/
function remove_word_junk($t)
{
	$a=array(
	"\xe2\x80\x9c"=>'"',
	"\xe2\x80\x9d"=>'"',
	"\xe2\x80\x99"=>"'",
	"\xe2\x80\xa6"=>"...",
	"\xe2\x80\x98"=>"'",
	"\xe2\x80\x94"=>"---",
	"\xe2\x80\x93"=>"--",
	"\x85"=>"...",
	"\221"=>"'",
	"\222"=>"'",
	"\223"=>'"',
	"\224"=>'"',
	"\x97"=>"---",
	"\x96"=>"--"
	);

	foreach($a as $k=>$v){
		$oa[]=$k;
		$ra[]=$v;
	}

	$t=trim(str_replace($oa,$ra,$t));
	return $t;

} // end remove_word_junk


/*************************************************************
 * switchText($string)
 *
 * This function prepares the text to be sent back to the text
 * box from the div.  The comments are removed and breaks are
 * converted back into \n's.  All the html tags that the user
 * might have entered that aren't on the approved list:
 * <p><br /><a><b><strong><i><small><ul><li> are stripped out.
 * The user-entered returns have already been replaced with
 * $u2026 so that they can be preserved.  I replace all the
 * \n's that might have been added by the browser (Firefox does
 * this in trying to pretty up the HTML) with " " so that
 * everything will look the way it did when the user typed it
 * in the box the first time.
 *
 * $string - The string of html from the div that will be sent
 *           back to the text box.
 *
 *************************************************************/
function switchText($string)
{

        $string = utf8_decode($string);

        global $allowed_html;
	global $cp; //the CPAINT object
	$string = remove_word_junk($string);
	$string = preg_replace("/<!--/", "", $string);
	$string = preg_replace("/-->/", "", $string);
	$string = preg_replace("/\r?\n/", " ", $string);
	$string = stripslashes_custom($string); //we only need to strip slashes if magic quotes are on
	//$string = strip_tags($string, $allowed_html); //Removed. Accept all HTML tags.
	$string = preg_replace('{&lt;/?span.*?&gt;}i', '', $string);
        
        //$string = html_entity_decode($string);

        


        $cp->set_encoding('ISO-8859-1');
	$cp->set_data($string); //the return value


} // end switchText

?>
