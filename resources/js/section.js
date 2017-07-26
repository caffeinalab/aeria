jQuery(function($){

    var box_reorder = $('.box-reorder');

    var initTMCE = function() {
        $('.wp-editor-wrap').each(function() {
            var editor_el = $(this);
            if (editor_el.find('iframe').length > 0) {
                return true;
            }
            var id = '#' + editor_el.find('.wp-editor-area').attr('id');
            var settings = tinymce.activeEditor && tinymce.activeEditor.settings
                ? tinymce.activeEditor.settings
                : {};
            settings.selector = '#' + editor_el.find('.wp-editor-area').attr('id');
            tinymce.init(settings);
        });
    };

    var renderSectionFields = function(section_fields, box_section, section_type) {
        box_section.find('[data-section-fields]').html(section_fields);
        box_section.attr('data-current-section-type', section_type);

        // re-init eventual new fields
        window.aeria_init_select2_ajax();
        window.aeria_init_select2();
        initTMCE();
    };

    var renderSection = function(section, content_section) {
        var sect_box = $(section);
        $('#'+content_section+' .box-sections').append(sect_box);
        sectionExpand(sect_box.find('[data-section-expand]'), true);
        engineSelectBg();
        initTMCE();
    };

    var generateSectionFieldsBehaviour = function(button, id_section, box_section, section_element) {
        if (!button.data('initialized')) {
            button.data('initialized', true);
            button.on('click', function(e2) {
                e2.preventDefault();
                var columns = box_section.find('[data-section-columns]').val();
                var post_type = $('#post_type').val();
                var section_num = box_section.attr('data-section-num');
                var section_type = box_section.find('[data-section-type]').val();
                if (!section_type) {
                    return;
                }
                if (section_type === box_section.attr('data-current-section-type')) {
                    return;
                }
                if (!section_element.data('new-section')) {
                    if (!confirm('Cambiando il tipo di Sezione si perderanno i dati compilati per la Sezione corrente; sei sicuro di voler continuare?')) {
                        return;
                    }
                }

                $.post(
                    window.ajaxurl,
                    {
                        action: 'add_section_fields',
                        section: section_num,
                        section_type: section_type,
                        post_type: post_type,
                        ncol: columns,
                        id_section: id_section
                    },
                    function(response) {
                        renderSectionFields(response, box_section, section_type);
                        section_element.data('new-section', false);
                    }
                );
            });
        }
    };

    var sectionExpand = function(that, new_section) {
        var new_section = new_section || false;
        that.on('click', function(e) {
            var id_section = that.data('section-expand');
            var content_section = (id_section.length > 0)?'aeria_section_'+id_section:'aeria_section';
            e.preventDefault();
            var section_element = $('#'+content_section+' .body-section');
            section_element.css('display','none');
            section_element.data('new-section', new_section);
            var box_section = that.parents('.box-section');
            box_section.find('.body-section').css('display','block');
            var generate_fields = box_section.find('[data-generate-section-fields]');
            if (generate_fields.length > 0) {
                generateSectionFieldsBehaviour(generate_fields, id_section, box_section, section_element);
            }
        });
    };

    $('[data-section-add]').on('click', function (e) {
        var that = $(this);
        var id_section = that.data('section-add');
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
            var post_type = $('#post_type').val();
            $.post(
                window.ajaxurl,
                {
                    action: 'add_section',
                    section: new_section_num,
                    post_type: post_type,
                    ncol: parseInt(ncol),
                    id_section: id_section
                },
                function(response) {
                    renderSection(response, content_section);
                }
            );
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

    $('[data-section-expand]').each(function() {
        sectionExpand($(this));
    });

    $('[data-section-remove]').on('click', function(e) {
        e.preventDefault();
        $(this).parent('li').hide().data('section-removed', true);
    });

    $('[data-section-sort]').on('click', function() {
        var id_section = $(this).data('section-sort');
        var content_section = (id_section.length > 0)?'aeria_section_'+id_section:'aeria_section';
        $('#'+content_section+' .box-sections').hide();
        $('#'+content_section+' .box-reorder').fadeIn();
        var box_controls = $('#'+content_section+' .box-controls');
        box_controls.css('opacity',0.3);
        box_controls.append('<div data-overlay class="box-controls-overlay"></div>');
    });

    $('[data-section-sortable-cancel]').on('click', function() {
        var id_section = $(this).data('section-sortable-cancel');
        var content_section = (id_section.length > 0)?'aeria_section_'+id_section:'aeria_section';
        $('#'+content_section+' .box-reorder').fadeOut(function() {
            $('#'+content_section+' .box-sections').show();
            $('#'+content_section+' [data-section-sortable] > li').each(function(index, el) {
                $(el).show().data('section-removed', false);
            });
        });
        var box_controls = $('#'+content_section+' .box-controls');
        box_controls.css('opacity',1);
        box_controls.children('[data-overlay]').remove();
    });

    $('[data-section-sortable]').sortable({
        cursor: 'move',
        containment: 'parent',
        axis: 'y'
    });

    $('[data-section-sortable-save]').on('click', function(e) {
        var order = [];
        var id_section = $(this).data('section-sortable-save');
        var content_section = (id_section.length > 0)?'aeria_section_'+id_section:'aeria_section';

        var post_id = $('[data-section-sortable]').data('post-id');

        $('#'+content_section+' [data-section-sortable] > li').each(function(index, el) {
            var removed = !!$(el).data('section-removed');
            if (!removed) {
                order.push($(el).data('section-id'));
            }
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
                button: { text:  meta_image.button },
                library: { type: 'image' }
            });

            // Runs when an image is selected.
            meta_image_frame.on('select', function(){

                // Grabs the attachment selection and creates a JSON representation of the model.
                var media_attachment = meta_image_frame.state().get('selection').first().toJSON();

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

});