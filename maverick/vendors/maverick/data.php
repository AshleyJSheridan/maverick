<?php
class data
{
	public static function get($var)
	{
		$app = maverick::getInstance();
		
		return ($app->view->get_data($var));
	}
}