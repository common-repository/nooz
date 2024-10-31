<?php if ( ! empty( $data['more_link'] ) ) { ?>
    <p class="nooz-more-link nooz-post__action">
        <a href="<?php echo esc_url( $data['link'] ); ?>"<?php if ( ! empty( $data['target'] ) ) { ?> target="<?php echo esc_attr( $data['target'] ); ?>"<?php } ?>><?php echo esc_html( $data['more_link'] ); ?></a>
    </p>
<?php } ?>
