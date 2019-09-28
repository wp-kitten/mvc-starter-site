<?php
/*
 * Application's setup file
 * Includes and instantiates the required classes
 */

/**
 * @package Kyt
 * @version v0.1
 * @author wp-kitten (https://github.com/wp-kitten)
 */

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Kyt\Core\WP_Hook;
use Slab\Session\Driver;
use Slab\Session\Handlers\Database\MySQL;
use Slab\Session\Handlers\File;
use Kyt\Core\App;
use Kyt\Helpers\DirectoryAutoloader;
use Kyt\Helpers\Logger;

require_once( dirname( __FILE__ ) . '/app.constants.php' );

if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

//#! Create the _session & log directories
if ( !is_dir( APP_DIR . '/_session' ) ) {
    mkdir( APP_DIR . '/_session', 0777 );
}
if ( !is_dir( APP_DIR . '/_log' ) ) {
    mkdir( APP_DIR . '/_log', 0777 );
}

require_once( APP_DIR . '/vendor/autoload.php' );
require_once( APP_DIR . '/core/autoload.php' );

global
/**@var WP_Hook|array|null $wp_filter */
$wp_filter,
    /**@var array $wp_actions */
$wp_actions,
    /**@var array $wp_current_filter */
$wp_current_filter;

$wp_filter = ( $wp_filter ? WP_Hook::build_preinitialized_hooks( $wp_filter ) : [] );
$wp_actions = ( isset( $wp_actions ) ? $wp_actions : [] );
$wp_current_filter = ( isset( $wp_current_filter ) ? $wp_current_filter : [] );
require_once( APP_DIR . '/app.functions.php' );

//#! Load configuration files
require_once( APP_DIR . '/config/env.config.php' );
require_once( APP_DIR . '/config/logger.config.php' );
require_once( APP_DIR . '/config/db.config.php' );
require_once( APP_DIR . '/config/session.config.php' );
require_once( APP_DIR . '/config/mvc.config.php' );

//#! Setup the environment error reporting
if ( ENV_SETUP == ENV_PRODUCTION ) {
    ini_set( 'display_errors', '0' );
    ini_set( 'display_startup_errors', '0' );
    error_reporting( 0 );
}
else {
    ini_set( 'display_errors', '1' );
    ini_set( 'display_startup_errors', '1' );
    error_reporting( -1 );
}

/*
 * GLOBAL VARS
 * ===========================================================
 * 		$logger => \Kyt\Helpers\Logger
 * 		$db => \Doctrine\DBAL\Connection
 * 		$sessionDriver => \Slab\Session\Driver
 * ===========================================================
 */

//#! Initialize the classes
try {
    /*
     * Setup logging
     */
    /**
     * Holds the reference to the instance of the Logger class
     * @var \Kyt\Helpers\Logger $logger
     */
    $logger = Logger::getInstance( LOG_ENABLE, $configLogLevels[ ENV_SETUP ] );

    /**
     * Holds the reference to the instance of the Doctrine\DBAL\Connection class
     * @var \Doctrine\DBAL\Connection $db
     */
    $db = DriverManager::getConnection( $dbConnectionParams[ DB_INSTANCE_TYPE ], new Configuration() );
    if ( !$db->isConnected() ) {
        $db = null;
    }

    /**
     * Instantiate the Session class
     */
    $session = null;
    $sessionDriver = null;
    if ( SESSION_HANDLER == SESSION_HANDLER_FILE ) {
        $sessionHandler = new File();
        $sessionHandler->setSavePath( $sessionConfig[ SESSION_INSTANCE_TYPE ][ 'file' ][ 'file_path' ] );
    }
    elseif ( SESSION_HANDLER == SESSION_HANDLER_DATABASE ) {
        $sessionHandler = new MySQL();
        $cfgEntry = $sessionConfig[ SESSION_INSTANCE_TYPE ][ 'database' ];
        $sessionHandler->setDatabase( $GLOBALS[ 'db' ], $cfgEntry[ 'db_name' ], $cfgEntry[ 'table_name' ], $cfgEntry[ 'site_name' ] );
    }

    if ( $sessionHandler ) {
        $sessionDriver = new Driver();
        $sessionDriver
            ->setHandler( $sessionHandler )
            ->start();
        $session = $sessionDriver;
    }
}
catch ( Exception $e ) {
    error_log( '[Application Start] An error occurred: ' . $e->getMessage() );
}

//#! Core classes autoloader
DirectoryAutoloader::setPath( APP_DIR . '/classes' );
DirectoryAutoloader::setFileExt( '.php' );
spl_autoload_register( [ 'Kyt\\Helpers\\DirectoryAutoloader', 'loader' ] );

/**
 * Event triggered so the application can check the database
 */
require_once( APP_DIR . '/app.db.php' );
do_action( 'app/db' );

/*
 * Instantiate the Application class
 */
$app = App::getInstance();

//#! Instantiate the Router
$router = new Router();

//#! Load the global.functions.php file from views if exists
$functionsFilePath = MVC_VIEWS_PATH . '/global.functions.php';
if ( is_file( $functionsFilePath ) ) {
    require_once( $functionsFilePath );
}

//#! Load the functions.php file from views, depending on the section loaded
//#! so we can hook into events
$request = $router->request;
if ( $request->isAdmin ) {
    $functionsFilePath = MVC_VIEWS_PATH . '/admin/functions.php';
}
else {
    $functionsFilePath = MVC_VIEWS_PATH . '/frontend/functions.php';
}
if ( is_file( $functionsFilePath ) ) {
    require_once( $functionsFilePath );
}

/*
 * Register app events
 * =====================================
 */
//#! Emit the app/loaded event
do_action( 'app/loaded' );

add_action( 'app/frontend/head', function () {
    do_action( 'print-styles' );
    do_action( 'print-scripts' );
} );
add_action( 'app/frontend/footer', function () {
    do_action( 'print-scripts' );
} );

