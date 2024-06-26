<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Plugin\Phinx;

use Cake\Database\Query;
use Phinx\Db\Adapter\AbstractAdapter;
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Db\Table\Table;
use Phinx\Db\Table as DbTable;
use Phinx\Db\Table\Column;
use Phinx\Migration\MigrationInterface;
use Phinx\Util\Literal;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WordPressAdapter extends AbstractAdapter {
	public function __construct( array $options, ?InputInterface $input = null, ?OutputInterface $output = null ) {
		parent::__construct( $options, $input, $output );

		// MySQLの処理として使用可能なものはこのクラスオブジェクトから取得する。
		// $this->mysql_adapter = new MysqlAdapter($options, $input, $output);
		$this->mysql_adapter = new MysqlAdapterEx( $this, $options );

		$this->connection = new WPConnection();

		$this->createSchemaTableIfNotExists();
	}

	/** @var MysqlAdapter */
	private $mysql_adapter;

	private $connection;

	public function getConnection() {
		return $this->connection;
	}

	public function getVersionLog(): array {
		$version_log = $this->mysql_adapter->getVersionLog();
		return $version_log;
	}

	public function migrated( MigrationInterface $migration, string $direction, string $startTime, string $endTime ) {
		$this->mysql_adapter->migrated( $migration, $direction, $startTime, $endTime );
		return $this;
	}

	public function toggleBreakpoint( MigrationInterface $migration ) {
		throw new \Exception( 'Not implemented' );
	}

	public function resetAllBreakpoints(): int {
		throw new \Exception( 'Not implemented' );
	}

	public function setBreakpoint( MigrationInterface $migration ) {
		throw new \Exception( 'Not implemented' );
	}

	public function unsetBreakpoint( MigrationInterface $migration ) {
		throw new \Exception( 'Not implemented' );
	}

	public function disconnect(): void {
		throw new \Exception( 'Not implemented' );
	}

	public function hasTransactions(): bool {
		return $this->mysql_adapter->hasTransactions();
	}

	public function beginTransaction(): void {
		$this->execute( 'START TRANSACTION' );
	}

	public function commitTransaction(): void {
		$this->execute( 'COMMIT' );
	}

	public function rollbackTransaction(): void {
		$this->execute( 'ROLLBACK' );
	}

	public function execute( string $sql, array $params = array() ): int {

		if ( empty( $params ) ) {
			$ret = $this->getConnection()->exec( $sql );
		} else {
			$ret = $this->getConnection()->exec( $this->getConnection()->prepare( $sql, $params ) );
		}

		if ( false === $ret ) {
			throw new RuntimeException( 'Failed to execute SQL: ' . $sql );
		}
		return true === $ret ? 0 : $ret;
	}

	public function executeActions( Table $table, array $actions ): void {
		throw new \Exception( 'Not implemented' );
	}

	public function getQueryBuilder(): Query {
		throw new \Exception( 'Not implemented' );
	}

	public function fetchRow( string $sql ) {
		$row = $this->getConnection()->fetchRow( $sql );
		return $row;
	}

	public function insert( Table $table, array $row ): void {
		throw new \Exception( 'Not implemented' );
	}

	public function bulkinsert( Table $table, array $rows ): void {
		throw new \Exception( 'Not implemented' );
	}

	public function quoteTableName( string $tableName ): string {
		throw new \Exception( 'Not implemented' );
	}

	public function quoteColumnName( string $columnName ): string {
		throw new \Exception( 'Not implemented' );
	}

	public function hasTable( string $tableName ): bool {
		return $this->mysql_adapter->hasTable( $tableName );
	}

	public function createTable( Table $table, array $columns = array(), array $indexes = array() ): void {
		$this->mysql_adapter->createTable( $table, $columns, $indexes );
	}

	public function truncateTable( string $tableName ): void {
		throw new \Exception( 'Not implemented' );
	}

	public function getColumns( string $tableName ): array {
		throw new \Exception( 'Not implemented' );
	}

	public function hasColumn( string $tableName, string $columnName ): bool {
		return $this->mysql_adapter->hasColumn( $tableName, $columnName );
	}

	public function hasIndex( string $tableName, $columns ): bool {
		throw new \Exception( 'Not implemented' );
	}

	public function hasIndexByName( string $tableName, string $indexName ): bool {
		throw new \Exception( 'Not implemented' );
	}

	public function hasPrimaryKey( string $tableName, $columns, ?string $constraint = null ): bool {
		throw new \Exception( 'Not implemented' );
	}

	public function hasForeignKey( string $tableName, $columns, ?string $constraint = null ): bool {
		throw new \Exception( 'Not implemented' );
	}

	public function getColumnTypes(): array {
		return $this->mysql_adapter->getColumnTypes();
	}

	public function getSqlType( $type, ?int $limit = null ): array {
		throw new \Exception( 'Not implemented' );
	}

	public function createDatabase( string $name, array $options = array() ): void {
		throw new \Exception( 'Not implemented' );
	}

	public function hasDatabase( string $name ): bool {
		throw new \Exception( 'Not implemented' );
	}

	public function dropDatabase( string $name ): void {
		throw new \Exception( 'Not implemented' );
	}

	public function createSchema( string $schemaName = 'public' ): void {
		throw new \Exception( 'Not implemented' );
	}

	public function dropSchema( string $schemaName ): void {
		throw new \Exception( 'Not implemented' );
	}

	public function castToBool( $value ) {
		throw new \Exception( 'Not implemented' );
	}

	public function query( string $sql, array $params = array() ) {
		throw new \Exception( 'Not implemented' );
	}

	public function fetchAll( string $sql ): array {
		return $this->getConnection()->fetchAll( $sql );
	}

	public function connect(): void {
		// throw new \Exception("Not implemented");
		// ここは何もしない
	}



	private function createSchemaTableIfNotExists() {
		// phpcs:disable
		// FROM: Phinx\Db\Adapter\PdoAdapter::setConnection

        // Create the schema table if it doesn't already exist
        if (!$this->hasTable($this->getSchemaTableName())) {
            $this->createSchemaTable();
        } else {
            $table = new DbTable($this->getSchemaTableName(), [], $this);
            if (!$table->hasColumn('migration_name')) {
                $table
                    ->addColumn(
                        'migration_name',
                        'string',
                        ['limit' => 100, 'after' => 'version', 'default' => null, 'null' => true]
                    )
                    ->save();
            }
            if (!$table->hasColumn('breakpoint')) {
                $table
                    ->addColumn('breakpoint', 'boolean', ['default' => false, 'null' => false])
                    ->save();
            }
        }

        return $this;
		// phpcs:enable
	}
}




