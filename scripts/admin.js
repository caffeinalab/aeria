jQuery(function(){
  (function(formatRes,formatSel){
    var $sel = jQuery('.select2');
    $sel.select2({
       formatResult: formatRes,
       minimumInputLength: $sel.data('minimum'),
       allowClear: false,
       formatSelection: formatSel || undefined,
       escapeMarkup: function(m) { return m; }
    }).on('select2-loaded',function(){
      this.addClass('loaded').css({opacity:1});
    });
    
  })(
  function(state){
    if (!state.id) return state.text; // optgroup
    var originalOption = state.element,
        $me = jQuery(originalOption);
    var tmp = '';
    if($me.data('image')) {
      // tmp += '<div style="height:120px;clear:both"><img src="http://static.appcaffeina.com/assets/i/120x120/' + $me.data('image') + '" style="float:left;"><span style="line-height:120px;float:left;margin-left:1em;font-size:15px">'+$me.data('html')+'</span></div>';

      tmp += '<div style="height:120px;clear:both"><img  width="120" height="120" src="' + $me.data('image') + '" style="float:left;"><span style="line-height:120px;float:left;margin-left:1em;font-size:15px">'+$me.data('html')+'</span></div>';
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
      //tmp += '<img src="http://static.appcaffeina.com/assets/i/120x120/' + $me.data('image') + '"><br>'+$me.data('html');
      tmp += '<img width="120" height="120" src="' + $me.data('image') + '"><br>'+$me.data('html');
    } else {
      tmp += $me.data('html') || state.text;
    }
    return tmp ;
  });
});
