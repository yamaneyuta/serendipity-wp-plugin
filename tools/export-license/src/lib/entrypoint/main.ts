import { copyLicenseFiles } from '../copyLicenseFiles';
import { exportLicenseMeta } from '../exportLicenseMeta';
import { getPackageManagerType, PackageManagerType } from '../getPackageManagerType';
import { getPackagesComposer } from '../getPackagesComposer';
import { getPackagesNpm } from '../getPackagesNpm';
import { parseCommand } from '../parseCommand';
import { verifyMetaFile } from '../verify/verifyMetaFile';

export const main = async () => {
	// コマンドラインから引数を取得
	const { start, output, metaFile } = parseCommand();

	// 起点となるディレクトリから、パッケージマネージャーの種類を判定
	const packageManagerType = getPackageManagerType( start );

	// パッケージ情報を取得
	const packages = await getPackagesInfo( start, packageManagerType );

	// ライセンスファイルをコピー
	const copiedFiles = await copyLicenseFiles( packages, start, output );

	// ライセンス情報を出力
	await exportLicenseMeta( packages, start, output, metaFile );

	// 出力したライセンス情報を検証
	verifyMetaFile( metaFile );

	return {
		copiedFiles,
	};
};

const getPackagesInfo = async ( start: string, packageManagerType: PackageManagerType ) => {
	if ( packageManagerType.isNpm ) {
		return getPackagesNpm( {
			start,
			excludePrivatePackages: true, // プライベートパッケージは除外
			production: true, // devDependencies は除外
		} );
	} else if ( packageManagerType.isComposer ) {
		return getPackagesComposer( start );
	}
	throw new Error( `[E45C292D] Invalid argument. start: ${ start }` );
};
