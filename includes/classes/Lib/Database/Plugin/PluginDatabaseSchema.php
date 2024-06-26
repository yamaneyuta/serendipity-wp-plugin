<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Plugin;

use Cornix\Serendipity\Core\Lib\Path\ProjectFile;
use Cornix\Serendipity\Core\Lib\Database\Plugin\Phinx\WordPressAdapter;
use Cornix\Serendipity\Core\Lib\SystemInfo\Config;
use Phinx\Config\Config as PhinxConfig;
use Phinx\Console\Command\Migrate;
use Phinx\Db\Adapter\TablePrefixAdapter;
use Phinx\Migration\Manager;
use Phinx\Migration\Manager\Environment;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class PluginDatabaseSchema {

	public function migrate(): void {

		// $application = new PhinxApplication();
		$migrate = new Migrate();
		// $application->add($migrate);

		/** @var Migrate $command */
		// $command = $application->find('migrate');
		$command = $migrate;

		$options = $this->options();

		$config = new PhinxConfig( $options );

		$input  = new ArrayInput( array() );
		$output = new StreamOutput( fopen( 'php://memory', 'a', false ) ); // new NullOutput();

		$mode = 'production';

		$adapter = new WordPressAdapter( $config->getEnvironment( $mode ) + array( 'version_order' => $options['version_order'] ) );

		$adapter = new TablePrefixAdapter( $adapter );

		$environment = ( new Environment( $mode, $config->getEnvironment( $mode ) ) )->setAdapter( $adapter );
		$manager     = ( new Manager( $config, $input, $output ) )->setEnvironments( array( 'production' => $environment ) );

		$command->setConfig( $config )->setManager( $manager );

		$code = $command->run( $manager->getInput(), $manager->getOutput() );

		error_log( 'code: ' . print_r( $code, true ) );
		if ( 0 !== $code ) {
			// $outputから結果を取得
			$resource = $output->getStream();
			rewind( $resource );
			$output = stream_get_contents( $resource );
			fclose( $resource );

			// ログ出力
			error_log( '[82DE4891] ' . $output );
			throw new \Exception( '[A39C7F95] Failed to migrate database.' );
		}
	}

	private function options(): array {

		$prefix = $this->tableNamePrefix();
		$dbname = $this->dbname();

		$options = array(
			'paths'         => array(
				'migrations' => ( new ProjectFile( '/includes/assets/db/migrations' ) )->toLocalPath(),
				// 'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
			),
			'environments'  => array(
				'default_migration_table' => "${prefix}phinxlog",
				'default_environment'     => 'production',
				'production'              => array(
					'name'         => $dbname,
					'table_prefix' => $prefix,
				),
			),
			'version_order' => 'creation',
		);

		return $options;
	}

	private function dbname(): string {
		global $wpdb;
		return $wpdb->dbname;
	}

	private function tableNamePrefix(): string {
		// マルチサイト(2番目以降のサイト)はサポートしない
		if ( get_current_blog_id() > 1 ) {
			throw new \Exception( '[24515B6F] This plugin does not support multisite.' );
		}

		// テーブル名のプレフィックスを取得
		// (プラグインのテキストドメインを使用)
		$prefix = ( new Config() )->getPluginInfo( 'TextDomain' ) . '_';

		return $prefix;
	}
}
