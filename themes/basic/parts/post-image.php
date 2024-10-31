<?php if ( ! empty( $data['featured_image_url'] ) ) { ?>
    <p class="nooz-image nooz-post__preview">
        <a href="<?php echo esc_url( $data['link_url'] ); ?>"<?php if ( ! empty( $data['link_target'] ) ) { ?> target="<?php echo esc_attr( $data['link_target'] ); ?>"<?php } ?>>
            <img class="nooz-post__preview-image" src="<?php echo esc_url( $data['featured_image_url'] ); ?>" alt="">
        </a>
    </p>
<?php } ?>
