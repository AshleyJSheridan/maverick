<?php

/**
 * a simple class just used as a hook to retrieve data from the main object view member variable
 */
class data
{
	public static function get($var)
	{
		$app = \maverick\maverick::getInstance();

		return ($app->view->get_data($var));
	}
}