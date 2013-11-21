<?php
/**
*
*   PowerGraphic
*   version 1.0
*
*
*
* Author: Carlos Reche
* Author: Sidnei Augusto Drovetto Junior (revision) Apr 30, 2007
* E-mail: carlosreche@yahoo.com
* Sorocaba, SP - Brazil
*
* Created: Sep 20, 2004
* Last Modification: Sep 20, 2004
*
*
*
*  Authors' comments:
*
*  PowerGraphic creates 6 different types of graphics with how many parameters you want. You can
*  change the appearance of the graphics in 3 different skins, and you can still cross data from 2 
*  graphics in only 1! It's a powerful script, and I recommend you read all the instructions 
*  to learn how to use all of this features. Don't worry, it's very simple to use it.
*
*  This script is free. Please keep the credits.
*
*/

/**

   INSTRUNCTIONS OF HOW TO USE THIS SCRIPT  (Please, take a minute to read it. It's important!)


   NOTE: make sure that your PHP is compiled to work with GD Lib.

   NOTE: You may create test images using a form that comes with this script. Just add a "showform" 
   as a query string. (Example: "graphic.php?showform")


   PowerGraphic works with query strings (information sent after the "?" of an URL). Here is an 
   example of how you will have to send the graphic information. Let's suppose that you want to 
   show a graphic of your user's sex:

       <img src="graphic.php?title=Sex&type=5&x1=male&y1=50&x2=female&y2=55" />

   This will create a pie graphic (set by type=5) with title as "Sex" and default skin. 
   Let's see the other parameters:

       x1 = male
       y1 = 50 (quantity of males)
       x2 = female
       y2 = 55 (quantity of females)

   See how it's simple! :)
   For those who don't know, to create a query string you have to put an "?" at the end of the URL and join
   the parameters with "&". Example: "graphic.php?Parameter_1=Value_1&Parameter_2=Value_2" (and so on). You 
   can set how many parameters you want.

   The boring step would be to create this query string. Well, "would be", if I didn't create a function to do that. :)
   Let's see an example of how you can use this function in a PHP document:


///// START OF EXAMPLE /////

<?php

require "class.graphics.php";
$PG = new PowerGraphic;

$PG->title = "Sex";
$PG->type  = "5";
$PG->x[0]  = "male";
$PG->y[0]  = "50";
$PG->x[1]  = "female";
$PG->y[1]  = "55";

echo '<img src="graphic.php?' . $PG->create_query_string() . '" />';


// If you're going to create more than 1 graphic, it'll be important to reset the values before 
// create the next query string:
$PG->reset_values();

?>

///// END OF EXAMPLE /////



   Here is a list of all parameters you may set:

   title      =>  Title of the graphic
   axis_x     =>  Name of values from Axis X
   axis_y     =>  Name of values from Axis Y
   graphic_1  =>  Name of Graphic_1 (only shown if you are gonna cross data from 2 different graphics)
   graphic_2  =>  Name of Graphic_2 (same comment of above)

   type  =>  Type of graphic (values 1 to 6)
                1 => Vertical bars (default)
                2 => Horizontal bars
                3 => Dots
                4 => Lines
                5 => Pie
                6 => Donut

   skin   => Skin of the graphic (values 1 to 3)
                1 => Office (default)
                2 => Matrix
                3 => Spring

   credits => Only if you want to show my credits in the image. :)
                0 => doesn't show (default)
                1 => shows

   x[0]  =>  Name of the first parameter in Axis X
   x[1]  =>  Name of the second parameter in Axis X
   ... (etc)

   y[0]  =>  Value from "graphic_1" relative for "x[0]"
   y[1]  =>  Value from "graphic_1" relative for "x[1]"
   ... (etc)

   z[0]  =>  Value from "graphic_2" relative for "x[0]"
   z[1]  =>  Value from "graphic_2" relative for "x[1]"
   ... (etc)

   NOTE: You can't cross data between graphics if you use "pie" or "donut" graphic. Values for "z"
   won't be considerated.

   That's all! Hope you make a good use of it!
   It would be nice to receive feedback from others users. All comments are welcome!

   Regards,

   Carlos Reche

*/


require_once 'common.inc.php';

$PowerGraphic = &Factory::newInstance('powergraphic');

$PowerGraphic->start();


class powergraphic 
{
	/**
	 * @var float $x coordenate
	 * @access public 
 	 */
    var $x;
    /**
	 * @var float $y y coordenate
	 * @access public 
 	 */
    var $y; 
    /**
	 * @var float $z z coordenate
	 * @access public 
 	 */
    var $z;
    /**
	 * @var string $title 
	 * @access public 
 	 */
    var $title;
    
    /**
     * @var float $axis_x
     * @access public 
     */
    var $axis_x;
    
    /**
     * @var float $axis_y
     * @access public
     */
    var $axis_y;
    
    /**
     * @var $graphic_1
     * @access public
     */
    var $graphic_1;
    
    /**
     * @var $graphic_2
     * @access public 
     */
    var $graphic_2;
    
    /**
     * @var string $type
     * @access public
     */
    var $type;
    
    /**
     * @var string $skin
     * @access public
     */
    var $skin;
    
    /**
     * @var $credits
     * @access public
     */
    var $credits;
    
    /**
     * @var $latin_notation
     * @access public
     */
    var $latin_notation;

	/**
	 * @var int $width
	 * @access public
	 */
    var $width;
    
    /**
	 * @var float $height
	 * @access public
	 */
    var $height;
    
    /**
     * @var float $height_title
     * @access public
     */
    var $height_title;
    
    /**
     * @var float $alternate_x
     * @access public
     */
    var $alternate_x;
    
	/**
	 * @var float $total_parameters
     * @access public
     */
    var $total_parameters;
    
    /**
     * @var float $sum_total
     * @access public
     */
    var $sum_total;
    
    /**
     * @var float $biggest_value
     * @access public
     */
    var $biggest_value;
    
    /**
     * @var float $biggest_parameter
     * @access public
     */
    var $biggest_parameter;
    
    /**
     * @var array $available_types
     * @access public
     */
    var $available_types;

