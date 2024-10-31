<?php if ( $data['pagination']['previous_posts_link_url'] || $data['pagination']['next_posts_link_url'] ) { ?>
    <div class="nooz-pagination">
        <?php if ( $data['pagination']['previous_posts_link_url'] ) { ?>
            <div class="nooz-pagination__nav-prev nooz-pagination__nav">
                <a class="nooz-pagination__nav-prev-link nooz-pagination__nav-link" href="<?php echo esc_url( $data['pagination']['previous_posts_link_url'] ); ?>"><?php echo esc_html( $data['pagination']['previous_posts_link_text'] ); ?></a>
            </div>
        <?php } ?>
        <?php if ( $data['pagination']['next_posts_link_url'] ) { ?>
            <div class="nooz-pagination__nav-next nooz-pagination__nav">
                <a class="nooz-pagination__nav-next-link nooz-pagination__nav-link" href="<?php echo esc_url( $data['pagination']['next_posts_link_url'] ); ?>"><?php echo esc_html( $data['pagination']['next_posts_link_text'] ); ?></a>
            </div>
        <?php } ?>
    </div>
<?php } ?>
