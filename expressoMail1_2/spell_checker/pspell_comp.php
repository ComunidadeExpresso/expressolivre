 <?php
/**
 * pspell compatibility wrapper
 *
 * This library provides some of the functions from the pspell PHP extension
 * by wrapping them to calls to the aspell binary
 *
 * It can be simply dropped into code written for the pspell extension like
 * the following
 *
 * if(!function_exists('pspell_suggest')){
 *   require_once ("pspell_comp.php");
 * }
 *
 * Define the path to the aspell binary like this if needed:
 *
 * define('ASPELL_BIN','/path/to/aspell');
 *
 * @author   Andreas Gohr <andi@splitbrain.org>
 *
 * Copyright (c) 2005, Andreas Gohr
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
 */

if(!defined('ASPELL_BIN')) define('ASPELL_BIN','aspell');

define(PSPELL_FAST,1);         # Fast mode (least number of suggestions)
define(PSPELL_NORMAL,2);       # Normal mode (more suggestions)
define(PSPELL_BAD_SPELLERS,3); # Slow mode (a lot of suggestions) )

function pspell_config_create($language, $spelling=null, $jargon=null, $encoding='iso8859-1'){
    return new Pspell($language, $spelling, $jargon, $encoding);
}

function pspell_config_mode(&$config, $mode){
    return $config->setMode($mode);
}

function pspell_new_config(&$config){
    return $config;
}

function pspell_check(&$dict,$word){
    return $dict->check($word);
}

function pspell_suggest(&$dict, $word){
    return $dict->suggest($word);
}

/**
 * Class to provide pspell functionality through aspell
 *
 * Needs PHP >= 4.3.0
 */
class Pspell{
    var $language;
    var $spelling;
    var $jargon;
    var $encoding;
    var $mode=PSPELL_NORMAL;
    
    var $args='';

    /**
     * Constructor. Works like pspell_config_create()
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     * @todo     $spelling isn't used
     */
    function Pspell($language, $spelling=null, $jargon=null, $encoding='iso8859-1'){
        $this->language = $language;
        $this->spelling = $spelling;
        $this->jargon   = $jargon;
        $this->encoding = $encoding;
        $this->_prepareArgs();
    } 

    /**
     * Set the spelling mode like pspell_config_mode()
     *
     * Mode can be PSPELL_FAST, PSPELL_NORMAL or PSPELL_BAD_SPELLER
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     * @todo     check for valid mode
     */
    function setMode($mode){
        $this->mode = $mode;
        $this->_prepareArgs();
        return $mode;
    }

    /**
     * Prepares the needed arguments for the call to the aspell binary
     *
     * No need to call this directly
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     */
    function _prepareArgs(){
        $this->args = '';

        if($this->language){
            $this->args .= ' --lang='.escapeshellarg($this->language);
        }else{
            return false; // no lang no spell
        }

        #FIXME how to support spelling?

        if($this->$jargon){
            $this->args .= ' --jargon='.escapeshellarg($jargon);
        }

        if($this->$encoding){
            $this->args .= ' --encoding='.escapeshellarg($encoding);
        }

        switch ($this->mode){
            case PSPELL_FAST:
                $this->args .= ' --sug-mode=fast';
                break;
            case PSPELL_BAD_SPELLERS:
                $this->args .= ' --sug-mode=bad-spellers';
                break;
            default:
                $this->args .= ' --sug-mode=normal';
        }

        return true;
    }

    /**
     * Checks a word for correctness
     *
     * This opens a bidirectional pipe to the aspell binary, writes
     * the given word to STDIN and reads STDOUT for the result
     *
     * @returns array of suggestions on wrong spelling, or true on no spellerror
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     */
    function suggest($word){
        $out = '';
        $err = '';

        $word = trim($word);

        if(empty($word)) return true;

        //prepare file descriptors
        $descspec = array(
               0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
               1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
               2 => array('pipe', 'w')    // stderr is a file to write to
        );

        $process = proc_open(ASPELL_BIN.' -a'.$this->args, $descspec, $pipes);
        if (is_resource($process)) {
            //write to stdin
            fwrite($pipes[0],$word);
            fclose($pipes[0]);

            //read stdout
            while (!feof($pipes[1])) {
                $out .= fread($pipes[1], 8192);
            }
            fclose($pipes[1]);

            //read stderr
            while (!feof($pipes[2])) {
                $err .= fread($pipes[2], 8192);
            }
            fclose($pipes[2]);

            if(proc_close($process) != 0){
                //something went wrong
                trigger_error("aspell returned an error: $err", E_USER_WARNING);
                return array();
            }

            //parse output
            $lines = preg_split('/\n/',$out);
            foreach ($lines as $line){
                $line = trim($line);
                if(empty($line))    continue;       // empty line
                if($line[0] == '@') continue;       // comment
                if($line[0] == '*') return true;    // no mistakes made
                if($line[0] == '#') return array(); // mistake but no suggestions
                if($line[0] == '&'){
                    $line = preg_replace('/&.*?: /','',$line);
                    return preg_split('/, /',$line);
                }
            }
            return true; // shouldn't be reached
        }
        //opening failed
        trigger_error("Could not run aspell '".ASPELL_BIN."'", E_USER_WARNING);
        return array();
    }

    /**
     * Check if a word is misspelled like pspell_check
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     */
    function check($word){
        if(is_array($this->suggest($word))){
            return false;
        }else{
            return true;
        }
    }
}

?>