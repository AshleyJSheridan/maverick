<?php
class main_controller extends base_controller
{
	function __construct()
	{
		$app = maverick::getInstance();
	}
	
	function page123()
	{
		$app = maverick::getInstance();
		
		//$data = content::get_all_from_test_table();
		
		//$data2 = content::get_from_test_with_matching_id(2);
		
		//$insert = content::update_record();

	}
	
	function home()
	{
		$data = content::get_all_from_test_table();
		
		$view = view::make('includes/template')->with('page', 'home')->with('data', $data)->render();
	}

	function error()
	{
		echo 'error';
	}
}