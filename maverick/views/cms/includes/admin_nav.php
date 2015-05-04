<?php
$app = \maverick\maverick::getInstance();

$nav = array(
	'/' => 'Home',
	'/forms' => 'Forms',
	'/media' => 'Media',
	'/pages' => 'Pages',
	'/users' => 'Users',
);
$params = data::get('params');
$request = isset($params[0])?$params[0]:'';

foreach($nav as $uri => $name)
{
	$active = trim($uri, '/') == $request?'active':'';
	$class = strtolower(str_replace('_', '', $name));
	$uri = '/' . $app->get_config('cms.path') . $uri;

	echo <<<NAV
	<a href="$uri" class="$class $active">$name</a>
NAV;
}
