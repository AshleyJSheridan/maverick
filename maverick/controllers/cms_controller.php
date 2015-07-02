<?php
class cms_controller extends base_controller
{
	private $nav;
	private $cms;
	private $app;
	
	/**
	 * main constructor function, just deals with setting some basic options for this controller
	 */
	function __construct()
	{
		if(!isset($_SESSION))
			session_start();
		
		$this->cms = \maverick_cms\cms::getInstance();
		$this->app = \maverick\maverick::getInstance();
	}

	/**
	 * the main method that all the admin routes will point to, which will determine which methods handle the request
	 * @param array $params the URL parameters
	 */
	function main($params)
	{
		$params = $this->clean_params($params);
		
		//unset($_SESSION['']);
		
		// check login status
		if(!$this->check_login_status($params))
			view::redirect('/' . $this->app->get_config('cms.path') . '/login');
		
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
			case 'ajax':
				$this->ajax($params);
				break;
			default:
				// loop through the hooks listed in the main MaVeriCk class
				break;
		}
	}

	/**
	 * method for showing the main CMS dashboard
	 */
	private function dash()
	{
		$page = 'dash';
		
		$this->load_view($page);
	}

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
					$this->cms->check_permissions('form_edit', '/' . $app->get_config('cms.path') . '/forms');
					
					$element_html = cms::get_form_element_preview();
					break;
				case 'get_form_element_block':
					$this->cms->check_permissions('form_edit', '/' . $app->get_config('cms.path') . '/forms');
					
					$display_order = (isset($_REQUEST['display_order']) && intval($_REQUEST['display_order']) )?intval($_REQUEST['display_order']):1;
					$element = array('type'=>'text', 'display'=>'yes', 'display_order'=>$display_order, 'element_name'=>"new element $display_order" );

					$element_html = cms::get_form_element($element, true);
					break;
				default:
					// handle ajax extensions here
					break;
			}
		}
	}
	
	/**
	 * method that deals with all forms created in the CMS
	 * @param array $params the URL parameters
	 */
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
				$data[] = array($form['name'], $form['lang'], $form['total_elements'], cms::generate_actions('forms', $form['id'], array('edit', 'delete', 'duplicate') ) );
			
			$form_table = new \helpers\html\tables('forms', 'layout', $data, $headers);
			$form_table->class = 'item_table';
			
			$view_params = array(
				'forms'=>$form_table->render(),
				'form_buttons'=> cms::generate_actions('forms', '', array('new form','deleted forms'), 'full', 'a'),
				'scripts'=>array(
					'/js/cms/forms.js'=>10, 
				)
			);
			$this->load_view($page, $view_params );
		}
		else
		{
			// an action was specified, so instead of showing all the forms, deal with the request here
			switch($params[1])
			{
				case 'deleted_forms':
				{
					$this->cms->check_permissions('form_undelete', '/' . $app->get_config('cms.path') . '/forms');
					
					$forms = cms::get_forms(true);
					
					$headers = '["Name","Language","Total Elements","Actions"]';
					$data = array();
					foreach($forms as $form)
						$data[] = array($form['name'], $form['lang'], $form['total_elements'], cms::generate_actions('forms', $form['id'], array('delete full','undelete') ) );

					$form_table = new \helpers\html\tables('forms', 'layout', $data, $headers);
					$form_table->class = 'item_table';

					$view_params = array(
						'forms'=>$form_table->render(),
						'scripts'=>array(
							'/js/cms/forms.js'=>10, 
						)
					);
					$this->load_view($page, $view_params );
					
					break;
				}
				case 'edit':
					// check permissions and redirect to the forms list if the current user doesn't have the right permissions
					$this->cms->check_permissions('form_edit', '/' . $app->get_config('cms.path') . '/forms');
					
					$errors = false;
					
					// redirect to create a new form section if no form ID is in the URL, or a form does not actually exist with that ID
					if(isset($params[2]) && intval($params[2]))
					{
						$form_buttons = cms::generate_actions($params[1], $params[2], array('save', 'add element'), 'full', 'button');
						
						// process the posted data and save the form if the required fields are present
						if(count($_REQUEST))
						{
							$rules = array(
								'form_name' => 'required',
								'lang' => 'required',
							);
							validator::make($rules);

							if(validator::run())
								cms::save_form($params[2]);
							else
								$errors = $this->cms->get_all_errors_as_string(null, array('<span class="error">', '</span>') );
						}
						
						// get the form from the specified ID, returning the user to the main forms list if no form could be found with that ID
						$form = cms::get_form($params[2]);
						if(empty($form))
							view::redirect('/' . $app->get_config('cms.path') . '/forms/new_form');
						
						// build up the extra fields for the form-specific details, like form name, etc
						$form_details = \helpers\html\html::load_snippet(MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/label_wrap.php', array(
							'label'=>'Form Name',
							'element'=>\helpers\html\html::load_snippet(MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/input_text.php', array(
									'value'=>"value=\"{$form[0]['form_name']}\"",
									'placeholder'=>"placeholder=\"form name\"",
									'name'=>'form_name'
								))
							)
						);
						$form_details .= \helpers\html\html::load_snippet(MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/label_wrap.php', array(
							'label'=>'Form Language',
							'element'=>\helpers\html\html::load_snippet(MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/input_select.php', array(
									'values'=> $this->cms->build_select_options(
										cms::get_languages(false, true),
										$form[0]['lang'],
										true,
										MAVERICK_BASEDIR . 'vendor/helpers/html/snippets'
									),
									'name'=>'lang'
								))
							)
						);
						
						$view_params = array(
							'form'=>$form,
							'form_buttons'=>$form_buttons,
							'form_details'=>$form_details,
							'scripts'=>array(
								'/js/cms/forms.js'=>10, 
								'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js'=>5,
							)
						);
						if($errors)
							$view_params['errors'] = $errors;

						$this->load_view('form_edit', $view_params );
					}
					else
						view::redirect('/' . $app->get_config('cms.path') . '/forms/new_form');
					
					break;
				case 'new_form':
					$this->cms->check_permissions('form_new', '/' . $app->get_config('cms.path') . '/forms');
					// create a new blank form, get the ID for it and redirect to the edit screen with it
					$new_form_id = cms::new_form();
					
					view::redirect('/' . $app->get_config('cms.path') . "/forms/edit/$new_form_id");
					
					break;
				case 'delete':
					$this->cms->check_permissions('form_delete', '/' . $app->get_config('cms.path') . '/forms');
					
					// check a form ID was passed for deletion
					if(isset($params[2]) && intval($params[2]))
					{
						$deleted = cms::delete_form($params[2]);
						
						if($deleted->fetch() )
							view::redirect('/' . $app->get_config('cms.path') . '/forms');
					}
					else
						view::redirect('/' . $app->get_config('cms.path') . '/forms');
					break;
				case 'undelete':
					$this->cms->check_permissions('form_undelete', '/' . $app->get_config('cms.path') . '/forms');
					
					// check a form ID was passed for restoration
					if(isset($params[2]) && intval($params[2]))
					{
						$deleted = cms::undelete_form($params[2]);
						
						if($deleted->fetch() )
							view::redirect('/' . $app->get_config('cms.path') . '/forms');
					}
					else
						view::redirect('/' . $app->get_config('cms.path') . '/forms');
					break;
				case 'delete_full':
					$this->cms->check_permissions('form_delete_full', '/' . $app->get_config('cms.path') . '/forms/deleted_forms');
					

					// check a form ID was passed for full deletion
					if(isset($params[2]) && intval($params[2]))
					{
						$deleted = cms::delete_form($params[2], true);
						
						if($deleted->fetch() )
							view::redirect('/' . $app->get_config('cms.path') . '/forms');
					}
					else
						view::redirect('/' . $app->get_config('cms.path') . '/forms');
					break;
				case 'duplicate':
					$this->cms->check_permissions('form_copy', '/' . $app->get_config('cms.path') . '/forms');
					
					// check a form ID was passed for the form to copy
					if(isset($params[2]) && intval($params[2]))
					{
						cms::duplicate_form($params[2]);
						
						view::redirect('/' . $app->get_config('cms.path') . '/forms');
					}
					break;
			}
		}
	}
	
	/**
	 * load in an admin view - this is basically a small wrapper to view::make(), it just adds in the admin nav and any other parameters passed into it
	 * this allows things to be added to all admin sections easily, in one method
	 * @param string $view the view to load - this is the same format as view::make()
	 * @param array $with_params any extra parameters that need to be passed in to this view
	 */
	private function load_view($view, $with_params = array() )
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
	
	/**
	 * checks the status of a user login and determines if it is valid
	 * @param array $params the URL params - used to prevent a redirect loop
	 * @return bool
	 */
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