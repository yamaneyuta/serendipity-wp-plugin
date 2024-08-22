import { NetworkType } from '../../../types/gql/generated';

/**
 * 画面上で保持する設定情報
 *
 * 基本構造はPostSettingQueryの型と同じだが、画面上のデータとして扱いやすくするように
 * undefinedを許容するなどの変更を加えている
 */
export type ScreenPostSetting = {
	/**
	 * 販売価格 null: 未指定, undefined: 読み込み中
	 */
	sellingPrice?: {
		amountHex: string;
		decimals: number;
		symbol?: string | null;
	} | null;

	/**
	 * 販売するネットワーク null: 未指定, undefined: 読み込み中
	 */
	sellingNetwork?: NetworkType | null;
};
