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
		}
	};
})(window.MAV = window.MAV || {}, jQuery);