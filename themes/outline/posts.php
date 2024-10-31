<?php if ( ! empty( $data['posts'] ) ) { ?>
    <div class="nooz-posts nooz-posts--<?php echo esc_attr( $data['active_category'] ); ?>">
        <?php foreach( $data['posts'] as $item ) { ?>
            <?php echo nooz_get_template( 'post', $item ); ?>
        <?php } ?>
    </div>
<?php } ?>
