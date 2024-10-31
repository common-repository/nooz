<?php

namespace MightyDev\Nooz;

class Release extends Base
{
    protected $post_type = 'nooz_release';

    public function register() {
        add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_post_version' ), 10, 3 );
        add_action( 'save_post', array( $this, 'save_dateline_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_subheadline_meta_box' ) );
        add_filter( 'shortcode_atts_nooz-release', array( $this, 'filter_shortcode_post_type' ), 10, 4 );
        add_filter( 'shortcode_atts_nooz', array( $this, 'filter_shortcode_post_type' ), 10, 4 );
        // filter early, before "run_shortcode() 8, autoembed() 8, wptexturize() 10, wpautop() 10, shortcode_unautop() 10, do_shortcode() 11",
        // so that dateline can be combined with the first line of the release content
        add_filter( 'the_content', array( $this, 'filter_post_content' ), 6 );
        add_filter( 'nooz_post_data', array( $this, 'filter_post_data' ) );
        add_filter( 'post_class', array( $this, 'filter_post_class' ), 10, 3 );
        add_filter( 'nooz/settings/groups', array ( $this, 'add_settings_group' ) );
        add_action( 'nooz/settings/setup/group=nooz_release', array ( $this, 'setup_settings' ) );
        // action: "update_option_{$option}", runs only when the option is updated (e.g. if new and old values are the same, this DOES NOT run)
        // @see https://developer.wordpress.org/reference/functions/update_option/
        add_action( 'update_option_mdnooz_release_slug', array( $this, 'trigger_flush_rewrite_rules') );
    }

    public function filter_post_data( $data ) {
        if ( $this->post_type == $data['post_type'] ) {
            $data['featured_image_url'] = $data['featured_image_url'] ?: get_option( 'mdnooz_release_default_image_url' );
            $data['subheadline'] = get_post_meta( $data['post_id'], '_mdnooz_subheadline', TRUE );
            $data['location'] = get_post_meta( $data['post_id'], '_mdnooz_release_location', TRUE ) ?: get_option( 'mdnooz_release_location' );
            $data['source'] = NULL;
        }
        return $data;
    }

    public function filter_shortcode_post_type( $atts, $default_atts, $runtime_atts, $tag ) {
        // possible shortcode usage: [nooz type="nooz_release"], [nooz type="release"], [nooz-release], [nooz]
        if ( stristr( $atts['type'], 'release' ) || stristr( $tag, 'release' ) || ( '' == $atts['type'] && 'nooz' ==  $tag ) ) {
            $atts['type'] = $this->post_type;
        }
        return $atts;
    }

    /**
     * Add css classes to the post.
     *
     * @param array  $classes CSS classes
     *
     * @return array CSS classes
     */
    public function filter_post_class( $classes, $additional_classes, $post_id ) {
        $post = get_post( $post_id );
        if ( $this->post_type !== $post->post_type ) {
            return $classes;
        }
        $classes[] = 'nooz-release';
        if ( $this->is_truthy( get_post_meta( $post->ID, '_mdnooz_combine_dateline', TRUE ) ) ) {
            $classes[] = 'nooz-release--combine-dateline';
        }
        return $classes;
    }

    /**
     * Used to filter "the_content" for a press release post. This filter adds
     * additional content to the press release body.
     *
     * @param string  $content Post content
     *
     * @return string Post content
     */
    public function filter_post_content( $content ) {
        global $post;
        if ( is_admin() ) return $content;
        if ( ! isset( $post->post_type ) ) return $content;
        if ( $this->post_type !== $post->post_type ) return $content;
        $data = array(
            'subheadline' => get_post_meta( $post->ID, '_mdnooz_subheadline', TRUE ),
            'location' => get_post_meta( $post->ID, '_mdnooz_release_location', TRUE ) ?: get_option( 'mdnooz_release_location' ),
            'date' => get_the_date( $this->get_date_format(), $post->ID ),
            'dateline' => 'no' == get_post_meta( $post->ID, '_mdnooz_use_dateline', TRUE ) ? NULL : $this->get_dateline( $post->ID ),
            'boilerplate' => get_option( 'mdnooz_release_boilerplate' ),
            'contact' => get_option( 'mdnooz_release_contact' ),
            'ending' => get_option( 'mdnooz_release_ending' ),
            'content' => $content,
        );
        $data = apply_filters( 'nooz/release/data', $data, $post->ID );
        $template_file = apply_filters( 'nooz/theme/release_template_file', plugin_dir_path( MDNOOZ_PLUGIN_FILE ) . 'themes/' . get_option( 'mdnooz_shortcode_theme' ) . '/release.php' );
        $content = '';
        if ( $template_file && file_exists( $template_file ) ) {
            ob_start();
            include( $template_file );
            $content = ob_get_clean();
        }
        $content = apply_filters( 'nooz_release', $content, $data, $post->ID );
        $content = apply_filters( 'nooz/release/content', $content, $data, $post->ID );
        return apply_filters( 'nooz/theme/release_template_content', $content, $data, $post->ID );
    }

