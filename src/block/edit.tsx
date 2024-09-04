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

import { BlockEditProps } from '@wordpress/blocks';
import { GutenbergPostEdit } from './GutenbergPostEdit';
import { GutenbergPostEditProvider } from './provider/GutenbergPostEditProvider';
import { WidgetAttributes } from './types/WidgetAttributes';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @param root0
 * @param root0.setAttributes
 * @param root0.attributes
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 */
const Edit: React.FC< BlockEditProps< WidgetAttributes > > = ( { setAttributes, attributes } ) => {
	const blockProps = useBlockProps?.() ?? {};

	return (
		<div { ...blockProps }>
			<GutenbergPostEditProvider attributes={ attributes } setAttributes={ setAttributes }>
				<GutenbergPostEdit />
			</GutenbergPostEditProvider>
		</div>
	);
};

export default Edit;
