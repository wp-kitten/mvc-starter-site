<?php

namespace Kyt\Core;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/**
 * Class MigrationBase
 * @package Kyt\Core
 */
abstract class MigrationAbstract
{
    /**
     * The name of the table handled by this migration. Include the prefix
     * @var string
     */
    protected $table = '';
    /**
     * The table prefix set in the configuration file
     * @var string
     */
    protected $prefix = '';

    /**
     * Holds the reference to the Schema to sue for table creation
     * @var null|Schema
     */
    protected $schema = null;

    /**
     * Create the table
     */
    abstract public function createTable();

    /**
     * Drop the table
     */
    abstract public function dropTable();

    /**
     * MigrationAbstract constructor.
     * @param string $tableName The name of the table
     * @throws \Exception
     */
    public function __construct( $tableName )
    {
        global $db;
        if ( !$db ) {
            throw new \Exception( 'Please setup a connection to database first.' );
        }

        $tableName = wp_kses( sanitize_file_name( $tableName ), [] );
        if ( empty( $tableName ) ) {
            throw new \Exception( 'Table name cannot be empty' );
        }

        global $dbConnectionParams;
        $this->prefix = $dbConnectionParams[ DB_INSTANCE_TYPE ][ 'prefix' ];
        $this->table = $this->prefix . $tableName;
        $this->schema = new Schema();
    }
}
