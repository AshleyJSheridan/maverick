<?php
class cms_controller extends base_controller
{
	function __construct()
	{
		if(!isset($_SESSION))
			session_start();
	}

	function main($params)
	{
		$params = $this->clean_params($params);
		$app = \maverick\maverick::getInstance();
		
		// check login status
		if(!$this->check_login_status($params))
		{
			header('Location: /' . $app->get_config('cms.path') . '/login');
			exit;
		}
		
		switch($params[0])
		{
			case '':
				$this->dash();
				break;
			case 'form':
				$this->form();
				break;
			case 'login':
				$this->login();
				break;
			default:
				// loop through the hooks listed in the main MaVeriCk class
				break;
		}
	}

	private function dash()
	{
		echo 'dash';
	}

	private function form()
	{
		
	}

	private function login()
	{
		if(isset($_POST['username']) && isset($_POST['password']))
		{
			// check the passed in login details
			$login = cms::check_login($_POST['username'], $_POST['password']);
			
			if($login)
			{
				$_SESSION['maverick_admin'] = true;
				
				$app = \maverick\maverick::getInstance();
				
				header('Location: /' . $app->get_config('cms.path'));
				exit;
			}
		}
		
		$elements = '{
			"username":{"type":"text","label":"Username","validation":["required"]},
			"password":{"type":"password","label":"Password","validation":["required"]},
			"submit":{"type":"submit","value":"Login"}
		}';
		$form = new \helpers\html\form('login', $elements);
		$form->method = 'post';
		
		$view = view::make('cms/includes/template_basic')->with('page', 'login')->with('login_form', $form->render() )->render();
	}
	
	
	private function check_login_status($params)
	{
		return !(!isset($_SESSION['maverick_admin']) && $params[0] != 'login');
	}
	
	/**
	 * clean up the list of passed in url parameters
	 * @param array $params the array of parameters to clean
	 * @return array
	 */
	private function clean_params($params)
	{
		foreach($params as $key => &$param)
			$param = trim($param, '/');
		
		return $params;
	}
}