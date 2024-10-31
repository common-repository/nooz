<?php

require_once( __DIR__ . '/vendor/MightyDev/WordPress/functions.php' );

\MightyDev\WordPress\autoload( array(
    array( 'MightyDev\WordPress', __DIR__ . '/vendor/MightyDev/WordPress' ),
    array( 'MightyDev\WordPress', __DIR__ ),
    array( 'MightyDev\WordPress\Plugin', __DIR__ ),
    array( 'MightyDev\Nooz', __DIR__ ),
) );
