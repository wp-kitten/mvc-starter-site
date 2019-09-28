<?php if ( ! defined( 'APP_DIR' ) ) {
	exit( 'No direct access please.' );
}

use Kyt\Core\MigrationAbstract;
use Kyt\Helpers\Logger;
use \Doctrine\DBAL\Types\Type;
use \Doctrine\DBAL\Platforms\MySqlPlatform;


/**
 * Class MigrationOptions
 *
 * Creates the options table
 */
class MigrationOptions extends MigrationAbstract
{
	/**
	 * MigrationAbstract constructor.
	 * @param string $tableName The name of the table
	 * @throws \Exception
	 */
	public function __construct( $tableName )
	{
		parent::__construct( $tableName );

		//#! Define the schema for the table
		$myTable = $this->schema->createTable( $this->table );
		$myTable->addColumn( "id", Type::BIGINT, [ "autoincrement" => true ] );
		$myTable->addColumn( "option_name", Type::STRING, [ "length" => 125 ] );
		$myTable->addColumn( "option_value", Type::TEXT );
		$myTable->addColumn( "autoload", Type::INTEGER, [ "length" => 1 ] );

		$myTable->setPrimaryKey( [ "id" ] );
		$myTable->addIndex( [ 'option_name' ] );
	}


	/**
	 * Helper method to create the table
	 */
	public function createTable()
	{
		$platform = new MySqlPlatform();
		$queries = $this->schema->toSql( $platform ); // get queries to create this schema.

		//#! Create the table
		if ( $queries ) {
			global $db, $logger;
			foreach ( $queries as $query ) {
				try {
					$db->query( $query );
				}
				catch ( Exception $e ) {
                    $logger->write( $e->getMessage(), Logger::PLACEHOLDER, LOG_LEVEL_CRITICAL );
				}
			}
		}
	}

	/**
	 * Helper method to drop the table
	 */
	public function dropTable()
	{
		if ( $this->schema ) {
			global $db, $logger;
			$platform = new MySqlPlatform();
			$dropQueries = $this->schema->toDropSql( $platform ); // get queries to safely delete this schema
			if ( $dropQueries ) {
				foreach ( $dropQueries as $query ) {
					try {
						$db->query( $query );
					}
					catch ( \Exception $e ) {
                        $logger->write( $e->getMessage(), Logger::PLACEHOLDER, LOG_LEVEL_CRITICAL );
					}
				}
			}
		}
	}

}
