<?php
class forms_controller extends cms_controller
{	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * method that deals with all forms created in the CMS
	 * @param array $params the URL parameters
	 */
	function forms($params)
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
					$this->cms->check_permissions(array('form', 'form_undelete'), '/' . $app->get_config('cms.path') . '/forms');
					
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
					$this->cms->check_permissions(array('form', 'form_edit'), '/' . $app->get_config('cms.path') . '/forms');
					
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
							{
								cms::save_form($params[2]);
								
								\maverick_cms\log::log('forms', 'updated', $_REQUEST, 'info');
							}
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
					$this->cms->check_permissions(array('form', 'form_new'), '/' . $app->get_config('cms.path') . '/forms');
					// create a new blank form, get the ID for it and redirect to the edit screen with it
					$new_form_id = cms::new_form();
					
					\maverick_cms\log::log('forms', 'new', array('form_id'=>$new_form_id), 'info');
					
					view::redirect('/' . $app->get_config('cms.path') . "/forms/edit/$new_form_id");
					
					break;
				case 'delete':
					$this->cms->check_permissions(array('form', 'form_delete'), '/' . $app->get_config('cms.path') . '/forms');
					
					// check a form ID was passed for deletion
					if(isset($params[2]) && intval($params[2]))
					{
						$deleted = cms::delete_form($params[2])->fetch();
						
						\maverick_cms\log::log('forms', 'deleted (soft)', array('form_id'=>$params[2]), 'info');
						
						if($deleted)
							view::redirect('/' . $app->get_config('cms.path') . '/forms');
					}
					else
						view::redirect('/' . $app->get_config('cms.path') . '/forms');
					break;
				case 'undelete':
					$this->cms->check_permissions(array('form', 'form_undelete'), '/' . $app->get_config('cms.path') . '/forms');
					
					// check a form ID was passed for restoration
					if(isset($params[2]) && intval($params[2]))
					{
						$undeleted = cms::undelete_form($params[2])->fetch();
						
						\maverick_cms\log::log('forms', 'undeleted', array('form_id'=>$params[2]), 'info');
						
						if($undeleted)
							view::redirect('/' . $app->get_config('cms.path') . '/forms');
					}
					else
						view::redirect('/' . $app->get_config('cms.path') . '/forms');
					break;
				case 'delete_full':
					$this->cms->check_permissions(array('form', 'form_delete_full'), '/' . $app->get_config('cms.path') . '/forms/deleted_forms');
					

					// check a form ID was passed for full deletion
					if(isset($params[2]) && intval($params[2]))
					{
						$deleted = cms::delete_form($params[2], true)->fetch();
						
						\maverick_cms\log::log('forms', 'deleted (hard)', array('form_id'=>$params[2]), 'info');
						
						if($deleted)
							view::redirect('/' . $app->get_config('cms.path') . '/forms');
					}
					else
						view::redirect('/' . $app->get_config('cms.path') . '/forms');
					break;
				case 'duplicate':
					$this->cms->check_permissions(array('form', 'form_copy'), '/' . $app->get_config('cms.path') . '/forms');
					
					// check a form ID was passed for the form to copy
					if(isset($params[2]) && intval($params[2]))
					{
						cms::duplicate_form($params[2]);
						
						\maverick_cms\log::log('forms', 'duplicated', array('form_id'=>$params[2]), 'info');
						
						view::redirect('/' . $app->get_config('cms.path') . '/forms');
					}
					break;
			}
		}
	}
}