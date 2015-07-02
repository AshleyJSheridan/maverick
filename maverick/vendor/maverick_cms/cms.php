<?php
namespace maverick_cms;


class cms extends \maverick\maverick
{
	static $_instance;
	
	private function __clone() {}
	
	private function __construct() {}
	
	/**
	 * return the single instance of this class - there can be only one!
	 * @return \maverick_cms\cms
	 */
	public static function getInstance()
	{
		if(!(self::$_instance instanceof self))
			self::$_instance = new self;
		return self::$_instance;
	}
	
	/**
	 * check the permissions listed for the currently logged in user
	 * if the check fails, and a redirect URL is supplied, a redirect is actioned
	 * @param array $perms a list of permissions to check against - a check must pass ALL of these to succeed
	 * @param string|bool $redirect if non-false, this should be the URL to redirect to if there the permission check failed
	 * @return boolean
	 */
	public function check_permissions($perms, $redirect = false)
	{
		$allowed = false;
		$perms = (array)$perms;
		
		$user_id = isset($_SESSION['maverick_id'])?$_SESSION['maverick_id']:0;
		
		$all_permissions = \cms::get_permissions($user_id, $perms);
		
		// if the user is marked as an admin, they can do anything
		if($all_permissions['admin'] == 'yes')
			$allowed = true;
		else
		{
			// otherwise, check each permission that was requested and only return true if all of them match
			if(!empty($all_permissions['perm_names']))
			{
				$allowed = true;
				$all_permissions = explode(',', $all_permissions['perm_names']);

				// only return true if all the permissions match
				foreach($perms as $perm)
				{
					if(!in_array($perm, $all_permissions))
					{
						$allowed = false;
						break;
					}
				}
			}
		}
		
		// redirect if a URL was supplied and the permissions were not correct
		if($redirect && !$allowed)
			\view::redirect($redirect);
		else
			return $allowed;
	}
	
	/**
	 * a static method to build a list of select list options using a template and return that list as an html string
	 * @param array $options a list of values for the select list
	 * @param string $element_name the name of the select list element - used to determine if this should be marked as selected in the rendered html or not (e.g. for a form posted with errors)
	 * @param bool $key_value whether or not this is using a simple array where the option value and text are the same, or associative key=>value pair
	 * @param string a string indicating the directory where override snippets exist for the <option> tag
	 * @return string
	 */
	public static function build_select_options($options, $element_name, $key_value=false, $snippets_dir=null)
	{
		$html = '';
		
		foreach($options as $key => $option)
		{
			$selected = ((isset($_REQUEST[$element_name]) && $_REQUEST[$element_name] == $option) || (strtolower($element_name) == strtolower($key) ) )?'selected="selected"':'';
			
			if($snippets_dir && file_exists("$snippets_dir/input_option.php"))
				$snippet = "$snippets_dir/input_option.php";
			else
				$snippet = __DIR__ . "/snippets/input_option.php";
			
			$html .= \helpers\html\html::load_snippet($snippet, array(
				'value' => ($key_value)?$key:$option,
				'display_value' => $option,
				'selected' => $selected,
			) );
		}

		return $html;
	}
}