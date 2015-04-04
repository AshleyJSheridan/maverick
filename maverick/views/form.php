<?php
echo data::get('form')->render();

var_dump(validator::get_all_errors(null, array('<span class="error">', '</span>')));
var_dump(validator::get_all_errors('name', array('<span class="error">', '</span>')));