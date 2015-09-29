<?php
/**
 * the controller responsible for dealing with the language cultures on the site
 * @todo implement
 * @package MaverickCMS
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class cultures_controller extends cms_controller
{
	/**
	 * the magic constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * get a list of all the language cultures on the site
	 * @param array $params the url parameters
	 * @return bool
	 */
	public function cultures($params)
	{
		$cultures = cms::get_all_cultures();
		
		var_dump($cultures);
	}
}
