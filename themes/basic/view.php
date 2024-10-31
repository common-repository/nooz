<?php if ( count( $data['groups'] ) || count( $data['posts'] ) ) { ?>
    <div class="nooz-<?php echo esc_attr( $data['type'] ); ?>-list-wrapper nooz-list-wrapper <?php echo esc_attr( $data['css_class'] ); ?> nooz-view">
        <?php echo nooz_get_template( 'posts', dirname( __FILE__ ) . '/posts.php', $data ); ?>
        <?php echo nooz_get_template( 'pagination', dirname( __FILE__ ) . '/pagination.php', $data ); ?>
        <?php echo nooz_get_template( 'archive-link', dirname( __FILE__ ) . '/archive-link.php', $data ); ?>
    </div>
<?php } ?>
