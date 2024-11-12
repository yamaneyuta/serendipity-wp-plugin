<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\ArrayOption;
use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Web3\Signer;

// ■秘密鍵の保存について
// - `/wp-admin/options.php`での閲覧/編集を防止するため(だけ)にオブジェクト型で保存しています。
// - 暗号化や難読化は意味がないため行っていません。

/**
 * 署名用の秘密鍵を取得または保存するためのクラス
 */
class ServerSignerData {
	public function __construct() {
		$this->option = ( new OptionFactory() )->serverSignerData();
	}
	private ArrayOption $option;

	private const FIELD_NAME_PRIVATE_KEY = 'private_key';
	private const FIELD_NAME_ADDRESS     = 'address';

	/**
	 * 秘密鍵を取得します。
	 * 秘密鍵が作成されていない場合は例外をスローします。
	 */
	public function getPrivateKey(): string {
		/** @var string|null */
		$private_key = $this->option->get( null )[ self::FIELD_NAME_PRIVATE_KEY ] ?? null;
		if ( ! is_string( $private_key ) ) {
			// プラグイン初期化時に秘密鍵が生成されるため、ここは通らない
			throw new \Exception( '[D49203A3] The private key has not been set.' );
		}
		return $private_key;
	}

	/**
	 * 署名用ウォレットのアドレスを取得します。
	 */
	public function getAddress(): string {
		/** @var string|null */
		$address = $this->option->get( null )[ self::FIELD_NAME_ADDRESS ] ?? null;
		if ( ! is_string( $address ) ) {
			throw new \Exception( '[F16701F9] The private key has not been set.' );
		}
		return $address;
	}

	/**
	 * 秘密鍵が保存済みかどうかを取得します。
	 */
	public function exists(): bool {
		return ! is_null( $this->option->get( null ) );
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

		// 秘密鍵保存時にアドレスも計算して格納。(署名用ウォレットアドレスの計算を省略することが目的)
		$this->option->update(
			array(
				self::FIELD_NAME_PRIVATE_KEY => $private_key,
				self::FIELD_NAME_ADDRESS     => ( new Signer( $private_key ) )->address(),
			)
		);
	}
}
