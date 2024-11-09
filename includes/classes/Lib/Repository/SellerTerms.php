<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

/**
 * 本プラグインにおける販売者向け利用規約の情報を取得するためのクラス
 */
class SellerTerms {

	/**
	 * このプラグインに同梱されている販売者向け利用規約のバージョンを取得します。
	 */
	public function currentVersion(): int {
		error_log( '[92FBB7F4] SellerTerms::version() - Not implemented yet' );
		return 1;
	}

	/**
	 * 販売者向け利用規約に署名する時のメッセージを取得します。
	 * ※※※ 過去のバージョンが引数として渡される可能性があるため、過去バージョンでのメッセージが壊れないように注意してください。
	 */
	public function message( int $version ): string {
		return 'I agree to the seller\'s terms of service v' . $version;
	}
}
