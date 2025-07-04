import { jest } from '@jest/globals';
import { render as _render, RenderOptions, RenderResult } from '@testing-library/react';

/**
 * renderに例外が発生するコンポーネントを渡すとエラーログが出力されるので、それを抑制するラッパー関数。
 *
 * 発生時バージョン: @testing-library/react@16.0.0
 * @param ui
 * @param options
 * @see ./renderHook.ts
 */
export const render = ( ui: React.ReactNode, options?: Omit< RenderOptions, 'queries' > ): RenderResult => {
	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const spy = jest.spyOn( console, 'error' ).mockImplementation( () => {} );
	try {
		return _render( ui, options );
	} finally {
		spy.mockRestore();
	}
};
