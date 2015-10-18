<h2>Editing Page - "{{page_details.0.page_name}}"</h2>

{{errors}}

<form class="page_elements edit" method="post" enctype="multipart/form-data" name="edit_page" novalidate>
	{{page_buttons}}
	
	<div class="page_details">{{page_info}}</div>
	
	<div class="elements">
		<?php
		$page = data::get('page_details');

		//
		
		if(isset($page[0]['html']))
		{
			foreach($page as $element)
				echo $element['html'];
		}
		
		var_dump($page);
		?>
	</div>
	
	{{form_buttons}}
</form>