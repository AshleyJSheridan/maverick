<?php
class cms_controller extends base_controller
{
	private $nav;
	private $cms;
	private $app;
	
	function __construct()
	{
		if(!isset($_SESSION))
			session_start();
		
		$this->cms = \maverick_cms\cms::getInstance();
		$this->app = \maverick\maverick::getInstance();
	}

	function main($params)
	{
		$params = $this->clean_params($params);
		$app = \maverick\maverick::getInstance();
		
		unset($_SESSION['']);
		
		// check login status
		if(!$this->check_login_status($params))
			view::redirect('Location: /' . $app->get_config('cms.path') . '/login');
		
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
		$app = \maverick\maverick::getInstance();
		
		if(!$this->cms->check_permissions('form'))
			view::redirect('Location: /' . $app->get_config('cms.path') . '/');
		
		
		if(!empty($_POST))
		{
			// process post data here
			
		}
		else
		{
			// get list of forms and show them
			$forms = cms::get_forms();
			$headers = '["Name","Language","Total Elements","Actions"]';
			$data = array();
			foreach($forms as $form)
				$data[] = array($form['name'], $form['lang'], $form['total_elements'], $this->generate_actions('forms', $form['id'], array('delete', 'copy') ) );
			
			$form_table = new \helpers\html\tables('forms', 'layout', $data, $headers);

		}
		
		$this->load_view($page, array('forms'=>$form_table->render()) );
	}
	
	private function generate_actions($section, $id, $actions = array() )
	{
		if(empty($actions) || !intval($id) || empty($section) )
			return '';
		
		$app = \maverick\maverick::getInstance();
		
		$actions_html = '';
		foreach($actions as $action)
		{
			$actions_html .= <<<ACTION
			<a href="/{$app->get_config('cms.path')}/$section/$action/$id">$action</a>
ACTION;
		}
		return $actions_html;
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
		$app = \maverick\maverick::getInstance();
		
		if(isset($_POST['username']) && isset($_POST['password']))
		{
			// check the passed in login details
			$login = cms::check_login($_POST['username'], $_POST['password']);
			
			if($login)
			{
				$_SESSION['maverick_login'] = true;
				$_SESSION['maverick_id'] = $login;
				
				$app = \maverick\maverick::getInstance();

				view::redirect('Location: /' . $app->get_config('cms.path') . '/');
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
		return !(!isset($_SESSION['maverick_login']) && $params[0] != 'login');
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