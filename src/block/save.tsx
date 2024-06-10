/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';
import { getBlockClassName } from "./features/className/getBlockClassName";

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {WPElement} Element to render.
 */
export default function save() {
	const className = getBlockClassName();
	const myProps = {
		className: className,
	};
	//	ウィジェット表示用のクラス名を付与
	const props = useBlockProps?.save( myProps ) ?? myProps;
	return <div { ...props }></div>;
}
