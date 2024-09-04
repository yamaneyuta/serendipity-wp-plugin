import assert from 'assert';
import { useContext } from 'react';
import { WidgetAttributesContext } from './WidgetAttributesProvider';

/**
 * ウィジェットの属性(HTMLコメントで保存される内容)をを取得または設定する機能を提供します。
 */
export const useWidgetAttributes = () => {
	const context = useContext( WidgetAttributesContext );
	assert( context, '[BEE69350] Context is not found' );

	return context;
};