    /**
     * Compose the dateline (e.g. "San Francisco, CA, April, 4 2018")
     *
     * @param int $post_id Post ID
     *
      * @return string Dateline content
     */
    public function get_dateline( $post_id ) {
        $location = get_post_meta( $post_id, '_mdnooz_release_location', TRUE ) ?: get_option( 'mdnooz_release_location' );
        $date = get_the_date( $this->get_date_format(), $post_id );
        $dateline_search = array(
            '{location}',
            '{date}',
            '{dash}',
        );
        $dateline_replace = array(
            $location ? sprintf( '<span class="nooz-dateline__location nooz-location">%s</span>', wp_kses_post( $location ) ) : NULL,
            sprintf( '<span class="nooz-dateline__datetime nooz-datetime">%s</span>', $date ),
            '<span class="nooz-dateline__separator">—</span>',
        );
        $dateline = trim( str_replace( $dateline_search, $dateline_replace, get_option( 'mdnooz_release_dateline_format' ) ) );
        // trim non-alphanumeric from the begining of string
        $dateline = preg_replace( '/^[^<A-Za-z0-9]+/', '', $dateline );
        /**
         * @deprecated 1.0.0 Do not use css class "nooz-location-datetime", use "nooz-dateline" or "nooz-release__dateline" instead.
         */
        return $dateline ? sprintf( '<span class="nooz-release__dateline nooz-dateline nooz-location-datetime">%s</span> ', $dateline ) : NULL;
    }

    /**
     * Used to filter "the_content" for press releases. This filter adds
     * additional content to the press release body.
     *
     * @param string  $content Post content
     *
     * @return string Post content
     */
    protected function get_date_format() {
        if ( get_option( 'mdnooz_release_date_format' ) ) {
            return wp_kses_data( strip_tags( get_option( 'mdnooz_release_date_format' ) ) );
        } else {
            return get_option( 'date_format' );
        }
    }

    /**
     * Register meta boxes.
     */
    public function register_meta_boxes() {
        // id, title, callback, screen, context, priority, callback_args
        add_meta_box( 'nooz-meta-box__release-dateline', 'Dateline', array( $this, 'get_dateline_meta_box' ), 'nooz_release', 'normal', 'high', NULL );
        add_meta_box( 'nooz-meta-box__release-subheadline', 'Subheadline', array( $this, 'get_subheadline_meta_box' ), 'nooz_release', 'normal', 'high', NULL );
    }

    /**
     * Get subheadline meta box content.
     */
    public function get_subheadline_meta_box( $post ) {
        // action_name, field_name
        $nonce_id = 'mdnooz_subheadline';
        wp_nonce_field( $nonce_id, $nonce_id . '_nonce' );
        include( plugin_dir_path( $this->plugin_file ) . 'inc/templates/release-subheadline-meta.php' );
    }

    /**
     * Get dateline meta box content.
     */
    public function get_dateline_meta_box( $post ) {
        // action_name, field_name
        $nonce_id = 'mdnooz_release_dateline';
        wp_nonce_field( $nonce_id, $nonce_id . '_nonce' );
        include( plugin_dir_path( $this->plugin_file ) . 'inc/templates/release-dateline-meta.php' );
    }

    /**
     * Save subheadline meta box data.
     */
    public function save_subheadline_meta_box( $post_id ) {
        if ( ! $this->can_save_meta_box( $post_id, 'mdnooz_subheadline' ) ) {
            return;
        }
        if ( isset( $_POST['_mdnooz_subheadline'] ) ) {
            update_post_meta( $post_id, '_mdnooz_subheadline', sanitize_text_field( $_POST['_mdnooz_subheadline'] ) );
        }
    }

