<?php
class main_controller extends base_controller
{
	public function page123()
	{
		$app = maverick::getInstance();
		
		$data = content::get_all_from_test_table();
		
		$data2 = content::get_from_test_with_matching_id(2);
		
		var_dump($data, $data2);
	}
	
	public function error()
	{
		echo 'error';
	}
}