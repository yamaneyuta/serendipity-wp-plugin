import { Placeholder } from '@wordpress/components';
import { widget } from '@wordpress/icons';
import { SymbolSelect } from './features/symbolSelect/SymbolSelect';
import { NetworkCategorySelect } from './features/networkCategorySelect/NetworkCategorySelect';
import { useNetworkCategorySelectProps } from './features/networkCategorySelect/useNetworkCategorySelectProps';
import { useSymbolSelectProps } from './features/symbolSelect/useSymbolSelectProps';
import { usePriceValueInputProps } from './features/priceValueInput/usePriceValueInputProps';
import { PriceValueInput } from './features/priceValueInput/PriceValueInput';
import { useInitWidgetState } from './features/initialize/useInitWidgetState';
import { useUpdateWidgetAttributes } from './features/update/useUpdateWidgetAttributes';

type GutenbergPostEditProps = {};

export const GutenbergPostEdit: React.FC< GutenbergPostEditProps > = ( {} ) => {
	// ウィジェットの状態を初期化
	useInitWidgetState();
	// ウィジェットの属性を更新
	useUpdateWidgetAttributes();

	return (
		<Placeholder icon={ widget } label={ 'Qik Chain Pay' }>
			<div style={ { width: '100%' } }>
				<NetworkCategorySelect { ...useNetworkCategorySelectProps() } />
			</div>
			<div style={ { display: 'flex', alignItems: 'flex-end' } }>
				<PriceValueInput
					{ ...usePriceValueInputProps() }
					width={ 90 }
					style={ { display: 'block', maxWidth: '100px' } }
				/>
				<SymbolSelect { ...useSymbolSelectProps() } />
			</div>
		</Placeholder>
	);
};
