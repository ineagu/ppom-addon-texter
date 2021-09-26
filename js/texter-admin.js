"use strict"
jQuery(function($){

    /******************************
    *    Texter Addon Admin JS    *
    *******************************/


    /*-------------------------------------------------------
        
        ------ Its Include Following Function -----

        1- TextBox ID
        2- Load WP Color Picker
        3- Hide All Textboxes Settings Panel Before Click
        4- Handle Image Upload Button
        5- Add Textboxes Over Image
        6- Save All Textboxes Meta Settings
        7- Show Alert On Empty Textboxes Title On Publish Meta
        8- Remove Textboxes And Thier Settings
        9- Show Active Textbox And Thier Settings
        10- Get Value Of Textbox On Positions & Resize
        11- Textboxes Dragable Over The Image
        12- Textboxes Resizable Over The Image
        13- Reset The Position Of Textbox Over The Image After Add New Box Function
        14- Defualt Textboxes Position Over Image Function
        15- Create Texter Settings Meta On Hidden Input Function
        16- Upload Image Event
        17- Remove Image Event
    ------------------------------------------------------------*/


    /**
        1- TextBox ID
    **/
    var textbox_id = 0;


    /**
        2- Load WP Color Picker
    **/
    $('.wp-color').wpColorPicker();

    
    /**
        3- Hide All Textboxes Settings Panel Before Click
    **/
    $('.ppom-setting-panel-clone').hide();
    $('#ppom_textbox_setting_panel').hide();


    /**
        4- Handle Image Upload Button
    **/
    var img_id = $('#ppom_image_upload').val();
    if (img_id != '') {
        $('.ppom-textbox-area').show();
        $('.ppom-texbox-add').show();
        $('.ppom-img-selector').removeClass('button ppom_upload_image_btn');
    }


    /**
        5- Add Textboxes Over Image
    **/
    $(document).on("click", '.ppom-texbox-add button', function(e){
        e.preventDefault();

        $('.ppom-textbox-area').show();
        var last_box_id = $('#ppom-last-box-id').val();
        
        // set textbox defualt position after rendering
        var xPos = Number(9);
        var yPos = Number(22);

        var newpos = yPos -2;

        // clone textboxes
        var textbox_panel  = $('.ppom-textbox-area:last').clone();

        textbox_panel.find('.ui-resizable-handle').remove();

        textbox_id = Number(last_box_id)+1;

        $('#ppom-last-box-id').val(textbox_id);

        textbox_panel.attr('data-textbox-id', textbox_id);
        textbox_panel.find('.ppom-img-uploader-area').end().appendTo('.ppom-img-uploader-area');
        textbox_panel.find('.ppom-textbox-tool').attr('data-draggable-id', textbox_id);
        
        // clone textboxes setting panel
        var panel_id      = 'setting-panel-'+textbox_id;
        var setting_panel = $('.ppom-setting-panel-clone:last').clone().attr('id',panel_id);
        setting_panel.find('#ppom-texbox-title').val('');
        setting_panel.find('.ppom-setting-panel-wrapper').end().appendTo('.ppom-setting-panel-wrapper');
        setting_panel.attr('data-setting-panel', textbox_id);
        setting_panel.find('.ppom-setting-btn').attr('data-setting-btn-id', textbox_id);

        // load wp color picker after clone color settings
        setting_panel.find('.ppom-text-bgcolor-label').next().next()
        .html('<input id="ppom-textbox-bg-color" class="wp-color">');
        setting_panel.find('.ppom-text-color-label').next().next()
        .html('<input id="ppom-textbox-color" class="wp-color">');
        setting_panel.find('.wp-color').wpColorPicker();

        // hide setting panel defualt before textbox click
        setting_panel.css('display', 'none');
        
        // reset all textboxes meta atfter clone
        textbox_panel.find('.ppom-textbox-show').css('font-size','');
        textbox_panel.find('.ppom-textbox-show').css('font-family','');
        textbox_panel.find('.ppom-textbox-show').css('color','');
        textbox_panel.find('.ppom-textbox-tool').css('height','');
        textbox_panel.find('.ppom-textbox-tool').css('width','');
        textbox_panel.find('.ppom-textbox-tool').css('background-color','');

        // remove active textbox class after clone
        $('.ppom-textbox-tool').removeClass('ppom-textbox-active');

        // add textbox notice
        if (textbox_panel) {
            $('.texter-textbox-notice-js').show(); 
            setTimeout(function(){
            $('.texter-textbox-notice-js').hide(); 
            }, 2000);
        }

        //textboxes draggable callback function
        ppom_textbox_dragable(textbox_id);

        // textboxes resizable callback function
        ppom_textbox_resizable(textbox_id)

        // textboxes defualt position over image callback function
        ppom_defualt_textbox_postion(textbox_id, xPos, yPos);

        // get defualt xPos & yPos in hidden input callback functions
        ppom_create_hidden_input('x_pos', xPos, textbox_id);
        ppom_create_hidden_input('y_pos', yPos, textbox_id);

        // remove textbox icon position controle callback function
        ppom_set_remove_icon_postion(textbox_id, xPos, newpos);

    });


    /**
        6- Save All Textboxes Meta Settings
    **/
    $(document).on('click', ".ppom-setting-btn", function(e){
        e.preventDefault();

        var textbox_id    = $(this).data('setting-btn-id');
        var setting_panel = $("#setting-panel-"+textbox_id);

        // get all textbox setting meta
        var font_size   = setting_panel.find('#ppom-textbox-font-size').val();
        var font_family = setting_panel.find('#ppom-textbox-font-family').val();
        var font_color  = setting_panel.find('#ppom-textbox-color').val();
        var title       = setting_panel.find('#ppom-texbox-title').val();
        var max_char    = setting_panel.find('#ppom-textbox-max-char').val();
        var min_char    = setting_panel.find('#ppom-textbox-min-char').val();
        var bg_color    = setting_panel.find('#ppom-textbox-bg-color').val();

        // title of textboxes is required
        if (title == '') {
            setting_panel.find('.texter-setting-alert').html('Textbox Title Required');
        }else{
            setting_panel.find('.texter-setting-alert').html('');
        }

        // apply textbox getting setting meta on selected textbox
        $("[data-draggable-id='"+textbox_id+"']").find('.ppom-textbox-show').css('font-size', font_size);
        $("[data-draggable-id='"+textbox_id+"']").find('.ppom-textbox-show').css('font-family', font_family);
        $("[data-draggable-id='"+textbox_id+"']").find('.ppom-textbox-show').css('color', font_color);
        $("[data-draggable-id='"+textbox_id+"']").css('background-color', bg_color);

        // get all textbox setting meta in hidden input
        ppom_create_hidden_input('font_size', font_size, textbox_id);
        ppom_create_hidden_input('font_family', font_family, textbox_id);
        ppom_create_hidden_input('font_color', font_color, textbox_id);
        ppom_create_hidden_input('textbox_title', title, textbox_id);
        ppom_create_hidden_input('max_char', max_char, textbox_id);
        ppom_create_hidden_input('min_char', min_char, textbox_id);
        ppom_create_hidden_input('font_bg_color', bg_color, textbox_id);

    });


    /**
        7- Show Alert On Empty Textboxes Title On Publish Meta 
    **/
    $('#publish').click(function(){

        var $return       = true;
        var setting_panel = $('.ppom-setting-panel-wrapper');
        setting_panel.find('.ppom-setting-panel-clone').each(function(i, meta_field){
        
            var textbox_title = $(meta_field).find('#ppom-texbox-title').val();
            if (textbox_title == '') {
                $return = false;
            }
        });

        if (!$return) {
            alert('Do Not Empty Any Textbox Title');
            return $return;
        }

        $('.ppom-setting-btn').trigger('click');
    });


    /**
        8- Remove Textboxes And Thier Settings
    **/
    $('.ppom-design-wrapper').on('click','.ppom-textbox-remove', function(e){
        e.preventDefault();

        var count= $('div.ppom-textbox-tool').length;
        if ( count < 2 ) {
            alert('sorry! you can not remove more textbox');
            return;
        }
        var textbox_id =  $(this).closest(".ppom-textbox-area").attr('data-textbox-id');
        
        $('.ppom-box-'+textbox_id+'').remove();
        $(this).closest(".ppom-textbox-area").remove();
        $("[data-setting-panel='"+textbox_id+"']").remove();
    });


    /**
        9- Show Active Textbox And Thier Settings
    **/
    $('.ppom-design-wrapper').on('click', ".ppom-textbox-tool", function(e){
        e.preventDefault();

        var textbox_id = $(this).data('draggable-id');

        $('.ppom-textbox-tool').removeClass('ppom-textbox-active texterActive');
        $(this).addClass('ppom-textbox-active texterActive');

        $('.ppom-setting-panel-clone').removeClass('ppom-setting-active').addClass('ppom-setting-inactive');
        $("[data-setting-panel='"+textbox_id+"']").addClass('ppom-setting-active').removeClass('ppom-setting-inactive');


        if ($(this).hasClass('texterActive')) {
            $('#ppom_textbox_setting_panel').show();
        }else{
            $('#ppom_textbox_setting_panel').hide();
        }
        
        $('.ppom-setting-active').show();
        $('.ppom-setting-inactive').hide(); 
    });


    /**
        10- Get Value Of Textbox On Positions & Resize
    **/
    $('.ppom-textbox-tool').each(function(i, meta_field){

        var textbox_id = $(meta_field).attr('data-draggable-id');
        ppom_textbox_dragable(textbox_id);
        ppom_textbox_resizable(textbox_id);
        var x = $(this).position();
        var count= $('div.ppom-textbox-tool').length;
        
        var width = $(this).width();
        var height = $(this).height();

        ppom_set_remove_icon_postion(textbox_id, x.left, x.top);

        ppom_create_hidden_input('x_pos', x.left, textbox_id);
        ppom_create_hidden_input('y_pos', x.top, textbox_id);
        ppom_create_hidden_input('width', width, textbox_id);
        ppom_create_hidden_input('height', height, textbox_id);
    });


    /**
        11- Textboxes Dragable Over The Image
    **/
    function ppom_textbox_dragable(textbox_id){

        $("[data-draggable-id='"+textbox_id+"']").draggable({
            containment: "#ppom-textbox-cover img",
            drag: function(e, ui){

                var xPos = ui.position.left;
                var yPos = ui.position.top;

                ppom_defualt_textbox_postion(textbox_id, xPos, yPos);
                ppom_set_remove_icon_postion(textbox_id, xPos, yPos);
            },
            stop: function(e, ui) {

                var xPos = ui.position.left;
                var yPos = ui.position.top;

                ppom_create_hidden_input('x_pos', xPos, textbox_id);
                ppom_create_hidden_input('y_pos', yPos, textbox_id);
            }
            
        });             
    }


    /**
        12- Textboxes Resizable Over The Image
    **/
    function ppom_textbox_resizable(draggable){

        $("[data-draggable-id='"+draggable+"']" ).resizable({
            containment: "#ppom-textbox-cover img",
            minHeight: 25,
            minWidth: 84,

            stop: function(event, ui) {
                var width = ui.size.width;
                var height = ui.size.height;

                ppom_create_hidden_input('width', width, draggable);
                ppom_create_hidden_input('height', height, draggable);
            }
            
        });
    }


    /**
        13- Reset The Position Of Textbox Over The Image After Add New Box Function
    **/
    function ppom_set_remove_icon_postion(textbox_id, xPos, yPos){
        
        yPos = yPos - 20;
        var selector_id = $("[data-textbox-id='"+textbox_id+"']");
        $('.ppom-textbox-remove', selector_id).css('top',yPos);
        $('.ppom-textbox-remove', selector_id).css('left',xPos);
    }


    /**
        14- Defualt Textboxes Position Over Image Function
    **/
    function ppom_defualt_textbox_postion(draggable, xPos, yPos){
        
        $("[data-draggable-id='"+draggable+"']").css('top', yPos + 'px');
        $("[data-draggable-id='"+draggable+"']").css('left', xPos + 'px');
    }


    /**
        15- Create Texter Settings Meta On Hidden Input Function
    **/
    function ppom_create_hidden_input( attr, value, textbox_id ) {

        var container = $('.ppom-design-wrapper');
        // GETTING X
        var the_id    = 'ppom-'+attr+'-'+textbox_id;
        var the_class = 'ppom-box-'+textbox_id+' ppom-all-remove';
        // remove/reset
        $("#"+the_id).remove();
        var _x = $('<input/>')
                .attr({'type':'hidden','name':'ppom_design['+textbox_id+']['+attr+']'})
                .attr('id', the_id)
                .attr('class', the_class)
                .val(value)
                .appendTo(container);
    }


    /**
        16- Upload Image Event
    **/
    $('body').on('click', '.ppom_upload_image_btn', function(e){
        e.preventDefault();
 
            var button = $(this),
                custom_uploader = wp.media({
            title: 'Insert image',
            library : {
                // uncomment the next line if you want to attach image to the current post
                // uploadedTo : wp.media.view.settings.post.id, 
                type : 'image'
            },
            button: {
                text: 'Use this image' // button label text
            },
            multiple: false // for multiple image selection set to true
        }).on('select', function() { // it also has "open" and "close" events 
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $(button).removeClass('button ppom_upload_image_btn').html('<img class="true_pre_image" src="' + attachment.url + '" style="display:block;" />').next().val(attachment.id).next().show();
            
            if (attachment.id) {
                $('.ppom-textbox-area').show();
                $('.ppom-texbox-add').show();
            }

        })
        .open();

        $('.ppom-textbox-tool').each(function(i, meta_field){
            var textbox_id = $(meta_field).attr('data-draggable-id');
        
            ppom_create_hidden_input('x_pos', 9, textbox_id);
            ppom_create_hidden_input('y_pos', 22, textbox_id);
        
        });

    });
 

    /**
        17- Remove Image Event
    **/
    $('body').on('click', '.ppom-remove-image-btn', function(){
        $(this).hide().prev().val('').prev().addClass('button ppom_upload_image_btn').html('Upload image');

        if ($(this).prev().val()== '') {
            $('.ppom-textbox-area').hide();
            $('.ppom-texbox-add').hide();
            $('.ppom-setting-panel-clone').hide();
        }
        
        $('.ppom-all-remove').remove();
        $('.ppom-textbox-area').not(':first').remove();
        $('.ppom-setting-panel-clone').not(':first').remove();

        $('.ppom-textbox-area').find('.ppom-textbox-tool')
        .css({"top":22,"left":9});

        $('.ppom-textbox-area').find('.ppom-textbox-remove')
        .css({"top":0,"left":9});

        return false;
    });
});