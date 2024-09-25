<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\Option;

// ■秘密鍵の保存について
// - `/wp-admin/options.php`での閲覧/編集を防止するため(だけ)にオブジェクト型で保存しています。
// - 暗号化や難読化は意味がないため行っていません。

/**
 * 署名用の秘密鍵を取得または保存するためのクラス
 */
class SignerPrivateKey {
	public function __construct() {
		$this->option = new Option( ( new OptionKeyName() )->signerPrivateKey() );
	}
	private Option $option;

	/**
	 * 秘密鍵を取得します。
	 *
	 * @return null|string
	 */
	public function get(): ?string {
		$obj = $this->option->get( null );
		return $obj ? $obj->value : null;
	}

	/**
	 * 秘密鍵を保存します。
	 *
	 * @param string $private_key
	 */
	public function save( string $private_key ): void {
		if ( null !== $this->get() ) {
			throw new \Exception( '[2DD53C18] The private key has already been set.' );
		}

		$this->option->update( (object) array( 'value' => $private_key ) );
	}
}
