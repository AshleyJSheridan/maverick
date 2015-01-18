<?php
class db_raw
{
	public $value='';
	public $type='raw';
	
	function __construct($value)
	{
		$this->value = $value;
	}
	
	function __toString()
	{
		return $this->value;
	}
}