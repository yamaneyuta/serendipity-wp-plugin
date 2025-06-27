<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Dto\ChainDto;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

/** フィルタに合致するチェーン情報一覧を取得します */
class GetChainsByFilter {

	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}

	private ChainRepository $chain_repository;

	/**
	 * フィルタに合致するチェーン情報一覧を取得します。フィルタを指定しない場合はすべてのチェーン情報を取得します。
	 *
	 * @param int|null  $filter_chain_id チェーンIDでフィルタする場合に指定
	 * @param bool|null $filter_is_connectable 接続可能なチェーンでフィルタする場合に指定
	 * @return ChainDto[] フィルタに合致するチェーン情報の配列
	 */
	public function handle( ?int $filter_chain_id, ?bool $filter_is_connectable ): array {
		// 引数が増えてきた場合は引数をクラスインスタンスに変更する
		// 一旦はフィルタの値を直接渡す形で実装

		// フィルタ処理
		$chains_filter = new ChainsFilter();
		// チェーンIDでフィルタ
		$chains_filter = null !== $filter_chain_id ? $chains_filter->byChainID( new ChainID( $filter_chain_id ) ) : $chains_filter;
		// 接続可能なチェーンでフィルタ
		$chains_filter = null !== $filter_is_connectable ? $chains_filter->byConnectable( $filter_is_connectable ) : $chains_filter;

		$chains = $chains_filter->apply( $this->chain_repository->all() );

		return array_map( fn( $chain ) => ChainDto::fromEntity( $chain ), $chains );
	}
}
