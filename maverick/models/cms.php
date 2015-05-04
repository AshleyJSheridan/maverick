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

		return(isset($data[0]) );
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
}