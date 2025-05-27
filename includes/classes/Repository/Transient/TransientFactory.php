<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\Transient;

use Cornix\Serendipity\Core\Repository\Name\Prefix;
use Cornix\Serendipity\Core\Types\SymbolPair;

class TransientFactory {

	/**
	 * transientデータをoptionsテーブルへ問い合わせる時のキーを取得します。
	 */
	private function getTransientKeyName( string $raw_transient_key_name ): string {
		return ( new Prefix() )->transientKeyPrefix() . $raw_transient_key_name;
	}

	/**
	 * 指定した通貨ペアのレートの値を取得または保存するオブジェクトを取得します。
	 */
	public function rateAmountHex( SymbolPair $symbol_pair ): StringTransient {
		return new StringTransient( $this->getTransientKeyName( 'rate_amount_hex_' . $symbol_pair->base() . '_' . $symbol_pair->quote() ) );
	}

	/**
	 * 指定した通貨ペアのレートの小数点以下の桁数を取得または保存するオブジェクトを取得します。
	 */
	public function rateDecimals( SymbolPair $symbol_pair ): IntTransient {
		return new IntTransient( $this->getTransientKeyName( 'rate_decimals_' . $symbol_pair->base() . '_' . $symbol_pair->quote() ) );
	}
}
