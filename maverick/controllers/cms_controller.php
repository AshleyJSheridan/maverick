<?php
/**
 * the main maverick cms controller, which will delegate out to other controllers for specific sections within the cms
 * @package MaverickCMS
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class cms_controller extends base_controller
{
	protected $nav;
	protected $cms;
	protected $app;
	protected $controller;
	
	/**
	 * main constructor function, just deals with setting some basic options for this controller
	 */
	public function __construct()
	{
		if(!isset($_SESSION))
			session_start();
		
		$this->cms = \maverick_cms\cms::getInstance();
		$this->app = \maverick\maverick::getInstance();
		
		$params = $this->app->controller['args'];
		$params = \maverick_cms\cms::clean_params($params);
		$this->nav = view::make('cms/includes/admin_nav')->with('params', $params)->render(false);
	}

	/**
	 * the main method that all the admin routes will point to, which will determine which methods handle the request
	 * @param array $params the URL parameters
	 * @return bool
	 */
	public function main($params)
	{
		$params = \maverick_cms\cms::clean_params($params);
		
		// check login status
		if(!\maverick_cms\cms::check_login_status($params))
			view::redirect('/' . $this->app->get_config('cms.path') . '/login');
		
		// this fixes an empty param set
		if(!count($params))
			$params[0] = 'dash';

		switch($params[0])
		{
			case 'ajax':
				$this->{$params[0]}($params);
				break;
			case 'login':
			case 'logout':
			case 'dash':
				$this->{$params[0]}();
				break;
			case 'logs':
			case 'forms':
			case 'users':
			case 'tags':
			case 'cultures':
			case 'pages':
				$this->dispatch_controller($params[0], $params);
				break;
			default:
				// loop through the hooks listed in the main MaVeriCk class
				break;
		}//end switch
	}
	
	/**
	 * calls out to an external controller for a section of the cms
	 * @param string $controller the controller to call
	 * @param array  $params     extra parameters, such as method and arguments
	 * @return bool
	 */
	private function dispatch_controller($controller, $params)
	{
		$method = $controller;
		$controller = "{$controller}_controller";
		if(class_exists($controller) && $this->controller = new $controller)
			$this->controller->{$method}($params);
		//TODO: add error call here if the admin controller for this section doesn't exist
	}

	/**
	 * method for showing the main CMS dashboard
	 * @return bool
	 */
	private function dash()
	{
		$page = 'dash';
		
		$this->load_view($page);
	}

	/**
	 * method to handle ajax requests coming through to the admin area
	 * @param array $params the URL parameters
	 * @todo the ajax URLs are currently hard-coded into the javascript - need a way to pass the value in the PHP config to the js
	 * @return bool
	 */
	private function ajax($params)
	{
		$app = \maverick\maverick::getInstance();
		
		if(!isset($params[1]) )
			exit;
		else
		{
			switch($params[1])
			{
				case 'get_form_element':
					$this->cms->check_permissions(array('form', 'form_edit'), '/' . $app->get_config('cms.path') . '/forms');
					
					$element_html = cms::get_form_element_preview();
					break;
				case 'get_form_element_block':
					$this->cms->check_permissions(array('form', 'form_edit'), '/' . $app->get_config('cms.path') . '/forms');
					
					$display_order = (isset($_REQUEST['display_order']) && intval($_REQUEST['display_order']) )?intval($_REQUEST['display_order']):1;
					$element = array('type'=>'text', 'display'=>'yes', 'display_order'=>$display_order, 'element_name'=>"new element $display_order" );
					$element['values_html'] = \helpers\html\html::load_snippet(
						MAVERICK_VIEWSDIR . "cms/includes/snippets/list_values_block.php",
						array(
							'id'=>($display_order-1)
						)
					);

					$element_html = cms::get_form_element($element, true);
					break;
				default:
					// handle ajax extensions here
					break;
			}//end switch
		}//end if
	}

	/**
	 * load in an admin view - this is basically a small wrapper to view::make(), it just adds in the admin nav and any other parameters passed into it
	 * this allows things to be added to all admin sections easily, in one method
	 * @param string $view        the view to load - this is the same format as view::make()
	 * @param array  $with_params any extra parameters that need to be passed in to this view
	 * @return bool
	 */
	protected function load_view($view, $with_params = array() )
	{
		// load scripts that need to be included on all pages and then sort them by their priority
		$global_scripts = array(
			'https://code.jquery.com/jquery-2.1.4.min.js' => 0,
		);
		if(!isset($with_params['scripts']))
			$with_params['scripts'] = array();
		
		$with_params['scripts'] = array_merge($with_params['scripts'], $global_scripts);
		$with_params['scripts'] = $this->sort_external_assets($with_params['scripts']);	// sort the assets by priority

		
		$view = view::make('cms/includes/template')->with('page', $view)->with('admin_nav', $this->nav);
		
		foreach($with_params as $param => $value)
			$view->with($param, $value);
				
		$view->render();
	}
	
	/**
	 * sorts external assets (e.g. scripts, css) to ensure that they can be output in a sane order on the front-end
	 * returns a sorted list of assets
	 * @param array $assets an associative array of assets to sort, the key being the asset path, and the value being the priority value - lower values = more important
	 * @return array
	 */
	private function sort_external_assets($assets)
	{
		asort($assets);
		
		return $assets;
	}

	/**
	 * log a user out of the admin area
	 * @return bool
	 */
	private function logout()
	{
		$app = \maverick\maverick::getInstance();
		
		unset($_SESSION['maverick_login']);
		unset($_SESSION['maverick_id']);
		
		view::redirect('/' . $app->get_config('cms.path') . '/login');
	}
	
	/**
	 * handles the showing of the login form and login of a user
	 * failed logins are recorded
	 * @return bool
	 */
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
		}//end if
		
		$elements = '{
			"username":{"type":"text","label":"Username","validation":["required"]},
			"password":{"type":"password","label":"Password","validation":["required"]},
			"submit":{"type":"submit","value":"Login"}
		}';
		$form = new \helpers\html\form('login', $elements);
		$form->method = 'post';
		
		$view = view::make('cms/includes/template_basic')->with('page', 'login')->with('login_form', $form->render() )->render();
	}
}
