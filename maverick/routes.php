<?php
route::any('one/two/three', 'main_controller->page123');
route::get('one/two', 'main_controller->page12');
route::post('one/two', 'main_controller->page12');
route::any('', 'main_controller->home');

route::error('404', 'main_controller->error');
