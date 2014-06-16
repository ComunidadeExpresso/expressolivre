<?php
		/*************************************************************************** 
		* Expresso Livre                                                           * 
		* http://www.expressolivre.org                                             * 
		* --------------------------------------------                             * 
		*  This program is free software; you can redistribute it and/or modify it * 
		*  under the terms of the GNU General Public License as published by the   * 
		*  Free Software Foundation; either version 2 of the License, or (at your  * 
		*  option) any later version.                                              * 
		\**************************************************************************/ 
		
if(!isset($GLOBALS['phpgw_info'])){
        $GLOBALS['phpgw_info']['flags'] = array(
                'currentapp' => 'expressoMail',
                'nonavbar'   => true,
                'noheader'   => true
        );
}
require_once '../header.inc.php';

/*Author:
*    JPEXS  from http://www.hotscripts.com* ImageCreateFromBmp
*/

function imagecreatefrombmp($file)
{
global  $CurrentBit, $echoMode;

$f=fopen($file,"r");
$Header=fread($f,2);

if($Header=="BM")
{
 $Size=freaddword($f);
 $Reserved1=freadword($f);
 $Reserved2=freadword($f);
 $FirstByteOfImage=freaddword($f);

 $SizeBITMAPINFOHEADER=freaddword($f);
 $Width=freaddword($f);
 $Height=freaddword($f);
 $biPlanes=freadword($f);
 $biBitCount=freadword($f);
 $RLECompression=freaddword($f);
 $WidthxHeight=freaddword($f);
 $biXPelsPerMeter=freaddword($f);
 $biYPelsPerMeter=freaddword($f);
 $NumberOfPalettesUsed=freaddword($f);
 $NumberOfImportantColors=freaddword($f);

if($biBitCount<24)
 {
  $img=imagecreate($Width,$Height);
  $Colors=pow(2,$biBitCount);
  for($p=0;$p<$Colors;++$p)
   {
    $B=freadbyte($f);
    $G=freadbyte($f);
    $R=freadbyte($f);
    $Reserved=freadbyte($f);
    $Palette[]=imagecolorallocate($img,$R,$G,$B);
   };




if($RLECompression==0)
{
   $Zbytek=(4-ceil(($Width/(8/$biBitCount)))%4)%4;

for($y=$Height-1;$y>=0;$y--)
    {
     $CurrentBit=0;
     for($x=0;$x<$Width;++$x)
      {
         $C=freadbits($f,$biBitCount);
       imagesetpixel($img,$x,$y,$Palette[$C]);
      };
    if($CurrentBit!=0) {freadbyte($f);};
    for($g=0;$g<$Zbytek;++$g)
     freadbyte($f);
     };

 };
};


if($RLECompression==1) //$BI_RLE8
{
$y=$Height;

$pocetb=0;

while(true)
{
$y--;
$prefix=freadbyte($f);
$suffix=freadbyte($f);
$pocetb+=2;

$echoit=false;

if($echoit)echo "Prefix: $prefix Suffix: $suffix<br />";
if(($prefix==0)and($suffix==1)) break;
if(feof($f)) break;

while(!(($prefix==0)and($suffix==0)))
{
 if($prefix==0)
  {
   $pocet=$suffix;
   $Data.=fread($f,$pocet);
   $pocetb+=$pocet;
   if($pocetb%2==1) {freadbyte($f); ++$pocetb;};
  };
 if($prefix>0)
  {
   $pocet=$prefix;
   for($r=0;$r<$pocet;++$r)
    $Data.=chr($suffix);
  };
 $prefix=freadbyte($f);
 $suffix=freadbyte($f);
 $pocetb+=2;
 if($echoit) echo "Prefix: $prefix Suffix: $suffix<br />";
};

for($x=0;$x<strlen($Data);++$x)
 {
  imagesetpixel($img,$x,$y,$Palette[ord($Data[$x])]);
 };
$Data="";

};

};


if($RLECompression==2) //$BI_RLE4
{
$y=$Height;
$pocetb=0;

/*while(!feof($f))
 echo freadbyte($f)."_".freadbyte($f)."<br />";*/
while(true)
{
//break;
$y--;
$prefix=freadbyte($f);
$suffix=freadbyte($f);
$pocetb+=2;

$echoit=false;

if($echoit)echo "Prefix: $prefix Suffix: $suffix<br />";
if(($prefix==0)and($suffix==1)) break;
if(feof($f)) break;

while(!(($prefix==0)and($suffix==0)))
{
 if($prefix==0)
  {
   $pocet=$suffix;

   $CurrentBit=0;
   for($h=0;$h<$pocet;++$h)
    $Data.=chr(freadbits($f,4));
   if($CurrentBit!=0) freadbits($f,4);
   $pocetb+=ceil(($pocet/2));
   if($pocetb%2==1) {freadbyte($f); ++$pocetb;};
  };
 if($prefix>0)
  {
   $pocet=$prefix;
   $i=0;
   for($r=0;$r<$pocet;++$r)
    {
    if($i%2==0)
     {
      $Data.=chr($suffix%16);
     }
     else
     {
      $Data.=chr(floor($suffix/16));
     };
    ++$i;
    };
  };
 $prefix=freadbyte($f);
 $suffix=freadbyte($f);
 $pocetb+=2;
 if($echoit) echo "Prefix: $prefix Suffix: $suffix<br />";
};

for($x=0;$x<strlen($Data);++$x)
 {
  imagesetpixel($img,$x,$y,$Palette[ord($Data[$x])]);
 };
$Data="";

};

};


 if($biBitCount==24)
{
 $img=imagecreatetruecolor($Width,$Height);
 $Zbytek=$Width%4;

   for($y=$Height-1;$y>=0;$y--)
    {
     for($x=0;$x<$Width;++$x)
      {
       $B=freadbyte($f);
       $G=freadbyte($f);
       $R=freadbyte($f);
       $color=imagecolorexact($img,$R,$G,$B);
       if($color==-1) $color=imagecolorallocate($img,$R,$G,$B);
       imagesetpixel($img,$x,$y,$color);
      }
    for($z=0;$z<$Zbytek;++$z)
     freadbyte($f);
   };
};
return $img;

};


fclose($f);


};


