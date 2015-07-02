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
					//$('.' + $(this).data('tab'), this ).addClass('active');
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
						$('.label span', $(obj).closest('.form_element') ).html(element_type)
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
						})
						$('.form_elements .form_element input[name^=required\\[]').each(function(i){
							$(this).attr('name', 'display[' + i + ']');
						})
					}
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
			}
		}
	};
})(window.MAV = window.MAV || {}, jQuery);