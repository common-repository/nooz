<?php

namespace MightyDev\Nooz;

class Upgrade
{
    public function setup() {
        $this->set_plugin_version();
        $this->set_shortcode_theme();
        add_action( 'init', array( $this, 'upgrade_default_image' ) );
        add_action( 'init', array( $this, 'upgrade_post_priority' ) );
        add_action( 'init', array( $this, 'upgrade_post_version' ) );
        add_action( 'init', array( $this, 'upgrade_release_subheadline' ) );
        add_action( 'init', array( $this, 'upgrade_coverage_post_meta' ) );
        add_action( 'init', array( $this, 'upgrade_coverage_link' ) );
        add_action( 'wp_loaded', array( $this, 'upgrade_flush_rewrite_rules' ) );
    }

    /**
     * Used to flush rewrite rules necessitated by a change. $nonce is changed to
     * trigger the flush.
     */
    public function upgrade_flush_rewrite_rules() {
        $nonce = '20200207';
        if ( $nonce == get_option( 'mdnooz_flush_rewrite_rules_nonce' ) ) return;
        update_option( 'mdnooz_flush_rewrite_rules_nonce', $nonce );
        flush_rewrite_rules();
    }

    /**
     * Set current plugin version number. This number will be converted to
     * "mdnooz_plugin_previous_version" when Nooz setup() is run.
     */
    public function set_plugin_version() {
        if ( FALSE !== get_option( 'mdnooz_plugin_version' ) ) return;
        // if existing plugin install
        if ( FALSE !== get_option( 'mdnooz_release_slug' ) ) {
            // set last version (even though a user can be upgrading from an older version)
            update_option( 'mdnooz_plugin_version', '0.14.1' );
            update_option( 'mdnooz_plugin_previous_version', '0.14.1' );
        }
    }

    /**
     * Set theme to "basic" if existing installation.
     */
    public function set_shortcode_theme() {
        if ( FALSE !== get_option( 'mdnooz_shortcode_theme' ) ) return;
        if ( FALSE !== get_option( 'mdnooz_plugin_previous_version' ) && version_compare( get_option( 'mdnooz_plugin_previous_version' ), '1.0', '<' ) ) {
            update_option( 'mdnooz_shortcode_theme', 'basic' );
        }
    }

    /**
     * Change "mdnooz_upgrade_default_image" (media ID) to "mdnooz_upgrade_default_image_url" (media URL).
     */
    public function upgrade_default_image() {
        if ( ! get_option( 'mdnooz_upgrade_default_image' ) ) {
            $media_url = wp_get_attachment_url( get_option( 'mdnooz_release_default_image' ) );
            if ( $media_url ) {
                update_option( 'mdnooz_release_default_image_url', $media_url );
            }
            $media_url = wp_get_attachment_url( get_option( 'mdnooz_coverage_default_image' ) );
            if ( $media_url ) {
                update_option( 'mdnooz_coverage_default_image_url', $media_url );
            }
            update_option( 'mdnooz_upgrade_default_image', time() );
        }
    }

    /**
     * Remove "_mdnooz_post_priority" postmeta, reset menu_order.
     */
    public function upgrade_post_priority() {
        if ( ! get_option( 'mdnooz_upgrade_post_priority' ) ) {
            $q = new \WP_Query( array(
                'post_type' => array( 'nooz_release', 'nooz_coverage' ),
                'post_status' => 'any',
                'posts_per_page' => -1,
            ) );
            foreach( $q->posts as $post ) {
                if ( 'pinned' == get_post_meta( $post->ID, '_mdnooz_post_priority', TRUE ) && 0 == $post->menu_order ) {
                    wp_update_post( array(
                        'ID' => $post->ID,
                        'menu_order' => 1,
                    ) );
                }
            }
            // delete after update_post_meta, encountered strange behavior when delete_post_meta was in the same loop above
            foreach( $q->posts as $post ) {
                delete_post_meta( $post->ID, '_mdnooz_post_priority' );
            }
            update_option( 'mdnooz_upgrade_post_priority', time() );
        }
    }

    /**
     * Tag all existing release and coverage posts with version 0.14.1
     */
    public function upgrade_post_version() {
        if ( ! get_option( 'mdnooz_upgrade_post_version' ) ) {
            $q = new \WP_Query( array(
                'post_type' => array( 'nooz_release', 'nooz_coverage' ),
                'post_status' => 'any',
                'posts_per_page' => -1,
            ) );
            foreach( $q->posts as $post ) {
                update_post_meta( $post->ID, '_mdnooz_version', '0.14.1' );
            }
            update_option( 'mdnooz_upgrade_post_version', time() );
        }
    }

    /**
     * Change "_nooz_release[subheadline]" into "_mdnooz_subheadline". Existing
     * data is intentionally not deleted.
     */
    public function upgrade_release_subheadline() {
        if ( ! get_option( 'mdnooz_upgrade_release_subheadline' ) ) {
            $q = new \WP_Query( array(
                'post_type' => array( 'nooz_release' ),
                'post_status' => 'any',
                'posts_per_page' => -1,
            ) );
            foreach( $q->posts as $post ) {
                $post_meta = get_post_meta( $post->ID, '_nooz_release', TRUE );
                if ( ! empty( $post_meta['subheadline'] ) ) {
                    update_post_meta( $post->ID, '_mdnooz_subheadline', $post_meta['subheadline'] );
                }
            }
            update_option( 'mdnooz_upgrade_release_subheadline', time() );
        }
    }

    /**
     * Upgrade post meta data.
     * Existing data is intentionally not deleted.
     */
    public function upgrade_coverage_post_meta() {
        if ( ! get_option( 'mdnooz_upgrade_coverage_post_meta' ) ) {
            $q = new \WP_Query( array(
                'post_type' => array( 'nooz_coverage' ),
                'post_status' => 'any',
                'posts_per_page' => -1,
            ) );
            foreach( $q->posts as $post ) {
                $post_meta = get_post_meta( $post->ID, '_nooz', TRUE );
                if ( isset( $post_meta['link'] ) ) {
                    update_post_meta( $post->ID, '_mdnooz_link', array(
                        'url' => $post_meta['link'],
                        'target' => '_blank',
                    ) );
                }
                if ( isset( $post_meta['source'] ) ) {
                    update_post_meta( $post->ID, '_mdnooz_source', $post_meta['source'] );
                }
            }
            // leave old data "_nooz" as-is
            update_option( 'mdnooz_upgrade_coverage_post_meta', time() );
        }
    }

    /**
     * Split "_mdnooz_link" into "_mdnooz_link_url" and "_mdnooz_link_target".
     * Existing data is intentionally not deleted.
     */
    public function upgrade_coverage_link() {
        if ( ! get_option( 'mdnooz_upgrade_coverage_link' ) ) {
            $q = new \WP_Query( array(
                'post_type' => array( 'nooz_coverage' ),
                'post_status' => 'any',
                'posts_per_page' => -1,
            ) );
            foreach( $q->posts as $post ) {
                if ( ! metadata_exists( 'post', $post->ID, '_mdnooz_link' ) ) continue;
                $post_meta = get_post_meta( $post->ID, '_mdnooz_link', TRUE );
                update_post_meta( $post->ID, '_mdnooz_link_url', isset( $post_meta['url'] ) ? $post_meta['url'] : '' );
                update_post_meta( $post->ID, '_mdnooz_link_target', isset( $post_meta['target'] ) ? $post_meta['target'] : '' );
            }
            update_option( 'mdnooz_upgrade_coverage_link', time() );
        }
    }
}
