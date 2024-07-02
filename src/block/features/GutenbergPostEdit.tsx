import { usePostSetting } from './provider/postSetting/usePostSetting';

export const GutenbergPostEdit: React.FC = () => {
	const { } = useScreenPostSetting();
	return (
		<>
			<h2>GutenbergPostEdit</h2>

			{/* <div>amount: { JSON.stringify( data?.postSetting ) }</div> */}
		</>
	);
};

// 画面上で保持する設定情報
type PostSetting = {
	sellingPrice: {
        amountHex: string;
        decimals: number;
        symbol: string | undefined;
    };
};

const useScreenPostSetting = () => {

	const postSetting: PostSetting | null | undefined = usePostSetting();
	console.log("postSetting: ", postSetting);

	return {}
};
