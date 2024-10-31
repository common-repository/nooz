<?php if ( ! empty( $data['date'] ) ) { ?>
    <div class="nooz-post__datetime">
        <time class="nooz-post__datetime-text" datetime="<?php echo esc_attr( date( 'c', $data['timestamp'] ) ); ?>"><?php echo esc_html( $data['date'] ); ?></time>
    </div>
<?php } ?>
