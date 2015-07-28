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

			// add tag button
			$('.action.add_tag').on('click', function(e){
				e.preventDefault();

				var new_tag_name = 'new tag';
				
				$('.tag_group .tag').first()
					.clone()
					.appendTo('.tag_group.ungrouped .tags')
						.find('span').html(new_tag_name)
						.parent().find('input').attr('value', new_tag_name)
			});
			
			// add group button
			$('.action.add_group').on('click', function(e){
				e.preventDefault();
				
				var new_group_name = 'new group ' + ($('.tag_groups .tag_group').length + 1 )
				
				$('.tag_group.ungrouped')
					.clone()
					.appendTo('.tag_groups')
					.removeClass('ungrouped')
						.find('.tag_group_name input').attr('value', new_group_name )
						.parent().find('span').html(new_group_name)
						.parent().parent().find('.tags').droppable({
							accept: '.tag',
							drop: function( event, ui ) {
								$(ui.draggable).css({
									top: 'inherit',
									left: 'inherit',
									bottom: 'inherit',
									right: 'inherit'
								}).appendTo($(this) );
							}
						})
						.parent().find('.tags .tag').remove();
			});
			
			// save button
			$('.action.save').on('click', function(e){
				e.preventDefault();
				
				$('.tag_groups .tag_group').each(function(t, obj){
					tag_count = $('.tag', obj).length;
					
					$('input[name=tag_count\\[\\]]', obj).attr('value', tag_count);
				});

				$('form.tag_groups').submit();
			});
		}
	};
})(window.MAV = window.MAV || {}, jQuery);

