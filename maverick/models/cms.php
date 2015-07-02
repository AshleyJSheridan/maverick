<?php
use \maverick\db as db;

/**
 * the main model for the CMS
 */
class cms
{
	/**
	 * determines if a username and password is valid - this does not presume any particular type of user, just a valid user
	 * @param string $username the username of the user to determine
	 * @param string $password the password for the user
	 * @return int|bool false if the user is not valid, or the id of the user otherwise
	 */
	static function check_login($username, $password)
	{
		$data = db::table('maverick_cms_users')
			->where('username', '=', db::raw($username) )
			->where('password', '=', db::raw(md5($username . $password) ) )
			->get()
			->fetch();

		return(isset($data[0])?(int)$data[0]['id']:false );
	}
	
	/**
	 * gets a list of all the forms in the CMS as defined in the database
	 * @return array
	 */
	static function get_forms()
	{
		$data = db::table('maverick_cms_forms AS f')
			->leftJoin('maverick_cms_form_elements AS fe', array('f.id', '=', 'fe.form_id') )
			->groupBy('f.id')
			->get(array('f.id', 'f.name', 'f.lang', 'COUNT(fe.id) AS total_elements') )
			->fetch();

		return $data;
	}
	
	/**
	 * get a list of the languages listed in the database
	 * this list is a full list of ISO-639x language cultures
	 * @param bool $all determines if only languages currently being used within the CMS are to be returned
	 * @param bool $short if set to true, this returns a simple culture=>fullname array, otherwise returns a full array from the database
	 * @return array
	 */
	static function get_languages($all=false, $short=false)
	{
		$languages = db::table('maverick_cms_languages');
		
		if(!$all)
			$languages->where('in_use', '=', db::raw('yes') );
		
		if($short)
			$languages->get(array('culture_name', 'display_name') );
		else
			$languages->get();
		
		$languages = $languages->fetch();
		
		$lang_list = array();
		
		foreach($languages as $lang)
		{
			if($short)
				$lang_list[$lang['culture_name']] = $lang['display_name'];
			else
				$lang_list[$lang['culture_name']] = $lang;
		}
		
		return $lang_list;
	}
	
	/**
	 * get a fom and all of its form elements with their respective element details
	 * if no elements are attached to a form, then the returned array contains a single element with mostly missing details
	 * if no form is found, a completely empty array is returned
	 * @param int $form_id the ID of the form to retrieve
	 * @return array
	 */
	static function get_form($form_id)
	{
		$form_id = intval($form_id);

		$form = db::table('maverick_cms_forms AS f')
			->leftJoin('maverick_cms_form_elements AS fe', array('fe.form_id', '=', 'f.id') )
			->where('f.id', '=', db::raw($form_id))
			->where('f.deleted', '=', db::raw('no'))
			->orderBy('fe.display_order')
			->get(array(
				'f.name AS form_name',
				'f.active AS form_active',
				'f.lang',
				'f.deleted AS form_deleted',
				'fe.id AS element_id',
				'fe.element_name',
				'fe.type',
				'fe.display',
				'fe.label',
				'fe.placeholder',
				'fe.value',
				'fe.display_order',
				'fe.class',
				'fe.html_id',
			))
			->fetch();
		
		$elements = array();
		foreach($form as $element)
			$elements[] = $element['element_id'];
		
		// get the extra bits for any fields, such as select values and extra validation parameters
		$extra = db::table('maverick_cms_form_elements_extra AS fee')
			->whereIn('fee.element_id', $elements)
			->get()
			->fetch();
		
		foreach($form as &$element)
		{
			// this ensures that we're not looping through anything uneccessarily
			if(!count($extra))
				break;
			else
			{
				for($i=0; $i<count($extra); $i++)
				{
					// if the id matches then this extra bit belongs to the element
					if($extra[$i]['element_id'] == $element['element_id'])
					{
						$type = $extra[$i]['special_type'];
						if(!isset($element[$type]))
							$element[$type] = array();
						
						$element[$type][] = $extra[$i]['value'];
						
						// this bit removes the elements from the $extra array so that we're not looping through them later
						// it will pay off dramatically for forms that contain large select lists!
						array_splice($extra, $i, 1);
						$i--;
					}
				}
			}
			
			// create the CMS HTML for each element
			$element['html'] = cms::get_form_element($element);
		}

		return $form;
	}
	
