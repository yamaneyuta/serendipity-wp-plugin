<?php

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Definition\AppContractDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Definition\NetworkCategoryDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Environment;
use Cornix\Serendipity\Core\Types\NetworkCategory;

/**
 * 本アプリケーション用のコントラクトに関する情報を提供します
 */
class AppContract {
	public function __construct( Environment $environment = null ) {
		$this->definition = new AppContractDefinition();

		// 開発環境でない場合はプライベートネットの定義を除外するためのコールバック
		$environment        = $environment ?? new Environment();
		$this->chain_filter = $environment->isDevelopmentMode()
			? fn( $chain_ID ) => true
			: fn( $chain_ID ) => ( new NetworkCategoryDefinition() )->get( $chain_ID ) !== NetworkCategory::privatenet();
	}

	private AppContractDefinition $definition;

	/** @var callable */
	private $chain_filter;

	/**
	 * アプリケーションがデプロイされているチェーンIDをすべて取得します。
	 *
	 * ※ 開発環境でない場合は、プライベートネットワークのチェーンIDは含まれません。
	 *
	 * @return int[]
	 */
	public function allChainIDs(): array {
		return array_values( array_filter( $this->definition->allChainIDs(), $this->chain_filter ) );
	}

	/**
	 * 指定されたチェーンIDに対応するアプリケーションのコントラクトアドレスを取得します。
	 */
	public function address( int $chain_ID ): ?string {
		return in_array( $chain_ID, $this->allChainIDs() ) ? $this->definition->address( $chain_ID ) : null;
	}
}
