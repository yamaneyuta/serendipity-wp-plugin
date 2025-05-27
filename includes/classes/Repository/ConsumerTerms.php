<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

/**
 * 本プラグインにおける購入者向け利用規約の情報を取得するためのクラス
 */
class ConsumerTerms {

	/**
	 * このプラグインに同梱されている購入者向け利用規約のバージョンを取得します。
	 */
	public function currentVersion(): int {
		// TODO: 購入者向け利用規約バージョン取得処理の実装
		error_log( '[230E2F1C] ConsumerTerms::version() - Not implemented yet' );
		return 1;
	}
}
