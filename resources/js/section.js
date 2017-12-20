jQuery(function($){

    var box_reorder = $('.box-reorder');

    $('[data-section-add]').on('click', function (e) {
        var id_section = $(this).data('section-add');
        var content_section = (id_section.length > 0)?'aeria_section_'+id_section:'aeria_section';

        e.preventDefault();
        if(!box_reorder.is(':visible')) {
            var last_section = $('#'+content_section+' .box-sections > .box-section').last();
            var new_section_num;
            if(typeof last_section.data('section-num') === 'undefined'){
                new_section_num = 0;
            }else{
                new_section_num = parseInt(last_section.data('section-num'))+1;
            }
            var ncol = $('#ncol').val();

            $.post(window.ajaxurl, { action: 'add_section', section : new_section_num, ncol : parseInt(ncol), id_section : id_section }, function(response) {
                $('#'+content_section+' .box-sections').append(response);
                engineSelectBg();
            });
        }
    });

    $('[data-section-expand-all]').on('click', function(e) {
         var id_section = $(this).data('section-expand-all');
         var content_section = (id_section.length > 0)?'aeria_section_'+id_section:'aeria_section';
        e.preventDefault();
        if(!box_reorder.is(':visible')) {
          $('#'+content_section+' .box-sections').find('.body-section').css('display','block');
        }
    });

    $('[data-section-expand]').on('click', function(e) {
        var id_section = $(this).data('section-expand');
        var content_section = (id_section.length > 0)?'aeria_section_'+id_section:'aeria_section';
        e.preventDefault();
        $('#'+content_section+' .body-section').css('display','none');
        $(this).parents('.box-section').find('.body-section').css('display','block');
    });

    $('[data-section-remove]').on('click', function(e) {
        e.preventDefault();
        $(this).parent('li').remove();
    });

    $('[data-section-sort]').on('click', function() {
        var id_section = $(this).data('section-sort');
         var content_section = (id_section.length > 0)?'aeria_section_'+id_section:'aeria_section';
        $('#'+content_section+' .box-sections').hide();
        $('#'+content_section+' .box-reorder').fadeIn();
        $('#'+content_section+' .box-controls').css('opacity',0.3);
    });

    $('[data-section-sortable]').sortable({
        cursor: 'move',
        containment: 'parent',
        axis: 'y'
    });

    $('[data-section-sortable-save]').on('click', function() {
        var order = [];
        var id_section = $(this).data('section-sortable-save');
        var content_section = (id_section.length > 0)?'aeria_section_'+id_section:'aeria_section';

        var post_id = $('[data-section-sortable]').data('post-id');

        $('#'+content_section+' [data-section-sortable] > li').each(function(index, el) {
            order.push($(el).data('section-id'));
        });

        $.post(window.ajaxurl, { action: 'sort_section', order : order, post_id : post_id, 'id_section' : id_section  }, function(response) {
            location.reload();
        });
    });

    $('[data-generate-section]').on('click', function(e) {
        e.preventDefault();
        var $btn_draft = $('#save-post');
        if($btn_draft.length){
            $btn_draft.trigger('click');
        }else{
            $('#publish').trigger('click');
        }

    });
    var $select_preview = $('[data-select-preview]');

    if($select_preview.length){
         $('[data-select-preview]').on('change', function(e) {
            e.preventDefault();
            var path = $(this).attr('data-select-preview');
            var img = $(this).val();
            var $wrap = $(this).parents('.body-section').find('.wrap-preview');
            $wrap.empty();
            if( img.length  && imageExists( path + img ) ) $wrap.html('<img src="'+path+img+'.png">');
        });

        $select_preview.trigger('change');
    }

    function imageExists(image_url){

        var http = new XMLHttpRequest();

        http.open('HEAD', image_url, false);
        http.send();

        return http.status != 404;

    }

    function engineSelectBg(){

        // Runs when the image button is clicked.
        $('[data-section-background]').click(function(e){

            // Instantiates the variable that holds the media library frame.
            var meta_image_frame;

            var container = $(this);
            var input = container.find('input');

            // Prevents the default action from occuring.
            e.preventDefault();

            // If the frame already exists, re-open it.
            if ( meta_image_frame ) {
                meta_image_frame.open();
                return;
            }

            // Sets up the media library frame
            meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
                title: meta_image.title,
                button: { text:  meta_image.button }
            });

            // Runs when an image is selected.
            meta_image_frame.on('select', function(){

                // Grabs the attachment selection and creates a JSON representation of the model.
                var media_attachment = meta_image_frame.state().get('selection').first().toJSON();
                container.find('span.label').empty();
                if(media_attachment.subtype == 'mp3' || media_attachment.subtype == 'mpeg'){
                    container.find('span.label').html(media_attachment.filename);
                }
                // Sends the attachment URL to our custom image input field.
                input.val(media_attachment.url);
                container.css('background-image' , 'url('+media_attachment.url+')');
            });

            // Opens the media library frame.
            meta_image_frame.open();
        });

        $('[data-remove-background]').on('click', function () {
            var container = $(this).parent().find('[data-section-background]');
            var input = container.find('input');

            input.val('');
            container.css('background-image' , 'url()');

        });
    }


    engineSelectBg();

    $('.post_section_title').on('keypress', function (ev) {
        if (ev.keyCode == 13) return false;
    });
});