    /**
     * Save dateline meta box data.
     */
    public function save_dateline_meta_box( $post_id ) {
        if ( ! $this->can_save_meta_box( $post_id, 'mdnooz_release_dateline' ) ) {
            return;
        }
        if ( isset( $_POST['_mdnooz_release_location'] ) ) {
            update_post_meta( $post_id, '_mdnooz_release_location', $this->sanitize_release_location( $_POST['_mdnooz_release_location'] ) );
        }
        update_post_meta( $post_id, '_mdnooz_combine_dateline', isset( $_POST['_mdnooz_combine_dateline'] ) ? 'yes' : 'no' );
        // meta box ui is a checkbox "Hide dateline?"
        update_post_meta( $post_id, '_mdnooz_use_dateline', isset( $_POST['_mdnooz_use_dateline'] ) ? 'no' : 'yes' );
    }

    /**
     * Save the Nooz version that created the post.
     *
     * @see https://developer.wordpress.org/reference/hooks/save_post/
     */
    public function save_post_version( $post_id, $post, $is_update ) {
        $is_post_type = $this->post_type == get_post_type( $post_id );
        $is_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
        if ( ! $is_update || ! $is_post_type || $is_autosave ) {
            return;
        }
        if ( ! get_post_meta( $post_id, '_mdnooz_version', TRUE ) ) {
            update_post_meta( $post_id, '_mdnooz_version', MDNOOZ_PLUGIN_VERSION );
        }
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
        $is_post_type = $this->post_type == get_post_type( $post_id );
        $is_valid_nonce = is_null( $nonce_id ) || ( isset( $_POST[$nonce_id . '_nonce'] ) && wp_verify_nonce( $_POST[$nonce_id . '_nonce'], $nonce_id ) );
        $is_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
        return $is_post_type && $is_valid_nonce && ! $is_autosave;
    }

    /**
     * Used to sanitize release location field values.
     *
     * @see https://developer.wordpress.org/reference/functions/sanitize_text_field/
     *
     * @param string $str Location
     *
     * @return string Sanitized location
     */
    public function sanitize_release_location( $str ) {
        $str = sanitize_text_field( $str );
        $str = preg_replace( '/^[^[:alnum:]]+/u', '', $str ); // beginning
        return preg_replace( '/[^[:alnum:]\.\)]+$/u', '', $str ); // end
    }

    public function add_settings_group( $groups ) {
        array_push( $groups, array(
            'id' => 'nooz_release',
            'title' => __( 'Press Release', 'mdnooz' ),
            'description' => __( 'These options modify the behavior of a press release page.', 'mdnooz' ) . ' <a href="#" class="nooz-help-link" data-help-tab="nooz-release"><span class="dashicons dashicons-editor-help"></span></a>',
        ) );
        return $groups;
    }

