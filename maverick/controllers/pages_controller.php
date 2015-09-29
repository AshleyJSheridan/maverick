<?php
/**
 * the controller for dealing with pages in the cms
 * @package MaverickCMS
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class pages_controller extends cms_controller
{
	/**
	 * magic constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * do stuff with pages
	 * @todo implement this
	 * @param array $params the url params
	 * @return bool
	 */
	public function pages($params)
	{
		var_dump($params);
	}
	
}
