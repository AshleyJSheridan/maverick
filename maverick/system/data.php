<?php
class data
{
	public static function get($var)
	{
		$app = maverick::getInstance();
		
		return (string)($app->view->get_data($var));
	}
}