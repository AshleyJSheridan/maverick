<?php
/**
 * the controller for dealing with pages in the cms
 * @package MaverickCMS
 * @author Ashley Sheridan <ash@ashleysheridan.co.uk>
 */
class pages_controller extends cms_controller
{
	private $page = 'pages';
	
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
		$this->cms->check_permissions('page', '/' . $this->app->get_config('cms.path') . '/');
		
		// show the list of pages as this is the main forms page requested
		if(!isset($params[1]))
		{
			// get list of pages and show them
			$pages = cms::get_pages();
			$headers = '["Name","Language","Status","Last Edit","Actions"]';
			$data = array();
			foreach($pages as $page)
				$data[] = array($page['page_name'], $page['language_culture'], $page['status'], date("l, jS M, Y", strtotime($page['last_edit'])), cms::generate_actions('pages', $page['id'], array('edit', 'delete', 'duplicate') ) );
			
			$page_table = new \helpers\html\tables('pages', 'layout', $data, $headers);
			$page_table->class = 'item_table';
			
			$view_params = array(
				'pages'=>$page_table->render(),
				'page_buttons'=> cms::generate_actions('pages', '', array('new page'), 'full', 'a'),
				'scripts'=>array(
					'/js/cms/pages.js'=>10, 
				)
			);
			$this->load_view($this->page, $view_params );
		}
		else
		{
			if(method_exists($this, $params[1]))
				$this->{$params[1]}($params);
		}//end if
	}
	
	/**
	 * handles editing of a form and its fields
	 * @param array $params the URL parameters
	 * @return bool
	 */
	private function edit($params)
	{
		// check permissions and redirect to the forms list if the current user doesn't have the right permissions
		$this->cms->check_permissions(array('pages', 'page_edit'), '/' . $this->app->get_config('cms.path') . '/pages');
		
		$errors = false;
		
		// redirect to create a new page section if no page ID is in the URL, or a page does not actually exist with that ID
		if(isset($params[2]) && intval($params[2]))
		{
			$page_buttons = cms::generate_actions($params[1], $params[2], array('save', 'add element'), 'full', 'button');
			
			// process the posted data and save the form if the required fields are present
			if(count($_REQUEST))
			{
				cms::update_page();
			}
			
			// get the page from the specified ID, returning the user to the main pages list if no page could be found with that ID
			$page = cms::get_page($params[2]);
			
			if(empty($page))
				view::redirect('/' . $this->app->get_config('cms.path') . '/page/new_page');
			
			// build up the extra fields for the page-specific details, like page name, etc
			$page_info = \helpers\html\html::load_snippet(
				MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/label_wrap.php',
				array(
					'label'=>'Page Name',
					'element'=>\helpers\html\html::load_snippet(
						MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/input_text.php',
						array(
							'value'=>"value=\"{$page[0]['page_name']}\"",
							'placeholder'=>"placeholder=\"page name\"",
							'name'=>'page_name'
						)
					)
				)
			);
			$page_info .= \helpers\html\html::load_snippet(
				MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/label_wrap.php',
				array(
					'label'=>'Language Culture',
					'element'=>\helpers\html\html::load_snippet(
						MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/input_select.php',
						array(
							'values'=> $this->cms->build_select_options(
								cms::get_languages(false, true),
								$page[0]['language_culture'],
								true,
								MAVERICK_BASEDIR . 'vendor/helpers/html/snippets'
							),
							'name'=>'language_culture'
						)
					)
				)
			);
			$page_info .= \helpers\html\html::load_snippet(
				MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/label_wrap.php',
				array(
					'label'=>'Page Status',
					'element'=>\helpers\html\html::load_snippet(
						MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/input_select.php',
						array(
							'values'=> $this->cms->build_select_options(
								array('live'=>'Live', 'draft'=>'Draft'),
								$page[0]['status'],
								true,
								MAVERICK_BASEDIR . 'vendor/helpers/html/snippets'
							),
							'name'=>'status'
						)
					)
				)
			);
			$page_info .= \helpers\html\html::load_snippet(
				MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/label_wrap.php',
				array(
					'label'=>'Page Route',
					'element'=>\helpers\html\html::load_snippet(
						MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/input_text.php',
						array(
							'value'=>"value=\"{$page[0]['page_route']}\"",
							'placeholder'=>"placeholder=\"page route\"",
							'name'=>'page_route'
						)
					)
				)
			);
			$page_info .= \helpers\html\html::load_snippet(
				MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/label_wrap.php',
				array(
					'label'=>'Template Path',
					'element'=>\helpers\html\html::load_snippet(
						MAVERICK_BASEDIR . 'vendor/helpers/html/snippets/input_text.php',
						array(
							'value'=>"value=\"{$page[0]['template_path']}\"",
							'placeholder'=>"placeholder=\"tempate file\"",
							'name'=>'template_path'
						)
					)
				)
			);
							
			//var_dump($page);
			
			$view_params = array(
				'page_details'=>$page,
				'page_buttons'=>$page_buttons,
				'page_info'=>$page_info,
				'scripts'=>array(
					'/js/cms/pages.js'=>10, 
					'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js'=>5,
				)
			);
			if($errors)
				$view_params['errors'] = $errors;

			$this->load_view('page_edit', $view_params );
			
		}//end if
		else
			view::redirect('/' . $this->app->get_config('cms.path') . '/pages/new_page');
	}
}
