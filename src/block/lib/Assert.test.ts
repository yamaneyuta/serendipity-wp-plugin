import { Assert } from './Assert';

/**
 * 16進数の文字列かどうかを検証するテスト
 */
describe( '[E5FC205F] isAmountHex()', () => {
	describe( '[845ED33C] valid', () => {
		// hexとして有効な文字列
		const dataset: string[] = [ '0x1234567890abcdef', '0x0', '0x00' ];

		for ( const amountHex of dataset ) {
			it( `[F96611B3] should not throw error - amountHex: ${ amountHex }`, () => {
				expect( () => Assert.isAmountHex( amountHex ) ).not.toThrow();
			} );
		}
	} );

	describe( '[22176981] invalid', () => {
		// hexとして無効な文字列
		// ``や`0x`は本システムで無効なhexとして扱う
		const dataset: string[] = [ '', '0x', '12345', 'abcd' ];

		for ( const amountHex of dataset ) {
			it( `[D0A61A4A] should throw error - amountHex: ${ amountHex }`, () => {
				expect( () => Assert.isAmountHex( amountHex ) ).toThrow( '[2407D05F]' );
			} );
		}
	} );
} );

/**
 * decimalsが有効な値かどうかを検証するテスト
 */
describe( '[EB44594C] isDecimals()', () => {
	describe( '[6024D8BA] valid', () => {
		// 有効な値
		const dataset: number[] = [ 0, 1, 2, 3, 4, 5, 18 ];

		for ( const decimals of dataset ) {
			it( `[D275544D] should not throw error - decimals: ${ decimals }`, () => {
				expect( () => Assert.isDecimals( decimals ) ).not.toThrow();
			} );
		}
	} );

	describe( '[F7A3BDA2] invalid', () => {
		// 無効な値
		const dataset: number[] = [ -1, -2, -3, 0.1, NaN, Infinity, -Infinity ];

		for ( const decimals of dataset ) {
			it( `[C409DFE4] should throw error - decimals: ${ decimals }`, () => {
				expect( () => Assert.isDecimals( decimals ) ).toThrow( '[AA0993FF]' );
			} );
		}
	} );
} );
