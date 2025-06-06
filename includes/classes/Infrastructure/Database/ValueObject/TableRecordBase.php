<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\ValueObject;

use stdClass;

abstract class TableRecordBase {
	/**
	 * このインスタンスのプロパティをレコードの値で初期化します。
	 * ※ フィールドにアクセスできるように子クラスのフィールドはpublicまたはprotectedで定義してください。
	 *
	 * @param stdClass $record テーブルから取得したレコード
	 */
	protected function import( stdClass $record ) {
		foreach ( $record as $property => $value ) {
			assert( property_exists( $this, $property ), '[D9F3A1B2] Invalid property: ' . $property );
			$this->{$property} = $value;
		}
	}
}
