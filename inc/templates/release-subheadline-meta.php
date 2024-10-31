<div class="nooz-meta-box__body">
    <div class="nooz-meta-box-field">
        <?php
            $field_name = '_mdnooz_subheadline';
            $field_value = get_post_meta( $post->ID, $field_name, TRUE );
        ?>
        <input class="nooz-meta-box-field__input--subheadline nooz-meta-box-field__input--text nooz-meta-box-field__input" type="text" id="<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>" placeholder="<?php _e( 'Enter subheadline here', 'mdnooz' ) ; ?>">
        <p class="nooz-meta-box-field__description"><?php _e( 'An optional subheadline.', 'mdnooz' ) ; ?></p>
    </div>
    <script>
        (function($){
            // hide-chrome, place after-title
            $('#nooz-meta-box__release-subheadline')
                .addClass('nooz-meta-box nooz-meta-box--hide-chrome')
                .prependTo('#nooz-meta-box__after-title');
        })(jQuery);
    </script>
</div>
