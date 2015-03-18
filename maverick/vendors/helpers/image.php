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
}