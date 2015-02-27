<?php
//route::preprocess();

route::any('one/two/three', 'main_controller->page123');
route::get('form', 'main_controller->form');
route::post('form', 'main_controller->form_post');
route::any('', 'main_controller->home');

route::any('^te[st]{2}', 'main_controller->regex_route_test_controller');

route::error('404', 'error');
route::error('500', 'error');
