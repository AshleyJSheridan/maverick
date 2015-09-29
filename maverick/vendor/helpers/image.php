<?php
namespace helpers;

/**
 * an image helper class allowing creation, resizing, text overlays and image effects
 * @package Maverick
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class image
{
	private $width = 100;
	private $height = 100;
	private $filename = '';
	private $format = 'jpg';
	private $mime = 'image/jpeg';
	private $image;
	private $font = '';
	private $font_size = 12;	// default size in points
	private $line_height = 0;
	private $colours = array();
	private $foreground = '';
	private $exif;
	private $auto_rotate = false;

	/**
	 * generates the image object, either from a file, or using the supplied parameters
	 * @param string $from_file   the path to an image to use as the basis for this object
	 * @param int    $width       the width in pixels to use for a new image
	 * @param int    $height      the height in pixels to use for a new image
	 * @param string $format      the type of image to generate, either jpg, gif, or png
	 * @param bool   $auto_rotate if the image needs to be automatically rotated or not based on the exif data
	 * @return GD_ImageResource|bool
	 */
	public function __construct($from_file=null, $width=100, $height=100, $format='jpg', $auto_rotate=false)
	{
		// set some defaults here as we can't use the string concatenator or method calls in the initial variable initalisation
		$this->font = MAVERICK_BASEDIR . 'views/LiberationSans-Regular.ttf';
		//$this->foreground = $this->add_colour('f00');
		
		if(!is_null($from_file) && file_exists($from_file) )
			$this->create_from_file($from_file);

		// if the image wasn't created from a file, or no file was specified, make an empty one with the other parameters
		if(empty($this->image) && in_array($format, array('gif', 'png', 'jpg') ) )
		{
			$this->format = $format;	// force the format to be one of the ones GD can handle
			$this->width = intval($width)?intval($width):100;
			$this->height = intval($height)?intval($height):100;
			
			if($format != 'jpg')
				$this->mime = "image/$this->format";
			
			$this->image = imagecreatetruecolor($this->width, $this->height);
		}
		
		if($auto_rotate)
			$this->auto_rotate = true;
		
		if(!$this->exif)
			$this->exif = exif_read_data($this->filename);

		return (!empty($this->image))?$this:false;
	}
	
	/**
	 * set various member variables for the image object using limiting constraints
	 * @param string $param the name of the variable to set
	 * @param string $value the value to set the variable to
	 * @return bool
	 */
	public function __set($param, $value)
	{
		switch($param)
		{
			case 'width':
			case 'height':
			case 'font_size':
			case 'line_height':
				if(intval($value) )
					$this->$param = intval($value);
				break;
			case 'format':
				if(in_array($value, array('jpg', 'jpeg') ) )
				{
					$this->format = 'jpg';
					$this->mime = 'images/jpeg';
				}
				if(in_array($value, array('png', 'gif') ) )
				{
					$this->format = $value;
					$this->mime = "image/$value";
				}
				break;
			case 'font':
				// you can only use OTF fonts if it contains TTF outlines, otherwise the font may not work at all with GD
				if(file_exists($value) && preg_match('/(\.[to]tf)$/', $value) )
					$this->font = $value;
				break;
			case 'foreground':
				if(preg_match('/^#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/', $value, $matches))
					$this->$param = $this->add_colour($matches[1]);
				break;
			case 'auto_rotate':
				if(is_bool($value))
					$this->$param = $value;
				break;
		}//end switch
	}
	
	/**
	 * getter for the image objects
	 * @param string $param the parameter to fetch
	 * @return mixed
	 */
	public function __get($param)
	{
		if(in_array($param, array('width', 'height', 'image') ) )
			return $this->$param;
	}
	
	/**
	 * add an image effect to the image
	 * @param string $filter the name of the effect filter to apply
	 * @param array  $params an optional list of parameters to supply the image effect - some effects require up to 4 parameters to be set
	 * @param int    $repeat the number of times to repeat this filter, e.g. to make it stronger
	 * @return bool
	 */
	public function effect($filter, $params = array(), $repeat = 1 )
	{
		if(!is_array($params)) $params = (array)$params;	// force the params to be an array if they're not already
		$repeat = !is_numeric($repeat)?1:(int)$repeat;		// the number of times to repeat a filter - i.e. to get a stronger blur, for example
		$filter_type = 'standard';	// determines the type of filter that gets applied
		$param_length = 4;	// determines how many parameters get passed to certain types of filters

		switch($filter)
		{
			case 'brightness':
			case 'smooth':
				$param_length = 1;
				$params[0] = $this->constrain_int($params[0]);
				break;
			case 'contrast':
				$param_length = 1;
				$params[0] = $this->constrain_int($params[0], -100, 100);
				break;
			case 'colorize':
				$param_length = 3;
				$params[0] = $this->constrain_int($params[0]);
				$params[1] = $this->constrain_int($params[1]);
				$params[2] = $this->constrain_int($params[2]);
				break;
			case 'multiply':
				$param_length = 3;
				$params[0] = abs(255 - $this->constrain_int($params[0]) ) * -1;
				$params[1] = abs(255 - $this->constrain_int($params[1]) ) * -1;
				$params[2] = abs(255 - $this->constrain_int($params[2]) ) * -1;
				
				$filter = 'colorize';
				break;
			case 'pixelate':
				$param_length = 2;
				$params[0] = $this->constrain_int($params[0], 0);
				$params[1] = (isset($params[1]) && is_bool($params[1]))?$params[1]:false;
				break;
			case 'negate':
			case 'grayscale':
			case 'edgedetect':
			case 'emboss':
			case 'gaussian_blur':
			case 'selective_blur':
			case 'mean_removal':
				$param_length = 0;
				break;
			case 'emboss2':
				$matrix = array(array(2, 0, 0), array(0, -1, 0), array(0, 0, -1));
				$divisor = 1;
				$offset = 127;
				$filter_type = 'matrix';
				break;
			case 'emboss3':
				$matrix = array(array(1, 1, -1), array(1, 1, -1), array(1, -1, -1));
				$divisor = 1;
				$offset = 127;
				$filter_type = 'matrix';
				break;
			case 'edgedetect2':
				$matrix = array(array(1, 1, 1), array(1, 7, 1), array(1, 1, -1));
				$divisor = 1;
				$offset = 127;
				$filter_type = 'matrix';
				break;
			case 'edgedetect3':
				$matrix = array(array(-1, -1, -1), array(0, 0, 0), array(1, 1, 1));
				$divisor = 1;
				$offset = 127;
				$filter_type = 'matrix';
				break;
			case 'gaussian_blur2':
				$matrix = array(array(1, 2, 1), array(2, 4, 2), array(1, 2, 1));
				$divisor = 16;
				$offset = -1;
				$filter_type = 'matrix';
				break;
			case 'sharpen':
				$matrix = array(array(0, -1, 0), array(-1, 5, -1), array(0, -1, 0));
				$divisor = 1;
				$offset = 63;
				$filter_type = 'matrix';
				break;
			case 'rotate':
				$param_length = 2;
				$filter_type = 'function';
				$filter_function = "image$filter";
				$params[0] = $this->constrain_int($params[0], -360, 360);
				$params[1] = 0;
				break;
			case 'round_pixelate':
				$param_length = 1;
				$filter_type = 'custom_method';
				$filter_function = "custom_$filter";
				$params[0] = $this->constrain_int($params[0], 0);
				break;
			case 'scatter':
				$param_length = 1;
				$filter_type = 'custom_method';
				$filter_function = "custom_$filter";
				$params[0] = $this->constrain_int($params[0], 0, 50);
				break;
			case 'noise':
				$param_length = 1;
				$filter_type = 'custom_method';
				$filter_function = "custom_$filter";
				$params[0] = $this->constrain_int($params[0], 0);
				break;
			case 'oil':
				$param_length = 3;
				$filter_type = 'custom_method';
				$filter_function = "custom_$filter";
				$params[0] = $this->constrain_int($params[0], 0);
				$params[1] = $this->constrain_int($params[1], 0, 20);
				$params[2] = $this->constrain_int($params[2], 0, 50);
				break;
		}//end switch
		
		for($i=0; $i<4; $i++)
		{
			if(!isset($params[$i]))
				$params[$i] = null;
		}

		if($filter_type == 'standard')
		{
			$filter_const = constant('IMG_FILTER_' . strtoupper($filter));
			// bit ugly, but for filters that expect 2 or 3 extra parameters, PHP gives a warning when more are passed
			// conversely, PHP doesn't care about extra parameters passed when it expects none
			switch($param_length)
			{
				case 0:
					for($i=0; $i<$repeat; $i++)
						imagefilter($this->image, $filter_const);
					break;
				case 1:
					for($i=0; $i<$repeat; $i++)
						imagefilter($this->image, $filter_const, $params[0]);
					break;
				case 2:
					for($i=0; $i<$repeat; $i++)
						imagefilter($this->image, $filter_const, $params[0], $params[1]);
					break;
				case 3:
					for($i=0; $i<$repeat; $i++)
						imagefilter($this->image, $filter_const, $params[0], $params[1], $params[2]);
					break;
				default:
					for($i=0; $i<$repeat; $i++)
						imagefilter($this->image, $filter_const, $params[0], $params[1], $params[2], $params[3]);
					break;
			}//end switch
		}//end if
		
		if($filter_type == 'matrix')
		{
			for($i=0; $i<$repeat; $i++)
				imageconvolution($this->image, $matrix, $divisor, $offset);
		}
		if($filter_type == 'function')
		{
			for($i=0; $i<$repeat; $i++)
				$this->image = call_user_func($filter_function, $this->image, $params[0], $params[1]);
		}
		if($filter_type == 'custom_method')
		{
			switch($param_length)
			{
				case 1:
					for($i=0; $i<$repeat; $i++)
						$this->$filter_function($params[0]);
				case 2:
					for($i=0; $i<$repeat; $i++)
						$this->$filter_function($params[0], $params[1]);
				case 3:
					for($i=0; $i<$repeat; $i++)
						$this->$filter_function($params[0], $params[1], $params[2]);
			}
			
		}//end if
	}
	
	/**
	 * resize an image to a specified width and height
	 * this method will return false if it determines that no resize will occur due to the passed parameters
	 * @param string|int $width  a width either expressed as pixels, a percentage, or the strings 'auto' or 'nochange'
	 * @param string|int $height a height either expressed as pixels, a percentage, or the strings 'auto' or 'nochange'
	 * @param string     $type   how the resize should occur - regular will resize and distort the image, crop will resize and crop parts of the image that do not fit
	 * @return boolean
	 */
	public function resize($width, $height, $type='regular')
	{
		$type = in_array($type, array('crop', 'regular') )?$type:'regular';
		
		// if both sizes are auto or the type is crop and either size is auto then do nothing
		if( (preg_match('/^(auto|nochange)$/', $width) && preg_match('/^(auto|nochange)$/', $height) ) || ( $type == 'crop' && ($width == 'auto' || $height == 'auto') ) )
			return false;
		
		// if both sizes are not within constrained type limits then do nothing
		// they should be either 'auto', 'nochange', a % value, or an integer representing the pixel dimensions
		if(!preg_match('/^(auto|nochange|(\d+)%?)$/', $width) && preg_match('/^(auto|nochange|(\d+)%?)$/', $height) )
			return false;
		
		// some types of resize also require a cropping action too so switch between them here
		switch($type)
		{
			case 'regular':
			{
				// determine the new width
				if($width == 'auto')
				{
					if(is_numeric($height))
						$width = (int)(intval($height) / $this->height * $this->width);
					else
						$width = $this->set_dimension($this->width, $height);
				}
				else
					$width = $this->set_dimension($this->width, $width);
				
				// determine the new height
				if($height == 'auto')
				{
					if(is_numeric($width))
						$height = (int)(intval($width) / $this->width * $this->height);
					else
						$height = $this->set_dimension($this->height, $width);
				}
				else
					$height = $this->set_dimension($this->height, $height);

				$image_p = imagecreatetruecolor($width, $height);
				return (imagecopyresampled($image_p, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height) && $this->image = $image_p);
				
				break;
			}//end case
			case 'crop':
			{
				$width = $this->set_dimension($this->width, $width);
				$height = $this->set_dimension($this->height, $height);

				if(($width / $height) < ($this->width / $this->height))
				{
					// landscape
					$ratio = $width / $height;
					$image_p = imagecreatetruecolor($width, $height);
					
					imagecopyresampled(
						$image_p, // dst_im
						$this->image, // src_im
						0, // dst_x
						0, // dst_y
						($this->width / 2) - ($this->height * $ratio) / 2, // src_x
						0, // src_y
						$width, // dst_w
						$height, // dst_h
						$this->height * $ratio, // src_w
						$this->height // src_h
					);
				}
				else
				{
					// portrait
					$ratio = $height / $width;
					$image_p = imagecreatetruecolor($width, $height);
					
					imagecopyresampled(
						$image_p, // dst_im
						$this->image, // src_im
						0, // dst_x
						0, // dst_y
						0, // src_x
						0, // src_y
						$width, // dst_w
						$height, // dst_h
						$this->width, // src_w
						$this->width * $ratio // src_h
					);
				}//end if
				return ($this->image = $image_p);
				
				break;
			}//end case
		}//end switch
	}
	
	/**
	 * outputs the image either to the standard output stream or a file
	 * if a filename is not specified, then the appropriate content type headers will be set and the image will be output
	 * @param null|string $filename whether or not to save this image or dump it to the standard output stream
	 * @return bool
	 */
	public function output($filename=null)
	{
		if($this->auto_rotate && $this->info('orientation') )
		{
			switch($this->info('orientation') )
			{
				case 8:
					$this->effect('rotate', array(90) );
					break;
				case 3:
					$this->effect('rotate', array(180) );
					break;
				case 6:
					$this->effect('rotate', array(-90) );
					break;
			}
		}
		
		if(empty($filename))
		{
			header("Content-Type: $this->mime");
		
			switch($this->format)
			{
				case 'png':
					imagepng($this->image);
					break;
				case 'gif':
					imagegif($this->image);
					break;
				case 'jpg':
					imagejpeg($this->image);
					break;
			}
		}
		else
		{
			if(!file_exists(dirname($filename) ) )
				mkdir(dirname($filename), 0777, true);

			switch($this->format)
			{
				case 'png':
					imagepng($this->image, $filename, 9);
					break;
				case 'gif':
					imagegif($this->image, $filename);
					break;
				case 'jpg':
					imagejpeg($this->image, $filename);
					break;
			}
		}//end if
	}
	
	/**
	 * write text onto the image using the specified parameters and the font settings set on the image object
	 * @param string $text  the message to write onto the image
	 * @param int    $x     the x position of the bottom left pixel of the text (or first line of text in the case of multi-line text)
	 * @param int    $y     the y posiiton in pixels of the line of text (or text box for multi-line text)
	 * @param int    $width the width of the text box - if this is 0, then the text will be written out on a single line and will not wrap
	 * @return boolean
	 */
	public function write($text='', $x=0, $y=0, $width=0)
	{
		if(!strlen($text))
			return false;
		
		// break the text into individual words, and make an empty array to hold the widths of each word
		$words = array_merge(explode(' ', $text), array(' '));	// add in the space so that we also calculate the width of that too
		$height = 0;
		$widths = array();
		
		for($i=0; $i<count($words); $i++)
		{
			$textbox = imagettfbbox($this->font_size, 0, $this->font, $words[$i]);
			$widths[$words[$i]] = $textbox[2] - $textbox[0];
		
			// this is necessary, because different words will create different heights due to parts of letters being below the baseline, etc
			$temp_height = $textbox[3] - $textbox[5];
			if($temp_height > $height)
				$height = $temp_height;
		}
		
		if($width)
		{
			// assume the text will need to be broken across several lines
			$lines = array();	// array to hold each line of the heading
			$current_line = 0;	// pointer indicating the current line
			$running_total = 0;	// running width for each line of text
			
			for($i=0; $i<count($words)-1; $i++)	// the -1 ensures the last space in the words width array is not added to the resulting string
			{
				if(!isset($lines[$current_line]))
					$lines[$current_line] = '';
				
				if(($running_total + $widths[$words[$i]] + $widths[' ']) < $width)
				{
					$lines[$current_line] .= $words[$i] . ' ';
					$running_total += $widths[$words[$i]];
				}
				else
				{
					$lines[$current_line] = rtrim($lines[$current_line]);	// trim the trailing space from the last line
					
					$current_line ++;
					$lines[$current_line] = $words[$i] . ' ';
					$running_total = $widths[$words[$i]];
				}
			}//end for
			
			$lines[$current_line] = rtrim($lines[$current_line]);	// trim the trailing space from the last line
			
			$line_height = $this->line_height?$this->line_height:$height;	// set the line height to use - this uses the parameter set by the user code if not 0, otherwise it sets it to what it determines the height of the text is
			
			for($i=0; $i<count($lines); $i++)
				imagettftext($this->image, $this->font_size, 0, $x, ($line_height*$i)+$y, $this->foreground, $this->font, $lines[$i]);

		}//end if
		else
			imagettftext($this->image, $this->font_size, 0, $x, $y, $this->foreground, $this->font, $text);
	}
	
	/**
	 * gets a list of exif data attached to the image
	 * @param string|array $fields a field or list of fields that you want to specifically return
	 * @return array
	 */
	public function info($fields = array() )
	{
		if($fields)
		{
			if(is_array($fields))
			{
				$exif = array();
				
				foreach($fields as $field)
				{
					if(preg_match("/\b($field)\b/i", join(',', array_keys($this->exif) ), $matches ) )
						$exif[$field] = $this->exif[$matches[0]];
				}
			}
			else
			{
				$exif = '';
				if(preg_match("/\b($fields)\b/i", join(',', array_keys($this->exif) ), $matches ) )
					$exif = $this->exif[$matches[0]];
			}
		}//end if
		else
			$exif = $this->exif;

		return $exif;
	}
	
	/**
	 * compares two \helpers\image objects visually and returns a number of how similar they appear to be
	 * the lower this number the more similar
	 * @param \helpers\image $img1  the first image to compare
	 * @param \helpers\image $img2  the second image to compare
	 * @param int            $level the accuracy of the comparison
	 * @return int
	 */
	public static function compare($img1, $img2, $level=5)
	{
		$level *= 10;
		$differences = array();	// this will keep track of the pixel variations to perform some statistical analysis on later
		
		// set up the widths of pixels for each image (as they may not be the same)
		$px1 = $img1->width / $level;
		$py1 = $img1->height / $level;
		$px2 = $img2->width / $level;
		$py2 = $img2->height / $level;
		$gd1 = $img1->image;
		$gd2 = $img2->image;
		
		for($x=1; $x<=$level; $x++)
		{
			for($y=1; $y<=$level; $y++)
			{
				$rgb1 = imagecolorsforindex($gd1, imagecolorat($gd1, $x*$px1-($px1/2), $y*$py1-($py1/2) ) );
				//$hsl1 = \helpers\image::rgbToHsl($rgb1['red'], $rgb1['green'], $rgb1['blue']);
				$hsl1 = self::rgbToHsl($rgb1['red'], $rgb1['green'], $rgb1['blue']);
				
				$rgb2 = imagecolorsforindex($gd2, imagecolorat($gd2, $x*$px2-($px2/2), $y*$py2-($py2/2) ) );
				//$hsl2 = \helpers\image::rgbToHsl($rgb2['red'], $rgb2['green'], $rgb2['blue']);
				$hsl2 = self::rgbToHsl($rgb2['red'], $rgb2['green'], $rgb2['blue']);
				
				$differences[] = intval(abs($hsl1[0] - $hsl2[0]) );
			}
		}
		return array_sum($differences)/count($differences);
	}

	/**
	 * converts rgb values into hsl and returns the three new values as an array
	 * originally sourced from https://gist.github.com/brandonheyer/5254516
	 * @param int $r the red component
	 * @param int $g the green component
	 * @param int $b the blue component
	 * @return array
	 */
	public static function rgbToHsl( $r, $g, $b ) 
	{
		$oldR = $r;
		$oldG = $g;
		$oldB = $b;
		
		$r /= 255;
		$g /= 255;
		$b /= 255;
		
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		
		$h;
		$s;
		$l = ( $max + $min ) / 2;
		$d = $max - $min;
		
		if($d == 0 )
			$h = $s = 0; // achromatic
		else
		{
			$s = $d / ( 1 - abs(2 * $l - 1 ) );
			
			switch( $max )
			{
				case $r:
					$h = 60 * fmod(( ( $g - $b ) / $d ), 6 );
					if ($b > $g)
						$h += 360;
					break;
				case $g:
					$h = 60 * ( ( $b - $r ) / $d + 2 );
					break;
				case $b:
					$h = 60 * ( ( $r - $g ) / $d + 4 );
					break;
			}
		}//end if
		return array(round($h, 2 ), round($s, 2 ), round($l, 2 ) );
	} 

	/**
	 * add a colour resource to the image object
	 * @param string $colour the hex string representation of the colour as one of: #rgb, #rgba, #rrggbb, and #rrggbbaa
	 * @return int a colour identifier
	 */
	private function add_colour($colour)
	{
		$alpha = 0;
		$original_colour = $colour;
		
		// this deals with the colours coming in various different formats: #rgb, #rgba, #rrggbb, and #rrggbbaa
		switch(strlen($colour) )
		{
			case 3:
				$colour = preg_replace('/^([0-9a-z])([0-9a-z])([0-9a-z])$/', '$1$1$2$2$3$3', $colour);
				break;
			case 4:
				$alpha = intval(floor(hexdec(substr($colour, -1).substr($colour, -1) ) / 2 ) );
				$colour = preg_replace('/^([0-9a-z])([0-9a-z])([0-9a-z])([0-9a-z])$/', '$1$1$2$2$3$3', $colour);
				break;
			case 8:
				$alpha = intval(floor(hexdec(substr($colour, -2) ) / 2 ) );
				$colour = substr($colour, 0, 6);
				break;
		}

		if(!array_key_exists($original_colour, $this->colours))
		{
			$rgb = sscanf($colour, '%2x%2x%2x');
			
			if($alpha)
				$this->colours[$original_colour] = imagecolorallocatealpha($this->image, $rgb[0], $rgb[1], $rgb[2], $alpha);
			else
				$this->colours[$original_colour] = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);
		}

		return $this->colours[$original_colour];
	}
	
	/**
	 * constrains a number to a range of values - used internally by the filter effects as different filters accept different ranges
	 * @param int $int the number to constrain
	 * @param int $min the minimum value
	 * @param int $max the maximum value
	 * @return int
	 */
	private function constrain_int($int, $min=-255, $max=255)
	{
		if($int < $min)
			$int = $min;
		if($int > $max)
			$int = $max;
		
		return $int;
	}
	
	/**
	 * generate an image resource from an already existing image
	 * if the image cannot be read by GD, then do nothing
	 * @param string $from_file the path to an existing image
	 * @return bool
	 */
	private function create_from_file($from_file)
	{
		// only proceed if the file details can be read by GD
		if(($details = getimagesize($from_file)) !== false)
		{
			$this->filename = $from_file;
			list($this->width, $this->height) = $details;
			$this->mime = $details['mime'];

			switch($details['mime'])
			{
				case 'image/png':
					$this->image = \imagecreatefrompng($this->filename);
					$this->format = 'png';
					break;
				case 'image/gif':
					$this->image = \imagecreatefrompng($this->filename);
					$this->format = 'gif';
					break;
				case 'image/jpeg':
					$this->image = \imagecreatefromjpeg($this->filename);
					$this->format = 'jpg';
			}
		}//end if
	}
	
	/**
	 * determine a dimension for use with an image resize
	 * @param string|int $input_val the input dimension to determine as pixels
	 * @param int        $type      the pixel value to use as a guide for calculating this dimension
	 * @return int
	 */
	private function set_dimension($input_val, $type)
	{
		$return_val = 0;

		switch(true)
		{
			case ($type=='nochange'):
				$return_val = $input_val;
				break;
			case (is_numeric($type)):
				$return_val = $type;
				break;
			case (!is_numeric($type)):
				$return_val = intval($type) / 100 * $input_val;
				break;
		}
		
		return (int)$return_val;
	}

	// custom image effect filters - they must all start with custom_ in their name
	
	/**
	 * a custom image effect filter to create a pixellated image using round pixels
	 * @param int $blocksize the pixel size of the block to use in the pixellation
	 * @return bool
	 */
	private function custom_round_pixelate($blocksize)
	{
		$imagex = imagesx($this->image);
		$imagey = imagesy($this->image);

		for ($x=0; $x<$imagex; $x+=$blocksize)
		{
			for ($y = 0; $y < $imagey; $y += $blocksize)
			{
				$colour = imagecolorat($this->image, $x, $y);
				imagefilledellipse($this->image, $x - $blocksize / 2, $y - $blocksize / 2, $blocksize, $blocksize, $colour);
			}
		}
	}
	
	/**
	 * a custom image effect filter to create a scatter effect of the pxels in the image
	 * @param int $dist the oixel value of the distribution offset
	 * @return bool
	 */
	private function custom_scatter($dist)
	{
		$imagex = imagesx($this->image);
		$imagey = imagesy($this->image);

		for ($x = 0; $x < $imagex; ++$x)
		{
			for ($y = 0; $y < $imagey; ++$y)
			{

				$distx = rand($dist * -1, $dist);
				$disty = rand($dist * -1, $dist);

				if ($x + $distx >= $imagex) continue;
				if ($x + $distx < 0) continue;
				if ($y + $disty >= $imagey) continue;
				if ($y + $disty < 0) continue;

				$oldcol = imagecolorat($this->image, $x, $y);
				$newcol = imagecolorat($this->image, $x + $distx, $y + $disty);
				imagesetpixel($this->image, $x, $y, $newcol);
				imagesetpixel($this->image, $x + $distx, $y + $disty, $oldcol);

			}
		}//end for
	}
	
	/**
	 * a custom image effect filter to add white noise to the image - higher values is more noise
	 * @param int $diff the value of difference to use
	 * @return bool
	 */
	private function custom_noise($diff)
	{
		$imagex = imagesx($this->image);
		$imagey = imagesy($this->image);

		for($x = 0; $x < $imagex; ++$x)
		{
			for($y = 0; $y < $imagey; ++$y)
			{
				if(rand(0, 1) )
				{
					$rgb = imagecolorat($this->image, $x, $y);
					$red = ($rgb >> 16) & 0xFF;
					$green = ($rgb >> 8) & 0xFF;
					$blue = $rgb & 0xFF;
					$modifier = rand($diff * -1, $diff);
					$red += $modifier;
					$green += $modifier;
					$blue += $modifier;

					if ($red > 255) $red = 255;
					if ($green > 255) $green = 255;
					if ($blue > 255) $blue = 255;
					if ($red < 0) $red = 0;
					if ($green < 0) $green = 0;
					if ($blue < 0) $blue = 0;

					$newcol = imagecolorallocate($this->image, $red, $green, $blue);
					imagesetpixel($this->image, $x, $y, $newcol);
				}//end if
			}//end for
		}//end for
	}
	
	/**
	 * a custom image effect filter to create an oil painting effect
	 * @param int $strength  the strength of the effect
	 * @param int $diff      how much random difference to apply to the colours used
	 * @param int $brushsize the size of the brush to use in pixels
	 * @return bool
	 */
	private function custom_oil($strength, $diff, $brushsize)
	{
		$imagex = imagesx($this->image);
		$imagey = imagesy($this->image);

		for ($x = 0; $x < $imagex; ++$x)
		{
			for ($y = 0; $y < $imagey; ++$y)
			{
				if(rand(0, $strength) < 2)
				{
					$rgb = imagecolorat($this->image, $x, $y);
					$red = ($rgb >> 16) & 0xFF;
					$green = ($rgb >> 8) & 0xFF;
					$blue = $rgb & 0xFF;
					$modifier = rand($diff * -1, $diff);
					$red += $modifier;
					$green += $modifier;
					$blue += $modifier;

					if ($red > 255) $red = 255;
					if ($green > 255) $green = 255;
					if ($blue > 255) $blue = 255;
					if ($red < 0) $red = 0;
					if ($green < 0) $green = 0;
					if ($blue < 0) $blue = 0;

					$colour = imagecolorallocate($this->image, $red, $green, $blue);
					//imagesetpixel($image, $x, $y, $newcol);
					imagefilledellipse($this->image, $x, $y, $brushsize, $brushsize, $colour);
				}//end if
			}//end for
		}//end for
	}
}
