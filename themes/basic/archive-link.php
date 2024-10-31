<?php if ( ! empty( $data['archive_link']['link_url'] ) ) { ?>
    <p class="nooz-archive-link"><a href="<?php echo esc_url( $data['archive_link']['link_url'] ); ?>"><?php echo esc_html( $data['archive_link']['link_text'] ); ?></a></p>
<?php } ?>
