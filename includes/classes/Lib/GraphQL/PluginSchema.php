<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\GraphQL;

use Cornix\Serendipity\Core\Lib\Path\ProjectFile;
use GraphQL\Language\Parser;
use GraphQL\Utils\AST;
use GraphQL\Utils\BuildSchema;

class PluginSchema {
	public function get() {
		// キャッシュファイルをこのプラグインディレクトリ内に作成することで
		// プラグインアップデート時は存在しなくなり、再作成される仕組み。
		$cache_file_path = ( new ProjectFile( '/includes/cache/graphql-schema.php' ) )->toLocalPath();

		// ここでは、キャッシュファイル生成に失敗しても例外を投げないことで
		// キャッシュファイルの生成に失敗しても、スキーマの取得ができるようにしている。
		if ( ! file_exists( $cache_file_path ) ) {
			$graphql_schema_path = ( new ProjectFile( '/includes/assets/schema.graphql' ) )->toLocalPath();
			$document            = Parser::parse( file_get_contents( $graphql_schema_path ) );
			// キャッシュファイルを作成
			file_put_contents( $cache_file_path, "<?php\nreturn " . var_export( AST::toArray( $document ), true ) . ";\n" );
		} else {
			$document = AST::fromArray( require $cache_file_path );
		}

		$schema = BuildSchema::build( $document );

		return $schema;
	}
}
