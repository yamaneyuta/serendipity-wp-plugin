/**
 * ブロックの属性(`wp_post`テーブルの`post_content`に保存されるHTMLのコメントに含まれる値)
 * (BlockAttributesは`@wordpress/blocks`に存在したため別の名称で定義)
 */
export type WidgetAttributes = {
	/** 販売するネットワーク(MAINNET, TESTNET, PRIVATENET) */
	sellingNetwork: string | null;

	/** 販売価格の値(sellingDecimalsの値と共に使用する) */
	sellingAmountHex: string;

	/** 販売価格の小数点以下桁数 */
	sellingDecimals: number;

	/** 販売価格の通貨シンボル */
	sellingSymbol: string | null;
};
