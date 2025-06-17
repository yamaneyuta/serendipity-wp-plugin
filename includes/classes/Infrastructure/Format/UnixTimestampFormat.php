<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Format;

use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;
use DateTimeImmutable;

class UnixTimestampFormat {

	/** UnixタイムスタンプをMySQL形式のDATETIME文字列に変換します。 */
	public static function toMySQL( UnixTimestamp $timestamp ): string {
		return ( new DateTimeImmutable() )->setTimestamp( $timestamp->value() )->format( 'Y-m-d H:i:s' );
	}

	/** MySQL形式のDATETIME文字列をUnixタイムスタンプに変換します。 */
	public static function fromMySQL( ?string $mysql_datetime ): ?UnixTimestamp {
		if ( null === $mysql_datetime ) {
			return null;
		}

		$datetime = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $mysql_datetime );
		if ( false === $datetime ) {
			throw new \InvalidArgumentException( '[5D7C87DE] Invalid MySQL DATETIME format: ' . $mysql_datetime );
		}
		return new UnixTimestamp( $datetime->getTimestamp() );
	}
}
