<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\GraphQL;

class RootValue {
	/**
	 * @return array<string, mixed>
	 */
	public function get() {

		/** @var array<string, mixed> $result */
		$result = array(
			'echo'   => function ( array $root_value, array $args ): string {
				return $root_value['prefix'] . $args['message'];
			},
			'prefix' => 'You said: ',
		);

		return $result;
	}
}
