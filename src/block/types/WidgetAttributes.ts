/**
 * ブロックの属性(`wp_post`テーブルの`post_content`に保存されるHTMLのコメントに含まれる値)
 * (BlockAttributesは`@wordpress/blocks`に存在したため別の名称で定義)
 */
export type WidgetAttributes = {
	/** 販売するネットワーク(MAINNET, TESTNET, PRIVATENET) */
	sellingNetwork: string | null;

	/** 販売価格 */
	sellingPrice: {
		/** 数量 */
		amountHex: string;
		/** 小数点以下桁数 */
		decimals: number;
		/** 通貨シンボル */
		symbol: string | null;
	};
};
