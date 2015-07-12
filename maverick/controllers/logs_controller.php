<?php
class logs_controller extends cms_controller
{	
	private $page = 1;
	private $per_page = 10;
	
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
		$app = \maverick\maverick::getInstance();
		
		$this->cms->check_permissions('logs', '/' . $app->get_config('cms.path') . '/');
		
		// get and use any filter params that exist
		$filter_params = $this->get_filter_params($params);
		foreach(array('page', 'per_page') as $option)
		{
			if(!array_key_exists($option, $filter_params))
				continue;
				
			switch($option)
			{
				case 'page':
				case 'per_page':
					if(intval($filter_params[$option]) )
						$this->{$option} = $filter_params[$option];
					break;
			}
		}
		
				
		// get list of users and show them
		$logs = cms::get_logs($this->page, $this->per_page);
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
			//'user_buttons'=> cms::generate_actions('users', '', array('new user','update permissions', 'list permissions'), 'full', 'a'),
			/*'scripts'=>array(
				'/js/cms/users.js'=>10, 
			)*/
		);
		$this->load_view($page, $view_params );
	}
}