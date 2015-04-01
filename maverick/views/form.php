<form name="form" method="post">
	<label>Name <input type="text" name="name"/><?php echo validator::get_first_error('name', array('<span class="error">', '</span>')); ?></label>
	<label>Age <input type="text" name="age"/></label>
	<label>Email <input type="text" name="email"/><?php echo validator::get_first_error('email', array('<span class="error">', '</span>')); ?></label>
	<label>Postcode <input type="text" name="postcode"/></label>
	<label>Web Address <input type="text" name="web_address"/></label>
	<label>Phone <input type="text" name="phone"/></label>
	
	<input type="submit" value="submit" name="submit"/>
</form>

<?php
echo data::get('form')->render();

var_dump(validator::get_all_errors(null, array('<span class="error">', '</span>')));
var_dump(validator::get_all_errors('name', array('<span class="error">', '</span>')));