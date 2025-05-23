<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

class BoolOption {

	//
	// falseを保存するとnullで返ってきてしまうため、
	// `1`または`0`を書き込む仕様としています。
	//

	/** trueを保存する際にテーブルに書き込まれる値(変更禁止) */
	private const TRUE_VALUE = 1;
	/** falseを保存する際にテーブルに書き込まれる値(変更禁止) */
	private const FALSE_VALUE = 0;

	public function __construct( string $option_key_name ) {
		$this->option = new IntOption( $option_key_name );
	}

	private IntOption $option;

	public function get( $default = null ): ?bool {
		$ret = $this->option->get( $default );
		return is_null( $ret ) ? null : $ret !== self::FALSE_VALUE;
	}

	public function update( bool $value, ?bool $autoload = null ): void {
		$this->option->update( $value ? self::TRUE_VALUE : self::FALSE_VALUE, $autoload );
	}
}
