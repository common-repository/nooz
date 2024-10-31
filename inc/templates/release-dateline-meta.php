<div class="nooz-meta-box__body">
    <?php $dateline_format = get_option( 'mdnooz_release_dateline_format' ); ?>
    <div class="nooz-meta-box-field-dateline nooz-meta-box-field">
        <label class="nooz-meta-box-field__label"><?php _e( 'Dateline', 'mdnooz' ); ?> <?php if ( $dateline_format ) { ?><a class="nooz-release__edit-location-link" href="#"><?php _e( 'Edit', 'mdnooz' ); ?></a><?php } ?></label>
        <?php if ( $dateline_format ) { ?><div class="nooz-meta-box-field__preview"><?php echo $this->get_dateline( $post->ID ); ?></div><?php } ?>
        <div class="nooz-meta-box-field__description">
            <?php echo $dateline_format ? sprintf( '%s <code>%s</code>', __( 'The current dateline format is:', 'mdnooz' ), $dateline_format ) : __( 'The dateline format field is empty.', 'mdnooz' ); ?>
            <a href="<?php echo admin_url( 'admin.php?page=nooz&group=nooz_release' ); ?>" target="_blank"><?php _e( 'Edit dateline format', 'mdnooz' ); ?><span class="nooz-admin__link-icon dashicons dashicons-external"></span></a>
        </div>
    </div>
    <?php
        $field_name = '_mdnooz_release_location';
        $field_value = get_post_meta( $post->ID, $field_name, TRUE );
    ?>
    <div class="nooz-meta-box-field-group-location nooz-meta-box-field-group"<?php echo empty( $field_value ) ? ' style="display:none;"' : ''; ?>>
        <?php if ( stristr( $dateline_format, '{location}' ) ) { ?>
            <div class="nooz-meta-box-field-location nooz-meta-box-field">
                <label class="nooz-meta-box-field__label" for="<?php echo esc_attr( $field_name ); ?>"><?php _e( 'Location', 'mdnooz' ); ?> <a href="<?php echo admin_url( 'admin.php?page=nooz&group=nooz_release' ); ?>" target="_blank"><?php _e( 'Edit default location', 'mdnooz' ); ?><span class="nooz-admin__link-icon dashicons dashicons-external"></span></a></label>
                <input class="nooz-meta-box-field__input nooz-meta-box-field__input--text nooz-meta-box-field__input--location" type="text" id="<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>" placeholder="<?php echo esc_attr( get_option( 'mdnooz_release_location' ) ); ?>">
                <p class="nooz-meta-box-field__description">
                    <?php
                        /* translators: 1: Link to Dateline information */
                        printf( __( 'The location is part of the <a href="%1$s" target="_blank">dateline</a> which precedes the press release and helps to orient the reader (e.g. San Francisco, CA).', 'mdnooz' ), 'https://en.wikipedia.org/wiki/Dateline' );
                    ?>
                    <?php _e( 'Leave blank to use the default location.', 'mdnooz' ); ?>
                </p>
            </div>
        <?php } ?>
        <div class="nooz-meta-box-field-combine-dateline nooz-meta-box-field nooz-meta-box-field--checkbox">
            <?php
                $field_name = '_mdnooz_combine_dateline';
                $field_value = get_post_meta( $post->ID, $field_name, TRUE );
                $post_version = get_post_meta( $post->ID, '_mdnooz_version', TRUE );
                $is_post_version_gt_0_14_1 = empty( $post_version ) || version_compare( $post_version, '0.14.1', '>' );
                if ( empty( $field_value ) && $is_post_version_gt_0_14_1 ) $field_value = 'yes';
            ?>
            <input class="nooz-meta-box-field__input nooz-meta-box-field__input--checkbox nooz-meta-box-field__input--combine-dateline" type="checkbox" <?php checked( $field_value, 'yes' ); ?> id="<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="yes">
            <label class="nooz-meta-box-field__label" for="<?php echo esc_attr( $field_name ); ?>"><?php _e( 'Combine dateline with the first line of the press release', 'mdnooz' ); ?></label>
            <p class="nooz-meta-box-field__description"><?php _e( 'Otherwise, the dateline will be placed on a seperate line before the press release.', 'mdnooz' ); ?></p>
        </div>
        <div class="nooz-meta-box-field-use-dateline nooz-meta-box-field nooz-meta-box-field--checkbox">
            <?php
                $field_name = '_mdnooz_use_dateline';
                $field_value = get_post_meta( $post->ID, $field_name, TRUE );
            ?>
            <input class="nooz-meta-box-field-use-dateline__input nooz-meta-box-field__input nooz-meta-box-field__input--checkbox" type="checkbox" <?php checked( $field_value, 'no' ); ?> id="<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="no">
            <label class="nooz-meta-box-field__label" for="<?php echo esc_attr( $field_name ); ?>"><?php _e( 'Hide the dateline?', 'mdnooz' ); ?></label>
            <p class="nooz-meta-box-field__description"><?php _e( 'This will hide the dateline completely for this press release.', 'mdnooz' ); ?></p>
        </div>
    </div>
    <script>
        (function($){
            var mb = $('#nooz-meta-box__release-dateline');
            // hide-chrome, place after-title
            mb.addClass('nooz-meta-box nooz-meta-box--hide-chrome').appendTo('#nooz-meta-box__after-title');
            // visibly change dateline state
            $('.nooz-meta-box-field-use-dateline__input', mb).on('change', function(){
                $('.nooz-release__dateline', mb).css('text-decoration', this.checked ? 'line-through' : '');
            }).trigger('change');
        })(jQuery);
    </script>
</div>
