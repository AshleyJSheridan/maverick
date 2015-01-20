<?php
class main_controller extends base_controller
{
	function __construct() {}
	
	function page123()
	{
		$app = maverick::getInstance();
		
		//$data = content::get_all_from_test_table();
		
		//$data2 = content::get_from_test_with_matching_id(2);
		
		//$insert = content::update_record();
	}
	
	function form()
	{
		$view = view::make('includes/template')->with('page', 'form')->render();
	}
	
	function form_post()
	{
		$app = maverick::getInstance();
		
		$rules = array(
			'name' => array('required', 'alpha'),
			'age' => array('numeric', 'between:18:100'),
			'email' => array('required', 'email'),
			'postcode' => array('required', 'regex:/^([a-pr-uwyz][a-hk-y]{0,1}\d[\da-hjkst]{0,1} \d[abd-hjlnp-uw-z]{2})$/i'),
			'web_address' => 'url',
			'phone' => 'phone',
		);
		
		validator::make($rules);
		
		if(validator::run())
		{
			// form validates - do stuff
			
		}
		else
		{
			// errors - pass back to the form and show errors
		}
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