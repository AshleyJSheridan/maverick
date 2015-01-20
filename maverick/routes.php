<?php
route::any('one/two/three', 'main_controller->page123');
route::get('form', 'main_controller->form');
route::post('form', 'main_controller->form_post');
route::any('', 'main_controller->home');

route::error('404', 'main_controller->error');
