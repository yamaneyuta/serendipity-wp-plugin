/**
 * ブロックの属性(`wp_post`テーブルの`post_content`に保存されるHTMLのコメントに含まれる値)
 * (BlockAttributesは`@wordpress/blocks`に存在したため別の名称で定義)
 * ※ src/block/index.tsと型の同期をとること。
 */
export type WidgetAttributes = {
	/** 販売対象のネットワークカテゴリID */
	sellingNetworkCategoryID: number | null;

	/** 販売価格の値(sellingDecimalsの値と共に使用する) */
	sellingAmountHex: string | null;

	/** 販売価格の小数点以下桁数 */
	sellingDecimals: number | null;

	/** 販売価格の通貨シンボル */
	sellingSymbol: string | null;
};