/*
* Helping functions:
*-------------------------
*
* freadbyte($file) - reads 1 byte from $file
* freadword($file) - reads 2 bytes (1 word) from $file
* freaddword($file) - reads 4 bytes (1 dword) from $file
* freadlngint($file) - same as freaddword($file)
* decbin8($d) - returns binary string of d zero filled to 8
* RetBits($byte,$start,$len) - returns bits $start->$start+$len from $byte
* freadbits($file,$count) - reads next $count bits from $file
* RGBToHex($R,$G,$B) - convert $R, $G, $B to hex
* int_to_dword($n) - returns 4 byte representation of $n
* int_to_word($n) - returns 2 byte representation of $n
*/

function freadbyte($f)
{
 return ord(fread($f,1));
};

function freadword($f)
{
 $b1=freadbyte($f);
 $b2=freadbyte($f);
 return $b2*256+$b1;
};


function freadlngint($f)
{
return freaddword($f);
};

function freaddword($f)
{
 $b1=freadword($f);
 $b2=freadword($f);
 return $b2*65536+$b1;
};



function RetBits($byte,$start,$len)
{
$bin=decbin8($byte);
$r=bindec(substr($bin,$start,$len));
return $r;

};



$CurrentBit=0;
function freadbits($f,$count)
{
 global $CurrentBit,$SMode;
 $Byte=freadbyte($f);
 $LastCBit=$CurrentBit;
 $CurrentBit+=$count;
 if($CurrentBit==8)
  {
   $CurrentBit=0;
  }
 else
  {
   fseek($f,ftell($f)-1);
  };
 return RetBits($Byte,$LastCBit,$count);
};



function RGBToHex($Red,$Green,$Blue)
  {
   $hRed=dechex($Red);if(strlen($hRed)==1) $hRed="0$hRed";
   $hGreen=dechex($Green);if(strlen($hGreen)==1) $hGreen="0$hGreen";
   $hBlue=dechex($Blue);if(strlen($hBlue)==1) $hBlue="0$hBlue";
   return($hRed.$hGreen.$hBlue);
  };

        function int_to_dword($n)
        {
                return chr($n & 255).chr(($n >> 8) & 255).chr(($n >> 16) & 255).chr(($n >> 24) & 255);
        }
        function int_to_word($n)
        {
                return chr($n & 255).chr(($n >> 8) & 255);
        }


function decbin8($d)
{
return decbinx($d,8);
};

function decbinx($d,$n)
{
$bin=decbin($d);
$sbin=strlen($bin);
for($j=0;$j<$n-$sbin;++$j)
 $bin="0$bin";
return $bin;
};

function inttobyte($n)
{
return chr($n);
};

?>
