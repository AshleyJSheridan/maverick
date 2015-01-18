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
			->leftJoin('test2 AS t2', array(
				array('t2.test_id', '=', 't.id'),
				//array('t2.display', '=', db::raw('yes')),
			))
			//->leftJoin('test2 AS t2', array('t2.test_id', '=', 't.id') )
			//->where('t.field_key', '=', db::raw('test'))
			//->where(db::raw(1), '=', db::raw(1))
			//->groupBy('t2.other_value', 'desc')
			//->get(array('t.id', 't.field_key', 't.field_value', 't2.id AS test2_id', 't2.other_value'))
			->get()
		;
		
		var_dump($data->fetch());
	}
}