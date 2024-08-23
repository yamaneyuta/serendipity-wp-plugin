import { SelectedNetworkProvider } from './selectedNetwork/SelectedNetworkProvider';
import { InputPriceAmountProvider } from './inputPriceAmount/InputPriceAmountProvider';

type UserInputProviderProps = {
	children: React.ReactNode;
};

export const UserInputProvider: React.FC< UserInputProviderProps > = ( { children } ) => {
	return (
		<SelectedNetworkProvider>
			<InputPriceAmountProvider>{ children }</InputPriceAmountProvider>
		</SelectedNetworkProvider>
	);
};
