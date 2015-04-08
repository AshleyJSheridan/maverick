<?php
namespace maverick;

/**
 * creates a simple object used for holding raw strings to use in a query
 */
class db_raw
{
	public $value='';
	public $type='raw';
	
	/**
	 * creates the db_raw object using the specified value
	 * @param mixed $value
	 */
	function __construct($value)
	{
		$this->value = $value;
	}
	
	/**
	 * converts the value to a string, casting if necessary
	 * @return string
	 */
	function __toString()
	{
		return (string)$this->value;
	}
}