import * as ReactDOMClient from 'react-dom/client'
import { queries } from '@testing-library/dom'
import { renderHook as _renderHook, Queries, RenderHookOptions, RenderHookResult } from "@testing-library/react";

type RendererableContainer = ReactDOMClient.Container
type HydrateableContainer = Parameters<typeof ReactDOMClient['hydrateRoot']>[0]

/**
 * renderHookに例外が発生する関数オブジェクトを渡すとエラーログが出力されるので、それを抑制するラッパー関数。
 *
 * 発生時バージョン: @testing-library/react@16.0.0
 * 参考URL: https://stackoverflow.com/questions/72776771/i-need-to-render-a-custom-hook-and-test-the-error-message-when-someone-tries-to#answer-77968325
 */
export const renderHook = <
	Result,
	Props,
	Q extends Queries = typeof queries,
	Container extends RendererableContainer | HydrateableContainer = HTMLElement,
	BaseElement extends RendererableContainer | HydrateableContainer = Container,
>(
	render: (initialProps: Props) => Result,
	options?: RenderHookOptions<Props, Q, Container, BaseElement>,
): RenderHookResult<Result, Props> => {
	const spy = jest.spyOn(console, 'error').mockImplementation(() => { });
	try {
		return _renderHook(render, options);
	}
	finally {
		spy.mockRestore();
	}
};
