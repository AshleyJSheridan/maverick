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
			
			// permissions bit
			{
				// update permissions confirmation
				$('body').on('click', '.action.update_permissions', function(e){
					e.preventDefault();

					result = window.confirm('Are you sure you want to update the permissions list? This can take a long time to complete if there are a lot of files to scan.');
					if(result)
						location.href = e.target.href;
				});
				
				// permissions deletion confirmation
				$('body').on('click', '.action.delete_permission', function(e){
					e.preventDefault();

					result = window.confirm('Are you sure you want to permantently remove this permission? Note, it can only be removed if no users currently have this in their permission list.');
					if(result)
						location.href = e.target.href;
				});
				
				// add new permission row to table
				$('body').on('click', '.action.add_permission', function(e){
					e.preventDefault();
					
					$('.item_table tr').last().clone().appendTo('.item_table');
					$('input', $('.item_table tr').last() ).attr('value', '');
				});
				
				// save form permissions
				$('body').on('click', '.action.save_permissions', function(e){
					e.preventDefault();
					
					$('form.permissions').submit()
				});
			}
			
			// users bits
			{
				$('.item_table').on('click', '.action.delete_user', function(e){
					e.preventDefault();
					
					result = window.confirm('Are you sure you want to delete this user? This action can\'t be undone and you will need to recreate the user to undo this action.');
					if(result)
						location.href = e.target.href;
				});
			}
		}
	};
})(window.MAV = window.MAV || {}, jQuery);