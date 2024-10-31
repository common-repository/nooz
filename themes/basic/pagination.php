<?php if ( ! empty( $data['pagination']['previous_posts_link_url'] ) || ! empty( $data['pagination']['next_posts_link_url'] ) ) { ?>
    <div class="nooz-pagination">
        <?php echo $data['pagination']['previous_posts_link_html']; ?>
        <?php echo $data['pagination']['next_posts_link_html']; ?>
    </div>
<?php } ?>
