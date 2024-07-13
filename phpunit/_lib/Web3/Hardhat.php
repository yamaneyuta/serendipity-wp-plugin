<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Security\Assert;
use Cornix\Serendipity\Core\Lib\Strings\Strings;
use Web3\Methods\EthMethod;
use Web3\Providers\HttpProvider;
use Web3\Providers\Provider;
use Web3\Validators\QuantityValidator;

class Hardhat {
	public function __construct( string $rpc_url ) {
		$this->rpc_url = $rpc_url;
	}
	private string $rpc_url;

	/**
	 * 自動マイニングが有効かどうかを取得します。
	 */
	public function getAutomine(): bool {
		$provider = new HardhatProvider( $this->rpc_url );

		$is_auto_mine = false;
		$provider->getAutomine(
			function ( $err, $res ) use ( &$is_auto_mine ) {
				if ( $err ) {
					throw $err;
				}
				assert( is_bool( $res ), '[24270968] The result must be boolean.' );
				$is_auto_mine = $res;
			}
		);

		return $is_auto_mine;
	}


	/**
	 * スナップショットを作成し、そのIDを取得します。
	 */
	public function snapshot(): string {
		$provider = new HardhatProvider( $this->rpc_url );

		$id = '';

		$provider->snapshot(
			function ( $err, $res ) use ( &$id ) {
				if ( $err ) {
					throw $err;
				}
				assert( is_string( $res ), '[48861AA2] The result must be string.' );

				error_log( 'res: ' . var_export( $res, true ) );
				$id = $res; // `0xc`のようなID
			}
		);

		Assert::isAmountHex( $id );

		return $id;
	}

	/**
	 * スナップショットを復元します。
	 *
	 * @param string $id `snapshot()`で取得したID
	 */
	public function revert( string $id ): bool {
		$provider = new HardhatProvider( $this->rpc_url );

		$is_reverted = false;
		$provider->revert(
			$id,
			function ( $err, $res ) use ( &$is_reverted ) {
				if ( $err ) {
					throw $err;
				}
				assert( is_bool( $res ), '[C9C26348] The result must be boolean.' );
				$is_reverted = $res;
			}
		);

		assert( $is_reverted, '[1A839FBF] The result must be true.' );

		return $is_reverted;
	}
}


/**
 * @internal
 */
class HardhatProvider {
	public function __construct( string $rpc_url, $timeout = 1 ) {
		assert( Strings::starts_with( $rpc_url, 'http://' ) ); // hardhatはhttpプロトコルのみ対応
		$this->provider = new HttpProvider( $rpc_url, $timeout );
	}

	private Provider $provider;


	public function __call( $name, $arguments ) {
		$callback = array_pop( $arguments );
		if ( ! is_callable( $callback ) ) {
			throw new \InvalidArgumentException( '[485A40CB] The last param must be callback function.' );
		}

		$methodObject = $this->getMethodObject( $name, $arguments );
		if ( $methodObject->validate( $arguments ) ) {
			$inputs                  = $methodObject->transform( $arguments, $methodObject->inputFormatters );
			$methodObject->arguments = $inputs;
			/** @disregard P1013 Undefined method */
			return $this->provider->send( $methodObject, $callback );
		}
	}


	private function getMethodObject( string $method, array $arguments ) {
		switch ( $method ) {
			case 'getAutomine':
				return new GetAutomine( 'hardhat_getAutomine', $arguments );
			case 'snapshot':
				return new Snapshot( 'evm_snapshot', $arguments );
			case 'revert':
				return new Revert( 'evm_revert', $arguments );
			default:
				throw new \InvalidArgumentException( '[BA4B5347] Invalid method. - method: ' . $method );
		}
	}
}


/**
 * @internal
 */
class GetAutomine extends EthMethod {
	protected $validators       = array();
	protected $inputFormatters  = array();
	protected $outputFormatters = array();
	protected $defaultValues    = array();
}

/**
 * @internal
 */
class Snapshot extends EthMethod {
	protected $validators       = array();
	protected $inputFormatters  = array();
	protected $outputFormatters = array();
	protected $defaultValues    = array();
}

class Revert extends EthMethod {
	protected $validators       = array(
		QuantityValidator::class,
	);
	protected $inputFormatters  = array();
	protected $outputFormatters = array();
	protected $defaultValues    = array();
}
