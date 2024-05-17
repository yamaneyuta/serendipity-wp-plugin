<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Tools;

use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Utils\LocalPath;

/**
 * プラグイン導入後にサードパーティ製ライブラリをインストールするためのクラス。
 */
class DelayInstaller {

	/**
	 * コンストラクタ。
	 */
	private function __construct() {

		// このクラスで必要となるディレクトリを初期化(なければ作成)
		$this->initializeDirectories();

		// .htaccessファイルが存在しなければ作成
		$this->createAssetsHtaccessIfNotExists();
	}


	/**
	 * サーバー側でcomposerを実行させ、サードパーティ製ライブラリをインストールします。
	 */
	public static function execute(): void {

		$instance = new self();

		// 事前cleanup
		$instance->cleanComposerFiles();

		try {

			// composerのセットアップファイルをダウンロード
			$instance->downloadComposerSetupFile();
			// composerのセットアップファイルを実行
			$instance->executeComposerSetup();

			// composerの定義ファイルをコピー
			$instance->copyComposerConfigFiles();
			// インストール
			$instance->install();

			// vendorディレクトリ内のファイルを変更したファイルで置換
			$instance->modifyVendorFiles();

		} finally {
			try {
				// cleanup
				// $instance->clean_composer_files();
			} catch ( \Exception $e ) {
				Logger::error( $e );
				// ここは再スローしない
			}
		}
	}


	/**
	 * assetsディレクトリのパスを返します。
	 */
	public static function getAssetsDir(): string {
		$assets_dir = LocalPath::getUserAssetsDir();

		// assetsディレクトリが存在する場合は、相対パスが含まれない形式で返す
		return file_exists( $assets_dir ) ? realpath( $assets_dir ) : $assets_dir;
	}


	/**
	 * assetsディレクトリ内のbinディレクトリのパスを返します。
	 */
	private function getBinDir(): string {
		return untrailingslashit( $this->getAssetsDir() ) . '/bin';
	}


	/**
	 * composerの実行ファイル名を取得します。
	 */
	private function getComposerFileName(): string {
		return 'composer';
	}


	/**
	 * composerのセットアップファイルのパスを返します。
	 */
	private function getComposerSetupPath(): string {
		return untrailingslashit( $this->getBinDir() ) . '/composer-setup.php';
	}


	/**
	 * composerの実行ファイルのパスを返します。
	 */
	private function getComposerPath(): string {
		return untrailingslashit( $this->getBinDir() ) . '/' . $this->getComposerFileName();
	}


	/**
	 * コピー元の`composer.json`及び`composer.lock`のファイルパスを返します。
	 */
	private function getSrcComposerConfigFiles(): array {
		$dir   = __DIR__ . '/../../assets/delay-install';
		$files = array();

		$file_names = array(
			'composer.json',
			'composer.lock',
		);

		foreach ( $file_names as $file_name ) {
			$file = $dir . '/' . $file_name;
			if ( ! file_exists( $file ) ) {
				// ファイルが存在しない場合は例外
				Logger::error( "File not found: {$file}" );
				throw new \Exception( '{31438F81-5803-43FD-9721-49C09B966DC4}' );
			}
			$files[] = $file;
		}

		return $files;
	}


	/**
	 * このクラスで必要となるディレクトリを初期化(なければ作成)します。
	 */
	private function initializeDirectories(): void {
		// 必要なディレクトリの配列
		$dirs = array(
			$this->getAssetsDir(),
			$this->getBinDir(),
		);

		// 必要なディレクトリの配列`dirs`の最後が作成済みの場合、すべてのディレクトリが存在しているものとして処理抜け
		if ( is_dir( end( $dirs ) ) ) {
			return;
		}

		$mkdir = function ( $directory ): void {
			if ( is_dir( $directory ) ) {
				// すでに存在している場合は処理抜け
				return;
			}
			// 以下、ディレクトリが存在しない場合
			//
			// 再帰的にディレクトリを作成する
			if ( false === mkdir( $directory, 0777, true ) ) {
				// 作成に失敗した場合は例外を投げる
				Logger::error( 'Failed to create directory: ' . $directory );
				throw new \Exception( '{725445D2-B3D0-4EF9-A011-7B1BF46CA894}' );
			}
		};

		// 必要となるディレクトリを作成
		foreach ( $dirs as $dir ) {
			$mkdir( $dir );
		}
	}


	/**
	 * assetsディレクトリに直接アクセスされないように、.htaccessファイルを作成します。
	 * apache以外(nginx等)の場合は効果がないので、ユーザー側での対応が必要。
	 */
	private function createAssetsHtaccessIfNotExists(): void {
		$htaccess_file_path = untrailingslashit( $this->getAssetsDir() ) . '/.htaccess';
		if ( ! file_exists( $htaccess_file_path ) ) {
			$htaccess_file = fopen( $htaccess_file_path, 'w' );
			fwrite( $htaccess_file, 'Deny from all' . PHP_EOL );
			fclose( $htaccess_file );
		}
	}


	/**
	 * composerのセットアップファイルをダウンロードします。
	 */
	private function downloadComposerSetupFile(): void {
		$composer_setup_path = $this->getComposerSetupPath();

		$composer_setup_file = fopen( $composer_setup_path, 'w' );
		fwrite( $composer_setup_file, file_get_contents( 'https://getcomposer.org/installer' ) );
		fclose( $composer_setup_file );

		if ( ! file_exists( $composer_setup_path ) || filesize( $composer_setup_path ) === 0 ) {
			// composer-setup.phpの作成に失敗した場合
			throw new \Exception( '{633175F0-918E-4276-BE67-8573136F43AD}' );
		}
	}


