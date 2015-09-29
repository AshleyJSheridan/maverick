<?php
namespace maverick;

/**
 * the main database class, which just handles as a factory for the other database classes
 * @package Maverick
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class db
{
	/**
	 * all queries should start with a table request
	 * @param string $table set the table name to use for this db query
	 * @return bool
	 */
	public static function table($table)
	{
		$q = \maverick\query::getInstance(true);
		
		if(!strlen($table))
			error::log('Table not specified', true);
		else
			$q->set_from_table($table);
		
		return $q;
	}
	
	/**
	 * create a raw value to be used in a query
	 * this will then be escaped correctly later when the query is built and run
	 * @param mixed $value the value to escape
	 * @return \maverick\db_raw
	 */
	public static function raw($value)
	{
		$v = new \maverick\db_raw($value);
		
		return $v;
	}
}
