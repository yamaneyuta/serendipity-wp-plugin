<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\ValueObject\ObjectValueArrayBase;

// オブジェクトを配列として扱うため、ObjectValueArrayBaseと実質的に同じ
// ※ class_aliasを使用する方法はエディタで認識できないため不採用
/**
 * @template T
 */
abstract class EntityArrayBase extends ObjectValueArrayBase {
	/**
	 * @param T[] $items
	 */
	protected function __construct( array $items ) {
		parent::__construct( $items );
	}
}
