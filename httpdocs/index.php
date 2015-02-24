<?php
if(!defined('MAVERICK_BASEDIR'))
	define('MAVERICK_BASEDIR', dirname(__FILE__) . '/../maverick/');

if(!defined('MAVERICK_VIEWSDIR'))
	define('MAVERICK_VIEWSDIR', dirname(__FILE__) . '/../maverick/views/');


require_once MAVERICK_BASEDIR . 'maverick.php';

$app = maverick::getInstance();
$app->build();
