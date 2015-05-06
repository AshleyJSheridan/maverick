<?php
use \maverick\db as db;

class cms
{
	static function check_login($username, $password)
	{
		$data = db::table('maverick_cms_users')
			->where('username', '=', db::raw($username) )
			->where('password', '=', db::raw(md5($username . $password) ) )
			->get()
			->fetch();

		return(isset($data[0])?(int)$data[0]['id']:false );
	}
	
	static function get_forms()
	{
		$data = db::table('maverick_cms_forms AS f')
			->leftJoin('maverick_cms_form_elements AS fe', array('f.id', '=', 'fe.form_id') )
			->groupBy('f.id')
			->get(array('f.id', 'f.name', 'f.lang', 'COUNT(fe.id) AS total_elements') )
			->fetch();

		return $data;
	}
	
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