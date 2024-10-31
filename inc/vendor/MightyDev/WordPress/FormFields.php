<?php

namespace MightyDev\WordPress;

/**
 * Used to output form fields.
 *
 * When in the context of WordPress, both "class" and "label_for" options are
 * used by "do_settings_fields()" for TR element class and TR LABEL element.
 *
 * @version 1.0.0
 *
 * @see https://core.trac.wordpress.org/browser/trunk/src/wp-admin/includes/template.php
 */
class FormFields
{
    protected $data = NULL;

    protected $default_data = array(
        'base_class' => 'mightydev-field',
        'label' => NULL,
        'label_for' => NULL,
        'class' => NULL,
        'style' => NULL,
        'before_field' => NULL,
        'after_field' => NULL,
        'description' => NULL,
        'options' => array(),
        'dependency' => array(
            'name' => NULL,
            'value' => NULL,
            'not_empty' => NULL,
        ),
        'media' => array(
            'title' => NULL,
            'select_button' => NULL,
            'add_button' => NULL,
            'remove_button' => NULL,
            'error_message' => NULL,
        ),
        'id' => NULL,
        'name' => NULL,
        'value' => NULL,
        'checked' => NULL,
        'min' => NULL,
        'max' => NULL,
        'cols' => NULL,
        'rows' => NULL,
        'placeholder' => NULL,
    );

    protected function set_field_data( $data, $field_type ) {
        // isolates data when used in the context of "add_settings_field"
        if ( isset( $data['field_data'] ) ) $data = $data['field_data'];
        $data['field_type'] = $field_type;
        // set defaults keys
        $data = array_merge( $this->default_data, $data );
        $data['dependency'] = array_merge( $this->default_data['dependency'], $data['dependency'] );
        $data['media'] = array_merge( $this->default_data['media'], $data['media'] );
        foreach( $data['options'] as $i => $option ) {
            // set default for options
            $data['options'][$i] = array_merge( $this->default_data, $option );
        }
        $data['class'] = trim( sprintf( '%s %s', $data['base_class'], $data['class'] ) );
        $data['class'] = isset( $data['field_type'] ) ? trim( sprintf( '%s-%s %s', $data['base_class'], $data['field_type'], $data['class'] ) ) : $data['class'];
        $this->data = $data;
    }

    protected function get_field_props() {
        $props = '';
        $props .= $this->data['id'] ? sprintf( ' id="%s"', esc_attr( $this->data['id'] ) ) : NULL ;
        $props .= $this->data['class'] ? sprintf( ' class="%s"', esc_attr( $this->data['class'] ) ) : NULL ;
        $props .= $this->data['style'] ? sprintf( ' style="%s"', esc_attr( $this->data['style'] ) ) : NULL ;
        $props .= isset( $this->data['dependency']['name'] ) ? sprintf( ' data-dependency-name="%s"', esc_attr( trim( $this->data['dependency']['name'] ) ) ) : NULL ;
        $props .= isset( $this->data['dependency']['value'] ) ? sprintf( ' data-dependency-value="%s"', esc_attr( is_array( $this->data['dependency']['value'] ) ? implode( '@@@', $this->data['dependency']['value'] ) : $this->data['dependency']['value'] ) ) : NULL ;
        $props .= isset( $this->data['dependency']['not_empty'] ) ? sprintf( ' data-dependency-not-empty="%s"', esc_attr( $this->data['dependency']['not_empty'] ) ) : NULL ;
        $props .= isset( $this->data['media']['title'] ) ? sprintf( ' data-media-title="%s"', esc_attr( $this->data['media']['title'] ) ) : NULL ;
        $props .= isset( $this->data['media']['select_button'] ) ? sprintf( ' data-media-button="%s"', esc_attr( $this->data['media']['select_button'] ) ) : NULL ;
        return $props;
    }

