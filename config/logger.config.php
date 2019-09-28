<?php if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}
/*
 * The application's logging configuration file
 */

/**
 * Whether to enable logging or not
 * @var bool
 */
define( 'LOG_ENABLE', true );

/**
 * Holds the system path to the logging directory
 * @var string
 */
define( 'LOG_DIR_PATH', APP_DIR . '/_log' );

//#! Logging levels
define( 'LOG_LEVEL_DEBUG', 0 );
define( 'LOG_LEVEL_INFO', 1 );
define( 'LOG_LEVEL_WARN', 2 );
define( 'LOG_LEVEL_ERROR', 3 );
define( 'LOG_LEVEL_SYSTEM', 4 );
define( 'LOG_LEVEL_CRITICAL', 5 );

/**
 * The list of all available log levels and their corresponding translation
 */
$configLogLevels = [
    'translation' => [
        //#! level => translation
        LOG_LEVEL_DEBUG => 'debug',
        LOG_LEVEL_INFO => 'info',
        LOG_LEVEL_WARN => 'warn',
        LOG_LEVEL_ERROR => 'error',
        LOG_LEVEL_SYSTEM => 'system',
        LOG_LEVEL_CRITICAL => 'critical',
    ],
    //#! Set the logging level based on the project environment setup
    ENV_DEBUG => LOG_LEVEL_DEBUG,
    ENV_TEST => LOG_LEVEL_DEBUG,
    //#! Will log: warn, error, system, critical
    ENV_PRODUCTION => LOG_LEVEL_WARN,

];
