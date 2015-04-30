<?php
class main_controller extends base_controller
{
	function __construct() {}
	
	function form()
	{
		$elements = '{
			"name":{"type":"text","label":"Name","placeholder":"John Smith","validation":["required","minlength=2","maxlength=50"],"spellcheck":true},
			"email":{"type":"email","label":"Email","placeholder":"email@test.com","validation":["required","email"]},
			"vehicle":{"type":"select","label":"Vehicle","values":["","car","van","bus","plane","helicopter","other","yes"],"validation":["required"]},
			"colour":{"type":"color","label":"Favourite Colour","value":"#0070b0","validation":[]},
			
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
			'name' => array('required', 'between:2:50'),
			'email' => array('required', 'email'),
			'vehicle' => array('required'),
			'colour' => array('required_if_value:vehicle:car'),
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
				"vehicle":{"type":"select","label":"Vehicle","values":["","car","van","bus","plane","helicopter","other","yes"],"validation":["required"]},
				"colour":{"type":"color","label":"Favourite Colour","value":"#0070b0","validation":[]},

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
		/*$data = array(
			'PHP' => 'Great',
			'MySQL' => 'Great',
			'MSSQL' => 'Good',
			'HTML' => 'Great',
			'CSS' => 'Great',
			'SASS' => 'Good',
			'JavaScript' => 'Great',
			'JQuery' => 'Great',
			'XML' => 'Great',
			'XSLT' => 'Good',
			'BASH' => 'Good',
			'Arduino (C++)' => 'Fair',
			'Python' => 'Basic',
		);*/
		$data = '{"PHP":"Great","MySQL":"Great","MSSQL":"Good","HTML":"Great","CSS":"Great","SASS":"Good","JavaScript":"Great","JQuery":"Great","XML":"Great","XSLT":"Good","BASH":"Good","Arduino (C++)":"Fair","Python":"Basic"}';
		//$headers = array('Great', 'Good', 'Fair', 'Basic');
		$headers = '["Great","Good","Fair","Basic"]';
		$skills = new \helpers\html\tables('skill', 'xref', $data, $headers);
		$skills->xref_x = 'x';
		$skills->caption = 'This table charts my main skills and level of skill in each, out of great, good, fair, and basic for languages';
		
		
		/*$headers = array(
			array('Str', 'Agi', 'Sta', 'Int', 'Spi'),
			array('Human', 'Dwarf', 'Night Elf', 'Gnome', 'Draenei', 'Worgen', 'Pandaren', 'Orc', 'Undead', 'Tauren', 'Troll', 'Blood Elf', 'Goblin'),
		);*/
		$headers = '[
			["Str","Agi","Sta","Int","Spi"],
			["Human","Dwarf","Night Elf","Gnome","Draenei","Worgen","Pandaren","Orc","Undead","Tauren","Troll","Blood Elf","Goblin"]
		]';
		/*$data = array(
			array(20, 20, 20, 20, 20),
			array(25, 16, 21, 19, 19),
			array(16, 24, 20, 20, 20),
			array(15, 22, 20, 23, 20),
			array(21, 17, 20, 20, 22),
			array(23, 22, 20, 16, 19),
			array(20, 18, 21, 19, 22),
			array(23, 17, 21, 17, 22),
			array(19, 18, 20, 18, 25),
			array(25, 16, 21, 16, 22),
			array(21, 22, 20, 16, 21),
			array(17, 22, 20, 23, 18),
			array(17, 22, 20, 23, 18),
		);*/
		$data = '[
			[20,20,20,20,20],
			[25,16,21,19,19],
			[16,24,20,20,20],
			[15,22,20,23,20],
			[21,17,20,20,22],
			[23,22,20,16,19],
			[20,18,21,19,22],
			[23,17,21,17,22],
			[19,18,20,18,25],
			[25,16,21,16,22],
			[21,22,20,16,21],
			[17,22,20,23,18],
			[17,22,20,23,18]
		]';
		$stats = new \helpers\html\tables('stats', 'data', $data, $headers);
		$stats->caption = 'Base stats for World of Warcraft races';
		
		$view = view::make('includes/template')
			->with('page', 'home')
			->with('skills', $skills->render() )
			->with('stats', $stats->render() )
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