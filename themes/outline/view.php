<div
    <?php if ( $data['id'] ) echo sprintf( ' id="%s"', esc_attr( $data['id'] ) ); ?>
    class="<?php echo esc_attr( trim( 'nooz-view ' . $data['css_class'] ) ); ?> nooz-view--<?php echo esc_attr( $data['active_category'] ); ?>"
    data-paged="<?php echo esc_attr( $data['paged'] ); ?>"
    data-max-num-pages="<?php echo esc_attr( $data['max_num_pages'] ); ?>">
    <div class="nooz-view__body     nooz-wrapper nooz-posts__wrapper">
        <?php //include dirname( __FILE__ ) . '/heading.php'; ?>
        <?php include dirname( __FILE__ ) . '/taxonomies.php'; ?>
        <?php include dirname( __FILE__ ) . '/posts.php'; ?>
        <?php include dirname( __FILE__ ) . '/pagination.php'; ?>
    </div>
</div>
