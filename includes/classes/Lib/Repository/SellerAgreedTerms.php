<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;

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

		// TODO: 引数チェック
		// - versionが現在のバージョンと一致すること
		// - signatureが16進数の文字列であること

		$option_factory  = new OptionFactory();
		$version_saved   = $option_factory->sellerAgreedTermsVersion()->update( $version );
		$signature_saved = $option_factory->sellerAgreedTermsSignature()->update( $signature );

		return $version_saved && $signature_saved;
	}
}
