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
			$('.form_elements .form_element .label').on('click', function(e){
				$('.details', $(this).parent() ).toggleClass('active');
			});
			
			// tab navigation
			$('.form_elements .form_element .tab-nav li').on('click', function(e){
				$('li, .tab', $(this).parent().parent() ).removeClass('active');
				
				$(this).addClass('active');
				$('.' + $(this).data('tab'), $(this).parent().parent() ).addClass('active');
				//$('.' + $(this).data('tab'), this ).addClass('active');
			});
			
			$('.form_elements .form_element .details select[name=type\\[\\]]').on('change', function(e){
				element_type = this.value;
				element_value = $('input[name=value\\[\\]]', $(this).closest('.details') ).val();
				placeholder = $('input[name=placeholder\\[\\]]', $(this).closest('.details') ).val();
				var obj = this;
				
				$.ajax({
					url: 'http://maverick.local/maverick_admin/ajax/get_form_element',
					data: {'element_type': element_type, 'element_value': element_value, 'placeholder': placeholder},
					method: 'POST'
				}).success(function(data){
					$('.element', $(obj).closest('.form_element') ).html(data);
				});
			});
		}
	};
})(window.MAV = window.MAV || {}, jQuery);