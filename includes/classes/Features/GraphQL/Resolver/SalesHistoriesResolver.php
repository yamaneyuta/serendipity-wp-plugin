<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\SalesData;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class SalesHistoriesResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var array */
		$filter                     = $args['filter'] ?? null;
		/** @var string */
		$invoice_id				= $args['invoiceID'] ?? null;

		Judge::checkHasAdminRole(); // 管理者権限が必要

		$sales_data_records = (new SalesData())->select( $invoice_id );

		$ret = array_map(
			fn ( $sales_data ) => array(
				/*
					invoiceID: String!	# 請求書ID
					invoiceCreatedAt: String!	# 請求書作成日時
					postID: Int!	# 投稿ID
					postTitle: String!	# 投稿タイトル
					sellingPrice: Price!	# 販売価格
					sellerProfit: Price!	# 売上金額
					handlingFee: Price!		# 手数料
					sellerAddress: String!	# 販売者のアドレス
					consumerAddress: String!	# 購入者のアドレス
					chainID: Int!	# チェーンID
					transactionHash: String!	# トランザクションハッシュ
				*/
				'invoiceID' => $sales_data->invoiceID(),
				'invoiceCreatedAt' => $sales_data->createdAt()->format( 'c' ),
				'post' => function() use ( $root_value, $sales_data ) {
					return $root_value['post'](
						$root_value,
						array(
							'postID' => $sales_data->postID(),
						)
					);
				},

				// TODO: 以下の販売価格は現在の販売価格のため、sales_dataから取得する
				'sellingPrice' => function() use ( $root_value, $sales_data ) {
					return $root_value['sellingPrice'](
						$root_value,
						array(
							'postID' => $sales_data->postID(),
						)
					);
				}
			),
			$sales_data_records
		);

		return $ret;
	}
}