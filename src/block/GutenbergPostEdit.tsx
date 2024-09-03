import { Placeholder } from '@wordpress/components';
import { widget } from '@wordpress/icons';
import { SymbolSelect } from './features/symbolSelect/SymbolSelect';
import { NetworkSelect } from './features/networkSelect/NetworkSelect';
import { useNetworkSelectProps } from './features/networkSelect/useNetworkSelectProps';
import { useSymbolSelectProps } from './features/symbolSelect/useSymbolSelectProps';
import { usePriceValueInputProps } from './features/priceValueInput/usePriceValueInputProps';
import { PriceValueInput } from './features/priceValueInput/PriceValueInput';

type GutenbergPostEditProps = {
	onDataChanged: () => void;
};

export const GutenbergPostEdit: React.FC< GutenbergPostEditProps > = ( { onDataChanged } ) => {

	return (
		<Placeholder icon={ widget } label={ 'serendipity' }>
			<div style={ { width: '100%' } }>
				<NetworkSelect { ...useNetworkSelectProps() } />
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
