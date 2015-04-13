<?php

/**
 * a simple class just used as a hook to retrieve data from the main object view member variable
 */
class data
{
	public static function get($var)
	{
		$app = \maverick\maverick::getInstance();

		if(strpos($var, '.') !== false)
		{
			$matches = explode('.', $var);
			
			if(count($matches) > 1)
			{
				$v = $app->view->get_data($matches[0]);
				
				array_shift($matches);
				
				foreach($matches as $item)
				{
					if(isset($v[$item]))
					{
						$var = $v[$item];
						$v = $v[$item];
					}
					else
						break;
				}
			}
		}
		else
			$var = $app->view->get_data($var);
		
		return $var;
	}
}