<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use phpseclib\Math\BigInteger;
use Web3\Contract;

class OracleClient {
	public function __construct( string $rpc_url, Oracle $oracle ) {
		$this->oracle_contract = ( new ContractFactory() )->create( $rpc_url, ( new OracleAbi() )->get(), $oracle->address() );
	}
	private Contract $oracle_contract;

	/**
	 * レートの小数点以下桁数を取得します。
	 */
	public function decimals(): int {
		/** @var int|null */
		$result = null;
		$this->oracle_contract->call(
			'decimals',
			function ( $err, $res ) use ( &$result ) {
				if ( $err ) {
					throw $err;
				}
				$decimals = $res[0];
				assert( $decimals instanceof BigInteger );
				$result = (int) $decimals->toString();
			}
		);

		assert( is_int( $result ) );
		return $result;
	}

	/**
	 * オラクルの説明を取得します。
	 */
	public function description(): string {
		/** @var string|null */
		$result = null;
		$this->oracle_contract->call(
			'description',
			function ( $err, $res ) use ( &$result ) {
				if ( $err ) {
					throw $err;
				}
				assert( is_string( $res[0] ) );
				$result = $res[0];
			}
		);

		assert( is_string( $result ) );
		return $result;
	}

	/**
	 * 最新のレートを取得します。
	 * ※ `latestAnswer`は非推奨のため、`latestAnswer`で取得した値を使用
	 *
	 * @return BigInteger
	 */
	public function latestAnswer(): BigInteger {
		return $this->latestRoundData()->answer();
	}

	/**
	 * 最新のデータを取得します。
	 */
	public function latestRoundData(): OracleRoundData {
		/** @var OracleRoundData|null */
		$result = null;
		$this->oracle_contract->call(
			'latestRoundData',
			function ( $err, $res ) use ( &$result ) {
				if ( $err ) {
					throw $err;
				}

				$round_ID          = $res['roundId'];
				$answer            = $res['answer'];
				$started_at        = $res['startedAt'];
				$updated_at        = $res['updatedAt'];
				$answered_in_round = $res['answeredInRound'];

				assert( $round_ID instanceof BigInteger );
				assert( $answer instanceof BigInteger );
				assert( $started_at instanceof BigInteger );
				assert( $updated_at instanceof BigInteger );
				assert( $answered_in_round instanceof BigInteger );

				$result = new OracleRoundData( $round_ID, $answer, $started_at, $updated_at, $answered_in_round );
			}
		);

		assert( $result instanceof OracleRoundData );
		return $result;
	}
}


/**
 * @internal
 */
class OracleAbi {

	public function get(): array {
		$abi_json = <<<JSON
		{
			"abi": [
				{
					"inputs": [],
					"name": "decimals",
					"outputs": [
						{
							"internalType": "uint8",
							"name": "",
							"type": "uint8"
						}
					],
					"stateMutability": "view",
					"type": "function"
				},
				{
					"inputs": [],
					"name": "description",
					"outputs": [
						{
							"internalType": "string",
							"name": "",
							"type": "string"
						}
					],
					"stateMutability": "view",
					"type": "function"
				},
				{
					"inputs": [],
					"name": "latestRoundData",
					"outputs": [
						{
							"internalType": "uint80",
							"name": "roundId",
							"type": "uint80"
						},
						{
							"internalType": "int256",
							"name": "answer",
							"type": "int256"
						},
						{
							"internalType": "uint256",
							"name": "startedAt",
							"type": "uint256"
						},
						{
							"internalType": "uint256",
							"name": "updatedAt",
							"type": "uint256"
						},
						{
							"internalType": "uint80",
							"name": "answeredInRound",
							"type": "uint80"
						}
					],
					"stateMutability": "view",
					"type": "function"
				}
			]
		}
		JSON;

		return json_decode( $abi_json, true )['abi'];
	}
}

/** @internal */
class OracleRoundData {
	public function __construct( BigInteger $round_ID, BigInteger $answer, BigInteger $started_at, BigInteger $updated_at, BigInteger $answered_in_round ) {
		$this->round_ID          = $round_ID;
		$this->answer            = $answer;
		$this->started_at        = $started_at;
		$this->updated_at        = $updated_at;
		$this->answered_in_round = $answered_in_round;
	}

	private BigInteger $round_ID;
	private BigInteger $answer;
	private BigInteger $started_at;
	private BigInteger $updated_at;
	private BigInteger $answered_in_round;

	public function roundID(): BigInteger {
		return $this->round_ID;
	}

	public function answer(): BigInteger {
		return $this->answer;
	}

	public function startedAt(): BigInteger {
		return $this->started_at;
	}

	public function updatedAt(): BigInteger {
		return $this->updated_at;
	}

	public function answeredInRound(): BigInteger {
		return $this->answered_in_round;
	}
}
