<?php if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}
/*
 * The mvc configuration file
 */

define( 'MVC_DIR_PATH', APP_DIR . '/classes/mvc' );
define( 'MVC_VIEWS_PATH', APP_DIR . '/public/views' );

//#! Holds the base name of the directory storing the application (mainly on localhost)
define( 'MVC_BASE_URL', '/mvc-starter-site/' );
//
define( 'MVC_DEFAULT_CONTROLLER', 'Home' );
define( 'MVC_DEFAULT_ACTION', 'index' );

//#! The name of the default layout to use
define( 'MVC_DEFAULT_LAYOUT', 'default' );

//#! Whether or not there is an admin area
define( 'MVC_USE_ADMIN', true );
//#! The name of the directory that will store the controllers used in the admin area (if any)
define( 'MVC_ADMIN_DIR_NAME', 'admin' );

