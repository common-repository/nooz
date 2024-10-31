<?php

namespace MightyDev\WordPress;

class ThemesManager
{
    protected $themes_directory = array();
    protected $themes = array();
    protected $active_theme_file = null;

    public function register_themes_directory( $dir ) {
        if ( file_exists( $dir ) && ! isset( $this->themes_directory[$dir] ) ) {
            $this->themes_directory[$dir] = array(
                'directory' => $dir,
            );
        }
    }

    public function unregister_themes_directory( $dir ) {
        unset( $this->themes_directory[$dir] );
    }

    public function register_theme( $theme_file ) {
        $theme_file = $this->check_theme_file_path( $theme_file );
        if ( ! file_exists( $theme_file ) ) {
            return NULL;
        }
        if ( ! isset( $this->themes[$theme_file] ) ) {
            $this->themes[$theme_file] = array(
                'theme_file' => $theme_file,
                // HEADER DATA .. get data on use, not here on register
            );
        }
        return $theme_file;
    }

    public function activate_theme( $theme_file ) {
        $this->active_theme_file = $this->register_theme( $theme_file );
    }

    public function get_active_theme() {
        return $this->active_theme_file;
    }

    // TODO: should ThemesManager care about template_name ?
    // get_template( 'post-title' )
    // get_template( 'post-title', 'parts/post-title.php' )
    // get_template( 'post-title', $data )
    // get_template( 'post-title', 'parts/post-title.php', $data )
    public function get_template2( $template_name, $template_file = NULL, $data = NULL ) {
        if ( is_array( $template_file ) ) {
            $data = $template_file;
            $template_file = NULL;
        }
        if ( NULL === $template_file ) {
            $template_file = $template_name . '.php';
        }
        ob_start();
        // check for template_file in the theme first, then include as-is
        $possible_template_file = $this->get_theme_dir() . '/' . trim( $template_file, '/' );
        if ( file_exists( $possible_template_file ) ) {
            include $possible_template_file;
        } else if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        return ob_get_clean();
    }

    // get_template( 'post-title.php' )
    // get_template( 'parts/post-title.php', $data )
    // get_template( '/full/path/to/parts/post-title.php', $data )
    public function get_template( $template_file, $data = null ) {
        ob_start();
        // check for template_file in the theme first, then include as-is
        $possible_template_file = $this->get_theme_dir() . '/' . trim( $template_file, '/' );
        if ( file_exists( $possible_template_file ) ) {
            include $possible_template_file;
        } else if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        return ob_get_clean();
    }


    public function get_theme_dir() {
        return dirname( $this->active_theme_file );
    }

    protected function is_theme_registered( $theme_file ) {
        //return isset( $this->themes[$theme_file] );
    }

    protected function check_theme_file_path( $theme_file ) {
        $file_name = 'theme.php';
        if ( $file_name != basename( $theme_file ) ) {
            $theme_file = trailingslashit( $theme_file ) . $file_name;
        }
        return $theme_file;
    }
}
