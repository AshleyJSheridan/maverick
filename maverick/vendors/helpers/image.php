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
	
	public function resize($width, $height)
	{
		// if both sizes are set to auto then do nothing
		if($width === 'auto' && $height === 'auto')
			return false;
		
		if(preg_match('/^(auto|\d+%?)$/', $width) && preg_match('/^(auto|\d+%?)$/', $height) )
		{
			if($width === 'auto')
			{
				if(!is_numeric($this->height) )
					$this->width = $this->height;
				else
				{
					
				}
			}
		}
		exit;
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