<?php if ( ! empty( $data['title'] ) ) { ?>
    <div class="nooz-post__title">
        <h3 class="nooz-post__heading"><?php echo wp_kses_post( $data['title'] ); ?></h3>
    </div>
<?php } ?>
