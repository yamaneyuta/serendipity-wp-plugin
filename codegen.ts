import { CodegenConfig } from '@graphql-codegen/cli'

const config: CodegenConfig = {
	schema: './includes/assets/graphql/schema.graphql',
	documents: [ './includes/assets/graphql/block/*.graphql' ],
	ignoreNoDocuments: true, // for better experience with the watcher
	generates: {
		'./src/types/gql/generated.ts': { // `preset: 'client'`を使用しない場合はファイル名を指定
			// preset: 'client',
			plugins: [
				'typescript',
				'typescript-operations',
				'typescript-react-query',
			],
			config: {
				// ※ jestで`Cannot find module`が発生するため、相対パスで記述している
				fetcher: '@yamaneyuta/serendipity-lib-frontend#fetcher', // 相対パスの場合は、生成されるファイルからのパス
				// isReactHook: true,
				// exposeQueryKeys: true,

				/*
				// @tanstack/react-query@5に対応する場合は、以下の設定を有効にする
				// ※ @tanstack/react-query@5.56.2 ではReact18以上("^18 || ^19")が必要
				//    対して、WordPress組み込みのReact@18になるのは WordPress@6.2以降
				//    https://make.wordpress.org/core/2023/03/07/upgrading-to-react-18-and-common-pitfalls-of-concurrent-mode/
				//    WordPress@5.4(2024/9/22時点)で開発しているため、@tanstack/react-query@5は導入していない

				// https://github.com/dotansimha/graphql-code-generator/issues/9786#issuecomment-1938602063
				addSuspeneQuery: true,
				reactQueryVersion: 5,
				*/
			},
		}
	},
}

export default config
