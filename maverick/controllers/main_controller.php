<?php
class main_controller extends base_controller
{
	function __construct() {}
	
	function main($params)
	{
		$page = content::get_page($params);

		if(!$page || !file_exists(MAVERICK_VIEWSDIR . $page['template_path'] . '.php'))
		{
			// do 404 stuff here
		}
		else
		{
			$view = view::make($page['template_path'])
				->with('server', $_SERVER)
				->with('get', $_GET)
				->with('post', $_POST)
				->with('cookie', $_COOKIE)
				->with('request', $_REQUEST)
				->render();
		}
	}

	function error()
	{
		echo 'error';
	}

}