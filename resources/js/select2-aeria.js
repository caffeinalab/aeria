// Init Select2
window.aeria_init_select2 = function(){
	jQuery(function(){
		(function(formatRes,formatSel){
			jQuery('.select2:not(.multisettings)').each(function(idx,e){
				var $this = jQuery(e);
				if( ! $this.data("select2")){
					$this.select2({
						formatResult: formatRes,
						minimumInputLength: $this.data('minimum'),
						formatSelection: formatSel || undefined,
						escapeMarkup: function(m) { return m; }
					}).on('select2-loaded',function(){
						$this.addClass('loaded').css({opacity:1});
					});
				}
			});
		})(
	function(state){
      if (!state.id) return state.text; // optgroup
      var originalOption = state.element,
      $me = jQuery(originalOption);
      var tmp = '';
      if($me.data('image')) {
      	tmp += '<div class="box-preview small"><div class="image-preview small" style="background-image:url('+$me.data('image')+');"></div><span>'+$me.data('html')+'</span></div>';
      } else {
      	tmp += $me.data('html') || state.text;
      }
      return tmp ;
  },
  function(state){
  	var originalOption = state.element,
  	$me = jQuery(originalOption);
  	var tmp = '';
  	if($me.data('image')) {
  		tmp += '<div class="box-preview"><div class="image-preview" style="background-image:url('+$me.data('image')+');"><span>'+$me.data('html')+'</span></div></div>';
  	} else {
  		tmp += $me.data('html') || state.text;
  	}
  	return tmp ;
  });
	});
};

window.aeria_init_select2_ajax = function(){
	jQuery(function(){
		(function(formatRes,formatSel){
			jQuery('.select2_ajax').each(function(idx,e){
				var $this = jQuery(e);
				var posts_per_page = 10;
				var multiple = $this.attr('data-multiple');
				if(multiple=="true") {
					multiple = true;
				}else{
					multiple = false;
				}
				var relation = $this.attr('data-relation'),
					type = $this.attr('data-with');
				if( ! $this.data("select2")){
					$this.select2({
						minimumInputLength: 0,
						formatResult: formatRes,
						formatSelection: formatSel || undefined,
						allowClear: true,
						multiple: multiple,
						ajax: {
							url: window.ajaxurl,
							dataType: 'json',
							quietMillis: 300,
							data: function (term, page) {
								return {
								  q: term,
								  page: page,
								  posts_per_page : posts_per_page,
								  action: 'aeria_search',
								  post_type: relation,
								  type: type
								};
							},
							results: function (data, page) {
								var more = (page * posts_per_page) < data.total;
								return { results: data.result, more: more };
							},
    					},
						initSelection: function(element, callback) {

							var id=$this.val();
							if (id!=''||id!='-') {

							    jQuery.ajax(window.ajaxurl, {
							        data: {
							            id : id,
							            action : 'aeria_search_init',
							            multiple : multiple,
								  		type: type,
							            post_type: relation,
							        },
							        dataType: "json"
							    }).done(function(data) {
							    	jQuery.each(data,function( index, value ){
							    		data[index].text = jQuery("<textarea/>").html(value.text).val();
							    	});
							    	callback(data);
							    });
							}
						},
					});
				}
			});
		})();
	});
};

window.aeria_init_select2_ajax();
window.aeria_init_select2();
