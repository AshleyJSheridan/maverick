<?php
class tags_controller extends cms_controller
{	
	function __construct()
	{
		parent::__construct();
	}
	
	function tags($params)
	{
		$this->cms->check_permissions('tags', '/' . $this->app->get_config('cms.path') . '/');
		
		// show the tags
		if(!isset($params[1]))
		{
			$tags = cms::get_tags();
			$tag_groups = '';
			
			$tag_buttons = cms::generate_actions('tags', null, array('save', 'add group', 'add tag'), 'full', 'button');
			
			foreach($tags as $tag_group)
			{
				$tag_html = '';
				
				foreach($tag_group as $tag)
				{
					$tag_html .= \helpers\html\html::load_snippet(MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/tag.php', array(
							'tag'=>$tag['tag'],
						)
					);
				}
				
				$tag_groups .= \helpers\html\html::load_snippet(MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/tag_group.php', array(
						'group_name'=>(strlen($tag['group_name']))?$tag['group_name']:'ungrouped',
						'tag_html'=>$tag_html,
						'group'=>(strlen($tag['group_name']))?'grouped':'ungrouped',
					)
				);
			}
			
			$view_params = array(
				'tag_groups'=>$tag_groups,
				'tag_buttons'=>$tag_buttons,
				'scripts'=>array(
					'/js/cms/tags.js'=>10, 
					'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js'=>5,
				)
			);

			$this->load_view('tags', $view_params );
		}
	}
}