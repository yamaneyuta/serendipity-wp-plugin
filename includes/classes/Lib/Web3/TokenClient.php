<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use phpseclib\Math\BigInteger;
use Web3\Contract;

class TokenClient {
	public function __construct( string $rpc_url, string $contract_address ) {
		$this->token = ( new ContractFactory() )->create( $rpc_url, ( new TokenAbi() )->get(), $contract_address );
	}
	private Contract $token;

	/**
	 * トークンの小数点以下桁数を取得します。
	 */
	public function decimals(): int {
		/** @var int|null */
		$result = null;
		$this->token->call(
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
	 * トークンの通貨シンボルを取得します。
	 */
	public function symbol(): string {
		/** @var string|null */
		$result = null;
		$this->token->call(
			'symbol',
			function ( $err, $res ) use ( &$result ) {
				if ( $err ) {
					throw $err;
				}
				assert( is_string( $res[0] ?? null ), '[20FD9BCD] symbol is not string.' );
				assert( 0 < strlen( $res[0] ), '[6F010DF1] symbol is empty.' );
				$result = $res[0];
			}
		);

		assert( is_string( $result ) );
		return $result;
	}
}


/**
 * @internal
 */
class TokenAbi {

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
					"name": "symbol",
					"outputs": [
						{
							"internalType": "string",
							"name": "",
							"type": "string"
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
