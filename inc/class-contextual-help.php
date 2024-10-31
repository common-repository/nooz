<?php

namespace MightyDev\Nooz;

class ContextualHelp extends Base
{
    protected $active_help_group;

    /**
     * @codeCoverageIgnore
     */
    public function register() {
        add_action( 'admin_head', array( $this, '_add_help' ) );
    }

    protected function is_post_type( $post_type ) {
        if ( isset( $_GET['post_type'] ) ) {
            return $post_type == $_GET['post_type'];
        } else {
            global $post;
            return isset( $post->post_type ) && $post_type == $post->post_type;
        }
    }

    protected function is_admin_page( $page, $group = NULL ) {
        global $pagenow;
        $is_page = isset( $pagenow ) && 'admin.php' == $pagenow && isset( $_GET['page'] ) && $page == $_GET['page'];
        if ( ! is_null( $group ) ) {
            return $is_page && isset( $_GET['group'] ) && $group == $_GET['group'];
        }
        return $is_page;
    }

    protected function is_post_type_list_page() {
        global $pagenow;
        return isset( $pagenow ) && 'edit.php' == $pagenow;
    }

    protected function is_post_type_add_page() {
        global $pagenow;
        return isset( $pagenow ) && 'post-new.php' == $pagenow;
    }

    protected function is_post_type_edit_page() {
        global $pagenow;
        return isset( $pagenow ) && 'post.php' == $pagenow;
    }

    public function is_release_list_page() {
        return $this->is_post_type( 'nooz_release' ) && $this->is_post_type_list_page();
    }

    public function is_release_add_page() {
        return $this->is_post_type( 'nooz_release' ) && $this->is_post_type_add_page();
    }

    public function is_release_edit_page() {
        return $this->is_post_type( 'nooz_release' ) && $this->is_post_type_edit_page();
    }

    public function is_coverage_list_page() {
        return $this->is_post_type( 'nooz_coverage' ) && $this->is_post_type_list_page();
    }

    public function is_coverage_add_page() {
        return $this->is_post_type( 'nooz_coverage' ) && $this->is_post_type_add_page();
    }

    public function is_coverage_edit_page() {
        return $this->is_post_type( 'nooz_coverage' ) && $this->is_post_type_edit_page();
    }

    public function is_settings_page( $group = NULL ) {
        return $this->is_admin_page( 'nooz', $group );
    }

    public function is_plugin_page() {
        return $this->is_release_list_page()
            || $this->is_release_add_page()
            || $this->is_release_edit_page()
            || $this->is_coverage_list_page()
            || $this->is_coverage_add_page()
            || $this->is_coverage_edit_page()
            || $this->is_settings_page();
    }

    public function set_active_help_group( $group ) {
        $this->active_help_group = $group;
    }

    public function render_active_help_group() {
        // https://core.trac.wordpress.org/browser/tags/4.2.2/src/wp-admin/includes/screen.php#L862
        if ( ! empty( $this->active_help_group ) ) {
            ?><script>
                jQuery(function($) {
                    setTimeout(function() {
                        $('#tab-link-<?php echo $this->active_help_group; ?> a').trigger('click');
                        $('#contextual-help-wrap').addClass('nooz-contextual-help');
                    }, 500);
                });
            </script><?php
        }
    }

    public function _add_help() {
        if ( $this->is_plugin_page() ) {
            $screen = get_current_screen();
            $screen->add_help_tab( array(
                'id' => 'nooz-general-options',
                'title' => __( 'General Options', 'mdnooz' ),
                'content' => file_get_contents( plugin_dir_path( MDNOOZ_PLUGIN_FILE ) . '/help/general-options.html' ),
            ) );
            $screen->add_help_tab( array(
                'id' => 'nooz-release-options',
                'title' => __( 'Press Release Options', 'mdnooz' ),
                'content' => file_get_contents( plugin_dir_path( MDNOOZ_PLUGIN_FILE ) . '/help/release-options.html' ),
            ) );
            $screen->add_help_tab( array(
                'id' => 'nooz-coverage-options',
                'title' => __( 'Press Coverage Options', 'mdnooz' ),
                'content' => file_get_contents( plugin_dir_path( MDNOOZ_PLUGIN_FILE ) . '/help/coverage-options.html' ),
            ) );
            $screen->add_help_tab( array(
                'id' => 'nooz-shortcode-options',
                'title' => __( 'Shortcode Usage', 'mdnooz' ),
                'content' => file_get_contents( plugin_dir_path( MDNOOZ_PLUGIN_FILE ) . '/help/shortcodes.html' ),
            ) );
            $screen->set_help_sidebar(
                sprintf( '<p><strong>%s</strong></p>', __( 'For more information:', 'mdnooz' ) ) .
                sprintf( '<p><a href="%s" target="_blank">%s</a></p>', 'https://wordpress.org/plugins/nooz/faq/', __( 'Frequently Asked Questions', 'mdnooz' ) ) .
                sprintf( '<p><a href="%s" target="_blank">%s</a></p>', 'https://wordpress.org/support/plugin/nooz/', __( 'Support Forums', 'mdnooz' ) )
            );
            // release list, coverage list
            $this->set_active_help_group( 'nooz-shortcode-options' );
            if ( $this->is_settings_page() ) {
                $this->set_active_help_group( 'nooz-general-options' );
            }
            if ( $this->is_settings_page( 'nooz_release' ) || $this->is_release_add_page() || $this->is_release_edit_page() ) {
                $this->set_active_help_group( 'nooz-release-options' );
            }
            if ( $this->is_settings_page( 'nooz_coverage' ) || $this->is_coverage_add_page() || $this->is_coverage_edit_page() ) {
                $this->set_active_help_group( 'nooz-coverage-options' );
            }
            $this->render_active_help_group();
        }
    }
}
