<?php
use maverick\route;

$app = \maverick\maverick::getInstance();

route::any("{$app->get_config('cms.path')}(/[^/]+)?(/.+)?", 'cms_controller->main', array('$1', '$2') );
route::any('', 'main_controller->main');

route::error('404', 'error');
route::error('500', 'error');

