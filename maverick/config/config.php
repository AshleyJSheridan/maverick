<?php
return array(

	'debug' => false,
	
	'log_errors' => true,	// if set to true, then the directory /logs needs to be writeable by the web server
	
	'xss_protection' => true,
	
	'app_name' => 'MaVeriCk',
	
	'paths' => array(
		MAVERICK_BASEDIR . 'controllers',
		MAVERICK_BASEDIR . 'models',
		MAVERICK_BASEDIR . 'system',
	),
	
	
);
