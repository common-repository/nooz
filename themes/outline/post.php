<?php
    $css_class = array( 'nooz-post' );
    if ( ! empty( $data['post_type'] ) ) {
        array_push( $css_class, 'nooz-post--' . $data['post_type'] );
    }
    if ( ! empty( $data['featured_image_url'] ) ) {
        array_push( $css_class, 'has-preview' );
    }
    if ( ! empty( $data['excerpt'] ) ) {
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
<div id="nooz-post-<?php echo esc_attr( $data['post_id'] ); ?>" class="<?php echo esc_attr( $css_class ); ?>" data-nooz-href="<?php echo esc_url( $data['link_url'] ); ?>" data-nooz-target="<?php echo esc_attr( $data['link_target'] ); ?>">
    <a class="nooz-post__link" href="<?php echo esc_url( $data['link_url'] ); ?>" target="<?php echo esc_attr( $data['link_target'] ); ?>">
        <?php echo nooz_get_template( 'post-preview', 'parts/post-preview.php', $data ); ?>
        <div class="nooz-post__body">
            <?php echo nooz_get_template( 'post-source', 'parts/post-source.php', $data ); ?>
            <?php echo nooz_get_template( 'post-meta', 'parts/post-meta.php', $data ); ?>
            <?php echo nooz_get_template( 'post-title', 'parts/post-title.php', $data ); ?>
            <?php echo nooz_get_template( 'post-date', 'parts/post-date.php', $data ); ?>
            <?php echo nooz_get_template( 'post-excerpt', 'parts/post-excerpt.php', $data ); ?>
        </div>
        <?php echo nooz_get_template( 'post-action', 'parts/post-action.php', $data ); ?>
    </a>
    <script>(function($){
        var el = $('#nooz-post-<?php echo esc_js( $data['post_id'] ); ?>');
        if ($('.nooz-post__datetime:hidden', el).length) el.removeClass('has-datetime');
    })(jQuery);</script>
</div>
