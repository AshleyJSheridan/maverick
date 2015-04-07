<?php
namespace maverick;

/**
 * the main database class, which just handles as a factory for the other database classes
 */
class db
{
	private function __construct() {}

	// all queries should start with a table request
	public static function table($table)
	{
		$q = \maverick\query::getInstance(true);
		
		if(!strlen($table))
			error::log('Table not specified', true);
		else
			$q->set_from_table($table);
		
		return $q;
	}
	
	public static function raw($value)
	{
		$v = new \maverick\db_raw($value);
		
		return $v;
	}
}