<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class SellerAgreedTerms {

	public function exists(): bool {
		return ! is_null( $this->signature() );
	}

	/**
	 * 販売者が同意した利用規約のバージョンを取得します。
	 */
	public function version(): ?int {
		return ( new OptionFactory() )->sellerAgreedTermsVersion()->get();
	}

	/**
	 * 販売者が署名した同意メッセージを取得します。
	 */
	public function message(): ?string {
		return ( new SellerTerms() )->message( $this->version() );
	}

	/**
	 * 販売者が利用規約に同意した際の署名を取得します。
	 */
	public function signature(): ?string {
		return ( new OptionFactory() )->sellerAgreedTermsSignature()->get();
	}


	/**
	 * 販売者が利用規約に同意した際の情報を保存します。
	 */
	public function save( int $version, string $signature ): bool {
		// 引数チェック
		Judge::checkCurrentSellerTermsVersion( $version );  // 現在の販売者向け利用規約バージョンで登録されること
		Judge::checkHex( $signature );  // 署名が16進数表記であること

		// 保存
		$option_factory  = new OptionFactory();
		$version_saved   = $option_factory->sellerAgreedTermsVersion()->update( $version );
		$signature_saved = $option_factory->sellerAgreedTermsSignature()->update( $signature );
		// 今後、アップデートでWordPressのユーザーIDとウォレットを紐づけることが発生したときに
		// マイグレーションを行いやすいように登録ボタンを押下したユーザーIDを保存しておく
		$option_factory->sellerAgreedTermsUserID()->update( get_current_user_id(), false );  // 普段は使用しないため`autoload`は`false`

		return $version_saved && $signature_saved;
	}
}
