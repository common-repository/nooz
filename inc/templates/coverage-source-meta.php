<div class="nooz-meta-box__body">
    <div class="nooz-meta-box-field-group nooz-meta-box-field-group--link">
        <div class="nooz-meta-box-field nooz-meta-box-field--link-url nooz-meta-box-field--text">
            <?php
                $field_name = '_mdnooz_link_url';
                $field_value = get_post_meta( $post->ID, $field_name, TRUE );
            ?>
            <label class="nooz-meta-box-field__label" for="<?php echo esc_attr( $field_name ); ?>"><?php _e( 'URL', 'mdnooz'  ); ?></label>
            <input class="nooz-meta-box-field__input nooz-meta-box-field__input--text" type="text" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>">
            <p class="nooz-meta-box-field__description"><?php _e( 'The URL of the news coverage.', 'mdnooz' ); ?></p>
        </div>
        <div class="nooz-meta-box-field nooz-meta-box-field--link-target nooz-meta-box-field--checkbox">
            <?php
                $field_name = '_mdnooz_link_target';
                $field_value = metadata_exists( 'post', $post->ID, $field_name ) ? get_post_meta( $post->ID, $field_name, TRUE ) : '_blank';
            ?>
            <input class="nooz-meta-box-field__input nooz-meta-box-field__input--checkbox" type="checkbox" <?php checked( $field_value, '_blank' ); ?> name="<?php echo esc_attr( $field_name ); ?>" value="_blank">
            <label class="nooz-meta-box-field__label nooz-meta-box-field__label--inline" for="<?php echo esc_attr( $field_name ); ?>"><?php _e( 'Open link in a new tab', 'mdnooz' ); ?></label>
        </div>
    </div>
    <div class="nooz-meta-box-field nooz-meta-box-field--text">
        <?php
            $field_name = '_mdnooz_source';
            $field_value = get_post_meta( $post->ID, $field_name, TRUE );
        ?>
        <label class="nooz-meta-box-field__label"><?php _e( 'Source name', 'mdnooz'  ); ?></label>
        <input class="nooz-meta-box-field__input nooz-meta-box-field__input--text" type="text" id="<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>">
        <p class="nooz-meta-box-field__description"><?php _e( 'The name of the source (e.g. Mashable, PR Newswire, CNN).', 'mdnooz' ); ?></p>
    </div>
</div>