	/**
	 * Constructor of class powergraphic
	 * @access public
	 * @return object
	 */
    function powergraphic()
    {
        $this->x = $this->y = $this->z = array();

        $this->biggest_x        = NULL;
        $this->biggest_y        = NULL;
        
        $this->alternate_x      = false;
        $this->graphic_2_exists = false;
        $this->total_parameters = 0;
        $this->sum_total        = 1;

        $this->title     = (isset($_GET['title']))     ? $_GET['title']     : "";
        $this->axis_x    = (isset($_GET['axis_x']))    ? $_GET['axis_x']    : "";
        $this->axis_y    = (isset($_GET['axis_y']))    ? $_GET['axis_y']    : "";
        $this->graphic_1 = (isset($_GET['graphic_1'])) ? $_GET['graphic_1'] : "";
        $this->graphic_2 = (isset($_GET['graphic_2'])) ? $_GET['graphic_2'] : "";
        $this->type      = (isset($_GET['type']))      ? $_GET['type']      : 1;
        $this->skin      = (isset($_GET['skin']))      ? $_GET['skin']      : 1;
        $this->credits        = ((isset($_GET['credits'])) && ($_GET['credits'] == 1))               ? true : false;
        $this->latin_notation = ((isset($_GET['latin_notation'])) && ($_GET['latin_notation'] == 1)) ? true : false;

        $this->legend_exists        = (preg_match('/(5|6)/', $this->type)) ? true : false;
        $this->biggest_graphic_name = (strlen($this->graphic_1) > strlen($this->graphic_2)) ? $this->graphic_1 : $this->graphic_2;
        $this->height_title         = (!empty($this->title)) ? ($this->string_height(5) + 15) : 0;
        $this->space_between_bars   = ($this->type == 1) ? 40 : 30;
        $this->space_between_dots   = 40;
        $this->higher_value         = 0;
        $this->higher_value_str     = 0;

        $this->width               = 0;
        $this->height              = 0;
        $this->graphic_area_width  = 0;
        $this->graphic_area_height = 0;
        $this->graphic_area_x1     = 30;
        $this->graphic_area_y1     = 20 + $this->height_title;
        $this->graphic_area_x2     = $this->graphic_area_x1 + $this->graphic_area_width;
        $this->graphic_area_y2     = $this->graphic_area_y1 + $this->graphic_area_height;

        $this->available_types = array(
            1 => 'Vertical Bars',
            2 => 'Horizontal Bars',
            3 => 'Dots',
            4 => 'Lines',
            5 => 'Pie',
            6 => 'Donut',
        );
        $this->available_skins = array(
            1 => 'Office',
            2 => 'Matrix',
            3 => 'Spring',
        );
    }


	/**
	 * Start the create of graphic 
	 * @access public
	 * @return bool
	 */
    function start()
    {
        if ( (!isset($_GET['x0'])) || (!isset($_GET['y0'])) )
        {
            if ($_SERVER['QUERY_STRING'] == 'showform')
            {
                $this->html();
            }
            return false;
        }

        // Defines array $temp
        foreach ($_GET as $parameter => $value)
        {
            if (preg_match("/^x\d+$/i", $parameter))
            {
                if (strtolower($parameter{0}) == 'x')
                {
                    if (empty($value))
                    {
                        continue;
                    }

                    if (strlen($value) > strlen($this->biggest_x))
                    {
                        $this->biggest_x = $value;
                    }

                    $num        = substr($parameter, 1, (strlen($parameter)-1) );
                    $temp[$num] = $value;

                    if ((!empty($_GET['z'.$num])) && (preg_match('/(1|2|3|4)/', $this->type)))
                    {
                        $this->graphic_2_exists = true;
                    }
                }
            }
        }

        $i = 0;

        // Defines arrays $this->x, $this->y and $this->z (if exists)
        foreach ($temp as $index => $parameter)
        {
            $this->x[$i] = $parameter;
            $this->y[$i] = 0;

            if (!empty($_GET['y'.$index]))
            {
                $this->y[$i] = $_GET['y'.$index];

                if ($_GET['y'.$index] > $this->biggest_y)
                {
                    $this->biggest_y = number_format(round($_GET['y'.$index], 1), 1, ".", "");
                }
            }

            if ($this->graphic_2_exists)
            {
                $value       = (!empty($_GET['z'.$index])) ? $_GET['z'.$index] : 0;
                $this->z[$i] = $value;

                if ($value > $this->biggest_y)
                {
                    $this->biggest_y = number_format(round($value, 1), 1, ".", "");
                }
            }

            unset($temp[$index]);
            ++$i;
        }

        if (($this->graphic_2_exists == true)  &&  ((!empty($this->graphic_1)) || (!empty($this->graphic_2))))
        {
            $this->legend_exists = true;
        }


        $this->total_parameters    = count($this->x);
        $this->sum_total           = array_sum($this->y);
        $this->space_between_bars += ($this->graphic_2_exists == true) ? 10 : 0;

        $this->calculate_higher_value();
        $this->calculate_width();
        $this->calculate_height();

        $this->create_graphic();
    }


