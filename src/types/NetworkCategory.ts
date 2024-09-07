import assert from 'assert';

/**
 * ネットワークカテゴリを表すクラス
 */
export class NetworkCategory {
	private static readonly NETWORK_CATEGORY_ID_MAINNET = 1; // メインネット(Ethereumメインネット、Polygonメインネット等)
	private static readonly NETWORK_CATEGORY_ID_TESTNET = 2; // テストネット(Ethereum Sepolia等)
	private static readonly NETWORK_CATEGORY_ID_PRIVATENET = 3; // プライベートネット(Ganache、Hardhat等)

	private constructor( networkCategoryID: number ) {
		this.networkCategoryID = networkCategoryID;
	}

	/** ネットワークカテゴリID(数値) */
	private networkCategoryID: number;

	public id(): number {
		return this.networkCategoryID;
	}

	private static cache: Map< number, NetworkCategory > = new Map();

	public static from( networkCategoryID: number ): NetworkCategory {
		// キャッシュにない場合は新規作成してキャッシュに登録
		if ( ! NetworkCategory.cache.has( networkCategoryID ) ) {
			NetworkCategory.checkNetworkCategoryID( networkCategoryID ); // ネットワークカテゴリIDが不正な場合は例外を投げる
			NetworkCategory.cache.set( networkCategoryID, new NetworkCategory( networkCategoryID ) );
		}

		const networkCategory = NetworkCategory.cache.get( networkCategoryID );
		assert( networkCategory, `[A40CE190] networkCategoryID is invalid. networkCategoryID: ${ networkCategoryID }` );
		return networkCategory;
	}

	/**
	 * ネットワークカテゴリIDが不正な値の場合に例外をスローします。
	 */
	private static checkNetworkCategoryID( networkCategoryID: number ): void {
		if (
			! [
				NetworkCategory.NETWORK_CATEGORY_ID_MAINNET,
				NetworkCategory.NETWORK_CATEGORY_ID_TESTNET,
				NetworkCategory.NETWORK_CATEGORY_ID_PRIVATENET,
			].includes( networkCategoryID )
		) {
			throw new Error( `[B1485F06] networkCategoryID is invalid. networkCategoryID: ${ networkCategoryID }` );
		}
	}

	/**
	 * メインネットを表すネットワークカテゴリインスタンスを取得します。
	 */
	public static mainnet(): NetworkCategory {
		return NetworkCategory.from( NetworkCategory.NETWORK_CATEGORY_ID_MAINNET );
	}

	/**
	 * テストネットを表すネットワークカテゴリインスタンスを取得します。
	 */
	public static testnet(): NetworkCategory {
		return NetworkCategory.from( NetworkCategory.NETWORK_CATEGORY_ID_TESTNET );
	}

	/**
	 * プライベートネットを表すネットワークカテゴリインスタンスを取得します。
	 */
	public static privatenet(): NetworkCategory {
		return NetworkCategory.from( NetworkCategory.NETWORK_CATEGORY_ID_PRIVATENET );
	}

	public toString(): string {
		switch ( this.networkCategoryID ) {
			case NetworkCategory.NETWORK_CATEGORY_ID_MAINNET:
				return 'Mainnet';
			case NetworkCategory.NETWORK_CATEGORY_ID_TESTNET:
				return 'Testnet';
			case NetworkCategory.NETWORK_CATEGORY_ID_PRIVATENET:
				return 'Privatenet';
			default:
				throw new Error(
					`[94693A55] networkCategoryID is invalid. networkCategoryID: ${ this.networkCategoryID }`
				);
		}
	}
}
