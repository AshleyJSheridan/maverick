<?php
/**
 * a controller in userspace which contains custom methods for dealing with routes
 * this could contain multiple methods for different types of routes
 * @package Userspace
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class route_controller extends base_controller
{
	/**
	 * filter a language culture out of the URL and set the language in the application variable
	 * this also removes the language culture parts from the URL path
	 * @return bool
	 */
	public function lang_filter()
	{
		// any URL pre-parsing logic goes here
		// for example, this method will look for language culture segments in the URL, and return the default language of the app
		$regex = '/^\/?([a-z]{2})\/([a-z]{2})\b/';
		
		if(preg_match($regex, $_SERVER['REQUEST_URI'], $matches) )
		{
			// remove those matched segments from the URL so that regular routing can take place
			$_SERVER['REQUEST_URI'] = '/' . substr($_SERVER['REQUEST_URI'], 6);
			
			// as the app will use this if it exists - we should also strip the same parts out of this if they exist there
			if(isset($_SERVER['REDIRECT_URL']) && preg_match($regex, $_SERVER['REDIRECT_URL']) )
				$_SERVER['REDIRECT_URL'] = '/' . substr($_SERVER['REDIRECT_URL'], 6);
			
			// return the language culture member variable on main app object
			$app = \maverick\maverick::getInstance();
			$app->language_culture = "{$matches[1]}_" . strtoupper($matches[2]);
		}
	}
}
