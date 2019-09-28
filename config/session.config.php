<?php if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}
/*
 * The session configuration file
 *
 * @see https://github.com/SlabPHP/session-manager
 */

//#! Option session handlers
define( 'SESSION_HANDLER_FILE', 'FILE' );
define( 'SESSION_HANDLER_DATABASE', 'DATABASE' );

/**
 * Holds the selected session handler to instantiate
 */
define( 'SESSION_HANDLER', SESSION_HANDLER_FILE );

/**
 * Set the connection param key to use. Valid values: see $sessionConfig keys
 * @var string
 * @see $sessionConfig
 */
define( 'SESSION_INSTANCE_TYPE', 'localhost' );


$sessionConfig = [
	'localhost' => [
		'file' => [
			'file_path' => APP_DIR . '/_session'
		],
		'database' => [
			'db_name' => '',
			'table_name' => '',
			'site_name' => '',
		]
	],
	'remote' => [
		'file' => [
			'file_path' => ini_get( 'session.save_path' )
		],
		'database' => [
			'db_name' => '',
			'table_name' => '',
			'site_name' => '',
		]
	],
	'production' => [
		'file' => [
			'file_path' => APP_DIR . '/_session'
		],
		'database' => [
			'db_name' => '',
			'table_name' => '',
			'site_name' => '',
		]
	],
];


