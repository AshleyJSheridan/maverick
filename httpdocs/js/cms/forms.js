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
				});
			});
			
			// delete element
			$('.form_elements').on('click', '.form_element .action.delete', function(e){
				e.preventDefault();
				
				if(window.confirm('Are you sure you want to delete this element?'))
					$(this).closest('.form_element').remove();
			});
			
			// get new element block
			$('.action.add_element').on('click', function(e){
				$.ajax({
					url: '/maverick_admin/ajax/get_form_element_block',
					data: {'element_type': 'text', 'display_order': $('.form_elements .form_element').length + 1},
					method: 'POST'
				}).success(function(data){
					$('.form_elements').append(data);
				});
			});
			
			// submit the form
			$('.action.save').on('click', function(e){
				$('form.form_elements.edit').submit();
			});
		}
	};
})(window.MAV = window.MAV || {}, jQuery);