	/**
	 * このプラグインで使うcomposerのCOMPOSER_HOMEを取得します。
	 * 既にCOMPOSER_HOMEやHOMEが設定されていても、その値は使用しない。
	 */
	private function getComposerHome(): string {
		// bin/.composerディレクトリをCOMPOSER_HOMEとする。
		return untrailingslashit( $this->getBinDir() ) . '/.composer';
	}


	/**
	 * composerのセットアップファイルを実行します。
	 */
	private function executeComposerSetup(): void {

		$compose_home   = $this->getComposerHome();
		$composer_setup = $this->getComposerSetupPath();
		$install_dir    = $this->getBinDir();
		$filename       = $this->getComposerFileName();
		$cmd            = "export COMPOSER_HOME='{$compose_home}'; php {$composer_setup} --version=2.4.4 --install-dir={$install_dir} --filename={$filename}";

		exec( $cmd );

		if ( ! file_exists( $this->getComposerPath() ) || filesize( $this->getComposerPath() ) === 0 ) {
			// composerファイルの作成に失敗した場合
			throw new \Exception( '{5BC9AFCA-AD75-4302-B0B8-95A7984935C6}' );
		}
	}


	/**
	 * composerでインストールするために必要な設定ファイルをコピーします。
	 */
	private function copyComposerConfigFiles(): void {
		$src_composer_config_files = $this->getSrcComposerConfigFiles();
		$dest_dir                  = $this->getAssetsDir();

		// すべてのファイルをコピー
		foreach ( $src_composer_config_files as $src_composer_config_file ) {
			// コピー先のファイルパス
			$dest_composer_config_file = $dest_dir . '/' . basename( $src_composer_config_file );
			// コピー実行
			$copy_success = copy( $src_composer_config_file, $dest_composer_config_file );

			if ( false === $copy_success ) {
				// コピーに失敗した場合
				Logger::error( "Failed to copy file: {$src_composer_config_file} to {$dest_composer_config_file}" );
				throw new \Exception( '{996FB273-15AE-4936-AAA5-9C525E90071B}' );
			}
		}
	}


	/**
	 * composerでインストールします。
	 */
	private function install(): void {
		$org_dir = getcwd();    // ディレクトリ移動を行うので、現在のディレクトリを記憶
		try {
			// assetsディレクトリに移動
			chdir( $this->getAssetsDir() );

			$compose_home = $this->getComposerHome();
			$composer     = $this->getComposerPath();
			$cmd          = "export COMPOSER_HOME='{$compose_home}'; {$composer} --ignore-platform-req=ext-gmp install";

			// TODO: バックグラウンドで実行し、タイムアウト対応を行う
			// https://www.php.net/manual/ja/info.configuration.php#ini.max-execution-time
			exec( $cmd );
		} finally {
			// 元のディレクトリに戻す
			chdir( $org_dir );
		}
	}

	private function modifyVendorFiles(): void {
		$src_dir = __DIR__ . '/../../assets/delay-install/modifications/vendor';
		$dst_dir = $this->getAssetsDir() . '/vendor';

		// src_dirに存在するファイルをすべてdst_dirに再帰的にコピー
		$copy_recursive = function ( $src_dir, $dst_dir ) use ( &$copy_recursive ) {
			$src_dir = untrailingslashit( $src_dir );
			$dst_dir = untrailingslashit( $dst_dir );

			$files = glob( $src_dir . '/*' );
			foreach ( $files as $file ) {
				if ( is_dir( $file ) ) {
					$copy_recursive( $file, $dst_dir . '/' . basename( $file ) );
				} else {
					$dst_file = $dst_dir . '/' . basename( $file );
					copy( $file, $dst_file );
				}
			}
		};

		$copy_recursive( $src_dir, $dst_dir );
	}


	/**
	 * composerのセットアップファイル及びcomposerファイルを削除します。
	 */
	private function cleanComposerFiles(): void {
		// composerのセットアップファイル
		$composer_setup_path = $this->getComposerSetupPath();
		// composerファイル
		$composer_path = $this->getComposerPath();
		// composerでインストールするために必要な設定ファイル
		$composer_config_files = array();
		foreach ( $this->getSrcComposerConfigFiles() as $src_composer_config_file ) {
			$composer_config_files[] = $this->getAssetsDir() . '/' . basename( $src_composer_config_file );
		}

		// 削除対象のファイル一覧
		$files = array_merge(
			array(
				$composer_setup_path,
				$composer_path,
			),
			$composer_config_files
		);

		$not_deleted_files = array();   // 削除できなかったファイルまたはディレクトリのパスを格納する配列

		// ファイルを削除
		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
			// 削除できていない場合は、配列に追加
			if ( file_exists( $file ) ) {
				$not_deleted_files[] = $file;
			}
		}

		if ( ! empty( $not_deleted_files ) ) {
			// 削除できなかったファイルまたはディレクトリがある場合は例外
			Logger::error( 'Failed to delete files: ' . implode( ', ', $not_deleted_files ) );
			throw new \Exception( '{0AC3C66A-80BD-4884-8219-8310CA2463BB}' );
		}
	}
}
