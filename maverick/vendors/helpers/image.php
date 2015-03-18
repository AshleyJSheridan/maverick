<?php
namespace helpers;

class image
{
	private $width = 100;
	private $height = 100;
	private $filename = '';
	private $format = 'jpg';
	private $mime = 'image/jpeg';
	private $image;

	public function __construct($from_file=null, $width=100, $height=100, $format='jpg')
	{
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

		return (!empty($this->image))?$this:false;
	}
	
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
		}
		
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
			}
		}
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
			
		}
	}
	
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
			}
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
				}
				return ($this->image = $image_p);
				
				break;
			}
		}
	}
	
	public function output($filename=null)
	{
		if(empty($filename))
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
	
	private function constrain_int($int, $min=-255, $max=255)
	{
		if($int < $min)
			$int = $min;
		if($int > $max)
			$int = $max;
		
		return $int;
	}
	
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
		}
	}
		
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
		}
	}
	
	private function custom_noise($diff)
	{
		$imagex = imagesx($this->image);
		$imagey = imagesy($this->image);

		for ($x = 0; $x < $imagex; ++$x)
		{
			for ($y = 0; $y < $imagey; ++$y)
			{
				if (rand(0,1)) {
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
				}
			}
		}
	}
	
	private function custom_oil($strength, $diff, $brushsize)
	{
		$imagex = imagesx($this->image);
		$imagey = imagesy($this->image);

		for ($x = 0; $x < $imagex; ++$x)
		{
			for ($y = 0; $y < $imagey; ++$y)
			{
				if (rand(0,$strength) < 2)
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
				}
			}
		}
	}
}