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
		$elements = '{
			"name":{"type":"text","label":"Name","class":"form_name","id":"form_name","value":"John Smith","placeholder":"John Smith","validation":["required","alpha"]},
			"age":{"type":"number","label":"Age","class":"form_age","placeholder":"42","validation":["required","numeric","between:18:100"]},
			"email":{"type":"email","label":"Email","class":"form_email","placeholder":"email@test.com","validation":["required","email"]},
			"postcode":{"type":"text","label":"Postcode","class":"form_postcode","placeholder":"w1 1ab","validation":["required","regex:/^([a-pr-uwyz][a-hk-y]{0,1}\\\d[\\\da-hjkst]{0,1} \\\d[abd-hjlnp-uw-z]{2})$/i"]},
			"web_address":{"type":"text","label":"Web Address","class":"form_web_address","placeholder":"http://www.somesite.com","validation":["url"]},
			"phone":{"type":"text","label":"Phone","class":"form_phone","placeholder":"0123456789","validation":["phone"]}
		}';
		$form = new \helpers\html\form('form', $elements);
		$form->labels = 'before';
		
		$view = view::make('includes/template')->with('page', 'form')->with('form', $form)->render();
	}
	
	function regex_route_test_controller()
	{
		$data = content::get_all_from_unspecified_table();
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

		$view = view::make('includes/template')->with('page', 'home')->with('data', $data)->render();
	}

	function error()
	{
		echo 'error';
	}
}