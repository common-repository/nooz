<?php

namespace MightyDev\WordPress;

/**
 * Checks wordpress timezone_string and gmt_offset and returns a timezone
 * string suitable for \DateTimeZone.
 *
 * @see https://wordpress.stackexchange.com/questions/8400/how-to-get-wordpress-time-zone-setting
 *
 * @return string Timezone string
 */
if ( ! function_exists( __NAMESPACE__ . '\get_timezone' ) ) {
    function get_timezone() {
        $timezone_string = get_option( 'timezone_string' );
        if ( ! empty( $timezone_string ) ) return $timezone_string;
        $offset  = get_option( 'gmt_offset' );
        $hours   = (int) $offset;
        $minutes = abs( ( $offset - (int) $offset ) * 60 );
        $offset  = sprintf( '%+03d:%02d', $hours, $minutes );
        return $offset;
    }
}

if ( ! function_exists( __NAMESPACE__ . '\autoload' ) ) {
    function autoload( $map ) {
        \spl_autoload_register( function ( $class ) use ( $map ) {
            $class = ltrim( $class, '\\' );
            foreach ( $map as $current ) {
                list( $prefix, $base_dir ) = $current;
                $len = strlen( $prefix );
                if ( 0 !== strncmp( $prefix, $class, $len ) ) continue;
                // psr4
                $relative_class = substr( $class, $len );
                $file = $base_dir . str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';
                if ( file_exists( $file ) ) {
                    require_once( $file );
                    return TRUE;
                }
                // wp
                $class_parts = explode( '\\', $class );
                $class_name = $class_parts[ count( $class_parts ) -  1 ];
                $class_name_parts = array_filter( preg_split( '/((?<=[a-z])(?=[A-Z])|(?=[A-Z][a-z]))/', $class_name ) );
                $file_name = implode( '-', $class_name_parts );
                $file_name = preg_replace( array( '/_+/', '/-+/' ), '-', $file_name );
                $file = $base_dir . '/class-' . strtolower( $file_name ) . '.php';
                if ( file_exists( $file ) ) {
                    require_once( $file );
                    return TRUE;
                }
            }
            return FALSE;
        } );
    }
}

/**
 * Removes settings field data.
 *
 * This is a helper function to remove a settings field, the option will still
 * need to be unregistered with "unregister_setting()".
 *
 * @see https://core.trac.wordpress.org/browser/tags/5.2.2/src/wp-admin/includes/template.php#L1504
 *
 * @return array|bool Settings field data that was removed, or FALSE if nothing found to remove.
 */
if ( ! function_exists( __NAMESPACE__ . '\remove_settings_field' ) ) {
    function remove_settings_field( $id, $page, $section = 'default' ) {
        global $wp_settings_fields;
        if ( ! isset( $wp_settings_fields[$page][$section][$id] ) ) return FALSE;
        $field_data = $wp_settings_fields[$page][$section][$id];
        unset( $wp_settings_fields[$page][$section][$id] );
        return $field_data;
    }
}
