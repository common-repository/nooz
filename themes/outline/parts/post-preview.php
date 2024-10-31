<?php if ( ! empty( $data['featured_image_url'] ) ) { ?>
    <div class="nooz-post__preview">
        <div class="nooz-post__preview-background" style="background-image: url(<?php echo esc_url( $data['featured_image_url'] ); ?>)">
            <img class="nooz-post__preview-image" src="<?php echo esc_url( $data['featured_image_url'] ); ?>" alt="<?php echo esc_attr( $data['title'] ); ?>">
        </div>
    </div>
<?php } ?>
