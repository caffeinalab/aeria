window.aeria_setup_media_upload_fields = function(){
  var file_frame;

  jQuery('.aeria_upload_media_button').off('click').on('click', function( event ){
    var $me = jQuery(this),
        target = $me.data('target').replace('##','#'), // FIX!
        $target = jQuery(target),
        $target_image = jQuery(target+'_image');

    event.preventDefault();

      // Create the media frame.
      file_frame = wp.media.frames.file_frame = wp.media({
        title: jQuery( this ).data( 'uploader_title' ),
        button: {
          text: jQuery( this ).data( 'uploader_button_text' )
        },
        multiple: false  // Set to true to allow multiple files to be selected
      });

      // When an image is selected, run a callback.
      file_frame.on( 'select', function() {
        // We set multiple to false so only get one image from the uploader
        attachment = file_frame.state().get('selection').first().toJSON();

        // Do something with attachment.id and/or attachment.url here
        $target.val(attachment.url);
        if($target_image.length) $target_image.attr('src',attachment.url);
      });

      // Finally, open the modal
      file_frame.open();
    });
};

window.aeria_setup_media_gallery_fields = function(){
  var file_frame;

  jQuery('.aeria_upload_media_gallery_button').off('click').on('click', function( event ){
    var $me = jQuery(this),
        target = $me.data('target').replace('##','#'), // FIX!
        $target = jQuery(target),
        $target_image = jQuery(target+'_image');

    event.preventDefault();

      // Create the media frame.
      file_frame = wp.media.frames.file_frame = wp.media({
        title: jQuery( this ).data( 'uploader_title' ),
        button: {
          text: jQuery( this ).data( 'uploader_button_text' )
        },
        multiple: true  // Set to true to allow multiple files to be selected
      });

      // When an image is selected, run a callback.
      file_frame.on( 'select', function() {
        // We set multiple to false so only get one image from the uploader
        attachment = file_frame.state().get('selection').first().toJSON();

        // Do something with attachment.id and/or attachment.url here
        $target.val(attachment.url);
        if($target_image.length) $target_image.attr('src',attachment.url);
      });

      // Finally, open the modal
      file_frame.open();
    });
};

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
            this.addClass('loaded').css({opacity:1});
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
        tmp += '<div style="height:120px;clear:both"><img src="http://static.appcaffeina.com/assets/i/120x120/' + $me.data('image') + '" style="float:left;"><span style="line-height:120px;float:left;margin-left:1em;font-size:15px">'+$me.data('html')+'</span></div>';
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
        tmp += '<img src="http://static.appcaffeina.com/assets/i/120x120/' + $me.data('image') + '"><br>'+$me.data('html');
      } else {
        tmp += $me.data('html') || state.text;
      }
      return tmp ;
    });
  });
};

window.aeria_setup_media_upload_fields();
window.aeria_setup_media_gallery_fields();
window.aeria_init_select2();
