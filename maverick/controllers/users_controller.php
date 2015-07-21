<?php
class users_controller extends cms_controller
{	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * method that handles the users list and passes control out to other methods for more specific actions with users and permissions
	 * @param array $params the URL parameters
	 */
	public function users($params)
	{
		$page = 'users';

		$this->cms->check_permissions('user', '/' . $this->app->get_config('cms.path') . '/');
		
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
			if(method_exists($this, $params[1]))
				$this->{$params[1]}($params);
		}
	}
	
	/**
	 * updates the list of permissions in the database by analysing the code for permission checks
	 * @param array $params the URL parameters
	 */
	private function update_permissions($params)
	{
		$this->cms->check_permissions('user_update_permissions', '/' . $this->app->get_config('cms.path') . '/users');
					
		cms::get_permissions_from_code();

		\maverick_cms\log::log('permissions', 'update from code', null, 'info');

		view::redirect('/' . $this->app->get_config('cms.path') . "/users");
	}
	
	/**
	 * list the permissions held in the permissions table of the database, and handles the ability to update their names, descriptions, and delete them if they're not in use
	 * @param array $params the URL parameters
	 */
	private function list_permissions($params)
	{
		$this->cms->check_permissions('user_list_permissions', '/' . $this->app->get_config('cms.path') . '/users');
					
		$page = 'perms';
		$errors = false;

		// process the posted data and update the permissions if the required fields are present and valid
		if(count($_REQUEST))
		{
			$this->cms->check_permissions('user_new_permission', '/' . $this->app->get_config('cms.path') . '/users');

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

				view::redirect('/' . $this->app->get_config('cms.path') . "/users/list_permissions");	// this ensures the form won't be re-submitted if the user hits refresh
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
	}
	
	/**
	 * a form that allows a new user to be created with basic details
	 * @param array $params the URL parameters
	 */
	private function new_user($params)
	{
		$this->cms->check_permissions('user_create', '/' . $this->app->get_config('cms.path') . '/users');
					
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
					view::redirect('/' . $this->app->get_config('cms.path') . "/users");
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
	}
	
	/**
	 * handles deleting a user completely from the database
	 * @param array $params the URL parameters
	 */
	private function delete_user($params)
	{
		$this->cms->check_permissions('user_delete', '/' . $this->app->get_config('cms.path') . '/users');

		if(isset($params[2]) && is_numeric($params[2]) )
		{
			cms::delete_user($params[2]);
			\maverick_cms\log::log('users', 'deleted', array('user_id'=>$params[2]), 'info');
		}

		view::redirect('/' . $app->get_config('cms.path') . "/users");
	}
	
	/**
	 * deals with updating a user profile and their assigned permissions
	 * @param array $params the URL parameters
	 */
	private function edit_user($params)
	{
		$this->cms->check_permissions('user_edit', '/' . $this->app->get_config('cms.path') . '/users');
					
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
			view::redirect('/' . $this->app->get_config('cms.path') . "/users");
	}
}