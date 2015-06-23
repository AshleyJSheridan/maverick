<?php
$form = data::get('form');
?>

<h2>Editing Form - "<?= $form[0]['form_name']; ?>"</h2>

<div class="form_elements edit">
	<?php
	foreach($form as $element)
		echo $element['html'];
	?>
</div>