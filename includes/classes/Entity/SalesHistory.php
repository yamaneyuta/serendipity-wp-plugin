<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\Entity\Token;
use Cornix\Serendipity\Core\Repository\TokenRepository;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\Price;
use DateTime;
/**
 * @deprecated
 * TODO: Refactor
 */
class SalesHistory {

	private const COLUMN_INVOICE_ID               = 'invoice_id';
	private const COLUMN_POST_ID                  = 'post_id';
	private const COLUMN_CHAIN_ID                 = 'chain_id';
	private const COLUMN_SELLING_AMOUNT_HEX       = 'selling_amount_hex';
	private const COLUMN_SELLING_DECIMALS         = 'selling_decimals';
	private const COLUMN_SELLING_SYMBOL           = 'selling_symbol';
	private const COLUMN_SELLER_ADDRESS           = 'seller_address';
	private const COLUMN_PAYMENT_AMOUNT_HEX       = 'payment_amount_hex';
	private const COLUMN_CONSUMER_ADDRESS         = 'consumer_address';
	private const COLUMN_CREATED_AT               = 'created_at';
	private const COLUMN_BLOCK_NUMBER             = 'block_number';
	private const COLUMN_TRANSACTION_HASH         = 'transaction_hash';
	private const COLUMN_SELLER_PROFIT_AMOUNT_HEX = 'seller_profit_amount_hex';
	private const COLUMN_HANDLING_FEE_AMOUNT_HEX  = 'handling_fee_amount_hex';
	private const COLUMN_TOKEN_SYMBOL             = 'token_symbol';
	private const COLUMN_TOKEN_ADDRESS            = 'token_address';
	private const COLUMN_TOKEN_DECIMAL            = 'token_decimals';

	private function __construct( array $record ) {
		$this->record = $record;
	}

	private array $record;

	/**
	 * SalesData::select()内で取得したレコードを元に、SalesDataTypeを生成します
	 *
	 * @param array $record
	 * @return SalesHistory
	 */
	public static function fromRecord( array $record ): self {
		assert( array_key_exists( self::COLUMN_INVOICE_ID, $record ), '[FEABA7F7]' );
		assert( array_key_exists( self::COLUMN_POST_ID, $record ), '[EE1B5226]' );
		assert( array_key_exists( self::COLUMN_CHAIN_ID, $record ), '[62678DC3]' );
		assert( array_key_exists( self::COLUMN_SELLING_AMOUNT_HEX, $record ), '[D824D279]' );
		assert( array_key_exists( self::COLUMN_SELLING_DECIMALS, $record ), '[E7222C49]' );
		assert( array_key_exists( self::COLUMN_SELLING_SYMBOL, $record ), '[9CAD7547]' );
		assert( array_key_exists( self::COLUMN_SELLER_ADDRESS, $record ), '[F1806339]' );
		assert( array_key_exists( self::COLUMN_PAYMENT_AMOUNT_HEX, $record ), '[94AC06B9]' );
		assert( array_key_exists( self::COLUMN_CONSUMER_ADDRESS, $record ), '[2AA73D62]' );
		assert( array_key_exists( self::COLUMN_CREATED_AT, $record ), '[2553028F]' );
		assert( array_key_exists( self::COLUMN_BLOCK_NUMBER, $record ), '[B1FE7B87]' );
		assert( array_key_exists( self::COLUMN_TRANSACTION_HASH, $record ), '[0733883B]' );
		assert( array_key_exists( self::COLUMN_SELLER_PROFIT_AMOUNT_HEX, $record ), '[2ABB4891]' );
		assert( array_key_exists( self::COLUMN_HANDLING_FEE_AMOUNT_HEX, $record ), '[460AFAB7]' );
		assert( array_key_exists( self::COLUMN_TOKEN_SYMBOL, $record ), '[1864901C]' );
		assert( array_key_exists( self::COLUMN_TOKEN_ADDRESS, $record ), '[7BEA82F4]' );
		assert( array_key_exists( self::COLUMN_TOKEN_DECIMAL, $record ), '[8F287790]' );

		return new self( $record );
	}

	/** 請求書ID */
	public function invoiceID(): string {
		return (string) $this->record[ self::COLUMN_INVOICE_ID ];
	}

	/** 投稿ID */
	public function postID(): int {
		return (int) $this->record[ self::COLUMN_POST_ID ];
	}

	/** 取引されるチェーンID */
	public function chainID(): int {
		return (int) $this->record[ self::COLUMN_CHAIN_ID ];
	}

	/** 販売価格 */
	public function sellingPrice(): Price {
		return new Price(
			(string) $this->record[ self::COLUMN_SELLING_AMOUNT_HEX ],
			(int) $this->record[ self::COLUMN_SELLING_DECIMALS ],
			(string) $this->record[ self::COLUMN_SELLING_SYMBOL ]
		);
	}

	/** 販売者のアドレス */
	public function sellerAddress(): string {
		return (string) $this->record[ self::COLUMN_SELLER_ADDRESS ];
	}

	/** 支払い金額 */
	public function paymentPrice(): Price {
		return new Price(
			(string) $this->record[ self::COLUMN_PAYMENT_AMOUNT_HEX ],
			(int) $this->record[ self::COLUMN_TOKEN_DECIMAL ],
			(string) $this->record[ self::COLUMN_TOKEN_SYMBOL ]
		);
	}

	/** 購入者のアドレス */
	public function consumerAddress(): string {
		return (string) $this->record[ self::COLUMN_CONSUMER_ADDRESS ];
	}

	/** 販売履歴レコードの作成日時 */
	public function createdAt(): DateTime {
		return new DateTime( $this->record[ self::COLUMN_CREATED_AT ] );
	}

	/** 取引が行われたブロック番号 */
	public function blockNumber(): int {
		return (int) $this->record[ self::COLUMN_BLOCK_NUMBER ];
	}

	/** トランザクションハッシュ */
	public function transactionHash(): string {
		return (string) $this->record[ self::COLUMN_TRANSACTION_HASH ];
	}

	/** 販売者の利益 */
	public function sellerProfitPrice(): Price {
		return new Price(
			(string) $this->record[ self::COLUMN_SELLER_PROFIT_AMOUNT_HEX ],
			(int) $this->record[ self::COLUMN_TOKEN_DECIMAL ],
			(string) $this->record[ self::COLUMN_TOKEN_SYMBOL ]
		);
	}

	/** 手数料 */
	public function handlingFeePrice(): Price {
		return new Price(
			(string) $this->record[ self::COLUMN_HANDLING_FEE_AMOUNT_HEX ],
			(int) $this->record[ self::COLUMN_TOKEN_DECIMAL ],
			(string) $this->record[ self::COLUMN_TOKEN_SYMBOL ]
		);
	}

	/** 支払いトークン */
	public function paymentToken(): Token {
		return ( new TokenRepository() )->get( (int) $this->record[ self::COLUMN_CHAIN_ID ], Address::from( (string) $this->record[ self::COLUMN_TOKEN_ADDRESS ] ) );
	}
}
