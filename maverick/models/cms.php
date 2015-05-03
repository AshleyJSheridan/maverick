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
}