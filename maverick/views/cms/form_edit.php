<h2>Editing Form - "{{form.0.form_name}}"</h2>

{{errors}}

{{form_buttons}}

<form class="form_elements edit" method="post" enctype="multipart/form-data">	
	<div class="form_details">{{form_details}}</div>
	<?php
	$form = data::get('form');
	
	if(isset($form[0]['html']))
	{
		foreach($form as $element)
			echo $element['html'];
	}
	?>
</form>

{{form_buttons}}