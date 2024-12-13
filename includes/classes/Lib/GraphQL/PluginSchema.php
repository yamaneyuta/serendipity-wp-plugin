<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\GraphQL;

use Cornix\Serendipity\Core\Lib\Path\ProjectFile;
use Cornix\Serendipity\Core\Lib\Repository\Environment;
use GraphQL\Language\Parser;
use GraphQL\Utils\AST;
use GraphQL\Utils\BuildSchema;

class PluginSchema {
	public function get() {
		// キャッシュファイルをこのプラグインディレクトリ内に作成することで
		// プラグインアップデート時は存在しなくなり、再作成される仕組み。
		$cache_file_path = ( new ProjectFile( 'includes/cache/graphql-schema.php' ) )->toLocalPath();

		// 開発中は、スキーマ等が更新される可能性があるので、キャッシュファイルが古い場合はキャッシュファイルを削除
		if ( ( new Environment() )->isDevelopmentMode() ) {
			$this->deleteCacheFileIfOutdated( $cache_file_path );
		}

		// ここでは、キャッシュファイル生成に失敗しても例外を投げないことで
		// キャッシュファイルの生成に失敗しても、スキーマの取得ができるようにしている。
		if ( ! file_exists( $cache_file_path ) ) {
			$graphql_schema_path = $this->graphqlSchemaPath();
			$document            = Parser::parse( file_get_contents( $graphql_schema_path ) );
			// キャッシュファイルを作成
			file_put_contents( $cache_file_path, "<?php\nreturn " . var_export( AST::toArray( $document ), true ) . ";\n" );
		} else {
			$document = AST::fromArray( require $cache_file_path );
		}

		$schema = BuildSchema::build( $document );

		return $schema;
	}

	/**
	 * GraphQLスキーマファイルのパスを取得します。
	 */
	private function graphqlSchemaPath() {
		return ( new ProjectFile( 'includes/assets/graphql/schema/schema.graphql' ) )->toLocalPath();
	}

	/**
	 * キャッシュファイルが古い場合は削除します。
	 */
	private function deleteCacheFileIfOutdated( string $cache_file_path ) {
		$cache_file_mtime     = filemtime( $cache_file_path );
		$graphql_schema_mtime = filemtime( $this->graphqlSchemaPath() );

		if ( $cache_file_mtime < $graphql_schema_mtime ) {
			unlink( $cache_file_path );
		}
	}
}
