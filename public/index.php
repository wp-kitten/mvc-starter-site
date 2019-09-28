<?php
/*
 * The main entry point of the application
 */
require_once( dirname( __FILE__ ) . '/../app.load.php' );

if ( !defined( 'APP_DIR' ) ) {
    exit( 'Invalid request.' );
}

/**@var Router $router */
global $router;
$router->dispatch();
