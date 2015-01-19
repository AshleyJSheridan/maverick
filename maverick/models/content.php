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
			->whereNotIn('t.id', array(1,3))
			//->leftJoin('test2 AS t2', array('t2.test_id', '=', 't.id') )
			//->where('t.id', '>', db::raw('1'))
			//->where(db::raw(1), '=', db::raw(1))
			//->orderBy('t2.id', 'desc')
			//->get(array('t.id', 't.field_key', 't.field_value', 't2.id AS test2_id', 't2.other_value'))
			->get(array('t.*'))
		;
		
		return $data->fetch();
	}
}