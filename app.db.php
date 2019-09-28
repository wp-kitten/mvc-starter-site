<?php if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/*
 * Database setup
 */

use \Kyt\Helpers\Logger;

global $db, $logger;

/*
 * Setup / check the database
 */
add_action( 'app/db', function () use ( $db, $logger ) {
    if ( !$db ) {
        return;
    }
    try {
        $migration = new MigrationOptions( 'options' );
        $migration->createTable();
    }
    catch ( Exception $e ) {
        $logger->write( $e->getMessage(), Logger::PLACEHOLDER, LOG_LEVEL_CRITICAL );
    }
} );
