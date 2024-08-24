import { useCallback } from 'react';
import { NetworkType } from '../../../types/gql/generated';
import { BlockSelect, BlockSelectOption } from '../../components/BlockSelect';
import { useSelectedNetwork } from '../../provider/userInput/selectedNetwork/useSelectedNetwork';

interface NetworkSelectProps {
	value: NetworkType | null | undefined;
	networks: string[] | null | undefined;
}
export const NetworkSelect: React.FC< NetworkSelectProps > = ( { value, networks } ) => {
	const handleChange = useOnChangeCallback();

	const disabled = value === undefined; // 読み込み中はコントロールを無効化

	return (
		<BlockSelect value={ value ?? '' } onChange={ handleChange } disabled={ disabled }>
			{ value === null ? <BlockSelectOption>{ 'Select a network' }</BlockSelectOption> : null }
			{ value === undefined ? <BlockSelectOption>{ 'Loading...' }</BlockSelectOption> : null }
			{ networks?.map( ( network ) => (
				<BlockSelectOption key={ network } value={ network }>
					{ network }
				</BlockSelectOption>
			) ) }
		</BlockSelect>
	);
};

const useOnChangeCallback = () => {
	const { setSelectedNetwork } = useSelectedNetwork();

	return useCallback(
		( event: React.ChangeEvent< HTMLSelectElement > ) => {
			setSelectedNetwork( event.target.value as NetworkType );
		},
		[ setSelectedNetwork ]
	);
};
