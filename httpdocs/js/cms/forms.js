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
			$('.form_elements .form_element .label').on('click', function(e){
				console.log(this)
			});
		}
	};
})(window.MAV = window.MAV || {}, jQuery);