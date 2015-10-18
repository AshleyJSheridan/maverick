<div
	class="page_element"
	data-element_id="{{content_id}}"
	>
	
	<div class="actions">
		<button class="delete action">delete</button>
	</div>
	
	<div class="label {{content_type}}_field">{{content_name}} (<span>{{content_type}}</span>)</div>

	<input type="hidden" name="id[]" value="{{content_id}}"/>
	<input type="hidden" name="content_order[]" value="{{content_order}}"/>
	
	<label>Type: <select name="type[]">{{content_types}}</select></label>
	<label>Name: <input type="text" name="name[]" value="{{content_name}}"/></label>
	
	<label>
		Content:
		<textarea name="content[]">{{content}}</textarea>
	</label>
</div>