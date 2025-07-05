import crypto from 'node:crypto';

/**
 * 指定された文字列のMD5ハッシュを返します。
 * @param str
 */
export const md5 = ( str: string ): string => {
	return crypto.createHash( 'md5' ).update( str ).digest( 'hex' );
};
