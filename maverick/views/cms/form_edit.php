<h2>Editing Form - "{{form.0.form_name}}"</h2>

{{errors}}

<form class="form_elements edit" method="post" enctype="multipart/form-data" name="edit_form" novalidate>
	{{form_buttons}}
	
	<div class="form_details">{{form_details}}</div>
	
	<div class="elements">
		<?php
		$form = data::get('form');

		if(isset($form[0]['html']))
		{
			foreach($form as $element)
				echo $element['html'];
		}
		?>
	</div>
	
	{{form_buttons}}
</form>
