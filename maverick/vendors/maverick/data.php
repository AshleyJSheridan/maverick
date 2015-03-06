<?php
//namespace maverick;

class data
{
	public static function get($var)
	{
		$app = \maverick\maverick::getInstance();

		return ($app->view->get_data($var));
	}
}