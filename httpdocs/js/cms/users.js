/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

;(function(MAV, $){
	$(function() {
		MAV.Users.init();
	});
	
	MAV.Users = {
		init : function(){
			
			// delete form
				$('body').on('click', '.action.update_permissions', function(e){
					e.preventDefault();

					result = window.confirm('Are you sure you want to update the permissions list? This can take a long time to complete.');
					if(result)
						location.href = e.target.href;
				});
			
		}
	};
})(window.MAV = window.MAV || {}, jQuery);