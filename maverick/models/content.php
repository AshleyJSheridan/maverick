<?php
use \maverick\db as db;

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