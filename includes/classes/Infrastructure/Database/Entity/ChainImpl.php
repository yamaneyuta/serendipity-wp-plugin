<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Entity;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\ChainTableRecord;

class ChainImpl extends Chain {

	/**
	 * @param int         $chain_id
	 * @param string      $name
	 * @param null|string $rpc_url
	 * @param int|string  $confirmations
	 */
	private function __construct(
		int $chain_id,
		string $name,
		?string $rpc_url,
		$confirmations
	) {
		parent::__construct( $chain_id, $name, $rpc_url, $confirmations );
	}

	public static function fromTableRecord( ChainTableRecord $record ): self {
		/** @var string $confirmations */
		$confirmations = $record->confirmations();
		return new self(
			$record->chainID(),
			$record->name(),
			$record->rpcURL(),
			is_numeric( $confirmations ) ? (int) $confirmations : $confirmations,
		);
	}
}
