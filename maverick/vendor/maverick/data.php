<?php

/**
 * a simple class just used as a hook to retrieve data from the main object view member variable
 * @package Maverick
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class data
{
	/**
	 * grabs a bit of information from the data array in the main view object being used
	 * @param string $var the name of the data to get
	 * @return mixed
	 */
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
		}//end if
		else
			$var = $app->view->get_data($var);
		
		return $var;
	}
}
