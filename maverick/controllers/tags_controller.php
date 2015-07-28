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
			if(count($_REQUEST) )
			{
				// loop through the tags, and keep track of the tag counts, assigning them to groups
				$tags = array();
				$tag_group = 0;
				$tag_group_count = 1;

				for($i=0; $i<count($_REQUEST['tag']); $i++)
				{
					$tag_group_name = $_REQUEST['tag_group'][$tag_group];

					if($tag_group_count == $_REQUEST['tag_count'][$tag_group])
					{
						$tag_group++;
						$tag_group_count = 0;
					}

					if(!isset($tags[$tag_group_name]))
						$tags[$tag_group_name] = array();
					
					$tags[$tag_group_name][] = $_REQUEST['tag'][$i];
					
					$tag_group_count ++;
				}
				
				cms::update_tags($tags);
			}
			
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
						'group_class'=>(str_replace(' ', '_', strlen($tag['group_name']) ) )?'grouped':'ungrouped',
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