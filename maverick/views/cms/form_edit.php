<?php
$form = data::get('form');
?>

<h2>Editing Form - "<?= $form[0]['form_name']; ?>"</h2>

<div class="form_elements edit">
	<?php
	if(isset($form[0]['html']))
	{
		foreach($form as $element)
		{
			var_dump($element);
			echo $element['html'];
		}
	}
	?>
</div>