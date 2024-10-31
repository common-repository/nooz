<?php
    $css_class = array(
        'nooz-post',
        'nooz-item',
        'nooz-item-' . ( $data['i']%2 ? 'odd' : 'even' ),
    );
    if ( isset( $data['priority'] ) && 'pinned' == $data['priority'] ) {
        array_push( $css_class, 'nooz-item-pinned' );
    }
    if ( ! empty( $data['post_type'] ) ) {
        array_push( $css_class, 'nooz-post--' . $data['post_type'] );
    }
    if ( ! empty( $data['featured_image_url'] ) ) {
        array_push( $css_class, 'has-preview' );
    }
    if ( ! empty( $data['excerpt'] ) ) {
        array_push( $css_class, 'nooz-item-with-excerpt' );
        array_push( $css_class, 'has-excerpt' );
    }
    if ( ! empty( $data['date'] ) ) {
        array_push( $css_class, 'has-datetime' );
    }
    if ( ! empty( $data['link_text'] ) ) {
        array_push( $css_class, 'has-action' );
    }
    $css_class = implode( ' ', $css_class );
?>
<li id="nooz-post-<?php echo esc_attr( $data['post_id'] ); ?>" class="<?php echo esc_attr( $css_class ); ?>">
    <?php foreach( $data['element_order'] as $element_name ) {
        $file = sprintf( dirname( __FILE__ ) . '/parts/post-%s.php', $element_name );
        if ( file_exists( $file ) ) {
            echo nooz_get_template( 'post-' . $element_name, $file, $data );
        }
    } ?>
</li>
