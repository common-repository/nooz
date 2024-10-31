<p class="nooz-title nooz-link nooz-post__title">
    <a class="nooz-post__heading" href="<?php echo esc_url( $data['link_url'] ); ?>"<?php if ( ! empty( $data['link_target'] ) ) { ?> target="<?php echo esc_attr( $data['link_target'] ); ?>"<?php } ?>><?php echo wp_kses_post( $data['title'] ); ?></a>
</p>
