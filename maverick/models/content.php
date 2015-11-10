<?php
use \maverick\db as db;

/**
 * the main model used in the app
 * @package MaverickCMS
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class content
{
	/**
	 * get a page from the db
	 * @param string $uri the url of the page to fetch (after any preparsers have modified the url)
	 * @return array|bool
	 */
	public static function get_page($uri)
	{
		$uri = '/' . $uri;
		
		$content = db::table('maverick_cms_pages AS p')
			->leftJoin(
				'maverick_cms_page_content AS pc',
				array(
					array('pc.page_id', '=', 'p.id'),
					array('pc.display', '=', db::raw('yes') ),
				)
			)
			->leftJoin('maverick_cms_users AS u', array('u.id', '=', 'p.author_id') )
			->where(db::raw($uri), 'REGEXP', 'page_route' )
			->where('p.status', '=', db::raw('live') )
			->orderBy('pc.content_name')
			->orderBy('pc.content_order')
			->get(
				array(
					'p.id AS page_id',
					'p.page_name',
					'page_route',
					'p.status AS page_status',
					'p.template_path',
					'p.added_at AS page_added_at',
					'last_edit AS page_last_edit',
					'u.username AS page_author',
					'p.language_culture',
					'pc.id AS content_id',
					'pc.content_name',
					'pc.content_order',
					'pc.content',
				)
			)
			->fetch();

		$page = array();
		foreach($content as $c => $elements)
		{
			foreach($elements as $e => $value)
			{
				if(in_array($e, array('page_id', 'page_name', 'page_route', 'page_status', 'template_path', 'page_added_at', 'page_last_edit', 'page_author', 'language_culture') ) && !isset($page[$e]) )
					$page[$e] = $value;
			}
			
			$content_key = $elements['content_name'];
			
			if(!isset($page[$content_key] ) )
				$page[$content_key] = $elements['content'];
			else
			{
				// this appears to be a group of content, so cast the previously added element to an array and add the rest in
				if(!is_array($page[$content_key]) )
					$page[$content_key] = (array)$page[$elements['content_name']];
				$page[$content_key][] = $elements['content'];
			}
		}

		return (!empty($page))?$page:false;
	}
}
