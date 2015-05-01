<?php
return array(

	'debug' => true,
	'log_errors' => false,	// if set to true, then the directory /logs needs to be writeable by the web server
	'error_detail' => true,	// whether or not the log includes a stack trace - this will give much more detailed information on an error, but will bloat the log files if there are unresolved errors in your application
	
	'xss_protection' => true,
	
	'app_name' => 'maverick',
	
	'paths' => array(
		MAVERICK_BASEDIR . 'controllers',
		MAVERICK_BASEDIR . 'models',
	),
	
	'route_preparser' => 'route_controller->maverick_cms',
	
	'view_parsing' => true,
	
	'teapot' => true,
);
