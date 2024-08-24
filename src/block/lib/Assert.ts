export class Assert {
	/**
	 * 指定した文字列が16進数の文字列でない場合に例外をスローします。
	 * @param amountHex 16進数の文字列
	 */
	public static isAmountHex( amountHex: string ) {
		if ( ! amountHex.match( /^0x[0-9a-fA-F]+$/ ) ) {
			throw new Error( `[2407D05F] Invalid amountHex: ${ amountHex }` );
		}
	}

	/**
	 * 指定した小数点以下桁数が不正な場合に例外をスローします。
	 * @param decimals 小数点以下桁数
	 */
	public static isDecimals( decimals: number ) {
		if ( decimals < 0 ) {
			throw new Error( `[AA0993FF] Invalid decimals: ${ decimals }` );
		}
	}
}