class WPConnection {

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}
	private $wpdb;

	public function quote( $value ) {
		return $this->wpdb->prepare( '%s', $value );
	}

	/**
	 *
	 * @param string $sql
	 * @return int|bool
	 */
	public function exec( string $sql ) {
		return $this->wpdb->query( $sql );
	}

	/**
	 *
	 * @param string $sql
	 * @param array  $args
	 * @return string|void
	 */
	public function prepare( string $sql, array $args ) {
		assert( is_array( $args ) );
		assert( count( $args ) > 0 );

		return $this->wpdb->prepare( $sql, $args );
	}

	public function fetchAll( string $sql ) {
		return $this->wpdb->get_results( $sql, ARRAY_A );
	}

	public function fetchRow( string $sql ) {
		return $this->wpdb->get_row( $sql, ARRAY_A );
	}
}




class MysqlAdapterEx extends MysqlAdapter {
	public function __construct( WordPressAdapter $wp_adapter, array $options ) {
		parent::__construct( $options, null, null );
		$this->wp_adapter = $wp_adapter;
	}
	private $wp_adapter;

	public function connect(): void {
		$this->wp_adapter->connect();
	}

	public function execute( string $sql, array $params = array() ): int {
		return $this->wp_adapter->execute( $sql, $params );
	}

