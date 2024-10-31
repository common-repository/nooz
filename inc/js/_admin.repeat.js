/**
 * Repeating field functionality. Class names are only used in js.
 *
 * Class names used to facilitate functionality:
 *
 * mightydev-repeat // container
 *   mightydev-repeat__add // add link
 *   mightydev-repeat__items // items container
 *     mightydev-repeat__item // generated item from template
 *   mightydev-repeat__template // item template for duplication
 *     mightydev-repeat__item
 *       mightydev-repeat__remove // remove link
 *       mightydev-repeat__count // item count starting at 1
 *
 * @version 0.2.0
 */
(function($){
    function rehydrate(selector, data) {
        var el = $(selector).closest('.mightydev-repeat');
        if (!el.length) return;
        var data = 'undefined' != typeof data ? data : el.data('repeat');
        $.each(data, function(i, item_data){
            rehydrateItem(createItem(el), item_data);
        });
    }
    function rehydrateItem(selector, data) {
        if ('undefined' == typeof data) return;
        var el = $(selector);
        if (Array.isArray(data)) {
            $.each(data, function(k, v){
                $('[name*="['+k+']"]', el).val(v);
            });
        } else {
            $('input, textarea', el).val(data);
        }
    }
    function createItem(selector) {
        var el = $(selector).closest('.mightydev-repeat');
        var items_el = $('.mightydev-repeat__items', el);
        var item_el = $('.mightydev-repeat__template .mightydev-repeat__item', el).clone().appendTo(items_el);
        enableInputs(item_el);
        el.trigger('create', [item_el]);
        return item_el;
    }
    $('.mightydev-repeat').each(function(){
        rehydrate(this);
        $(this).trigger('ready');
    });
    function renumber(selector) {
        if ('undefined' == typeof selector) return;
        $(selector).each(function(){
            var container_el = $(this).closest('.mightydev-repeat');
            $('.mightydev-repeat__items > *', container_el).each(function(i, el){
                $('[for]', this).each(function(){
                    $(this).attr('for', $(this).attr('for').replace(/\[\d*\]/i, '['+i+']'));
                });
                $('[name]', this).each(function(){
                    $(this).attr('name', $(this).attr('name').replace(/\[\d*\]/i, '['+i+']'));
                });
                $('[id]', this).each(function(){
                    $(this).attr('id', $(this).attr('id').replace(/\[\d*\]/i, '['+i+']'));
                });
                $('.mightydev-repeat__count', this).text(i+1);
            });
        });
    }
    function disableInputs(selector, isDisabled) {
        if ('undefined' == typeof selector) return;
        isDisabled = ('undefined' !== typeof isDisabled) ? isDisabled : true;
        var formElements = 'input, textarea, select, button';
        var elements = $(selector).is(formElements) ? $(selector) : $(formElements, selector);
        elements.each(function(){
            if (isDisabled) $(this).attr('disabled', true);
            else $(this).removeAttr('disabled');
        });
    }
    function enableInputs(selector) {
        disableInputs(selector, false);
    }
    renumber('.mightydev-repeat');
    disableInputs('.mightydev-repeat__template');
    $('.mightydev-repeat__template').hide();
    $('.mightydev-repeat').on('click', '.mightydev-repeat__add', function(e){
        e.preventDefault();
        createItem(this);
        renumber(this);
    });
    $('.mightydev-repeat').on('click', '.mightydev-repeat__remove', function(e){
        e.preventDefault();
        // TODO this text line needs translation
        if (confirm('This action can not be undone, are you sure?')) {
            var container_el = $(this).closest('.mightydev-repeat');
            $(this).closest('.mightydev-repeat__item').remove();
            renumber(container_el);
        }
    });
})(jQuery);
