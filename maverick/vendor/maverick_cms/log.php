<?php
namespace maverick_cms;
use \maverick\db as db;	// although this is a helper, it's making direct calls to the DB rather than going to a model class, because it's only doing simple logging

/**
 * a basic logging class to log events that happen within the CMS
 */
class log
{
	/**
	 * log a regular event in the database
	 * @param string $category the name of the category to which this event belongs - used for filtering
	 * @param string $sub_category the sub-category for the event - used for filtering
	 * @param string $details more details for this event entry
	 * @param string $type either 'info' or 'error' - used to classify this event entry further
	 */
	function log($category, $sub_category, $details='', $type='info')
	{
		
	}
	
	/**
	 * records a login attempt and marks whether or not it was successful
	 * @param string $username the username that attempted to log in
	 * @param bool $successful whether or not the attempt was successful
	 * @return bool
	 */
	static function record_login($username, $successful=false)
	{
		
		$login = db::table('maverick_cms_logins')
			->insert(array(
				'username' => $username,
				'ip' => ( (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']),
				'user_agent' => $_SERVER['HTTP_USER_AGENT'],
				'login_at' => date("Y-m-d H:i:s"),
				'successful' => (string)$successful,
			));
		return $login;
	}
}