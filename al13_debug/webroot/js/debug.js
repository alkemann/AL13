$(document).ready(function(){
	$('.debug-dump').each(function() {
		$(this).click(function() {
			$(this).children('.debug-content').each(function() {$(this).toggle();});
		});
	});
});