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
	
	
}