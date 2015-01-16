<?php
class db
{
	
	
	private function __construct() {}

	// all queries should start with a table request
	public static function table($table)
	{
		$q = query::getInstance();
		
		if(!strlen($table))
			return $q;	// TODO: consider throwing an error here
		else
			$q->set_from_table($table);
		
		return $q;
	}
	
}