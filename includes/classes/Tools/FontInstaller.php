<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Tools;

use Cornix\Serendipity\Core\Logger\Logger;

/**
 * dompdfにフォントを追加します。
 *
 * - `dompdf/dompdf/lib/fonts`にフォントファイルがコピーされる
 * - `dompdf/dompdf/lib/fonts/installed-fonts.json`にフォントの設定が追加される
 */
class FontInstaller {

	private const SRC_LOAD_FONT_PATH = __DIR__ . '/../../assets/font-install/load_font.php';

	/**
	 * コンストラクタ。
	 */
	private function __construct() {
	}

	/**
	 * フォントをインストールします。
	 *
	 * 指定されたフォントファイルのパスのファイル名がfont_familyとして使用されます。
	 *
	 * @param string $font_file_path フォントファイルのパス。
	 */
	public static function execute( string $font_file_path ): void {

		$instance = new self();

		// `load_font.php`をコピー
		$instance->copyLoadFontFile();

		// 過去にフォントをインストールしたことがある場合は、`installed-fonts.json`の内容を取得
		$old_installed_fonts_json = file_exists( $instance->getUserInstalledFontsJsonPath() )
			? json_decode( file_get_contents( $instance->getUserInstalledFontsJsonPath() ), true )
			: null;

		// `load_font.php`を用いてフォントをインストール
		$load_font_path = $instance->getLoadFontPath();
		$font_name      = pathinfo( $font_file_path )['filename']; // インストールするフォント名を取得(拡張子を除いたものを取得)
		$font_family    = explode( '-', $font_name )[0]; // ハイフンより前の部分をフォントファミリーとして使用
		// php load_font.php `フォントファミリー` `フォントファイルパス`
		exec( "php '{$load_font_path}' '{$font_family}' '{$font_file_path}' 2>&1", $opt, $result_code );
		if ( 0 !== $result_code ) {
			// 正常終了していない場合はログを出力して例外を投げる
			Logger::error( "font_family: $font_family" );
			Logger::error( "font_file_path: $font_file_path" );
			Logger::error( 'opt: ' . implode( PHP_EOL, $opt ) );
			throw new \Exception( '{61EA3942-CB59-4FA2-98A6-1124B9CB28A8}' );
		}

		// フォントファミリーが同じで、フォントファイルが異なる場合、後から追加したフォントですべて上書きされてしまうのでマージ作業を行う
		//
		// `font_name`にハイフンが含まれており、`opt`に`Unable to find bold face file.`, `Unable to find italic face file.`, `Unable to find bold_italic face file.` が含まれている場合
		// ⇒太字や斜体のフォントだが、通常のフォントファイルフォーマット。フォントファイル名からフォントスタイルを推測する。
		$check_messages = array( 'Unable to find bold face file.', 'Unable to find italic face file.', 'Unable to find bold_italic face file.' );
		if ( null !== $old_installed_fonts_json && strpos( $font_name, '-' ) !== false && count( array_intersect( $check_messages, $opt ) ) === count( $check_messages ) ) {
			// `font_name`のハイフンより後ろの文字からフォント種別を取得
			$font_name_suffix = strtolower( explode( '-', $font_name )[1] );
			// font_name_suffixを小文字に変換
			$bold_text_list   = array( 'bold' );  // boldとして扱うことができる文字列(小文字)
			$italic_text_list = array( 'italic', 'oblique' );   // italicとして扱うことができる文字列(小文字)

			// 指定した文字列に、`bold_text_list`の配列の文字列のいずれかが含まれるかどうかを判定する関数
			// アロー関数はPHP7.4から
			// $is_bold = fn( $text ) => count( array_filter( $bold_text_list, fn( $bold_text ) => strpos( $text, $bold_text ) !== false ) ) > 0;
			$is_bold = function ( $text ) use ( $bold_text_list ) {
				return count(
					array_filter(
						$bold_text_list,
						function ( $bold_text ) use ( $text ) {
							return strpos( $text, $bold_text ) !== false;
						}
					)
				) > 0;
			};
			// 指定した文字列に、`italic_text_list`の配列の文字列のいずれかが含まれるかどうかを判定する関数
			// アロー関数はPHP7.4から
			// $is_italic = fn( $text ) => count( array_filter( $italic_text_list, fn( $italic_text ) => strpos( $text, $italic_text ) !== false ) ) > 0;
			$is_italic = function ( $text ) use ( $italic_text_list ) {
				return count(
					array_filter(
						$italic_text_list,
						function ( $italic_text ) use ( $text ) {
							return strpos( $text, $italic_text ) !== false;
						}
					)
				) > 0;
			};

			// フォントスタイルを判定
			$font_style = 'normal'; // フォントファイル名から推測したフォントスタイル
			if ( $is_bold( $font_name_suffix ) && $is_italic( $font_name_suffix ) ) {
				$font_style = 'bold_italic';
			} elseif ( $is_bold( $font_name_suffix ) ) {
				$font_style = 'bold';
			} elseif ( $is_italic( $font_name_suffix ) ) {
				$font_style = 'italic';
			}

			// 更新後の`installed-fonts.json`の内容を取得
			$new_installed_fonts_json = json_decode( file_get_contents( $instance->getUserInstalledFontsJsonPath() ), true );
			$lower_font_family        = strtolower( $font_family );    // `installed-fonts.json`に登録されるフォントファミリー(小文字で登録される)
			// フォントインストール前に読みこんだ`installed-fonts.json`に同じフォントファミリーの設定がある場合
			if ( isset( $old_installed_fonts_json[ $lower_font_family ] ) ) {
				$font_styles = array( 'normal', 'bold', 'italic', 'bold_italic' );

				// インストールしたフォントスタイル以外をold_installed_fonts_jsonから取得して上書き
				foreach ( $font_styles as $fs ) {
					if ( $font_style !== $fs ) {
						$new_installed_fonts_json[ $lower_font_family ][ $fs ] = $old_installed_fonts_json[ $lower_font_family ][ $fs ];
					}
				}

				// `installed-fonts.json`を更新
				file_put_contents( $instance->getUserInstalledFontsJsonPath(), json_encode( $new_installed_fonts_json, JSON_PRETTY_PRINT ) );
			}
		}
	}

