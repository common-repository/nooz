<?php if ( count( $data['groups'] ) ) { ?>
    <?php foreach( $data['groups'] as $group ) { ?>
        <div class="nooz-group-container">
            <h3 class="nooz-group-title nooz-group"><?php echo $group['title']; ?></h3>
            <?php if ( count( $group['posts'] ) ) { ?>
                <ul class="nooz-list nooz-<?php echo esc_attr( $data['type'] ); ?> nooz-posts nooz-posts--<?php echo esc_attr( $data['active_category'] ); ?>">
                    <?php $i=0; foreach( $group['posts'] as $item ) { $i++; $item['i'] = $i; ?>
                        <?php echo nooz_get_template( 'post', dirname( __FILE__ ) . '/post.php', $item ); ?>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    <?php } ?>
<?php } else { ?>
    <ul class="nooz-list nooz-<?php echo esc_attr( $data['type'] ); ?> nooz-posts nooz-posts--<?php echo esc_attr( $data['active_category'] ); ?>">
        <?php $i=0; foreach( $data['posts'] as $item ) { $i++; $item['i'] = $i; ?>
            <?php echo nooz_get_template( 'post', dirname( __FILE__ ) . '/post.php', $item ); ?>
        <?php } ?>
    </ul>
<?php } ?>
