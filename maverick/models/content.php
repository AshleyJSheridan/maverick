<?php
class content
{
	static function get_day()
	{
		return date("Y-m-d");
	}
	
	static function get_all_from_test_table()
	{
		$data = db::table('test AS t')
			/*->leftJoin('test2 AS t2', array(
				array('t2.test_id', '=', 't.id'),
				array('t2.display', '=', db::raw('yes')),
			))*/
			//->whereIn('t.id', array(1,3))
			//->leftJoin('test2 AS t2', array('t2.test_id', '=', 't.id') )
			//->where('t.id', '>', db::raw('1'))
			//->where(db::raw(1), '=', db::raw(1))
			//->orderBy('t2.id', 'desc')
			->groupBy('t.field_value')
			//->get(array('t.id', 't.field_key', 't.field_value', 't2.id AS test2_id', 't2.other_value'))
			->get(array('t.*', 'COUNT(t.id) AS total'))
		;
		
		return $data->fetch();
	}
	
	static function get_from_test_with_matching_id($id)
	{
		$data = db::table('test2 AS t')->where('t.id', '=', $id)->get();
		
		$q = query::getInstance();
		var_dump($q->get_queries());
		
		return $data->fetch();
	}
	
	static function add_record()
	{
		$insert = db::table('test')
			->insert(array('field_key'=>'insert test', 'field_value'=>date("ymd His") ) );
			/*->insert(array(
				array('field_key'=>'insert bulk test', 'field_value'=>date("ymd His") ),
				array('field_key'=>'insert bulk test2', 'field_value'=>date("ymd His") ),
			));*/
	}
	
	static function update_record()
	{
		$update = db::table('test')
			->where('field_key', '=', db::raw('insert bulk test') )
			->update( array('field_value'=>'ash') );
	}
	
	static function delete_record()
	{
		$delete = db::table('test')
			->where('id', '=', db::raw(10))
			->delete();
	}
}