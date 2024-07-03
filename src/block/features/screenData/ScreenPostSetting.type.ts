// 画面上で保持する設定情報
// 基本構造はPostSettingQueryの型と同じだが、画面上のデータとして扱いやすくするように
// undefinedを許容するなどの変更を加えている
export type ScreenPostSetting = {
	sellingPrice?: {
		amountHex: string;
		decimals: number;
		symbol?: string;
	};
};
