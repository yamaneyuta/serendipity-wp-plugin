import { SelectedNetworkProvider } from './selectedNetwork/SelectedNetworkProvider';

type UserInputProviderProps = {
	children: React.ReactNode;
};

export const UserInputProvider: React.FC< UserInputProviderProps > = ( { children } ) => {
	return <SelectedNetworkProvider>{ children }</SelectedNetworkProvider>;
};
