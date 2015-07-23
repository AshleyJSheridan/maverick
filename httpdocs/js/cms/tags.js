/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

;(function(MAV, $){
	$(function() {
		MAV.Tags.init();
	});
	
	MAV.Tags = {
		init : function(){
			
			// drag and drop functionality
			 $('.tag_groups .tag').draggable({
				revert: 'invalid', // when not dropped, the item will revert back to its initial position
				containment: '.tag_groups',
				//helper: 'clone',
				cursor: 'grabbing',
				appendTo: 'body'
			});
			
			$('.tag_groups .tags').droppable({
				accept: '.tag',
				drop: function( event, ui ) {
					$(ui.draggable).css({
						top: 'inherit',
						left: 'inherit',
						bottom: 'inherit',
						right: 'inherit'
					}).appendTo($(this) );
				}
			});
			
			// edit functionality of tags and tag group names
			$('.tag_groups').on('dblclick', '.tag, .tag_group_name', function(e){
				$('span', $(this)).hide();
				$('input', $(this)).show().focus();
			});
			$('.tag_groups').on('blur', 'input', function(e){
				$('span', $(this).parent() ).html($(this).val() ).show();
				$(this).hide();
			});

		}
	};
})(window.MAV = window.MAV || {}, jQuery);

