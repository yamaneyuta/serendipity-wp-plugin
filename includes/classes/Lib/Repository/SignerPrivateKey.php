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
		$this->option = ( new OptionFactory() )->signerPrivateKey();
	}
	private Option $option;

	/**
	 * 秘密鍵を取得します。
	 * 秘密鍵が作成されていない場合は例外をスローします。
	 */
	public function get(): string {
		$obj = $this->option->get( null );
		if ( null === $obj ) {
			throw new \Exception( '[D49203A3] The private key has not been set.' );
		}
		return $obj->value;
	}

	/**
	 * 秘密鍵が保存済みかどうかを取得します。
	 */
	public function exists(): bool {
		return null !== $this->option->get( null );
	}

	/**
	 * 秘密鍵を保存します。
	 *
	 * @param string $private_key
	 * @disregard P1009 Undefined type
	 */
	public function save(
		#[\SensitiveParameter]
		string $private_key
	): void {
		// 上書き禁止
		if ( $this->exists() ) {
			throw new \Exception( '[2DD53C18] The private key has already been set.' );
		}

		$this->option->update( (object) array( 'value' => $private_key ) );
	}
}
