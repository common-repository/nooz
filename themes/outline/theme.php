<?php

add_filter( 'nooz_shortcode', 'nooz_outline_view_content', 10, 2 );
function nooz_outline_view_content( $output, $data ) {
    ob_start();
    include trailingslashit( dirname( __FILE__ ) ) . 'view.php';
    return ob_get_clean();
}

add_filter( 'nooz_release', 'nooz_outline_release_content', 5, 2 );
if ( ! function_exists( 'nooz_outline_release_content' ) ) {
    function nooz_outline_release_content( $output, $data ) {
        return nooz_get_template( 'release', $data );
    }
}

// TODO: rename $item to $data
add_filter( 'nooz_post_content', 'nooz_outline_post_content', 10, 2 );
function nooz_outline_post_content( $output, $item ) {
    ob_start();
    include trailingslashit( dirname( __FILE__ ) ) . 'post.php';
    return ob_get_clean();
}

// TODO: action/filter order need to be thought out better instead of using priority 11
// priority 11 puts styles after js_composer styles
add_action( 'wp_enqueue_scripts', 'nooz_outline_styles_and_scripts' );
function nooz_outline_styles_and_scripts() {
    $theme_css_file = 'css/theme.css';
    wp_enqueue_style( 'nooz-outline', plugins_url( $theme_css_file, __FILE__ ), array(), filemtime( trailingslashit( dirname( __FILE__ ) ) . $theme_css_file ) );
    $theme_js_file = 'js/front.js';
    wp_enqueue_script( 'nooz-outline', plugins_url( $theme_js_file, __FILE__ ), array(), filemtime( trailingslashit( dirname( __FILE__ ) ) . $theme_js_file ), TRUE );
}

/**
 * Load this css file only if javascript is enabled.
 */
add_action( 'wp_head', 'nooz_outline_load_css_with_javascript' );
function nooz_outline_load_css_with_javascript() {
    ?><script>
        (function(){
            var id = 'nooz-outline-theme-js-css';
            if ( ! document.getElementById(id)) {
                var link = document.createElement('link');
                link.href = '<?php echo plugins_url( 'css/theme-js.css', __FILE__ ); ?>';
                link.id = id;
                link.type = 'text/css';
                link.rel = 'stylesheet';
                link.media = 'all';
                document.getElementsByTagName('head')[0].appendChild(link);
            }
        })();
    </script><?php
}
