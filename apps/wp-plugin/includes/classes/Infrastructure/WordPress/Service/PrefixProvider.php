<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Repository\Name\Prefix;

class PrefixProvider {

	// TODO: Prefixクラスから処理をこのクラスへ移動

	public function getOptionNamePrefix(): string {
		return ( new Prefix() )->optionKeyPrefix();
	}
}
