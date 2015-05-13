<?php
namespace maverick_cms;
use \maverick\db as db;	// although this is a helper, it's making direct calls to the DB rather than going to a model class, because it's only doing simple logging

class log
{
	function log($category, $sub_category, $details='', $type='info')
	{
		
	}
	
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