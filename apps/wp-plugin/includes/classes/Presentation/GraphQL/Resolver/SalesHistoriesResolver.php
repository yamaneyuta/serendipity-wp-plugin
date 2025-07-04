<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\SalesHistoryService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Entity\SalesHistory;

class SalesHistoriesResolver extends ResolverBase {

	public function __construct(
		UserAccessChecker $user_access_checker
	) {
		$this->user_access_checker = $user_access_checker;
	}
	private UserAccessChecker $user_access_checker;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var array */
		$filter = $args['filter'] ?? null;
		/** @var string */
		$invoice_id = $args['invoiceID'] ?? null;

		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		$sales_data_records = ( new SalesHistoryService() )->select( $invoice_id );

		$ret = array_map(
			fn ( SalesHistory $sales_data ) => array(
				'invoice'                  => array(
					'id'        => $sales_data->invoiceID(),
					'createdAt' => $sales_data->createdAt()->format( 'c' ),
					'chain'     => function () use ( $root_value, $sales_data ) {
						return $root_value['chain'](
							$root_value,
							array(
								'chainID' => $sales_data->chainID(),
							)
						);
					},
					'post'      => function () use ( $root_value, $sales_data ) {
						return $root_value['post'](
							$root_value,
							array(
								'postID' => $sales_data->postID(),
							)
						);
					},
					// 販売価格は請求書に記載されている価格を返す
					// ※ Postの販売価格は現在の販売価格であり、取引時の価格とは異なる場合があるため
					// 'sellingPrice' => array(
					// 'amountHex' => $sales_data->sellingPrice()->amountHex(),
					// 'decimals'  => $sales_data->sellingPrice()->decimals(),
					// 'symbol'    => $sales_data->sellingPrice()->symbol()->value(),
					// ),
				),

				'unlockPaywallTransaction' => array(
					'chain'             => function () use ( $root_value, $sales_data ) {
						return $root_value['chain'](
							$root_value,
							array(
								'chainID' => $sales_data->chainID(),
							)
						);
					},
					'blockNumber'       => $sales_data->blockNumber(),
					'transactionHash'   => $sales_data->transactionHash(),
					'sellerAddress'     => $sales_data->sellerAddress(),
					'consumerAddress'   => $sales_data->consumerAddress(),
					// 'paymentPrice' => array(
					// 'amountHex' => $sales_data->paymentPrice()->amountHex(),
					// 'decimals'  => $sales_data->paymentPrice()->decimals(),
					// 'symbol'    => $sales_data->paymentPrice()->symbol(),
					// ),
					'sellerProfitPrice' => array(
						'amountHex' => $sales_data->sellerProfitPrice()->amountHex(),
						'decimals'  => $sales_data->sellerProfitPrice()->decimals(),
						'symbol'    => $sales_data->sellerProfitPrice()->symbol()->value(),
					),
					'handlingFeePrice'  => array(
						'amountHex' => $sales_data->handlingFeePrice()->amountHex(),
						'decimals'  => $sales_data->handlingFeePrice()->decimals(),
						'symbol'    => $sales_data->handlingFeePrice()->symbol()->value(),
					),
				),
			),
			$sales_data_records
		);

		return $ret;
	}
}
