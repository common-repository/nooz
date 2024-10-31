<?php

add_filter( 'nooz_shortcode_data', 'nooz_basic_display_group', 10, 2 );
if ( ! function_exists( 'nooz_basic_display_group' ) ) {
    function nooz_basic_display_group( $data, $atts ) {
        $data['groups'] = array();
        if ( ! empty( $data['posts'] ) ) {
            foreach( $data['posts'] as $post_data ) {
                if ( 'group' == $atts['display'] ) {
                    $year = date( 'Y', $post_data['timestamp'] );
                    if ( ! isset( $data['groups'][$year] ) ) {
                        $data['groups'][$year] = array(
                            'title' => $year,
                            'posts' => array(),
                            // @deprecated
                            'items' => array(),
                        );
                    }
                    $data['groups'][$year]['posts'][] = $post_data;
                    // @deprecated
                    $data['groups'][$year]['items'][] = $post_data;
                }
            }
        }
        return $data;
    }
}

add_filter( 'nooz_shortcode', 'nooz_basic_shortcode_content', 5, 2 );
if ( ! function_exists( 'nooz_basic_shortcode_content' ) ) {
    function nooz_basic_shortcode_content( $output, $data ) {
        return nooz_get_template( 'view', $data );
    }
}

add_filter( 'nooz_release', 'nooz_basic_release_content', 5, 2 );
if ( ! function_exists( 'nooz_basic_release_content' ) ) {
    function nooz_basic_release_content( $output, $data ) {
        return nooz_get_template( 'release', $data );
    }
}

add_filter( 'nooz_post_content', 'nooz_basic_shortcode_post_content', 5, 2 );
if ( ! function_exists( 'nooz_basic_shortcode_post_content' ) ) {
    function nooz_basic_shortcode_post_content( $output, $data ) {
        return nooz_get_template( 'post', $data );
    }
}
add_action( 'wp_enqueue_scripts', 'nooz_basic_enqueue_styles_and_scripts' );
if ( ! function_exists( 'nooz_basic_enqueue_styles_and_scripts' ) ) {
    function nooz_basic_enqueue_styles_and_scripts() {
        $theme_css_file = 'css/theme.css';
        wp_enqueue_style( 'nooz-basic', plugins_url( $theme_css_file, __FILE__ ), array(), filemtime( trailingslashit( dirname( __FILE__ ) ) . $theme_css_file ) );
    }
}
