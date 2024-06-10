/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

// import { GutenbergPostEdit } from './components/GutenbergPostEdit';
import { BlockEditProps } from '@wordpress/blocks';

import { GutenbergPostEdit } from "./features/GutenbergPostEdit";

type BlockAttributes = {
	dummy: string;
};

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
const Edit: React.FC< BlockEditProps< BlockAttributes > > = ( {
	setAttributes,
} ) => {
	const blockProps = useBlockProps?.() ?? {};

	// ユーザーの入力によって画面上のデータが更新された時に呼び出す関数。
	// `setAttributes`で値を設定することでWordPressの保存ボタンが押下できるようになる。
	const onDataChanged = () => {
		// 設定する値の意味はないので、毎回値が異なればよい。
		// 今回は日時を設定している。
		setAttributes( { dummy: new Date().toISOString() } );
	};

	return (
		<div { ...blockProps }>
			{/* <QueryClientProvider client={ client }>
				<BlockProvider onDataChanged={ onDataChanged }> */}
					<GutenbergPostEdit />
				{/* </BlockProvider>
			</QueryClientProvider> */}
		</div>
	);
};
export default Edit;
