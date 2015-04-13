<?php
class main_controller extends base_controller
{
	function __construct() {}
	
	function form()
	{
		$elements = '{
			"title":{"type":"select","label":"Title","class":"form_title","id":"form_title","values":["Mr","Mrs","Miss","Other"],"validation":["required"]},
			"name":{"type":"text","label":"Name","class":"form_name","id":"form_name","value":"John Smith","placeholder":"John Smith","validation":["required","alpha"]},
			"age":{"type":"number","label":"Age","class":"form_age","placeholder":"42","validation":["required","numeric","between:18:100"]},
			"email":{"type":"email","label":"Email","class":"form_email","placeholder":"email@test.com","validation":["required","email"]},
			"postcode":{"type":"text","label":"Postcode","class":"form_postcode","placeholder":"w1 1ab","validation":["required","regex:/^([a-pr-uwyz][a-hk-y]{0,1}\\\d[\\\da-hjkst]{0,1} \\\d[abd-hjlnp-uw-z]{2})$/i"]},
			"web_address":{"type":"text","label":"Web Address","class":"form_web_address","placeholder":"http://www.somesite.com","validation":["url"]},
			"phone":{"type":"text","label":"Phone","class":"form_phone","placeholder":"0123456789","validation":["phone"]},
			"colour":{"type":"radio","label":"Favourite Colour","class":"form_colour","values":["Red","Blue","Green","Yellow","Black"]},
			
			"submit":{"type":"submit","value":"Submit","class":"form_submit"}
		}';
		$form = new \helpers\html\form('form', $elements);
		$form->labels = 'wrap';
		$form->novalidate = true;
		$form->snippets = MAVERICK_VIEWSDIR . 'includes/snippets';
		
		$view = view::make('includes/template')->with('page', 'form')->with('form', $form)->render();
	}

	function form_post()
	{
		$app = \maverick\maverick::getInstance();
		
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
			$elements = '{
				"title":{"type":"select","label":"Title","class":"form_title","id":"form_title","values":["Mr","Mrs","Miss","Other"],"validation":["required"]},
				"name":{"type":"text","label":"Name","class":"form_name","id":"form_name","value":"John Smith","placeholder":"John Smith","validation":["required","alpha"]},
				"age":{"type":"number","label":"Age","class":"form_age","placeholder":"42","validation":["required","numeric","between:18:100"]},
				"email":{"type":"email","label":"Email","class":"form_email","placeholder":"email@test.com","validation":["required","email"]},
				"postcode":{"type":"text","label":"Postcode","class":"form_postcode","placeholder":"w1 1ab","validation":["required","regex:/^([a-pr-uwyz][a-hk-y]{0,1}\\\d[\\\da-hjkst]{0,1} \\\d[abd-hjlnp-uw-z]{2})$/i"]},
				"web_address":{"type":"text","label":"Web Address","class":"form_web_address","placeholder":"http://www.somesite.com","validation":["url"]},
				"phone":{"type":"text","label":"Phone","class":"form_phone","placeholder":"0123456789","validation":["phone"]},
				"colour":{"type":"radio","label":"Favourite Colour","class":"form_colour","values":["Red","Blue","Green","Yellow","Black"]},

				"submit":{"type":"submit","value":"Submit","class":"form_submit"}
			}';
			$form = new \helpers\html\form('form', $elements);
			$form->labels = 'wrap';
			$form->novalidate = true;
			$form->snippets = MAVERICK_VIEWSDIR . 'includes/snippets';

			$view = view::make('includes/template')->with('page', 'form')->with('form', $form)->render();
		}
	}
	
	function home()
	{
		$list_json = '["a","b","c","d",[
			1,2,3,[
				"x","y","z"
				],4,5
			]
			,"e","f","g"]';
		$list_types = array('ul', 'ol', 'ul');
		$list = new \helpers\html\lists($list_json, $list_types);
		
		$data = array(
			'red',
			'green',
			'blue',
			'yellow',
			array(
				'colour1' => 'orange',
				'colour2' => 'purple',
				'colour3' => 'pink',
			)
		);
		
		$view = view::make('includes/template')->with('page', 'home')->with('list', $list)->with('data', $data)->render(true, true);
	}

	function error()
	{
		echo 'error';
	}
}