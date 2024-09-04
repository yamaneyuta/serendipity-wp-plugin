import { SelectedNetworkProvider } from './selectedNetwork/SelectedNetworkProvider';
import { InputPriceValueProvider } from './inputPriceValue/InputPriceValueProvider';
import { SelectedPriceSymbolProvider } from './selectedPriceSymbol/SelectedPriceSymbolProvider';
import { WidgetAttributes } from '../../types/WidgetAttributes';
import { WidgetAttributesProvider } from './widgetAttributes/WidgetAttributesProvider';

type WidgetStateProviderProps = {
	attributes: Readonly< WidgetAttributes >;
	setAttributes: ( attrs: Partial< WidgetAttributes > ) => void;
	children: React.ReactNode;
};

export const WidgetStateProvider: React.FC< WidgetStateProviderProps > = ( {
	attributes,
	setAttributes,
	children,
} ) => {
	return (
		<WidgetAttributesProvider attributes={ attributes } setAttributes={ setAttributes }>
			<SelectedNetworkProvider>
				<InputPriceValueProvider>
					<SelectedPriceSymbolProvider>{ children }</SelectedPriceSymbolProvider>
				</InputPriceValueProvider>
			</SelectedNetworkProvider>
		</WidgetAttributesProvider>
	);
};
