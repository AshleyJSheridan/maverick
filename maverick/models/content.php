<?php
use \maverick\db as db;

/**
 * the main model used in the app
 * @package Userspace
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class content
{
	static function get_page($uri)
	{
		$uri = '/' . $uri;
		
		$page = db::table('maverick_cms_pages')
			->where(db::raw($uri), 'REGEXP', 'page_route' )
			->limit(1)
			->get()
			->fetch();
		
		return (!empty($page))?$page[0]:false;
	}
}
