<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Convert;

class HtmlFormat {
	/**
	 * HTMLコメントを削除します。
	 *
	 * @param string $html HTML文字列
	 * @return string コメントを削除したHTML文字列
	 */
	public static function removeHtmlComments( string $html ): string {
		return preg_replace( '/<!--.*?-->/s', '', $html );
	}
}
