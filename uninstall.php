<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    status_header( 404 );
    exit;
}

require_once( __DIR__ . '/inc/autoload.php' );
\MightyDev\Nooz\Admin::get_instance()->uninstall();
