<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Repository\ConsumerTerms;

class ConsumerTermsVersionResolver extends ResolverBase {
	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		// アクセス制御は不要

		// 購入者向け利用規約のバージョンを取得
		return ( new ConsumerTerms() )->currentVersion();
	}
}
