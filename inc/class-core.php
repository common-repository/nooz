<?php

namespace MightyDev\Nooz;

use \MightyDev\WordPress\FormFields;

abstract class Core
{
    protected $form_fields;

    /**
     * Add FormFields dependency. This method can be chained.
     *
     * @var FormFields $form_fields FormFields dependency.
     *
     * @return \MightyDev\Nooz\Core
     */
    public function use_form_fields( FormFields $form_fields ) {
        $this->form_fields = $form_fields;
        return $this;
    }

    /**
     * Gets the content of a template file and applies template data. The
     * template file should be a PHP file and an assoc-array
     *
     * @var string      $file Template file path
     * @var mixed|null  $data Data passed to the template file
     *
     * @return string|null Processed template file/data
     */
    protected function get_template_content( $file, $data = NULL ) {
        if ( ! file_exists( $file ) ) return NULL;
        ob_start();
        include $file;
        return ob_get_clean();
    }

    /**
     * Confirms if $val is a truthy value.
     *
     * @var string|int $val A truthy value: 'on', 'yes', 'yup', 'y', '1', 1, 'true', 't', 'enable', 'enabled', 'ok'
     *
     * @return bool
     */
    public function is_truthy( $val ) {
        $truthy = array( 'on', 'yes', 'yup', 'y', '1', 1, 'true', 't', 'enable', 'enabled', 'ok' );
        return TRUE === $val || in_array( strtolower( trim( $val ) ), $truthy );
    }

    /**
     * Get all post IDs for a post type. Useful for upgrade functions.
     *
     * @return array Post IDs.
     */
    protected function get_post_ids( $post_type ) {
        $query = new \WP_Query( array(
            'post_type' => $post_type,
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ) );
        return $query->posts;
    }

    /**
     * Checks and sets a run once option by name. Used by upgrade functions
     * to short circuit an upgrade routine if already completed.
     *
     * @var string $option_name Option name
     *
     * @return mixed FALSE if unset or option value if exists.
     */
    protected function run_once( $option_name ) {
        $option_value = get_option( $option_name );
        if ( ! $option_value ) update_option( $option_name, time() );
        return $option_value;
    }

    /**
    * Deletes all options with prefix.
    *
    * @var string $prefix Option name prefix
    *
    * @return mixed FALSE on error, TRUE or number of rows affected
     */
    public function delete_option_with_prefix( $prefix ) {
        if ( empty( $prefix ) ) return FALSE;
        global $wpdb;
        return $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE `option_name` LIKE %s", $prefix . '%' ) );
    }

    /**
     * Only creates options if not already present. This method can be chained.
     *
     * @var array $default_options Option and value pairs
     *
     * @return \MightyDev\Nooz\Core
     */
    public function set_default_options( $default_options ) {
        foreach( $default_options as $name => $value ) {
            if ( FALSE === get_option( $name ) ) {
                update_option( $name, $value );
            }
        }
        return $this;
    }

    /**
     * Used to trigger flush_rewrite_rules() on the next "wp_loaded" action call.
     */
    public function trigger_flush_rewrite_rules() {
        delete_option( 'mdnooz_flush_rewrite_rules' );
    }
}
