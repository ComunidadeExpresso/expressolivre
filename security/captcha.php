<?php
 /******************************************************************
   Projectname:   CAPTCHA class
   Version:       1.1
   Author:        Pascal Rehfeldt <Pascal@Pascal-Rehfeldt.com>
   Last modified: 15. March 2004
   Copyright (C): 2003, 2004 Pascal Rehfeldt, all rights reserved

   * GNU General Public License (Version 2, June 1991)
   *
   * This program is free software; you can redistribute
   * it and/or modify it under the terms of the GNU
   * General Public License as published by the Free
   * Software Foundation; either version 2 of the License,
   * or (at your option) any later version.
   *
   * This program is distributed in the hope that it will
   * be useful, but WITHOUT ANY WARRANTY; without even the
   * implied warranty of MERCHANTABILITY or FITNESS FOR A
   * PARTICULAR PURPOSE. See the GNU General Public License
   * for more details.

   Description:
   This class can generate CAPTCHAs, see README for more details!

   Get the "Hurry up!" Font for the Captcha and
   save it in the same directory as this file.

   "Hurry up!" Font (c) by Andi
   See http://www.1001fonts.com/font_details.html?font_id=2366
  ******************************************************************/

  class captcha
  {

    var $Length;
    var $CaptchaString;
    var $ImageType;
    var $Font;
    var $CharWidth;

    function captcha ($length = 5, $type = 'png')
    {
      $this->Length    = $length;
      $this->ImageType = $type;
      $this->Font      = './hurryup.ttf';      
      $this->CharWidth = 27;
      $this->StringGen();
    }

    function Showcaptcha()
    {
      $this->SendHeader();
      $this->MakeCaptcha();   
    }

    function StringGen ()
    {
      $uppercase  = range('A', 'Z');
      $numeric    = range(0, 9);
      $CharPool   = array_merge($uppercase, $numeric);
      $PoolLength = count($CharPool) - 1;
      for ($i = 0; $i < $this->Length; ++$i)
      {
        $this->CaptchaString .= $CharPool[mt_rand(0, $PoolLength)];
      }
    }

    function SendHeader ()
    {
      switch ($this->ImageType)
      {
        case 'jpeg': header('Content-type: image/jpeg'); break;
        case 'png':  header('Content-type: image/png');  break;
        default:     header('Content-type: image/png');  break;
      }
    }

    function MakeCaptcha ()
    {
      $imagelength = $this->Length * $this->CharWidth + 16;
      $imageheight = 37;
      $image       = imagecreate($imagelength, $imageheight);
      $bgcolor     = imagecolorallocate($image, 146, 176, 212);
      $stringcolor = imagecolorallocate($image, 0, rand(0,100), rand(0,155));
      $linecolor   = imagecolorallocate($image, 0, 0, 0);
      imagettftext($image, 20, rand(-4,4),8,30,
                   $stringcolor,
                   $this->Font,
                   $this->CaptchaString);
      imagecolortransparent($image,$bgcolor);

      function hex2int($image, $color) {
	      $string = str_replace("#","",$color);
	      $red = hexdec(substr($string,0,2));
	      $green = hexdec(substr($string,2,2));
	      $blue = hexdec(substr($string,4,2));

	      $color_int = imagecolorallocate($image, $red, $green, $blue);
	      return($color_int);
      }
      // create a blank image
      $src = imagecreatetruecolor(151, 37);

      // fill the background color
      imagefill($src, 0, 0, hex2int($src, "FFFFFF") );


	/* Put some elipses */
      for ($i=0; $i < 5; ++$i)
      {
	      $col_ellipse = imagecolorallocate($src, rand (60,255), rand(60,255), rand(60, 255));
	      imagefilledellipse($src, rand(1,150), rand(1,50), rand(10,30), rand(10,30), $col_ellipse);
      }

	/* Put some vertical lines*/
      for ($i=0; $i < 5; ++$i)
      {
	      $xr = rand(0,130);
	      $yr = rand(0,40);
	      imagefilledrectangle($src, $xr, $yr, $xr+100, $yr+1, rand(0,255));
      }

	/*Put some horizontal lines*/
      for ($i=0; $i < 5; ++$i)
      {
	      $xr = rand(0,130);
	      $yr = rand(0,40);
	      imagefilledrectangle($src, $xr, $yr, $xr+1, $yr+100, rand(0,255));
      }

      imagecopymerge($image, $src, 0, 0, 0, 0, 151, 37, 25);

      switch ($this->ImageType)
      {
        case 'jpeg': imagejpeg($image); break;
        case 'png':  imagepng($image);  break;
        default:     imagepng($image);  break;
      }
    }

    function GetCaptchaString ()
    {
      return $this->CaptchaString;
    }
    
  }
 
 // ************  Fim da Classe  *************************
  //Cria o CAPTCHA,  gera o string e a imagem ...
  $GLOBALS['captcha'] = new captcha;
  // Guarda o string do captcha na session...
  session_name('sessionid');
  session_start();
  $_SESSION['CAPTCHAString'] = $GLOBALS['captcha'] ->GetCaptchaString();
  // Vai exibir a imagem do captcha...
  $GLOBALS['captcha'] ->Showcaptcha();
?>