    protected function get_input_props( $data = NULL ) {
        if ( is_null( $data ) ) $data = $this->data;
        $props = '';
        $props .= $data['min'] ? sprintf( ' min="%s"', $data['min'] ) : NULL ;
        $props .= $data['max'] ? sprintf( ' max="%s"', $data['max'] ) : NULL ;
        if ( isset( $data['field_type'] ) && 'textarea' == $data['field_type'] ) {
            $props .= sprintf( ' cols="%s"', $data['cols'] ?: '50' );
            $props .= sprintf( ' rows="%s"', $data['rows'] ?: '10' );
        }
        $props .= $data['placeholder'] ? sprintf( ' placeholder="%s"', esc_attr( $data['placeholder'] ) ) : NULL ;
        $props .= $data['checked'] ? ' checked="checked"' : NULL ;
        return $props;
    }

    protected function get_label( $label = NULL, $for = NULL ) {
        if ( is_null( $label ) ) $label = $this->data['label'];
        if ( is_null( $for ) ) $for = $this->data['label_for'];
        return $label ? sprintf( '<label class="%s__label" for="%s">%s</label>', esc_attr( $this->data['base_class'] ), esc_attr( $for ), $label ) : NULL ;
    }

    protected function get_description( $value = NULL ) {
        if ( is_null( $value ) ) $value = $this->data['description'];
        // .description is a wp css class
        return $value ? sprintf( '<p class="description %s__description">%s</p>', esc_attr( $this->data['base_class'] ), $value ) : NULL ;
    }

    protected function get_before_field( $value = NULL ) {
        if ( is_null( $value ) ) $value = $this->data['before_field'];
        return $value ? sprintf( '<span class="%s__before-field">%s</span>', esc_attr( $this->data['base_class'] ), $value ) : NULL ;
    }

    protected function get_after_field( $value = NULL ) {
        if ( is_null( $value ) ) $value = $this->data['after_field'];
        return $value ? sprintf( '<span class="%s__after-field">%s</span>', esc_attr( $this->data['base_class'] ), $value ) : NULL ;
    }

    public function media( $data ) {
        echo $this->get_media( $data );
    }

    public function get_media( $data ) {
        $this->set_field_data( $data, 'media' );
        ob_start();
        ?><div<?php echo $this->get_field_props(); ?>>
            <div class="mightydev-field-media__preview" style="display:none;"></div>
            <div class="mightydev-field-media__body">
                <p class="mightydev-field-media__error-message" style="display:none;"><?php echo $this->data['media']['error_message']; ?></p>
                <?php echo $this->get_before_field(); ?>
                <input class="mightydev-field-media__input--text mightydev-field-media__input" type="text" id="<?php echo esc_attr( $this->data['name'] ); ?>" name="<?php echo esc_attr( $this->data['name'] ); ?>" value="<?php echo esc_attr( $this->data['value'] ); ?>"<?php echo $this->get_input_props(); ?>>
                <a<?php if ( $this->data['value'] ) { ?> style="display:none;"<?php } ?> href="#" class="button mightydev-field-media__add"><?php echo $this->data['media']['add_button']; ?></a>
                <a<?php if ( ! $this->data['value'] ) { ?> style="display:none;"<?php } ?> href="#" class="button mightydev-field-media__remove"><?php echo $this->data['media']['remove_button']; ?></a>
                <?php echo $this->get_after_field(); ?>
                <?php echo $this->get_description(); ?>
            </div>
        </div><?php
        return ob_get_clean();
    }

    public function textarea( $data ) {
        echo $this->get_textarea( $data );
    }

    public function get_textarea( $data ) {
        $this->set_field_data( $data, 'textarea' );
        ob_start();
        ?><div<?php echo $this->get_field_props(); ?>>
            <?php echo $this->get_before_field(); ?>
            <textarea class="large-text <?php echo esc_attr( $this->data['base_class'] ); ?>__input <?php echo esc_attr( $this->data['base_class'] ); ?>__input--textarea" id="<?php echo esc_attr( $this->data['name'] ); ?>" name="<?php echo esc_attr( $this->data['name'] ); ?>"<?php echo $this->get_input_props(); ?>><?php echo esc_textarea( $this->data['value'] ); ?></textarea>
            <?php echo $this->get_after_field(); ?>
            <?php echo $this->get_description(); ?>
        </div><?php
        return ob_get_clean();
    }

