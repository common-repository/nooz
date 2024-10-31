// contextual help
jQuery(function($){
    var qs = {};
    $.each(location.search.substr(1).split('&'), function(i,v){
        var p = v.split('=');
        qs[p[0]] = p[1];
    });
    if (qs.tab) {
        $('.contextual-help-tabs li').removeClass('active');
        $('#tab-link-' + qs.tab).addClass('active');
    }
    $('.nooz-help-link').on('click', function(e){
        e.preventDefault();
        $('#tab-link-' + $(this).data('helpTab') + ' a').trigger('click');
        if ( $('#contextual-help-wrap').is(':hidden')) {
            $('#contextual-help-link').trigger('click');
        }
    });
});
