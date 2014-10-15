jQuery(function($){
	var $searchfield = $('.search');
	var $postList = $('#post-list');
	var $message_order = $('#message-order');

	var options = {
		valueNames: [ 'list_field' , 'list_title' ]
	};

	var resultList = new List('list', options);

	resultList.on('updated', function (e) {
		if ($searchfield.val()!=""){
			$postList.nestedSortable('disable');
			$message_order.slideDown();
		}else{
			$postList.nestedSortable('enable');
			$message_order.slideUp();
		}
	});
});