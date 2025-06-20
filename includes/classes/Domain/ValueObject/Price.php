<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\TokenRepositoryImpl;
use phpseclib\Math\BigInteger;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class Price {
	public function __construct( $amount_hex, $decimals, $symbol ) {
		Validate::checkHex( $amount_hex ); // レート変換の途中などで一時的に大きい値になることがあるため、ここでは16進数の形式であることのみチェック
		Validate::checkDecimals( $decimals );
		Validate::checkSymbol( $symbol );

		$this->amount_hex = $amount_hex;
		$this->decimals   = $decimals;
		$this->symbol     = $symbol;
	}

	private string $amount_hex;
	private int $decimals;
	private string $symbol;

	/** 金額の数量(0xプレフィックス付きの16進数)を取得します。 */
	public function amountHex(): string {
		return $this->amount_hex;
	}

	/** 金額の小数点以下桁数を取得します。 */
	public function decimals(): int {
		return $this->decimals;
	}

	/** 通貨記号(`USD`, `ETH`等)を取得します。記号(`$`等)ではない。 */
	public function symbol(): string {
		return $this->symbol;
	}

	/**
	 * 指定したネットワークにおけるトークンの数量に変換します。
	 */
	public function toTokenAmount( ChainID $chain_ID ): string {
		// そのトークン1単位における小数点以下桁数。ETHであれば18。
		$tokens_filter = ( new TokensFilter() )->byChainID( $chain_ID )->bySymbol( $this->symbol );
		$tokens        = $tokens_filter->apply( ( new TokenRepositoryImpl() )->all() );

		if ( 1 !== count( $tokens ) ) {
			throw new \InvalidArgumentException( '[1644531E] Invalid token data. - chainID: ' . $chain_ID->value() . ', symbol: ' . $this->symbol . ', count: ' . count( $tokens ) );
		}
		$token = array_values( $tokens )[0]; // 1つだけなので、配列の最初の要素を取得

		$token_decimals = $token->decimals();

		// 補正する小数点以下桁数。現在の値が0.01ETHの場合、Priceとしての小数点以下は2だが、
		// ETH自体の小数点以下桁数が18なので、補正する桁数は18-2=16。
		$diff_decimals = $token_decimals - $this->decimals;

		/** @var string|null */
		$result = null;
		if ( $diff_decimals === 0 ) {
			// 補正不要
			$result = $this->amount_hex;
		} elseif ( $diff_decimals > 0 ) {
			// 補正が必要な場合(0を増やす場合)
			$amount = new BigInteger( $this->amount_hex, 16 );
			$amount = $amount->multiply( new BigInteger( '1' . str_repeat( '0', $diff_decimals ), 10 ) );
			$result = HexFormat::toHex( $amount );
		} else {
			// 補正が必要な場合(0を減らす場合)
			$amount = new BigInteger( $this->amount_hex, 16 );
			$amount = $amount->divide( new BigInteger( '1' . str_repeat( '0', -$diff_decimals ), 10 ) )[0]; // 商のみを取得
			$result = HexFormat::toHex( $amount );
		}

		Validate::checkAmountHex( $result );   // トークンの数量としては256bit以内に収まっていないと困る
		return $result;
	}
}
