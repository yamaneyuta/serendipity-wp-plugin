/**
 * 指定された文字列のMD5ハッシュを返します。
 * @param str
 */
export const md5 = ( str: string ): string => {
	const crypto = require( 'crypto' );
	return crypto.createHash( 'md5' ).update( str ).digest( 'hex' );
};
