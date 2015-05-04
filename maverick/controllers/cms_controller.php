<?php
class cms_controller extends base_controller
{
	private $nav;
	
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
		
		// set up the main nav
		$this->nav = view::make('cms/includes/admin_nav')->with('params', $params)->render(false);
		
		switch($params[0])
		{
			case '':
				$this->dash();
				break;
			case 'forms':
				$this->forms();
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
		$page = 'dash';
		
		$this->load_view($page);
	}

	private function forms()
	{
		$page = 'form';
		
		if(!empty($_POST))
		{
			// process post data here
		}
		else
		{
			// get list of forms and show them
			$forms = cms::get_forms();
			$headers = '["Name","Language","Total Elements"]';
			$data = array();
			foreach($forms as $form)
				$data[] = array($form['name'], $form['lang'], $form['total_elements']);
			
			$form_table = new \helpers\html\tables('forms', 'layout', $data, $headers);
			
			var_dump($form_table->render());
		}
		
		$this->load_view($page, array('forms'=>'wtf') );
	}
	
	
	
	private function load_view($view, $with_params = array() )
	{
		$view = view::make('cms/includes/template')->with('page', $view)->with('admin_nav', $this->nav);
		
		foreach($with_params as $param => $value)
			$view->with($param, $value);
				
		$view->render();
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