<?php
$form = data::get('form');
?>

<h2>Editing Form - "<?= $form[0]['form_name']; ?>"</h2>

{{save_button}}

<form class="form_elements edit" method="post" enctype="multipart/form-data">
	<?php
	if(isset($form[0]['html']))
	{
		foreach($form as $element)
		{
			//var_dump($element);
			echo $element['html'];
		}
	}
	?>
</form>

{{save_button}}