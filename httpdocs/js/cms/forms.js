/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

;(function(MAV, $){
	$(function() {
		MAV.Elements.init();
	});
	
	MAV.Elements = {
		init : function(){

			// edit form screen bits
			if($('.form_elements').length)
			{
				// toggle the element details section
				$('.form_elements').on('click', '.form_element .label', function(e){
					$('.details', $(this).parent() ).toggleClass('active');
				});

				// tab navigation
				$('.form_elements').on('click', '.form_element .tab-nav li', function(e){
					$('li, .tab', $(this).parent().parent() ).removeClass('active');

					$(this).addClass('active');
					$('.' + $(this).data('tab'), $(this).parent().parent() ).addClass('active');
				});

				// fetching the preview of an element
				$('.form_elements').on('change', '.form_element .details select[name=type\\[\\]]', function(e){
					element_type = this.value;
					element_value = $('input[name=value\\[\\]]', $(this).closest('.details') ).val();
					placeholder = $('input[name=placeholder\\[\\]]', $(this).closest('.details') ).val();
					var obj = this;

					$.ajax({
						url: '/maverick_admin/ajax/get_form_element',
						data: {'element_type': element_type, 'element_value': element_value, 'placeholder': placeholder},
						method: 'POST'
					}).success(function(data){
						$('.element', $(obj).closest('.form_element') ).html(data);

						// update the type in the brackets that makes the visual label in the CMS and the x_field class in the .label itself
						$('.label', $(obj).closest('.form_element') ).attr('class', 'label ' + element_type + '_field');
						$('.label span', $(obj).closest('.form_element') ).html(element_type);
						
						// special case for showing/hiding the values bits for select and datalist elements, as they are the only ones that can have a range of values
						if(element_type == 'select' || element_type == 'datalist')
						{
							$('.list_values', $(obj).closest('.details') ).addClass('show');
						}
						else
						{
							// removes all but one element from the list and empties its value
							$('.list_value:not(:last)', $(obj).closest('.details') ).remove();
							$('.list_value input', $(obj).closest('.details') ).attr('value', '');
							$('.list_values', $(obj).closest('.details') ).removeClass('show');
						}
					});
				});

				// delete element
				$('.form_elements').on('click', '.form_element .action.delete', function(e){
					e.preventDefault();

					result = window.confirm('Are you sure you want to delete this element?');
					if(result)
						$(this).closest('.form_element').remove();
				});

				// get new element block
				$('.action.add_element').on('click', function(e){
					e.preventDefault();

					$.ajax({
						url: '/maverick_admin/ajax/get_form_element_block',
						data: {'element_type': 'text', 'display_order': $('.form_elements .form_element').length + 1},
						method: 'POST'
					}).success(function(data){
						$('.form_elements .elements').append(data);
					});
				});
				
				// add drag and drop to each film elements
				$('.form_elements .elements').sortable({
					container: 'parent',
					items: '.form_element',
					opacity: 0.8,
					cancel: 'input,select',
					stop: function(e, {}){
						// update the element order based on the position it was dragged to
						$('.form_elements .form_element input[name=display_order\\[\\]]').each(function(i){
							$(this).val((i+1));
						});

						// ensure that the explicit id values for the display and required checkboxes are also updated
						$('.form_elements .form_element input[name^=display\\[]').each(function(i){
							$(this).attr('name', 'display[' + i + ']');
						});
						$('.form_elements .form_element input[name^=required\\[]').each(function(i){
							$(this).attr('name', 'display[' + i + ']');
						});
						// and this bit updates the number in the multi-dimensional values input set so that the submitted values correspond with their element
						$('.form_elements .form_element .list_values').each(function(i){
							$('input[name^=values\\[]', $(this) ).each(function(j){
								$(this).attr('name', 'values[' + i + '][]');
							});
						});
					}
				});
				
				// remove a list value
				$('.form_elements').on('click', '.form_element .list_values .remove_value', function(e){
					e.preventDefault();

					button_obj = this;

					// only remove a value if it's not the last in the set, as an element of this type should always have at least one value, even if it's a blank string
					total_values = $('.list_value', $(this).closest('.list_values') ).length;
					if(total_values > 1)
						$(button_obj).parent().remove();
				});
				
				// add a new list value
				$('.form_elements').on('click', '.form_element .list_values .add_value', function(e){
					e.preventDefault();

					$('.list_value:last', $(this).closest('.list_values') ).clone().appendTo( $('.list_values_container', $(this).closest('.list_values') ) )
					
					$('.list_value:last input', $(this).closest('.list_values') ).focus().val('');
				});
				
				// submit the form
				$('body').on('click', '.action.save', function(e){
					$('form.form_elements.edit').submit();
				});
			}

			// everything else
			{
				// delete form
				$('.item_table').on('click', '.action.delete', function(e){
					e.preventDefault();

					result = window.confirm('Are you sure you want to delete this form?');
					if(result)
						location.href = e.target.href;
				});
				
				// delete form (complete)
				$('.item_table').on('click', '.action.delete_full', function(e){
					e.preventDefault();

					result = window.confirm('Are you sure you want to delete this form? This action cannot be undone!');
					if(result)
						location.href = e.target.href;
				});
			}
		}
	};
})(window.MAV = window.MAV || {}, jQuery);