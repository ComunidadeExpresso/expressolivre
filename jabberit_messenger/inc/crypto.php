<?php
/***************************************************************************\
*  Expresso - Expresso Messenger                                            *
*      - Serge Rehem                                                        *
*      - JETI - http://jeti-im.org/                                         *
* ------------------------------------------------------------------------- *
*  This code is based on AES Interop Between PHP and Java (Part 1) post at  *
*  http://propaso.com/blog/?cat=6                                           *
* ------------------------------------------------------------------------- *
*  This program is free software; you can redistribute it and/or modify it  *
*  under the terms of the GNU General Public License as published by the    *
*  Free Software Foundation; either version 2 of the License, or (at your   *
*  option) any later version.                                               *
\***************************************************************************/                
function encrypt($plain_text , $secret_key)
{
    $cipher     = "rijndael-128";
    $mode       = "cbc";
    $iv			= "@4321avaJtluafeD";
    $td = mcrypt_module_open($cipher, "", $mode, $iv);
    mcrypt_generic_init($td, $secret_key, $iv);
    $cyper_text = mcrypt_generic($td, $plain_text);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    return bin2hex($cyper_text);
}
?>
