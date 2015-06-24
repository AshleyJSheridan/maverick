<div
	class="form_element"
	data-element_id="{{element_id}}"
	>
	<div class="label {{type}}_field">{{label}} ({{type}})</div>
	<div class="element">{{element_html}}</div>
	
	<input type="hidden" name="id" value="{{element_id}}"/>
	
	<div class="details tabbed">
		<div class="tab" data-tab="main">
			<label class="tab-title">Main <input type="radio" checked/></label>
			
			<div class="tab-content">
				<label>Type: <select name="type[]">{{elements}}</select></label>
				<label>Name: <input type="text" name="name[]" value="{{element_name}}"/></label>
				<label>Label: <input type="text" name="label[]" value="{{label}}"/></label>
				<label>Default Value: <input type="text" name="value[]" value="{{value}}"/></label>
			</div>
		</div>
		<div class="tab" data-tab="display">
			<label class="tab-title">Display <input type="radio"/></label>
			
			<div class="tab-content">
				<label>Display Order: <input type="text" name="display_order[]" value="{{display_order}}"/></label>
				<label>Class: <input type="text" name="class[]" value="{{class}}"/></label>
				<label>ID: <input type="text" name="id[]" value="{{html_id}}"/></label>
				<label>Placeholder: <input type="text" name="placeholder[]" value="{{placeholder}}"/></label>
				<label>Display?: {{display_checkbox}}</label>
			</div>
		</div>
		<div class="tab" data-tab="validation">
			<label class="tab-title">Validation <input type="radio"/></label>
			
			<div class="tab-content">
				<label>Required?: {{required_checkbox}}</label>
				<label>Regex: <input type="text" name="regex[]" value="{{regex}}"/></label>
				<label>Min: <input type="text" name="min[]" value="{{min}}"/></label>
				<label>Max: <input type="text" name="max[]" value="{{max}}"/></label>
			</div>
		</div>
	</div>
</div>