<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Update;

use Cornix\Serendipity\Core\Lib\Logger\Logger;

class PluginUpdater {
	/**
	 * プラグインの更新を行います。
	 */
	public function update( ?string $from_version, string $to_version ): void {
		// バージョンが指定されていない場合は、0.0.0として扱う
		$from_version = $from_version ?? '0.0.0';
		Logger::info( "[EB4B8211] Update from {$from_version} to {$to_version}" );

		$target_dir = __DIR__ . '/Version';

		// 更新対象のバージョンを取得
		$versions = array_filter(
			( new UpdateVersions() )->get( $target_dir ),   // ディレクトリに存在するバージョンアップ用のファイルから取得したバージョン一覧
			fn( $version ) => version_compare( $from_version, $version, '<' ) && version_compare( $version, $to_version, '<=' )
		);
		usort( $versions, 'version_compare' );  // 小さい順(更新処理順)にソート

		$rollback_handler = new RollbackHandler();    // ロールバック用に実行したインスタンスを保持するオブジェクト

		foreach ( $versions as $version ) {
			// インスタンスを生成
			$instance = $this->createInstance( trailingslashit( $target_dir ) . $version . '.php' );
			// ロールバック用のオブジェクトに追加
			$rollback_handler->append( $version, $instance );

			try {
				// 更新処理実行
				$instance->up();
				Logger::info( "[B4721C01] Updated to {$version}" );
			} catch ( \Throwable $e ) {
				// 更新処理に失敗した旨のログ出力
				Logger::error( "[C84F4F82] Failed to update to {$version}. Rollback process will be executed." );
				Logger::error( $e );

				// ロールバック処理を行う
				$rollback_handler->rollback();

				throw $e;   // 例外を再スロー
			}
		}
	}

	/**
	 * 指定したPHPファイルに含まれるクラスをインスタンス化します。
	 */
	private function createInstance( string $target_file ) {
		// ファイルの内容を読み取り、名前空間とクラス名を取得
		$namespace       = $this->getNamespace( $target_file );
		$class_name      = $this->getClassName( $target_file );
		$full_class_name = $namespace . '\\' . $class_name;

		// インスタンス化
		require_once $target_file;
		$instance = new $full_class_name();

		// 更新用のオブジェクトであるかどうかを確認
		$this->checkUpdaterObject( $instance );

		return $instance;
	}

	/**
	 * 更新用のPHPファイルから作成したインスタンスの整合性を確認します。
	 */
	private function checkUpdaterObject( object $instance ): void {
		$full_class_name = get_class( $instance );
		// `up`及び`down`メソッドが存在しない場合は例外を投げる
		foreach ( array( 'up', 'down' ) as $method ) {
			if ( ! method_exists( $instance, $method ) ) {
				throw new \Exception( "[E17A8FDF] {$method}() method not found in {$full_class_name}" );
			}
		}
	}

	/**
	 * 指定したPHPファイルの名前空間を取得します。
	 */
	private function getNamespace( string $php_file_path ): string {
		$php_file = file_get_contents( $php_file_path );
		// token_get_allはPHP8.0から挙動が変わっているため、単純に`namespace `で開始する行から取得する
		$lines = explode( "\n", $php_file );
		foreach ( $lines as $line ) {
			if ( strpos( $line, 'namespace ' ) === 0 ) {
				return trim( str_replace( array( 'namespace ', ';' ), '', $line ) );
			}
		}
		throw new \Exception( "[2D428286] Namespace not found in {$php_file_path}" );
	}

	/**
	 * 指定したPHPファイルを分析し、最初に見つかったクラス名を取得します。
	 */
	private function getClassName( string $php_file_path ): string {
		$php_file = file_get_contents( $php_file_path );
		$tokens   = token_get_all( $php_file );

		foreach ( $tokens as $i => $token ) {
			if ( is_array( $token ) && $token[0] === T_CLASS ) {
				$next_token = $tokens[ $i + 2 ];
				if ( is_array( $next_token ) && $next_token[0] === T_STRING ) {
					return $next_token[1];
				}
			}
		}
	}
}

class RollbackHandler {
	public function append( string $version, object $instance ): void {
		// ロールバック用の配列に追加
		$this->update_instance_for_rollback[] = array(
			'version'  => $version,
			'instance' => $instance,
		);
	}

	/** ロールバックが必要になった時に`down`メソッドを呼び出すインスタンスを保持するための変数 */
	private array $update_instance_for_rollback = array();

	/** ロールバック処理を行います。 */
	public function rollback(): void {
		// ロールバック処理を行う
		foreach ( array_reverse( $this->update_instance_for_rollback ) as $rollback_instance ) {
			$rollback_instance['instance']->down();
			Logger::info( "[20697300] {$rollback_instance['version']}'s down() method was called." );
		}
	}
}


/**
 * 指定したディレクトリに存在するファイルからバージョン一覧を取得します。
 *
 * @internal
 */
class UpdateVersions {
	public function get( string $target_dir, string $ext = 'php' ): array {
		// 処理概要:
		// 指定されたディレクトリに存在するファイル一覧を取得する
		// 拡張子を除いたファイル名がバージョンのフォーマットと一致する場合にバージョンとして抽出する

		// 拡張子無しのファイル名を取得(ディレクトリパスと拡張子を削除)
		$file_names = $this->getFileBaseNames( $target_dir, $ext );

		// ファイル名がバージョニングのフォーマットのものを抽出
		$versions = array_filter( $file_names, fn( $file_name ) => $this->isSemanticalVersion( $file_name ) );

		// $versionsに含まれないファイルを$file_namesから抽出
		$not_versions = array_diff( $file_names, $versions );
		// ここで、$not_versionsに含まれるべきでないファイルがあれば例外を投げる
		foreach ( $not_versions as $not_version ) {
			// 条件なく例外を投げているが、対象のファイルに存在しても問題ないファイルがある場合はここの処理を修正する。
			throw new \Exception( "[06A45EA8] Invalid version file: {$not_version}" );
		}

		// インデックスを振り直した配列を返す
		return array_values( $versions );
	}

	/**
	 * 指定されたディレクトリに存在するファイルの拡張子を除いたファイル名一覧を取得します。
	 */
	private function getFileBaseNames( string $target_dir, string $ext ): array {

		$files = glob( trailingslashit( $target_dir ) . '*' );

		// basename()でディレクトリパスと拡張子を削除したファイル名一覧を返す
		return array_map(
			fn( $file ) => basename( $file, '.' . $ext ),
			$files
		);
	}

	private function isSemanticalVersion( string $version ): bool {
		// [セマンティックバージョニング](https://semver.org/lang/ja/)の最後に記載されている正規表現に合致するものをバージョンとして扱う
		return 1 === preg_match(
			'/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/',
			$version
		);
	}
}
