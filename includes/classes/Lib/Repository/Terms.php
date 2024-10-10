<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\Option;
use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;

class Terms {

	/**
	 * 販売者が署名する利用規約メッセージ情報(メッセージ及びバージョン)を取得します。
	 * (管理画面で販売者が署名する際に使用)
	 *
	 * @return object{terms_message:string,version:int}
	 */
	public function sellerAgreementMessageInfo() {
		$version = ( new TermsVersion() )->seller();
		return (object) array(
			'terms_message' => 'I agree to the seller\'s terms of service v' . $version,
			'version'       => $version,
		);
	}

	/**
	 * 販売者が同意した利用規約メッセージに関する情報(バージョン及び署名)を取得します。
	 * (購入者がチケットを入手する際に使用)
	 *
	 * @return object{terms_message:?string,version:?int}
	 */
	public function sellerAgreedMessageInfo() {
		$agreed_seller_terms = new AgreedSellerTerms();
		return (object) array(
			'terms_message' => $agreed_seller_terms->getTermsMessage(),
			'version'       => $agreed_seller_terms->getTermsVersion(),
		);
	}
}

class TermAgreedOption {

}

/**
 * 利用規約のバージョン情報を取得するためのクラス
 *
 * @internal
 */
class TermsVersion {
	/** 販売者向け利用規約のバージョン */
	public function seller(): int {
		return 1;
	}
}


/**
 * 販売者の利用規約同意情報を取得または保存するためのクラス
 *
 * @internal
 */
class AgreedSellerTerms {

	public function __construct() {
		$this->option = ( new OptionFactory() )->sellerTermsAgreedInfo();
	}

	private Option $option;

	public function set( string $terms_message, int $version, string $signature ): void {
		$this->option->update(
			(object) array(
				'terms_message' => $terms_message,
				'version'       => $version,
				'signature'     => $signature,
			)
		);
	}

	/**
	 * 販売者が利用規約に同意した時の署名を取得します。
	 */
	public function getSignature(): ?string {
		$obj = $this->option->get( null );
		if ( null === $obj ) {
			return null;
		}
		return $obj->signature;
	}

	/**
	 * 販売者が利用規約に同意した時の利用規約バージョンを取得します。
	 */
	public function getTermsVersion(): ?int {
		$obj = $this->option->get( null );
		if ( null === $obj ) {
			return null;
		}
		return $obj->version;
	}

	public function getTermsMessage(): ?string {
		$obj = $this->option->get( null );
		if ( null === $obj ) {
			return null;
		}
		return $obj->terms_message;
	}
}