    public function setup_settings() {
        // settings page
        $cb = 'sanitize_text_field';
        register_setting( 'nooz_release', 'mdnooz_release_slug', $cb );
        register_setting( 'nooz_release', 'mdnooz_release_default_image_url' );
        register_setting( 'nooz_release', 'mdnooz_release_location', array( $this, 'sanitize_release_location' ) );
        register_setting( 'nooz_release', 'mdnooz_release_date_format', $cb );
        register_setting( 'nooz_release', 'mdnooz_release_dateline_format' );
        register_setting( 'nooz_release', 'mdnooz_release_boilerplate' );
        register_setting( 'nooz_release', 'mdnooz_release_contact' );
        register_setting( 'nooz_release', 'mdnooz_release_ending', $cb );
        // settings page > sections
        add_settings_section( 'default', NULL, NULL, 'nooz_release' );
        // settings page > section > fields
        add_settings_field( 'mdnooz_release_slug', __( 'URL', 'mdnooz' ), array( $this->form_fields, 'text' ), 'nooz_release', 'default', array(
            'class' => 'nooz-field-release-slug',
            'label_for' => 'mdnooz_release_slug',
            'field_data' => array(
                'name' => 'mdnooz_release_slug',
                'description' => __( 'The URL base for a press release landing page.', 'mdnooz' ) . ' ' .
                /* translators: 1: Link to Caching information */
                sprintf( __( 'Your browser <a href="%1$s" target="_blank">cache</a> may need to be cleared if you change this.', 'mdnooz' ), 'https://codex.wordpress.org/WordPress_Optimization#Caching' ),
                'value' => get_option( 'mdnooz_release_slug' ),
                'before_field' => '<code>' . home_url() . '/</code>',
                'after_field' => '<code>/{slug}/</code>',
            ),
        ) );
        add_settings_field( 'mdnooz_release_default_image_url', __( 'Featured Image', 'mdnooz' ), array( $this->form_fields, 'media' ), 'nooz_release', 'default', array(
            'class' => 'nooz-field-media',
            'label_for' => 'mdnooz_release_default_image_url',
            'field_data' => array(
                'name' => 'mdnooz_release_default_image_url',
                'description' => __( 'The default featured image to use if one is not selected.', 'mdnooz' ),
                'value' => get_option( 'mdnooz_release_default_image_url' ),
                'media' => array(
                    'title' => __( 'Default Featured Image', 'mdnooz' ),
                    'select_button' => __( 'Select', 'mdnooz' ),
                    'add_button' => __( 'Select Image', 'mdnooz' ),
                    'remove_button' => __( 'Remove Image', 'mdnooz' ),
                    'error_message' => __( 'Unable to find image file', 'mdnooz' ),
                ),
            ),
        ) );
        add_settings_field( 'mdnooz_release_location', _x( 'Location', 'city/state', 'mdnooz' ), array( $this->form_fields, 'text' ), 'nooz_release', 'default', array(
            'label_for' => 'mdnooz_release_location',
            'field_data' => array(
                'name' => 'mdnooz_release_location',
                'description' => __( 'The location precedes the press release and helps to orient the reader (e.g. San Francisco, CA).', 'mdnooz' ),
                'value' => get_option( 'mdnooz_release_location' ),
            ),
        ) );
        add_settings_field( 'mdnooz_release_date_format', __( 'Date Format', 'mdnooz' ), array( $this->form_fields, 'text' ), 'nooz_release', 'default', array(
            'class' => 'nooz-field--tiny',
            'label_for' => 'mdnooz_release_date_format',
            'field_data' => array(
                'name' => 'mdnooz_release_date_format',
                /* translators: 1: Link to WordPress General options 2: Link to Formatting Date and Time information */
                'description' => sprintf( __( 'The date follows the location. Leave this blank to use the <a href="%1$s">default date format</a> as set in WordPress. Learn more about <a href="%2$s" target="_blank">formatting dates</a>.', 'mdnooz' ), admin_url( 'options-general.php' ), 'https://codex.wordpress.org/Formatting_Date_and_Time' ),
                'value' => get_option( 'mdnooz_release_date_format' ),
                'placeholder' => get_option( 'date_format' ),
            ),
        ) );
        add_settings_field( 'mdnooz_release_dateline_format', __( 'Dateline Format', 'mdnooz' ), array( $this->form_fields, 'text' ), 'nooz_release', 'default', array(
            'class' => 'nooz-field--medium',
            'label_for' => 'mdnooz_release_dateline_format',
            'field_data' => array(
                'name' => 'mdnooz_release_dateline_format',
                'description' => __( 'Available variables are <code>{location}</code>, <code>{date}</code> and <code>{dash}</code> (e.g. long dash —).', 'mdnooz' ),
                'value' => get_option( 'mdnooz_release_dateline_format' ),
            ),
        ) );
        add_settings_field( 'mdnooz_release_boilerplate', _x( 'Boilerplate', 'boilerplate text/content', 'mdnooz' ), array( $this->form_fields, 'textarea' ), 'nooz_release', 'default', array(
            'label_for' => 'mdnooz_release_boilerplate',
            'field_data' => array(
                'name' => 'mdnooz_release_boilerplate',
                'description' => __( 'The boilerplate is a few sentences at the end of the press release that describes your organization. This should be used consistently on press materials and written strategically, to properly reflect your organization.', 'mdnooz' ),
                'value' => get_option( 'mdnooz_release_boilerplate' ),
            ),
        ) );
        add_settings_field( 'mdnooz_release_ending', _x( 'Ending', 'an ending mark/the end', 'mdnooz' ), array( $this->form_fields, 'text' ), 'nooz_release', 'default', array(
            'class' => 'nooz-field--tiny',
            'label_for' => 'mdnooz_release_ending',
            'field_data' => array(
                'name' => 'mdnooz_release_ending',
                'value' => get_option( 'mdnooz_release_ending' ),
                'description' => __( 'The ending mark signifies the absolute end of the press release (e.g. ###, END, XXX, -30-).', 'mdnooz' ),
            ),
        ) );
        add_settings_field( 'mdnooz_release_contact', __( 'Media Contact', 'mdnooz' ), array( $this->form_fields, 'textarea' ), 'nooz_release', 'default', array(
            'label_for' => 'mdnooz_release_contact',
            'field_data' => array(
                'name' => 'mdnooz_release_contact',
                'description' => __( 'Contact information for the PR or other media relations contact person at your organization.', 'mdnooz' ),
                'value' => get_option( 'mdnooz_release_contact' ),
            ),
        ) );
    }
}
