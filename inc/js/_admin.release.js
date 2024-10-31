/**
 * Release Intro meta box functionality
 */
(function($){
    var meta_box_el = $('#nooz-meta-box__release-dateline');
    function showPreview(full) {
        var location_val = $('.nooz-meta-box-field__input--location', meta_box_el).val();
        if (!location_val) location_val = $('.nooz-meta-box-field__input--location', meta_box_el).attr('placeholder');
        $('.nooz-dateline__location', meta_box_el).html(location_val);
    }
    showPreview();
    meta_box_el.on('click', '.nooz-release__edit-location-link', function(e){
        e.preventDefault();
        var el = $('.nooz-meta-box-field-group-location', meta_box_el);
        el.is(':hidden') ? el.show() : el.hide();
        $('.nooz-meta-box-field__input--location', meta_box_el).focus();
    });
    meta_box_el.on('change keyup', '.nooz-meta-box-field__input--location', function(e) {
        showPreview();
    });
})(jQuery);
