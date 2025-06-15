<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ServerSignerTable;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;

class ServerSignerPrivateKeyRepository {
	public function __construct( ServerSignerTable $server_signer_table ) {
		$this->server_signer_table = $server_signer_table;
	}
	private ServerSignerTable $server_signer_table;

	public function encryptionKey(): ?string {
		$record = $this->server_signer_table->get();
		return is_null( $record ) ? null : $record->encryptionKeyValue();
	}
	public function encryptionIv(): ?string {
		$record = $this->server_signer_table->get();
		return is_null( $record ) ? null : $record->encryptionIvValue();
	}
	public function privateKeyData(): ?string {
		$record = $this->server_signer_table->get();
		return is_null( $record ) ? null : $record->privateKeyDataValue();
	}
	public function address(): ?Address {
		$record = $this->server_signer_table->get();
		return is_null( $record ) ? null : Address::from( $record->addressValue() );
	}

	/**
	 * 署名用ウォレットの秘密鍵を保存します。
	 *
	 * @disregard P1009 Undefined type
	 */
	public function save(
		Address $address,
		string $private_key_data,
		#[\SensitiveParameter]
		?string $encryption_key,
		?string $encryption_iv
	): void {
		$this->server_signer_table->save( $address, $private_key_data, $encryption_key, $encryption_iv );
	}
}
