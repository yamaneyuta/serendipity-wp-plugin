import { NetworkCategory } from '../../../types/NetworkCategory';
import { BlockSelect, BlockSelectOption } from '../../components/BlockSelect';

interface NetworkCategorySelectProps {
	value: NetworkCategory | null | undefined;
	networks: NetworkCategory[] | null | undefined;
	onChange: React.ChangeEventHandler< HTMLSelectElement >;
	disabled?: boolean;
}
export const NetworkCategorySelect: React.FC< NetworkCategorySelectProps > = ( {
	value,
	networks,
	onChange,
	disabled,
} ) => {
	return (
		<BlockSelect value={ value?.id() ?? '' } onChange={ onChange } disabled={ disabled }>
			{ value === null ? <BlockSelectOption>{ 'Select a network' }</BlockSelectOption> : null }
			{ value === undefined ? <BlockSelectOption>{ 'Loading...' }</BlockSelectOption> : null }
			{ networks?.map( ( network ) => (
				<BlockSelectOption key={ network.id() } value={ network.id() }>
					{ network.toString() }
				</BlockSelectOption>
			) ) }
		</BlockSelect>
	);
};
