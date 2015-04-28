<?php
class main_controller extends base_controller
{
	function __construct() {}
	
	function form()
	{
		$elements = '{
			"name":{"type":"text","label":"Name","placeholder":"John Smith","validation":["required","size=50","minlength=2","maxlength=50"],"spellcheck":true},
			"email":{"type":"email","label":"Email","placeholder":"email@test.com","validation":["required","email"]},
			"price":{"type":"number","label":"Price in £","placeholder":"9.99","validation":["required","step=0.01","min=1","max=10"]},
			"colour":{"type":"color","label":"Favourite Colour","value":"#0070b0","validation":["required"]},
			"dob":{"type":"date","label":"Date of Birth","validation":["required","min=1997-04-28","max=2015-04-28"]},
			
			"submit":{"type":"submit","value":"Submit","class":"form_submit"}
		}';
		$form = new \helpers\html\form('form', $elements);
		$form->labels = 'wrap';
		//$form->novalidate = true;
		$form->enctype = 'multipart/form-data';
		$form->snippets = MAVERICK_VIEWSDIR . 'includes/snippets';
		
		$view = view::make('includes/template')->with('page', 'form')->with('form', $form)->render();
	}

	function form_post()
	{
		$app = \maverick\maverick::getInstance();
		
		$rules = array(
			'email' => array('required', 'email'),
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
				"name":{"type":"text","label":"Name","placeholder":"John Smith","validation":["required","size=50","minlength=2","maxlength=50"],"spellcheck":true},
				"email":{"type":"email","label":"Email","placeholder":"email@test.com","validation":["required","email"]},
				"price":{"type":"number","label":"Price in £","placeholder":"9.99","validation":["required","step=0.01","min=1","max=10"]},
				"colour":{"type":"color","label":"Favourite Colour","value":"#0070b0","validation":["required"]},

				"submit":{"type":"submit","value":"Submit","class":"form_submit"}
			}';
			$form = new \helpers\html\form('form', $elements);
			$form->labels = 'wrap';
			$form->novalidate = true;
			$form->enctype = 'multipart/form-data';
			$form->snippets = MAVERICK_VIEWSDIR . 'includes/snippets';

			$view = view::make('includes/template')->with('page', 'form')->with('form', $form)->render();
		}
	}
	
	function home()
	{
		$image_1 = new \helpers\image(MAVERICK_HTDOCS . 'img/BlueMarbleWest.jpg');
		//$image_2 = new \helpers\image(MAVERICK_HTDOCS . 'img/BlueMarbleWest2.jpg');
		$image_2 = new \helpers\image(MAVERICK_HTDOCS . 'img/Road.jpg');
		//$image_2 = new \helpers\image(MAVERICK_HTDOCS . 'img/400/earth_oil.jpg');
		
		$similarity = \helpers\image::compare($image_1, $image_2);

		$view = view::make('includes/template')
			->with('page', 'home')
			->render(true, true);
	}

	function error()
	{
		echo 'error';
	}
	
	static function parse_handler_example($matches = array() )
	{
		$char = '';
		switch($matches[1])
		{
			case 'arrow':
				$char = '→';
				break;
			case 'chess':
				$char = '♔';
				break;
			case 'snowman':
				$char = '☃';
				break;
		}
		
		if(strlen($char))
			$matches[0] = str_repeat ($char, intval($matches[3]));
		
		return $matches[0];
	}
}