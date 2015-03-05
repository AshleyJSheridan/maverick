<?php
if(!defined('MAVERICK_BASEDIR'))
	define('MAVERICK_BASEDIR', dirname(__FILE__) . '/../maverick/');

if(!defined('MAVERICK_VIEWSDIR'))
	define('MAVERICK_VIEWSDIR', dirname(__FILE__) . '/../maverick/views/');

if(!defined('MAVERICK_HTDOCS'))
	define('MAVERICK_HTDOCS', dirname(__FILE__) . '/');

require_once MAVERICK_BASEDIR . 'vendors/maverick/maverick.php';

$app = maverick::getInstance();
$app->build();
