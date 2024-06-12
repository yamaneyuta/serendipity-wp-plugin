import { useMemo } from '@wordpress/element';

interface BlockButtonProps extends React.ComponentProps< 'button' > {
	isBusy?: boolean;
}

/**
 * ブロックエディタで描画するボタンコントロール
 *
 * @param root0
 * @param root0.isBusy
 * @param root0.disabled
 * @param root0.className
 * @param root0.children
 */
export const BlockButton: React.FC< BlockButtonProps > = ( {
	isBusy,

	disabled,
	className,
	children,
	...props
} ) => {
	const newClassName = useBlockButtonClassName( isBusy, className );

	return (
		<button
			{ ...props }
			disabled={ disabled || isBusy }
			aria-disabled={ disabled }
			// class="components-button editor-post-publish-button editor-post-publish-button__button is-primary"
			className={ newClassName }
		>
			{ children }
		</button>
	);
};

/**
 * ブロックエディタで描画するボタンコントロールのクラスを取得します。
 * @param isBusy
 * @param className
 */
const useBlockButtonClassName = ( isBusy: boolean | undefined, className: string | undefined ) => {
	return useMemo( () => {
		const classes = [ 'components-button', 'is-primary' ];
		if ( isBusy ) {
			classes.push( 'is-busy' );
		}
		if ( className ) {
			classes.push( className );
		}
		return classes.join( ' ' );
	}, [ isBusy, className ] );
};
