<?php if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}
/*
 * The environment configuration file
 */

//#! Various project setup stages
define( 'ENV_DEBUG', 'ENV_DEBUG' );
define( 'ENV_TEST', 'ENV_TEST' );
define( 'ENV_PRODUCTION', 'ENV_PRODUCTION' );

define( 'SITE_TITLE', 'MVC Starter Site' );
define( 'SITE_URL', 'http://localhost/mvc-starter-site' );
define( 'PUBLIC_DIR', APP_DIR . '/public' );
define( 'PUBLIC_URI', SITE_URL . '/public' );

/**
 * Holds the environment setup
 * @var string
 */
define( 'ENV_SETUP', ENV_DEBUG );

define( 'ENV_LOCALE', 'en-US' );
define( 'ENV_CHARSET', 'utf-8' );
