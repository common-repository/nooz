<?php

namespace MightyDev\Nooz;

class Base extends Core
{
    protected $plugin_file;

    public function plugin_file( $plugin_file = NULL ) {
        if ( ! is_null( $plugin_file ) ) {
            $this->plugin_file = $plugin_file;
        }
        return $this->plugin_file;
    }

    public function name( $str = NULL) {
        $option = 'mdnooz_plugin_name';
        $val = get_option( $option );
        if ( is_null( $str ) || $str === $val ) return $val;
        update_option( $option, $str );
    }

    public function version( $str = NULL ) {
        $option = 'mdnooz_plugin_version';
        $val = get_option( $option );
        if ( is_null( $str ) || $str === $val ) return $val;
        update_option( 'mdnooz_plugin_previous_version', $val );
        update_option( $option, $str );
    }
}