	public function fetchRow( string $sql ) {
		return $this->wp_adapter->fetchRow( $sql );
	}

	public function fetchAll( string $sql ): array {
		return $this->wp_adapter->fetchAll( $sql );
	}

	// public function getAttribute( int $attribute ) {
	// assert( $attribute === \PDO::ATTR_SERVER_VERSION );
	// global $wpdb;
	// $db_version = $wpdb->db_version();
	// return $db_version;
	// }


	/**
	 * Gets the MySQL Column Definition for a Column object.
	 *
	 * この関数の処理は、Phinx\Db\Adapter\MysqlAdapter::getColumnSqlDefinition()からコピーし、一部変更を加えています。
	 *
	 * @param \Phinx\Db\Table\Column $column Column
	 * @return string
	 */
	protected function getColumnSqlDefinition( Column $column ): string {
		if ( $column->getType() instanceof Literal ) {
			$def = (string) $column->getType();
		} else {
			$sqlType = $this->getSqlType( $column->getType(), $column->getLimit() );
			$def     = strtoupper( $sqlType['name'] );
		}
		if ( $column->getPrecision() && $column->getScale() ) {
			$def .= '(' . $column->getPrecision() . ',' . $column->getScale() . ')';
		} elseif ( isset( $sqlType['limit'] ) ) {
			$def .= '(' . $sqlType['limit'] . ')';
		}

		$values = $column->getValues();
		if ( $values && is_array( $values ) ) {
			$def .= '(' . implode(
				', ',
				array_map(
					function ( $value ) {
						// we special case NULL as it's not actually allowed an enum value,
						// and we want MySQL to issue an error on the create statement, but
						// quote coerces it to an empty string, which will not error
						return $value === null ? 'NULL' : $this->wp_adapter->getConnection()->quote( $value ); // [modify]
					},
					$values
				)
			) . ')';
		}

		$def .= $column->getEncoding() ? ' CHARACTER SET ' . $column->getEncoding() : '';
		$def .= $column->getCollation() ? ' COLLATE ' . $column->getCollation() : '';
		$def .= ! $column->isSigned() && isset( $this->signedColumnTypes[ $column->getType() ] ) ? ' unsigned' : '';
		$def .= $column->isNull() ? ' NULL' : ' NOT NULL';

		if (
			// version_compare($this->getAttribute(\PDO::ATTR_SERVER_VERSION), '8', '>=') // [modify]
			/*&&*/ in_array( $column->getType(), static::PHINX_TYPES_GEOSPATIAL )           // [modify]
			&& ! is_null( $column->getSrid() )
		) {
			$def .= " SRID {$column->getSrid()}";
		}

		$def .= $column->isIdentity() ? ' AUTO_INCREMENT' : '';

		$default = $column->getDefault();
		// MySQL 8 supports setting default for the following tested types, but only if they are "cast as expressions"
		if (
			// version_compare($this->getAttribute(\PDO::ATTR_SERVER_VERSION), '8', '>=') &&    // [modify]
			is_string( $default ) &&
			in_array(
				$column->getType(),
				array_merge(
					static::PHINX_TYPES_GEOSPATIAL,
					array( static::PHINX_TYPE_BLOB, static::PHINX_TYPE_JSON, static::PHINX_TYPE_TEXT )
				)
			)
		) {
			$default = Literal::from( '(' . $this->wp_adapter->getConnection()->quote( $column->getDefault() ) . ')' ); // [modify]
		}
		$def .= $this->getDefaultValueDefinition( $default, $column->getType() );

		if ( $column->getComment() ) {
			$def .= ' COMMENT ' . $this->wp_adapter->getConnection()->quote( $column->getComment() ); // [modify]
		}

		if ( $column->getUpdate() ) {
			$def .= ' ON UPDATE ' . $column->getUpdate();
		}

		return $def;
	}
}
