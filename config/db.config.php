<?php if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}
/*
 * The application's database configuration file
 *
 * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/index.html
 */

/**
 * Set the connection param key to use. Valid values: see $dbConnectionParams keys
 * @var string
 * @see $dbConnectionParams
 */
define( 'DB_INSTANCE_TYPE', 'localhost' );

/**
 * Holds the configuration data for the database setup
 */
$dbConnectionParams = [
	'localhost' => [
		'dbname' => '',
		'user' => '',
		'password' => '',
		'host' => 'localhost',
		'driver' => 'pdo_mysql',
		'prefix' => '',
	],
	'production' => [
		'dbname' => '',
		'user' => '',
		'password' => '',
		'host' => '',
		'driver' => 'pdo_mysql',
		'prefix' => '',
	],
	'remote' => [
		'dbname' => '',
		'user' => '',
		'password' => '',
		'host' => '',
		'driver' => 'pdo_mysql',
		'prefix' => '',
	],
	'test' => [
		'dbname' => '',
		'user' => '',
		'password' => '',
		'host' => '',
		'driver' => 'pdo_mysql',
		'prefix' => '',
	],
];

