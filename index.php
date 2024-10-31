<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use \MightyDev\Nooz;
use \MightyDev\WordPress;

// register_activation_hook must be setup in the main plugin file
// see: https://codex.wordpress.org/Function_Reference/register_activation_hook
register_activation_hook( MDNOOZ_PLUGIN_FILE, 'mdnooz_core_activation' );
function mdnooz_core_activation() {
    delete_option( 'mdnooz_flush_rewrite_rules' );
}

add_action( 'plugins_loaded', 'mdnooz_load' );
function mdnooz_load() {
    require_once( dirname( __FILE__ ) . '/inc/autoload.php' );
    $upgrades = new Nooz\Upgrade();
    $upgrades->setup();
    $form_fields = new WordPress\FormFields();
    $form_fields->setup();
    // manage plugin specific themes
    $themes_manager = new WordPress\ThemesManager();
    $themes_manager->register_themes_directory( dirname( __FILE__ ) . '/themes' );
    $nooz_admin = Nooz\Admin::get_instance();
    $nooz_admin->plugin_file( MDNOOZ_PLUGIN_FILE );
    $nooz_admin->name( 'Nooz' );
    $nooz_admin->version( '1.7.2' );
    $nooz_admin->use_form_fields( $form_fields );
    $nooz_admin->register_themes_manager( $themes_manager );
    $nooz_admin->register();
    $nooz_help = new Nooz\ContextualHelp();
    $nooz_help->plugin_file( MDNOOZ_PLUGIN_FILE );
    $nooz_help->register();
    $nooz_release_type = new Nooz\Release();
    $nooz_release_type->plugin_file( MDNOOZ_PLUGIN_FILE );
    $nooz_release_type->use_form_fields( $form_fields );
    $nooz_release_type->register();
    $nooz_coverage_type = new Nooz\Coverage();
    $nooz_coverage_type->plugin_file( MDNOOZ_PLUGIN_FILE );
    $nooz_coverage_type->use_form_fields( $form_fields );
    $nooz_coverage_type->register();

    #@#

    do_action( 'nooz_init', $nooz_admin );
}

/**
 * Get an instance of core.
 *
 * @since 0.8
 */
function mdnooz() {
    return Nooz\Admin::get_instance();
}

/**
 * Get and process a template content. Using this function allows Nooz and
 * other extensions to filter the content. At minimum, "post" template name is
 * required. Additionally the following template names/parts are recommended
 * "post-preview", "post-title", "post-date", "post-excerpt" and "post-action".
 *
 * @var string  $template_name  A template identifier.
 * @var string  $template_file  The template file to use, a theme relative or full file path.
 * @var array   $data           Data made available in the template file.
 *
 * @return string               Processed template cotent.
 */
function nooz_get_template( $template_name, $template_file = NULL, $data = NULL ) {
    if ( is_array( $template_file ) ) {
        $data = $template_file;
        $template_file = NULL;
    }
    if ( NULL === $template_file ) {
        $template_file = $template_name . '.php';
    }
    $content = mdnooz()->get_themes_manager()->get_template( $template_file, $data );
    return apply_filters( 'nooz_template_' . $template_name, $content, $data );
}
