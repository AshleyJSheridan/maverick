<?php
class main_controller extends base_controller
{
	function __construct() {}
	
	function form()
	{
		$elements = '{
			"email":{"type":"email","label":"Email","class":"form_email","placeholder":"email@test.com","validation":["required","email"]},
			"image":{"type":"file","label":"Image","class":"form_image","validation":["required"]},
			
			"submit":{"type":"submit","value":"Submit","class":"form_submit"}
		}';
		$form = new \helpers\html\form('form', $elements);
		$form->labels = 'wrap';
		$form->novalidate = true;
		$form->enctype = 'multipart/form-data';
		$form->snippets = MAVERICK_VIEWSDIR . 'includes/snippets';
		
		$view = view::make('includes/template')->with('page', 'form')->with('form', $form)->render();
	}

	function form_post()
	{
		$app = \maverick\maverick::getInstance();
		
		$rules = array(
			'email' => array('required', 'email', 'size:14'),
			'image' => array('required', 'mimes:jpeg:gif:png:text/*'),
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
				"email":{"type":"email","label":"Email","class":"form_email","placeholder":"email@test.com","validation":["required","email"]},
				"image":{"type":"file","label":"image","class":"form_image","validation":["required"]},
				
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