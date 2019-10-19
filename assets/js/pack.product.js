(function($){
	$(document).on('change','.pack-selector',function(){
		var id = ($(this).attr("ref"));
		$("#"+id).val($(this).val())
	})
})(jQuery);