	/**
	 * build a form element snippet for use in the CMS
	 * because the element is not passed by reference, the 'element_html' array element is scoped to this method only
	 * @param array $element the element details which are passed to \helpers\html\html::load_snippet
	 * @param bool $render whether or not to render the HTML for this
	 * @return string
	 */
	static function get_form_element($element, $render=false)
	{
		$element['element_html'] = \helpers\html\html::load_snippet(MAVERICK_VIEWSDIR . "cms/includes/snippets/input_{$element['type']}.php", $element);
		$element['elements'] = implode(\helpers\html\cms::get_available_elements('form', array('default'=>$element['type']) ) );
		$element['required_checkbox'] = \helpers\html\html::load_snippet(MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/input_checkbox.php',
			array(
				'name'=>'required',
				'value'=>'required',
				'checked'=>(isset($element['required'][0]) && $element['required'][0] == 'true')?'checked="checked"':''
			)
		);
		$element['display_checkbox'] = \helpers\html\html::load_snippet(MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/input_checkbox.php', 
			array(
				'name'=>'display',
				'value'=>'required',
				'checked'=>($element['display'] == 'yes')?'checked="checked"':''
			)
		);
		if(isset($element['between'][0]) && !empty($element['between'][0]))
			list($element['min'], $element['max']) = explode(':', $element['between'][0]);

		if($render)
		{
			$view = view::make("cms/includes/snippets/form_element")
				->with('type', $element['type'])
				->with('element_name', $element['element_name'])
				->with('elements', $element['elements'])
				->with('element_html', $element['element_html'])
				->with('display_order', $element['display_order'])
				->headers(array('content-type'=>'text/plain') )
				->render(true, true);
		}
		else
			return \helpers\html\html::load_snippet(MAVERICK_VIEWSDIR . 'cms/includes/snippets/form_element.php', $element);
	}
	
	/**
	 * deals with the creation of action buttons (links) used throughout the CMS to do something
	 * @param string $section the section, as all links will contain this in their URL
	 * @param int $id the ID of the object being worked on
	 * @param array $actions a basic single dimensional array of single-word actions, that go into the URL and the text of the link
	 * @param string $extra_classes a string of extra classes that should be added to each button
	 * @param string $type the type of element to use, either a button or a link
	 * @return string
	 */
	static function generate_actions($section, $id, $actions = array(), $extra_classes='', $type='link')
	{
		if(empty($actions) || !intval($id) || empty($section) )
			return '';
		
		$app = \maverick\maverick::getInstance();
		
		$type = in_array($type, array('link', 'button') )?$type:'link';
		
		$actions_html = '';
		foreach($actions as $action)
		{
			$replacements = array(
				'href' => str_replace(' ', '_', "/{$app->get_config('cms.path')}/$section/$action/$id"),
				'action' => $action,
				'id' => $id,
				'section' => $section,
				'class' => str_replace(' ', '_', $action) . " $extra_classes",
			);
			$actions_html .= \helpers\html\html::load_snippet(MAVERICK_VIEWSDIR . "cms/includes/snippets/action_$type.php", $replacements );
		}
		return $actions_html;
	}
	
	/**
	 * builds a form element preview for the admin area directly from AJAX data
	 */
	static function get_form_element_preview()
	{
		$available_elements = \helpers\html\cms::get_available_elements('form', array(), false);
		
		$element_type = (empty($_REQUEST['element_type']) || !in_array($_REQUEST['element_type'], $available_elements))?die():$_REQUEST['element_type'];
		$element_value = (!empty($_REQUEST['element_value']))?filter_var($_REQUEST['element_value'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
		$placeholder = (!empty($_REQUEST['placeholder']))?filter_var($_REQUEST['placeholder'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
		
		$view = view::make("cms/includes/snippets/input_$element_type")
			->with('type', $element_type)
			->with('value', $element_value)
			->with('placeholder', $placeholder)
			->headers(array('content-type'=>'text/plain') )
			->render(true, true);
	}
	
	/**
	 * process the form data and save the elements
	 */
	static function save_form()
	{
		
		var_dump($_REQUEST);
	}

	/**
	 * gets the permissions for the specified user id and an identifier of whether or not this user is an admin
	 * @param int $user_id the id of the user to get permissions for
	 * @return array
	 */
	static function get_permissions($user_id)
	{
		$perms = db::table('maverick_cms_users AS u')
			->leftJoin('maverick_cms_user_permissions AS up', array('up.user_id', '=', 'u.id') )
			->leftJoin('maverick_cms_permissions AS p', array(
					array('up.permission_id', '=', 'p.id'),
				))
			->where('u.id', '=', db::raw($user_id))
			->get(array('u.admin', 'GROUP_CONCAT(p.id) AS perm_ids', 'GROUP_CONCAT(p.name) AS perm_names') )
			->groupBy('u.id')
			->fetch()
			;
		
		return (isset($perms[0]))?$perms[0]:false;
	}
}