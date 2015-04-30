<?php
use maverick\route;

route::any('', 'main_controller->home');

route::error('404', 'error');
route::error('500', 'error');
