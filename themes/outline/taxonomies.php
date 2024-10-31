<div class="nooz-taxonomies">
    <div class="nooz-taxonomy nooz-taxonomy--post-types">
        <h3 class="nooz-taxonomy__title">Post Types</h3>
        <ul class="nooz-taxonomy__terms">
            <?php foreach ( $data['post_types'] as $name => $item ) { ?>
                <?php
                    $css_class = array( 'nooz-taxonomy__term--' . $name );
                    if ( $data['active_category'] == $name ) array_push( $css_class, 'active' );
                    if ( ! empty( $item->featured_image_url ) ) array_push( $css_class, 'has-featured-image' );
                ?>
                <li class="nooz-taxonomy__term <?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
                    <a class="nooz-taxonomy__term-link" href="<?php echo esc_url( $item->link_url ); ?>" target="<?php echo esc_attr( $item->link_target ); ?>">
                        <span class="nooz-taxonomy__term-link-text"><?php echo esc_html( $item->title ); ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
