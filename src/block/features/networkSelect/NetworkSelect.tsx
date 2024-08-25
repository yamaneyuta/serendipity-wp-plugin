import { NetworkType } from '../../../types/gql/generated';
import { BlockSelect, BlockSelectOption } from '../../components/BlockSelect';

interface NetworkSelectProps {
	value: NetworkType | null | undefined;
	networks: string[] | null | undefined;
	onChange: React.ChangeEventHandler< HTMLSelectElement >;
	disabled?: boolean;
}
export const NetworkSelect: React.FC< NetworkSelectProps > = ( { value, networks, onChange, disabled } ) => {
	return (
		<BlockSelect value={ value ?? '' } onChange={ onChange } disabled={ disabled }>
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
