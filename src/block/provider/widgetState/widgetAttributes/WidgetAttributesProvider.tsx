import { createContext, useEffect, useState } from 'react';
import { WidgetAttributes } from '../../../types/WidgetAttributes';

type WidgetAttributesContextType = ReturnType< typeof _useWidgetAttributes >;

export const WidgetAttributesContext = createContext< WidgetAttributesContextType | undefined >( undefined );

const _useWidgetAttributes = (
	attributes: Readonly< WidgetAttributes >,
	setAttributes: ( attrs: Partial< WidgetAttributes > ) => void
) => {
	const [ widgetAttributes, setWidgetAttributes ] = useState( structuredClone( attributes ) as WidgetAttributes );

	// 状態が更新された時は、`setAttributes`を呼び出してWordPressで管理されている`attributes`を更新する
	useEffect( () => {
		setAttributes( widgetAttributes );
	}, [ setAttributes, widgetAttributes ] );

	return {
		widgetAttributes,
		setWidgetAttributes,
	};
};

type WidgetAttributesProviderProps = {
	attributes: Readonly< WidgetAttributes >;
	setAttributes: ( attrs: Partial< WidgetAttributes > ) => void;
	children: React.ReactNode;
};

/**
 * ウィジェットの属性(HTMLコメントで保存される内容)を保持するコンテキストプロバイダー
 * @param root0
 * @param root0.children
 * @param root0.attributes
 * @param root0.setAttributes
 */
export const WidgetAttributesProvider: React.FC< WidgetAttributesProviderProps > = ( {
	attributes,
	setAttributes,
	children,
} ) => {
	const value = _useWidgetAttributes( attributes, setAttributes );
	return <WidgetAttributesContext.Provider value={ value }>{ children }</WidgetAttributesContext.Provider>;
};
