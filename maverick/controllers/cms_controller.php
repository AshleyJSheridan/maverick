<?php
class cms_controller extends base_controller
{
	protected $nav;
	protected $cms;
	protected $app;
	protected $controller;
	
	/**
	 * main constructor function, just deals with setting some basic options for this controller
	 */
	function __construct()
	{
		if(!isset($_SESSION))
			session_start();
		
		$this->cms = \maverick_cms\cms::getInstance();
		$this->app = \maverick\maverick::getInstance();
		
		$params = $this->app->controller['args'];
		$params = \maverick_cms\cms::clean_params($params); //$this->clean_params($params);
		$this->nav = view::make('cms/includes/admin_nav')->with('params', $params)->render(false);
	}

	/**
	 * the main method that all the admin routes will point to, which will determine which methods handle the request
	 * @param array $params the URL parameters
	 */
	function main($params)
	{
		$params = \maverick_cms\cms::clean_params($params);
		
		// check login status
		//if(!$this->check_login_status($params))
		if(!\maverick_cms\cms::check_login_status($params))
			view::redirect('/' . $this->app->get_config('cms.path') . '/login');
		
		// this fixes an empty param set
		if(!count($params))
			$params[0] = 'dash';

		switch($params[0])
		{
			case 'users':
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
				$this->dispatch_controller($params[0], $params);
				break;
			default:
				// loop through the hooks listed in the main MaVeriCk class
				break;
		}
	}
	
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

