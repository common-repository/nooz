<?php

/*
Plugin Name: Nooz
Plugin URI: https://www.mightydev.com/nooz/
Description: Simplified press release and media coverage management for websites.
Version: 1.7.2
Author: Mighty Digital
Author URI: https://www.mightydev.com
Text Domain: mdnooz
*/

// exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$exit_styles = '<style> html, body { font-family: sans-serif; font-size: 14px; margin: 0; padding: 0; } </style>';
if ( defined( 'MDNOOZ_PLUGIN_FILE' ) ) exit( __( 'Only a single version of the Nooz plugin can be active. You must deactivate the current version.', 'mdnooz' ) . $exit_styles );
/* translators: 1: Required PHP version 2: Current PHP version */
if ( version_compare( PHP_VERSION, '5.6', '<' ) ) exit( sprintf( __( 'The Nooz plugin requires PHP version %1$s, the current PHP version is %2$s', 'mdnooz' ), '5.6+', PHP_VERSION ) . $exit_styles );
unset( $exit_styles );

// @since 1.0.0
define( 'MDNOOZ_PLUGIN_FILE', __FILE__ );
define( 'MDNOOZ_PLUGIN_VERSION', '1.7.2' );
require_once( plugin_dir_path( MDNOOZ_PLUGIN_FILE ) . 'index.php' ); // bootstrap
