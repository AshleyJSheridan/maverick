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
			view::redirect('/' . $app->get_config('cms.path') . '/login');
		
		// set up the main nav
		$this->nav = view::make('cms/includes/admin_nav')->with('params', $params)->render(false);
		
		// this fixes an empty param set
		if(!count($params))
			$params[0] = '';
		
		switch($params[0])
		{
			case '':
				$this->dash();
				break;
			case 'forms':
				$this->forms($params);
				break;
			case 'login':
				$this->login();
				break;
			case 'logout':
				$this->logout();
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

	private function forms($params)
	{
		$page = 'form';
		$app = \maverick\maverick::getInstance();
		
		$this->cms->check_permissions('form', '/' . $app->get_config('cms.path') . '/');

		// show the list of forms as this is the main forms page requested
		if(!isset($params[1]))
		{
			// get list of forms and show them
			$forms = cms::get_forms();
			$headers = '["Name","Language","Total Elements","Actions"]';
			$data = array();
			foreach($forms as $form)
				$data[] = array($form['name'], $form['lang'], $form['total_elements'], $this->generate_actions('forms', $form['id'], array('edit', 'delete', 'duplicate') ) );
			
			$form_table = new \helpers\html\tables('forms', 'layout', $data, $headers);

			$this->load_view($page, array('forms'=>$form_table->render()) );
		}
		else
		{
			switch($params[1])
			{
				case 'edit':
					$this->cms->check_permissions('form_edit', '/' . $app->get_config('cms.path') . '/forms');
					
					if(isset($params[2]) && intval($params[2]))
					{
						$form = cms::get_form($params[2]);
						if(empty($form))
							view::redirect('/' . $app->get_config('cms.path') . '/forms/new');
						
						var_dump($form);
					}
					else
						view::redirect('/' . $app->get_config('cms.path') . '/forms/new');
					break;
				case 'new':
					echo 'new';
					break;
				case 'delete':
					echo 'delete';
					break;
				case 'duplicate':
					echo 'duplicate';
					break;
			}
		}
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

	private function logout()
	{
		$app = \maverick\maverick::getInstance();
		
		unset($_SESSION['maverick_login']);
		unset($_SESSION['maverick_id']);
		
		view::redirect('/' . $app->get_config('cms.path') . '/login');
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
				\maverick_cms\log::record_login($_POST['username'], true);
				
				$_SESSION['maverick_login'] = true;
				$_SESSION['maverick_id'] = $login;
				
				$app = \maverick\maverick::getInstance();

				view::redirect('/' . $app->get_config('cms.path') . '/');
			}
			else
				\maverick_cms\log::record_login($_POST['username']);
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
		{
			$param = trim($param, '/');
			
			if($param == '')
				unset($params[$key]);
			
			if(strpos($param, '/'))
			{
				array_splice($params, $key, 1, explode('/', $param) );
			}
		}

		return $params;
	}
}