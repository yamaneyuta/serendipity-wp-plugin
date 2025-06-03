<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Repository\TableGateway\PaidContentTable;
use Cornix\Serendipity\Core\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\ValueObject\Price;

/**
 * 有料記事の情報を管理するクラス
 */
class PaidContentData {
	public function __construct( int $post_ID ) {
		$this->post_ID = $post_ID;
		$this->table   = new PaidContentTable( $GLOBALS['wpdb'] );
	}
	private int $post_ID;
	private PaidContentTable $table;

	private function record() {
		return $this->table->select( $this->post_ID );
	}

	/**
	 * 有料記事の内容を取得します。
	 */
	public function content(): ?string {
		$record = $this->record();
		if ( ! is_null( $record ) && ! is_null( $record->paid_content ) ) {
			return $record->paid_content;
		} else {
			return null;
		}
	}

	/**
	 * 有料記事の情報を保存します。
	 */
	public function save( string $paid_content, ?NetworkCategory $selling_network_category, ?Price $selling_price ): void {
		$this->table->set( $this->post_ID, $paid_content, $selling_network_category, $selling_price );
	}

	/**
	 * 有料記事の情報を削除します。
	 */
	public function delete(): void {
		$this->table->delete( $this->post_ID );
	}

	/**
	 * 有料記事を販売するネットワークカテゴリを取得します。
	 *
	 * @return null|NetworkCategory 有料記事が存在しない場合、またはネットワークカテゴリが指定されていない場合はnullを返します。
	 */
	public function sellingNetworkCategory(): ?NetworkCategory {
		$record = $this->record();
		if ( is_null( $record ) ) {
			return null; // レコードが存在しない場合はnullを返す
		}
		return NetworkCategory::from( $record->selling_network_category_id );
	}

	/**
	 * 有料記事の販売価格を取得します。
	 *
	 * @return null|Price 有料記事が存在しない場合、または販売価格が指定されていない場合はnullを返します。
	 */
	public function sellingPrice(): ?Price {
		$record = $this->record();
		if ( ! is_null( $record ) && ! is_null( $record->selling_amount_hex ) && ! is_null( $record->selling_decimals ) && ! is_null( $record->selling_symbol ) ) {
			return new Price(
				$record->selling_amount_hex,
				$record->selling_decimals,
				$record->selling_symbol
			);
		} else {
			return null;
		}
	}
}
