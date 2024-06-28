<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Algorithm\Sort;

class VersionSorter {

	/**
	 * バージョンを昇順に並び替えて返します。
	 *
	 * @param array<string> $versions
	 * @return array
	 */
	public function sort( array $versions ): array {
		$result = $versions;

		// version_compare を用いて比較を行う
		usort(
			$result,
			function ( string $a, string $b ): int {
				return version_compare( $a, $b );
			}
		);

		return $result;
	}
}
