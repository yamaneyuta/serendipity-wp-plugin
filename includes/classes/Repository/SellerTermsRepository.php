<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Lib\Option\OptionFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\SignedTerms;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Domain\ValueObject\Terms;
use Cornix\Serendipity\Core\Domain\ValueObject\TermsVersion;

/**
 * 本プラグインにおける販売者向け利用規約の情報を取得するためのクラス
 */
class SellerTermsRepository {

	private const CURRENT_SELLER_TERMS_VERSION = 1;

	public function save( SignedTerms $singed_terms ): void {
		// TODO: 仮実装。テーブルに移行する予定
		$option_factory = new OptionFactory();
		$option_factory->sellerAgreedTermsVersion()->update( $singed_terms->terms()->version()->value() );
		$option_factory->sellerAgreedTermsSignature()->update( $singed_terms->signature()->value() );
		// 今後、アップデートでWordPressのユーザーIDとウォレットを紐づけることが発生したときに
		// マイグレーションを行いやすいように登録ボタンを押下したユーザーIDを保存しておく
		$option_factory->sellerAgreedTermsUserID()->update( get_current_user_id(), false );  // 普段は使用しないため`autoload`は`false`
	}

	/** 保存されている署名済みの販売者向け利用規約情報を取得します */
	public function get(): ?SignedTerms {
		$option_factory = new OptionFactory();
		$version_value  = $option_factory->sellerAgreedTermsVersion()->get();
		if ( is_null( $version_value ) ) {
			return null;  // 利用規約に同意していない
		}
		$signature = $option_factory->sellerAgreedTermsSignature()->get();
		$message   = $this->message( new TermsVersion( $version_value ) );
		return new SignedTerms( new Terms( new TermsVersion( $version_value ), $message ), $signature );
	}

	public function currentTerms(): Terms {
		$current_version = new TermsVersion( self::CURRENT_SELLER_TERMS_VERSION );

		// 利用規約のメッセージを取得
		$message = $this->message( $current_version );

		// Termsオブジェクトを生成して返す
		return new Terms( $current_version, $message );
	}

	/**
	 * 販売者向け利用規約に署名する時のメッセージを取得します。
	 * ※※※ 過去のバージョンが引数として渡される可能性があるため、過去バージョンでのメッセージが壊れないように注意してください。
	 */
	private function message( TermsVersion $version ): SigningMessage {
		return new SigningMessage( 'I agree to the seller\'s terms of service v' . $version->value() );
	}
}
