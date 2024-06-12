interface BlockInputProps extends React.ComponentProps< 'input' > {}

/**
 * ブロックエディタで描画するテキスト入力コントロール
 * @param root0
 * @param root0.type
 * @param root0.onCut
 * @param root0.onKeyDown
 * @param root0.onPaste
 */
export const BlockInput: React.FC< BlockInputProps > = ( { type, onCut, onKeyDown, onPaste, ...props } ) => {
	return (
		<input
			{ ...props }
			type={ type ?? 'text' }
			onCut={ ( e ) => {
				onCut?.( e );
				// カット処理を行ったとき、ブロックが削除されてしまうため
				// 親要素以降へのイベント伝播をキャンセルする
				e.stopPropagation();
			} }
			onKeyDown={ ( e ) => {
				onKeyDown?.( e );

				// TODO: Ctrl-Zが押された時に`e.preventDefault`や`e.stopPropagation`を行っても
				// エディタ画面でUndo処理が行われてしまうため、調査が必要。
			} }
			onPaste={ ( e ) => {
				onPaste?.( e );
				// 貼り付け処理を行ったとき、ブロックが削除されて文字列が貼り付けられてしまうため
				// 親要素以降へのイベント伝播をキャンセルする
				e.stopPropagation();
			} }
		/>
	);
};
