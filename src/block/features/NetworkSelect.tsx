import { NetworkType } from '../../types/gql/generated';
import { BlockSelect, BlockSelectOption } from '../components/BlockSelect';

interface NetworkSelectProps {
	value: string | null | undefined;
	networks: string[] | null | undefined;
	onChange: ( network: NetworkType ) => void;
}
export const NetworkSelect: React.FC< NetworkSelectProps > = ( { value, networks, onChange } ) => {
	const handleChange = ( event: React.ChangeEvent< HTMLSelectElement > ) => {
		onChange( event.target.value as NetworkType );
	};

	value = value ?? '';

	return (
		<BlockSelect value={ value } onChange={ handleChange }>
			{ value === '' ? <BlockSelectOption>{ 'Select a network' }</BlockSelectOption> : null }
			{ networks?.map( ( network ) => (
				<BlockSelectOption key={ network } value={ network }>
					{ network }
				</BlockSelectOption>
			) ) }
		</BlockSelect>
	);
};
