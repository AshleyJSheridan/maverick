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
	
	function regex_route_test_controller()
	{
		$data = content::get_all_from_unspecified_table();
	}
	
	function form_post()
	{
		$app = maverick::getInstance();
		
		$rules = array(
			'name' => array('required', 'alpha', 'email'),
			'age' => array('numeric', 'between:18:100'),
			'email' => array('required', 'email'),
			'postcode' => array('required', 'regex:/^([a-pr-uwyz][a-hk-y]{0,1}\d[\da-hjkst]{0,1} \d[abd-hjlnp-uw-z]{2})$/i'),
			'web_address' => 'url',
			'phone' => 'phone',
		);
		
		validator::make($rules);
		
		if(validator::run())
		{
			// form validates - do stuff with the data - maybe pass it to a model to save in a DB and then continue to a thanks page
			
		}
		else
		{
			// errors - pass back to the form and show errors
			$view = view::make('includes/template')->with('page', 'form')->render();
		}
	}
	
	function home()
	{
		$data = content::get_all_from_test_table();

		$view = view::make('includes/template')->with('page', 'home')->render();
	}

	function error()
	{
		echo 'error';
	}
}