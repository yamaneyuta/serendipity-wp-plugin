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
	public function sort( array $versions, string $order = 'ASC' ): array {
		assert( strtoupper( $order ) === 'ASC' || strtoupper( $order ) === 'DESC' );
		$is_reverse = strtoupper( $order ) === 'DESC';
		$result     = $versions;

		// version_compare を用いて比較を行う
		usort(
			$result,
			function ( string $a, string $b ) use ( $is_reverse ): int {
				$compare_result = version_compare( $a, $b );
				return $is_reverse ? $compare_result * -1 : $compare_result;
			}
		);

		return $result;
	}
}
