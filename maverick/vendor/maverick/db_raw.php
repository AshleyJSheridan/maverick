<?php
namespace maverick;

/**
 * creates a simple object used for holding raw strings to use in a query
 * @package Maverick
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class db_raw
{
	public $value='';
	public $type='raw';
	
	/**
	 * creates the db_raw object using the specified value
	 * @param mixed $value the value to push into this instance of the object
	 * @return bool
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}
	
	/**
	 * converts the value to a string, casting if necessary
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->value;
	}
}
