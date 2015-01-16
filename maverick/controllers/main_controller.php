<?php
class main_controller extends base_controller
{
	public function page123()
	{
		$app = maverick::getInstance();
		
		echo content::get_all_from_test_table();
		
		//var_dump($app);
	}
	
	public function error()
	{
		echo 'error';
	}
}