					$element_html = cms::get_form_element($element, true);
					break;
				default:
					// handle ajax extensions here
					break;
			}
		}
	}
	
	/**
	 * method that handles the users and permissions within the CMS
	 * @param array $params the URL parameters
	 */
	private function users($params)
	{
		$page = 'users';
		$app = \maverick\maverick::getInstance();
		
		$this->cms->check_permissions('user', '/' . $app->get_config('cms.path') . '/');
		
		// show the list of users in the CMS
		if(!isset($params[1]))
		{
			// get list of users and show them
			$users = cms::get_users();
			$headers = '["ID","Userame","Forename","Surname","Admin?","Actions"]';
			$data = array();
			foreach($users as $user)
			{
				$data[] = array(
					$user['id'],
					$user['username'],
					$user['forename'],
					$user['surname'],
					$user['admin'],
					cms::generate_actions('users', $user['id'], array('edit', 'delete user') )
				);
			}
			
			$user_table = new \helpers\html\tables('forms', 'layout', $data, $headers);
			$user_table->class = 'item_table';
			
			$view_params = array(
				'users'=>$user_table->render(),
				'user_buttons'=> cms::generate_actions('users', '', array('new user','update permissions', 'list permissions'), 'full', 'a'),
				'scripts'=>array(
					'/js/cms/users.js'=>10, 
				)
			);
			$this->load_view($page, $view_params );
		}
		else
		{
			// an action was specified, so instead of showing all the users, deal with the request here
			switch($params[1])
			{
				case 'update_permissions':
					$this->cms->check_permissions('user_update_permissions', '/' . $app->get_config('cms.path') . '/users');
					
					cms::get_permissions_from_code();
					
					\maverick_cms\log::log('permissions', 'update from code', null, 'info');
					
					view::redirect('/' . $app->get_config('cms.path') . "/users");
					break;
				case 'list_permissions':
					$this->cms->check_permissions('user_list_permissions', '/' . $app->get_config('cms.path') . '/users');
					
					$page = 'perms';
					$errors = false;
					
					// process the posted data and update the permissions if the required fields are present and valid
					if(count($_REQUEST))
					{
						$this->cms->check_permissions('user_new_permission', '/' . $app->get_config('cms.path') . '/users');
						
						$rules = array(
							'name' => array('required','alpha_dash'),
							'description' => 'alpha_dash',
							'id' => 'numeric',
						);
						validator::make($rules);

						if(validator::run())
						{
							cms::update_permissions();
							
							\maverick_cms\log::log('permissions', 'update manual', $_REQUEST, 'info');
							
							view::redirect('/' . $app->get_config('cms.path') . "/users/list_permissions");	// this ensures the form won't be re-submitted if the user hits refresh
						}
						else
							$errors = $this->cms->get_all_errors_as_string(null, array('<span class="error">', '</span>') );
					}
					
					// if any have been requested for deletion, process them
					if(isset($params[2]) && $params[2] == 'delete_permission' && isset($params[3]) && is_numeric($params[3]) )
					{
						$deleted = cms::remove_permission($params[3]);
						
						\maverick_cms\log::log('permissions', 'deletion', array('permission_id'=>$params[3]), 'info');
						
						if($deleted !== true)
							$errors = "<span class=\"error\">$deleted</span>";
					}
					
					$perms = cms::get_all_permissions();
					$headers = '["ID","Name","Description","Actions"]';
					$data = array();
					foreach($perms as $perm)
					{
						$data[] = array(
							"<input type=\"text\" value=\"{$perm['id']}\" name=\"id[]\" readonly class=\"short\"/>",
							"<input type=\"text\" value=\"{$perm['name']}\" name=\"name[]\"/>",
							"<input type=\"text\" value=\"{$perm['description']}\" name=\"description[]\"/>",
							cms::generate_actions('users/list_permissions', $perm['id'], array('delete permission') ),
						);
					}
					
					$perm_table = new \helpers\html\tables('users', 'layout', $data, $headers);
					$perm_table->class = 'item_table';
					
					$view_params = array(
						'perms'=>$perm_table->render(),
						'perm_buttons'=> cms::generate_actions('perms', '', array('save permissions', 'add permission'), 'full', 'a') . cms::generate_actions('users', '', array('update permissions'), 'full', 'a'),
						'scripts'=>array(
							'/js/cms/users.js'=>10,
						),
					);
					
					if($errors)
						$view_params['errors'] = $errors;
					
					$this->load_view($page, $view_params );
					break;
				case 'new_user':
					$this->cms->check_permissions('user_create', '/' . $app->get_config('cms.path') . '/users');
					
					$page = 'user_new';
					$errors = false;
					
					if(count($_REQUEST))
					{
						$rules = array(
							'username' => array('required', 'alpha_dash'),
							'forename' => array('required', 'alpha_apos'),
							'surname' => array('required', 'alpha_apos'),
							'email' => array('required', 'email'),
							'password' => array('required'),
							'password_confirm' => array('confirmed:password'),
						);

						validator::make($rules);

						if(validator::run())
						{
							$new_user = cms::add_new_user();
							
							\maverick_cms\log::log('users', 'new', $_REQUEST, 'info');
							
							if($new_user)
								view::redirect('/' . $app->get_config('cms.path') . "/users");
							else
								$errors = "There was a problem saving the user to the database.";
						}
					}
					
					$elements = '{
						"username":{"type":"text","label":"Username","placeholder":"jsmith","validation":["required","alpha_dash"]},
						"forename":{"type":"text","label":"Forename","placeholder":"John","validation":["required","alpha_apos"]},
						"surname":{"type":"text","label":"Surname","placeholder":"Smith","validation":["required","alpha_apos"]},
						"email":{"type":"email","label":"Email Address","placeholder":"jsmith@email.com","validation":["required","email"]},
						"password":{"type":"password","label":"Password","validation":["required"]},
						"password_confirm":{"type":"password","label":"Password Confirmation","validation":["required"]},
						"submit":{"type":"submit","value":"Save Details","class":"action save full"}
					}';
					$new_user_form = new \helpers\html\form('new_user', $elements);
					$new_user_form->class = 'user edit';
					
					
					$view_params = array(
						'scripts'=>array(
							'/js/cms/users.js'=>10, 
						),
						'new_user_form' => $new_user_form->render(),
					);
					
					if($errors)
						$view_params['errors'] = $errors;
					
					$this->load_view($page, $view_params );
					break;
				case 'delete_user':
					$this->cms->check_permissions('user_delete', '/' . $app->get_config('cms.path') . '/users');
					
					if(isset($params[2]) && is_numeric($params[2]) )
					{
						cms::delete_user($params[2]);
						\maverick_cms\log::log('users', 'deleted', array('user_id'=>$params[2]), 'info');
					}
					
					view::redirect('/' . $app->get_config('cms.path') . "/users");
					
					break;
				case 'edit':
					$this->cms->check_permissions('user_edit', '/' . $app->get_config('cms.path') . '/users');
					
					$page = 'user_edit';
					$errors = false;
					
					if(count($_REQUEST) && intval($params[2]))
					{
						$old_user = cms::get_user_details($params[2]);
						$rules = array(
							'username' => array('required', 'alpha_dash'),
							'forename' => array('required', 'alpha_apos'),
							'surname' => array('required', 'alpha_apos'),
							'email' => array('required', 'email'),
							'password' => array("required_if_not_value:username:{$old_user['username']}"),
							'password_confirm' => array('confirmed:password'),
							'permissions'=>array('numeric'),
						);
						validator::make($rules);

						if(validator::run())
						{
							\maverick_cms\log::log('users', 'updated', $_REQUEST, 'info');
							
							cms::update_user_details($params[2]);
						}
					}

					if(isset($params[2]) && is_numeric($params[2]) )
					{
						$user = cms::get_user_details($params[2]);
				
						// generate the form
						$elements = '{
							"username":{"type":"text","label":"Username","value":"'.$user['username'].'","placeholder":"jsmith","validation":["required","alpha_dash"]},
							"forename":{"type":"text","label":"Forename","value":"'.$user['forename'].'","placeholder":"John","validation":["required","alpha_apos"]},
							"surname":{"type":"text","label":"Surname","value":"'.$user['surname'].'","placeholder":"Smith","validation":["required","alpha_apos"]},
							"email":{"type":"email","label":"Email Address","value":"'.$user['email'].'","placeholder":"jsmith@email.com","validation":["required","email"]},
							"password":{"type":"password","label":"Password","validation":["required"]},
							"password_confirm":{"type":"password","label":"Password Confirmation","validation":["required"]}
						}';
						
						// convert to an object because it's easier to work with for this bit
						$elements = json_decode($elements);
						
						// loop through and add in the permissions
						$user_perms = explode(',', $user['permissions']);
						$old_permission_group = '';
						$elements->permissions = (object) array(
							'type'=>'checkbox',
							'label'=>'Permissions',
							'values'=>array(),
						);
						foreach($user['all_permissions'] as $permission)
						{
							$permission_group = substr($permission['name'], 0, (strpos($permission['name'], '_')?strpos($permission['name'], '_'):strlen($permission['name']) ) );
							$first_group = '';
							
							// if this is a new permission group, add an extra class to the first label
							if($permission_group != $old_permission_group)
							{
								$first_group = ' permission_group_start';
								$old_permission_group = $permission_group;
							}
							
							$elements->permissions->values[] = array(
								'value'=>$permission['id'], 
								'label'=>"<span title=\"{$permission['description']}\">{$permission['name']}</span>",
								'checked'=>(in_array($permission['id'], $user_perms))?'checked':'',
								'class'=>"class=\"permissions group_$permission_group $first_group\"",
							);
						}

						// add in the submit button
						$elements->submit = (object) array(
							'type' => 'submit',
							'value' => 'save user',
							'class' => 'action full save_user'
						);

						// convert back to json
						$elements = json_encode($elements);

						$new_user_form = new \helpers\html\form('new_user', $elements);
						$new_user_form->class = 'user edit';
						$new_user_form->autocomplete = false;
						$new_user_form->novalidate = true;

						$view_params = array(
							'scripts'=>array(
								'/js/cms/users.js'=>10, 
							),
							'user_edit_form' => $new_user_form->render(),
						);

						if($errors)
							$view_params['errors'] = $errors;

						$this->load_view($page, $view_params );
					}
					else
						view::redirect('/' . $app->get_config('cms.path') . "/users");
					
					
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

}