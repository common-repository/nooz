<?php if ( isset( $data['title'] ) ) { ?>
    <div class="nooz-heading">
        <h2 class="nooz-title"><?php echo wp_kses_post( $data['title'] ); ?></h2>
    </div>
<?php } ?>
