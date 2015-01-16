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
				array('t2.display', '=', 'yes'),
			))
			//->leftJoin('test2 AS t2', array('t2.test_id', '=', 't.id') )
		;
		
		var_dump($data);
	}
}