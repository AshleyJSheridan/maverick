<?php
use \maverick\db as db;

/**
 * the main model used in the app
 * @package Userspace
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class content
{
	/**
	 * example method showing how to get the current date from a model call
	 * @return string
	 */
	public static function get_day()
	{
		return date("Y-m-d");
	}
	
	/**
	 * example method showing how to make a simple query to the db
	 * @return type
	 */
	public static function get_all_from_test_table()
	{
		$data = db::table('test AS t')
			/*->leftJoin('test2 AS t2', array(
				array('t2.test_id', '=', 't.id'),
				array('t2.display', '=', db::raw('yes')),
			))*/
			//->whereIn('t.id', array(1,3))
			//->leftJoin('test2 AS t2', array('t2.test_id', '=', 't.id') )
			->where('t.id', '>', db::raw('1'))
			//->where(db::raw(1), '=', db::raw(1))
			//->orderBy('t2.id', 'desc')
			->groupBy('t.field_value')
			//->get(array('t.id', 't.field_key', 't.field_value', 't2.id AS test2_id', 't2.other_value'))
			->get(array('t.*', 'COUNT(t.id) AS total'));
		
		return $data->fetch();
	}
	
	/**
	 * simple method to show what happens when you attempt to query a nonexistent table
	 * @return type
	 */
	public static function get_all_from_unspecified_table()
	{
		$data = db::table('')->get();
		
		return $data->fetch();
	}
	
	/**
	 * simple method showing how to retrieve all records from a specific table using an id supplied as a model method argument
	 * @param int $id the id of the record to fetch
	 * @return array
	 */
	public static function get_from_test_with_matching_id($id)
	{
		$data = db::table('test2 AS t')->where('t.id', '=', $id)->get();
		
		$q = query::getInstance();
		var_dump($q->get_queries());
		
		return $data->fetch();
	}
	
	/**
	 * simple method showing how to make an insert into a table in the db
	 * @return bool
	 */
	public static function add_record()
	{
		$insert = db::table('test')
			->insert(array('field_key'=>'insert test', 'field_value'=>date("ymd His") ) );
			/*->insert(array(
				array('field_key'=>'insert bulk test', 'field_value'=>date("ymd His") ),
				array('field_key'=>'insert bulk test2', 'field_value'=>date("ymd His") ),
			));*/
	}
	
	/**
	 * simple method showing how to update a record in the db
	 * @return bool
	 */
	public static function update_record()
	{
		$update = db::table('test')
			->where('field_key', '=', db::raw('insert bulk test') )
			->update(array('field_value'=>'ash') );
	}
	
	/**
	 * simple method showing how to delete a record from the db
	 * @return bool
	 */
	public static function delete_record()
	{
		$delete = db::table('test')
			->where('id', '=', db::raw(10))
			->delete();
	}
}
