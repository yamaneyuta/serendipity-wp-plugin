import { usePostSetting } from './postSetting/usePostSetting';

/**
 * サーバーに記録されている販売ネットワークを取得します。
 */
export const useSellingNetwork = () => {
	return usePostSetting()?.sellingNetwork;
};
