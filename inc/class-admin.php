<?php

namespace MightyDev\Nooz;

use MightyDev\WordPress\ThemesManager;
use MightyDev\WordPress\FormFields;

class Admin extends Base
{
    protected $release_post_type = 'nooz_release';
    protected $coverage_post_type = 'nooz_coverage';
    protected $themes_manager;

    protected $form_fields;

    protected $managed_post_types;

    private static $instance = NULL;

    /**
     * Get an instance of core.
     *
     * @since 1.3.0
     * @return \MightyDev\Nooz\Admin
     */
    static function get_instance() {
        if ( NULL === self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    /**
     * Intentionally left empty and public.
     *
     * @since 1.3.0
     */
    public function __construct() {}

    public function use_form_fields( FormFields $form_fields ) {
        $this->form_fields = $form_fields;
    }

    /**
     * The post ID of the default "News" page when created.
     */
    protected $default_page_id;

    public function register_default_options() {
        $this->set_default_options( array(
            'mdnooz_coverage_default_image_url' => '',
            'mdnooz_coverage_slug' => 'news/press-coverage',
            'mdnooz_release_boilerplate' => '',
            'mdnooz_release_date_format' => 'F j, Y',
            'mdnooz_release_default_image_url' => '',
            'mdnooz_release_ending' => '###',
            'mdnooz_release_location' => '',
            'mdnooz_release_dateline_format' => '{location}, {date} {dash}',
            'mdnooz_release_contact' => '',
            'mdnooz_release_slug' => 'news/press-releases',
            'mdnooz_shortcode_count' => 8,
            'mdnooz_shortcode_date_format' => 'M j, Y',
            'mdnooz_shortcode_more_link' => '',
            'mdnooz_shortcode_next_link' => '',
            'mdnooz_shortcode_previous_link' => '',
            'mdnooz_shortcode_use_excerpt' => 'yes',
            // defaults to "no" for backward-compatibility
            'mdnooz_shortcode_use_more_link' => 'no',
            'mdnooz_shortcode_use_pagination' => 'no',
            'mdnooz_shortcode_use_archive_link' => 'no', // no, yes, auto
            /**
             * @deprecated 1.3.0 Element order SHOULD BE done in template or via css.
             */
            'mdnooz_shortcode_item_element_order' => 'image, date, source, meta, title, excerpt, more_link',
            'mdnooz_shortcode_theme' => 'outline',
        ) );
    }

    public function register_themes_manager( ThemesManager $manager ) {
        $this->themes_manager = $manager;
    }

    public function get_themes_manager() {
        return $this->themes_manager;
    }

    // todo: move into upgrade class
    public function upgrade_options() {
        $options = get_option( 'nooz_options', array() );
        if ( ! empty( $options ) ) {
            $map = array(
                'boilerplate'        => 'mdnooz_release_boilerplate',
                'date_format'        => 'mdnooz_release_date_format',
                'ending'             => 'mdnooz_release_ending',
                'location'           => 'mdnooz_release_location',
                'release_slug'       => 'mdnooz_release_slug',
                'shortcode_count'    => 'mdnooz_shortcode_count',
                'shortcode_display'  => 'mdnooz_shortcode_display',
            );
            foreach ( $options as $name => $value ) {
                if ( isset( $map[$name] ) ) {
                    if ( 'ending' == $name && $this->is_truthy( $value ) ) {
                        $value = '###';
                    }
                    update_option( $map[$name], $value );
                }
            }
            delete_option( 'nooz_options' );
        }
        if ( false !== get_option( 'nooz_default_pages' ) ) {
            update_option( 'mdnooz_default_pages', get_option( 'nooz_default_pages' ) );
            delete_option( 'nooz_default_pages' );
        }
    }

    public function get_post_types() {
        return array( $this->release_post_type, $this->coverage_post_type );
    }

    public function get_shortcode_date_format() {
        if ( get_option( 'mdnooz_shortcode_date_format' ) ) {
            return wp_kses_data( strip_tags( get_option( 'mdnooz_shortcode_date_format' ) ) );
        } else {
            return get_option( 'date_format' );
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function register() {
        $this->register_default_options();
        $this->upgrade_options();
        add_action( 'init', array( $this, 'create_cpt' ), 50 );
        // initialize admin menus
        add_action( 'admin_init', array ( $this, 'setup_settings_page' ) );
        add_filter( 'nooz/settings/groups', array ( $this, 'add_shortcode_settings_group' ) );
        add_action( 'nooz/settings/setup/group=nooz_general', array ( $this, 'setup_shortcode_settings' ) );
        add_action( 'admin_menu', array ( $this, '_create_admin_menus' ) );
        add_action( 'admin_init', array ( $this, 'init_default_pages' ) );
        add_shortcode( 'nooz', array( $this, '_shortcode' ) );
        add_shortcode( 'nooz-release', array( $this, '_shortcode' ) );
        add_shortcode( 'nooz-coverage', array( $this, '_shortcode' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_styles_and_scripts' ) );
        // run after themes have enabled supported features
        // TODO: is 99 required???
        add_action( 'after_setup_theme', array( $this, '_enable_featured_image_support' ), 99 );
        add_filter( 'get_the_excerpt', array( $this, '_release_excerpt' ), 10, 2 );
        // load theme, using 'init' action allows functions.php to use this filter
        add_action( 'init', array( $this, '_load_theme' ) );
        // TODO what if other plugins use this code ???
        add_action( 'edit_form_after_title', array( $this, '_add_meta_box_after_title_container' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( $this->plugin_file ), array( $this, 'plugin_action_links' ) );
        add_action( 'init', array( $this, 'setup_textdomain' ) );
        // handle pretty URLs for shortcode post_type (e.g. category) links
        add_filter( 'query_vars', array( $this, '_setup_query_vars_for_category_links' ) );
        add_action( 'nooz/post-types/registered', array( $this, '_setup_rewrite_rules_for_category_links' ) );
        // flush rewrite rules
        add_action( 'wp_loaded', array( $this, 'run_flush_rewrite_rules' ) );
    }

    /**
     * Triggers flush_rewrite_rules() if the "mdnooz_flush_rewrite_rules" option is not found.
     */
    public function run_flush_rewrite_rules() {
        // this will run once on install and everytime the 'mdnooz_flush_rewrite_rules' option is deleted
        if ( get_option( 'mdnooz_flush_rewrite_rules' ) ) return;
        update_option( 'mdnooz_flush_rewrite_rules', TRUE );
        flush_rewrite_rules();
    }

    /**
     * Register query vars for shortcode post_type (e.g. category) links.
     *
     * @since 1.6.0
     */
    public function _setup_query_vars_for_category_links( $vars ) {
        if ( is_array( $vars ) ) $vars[] = 'md_post_type';
        return $vars;
    }

    /**
     * Register query vars for shortcode post_type (e.g. category) links.
     *
     * @since 1.6.0
     */
    function _setup_rewrite_rules_for_category_links( $post_types ) {
        foreach( $post_types as $post_type ) {
            $post_type_object = get_post_type_object( $post_type );
            $post_type_slug = sanitize_title( isset( $post_type_object->label ) ? $post_type_object->label : $post_type );
            // TODO use post type slug by default, plugin can then use filter to change releases and coverage slugs
            $post_type_slug = apply_filters( 'nooz/rewrite/category_slug', $post_type_slug );
            add_rewrite_rule( '(.?.+?)/category/' . $post_type_slug . '/page/?([0-9]{1,})/?$', 'index.php?pagename=$matches[1]&paged=$matches[2]&md_post_type=' . $post_type, 'top' );
            add_rewrite_rule( '(.?.+?)/category/' . $post_type_slug . '/?', 'index.php?pagename=$matches[1]&md_post_type=' . $post_type, 'top' );
        }
    }

    /**
     * Setup textdomain for community translations.
     *
     * @since 1.5.0
     */
    public function setup_textdomain() {
        load_plugin_textdomain( 'mdnooz', false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages' );
    }

    /**
     * Prepends "Settings" plugin link.
     *
     * @see example.org/wp-admin/plugins.php
     *
     * @since 1.3.0
     *
     * @return array Plugin action links
     */
    function plugin_action_links( $links ) {
        array_unshift( $links, sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=nooz' ), __( 'Settings', 'mdnooz' ) ) );
        return $links;
    }

    public function _add_meta_box_after_title_container() {
        ?><div id="nooz-meta-box__after-title"></div><?php
    }

    /**
     * Loads the plugin theme. The "theme.php" file is analogous to the
     * "functions.php" file of a WordPress theme. It is used for setup (css,
     * js, filters) and functionality of the theme.
     *
     * @since 1.0
     */
    public function _load_theme() {
        $functions_file = apply_filters( 'nooz_theme_file', plugin_dir_path( MDNOOZ_PLUGIN_FILE ) . 'themes/' . get_option( 'mdnooz_shortcode_theme' ) . '/theme.php' );
        // @since 1.6.1
        $functions_file = apply_filters( 'nooz/theme/functions_file', $functions_file );
        if ( $functions_file ) {
            $this->themes_manager->activate_theme( $functions_file );
            require_once( $functions_file );
        }
    }

    /**
     * Used to filter "get_the_excerpt" for press releases. It uses raw
     * "post_content", before "the_content" filters are applied.
     *
     * @see https://developer.wordpress.org/reference/hooks/get_the_excerpt/
     * @see self::_release_content()
     *
     * @param string  $post_excerpt Post excerpt
     * @param WP_Post $post WP_Post object
     *
     * @return string Post excerpt
     */
    public function _release_excerpt( $post_excerpt, $post = NULL ) {
        if ( ! isset( $post->post_type ) ) global $post;
        if ( isset( $post->post_type ) && $this->release_post_type == $post->post_type ) {
            $length = apply_filters( 'excerpt_length', 55 );
            $ending = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
            // use post_content before subheadline is added
            $post_excerpt = ! empty( $post->post_excerpt ) ? $post->post_excerpt : wp_trim_words( $post->post_content, $length, $ending );
        }
        return $post_excerpt;
    }

    function _enable_featured_image_support() {
        // enable support if NOT already enabled by the theme
        if ( ! current_theme_supports( 'post-thumbnails' ) ) {
            add_theme_support( 'post-thumbnails', $this->get_post_types() );
        }
    }

    public function register_admin_styles_and_scripts() {
        wp_enqueue_style( 'mdnooz-admin', plugins_url( 'inc/css/admin.css', $this->plugin_file ), array(), get_option( 'mdnooz_plugin_version' ) );
        wp_enqueue_media();
        wp_enqueue_script( 'autosize', plugins_url( 'inc/vendor/autosize/autosize.min.js', $this->plugin_file ), array( 'jquery' ), '4.0.2' );
        wp_enqueue_script( 'mdnooz-admin', plugins_url( 'inc/js/admin.min.js', $this->plugin_file ), array( 'jquery' ), get_option( 'mdnooz_plugin_version' ), TRUE );
    }

    public function init_default_pages() {
        $option = 'mdnooz_default_pages';
        $option_val = get_option( $option );
        if ( FALSE !== $option_val ) {
            return $option_val;
        }
        if ( isset( $_GET[$option] ) ) {
            update_option( $option, $_GET[$option] );
            if ( 'publish' == $_GET[$option] ) {
                $page_id = $this->create_default_page();
                if ( ! is_wp_error( $page_id ) ) {
                    $this->default_page_id = $page_id;
                    add_action( 'admin_notices', array( $this, 'show_default_page_published_admin_notice' ) );
                }
            }
        } else {
            add_action( 'admin_notices', array( $this, 'show_default_page_prompt_admin_notice' ) );
        }
    }

    /**
     * Admin notice to prompt the user to create a default "News" page.
     */
    public function show_default_page_prompt_admin_notice() {
        $url = admin_url( 'edit.php?post_status=publish&post_type=page&mdnooz_default_pages=' );
        /* translators: 1: Link to create page 2: Link to dismiss */
        $message = sprintf( '<strong>%s:</strong> ', get_option( 'mdnooz_plugin_name' ) ) . sprintf( __( 'Create a “News” page? Yes, <a href="%1$s">create page</a>. No, <a href="%2$s">dismiss</a>.', 'mdnooz' ), $url . 'publish', $url . 'dismiss' );
        echo sprintf( '<div class="update-nag">%s</div>', $message );
    }

    /**
     * Admin notice that the default "News" page was created successfully.
     */
    public function show_default_page_published_admin_notice() {
        $url = admin_url( sprintf( 'post.php?post=%s&action=edit', $this->default_page_id ) );
        /* translators: 1: Link to edit page */
        $message = sprintf( '<strong>%s:</strong> ', get_option( 'mdnooz_plugin_name' ) ) . sprintf( __( '“News” page published. <a href="%1$s">Edit page</a>', 'mdnooz' ), esc_url( $url ) );
        echo sprintf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', $message );
    }

    /**
     * Create a default page that uses the [nooz] shortcode to display news content.
     */
    public function create_default_page() {
        return wp_insert_post( array (
            'post_content' => '[nooz view="auto" use_pagination="yes"]',
            'post_title'   => __( 'News', 'mdnooz' ),
            'post_name'    => 'news',
            'post_type'    => 'page',
            'post_status'  => 'publish',
        ) );
    }

    /**
     * Setup shortcode settings group.
     */
    public function add_shortcode_settings_group( $groups ) {
        array_push( $groups, array(
            'id' => 'nooz_general',
            'title' => __( 'General', 'mdnooz' ),
            'description' => __( 'These options modify the default behavior of the plugin. Additionally, you can modify each option per-shortcode.', 'mdnooz' ) . ' <a href="#" class="nooz-help-link" data-help-tab="nooz-general-options"><span class="dashicons dashicons-editor-help"></span></a>',
        ) );
        return $groups;
    }

    /**
     * Setup shortcode settings.
     */
    public function setup_shortcode_settings( $active_group ) {
        // settings page
        $cb = 'sanitize_text_field';
        register_setting( $active_group, 'mdnooz_shortcode_count', $cb );
        register_setting( $active_group, 'mdnooz_shortcode_date_format', $cb );
        register_setting( $active_group, 'mdnooz_shortcode_use_excerpt' );
        register_setting( $active_group, 'mdnooz_shortcode_use_more_link' );
        register_setting( $active_group, 'mdnooz_shortcode_more_link', $cb );
        register_setting( $active_group, 'mdnooz_shortcode_use_pagination' );
        register_setting( $active_group, 'mdnooz_shortcode_previous_link', $cb );
        register_setting( $active_group, 'mdnooz_shortcode_next_link', $cb );
        register_setting( $active_group, 'mdnooz_shortcode_theme' );
        // settings page > sections
        $active_section = 'default';
        add_settings_section( $active_section, NULL, NULL, $active_group );
        // settings page > section > fields
        add_settings_field( 'mdnooz_shortcode_count', __( 'Post Count', 'mdnooz' ), array( $this->form_fields, 'number' ), $active_group, $active_section, array(
            'class' => 'nooz-field--tiny',
            /* translators: 1: Link to WordPress Reading options */
            'description' => sprintf( __( 'The number of press releases or coverage to show. Leave this blank to use the <a href="%1$s">default posts per page</a> as set in WordPress.', 'mdnooz' ), admin_url( 'options-reading.php' ) ),
            'label_for' => 'mdnooz_shortcode_count',
            'name' => 'mdnooz_shortcode_count',
            'placeholder' => get_option( 'posts_per_page' ),
            'value' => get_option( 'mdnooz_shortcode_count' ),
            'min' => 1,
        ) );
        add_settings_field( 'mdnooz_shortcode_date_format', __( 'Date Format', 'mdnooz' ), array( $this->form_fields, 'text' ), $active_group, $active_section, array(
            'class' => 'nooz-field--tiny',
            'label_for' => 'mdnooz_shortcode_date_format',
            'name' => 'mdnooz_shortcode_date_format',
            /* translators: 1: Link to WordPress General options 2: Link to Formatting Date and Time information */
            'description' => sprintf( __( 'The date appearing for each press release and coverage. Leave this blank to use the <a href="%1$s">default date format</a> as set in WordPress. Learn more about <a href="%2$s" target="_blank">formatting dates</a>.', 'mdnooz' ), admin_url( 'options-general.php' ), 'https://codex.wordpress.org/Formatting_Date_and_Time' ),
            'value' => get_option( 'mdnooz_shortcode_date_format' ),
            'placeholder' => get_option( 'date_format' ),
        ) );
        add_settings_field( 'mdnooz_shortcode_use_excerpt', __( 'Excerpts', 'mdnooz' ), array( $this->form_fields, 'checkbox' ), $active_group, $active_section, array(
            'name' => 'mdnooz_shortcode_use_excerpt',
            'after_field' => __( 'Enable press release and coverage excerpts.', 'mdnooz' ),
            'description' => __( 'An excerpt will only be used if available for the specific press release or coverage.', 'mdnooz' ),
            'checked' => $this->is_truthy( get_option( 'mdnooz_shortcode_use_excerpt' ) ),
        ) );
        add_settings_field( 'mdnooz_shortcode_use_more_link', __( 'Call to Action', 'mdnooz' ), array( $this->form_fields, 'checkbox' ), $active_group, $active_section, array(
            'name' => 'mdnooz_shortcode_use_more_link',
            'description' => __( 'The call to action link provides a clear clickable target.', 'mdnooz' ),
            'after_field' => __( 'Enable a call to action link for each item.', 'mdnooz' ),
            'checked' => $this->is_truthy( get_option( 'mdnooz_shortcode_use_more_link' ) ),
        ) );
        add_settings_field( 'mdnooz_shortcode_more_link', NULL, array( $this->form_fields, 'text' ), $active_group, $active_section, array(
            'class' => 'nooz-field--dependency nooz-field--small',
            'name' => 'mdnooz_shortcode_more_link',
            'placeholder' => __( 'Read More', 'mdnooz' ),
            'description' => __( 'Default text for the call to action link.', 'mdnooz' ),
            'value' => get_option( 'mdnooz_shortcode_more_link' ),
            'dependency' => array(
                'name' => 'mdnooz_shortcode_use_more_link',
                'value' => '1',
            ),
        ) );
        add_settings_field( 'mdnooz_shortcode_use_pagination', __( 'Pagination', 'mdnooz' ), array( $this->form_fields, 'checkbox' ), $active_group, $active_section, array(
            'name' => 'mdnooz_shortcode_use_pagination',
            'after_field' => __( 'Enable pagination links.', 'mdnooz' ),
            'checked' => $this->is_truthy( get_option( 'mdnooz_shortcode_use_pagination' ) ),
            'description' => __( 'Add links to navigate between pages.', 'mdnooz' ),
        ) );
        add_settings_field( 'mdnooz_shortcode_pagination_links', NULL, array( $this->form_fields, 'text' ), $active_group, $active_section, array(
            'class' => 'nooz-field--dependency nooz-field--small',
            'description' => __( 'Default text for the previous and next pagination links.', 'mdnooz' ),
            'options' => array (
                array (
                    'name' => 'mdnooz_shortcode_previous_link',
                    'value' => get_option( 'mdnooz_shortcode_previous_link' ),
                    'placeholder' => __( 'Previous', 'mdnooz' ),
                ),
                array (
                    'name' => 'mdnooz_shortcode_next_link',
                    'value' => get_option( 'mdnooz_shortcode_next_link' ),
                    'placeholder' => __( 'Next', 'mdnooz' ),
                ),
            ),
            'dependency' => array(
                'name' => 'mdnooz_shortcode_use_pagination',
                'value' => '1',
            ),
        ) );
        add_settings_field( 'mdnooz_shortcode_theme', __( 'Theme', 'mdnooz' ), array( $this->form_fields, 'radio' ), $active_group, $active_section, array(
            'class' => 'nooz-field-radio',
            'name' => 'mdnooz_shortcode_theme',
            'value' => get_option( 'mdnooz_shortcode_theme' ),
            'options' => array(
                array(
                    'after_field' => 'Basic &mdash; List styled theme.',
                    'value' => 'basic',
                ),
                array(
                    'after_field' => 'Outline &mdash; Card styled theme.',
                    'value' => 'outline',
                ),
            ),
        ) );
        $option_name = 'mdnooz_hide_empty_categories';
        register_setting( $active_group, $option_name );
        add_settings_field( $option_name, __( 'Sections', 'mdnooz' ), array( $this->form_fields, 'checkbox' ), $active_group, $active_section, array(
            'name' => $option_name,
            // TODO: change "sections" to "categories" when category/tag functionality become available
            'after_field' => __( 'Hide sections that do not have content?', 'mdnooz' ),
            'description' => __( 'If no content exists within a section, the section will not be displayed.', 'mdnooz' ),
            'checked' => $this->is_truthy( get_option( $option_name ) ),
        ) );
    }

    public function _create_admin_menus()
    {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }
        global $submenu;
        $menu_slug = 'nooz';
        $parent_menu_slug = $menu_slug;
        $title = get_option( 'mdnooz_plugin_name' );
        add_menu_page( $title, $title, 'edit_posts', $menu_slug, null, 'dashicons-megaphone', '99.0100' );
        $add_new_release_text = __( 'Add New Release', 'mdnooz' );
        add_submenu_page( $parent_menu_slug, $add_new_release_text, $add_new_release_text, 'edit_posts', 'post-new.php?post_type=' . $this->release_post_type );
        // reposition "Add New" submenu item after "All Releases" submenu item
        if ( ! empty( $submenu[ $menu_slug ] ) ) {
            array_splice( $submenu[$menu_slug], 1, 0, array( array_pop( $submenu[$menu_slug] ) ) );
        }
        $add_new_coverage_text = __( 'Add New Coverage', 'mdnooz' );
        add_submenu_page( $parent_menu_slug, $add_new_coverage_text, $add_new_coverage_text, 'edit_posts', 'post-new.php?post_type=' . $this->coverage_post_type );
        $title = _x( 'Settings', 'Admin settings page', 'mdnooz' );
        add_submenu_page( $parent_menu_slug, $title, $title, 'manage_options', $menu_slug, array( $this, 'render_settings_page' ) );
    }

    /**
     * Setup primary settings page.
     */
    public function setup_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $active_group = $this->get_settings_active_group();
        /**
         * @since 1.3.0
         */
        do_action( 'nooz/settings/setup', $active_group );
        /**
         * @since 1.3.0
         * @since 1.4.0 The `$active_group` parameter was added.
         */
        do_action( 'nooz/settings/setup/group=' . $active_group, $active_group );
    }

    /**
     * Renders the settings page using a template.
     *
     * @codeCoverageIgnore
     */
    public function render_settings_page() {
        $groups = apply_filters( 'nooz/settings/groups', array() );
        foreach( $groups as $i => $group ) {
            $groups[$i]['url'] = $this->get_settings_group_url( $group['id'] );
            $groups[$i]['is_active'] = $group['id'] == $this->get_settings_active_group();
        }
        echo $this->get_template_content( dirname( __FILE__ ) . '/templates/settings.php', $groups );
    }

    /**
     * Helper function to get the active settings group.
     *
     * @return string Active settings group
     */
    protected function get_settings_active_group() {
        $active_group = isset( $_GET['group'] ) ? $_GET['group'] : 'nooz_general' ;
        return isset( $_POST['option_page'] ) ? $_POST['option_page'] : $active_group;
    }

    /**
     * Helper function to get the active settings group.
     *
     * @return string Settings page URL for a specific group
     */
    protected function get_settings_group_url( $group ) {
        $query = remove_query_arg( array( 'settings-updated', '_wpnonce' ) );
        return esc_url( add_query_arg( 'group', $group, $query ) );
    }

    public function create_cpt() {
        $menu_slug = 'nooz';
        $labels = array(
            'name'                  => _x( 'Press Releases', 'post type plural name', 'mdnooz' ),
            'singular_name'         => _x( 'Press Release', 'post type singular name', 'mdnooz' ),
            'add_new'               => _x( 'Add New', 'add new press release', 'mdnooz' ),
            'add_new_item'          => __( 'Add New Press Release', 'mdnooz' ),
            'new_item'              => __( 'New Page', 'mdnooz' ),
            'edit_item'             => __( 'Edit Press Release', 'mdnooz' ),
            'view_item'             => __( 'View Press Release', 'mdnooz' ),
            'all_items'             => __( 'All Releases', 'mdnooz' ),
            'not_found'             => __( 'No press releases found.', 'mdnooz' ),
            'not_found_in_trash'    => __( 'No press releases found in Trash.', 'mdnooz' ),
        );
        $args = array(
            'labels'                => $labels,
            'public'                => TRUE,
            'show_in_menu'          => $menu_slug,
            'rewrite'               => array( 'slug' => get_option( 'mdnooz_release_slug' ), 'with_front' => FALSE ),
            'supports'              => array( 'title', 'editor', 'excerpt', 'author', 'revisions', 'thumbnail' ),
        );
        /**
         * @deprecated 1.0.0 Use "nooz/post-types/nooz_release/options" instead.
         */
        $args = apply_filters( 'nooz_release_custom_post_type_options', $args );
        $args = apply_filters( 'nooz/post-types/nooz_release/options', $args );
        // TODO nooz/object/release/options .. nooz/register_object/nooz_release/options
        register_post_type( $this->release_post_type, $args );

        $labels = array(
            'name'                  => _x( 'Press Coverage', 'post type plural name', 'mdnooz' ),
            'singular_name'         => _x( 'Press Coverage', 'post type singular name', 'mdnooz' ),
            'add_new'               => _x( 'Add New', 'add new press coverage', 'mdnooz' ),
            'add_new_item'          => __( 'Add New Press Coverage', 'mdnooz' ),
            'new_item'              => __( 'New Press Coverage', 'mdnooz' ),
            'edit_item'             => __( 'Edit Coverage', 'mdnooz' ),
            'view_item'             => __( 'View Coverage', 'mdnooz' ),
            'all_items'             => __( 'All Coverage', 'mdnooz' ),
            'not_found'             => __( 'No press coverage found.', 'mdnooz' ),
            'not_found_in_trash'    => __( 'No press coverage found in Trash.', 'mdnooz' ),
        );
        $args = array(
            'labels'                => $labels,
            'public'                => FALSE,
            'exclude_from_search'   => FALSE,
            'publicly_queryable'    => TRUE,
            'show_ui'               => TRUE, // required because public == FALSE
            'show_in_menu'          => $menu_slug,
            'rewrite'               => array( 'slug' => get_option( 'mdnooz_coverage_slug' ), 'with_front' => FALSE ),
            'supports'              => array( 'title', 'excerpt', 'author', 'revisions', 'thumbnail' ),
        );
        /**
         * @deprecated 1.0.0 Use "nooz/post-types/nooz_coverage/options" instead.
         */
        $args = apply_filters( 'nooz_coverage_custom_post_type_options', $args );
        $args = apply_filters( 'nooz/post-types/nooz_coverage/options', $args );
        register_post_type( $this->coverage_post_type, $args );

        $this->managed_post_types = array( $this->release_post_type, $this->coverage_post_type );
        /**
         * @deprecated Use "nooz/post-types/register" or "nooz/post-types/registered" instead.
         * @since 1.6.0
         */
        $this->managed_post_types = apply_filters( 'nooz_view_post_types', $this->managed_post_types );
        $this->managed_post_types = apply_filters( 'nooz/post-types/register', $this->managed_post_types );
        update_option( 'mdnooz_managed_post_types', $this->managed_post_types );
        do_action( 'nooz/post-types/registered', $this->managed_post_types );
    }

    // templates: archive, single .. shortcode, posts, post

    // shortcode, posts, post, pagination, archive, single
    // 1) templates SHOULD BE concerned only with its own data ???
    // 2) templates SHOULD output own component, this includes its own wrapper and content

    // NOTES
    // 1) put archive_link in pagination
    // 2) ajax output needs to only return items WITHOUT a wrapper

    public function _shortcode( $atts, $content, $tag ) {
        $data = $this->get_shortcode_data( $atts, $content, $tag );
        return apply_filters( 'nooz_shortcode', __( 'Unable to load theme', 'mdnooz' ), $data );
    }

    protected function get_posts_data( $query_options, $atts ) {
        /**
         * Filters the data array before the query takes place.
         *
         * Return a non-null value to bypass plugin default post query.
         *
         * @since 1.0.0
         *
         * @param array|null $data          Return an array with posts data to short-circuit plugin query, or NULL to allow Nooz to run its normal queries.
         * @param array      $query_options The WP_Query options that will be used by Nooz.
         * @param array      $atts          Data attributes.
         */
        $data = apply_filters_ref_array( 'nooz_posts_pre_query', array( NULL, $query_options, $atts ) );
        if ( NULL !== $data ) {
            return $data;
        }
        $q = new \WP_Query( apply_filters( 'nooz_posts_query_options', $query_options, $atts ) );
        $data = array(
            'paged' => max( 1, $q->paged ),
            'max_num_pages' => $q->max_num_pages,
            'posts' => array(),
            'query' => $query_options,
        );
        if ( $q->have_posts() ) {
            global $post;
            while ( $q->have_posts() ) {
                $q->the_post();
                $item = array(
                    'post_id' => get_the_ID(),
                    'post_type' => get_post_type(),
                    'title' => get_the_title(),
                    'link_text' => NULL,
                    'link_url' => get_permalink(),
                    'link_target' => $atts['target'],
                    'date' => get_the_date( $atts['date_format'] ),
                    'timestamp' => get_the_date( 'U' ),
                    'featured_image_url' => wp_get_attachment_url( get_post_thumbnail_id() ),
                    //'wp_post' => $post,
                    'priority' => null,
                );
                if ( $atts['use_excerpt'] ) {
                    $item['excerpt'] = get_the_excerpt();
                }
                if ( $atts['use_more_link'] ) {
                    $item['link_text'] = get_post_meta( get_the_ID(), 'mdnooz_post_action', TRUE ) ?: $atts['more_link'];
                }
                array_push( $data['posts'], apply_filters_ref_array( 'nooz_post_data', array( $item, $atts ) ) );
            }
            wp_reset_postdata();
        }
        return $data;
    }

    // site: featured, post_types, latest, archives
    // archive_page: featured (single cpt), categories (groups), tags, items (list)
    // single: post_image, post_title, post_excerpt, related (list)
    // list: [title], items, pagination
    // item: featured_image, title, excerpt, link (link_url, link_target), date

    // site: featured (posts), posts
    // posts/list: post_types, items, pagination, archive_link
    // post/item: featured_image, title, excerpt, link (link_url, link_target), date

    // Override order:
    // 1) plugin settings
    // 2) shortcode atts (overrides plugin settings, on a per-instance basis)
    // 3) post-level settings
    // 4) final override with wordpress/nooz action/filters

    protected function process_atts( $atts, $tag ) {
        // filter default element_order, use the shortcode attribute to override
        $default_atts = array(
            'type' => '',
            'id' => '',
            'class' => '',
            'count' => get_option( 'mdnooz_shortcode_count' ),
            'date_format' => $this->get_shortcode_date_format(),
            /**
             * @deprecated 1.0.0 No longer used.
             */
            'display' => get_option( 'mdnooz_shortcode_display' ),
            /**
             * @deprecated 1.0.0 Use "featured_image_url" instead.
             */
            'featured_image' => '',
            'featured_image_url' => NULL,
            'more_link' => get_option( 'mdnooz_shortcode_more_link' ) ?: __( 'Read More', 'mdnooz' ),
            'previous_link' => get_option( 'mdnooz_shortcode_previous_link' ) ?: __( 'Previous', 'mdnooz' ),
            'next_link' => get_option( 'mdnooz_shortcode_next_link' ) ?: __( 'Next', 'mdnooz' ),
            'target' => NULL,
            'use_more_link' => get_option( 'mdnooz_shortcode_use_more_link' ),
            'use_excerpt' => get_option( 'mdnooz_shortcode_use_excerpt' ),
            'use_pagination' => get_option( 'mdnooz_shortcode_use_pagination' ),
            'use_archive_link' => get_option( 'mdnooz_shortcode_use_archive_link' ),
            'archive_link' => NULL,
            'archive_link_url' => NULL,
            /**
             * @deprecated 1.3.0 Element order SHOULD BE done in template or via css.
             */
            'element_order' => get_option( 'mdnooz_shortcode_item_element_order' ),
            'view' => NULL,
            'order' => NULL,
            'orderby' => NULL,
            'post_ids' => NULL,
        );

        // TIP: use "shortcode_atts_nooz" filter (https://developer.wordpress.org/reference/hooks/shortcode_atts_shortcode/)
        $atts = shortcode_atts( $default_atts, $atts, $tag );
        // compatibility (e.g. handle @deprecated atts)
        $atts['featured_image_url'] = $atts['featured_image_url'] ?: $atts['featured_image'];
        // transforms (e.g. comma-seperated-values to arrays, truthy to TRUE)
        $atts['element_order'] = array_filter( array_map( 'trim', explode( ',', $atts['element_order'] ) ) );
        $atts['use_archive_link'] = ( 'auto' == $atts['use_archive_link'] ) ? 'auto' : $this->is_truthy( $atts['use_archive_link'] );
        $atts['use_pagination'] = $this->is_truthy( $atts['use_pagination'] );
        $atts['use_excerpt'] = $this->is_truthy( $atts['use_excerpt'] );
        $atts['use_more_link'] = $this->is_truthy( $atts['use_more_link'] );
        $atts['post_ids'] = trim( $atts['post_ids'] );
        if ( $atts['post_ids'] ) {
            // explode CSV and trim
            $atts['post_ids'] = array_filter( array_map( 'trim', explode( ',', $atts['post_ids'] ) ) );
        }
        return $atts;
    }

    protected function get_shortcode_data( $atts ,$content, $tag )
    {
        $atts = $this->process_atts( $atts, $tag );

        $md_post_type = get_query_var( 'md_post_type' );

        $url_path = parse_url( get_permalink(), PHP_URL_PATH );

        /**
         * Default posts query.
         *
         * @see class-release.php, class-coverage.php
         */
        $query_post_types = array( $atts['type'] );

        $data = array(
            'id' => $atts['id'],
            'css_class' => $atts['class'],
            'type' => str_replace( 'nooz_', '', $atts['type'] ),
            'active_category' => $md_post_type ?: 'nooz_mixed',
            'post_types' => array(),
        );

        if ( 'auto' == $atts['view'] ) {
            // all (e.g. latest) post types
            $query_post_types = $this->managed_post_types;
            $data['post_types']['nooz_mixed'] = (object) array(
                'featured_image_url' => NULL,
                'title' => __( 'Latest', 'mdnooz' ),
                'excerpt' => '',
                'link_url' => $url_path,
                'link_target' => NULL,
            );
            foreach( $this->managed_post_types as $managed_post_type ) {
                /**
                 * Added option "mdnooz_hide_empty_categories", if TRUE, checks if
                 * section has a minimum of one post with `post_status=publish`.
                 *
                 * @since 1.5
                 */
                if ( get_option( 'mdnooz_hide_empty_categories' ) ) {
                    $post_counts = wp_count_posts( $managed_post_type );
                    if ( isset( $post_counts->publish ) && $post_counts->publish < 1 ) continue;
                }
                $post_type_object = get_post_type_object( $managed_post_type );
                $post_type_slug = sanitize_title( isset( $post_type_object->label ) ? $post_type_object->label : $managed_post_type );
                $post_type_slug = apply_filters( 'nooz/rewrite/category_slug', $post_type_slug, $managed_post_type );
                $data['post_types'][$managed_post_type] = (object) array(
                    'featured_image_url' => NULL,
                    'title' => $post_type_object->label,
                    'excerpt' => $post_type_object->description,
                    'link_url' => $url_path . 'category/' . $post_type_slug . '/',
                    'link_target' => NULL,
                );
            }
            $data['post_types'] = apply_filters( 'nooz_post_types_data', $data['post_types'] );
        }

        // use querystring selected post_type
        if ( in_array( $md_post_type, $query_post_types ) ) {
            $query_post_types = array( $md_post_type );
        }

        $query_options = array(
            'post_type' => $query_post_types,
            'posts_per_page' => ( '*' === $atts['count'] ) ? -1 : $atts['count'],
            'paged' => get_query_var( 'paged', 1 ),
            'order' => stristr( $atts['order'], 'asc' ) ? 'ASC' : 'DESC',
            'orderby' => $atts['orderby'] ?: 'post_date',
        );
        // specific posts only
        if ( $atts['post_ids'] ) {
            $query_options['post_type'] = array( 'any' );
            $query_options['post__in'] = $atts['post_ids'];
            $query_options['orderby'] = $atts['orderby'] ?: 'post__in';
        }

        do_action( 'nooz_shortcode_pre_query', $query_post_types );
        $posts_data = $this->get_posts_data( apply_filters( 'nooz_shortcode_query_options', $query_options ), $atts );
        do_action( 'nooz_shortcode_post_query', $query_post_types );

        $data = array_merge( $data, $posts_data );

        foreach( $data['posts'] as $key => $post_data ) {
            // set @deprecated properties for backward-compatibility
            $post_data['link'] = isset( $post_data['link_url'] ) ? $post_data['link_url'] : NULL;
            $post_data['target'] = isset( $post_data['link_target'] ) ? $post_data['link_target'] : NULL;
            $post_data['post_date'] = $post_data['post_date_formatted'] = isset( $post_data['date'] ) ? $post_data['date'] : NULL;
            $post_data['featured_image_html'] = $post_data['post_thumbnail_html'] = isset( $post_data['post_id'] ) ? get_the_post_thumbnail( $post_data['post_id'] ) : NULL;
            $post_data['image'] = isset( $post_data['featured_image_url'] ) ? $post_data['featured_image_url'] : NULL;
            $post_data['more_link'] = isset( $post_data['link_text'] ) ? $post_data['link_text'] : NULL;
            $data['posts'][$key] = $post_data;

            // shortcode $atts['featured_image_url'] will override "featured_image_url" if not set for the post
            // this will also override "mdnooz_[type]_default_image" for this shortcode instance
            if ( $atts['featured_image_url'] && empty( $post_data['featured_image_url'] ) ) {
                $post_data['featured_image_url'] = $atts['featured_image_url'];
            }

            /**
             * @deprecated 1.3.0 Element order SHOULD BE done in template or via css.
             */
            $post_data['element_order'] = apply_filters( 'nooz_shortcode_item_element_order', $atts['element_order'], $post_data['post_type'] );

            $post_data = apply_filters( 'nooz_shortcode_post_data', $post_data, $atts );
            $post_data = apply_filters( 'nooz_shortcode_data_item', $post_data, get_post( $post_data['post_id'] ) );
            $post_data = apply_filters( 'nooz_shortcode_item_data', $post_data, get_post( $post_data['post_id'] ) );
            $data['posts'][$key] = $post_data;
        }

        $data['pagination'] = $this->get_pagination_data( $data, $atts );

        $data['archive_link'] = array(
            'link_text' => null,
            'link_url' => null,
        );
        if ( ( 'auto' == $atts['use_archive_link'] && $data['max_num_pages'] > 1 ) || $this->is_truthy( $atts['use_archive_link'] ) ) {
            // viewing only a single post type or a single post type is selected
            if ( 1 == count( $query_post_types ) || in_array( $md_post_type, $query_post_types ) ) {
                $post_type_obj = get_post_type_object( $query_post_types[0] );
                /* translators: More [post_type] (plural form), e.g. More Books */
                $data['archive_link']['link_text'] = $atts['archive_link'] ?: sprintf( _x( 'More %1$s', 'More [post_type] (plural form), e.g. More Books', 'mdnooz' ), $post_type_obj->labels->name );
                $data['archive_link']['link_url'] = $atts['archive_link_url'] ?: get_post_type_archive_link( $query_post_types[0] );
            }
        }
        /**
         * @deprecated 1.0.0 See "get_pagination_data()", available for backward-compatibility.
         */
        $data['previous_posts_link'] = $data['pagination']['previous_posts_link_url'];
        $data['previous_posts_link_html'] = $data['pagination']['previous_posts_link_html'];
        $data['next_posts_link'] = $data['pagination']['next_posts_link_url'];
        $data['next_posts_link_html'] = $data['pagination']['next_posts_link_html'];
        /**
         * @deprecated 1.0.0 See "get_post_data()", available for backward-compatibility.
         */
        $data['more_link'] = $atts['use_more_link'] ? $atts['more_link'] : null;
        /**
         * @deprecated
         */
        $data['css_classes'] = $atts['class'];
        $data['items'] = $data['posts'];
        $data['atts'] = $atts;

        return apply_filters( 'nooz_shortcode_data', $data, $atts );
    }

    protected function get_pagination_data( $data, $atts ) {
        $pagination = array(
            'previous_posts_link' => null,
            'previous_posts_link_url' => null,
            'previous_posts_link_text' => null,
            'previous_posts_link_html' => null,
            'next_posts_link' => null,
            'next_posts_link_url' => null,
            'next_posts_link_text' => null,
            'next_posts_link_html' => null,
        );
        if ( ! $atts['use_pagination'] ) {
            return $pagination;
        }
        // "get_previous_posts_link()" and "get_next_posts_link()" checks page type and if prev/next page is available, only returns link if available
        // TIP: use filter: previous_posts_link_attributes
        $previous_posts_link = get_previous_posts_link( $atts['previous_link'] );
        if ( $previous_posts_link ) {
            $pagination['previous_posts_link_url'] = $pagination['previous_posts_link'] = previous_posts( false );
            $pagination['previous_posts_link_text'] = $atts['previous_link'];
            $pagination['previous_posts_link_html'] = $previous_posts_link;
        }
        // TIP: use filter: next_posts_link_attributes
        $next_posts_link = get_next_posts_link( $atts['next_link'], $data['max_num_pages'] );
        if ( $next_posts_link ) {
            $pagination['next_posts_link_url'] = $pagination['next_posts_link'] = next_posts( $data['max_num_pages'], false );
            $pagination['next_posts_link_text'] = $atts['next_link'];
            $pagination['next_posts_link_html'] = $next_posts_link;
        }
        return $pagination;
    }

    /**
     * Plugin uninstall.
     *
     * - Removes all plugin options from the database
     * - Flush rewrite rules
     *
     * @see uninstall.php
     */
    public function uninstall() {
        /**
         * Hidden option "mdnooz_delete_settings_data", set this to TRUE before
         * plugin uninstall. During uninstall, all plugin settings data will be
         * deleted.
         *
         * @since 1.5
         */
        if ( $this->is_truthy( get_option( 'mdnooz_delete_settings_data' ) ) ) {
            $this->delete_option_with_prefix( 'nooz' );
            $this->delete_option_with_prefix( 'mdnooz' );
        }
        flush_rewrite_rules();
    }
}
