<div
	class="form_element"
	data-element_id="{{element_id}}"
	>
	
	<div class="actions">
		<button class="delete action">delete</button>
	</div>
	
	<div class="label {{type}}_field">{{element_name}} ({{type}})</div>
	<div class="element">{{element_html}}</div>
	
	<input type="hidden" name="id[]" value="{{element_id}}"/>
	
	<div class="details tabbed">
		<ul class="tab-nav">
			<li data-tab="main" class="active">Main</li>
			<li data-tab="display">Display</li>
			<li data-tab="validation">Validation</li>
		</ul>
		
		<div class="tab active main">
			<div class="tab-content">
				<label>Type: <select name="type[]">{{elements}}</select></label>
				<label>Name: <input type="text" name="name[]" value="{{element_name}}"/></label>
				<label>Label: <input type="text" name="label[]" value="{{label}}"/></label>
				<label>Default Value: <input type="text" name="value[]" value="{{value}}"/></label>
			</div>
		</div>
		<div class="tab display">
			<div class="tab-content">
				<label>Display Order: <input type="text" name="display_order[]" value="{{display_order}}"/></label>
				<label>Class: <input type="text" name="class[]" value="{{class}}"/></label>
				<label>ID: <input type="text" name="html_id[]" value="{{html_id}}"/></label>
				<label>Placeholder: <input type="text" name="placeholder[]" value="{{placeholder}}"/></label>
				<label>Display?: {{display_checkbox}}</label>
			</div>
		</div>
		<div class="tab validation">
			<div class="tab-content">
				<label>Required?: {{required_checkbox}}</label>
				<label>Regex: <input type="text" name="regex[]" value="{{regex}}"/></label>
				<label>Min: <input type="text" name="min[]" value="{{min}}"/></label>
				<label>Max: <input type="text" name="max[]" value="{{max}}"/></label>
			</div>
		</div>
	</div>
</div>