	/**
	 * Create and draw a graphic
	 * @access public
	 * @return void
	 */
    function create_graphic()
    {
        $this->img = imagecreatetruecolor($this->width, $this->height);

        $this->load_color_palette();

        // Fill background
        imagefill($this->img, 0, 0, $this->color['background']);

        // Draw title
        if (!empty($this->title))
        {
            $center = ($this->width / 2) - ($this->string_width($this->title, 5) / 2);
            imagestring($this->img, 5, $center, 10, $this->title, $this->color['title']);
        }


        // Draw axis and background lines for "vertical bars", "dots" and "lines"
        if (preg_match('/^(1|3|4)$/', $this->type))
        {
            if ($this->legend_exists == true)
            {
                $this->draw_legend();
            }

            $higher_value_y    = $this->graphic_area_y1 + (0.1 * $this->graphic_area_height);
            $higher_value_size = 0.9 * $this->graphic_area_height;

            $less  = 7 * strlen($this->higher_value_str);

            imageline($this->img, $this->graphic_area_x1, $higher_value_y, $this->graphic_area_x2, $higher_value_y, $this->color['bg_lines']);
            imagestring($this->img, 3, ($this->graphic_area_x1-$less-7), ($higher_value_y-7), $this->higher_value_str, $this->color['axis_values']);

            for ($i = 1; $i < 10; ++$i)
            {
                $dec_y = $i * ($higher_value_size / 10);
                $x1 = $this->graphic_area_x1;
                $y1 = $this->graphic_area_y2 - $dec_y;
                $x2 = $this->graphic_area_x2;
                $y2 = $this->graphic_area_y2 - $dec_y;

                imageline($this->img, $x1, $y1, $x2, $y2, $this->color['bg_lines']);
                if ($i % 2 == 0) {
                    $value = $this->number_formated($this->higher_value * $i / 10);
                    $less = 7 * strlen($value);
                    imagestring($this->img, 3, ($x1-$less-7), ($y2-7), $value, $this->color['axis_values']);
                }
            }

            // Axis X
            imagestring($this->img, 3, $this->graphic_area_x2+10, $this->graphic_area_y2+3, $this->axis_x, $this->color['title']);
            imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y2, $this->graphic_area_x2, $this->graphic_area_y2, $this->color['axis_line']);
            // Axis Y
            imagestring($this->img, 3, 20, $this->graphic_area_y1-20, $this->axis_y, $this->color['title']);
            imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y1, $this->graphic_area_x1, $this->graphic_area_y2, $this->color['axis_line']);
        }


        // Draw axis and background lines for "horizontal bars"
        else if ($this->type == 2)
        {
            if ($this->legend_exists == true)
            {
                $this->draw_legend();
            }

            $higher_value_x    = $this->graphic_area_x2 - (0.2 * $this->graphic_area_width);
            $higher_value_size = 0.8 * $this->graphic_area_width;

            imageline($this->img, ($this->graphic_area_x1+$higher_value_size), $this->graphic_area_y1, ($this->graphic_area_x1+$higher_value_size), $this->graphic_area_y2, $this->color['bg_lines']);
            imagestring($this->img, 3, (($this->graphic_area_x1+$higher_value_size) - ($this->string_width($this->higher_value, 3)/2)), ($this->graphic_area_y2+2), $this->higher_value_str, $this->color['axis_values']);

            for ($i = 1, $alt = 15; $i < 10; ++$i)
            {
                $dec_x = number_format(round($i * ($higher_value_size  / 10), 1), 1, ".", "");

                imageline($this->img, ($this->graphic_area_x1+$dec_x), $this->graphic_area_y1, ($this->graphic_area_x1+$dec_x), $this->graphic_area_y2, $this->color['bg_lines']);
                if ($i % 2 == 0) {
                    $alt   = (strlen($this->biggest_y) > 4 && $alt != 15) ? 15 : 2;
                    $value = $this->number_formated($this->higher_value * $i / 10);
                    imagestring($this->img, 3, (($this->graphic_area_x1+$dec_x) - ($this->string_width($this->higher_value, 3)/2)), ($this->graphic_area_y2+$alt), $value, $this->color['axis_values']);
                }
            }

            // Axis X
            imagestring($this->img, 3, ($this->graphic_area_x2+10), ($this->graphic_area_y2+3), $this->axis_y, $this->color['title']);
            imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y2, $this->graphic_area_x2, $this->graphic_area_y2, $this->color['axis_line']);
            // Axis Y
            imagestring($this->img, 3, 20, ($this->graphic_area_y1-20), $this->axis_x, $this->color['title']);
            imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y1, $this->graphic_area_x1, $this->graphic_area_y2, $this->color['axis_line']);
        }


        // Draw legend box for "pie" or "donut"
        else if (preg_match('/^(5|6)$/', $this->type))
        {
            $this->draw_legend();
        }



        /**
        * Draw graphic: VERTICAL BARS
        */
        if ($this->type == 1)
        {
            $num = 1;
            $x   = $this->graphic_area_x1 + 20;

            foreach ($this->x as $i => $parameter)
            {
                if (isset($this->z[$i])) {
                    $size = round($this->z[$i] * $higher_value_size / $this->higher_value);
                    $x1   = $x + 10;
                    $y1   = ($this->graphic_area_y2 - $size) + 1;
                    $x2   = $x1 + 20;
                    $y2   = $this->graphic_area_y2 - 1;
                    imageline($this->img, ($x1+1), ($y1-1), $x2, ($y1-1), $this->color['bars_2_shadow']);
                    imageline($this->img, ($x2+1), ($y1-1), ($x2+1), $y2, $this->color['bars_2_shadow']);
                    imageline($this->img, ($x2+2), ($y1-1), ($x2+2), $y2, $this->color['bars_2_shadow']);
                    imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars_2']);
                }

                $size = round($this->y[$i] * $higher_value_size / $this->higher_value);
                $alt  = (($num % 2 == 0) && (strlen($this->biggest_x) > 5)) ? 15 : 2;
                $x1   = $x;
                $y1   = ($this->graphic_area_y2 - $size) + 1;
                $x2   = $x1 + 20;
                $y2   = $this->graphic_area_y2 - 1;
                $x   += $this->space_between_bars;
                ++$num;

                imageline($this->img, ($x1+1), ($y1-1), $x2, ($y1-1), $this->color['bars_shadow']);
                imageline($this->img, ($x2+1), ($y1-1), ($x2+1), $y2, $this->color['bars_shadow']);
                imageline($this->img, ($x2+2), ($y1-1), ($x2+2), $y2, $this->color['bars_shadow']);
                imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars']);
                imagestring($this->img, 3, ((($x1+$x2)/2) - (strlen($parameter)*7/2)), ($y2+$alt+2), $parameter, $this->color['axis_values']);
            }
        }


        /**
        * Draw graphic: HORIZONTAL BARS
        */
        else if ($this->type == 2)
        {
            $y = 10;

            foreach ($this->x as $i => $parameter)
            {
                if (isset($this->z[$i])) {
                    $size = round($this->z[$i] * $higher_value_size / $this->higher_value);
                    $x1   = $this->graphic_area_x1 + 1;
                    $y1   = $this->graphic_area_y1 + $y + 10;
                    $x2   = $x1 + $size;
                    $y2   = $y1 + 15;
                    imageline($this->img, ($x1), ($y2+1), $x2, ($y2+1), $this->color['bars_2_shadow']);
                    imageline($this->img, ($x1), ($y2+2), $x2, ($y2+2), $this->color['bars_2_shadow']);
                    imageline($this->img, ($x2+1), ($y1+1), ($x2+1), ($y2+2), $this->color['bars_2_shadow']);
                    imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars_2']);
                    imagestring($this->img, 3, ($x2+7), ($y1+7), $this->number_formated($this->z[$i], 2), $this->color['bars_2_shadow']);
                }

                $size = round(($this->y[$i] / $this->higher_value) * $higher_value_size);
                $x1   = $this->graphic_area_x1 + 1;
                $y1   = $this->graphic_area_y1 + $y;
                $x2   = $x1 + $size;
                $y2   = $y1 + 15;
                $y   += $this->space_between_bars;

                imageline($this->img, ($x1), ($y2+1), $x2, ($y2+1), $this->color['bars_shadow']);
                imageline($this->img, ($x1), ($y2+2), $x2, ($y2+2), $this->color['bars_shadow']);
                imageline($this->img, ($x2+1), ($y1+1), ($x2+1), ($y2+2), $this->color['bars_shadow']);
                imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars']);
                imagestring($this->img, 3, ($x2+7), ($y1+2), $this->number_formated($this->y[$i], 2), $this->color['bars_shadow']);

                imagestring($this->img, 3, ($x1 - ((strlen($parameter)*7)+7)), ($y1+2), $parameter, $this->color['axis_values']);
            }
        }


        /**
        * Draw graphic: DOTS or LINE
        */
        else if (preg_match('/^(3|4)$/', $this->type))
        {

            $x[0] = $this->graphic_area_x1+1;

            foreach ($this->x as $i => $parameter)
            {
                if ($this->graphic_2_exists == true) {
                    $size  = round($this->z[$i] * $higher_value_size / $this->higher_value);
                    $z[$i] = $this->graphic_area_y2 - $size;
                }

                $alt   = (($i % 2 == 0) && (strlen($this->biggest_x) > 5)) ? 15 : 2;
                $size  = round($this->y[$i] * $higher_value_size / $this->higher_value);
                $y[$i] = $this->graphic_area_y2 - $size;

                if ($i != 0) {
                    imageline($this->img, $x[$i], ($this->graphic_area_y1+10), $x[$i], ($this->graphic_area_y2-1), $this->color['bg_lines']);
                }
                imagestring($this->img, 3, ($x[$i] - (strlen($parameter)*7/2 )), ($this->graphic_area_y2+$alt+2), $parameter, $this->color['axis_values']);

                $x[$i+1] = $x[$i] + 40;
            }

            foreach ($x as $i => $value_x)
            {
                if ($this->graphic_2_exists == true)
                {
                    if (isset($z[$i+1])) {
                        // Draw lines
                        if ($this->type == 4)
                        {
                            imageline($this->img, $x[$i], $z[$i], $x[$i+1], $z[$i+1], $this->color['line_2']);
                            imageline($this->img, $x[$i], ($z[$i]+1), $x[$i+1], ($z[$i+1]+1), $this->color['line_2']);
                        }
                        imagefilledrectangle($this->img, $x[$i]-1, $z[$i]-1, $x[$i]+2, $z[$i]+2, $this->color['line_2']);
                    }
                    else { // Draw last dot
                        imagefilledrectangle($this->img, $x[$i-1]-1, $z[$i-1]-1, $x[$i-1]+2, $z[$i-1]+2, $this->color['line_2']);
                    }
                }

                if (count($y) > 1)
                {
                    if (isset($y[$i+1])) {
                        // Draw lines
                        if ($this->type == 4)
                        {
                            imageline($this->img, $x[$i], $y[$i], $x[$i+1], $y[$i+1], $this->color['line']);
                            imageline($this->img, $x[$i], ($y[$i]+1), $x[$i+1], ($y[$i+1]+1), $this->color['line']);
                        }
                        imagefilledrectangle($this->img, $x[$i]-1, $y[$i]-1, $x[$i]+2, $y[$i]+2, $this->color['line']);
                    }
                    else { // Draw last dot
                        imagefilledrectangle($this->img, $x[$i-1]-1, $y[$i-1]-1, $x[$i-1]+2, $y[$i-1]+2, $this->color['line']);
                    }
                }

            }
        }


        /**
        * Draw graphic: PIE or DONUT
        */
        else if (preg_match('/^(5|6)$/', $this->type))
        {
            $center_x = ($this->graphic_area_x1 + $this->graphic_area_x2) / 2;
            $center_y = ($this->graphic_area_y1 + $this->graphic_area_y2) / 2;
            $width    = $this->graphic_area_width;
            $height   = $this->graphic_area_height;
            $start    = 0;
            $sizes    = array();

            foreach ($this->x as $i => $parameter)
            {
                $size    = $this->y[$i] * 360 / $this->sum_total;
                $sizes[] = $size;
                $start  += $size;
            }
            $start = 270;

            // Draw PIE
            if ($this->type == 5)
            {
                // Draw shadow
                foreach ($sizes as $i => $size)
                {
                    $num_color = $i%7 + 1;
                    $color = 'arc_' . $num_color . '_shadow';

                    for ($i = 10; $i >= 0; $i -= 1)
                    {
                        imagearc($this->img, $center_x, ($center_y+$i), $width, $height, $start, ($start+$size), $this->color[$color]);
                    }
                    $start += $size;
                }

                $start = 270;

                // Draw pieces
                foreach ($sizes as $i => $size)
                {
                    $num_color = $i%7 + 1;
                    $color = 'arc_' . $num_color;

                    imagefilledarc($this->img, $center_x, $center_y, ($width+2), ($height+2), $start, ($start+$size), $this->color[$color], IMG_ARC_EDGED);
                    $start += $size;
                }
            }

            // Draw DONUT
            else if ($this->type == 6)
            {
                foreach ($sizes as $i => $size)
                {
                    $num_color = $i%7 + 1;
                    $color        = 'arc_' . $num_color;
                    $color_shadow = 'arc_' . $num_color . '_shadow';
                    imagefilledarc($this->img, $center_x, $center_y, $width, $height, $start, ($start+$size), $this->color[$color], IMG_ARC_PIE);
                    $start += $size;
                }
                imagefilledarc($this->img, $center_x, $center_y, 100, 100, 0, 360, $this->color['background'], IMG_ARC_PIE);
                imagearc($this->img, $center_x, $center_y, 100, 100, 0, 360, $this->color['bg_legend']);
                imagearc($this->img, $center_x, $center_y, ($width+1), ($height+1), 0, 360, $this->color['bg_legend']);
            }
        }


        if ($this->credits == true) {
            $this->draw_credits();
        }


        header('Content-type: image/png');
        imagepng($this->img);
        imagedestroy($this->img);
        exit;
    }



	/**
	 * Calculate width of graphic elements
	 * @access public
	 * @return void
	 */
    function calculate_width()
    {
        switch ($this->type)
        {
            // Vertical bars
            case 1:
                $this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, 3) + 25) : 0;
                $this->graphic_area_width = ($this->space_between_bars * $this->total_parameters) + 30;
                $this->graphic_area_x1   += $this->string_width(($this->higher_value_str), 3);
                $this->width += $this->graphic_area_x1 + 20;
                $this->width += ($this->legend_exists == true) ? 50 : ((7 * strlen($this->axis_x)) + 10);
                break;

            // Horizontal bars
            case 2:
                $this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, 3) + 25) : 0;
                $this->graphic_area_width = ($this->string_width($this->higher_value_str, 3) > 50) ? (5 * ($this->string_width($this->higher_value_str, 3)) * 0.85) : 200;
                $this->graphic_area_x1 += 7 * strlen($this->biggest_x);
                $this->width += ($this->legend_exists == true) ? 60 : ((7 * strlen($this->axis_y)) + 30);
                $this->width += $this->graphic_area_x1;
                break;

            // Dots
            case 3:
                $this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, 3) + 25) : 0;
                $this->graphic_area_width = ($this->space_between_dots * $this->total_parameters) - 10;
                $this->graphic_area_x1   += $this->string_width(($this->higher_value_str), 3);
                $this->width += $this->graphic_area_x1 + 20;
                $this->width += ($this->legend_exists == true) ? 40 : ((7 * strlen($this->axis_x)) + 10);
                break;

            // Lines
            case 4:
                $this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, 3) + 25) : 0;
                $this->graphic_area_width = ($this->space_between_dots * $this->total_parameters) - 10;
                $this->graphic_area_x1   += $this->string_width(($this->higher_value_str), 3);
                $this->width += $this->graphic_area_x1 + 20;
                $this->width += ($this->legend_exists == true) ? 40 : ((7 * strlen($this->axis_x)) + 10);
                break;

            // Pie
            case 5:
                $this->legend_box_width   = $this->string_width($this->biggest_x, 3) + 85;
                $this->graphic_area_width = 200;
                $this->width += 90;
                break;

            // Donut
            case 6:
                $this->legend_box_width   = $this->string_width($this->biggest_x, 3) + 85;
                $this->graphic_area_width = 180;
                $this->width += 90;
                break;
        }

        $this->width += $this->graphic_area_width;
        $this->width += $this->legend_box_width;


        $this->graphic_area_x2 = $this->graphic_area_x1 + $this->graphic_area_width;
        $this->legend_box_x1   = $this->graphic_area_x2 + 40;
        $this->legend_box_x2   = $this->legend_box_x1 + $this->legend_box_width;
    }


	/**
	 * Calculate height
	 * @access public
	 * @return void
	 */
    function calculate_height()
    {
        switch ($this->type)
        {
            // Vertical bars
            case 1:
                $this->legend_box_height   = ($this->graphic_2_exists == true) ? 40 : 0;
                $this->graphic_area_height = 150;
                $this->height += 65;
                break;

            // Horizontal bars
            case 2:
                $this->legend_box_height   = ($this->graphic_2_exists == true) ? 40 : 0;
                $this->graphic_area_height = ($this->space_between_bars * $this->total_parameters) + 10;
                $this->height += 65;
                break;

            // Dots
            case 3:
                $this->legend_box_height   = ($this->graphic_2_exists == true) ? 40 : 0;
                $this->graphic_area_height = 150;
                $this->height += 65;
                break;

            // Lines
            case 4:
                $this->legend_box_height   = ($this->graphic_2_exists == true) ? 40 : 0;
                $this->graphic_area_height = 150;
                $this->height += 65;
                break;

            // Pie
            case 5:
                $this->legend_box_height   = (!empty($this->axis_x)) ? 30 : 5;
                $this->legend_box_height  += (14 * $this->total_parameters);
                $this->graphic_area_height = 150;
                $this->height += 50;
                break;

            // Donut
            case 6:
                $this->legend_box_height   = (!empty($this->axis_x)) ? 30 : 5;
                $this->legend_box_height  += (14 * $this->total_parameters);
                $this->graphic_area_height = 180;
                $this->height += 50;
                break;
        }

        $this->height += $this->height_title;
        $this->height += ($this->legend_box_height > $this->graphic_area_height) ? ($this->legend_box_height - $this->graphic_area_height) : 0;
        $this->height += $this->graphic_area_height;

        $this->graphic_area_y2 = $this->graphic_area_y1 + $this->graphic_area_height;
        $this->legend_box_y1   = $this->graphic_area_y1 + 10;
        $this->legend_box_y2   = $this->legend_box_y1 + $this->legend_box_height;
    }

	/**
	 * Draw the graphic legend
	 * @access public
	 * @return void
	 */
    function draw_legend()
    {
        $x1 = $this->legend_box_x1;
        $y1 = $this->legend_box_y1;
        $x2 = $this->legend_box_x2;
        $y2 = $this->legend_box_y2;

        imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bg_legend']);

        $x = $x1 + 5;
        $y = $y1 + 5;


        // Draw legend values for VERTICAL BARS, HORIZONTAL BARS, DOTS and LINES
        if (preg_match('/^(1|2|3|4)$/', $this->type))
        {
            $color_1 = (preg_match('/^(1|2)$/', $this->type)) ? $this->color['bars']   : $this->color['line'];
            $color_2 = (preg_match('/^(1|2)$/', $this->type)) ? $this->color['bars_2'] : $this->color['line_2'];

            imagefilledrectangle($this->img, $x, $y, ($x+10), ($y+10), $color_1);
            imagerectangle($this->img, $x, $y, ($x+10), ($y+10), $this->color['title']);
            imagestring($this->img, 3, ($x+15), ($y-2), $this->graphic_1, $this->color['axis_values']);
            $y += 20;
            imagefilledrectangle($this->img, $x, $y, ($x+10), ($y+10), $color_2);
            imagerectangle($this->img, $x, $y, ($x+10), ($y+10), $this->color['title']);
            imagestring($this->img, 3, ($x+15), ($y-2), $this->graphic_2, $this->color['axis_values']);
        }

        // Draw legend values for PIE or DONUT
        else if (preg_match('/^(5|6)$/', $this->type))
        {
            if (!empty($this->axis_x))
            {
                imagestring($this->img, 3, ((($x1+$x2)/2) - (strlen($this->axis_x)*7/2)), $y, $this->axis_x, $this->color['title']);
                $y += 25;
            }

            $num = 0;

            foreach ($this->x as $i => $parameter)
            {
                $num = $i%7 + 1;
                $color = 'arc_' . $num;

                $percent = number_format(round(($this->y[$i] * 100 / $this->sum_total), 2), 2, ".", "") . ' %';
                $less    = (strlen($percent) * 7);

                if ($num != 1) {
                    imageline($this->img, ($x1+15), ($y-2), ($x2-5), ($y-2), $this->color['bg_lines']);
                }
                imagefilledrectangle($this->img, $x, $y, ($x+10), ($y+10), $this->color[$color]);
                imagerectangle($this->img, $x, $y, ($x+10), ($y+10), $this->color['title']);
                imagestring($this->img, 3, ($x+15), ($y-2), $parameter, $this->color['axis_values']);
                imagestring($this->img, 2, ($x2-$less), ($y-2), $percent, $this->color['axis_values']);
                $y += 14;
                ++$num;
            }
        }
    }

	/**
	 * Calculate the string width
	 * @access public
	 * @return int string width
	 */
    function string_width($string, $size) {
        $single_width = $size + 4;
        return $single_width * strlen($string);
    }
    
	/**
	 * Calculate the string height
	 * @access public
	 * @return int string height
	 */
    function string_height($size) {
        if ($size <= 1) {
            $height = 8;
        } else if ($size <= 3) {
            $height = 12;
        } else if ($size >= 4) {
            $height = 14;
        }
        return $height;
    }

	/** 
	 * Calculate the higher value
	 * @access public
	 * @return void
	 */
    function calculate_higher_value() {
        $digits   = strlen(round($this->biggest_y));
        $interval = pow(10, ($digits-1));
        $this->higher_value     = round(($this->biggest_y - ($this->biggest_y % $interval) + $interval), 1);
        $this->higher_value_str = $this->number_formated($this->higher_value);
    }

	/**
	 * Number formated (Float to string representation) 
	 * @param float $number
	 * @param int $dec_size
	 * @access public 
	 * @return string
	 */
    function number_formated($number, $dec_size = 1)
    {
        if ($this->latin_notation == true) {
            return number_format(round($number, $dec_size), $dec_size, ",", ".");
        }
        return number_format(round($number, $dec_size), $dec_size, ".", ",");
    }
	
	/**
	 * Number float
	 * @param string $number
	 * @return float
	 */
    function number_float($number)
    {
        if ($this->latin_notation == true) {
            $number = str_replace(".", "", $number);
            return (float)str_replace(",", ".", $number);
        }
        return (float)str_replace(",", "", $number);
    }

	/**
	 * Draw the credits string
	 * @param int $number 
	 * @return void
	 * @access public
	 */
    function draw_credits() {
        imagestring($this->img, 1, ($this->width-120), ($this->height-10), "Powered by Carlos Reche", $this->color['title']);
    }

	/**
	 * Load color palette
	 * @return void
	 * @access public
	 */
    function load_color_palette()
    {
        switch ($this->skin)
        {
            // Office
            case 1:
                $this->color['title']       = imagecolorallocate($this->img,   0,   0, 100);
                $this->color['background']  = imagecolorallocate($this->img, 220, 220, 220);
                $this->color['axis_values'] = imagecolorallocate($this->img,  50,  50,  50);
                $this->color['axis_line']   = imagecolorallocate($this->img, 100, 100, 100);
                $this->color['bg_lines']    = imagecolorallocate($this->img, 240, 240, 240);
                $this->color['bg_legend']   = imagecolorallocate($this->img, 205, 205, 205);

                if (preg_match('/^(1|2)$/', $this->type))
                {
                    $this->color['bars']          = imagecolorallocate($this->img, 100, 150, 200);
                    $this->color['bars_shadow']   = imagecolorallocate($this->img,  50, 100, 150);
                    $this->color['bars_2']        = imagecolorallocate($this->img, 200, 250, 150);
                    $this->color['bars_2_shadow'] = imagecolorallocate($this->img, 120, 170,  70);
                }
                else if (preg_match('/^(3|4)$/', $this->type))
                {
                    $this->color['line']   = imagecolorallocate($this->img, 100, 150, 200);
                    $this->color['line_2'] = imagecolorallocate($this->img, 230, 100, 100);
                }
                else if (preg_match('/^(5|6)$/', $this->type))
                {
                    $this->color['arc_1']        = imagecolorallocate($this->img, 100, 150, 200);
                    $this->color['arc_2']        = imagecolorallocate($this->img, 200, 250, 150);
                    $this->color['arc_3']        = imagecolorallocate($this->img, 250, 200, 150);
                    $this->color['arc_4']        = imagecolorallocate($this->img, 250, 150, 150);
                    $this->color['arc_5']        = imagecolorallocate($this->img, 250, 250, 150);
                    $this->color['arc_6']        = imagecolorallocate($this->img, 230, 180, 250);
                    $this->color['arc_7']        = imagecolorallocate($this->img, 200, 200, 150);
                    $this->color['arc_1_shadow'] = imagecolorallocate($this->img,  60, 110, 170);
                    $this->color['arc_2_shadow'] = imagecolorallocate($this->img, 120, 170,  70);
                    $this->color['arc_3_shadow'] = imagecolorallocate($this->img, 180, 120,  70);
                    $this->color['arc_4_shadow'] = imagecolorallocate($this->img, 170, 100, 100);
                    $this->color['arc_5_shadow'] = imagecolorallocate($this->img, 180, 180, 110);
                    $this->color['arc_6_shadow'] = imagecolorallocate($this->img, 160, 110, 190);
                    $this->color['arc_7_shadow'] = imagecolorallocate($this->img, 140, 140, 100);
                }
                break;

            // Matrix
            case 2:
                $this->color['title']       = imagecolorallocate($this->img, 255, 255, 255);
                $this->color['background']  = imagecolorallocate($this->img,   0,   0,   0);
                $this->color['axis_values'] = imagecolorallocate($this->img,   0, 230,   0);
                $this->color['axis_line']   = imagecolorallocate($this->img,   0, 200,   0);
                $this->color['bg_lines']    = imagecolorallocate($this->img, 100, 100, 100);
                $this->color['bg_legend']   = imagecolorallocate($this->img,  70,  70,  70);

                if (preg_match('/^(1|2)$/', $this->type))
                {
                    $this->color['bars']          = imagecolorallocate($this->img,  50, 200,  50);
                    $this->color['bars_shadow']   = imagecolorallocate($this->img,   0, 150,   0);
                    $this->color['bars_2']        = imagecolorallocate($this->img, 255, 255, 255);
                    $this->color['bars_2_shadow'] = imagecolorallocate($this->img, 220, 220, 220);
                }
                else if (preg_match('/^(3|4)$/', $this->type))
                {
                    $this->color['line']   = imagecolorallocate($this->img, 220, 220, 220);
                    $this->color['line_2'] = imagecolorallocate($this->img,   0, 180,   0);
                }
                else if (preg_match('/^(5|6)$/', $this->type))
                {
                    $this->color['arc_1']        = imagecolorallocate($this->img, 255, 255, 255);
                    $this->color['arc_2']        = imagecolorallocate($this->img, 200, 220, 200);
                    $this->color['arc_3']        = imagecolorallocate($this->img, 160, 200, 160);
                    $this->color['arc_4']        = imagecolorallocate($this->img, 135, 180, 135);
                    $this->color['arc_5']        = imagecolorallocate($this->img, 115, 160, 115);
                    $this->color['arc_6']        = imagecolorallocate($this->img, 100, 140, 100);
                    $this->color['arc_7']        = imagecolorallocate($this->img,  90, 120,  90);
                    $this->color['arc_1_shadow'] = imagecolorallocate($this->img, 200, 220, 200);
                    $this->color['arc_2_shadow'] = imagecolorallocate($this->img, 160, 200, 160);
                    $this->color['arc_3_shadow'] = imagecolorallocate($this->img, 135, 180, 135);
                    $this->color['arc_4_shadow'] = imagecolorallocate($this->img, 115, 160, 115);
                    $this->color['arc_5_shadow'] = imagecolorallocate($this->img, 100, 140, 100);
                    $this->color['arc_6_shadow'] = imagecolorallocate($this->img,  90, 120,  90);
                    $this->color['arc_7_shadow'] = imagecolorallocate($this->img,  85, 100,  85);
                }
                break;


            // Spring
            case 3:
                $this->color['title']       = imagecolorallocate($this->img, 250,  50,  50);
                $this->color['background']  = imagecolorallocate($this->img, 250, 250, 220);
                $this->color['axis_values'] = imagecolorallocate($this->img,  50, 150,  50);
                $this->color['axis_line']   = imagecolorallocate($this->img,  50, 100,  50);
                $this->color['bg_lines']    = imagecolorallocate($this->img, 200, 224, 180);
                $this->color['bg_legend']   = imagecolorallocate($this->img, 230, 230, 200);

                if (preg_match('/^(1|2)$/', $this->type))
                {
                    $this->color['bars']          = imagecolorallocate($this->img, 255, 170,  80);
                    $this->color['bars_shadow']   = imagecolorallocate($this->img, 200, 120,  30);
                    $this->color['bars_2']        = imagecolorallocate($this->img, 250, 230,  80);
                    $this->color['bars_2_shadow'] = imagecolorallocate($this->img, 180, 150,   0);
                }
                else if (preg_match('/^(3|4)$/', $this->type))
                {
                    $this->color['line']   = imagecolorallocate($this->img, 230, 100,   0);
                    $this->color['line_2'] = imagecolorallocate($this->img, 220, 200,  50);
                }
                else if (preg_match('/^(5|6)$/', $this->type))
                {
                    $this->color['arc_1']        = imagecolorallocate($this->img, 100, 150, 200);
                    $this->color['arc_2']        = imagecolorallocate($this->img, 200, 250, 150);
                    $this->color['arc_3']        = imagecolorallocate($this->img, 250, 200, 150);
                    $this->color['arc_4']        = imagecolorallocate($this->img, 250, 150, 150);
                    $this->color['arc_5']        = imagecolorallocate($this->img, 250, 250, 150);
                    $this->color['arc_6']        = imagecolorallocate($this->img, 230, 180, 250);
                    $this->color['arc_7']        = imagecolorallocate($this->img, 200, 200, 150);
                    $this->color['arc_1_shadow'] = imagecolorallocate($this->img,  60, 110, 170);
                    $this->color['arc_2_shadow'] = imagecolorallocate($this->img, 120, 170,  70);
                    $this->color['arc_3_shadow'] = imagecolorallocate($this->img, 180, 120,  70);
                    $this->color['arc_4_shadow'] = imagecolorallocate($this->img, 170, 100, 100);
                    $this->color['arc_5_shadow'] = imagecolorallocate($this->img, 180, 180, 110);
                    $this->color['arc_6_shadow'] = imagecolorallocate($this->img, 160, 110, 190);
                    $this->color['arc_7_shadow'] = imagecolorallocate($this->img, 140, 140, 100);
                }
                break;

        }

    }

	/**
	 * Echo html code for powergraphic funcionalitty
	 * @access public
	 * @return void
	 */
    function html()
    {
        echo 
'<html>
<head>
<title>Power Graphic - by Carlos Reche</title>
<style type="text/css">
<!--

body {
    margin-top: 50px;
    margin-bottom: 30px;
    margin-left: 70px;
    margin-right: 70px;
    background-color: #ffffff;
}
body, table {
    font-size: 13px;
    font-family: verdana;
    color: #666666;
}
input, select {
    font-size: 11px;
    font-family: verdana;
    color: #333333;
    border: 1px solid #aaaaaa;
}

a:link    { color: #666666; font-weight: bold; text-decoration: none; }
a:visited { color: #666666; font-weight: bold; text-decoration: none; }
a:hover   { color: #cc0000; text-decoration: none; }
a:active  { color: #666666; text-decoration: none; }

-->
</style>
<script type="text/javascript">
<!--

var i = 4;

function add_parameter() {
    i++;
    space = ((i+1) < 10) ? "8px" : "0px";
    display_status = (document.form_data.graphic_2_values.checked == true) ? "" : "none";
    new_line  = "<div style=\"margin-left: " + space + "\"> " + (i+1) + ". <input type=\"text\" name=\"x" + i + "\" id=\"x" + i + "\" size=\"20\" /> ";
    new_line += " <input type=\"text\" name=\"y" + i + "\" id=\"y" + i + "\" size=\"10\" onkeypress=\"return numbers();\" /> ";
    new_line += " <input type=\"text\" name=\"z" + i + "\" id=\"z" + i + "\" size=\"10\" onkeypress=\"return numbers();\" style=\"display: " + display_status + ";\" /> </div>";
    document.getElementById("add_parameter").innerHTML += new_line;
}

function show_graphic_2_values() {
    var display_status = (document.form_data.graphic_2_values.checked == true) ? "" : "none";
    document.getElementById("value_2").style.display = display_status;
    for (var x = 0; x <= i; x++) {
        document.getElementById("z" + x).style.display = display_status;
    }
}

function numbers() {
    key = event.keyCode;
    if ((key >= 48 && key <= 57) || key == 46 || key == 13) { return true; } else { return false; }
}

//-->
</script>
</head>
<body>

<div style="position: absolute; right: 0px; top: -10px; right: 20px; z-index: -1; text-align: right;">
  <div style="font-size: 60px;">
    <span style="letter-spacing: -5px; font-family: arial black; color: #ffbbbb;">Power</span><span style="color: #bbbbff;">Graphics</span>
  </div>
  <div style="font-style: italic; font-size: 15px;">Powered by Carlos Reche</div>
</div>


<form action="' . $_SERVER['SCRIPT_NAME'] . '" method="get" name="form_data" id="form_data"> <br />
  <div style="margin-bottom: 10px;">
    Title: <input type="text" name="title" id="title" size="30" style="margin: 0px 0px 0px 11px;" />
  </div>
  Axis X: <input type="text" name="axis_x" id="axis_x" size="30" /> <br />
  Axis Y: <input type="text" name="axis_y" id="axis_y" size="30" /> <br />

  <div style="margin: 10px 0px 10px 0px;">
    Graphic 1: <input type="text" name="graphic_1" id="graphic_1" style="width: 172px;" /> <br />
    Graphic 2: <input type="text" name="graphic_2" id="graphic_2" style="width: 172px;" /> <br />
  </div>

  Type: <select name="type" id="type" style="margin: 5px 0px 0px 7px;">';
        foreach ($this->available_types as $code => $type) {
            echo '    <option value="' . $code . '"> ' . $type . ' </option>';
        }
        echo 
'  </select> <br />
Color: <select name="skin" id="skin" style="margin: 5px 0px 0px 7px;">';
        foreach ($this->available_skins as $code => $color) {
            echo '    <option value="' . $code . '"> ' . $color . ' </option>';
        }
        echo 
'  </select>


  <div style="margin-top: 20px;" id="parameters">
    <div style="margin-bottom: 20px;">
      <input type="checkbox" name="graphic_2_values" id="graphic_2_values" value="1" onclick="javascript: show_graphic_2_values();" style="border-width: 0px;" /> Show values for Graphic 2
    </div>

    <span style="margin-left: 5px; font-size: 15px; font-family: arial;"><a href="javascript: add_parameter();">+</a></span>
    <span style="margin-left: 40px;">Parameter</span> 
    <span style="margin-left: 50px;">Value 1</span>  <span id="value_2" style="display: none; margin-left: 25px;">Value 2</span>';
        for ($i = 0; $i <= 4; ++$i) {
            echo '<div style="margin-left: 8px;"> '.($i+1).'. <input type="text" name="x'.$i.'" id="x'.$i.'" size="20" /> ';
            echo ' <input type="text" name="y'.$i.'" id="y'.$i.'" size="10" onkeypress="return numbers();" /> ';
            echo ' <input type="text" name="z'.$i.'" id="z'.$i.'" size="10" onkeypress="return numbers();" style="display: none;" /> </div>';
        }
echo 
'    <div id="add_parameter"></div>
    <span style="margin-left: 5px; font-size: 15px; font-family: arial;"><a href="javascript: add_parameter();">+</a></span>
  </div>

  <input type="checkbox" name="credits" id="credits" value="1" checked="checked" style="border-width: 0px;" /> Show credits

  <input type="submit" value="Create" style="cursor: pointer; margin: 10px 0px 0px 60px;" />
</form>


</body>
</html>';
    }

	/**
	 * Reset all graphics  value
	 * @access public
	 * @return void
	 */
    function reset_values() {
        $this->title     = NULL;
        $this->axis_x    = NULL;
        $this->axis_y    = NULL;
        $this->type      = NULL;
        $this->skin      = NULL;
        $this->graphic_1 = NULL;
        $this->graphic_2 = NULL;
        $this->credits   = NULL;
        $this->x = $this->y = $this->z = array();
    }
    
	/**
	 * Create query string array
	 * @param array $array
	 * @return string
	 * @access public
	 */
    function create_query_string_array($array) {
        if (!is_array($array)) {
            return false;
        }
        $query_string = array();
        foreach ($array as $parameter => $value) {
            $query_string[] = urlencode($parameter) . '=' . urlencode($value);
        }
        return implode("&", $query_string);
    }
	
	/**
	 * Create query string
	 * @return string query string 
	 * @access public
	 */
    function create_query_string() {
        $graphic['title']     = $this->title;
        $graphic['axis_x']    = $this->axis_x;
        $graphic['axis_y']    = $this->axis_y;
        $graphic['type']      = $this->type;
        $graphic['skin']      = $this->skin;
        $graphic['graphic_1'] = $this->graphic_1;
        $graphic['graphic_2'] = $this->graphic_2;
        $graphic['credits']   = $this->credits;

        foreach ($this->x as $i => $x)
        {
            $graphic['x'.$i] = $x;
            if (isset($this->y[$i])) { $graphic['y'.$i] = $this->y[$i]; }
            if (isset($this->z[$i])) { $graphic['z'.$i] = $this->z[$i]; }
        }
        return $this->create_query_string_array($graphic);
    }
}


?>
