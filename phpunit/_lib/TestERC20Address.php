<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\ValueObject\Address;

/** テスト対象のコントラクトアドレスを取得するクラス */
class TestERC20Address {
	private const L1_TUSD = '0x429035C67ACEA53E5Ae8d18e39294eF7Dadd52BF';
	private const L1_TJPY = '0x22a762Ba5e1BB196C89feC59a4438D515a13b8f9';

	private const L2_TUSD = '0x731A82e658305cE90316A7376092F54473b56681';
	private const L2_TJPY = '0x850d911A7baEe310281Bd914b73613734803b7aF';

	public static function L1_TUSD(): Address {
		return new Address( self::L1_TUSD );
	}
	public static function L1_TJPY(): Address {
		return new Address( self::L1_TJPY );
	}
	public static function L2_TUSD(): Address {
		return new Address( self::L2_TUSD );
	}
	public static function L2_TJPY(): Address {
		return new Address( self::L2_TJPY );
	}
}