    public function radio( $data ) {
        echo $this->get_radio( $data );
    }

    public function get_radio( $data ) {
        $this->set_field_data( $data, 'radio' );
        ob_start();
        ?><div<?php echo $this->get_field_props(); ?>>
            <?php echo $this->get_before_field(); ?>
            <?php if ( $this->data['options'] ) { ?>
                <ul class="mightydev-field-radio__options">
                    <?php foreach( $this->data['options'] as $option ) {
                        $option_name = is_null( $option['name'] ) ? $this->data['name'] : $option['name'] ;
                        $option['checked'] = $option['value'] == $this->data['value'];
                    ?>
                        <li class="mightydev-field-radio__option">
                            <?php if ( $option['before_field'] ) { ?><span class="mightydev-field-radio__option-before-field mightydev-field__option-before-field"><?php echo $option['before_field']; ?></span><?php } ?>
                            <input class="mightydev-field__input mightydev-field__input--radio" type="radio" name="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $option['value'] ); ?>"<?php echo $this->get_input_props( $option ); ?>>
                            <?php if ( $option['after_field'] ) { ?><span class="mightydev-field-radio__option-after-field mightydev-field__option-after-field"><?php echo $option['after_field']; ?></span><?php } ?>
                            <?php if ( $option['description'] ) { ?><p class="mightydev-field-radio__option-description mightydev-field__option-description"><?php echo $option['description']; ?></p><?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
            <?php echo $this->get_after_field(); ?>
            <?php echo $this->get_description(); ?>
        </div><?php
        return ob_get_clean();
    }

    public function checkbox( $data ) {
        echo $this->get_checkbox( $data );
    }

    public function get_checkbox( $data ) {
        $this->set_field_data( $data, 'checkbox' );
        $value = is_null( $this->data['value'] ) ? '1' : $this->data['value'] ;
        ob_start();
        ?><div<?php echo $this->get_field_props(); ?>>
            <?php if ( $this->data['before_field'] ) { ?><label class="<?php echo esc_attr( $this->data['base_class'] ); ?>__before-field" for="<?php echo esc_attr( $this->data['name'] ); ?>"><?php echo $this->data['before_field']; ?></label><?php } ?>
            <input class="<?php echo esc_attr( $this->data['base_class'] ); ?>__input <?php echo esc_attr( $this->data['base_class'] ); ?>__input--checkbox" type="checkbox" id="<?php echo esc_attr( $this->data['name'] ); ?>" name="<?php echo esc_attr( $this->data['name'] ); ?>" value="<?php echo esc_attr( $value ); ?>"<?php echo $this->get_input_props(); ?>>
            <?php if ( $this->data['after_field'] ) { ?><label class="<?php echo esc_attr( $this->data['base_class'] ); ?>__after-field" for="<?php echo esc_attr( $this->data['name'] ); ?>"><?php echo $this->data['after_field']; ?></label><?php } ?>
            <?php echo $this->get_description(); ?>
        </div><?php
        return ob_get_clean();
    }

    public function number( $data ) {
        echo $this->get_number( $data );
    }

    public function get_number( $data ) {
        $this->set_field_data( $data, 'number' );
        ob_start();
        ?><div<?php echo $this->get_field_props(); ?>>
            <?php echo $this->get_before_field(); ?>
            <input class="<?php echo esc_attr( $this->data['base_class'] ); ?>__input <?php echo esc_attr( $this->data['base_class'] ); ?>__input--number" type="number" id="<?php echo esc_attr( $this->data['name'] ); ?>" name="<?php echo esc_attr( $this->data['name'] ); ?>" value="<?php echo esc_attr( $this->data['value'] ); ?>"<?php echo $this->get_input_props(); ?>>
            <?php echo $this->get_after_field(); ?>
            <?php echo $this->get_description(); ?>
        </div><?php
        return ob_get_clean();
    }

    public function button( $data ) {
        echo $this->get_button( $data );
    }

    public function get_button( $data ) {
        $this->set_field_data( $data, 'button' );
        ob_start();
        ?><div<?php echo $this->get_field_props(); ?>>
            <?php echo $this->get_before_field(); ?>
            <a href="<?php echo esc_url( $this->data['href'] ); ?>" class="button <?php echo esc_attr( $this->data['base_class'] ); ?>__button"><?php echo esc_html( $this->data['value'] ); ?></a>
            <?php echo $this->get_after_field(); ?>
            <?php echo $this->get_description(); ?>
        </div><?php
        return ob_get_clean();
    }

    public function file_upload( $data ) {
        echo $this->get_file_upload( $data );
    }

    public function get_file_upload( $data ) {
        $this->set_field_data( $data, 'file_upload' );
        ob_start();
        ?><div<?php echo $this->get_field_props(); ?>>
            <?php echo $this->get_label(); ?>
            <?php echo $this->get_before_field(); ?>
            <label class="<?php echo esc_attr( $this->data['base_class'] ); ?>__label" for="<?php echo esc_attr( $this->data['name'] ); ?>">
                <input class="<?php echo esc_attr( $this->data['base_class'] ); ?>__input <?php echo esc_attr( $this->data['base_class'] ); ?>__input--file" type="file" id="<?php echo esc_attr( $this->data['name'] ); ?>" name="<?php echo esc_attr( $this->data['name'] ); ?>" value="<?php echo esc_attr( $this->data['value'] ); ?>"<?php echo $this->get_input_props(); ?>>
                <span class="button"><?php echo esc_html( $this->data['value'] ); ?></span>
                <span class="md-filename"></span>
                <input type="submit" value="<?php echo esc_attr( $this->data['submit'] ); ?>" class="button button-primary" style="display:none;" name="submit">
            </label>
            <?php echo $this->get_after_field(); ?>
            <?php echo $this->get_description(); ?>
        </div><?php
        return ob_get_clean();
    }

    public function text( $data ) {
        echo $this->get_text( $data );
    }

    public function get_text( $data ) {
        $this->set_field_data( $data, 'text' );
        ob_start();
        ?><div<?php echo $this->get_field_props(); ?>>
            <?php echo $this->get_label(); ?>
            <?php echo $this->get_before_field(); ?>
            <?php if ( $this->data['options'] ) { ?>
                <div class="<?php echo esc_attr( $this->data['base_class'] ); ?>__options">
                    <?php foreach( $this->data['options'] as $option ) { ?>
                        <input class="<?php echo esc_attr( $this->data['base_class'] ); ?>__input <?php echo esc_attr( $this->data['base_class'] ); ?>__input--text" type="text" id="<?php echo esc_attr( $option['name'] ); ?>" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option['value'] ); ?>"<?php echo $this->get_input_props( $option ); ?>>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <input class="<?php echo esc_attr( $this->data['base_class'] ); ?>__input <?php echo esc_attr( $this->data['base_class'] ); ?>__input--text" type="text" id="<?php echo esc_attr( $this->data['name'] ); ?>" name="<?php echo esc_attr( $this->data['name'] ); ?>" value="<?php echo esc_attr( $this->data['value'] ); ?>"<?php echo $this->get_input_props(); ?>>
            <?php } ?>
            <?php echo $this->get_after_field(); ?>
            <?php echo $this->get_description(); ?>
        </div><?php
        return ob_get_clean();
    }

    public function setup() {
        add_action( 'admin_footer', array( $this, 'scripts' ) );
    }

    public function scripts() {
        ?><script>
            /**
             * Field dependency functionality.
             */
            (function($){
                function isValid(elem, vals, not_empty) {
                    if (not_empty) {
                        if ('checkbox' === elem.type.toLowerCase()) {
                            if (elem.checked) return true;
                        } else if (elem.value) return true;
                    } else {
                        if ('checkbox' === elem.type.toLowerCase()) {
                            if (-1 !== vals.indexOf(elem.checked ? elem.value : '')) return true;
                        } else if (-1 !== vals.indexOf(elem.value)) return true;
                    }
                    return false;
                }
                var elems = document.querySelectorAll('[data-dependency-name]'), i;
                elems.forEach(function(elem){
                    var dep = elem.getAttribute('data-dependency-name');
                    if (!dep) return;
                    var not_empty = elem.getAttribute('data-dependency-not-empty') ? true : false;
                    var vals = ("" + elem.getAttribute('data-dependency-value')).split('@@@');
                    var depElem = document.getElementById(dep);
                    function listener(e) {
                        if (isValid(depElem, vals, not_empty)) $(elem).show('slow');
                        else $(elem).hide('slow');
                    }
                    if (isValid(depElem, vals, not_empty)) $(elem).show();
                    depElem.addEventListener('change', listener, false);
                    depElem.addEventListener('keyup', listener, false);
                });
            })(jQuery);

            (function($){
                /**
                 * Media selection field.
                 */
                function mediaExists(url, cb) {
                    if (!url) {
                        cb(false);
                        return;
                    }
                    var xhr = new XMLHttpRequest();
                    xhr.open('HEAD', url);
                    xhr.onload = function() {
                        cb(4 === xhr.readyState && 200 === xhr.status);
                    }
                    xhr.send();
                }
                var mediaFieldSelector = '.mightydev-field-media';
                var previewSelector = '.mightydev-field-media__preview';
                var errorMessageSelector = '.mightydev-field-media__error-message';
                var addButtonSelector = '.mightydev-field-media__add';
                var removeButtonSelector = '.mightydev-field-media__remove';
                var inputSelector = '.mightydev-field-media__input';
                $(mediaFieldSelector).each(function(){
                    var container = $(this);
                    var mediaUrl = $(inputSelector, container).val();
                    if (!mediaUrl) return;
                    mediaExists(mediaUrl, function(exists){
                        if (exists) {
                            $(previewSelector, container).empty().append('<img src="'+ mediaUrl +'">').show();
                        } else {
                            $(errorMessageSelector, container).show();
                        }
                    });
                });
                $('body').on('change', inputSelector, function(){
                    var container = $(this).closest(mediaFieldSelector);
                    var mediaUrl = $(this).val();
                    mediaExists(mediaUrl, function(exists){
                        if (exists) {
                            $(previewSelector, container).empty().append('<img src="'+ mediaUrl +'">').show();
                            $(errorMessageSelector, container).hide();
                            $(addButtonSelector, container).hide();
                            $(removeButtonSelector, container).show();
                        } else {
                            $(previewSelector, container).empty().hide();
                            if (mediaUrl) {
                                $(errorMessageSelector, container).show();
                                $(addButtonSelector, container).hide();
                                $(removeButtonSelector, container).show();
                            } else {
                                $(errorMessageSelector, container).hide();
                                $(addButtonSelector, container).show();
                                $(removeButtonSelector, container).hide();
                            }
                        }
                    });
                });
                $('body').on('click', addButtonSelector, function(e){
                    e.preventDefault();
                    var container = $(this).closest(mediaFieldSelector);
                    var mediaFrame = container.data('mediaFrame');
                    if (mediaFrame) {
                        mediaFrame.open();
                        return;
                    }
                    mediaFrame = wp.media({
                        title: container.data('mediaTitle'),
                        button: {text: container.data('mediaButton')},
                        multiple: false
                    });
                    mediaFrame.on('select', function(){
                        var attachment = mediaFrame.state().get('selection').first().toJSON();
                        $(inputSelector, container).val(attachment.url);
                        $(previewSelector, container).empty().append('<img src="'+ attachment.url +'">').show();
                        $(addButtonSelector, container).hide();
                        $(removeButtonSelector, container).show();
                    });
                    container.data('mediaFrame', mediaFrame);
                    mediaFrame.open();
                });
                $('body').on('click', removeButtonSelector, function(e){
                    e.preventDefault();
                    var container = $(this).closest(mediaFieldSelector);
                    $(inputSelector, container).val('');
                    $(previewSelector, container).empty().hide();
                    $(errorMessageSelector, container).hide();
                    $(removeButtonSelector, container).hide();
                    $(addButtonSelector, container).show();
                });
            })(jQuery);
        </script><?php
    }
}