	/**
	 * ライブラリインストール時に同梱されているフォント情報を取得します。
	 */
	public static function getDistInstalledFonts(): array {
		$instance                       = new self();
		$dist_installed_fonts_json_path = $instance->getDistInstalledFontsJsonPath();
		if ( ! file_exists( $dist_installed_fonts_json_path ) ) {
			Logger::error( "dist_installed_fonts_json_path not found - $dist_installed_fonts_json_path" );
			throw new \Exception( '{9E6AE668-E0A3-4FE2-BDCD-8668BC90D044}' );
		}
		return json_decode( file_get_contents( $dist_installed_fonts_json_path ), true );
	}

	/**
	 * ユーザーがインストールしたフォント情報を取得します。
	 */
	public static function getUserInstalledFonts(): array {
		$instance                       = new self();
		$user_installed_fonts_json_path = $instance->getUserInstalledFontsJsonPath();
		if ( ! file_exists( $user_installed_fonts_json_path ) ) {
			return array();
		}
		return json_decode( file_get_contents( $user_installed_fonts_json_path ), true );
	}

	/**
	 * フォントをインストールするためのload_font.phpのパスを取得します。
	 */
	private function getLoadFontPath(): string {
		return untrailingslashit( DelayInstaller::getAssetsDir() ) . '/load_font.php';
	}

	/**
	 * フォントファイルが格納されているディレクトリのパスを取得します。
	 */
	private function getFontsDir(): string {
		return untrailingslashit( DelayInstaller::getAssetsDir() ) . '/vendor/dompdf/dompdf/lib/fonts';
	}

	/**
	 * ライブラリインストール時に同梱されているフォント情報、`installed-fonts.dist.json`のパスを取得します。
	 */
	private function getDistInstalledFontsJsonPath(): string {
		return untrailingslashit( $this->getFontsDir() ) . '/installed-fonts.dist.json';
	}

	/**
	 * フォントインストール後に更新される`installed-fonts.json`のパスを取得します。
	 */
	private function getUserInstalledFontsJsonPath(): string {
		return untrailingslashit( $this->getFontsDir() ) . '/installed-fonts.json';
	}

	/**
	 * load_font.phpをコピーします。
	 */
	private function copyLoadFontFile(): void {

		// コピー元ファイルが存在しない場合はエラー
		if ( ! file_exists( self::SRC_LOAD_FONT_PATH ) ) {
			throw new \Exception( '{514D898B-DEBC-4253-88A6-1B78D0656116}' );
		}

		// ファイルをコピー
		if ( ! copy( self::SRC_LOAD_FONT_PATH, $this->getLoadFontPath() ) ) {
			throw new \Exception( '{7D2AA3C4-F901-4A5F-97E2-FA127D79108B}' );
		}
	}
}
