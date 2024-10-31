<?php

namespace MightyDev\Nooz;

class Coverage extends Base
{
    protected $post_type = 'nooz_coverage';

    public function register() {
        add_filter( 'shortcode_atts_nooz-coverage', array( $this, 'filter_shortcode_post_type' ), 10, 4 );
        add_filter( 'shortcode_atts_nooz', array( $this, 'filter_shortcode_post_type' ), 10, 4 );
        add_filter( 'post_type_link', array( $this, 'filter_post_link' ), 10, 2 );
        add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_source_meta_box' ) );
        add_filter( 'nooz_post_data', array( $this, 'filter_post_data' ), 10, 2 );
        add_filter( 'nooz/settings/groups', array ( $this, 'add_settings_group' ) );
        add_action( 'nooz/settings/setup/group=nooz_coverage', array ( $this, 'setup_settings' ) );
        add_filter( 'sanitize_post_meta__mdnooz_source', 'trim' );
        add_filter( 'sanitize_post_meta__mdnooz_link_url', 'trim' );
        add_filter( 'sanitize_post_meta__mdnooz_link_url', 'esc_url_raw' );
        // action: "update_option_{$option}", runs only when the option is updated (e.g. if new and old values are the same, this DOES NOT run)
        // @see https://developer.wordpress.org/reference/functions/update_option/
        add_action( 'update_option_mdnooz_coverage_slug', array( $this, 'trigger_flush_rewrite_rules' ) );
    }

    /**
     * Register meta boxes.
     */
    public function register_meta_boxes() {
        // id, title, callback, screen, context, priority, callback_args
        add_meta_box( 'nooz-meta-box__coverage-source', 'Source', array( $this, 'get_source_meta_box' ), 'nooz_coverage', 'normal', 'high', NULL );
    }

    /**
     * Get source meta box content.
     */
    public function get_source_meta_box( $post ) {
        wp_nonce_field( 'mdnooz_source', 'mdnooz_source_nonce' );
        include( __DIR__ . '/templates/coverage-source-meta.php' );
    }

    /**
     * Save source meta box data.
     */
    public function save_source_meta_box( $post_id ) {
        if ( ! $this->can_save_meta_box( $post_id, 'mdnooz_source' ) ) return;
        update_post_meta( $post_id, '_mdnooz_link_url', $_POST['_mdnooz_link_url'] );
        update_post_meta( $post_id, '_mdnooz_link_target', isset( $_POST['_mdnooz_link_target'] ) ? $_POST['_mdnooz_link_target'] : '' );
        update_post_meta( $post_id, '_mdnooz_source', $_POST['_mdnooz_source'] );
    }

    /**
     * Checks if meta box data should be saved. If nonce will also be checked
     * if a value is given.
     *
     * @param int    $post_id Post ID
     * @param string $nonce_id Nonce action name (nonce_id)
     *
     * @return bool
     */
    protected function can_save_meta_box( $post_id, $nonce_id = NULL ) {
        $is_post_type = $this->post_type === get_post_type( $post_id );
        $is_valid_nonce = is_null( $nonce_id ) || ( isset( $_POST[$nonce_id . '_nonce'] ) && wp_verify_nonce( $_POST[$nonce_id . '_nonce'], $nonce_id ) );
        $is_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
        return $is_post_type && $is_valid_nonce && ! $is_autosave;
    }

    public function filter_post_data( $data, $atts ) {
        if ( $this->post_type == $data['post_type'] ) {
            $data['featured_image_url'] = $data['featured_image_url'] ?: get_option( 'mdnooz_coverage_default_image_url' );
            $data['source'] = get_post_meta( $data['post_id'], '_mdnooz_source', TRUE ) ?: NULL;
            $link_target = '_blank';
            if ( ! is_null( $data['link_target'] ) ) {
                $link_target = $data['link_target'];
            }
            $field_name = '_mdnooz_link_target';
            if ( metadata_exists( 'post', $data['post_id'], $field_name ) ) {
                $link_target = get_post_meta( $data['post_id'], $field_name, TRUE );
            }
            $data['link_target'] = $link_target;
        }
        return $data;
    }

    public function filter_post_link( $permalink, $post ) {
        if ( isset( $post->post_type ) && $post->post_type === $this->post_type ) {
            $link_url = get_post_meta( $post->ID, '_mdnooz_link_url', TRUE );
            if( ! empty( $link_url ) ) $permalink = $link_url;
        }
        return $permalink;
    }

    public function filter_shortcode_post_type( $atts, $default_atts, $defined_atts, $tag ) {
        // possible shortcode usage: [nooz type="nooz_coverage"], [nooz type="coverage"], [nooz-coverage]
        if ( stristr( $atts['type'], 'coverage' ) || stristr( $tag, 'coverage' ) ) {
            $atts['type'] = $this->post_type;
        }
        return $atts;
    }

    public function add_settings_group( $groups ) {
        array_push( $groups, array(
            'id' => 'nooz_coverage',
            'title' => __( 'Press Coverage', 'mdnooz' ),
            'description' => __( 'These options modify the behavior of a coverage page.', 'mdnooz' ) . ' <a href="#" class="nooz-help-link" data-help-tab="nooz-coverage"><span class="dashicons dashicons-editor-help"></span></a>',
        ) );
        return $groups;
    }

    public function setup_settings() {
        // settings page
        $cb = 'sanitize_text_field';
        register_setting( 'nooz_coverage', 'mdnooz_coverage_slug', $cb );
        register_setting( 'nooz_coverage', 'mdnooz_coverage_default_image_url' );
        // settings page > sections
        add_settings_section( 'default', NULL, NULL, 'nooz_coverage' );
        // settings page > section > fields
        add_settings_field( 'mdnooz_coverage_slug', __( 'URL', 'mdnooz' ), array( $this->form_fields, 'text' ), 'nooz_coverage', 'default', array(
            'class' => 'nooz-field-coverage-slug',
            'label_for' => 'mdnooz_coverage_slug',
            'field_data' => array(
                'name' => 'mdnooz_coverage_slug',
                'description' => __( 'The URL base for a press coverage landing page.', 'mdnooz' ) . ' ' .
                /* translators: 1: Link to Caching information */
                sprintf( __( 'Your browser <a href="%1$s" target="_blank">cache</a> may need to be cleared if you change this.', 'mdnooz' ), 'https://codex.wordpress.org/WordPress_Optimization#Caching' ),
                'value' => get_option( 'mdnooz_coverage_slug' ),
                'before_field' => '<code>' . home_url() . '/</code>',
                'after_field' => '<code>/</code>',
            ),
        ) );
        add_settings_field( 'mdnooz_coverage_default_image_url', __( 'Featured Image', 'mdnooz' ), array( $this->form_fields, 'media' ), 'nooz_coverage', 'default', array(
            'class' => 'nooz-field-media',
            'label_for' => 'mdnooz_coverage_default_image_url',
            'field_data' => array(
                'name' => 'mdnooz_coverage_default_image_url',
                'description' => __( 'The default featured image to use if one is not selected.', 'mdnooz' ),
                'value' => get_option( 'mdnooz_coverage_default_image_url' ),
                'media' => array(
                    'title' => __( 'Default Featured Image', 'mdnooz' ),
                    'select_button' => __( 'Select', 'mdnooz' ),
                    'add_button' => __( 'Select Image', 'mdnooz' ),
                    'remove_button' => __( 'Remove Image', 'mdnooz' ),
                    'error_message' => __( 'Unable to find image file', 'mdnooz' ),
                ),
            ),
        ) );
    }
}
