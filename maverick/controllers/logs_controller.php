<?php
class logs_controller extends cms_controller
{	
	private $page = 1;
	private $per_page = 10;
	private $since;
	private $until;
	private $log_type;
	private $category;
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * method that deals with all logs created in the CMS
	 * @param array $params the URL parameters
	 */
	function logs($params)
	{
		$page = 'logs';

		$this->cms->check_permissions('logs', '/' . $this->app->get_config('cms.path') . '/');

		// get and use any filter params that exist
		foreach(array('page', 'per_page', 'until', 'since', 'log_type', 'category') as $option)
		{
			if(!array_key_exists($option, $_GET))
				continue;
				
			switch($option)
			{
				case 'page':
				case 'per_page':
					if(intval($_GET[$option]) )
						$this->{$option} = $_GET[$option];
					break;
				case 'since':
					if(preg_match('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $_GET[$option]) )
						$this->{$option} = $_GET[$option];
					break;
				case 'log_type':
					if(in_array($_GET[$option], array('info', 'error') ) )
						$this->{$option} = $_GET[$option];
				default:
					if($_GET[$option] != 'all')
						$this->{$option} = $_GET[$option];
			}
		}
		
		// fetch log count and calulate the number of pages this will spread across
		$log_count = cms::get_logs(1, $this->per_page, true);
		$total_pages = ceil($log_count / $this->per_page);
		
		// create the filter form
		$page_values = implode(',', range(1, $total_pages));
		$category_values = implode(',', cms::get_log_categories() );
		$now = date("Y-m-d H:i:s");
		$elements = '{
			"page":{"type":"select","values":['.$page_values.'],"label":"Page"},
			"per_page":{"type":"select","values":[10,20,50,100],"label":"Results Per Page"},
			"log_type":{"type":"select","values":["all","info","error"],"label":"Log Type"},
			"category":{"type":"select","values":["all",'.$category_values.'],"label":"Category"},
			"since":{"type":"date","value":"1970-1-1 00:00:01","label":"Since"},
			"until":{"type":"date","value":"'.$now.'","label":"Until"},
			"filter":{"type":"submit","value":"Filter","class":"action full filter"}
		}';
		$filter_form = new \helpers\html\form('name', $elements);
		$filter_form->method = 'get';
		$filter_form->enctype = 'text/plain';
		$filter_form->class = 'filter_form';
		
		// get list of users and show them
		$logs = cms::get_logs($this->page, $this->per_page, false, $this->since, $this->until, $this->log_type, $this->category);

		$headers = '["ID","User","Category","Sub-Category","When","Type","Actions"]';
		$data = array();

		foreach($logs as $log)
		{
			$data[] = array(
				$log['id'],
				$log['username'],
				$log['category'],
				$log['sub_category'],
				$log['added_at'],
				$log['type'],
				cms::generate_actions('users', $log['id'], array('view details') )
			);
		}

		$logs_table = new \helpers\html\tables('logs', 'layout', $data, $headers);
		$logs_table->class = 'item_table';

		$view_params = array(
			'logs'=>$logs_table->render(),
			'filter_form'=>$filter_form->render(),
			//'user_buttons'=> cms::generate_actions('users', '', array('new user','update permissions', 'list permissions'), 'full', 'a'),
			/*'scripts'=>array(
				'/js/cms/users.js'=>10, 
			)*/
		);
		$this->load_view($page, $view_params );
	}
}