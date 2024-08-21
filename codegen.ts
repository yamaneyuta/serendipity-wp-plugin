import { CodegenConfig } from '@graphql-codegen/cli'

const config: CodegenConfig = {
	schema: './includes/assets/graphql/schema.graphql',
	documents: [ '**/block/*graphql' ],
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
				fetcher: '../../../node_modules/@yamaneyuta/serendipity-lib-frontend#fetcher', // 生成されるファイルからのパス
				// fetcher: '@yamaneyuta/serendipity-lib-frontend#fetcher', // 相対パスの場合は、生成されるファイルからのパス
				// isReactHook: true,
				// exposeQueryKeys: true,
			},
		}
	},
}

export default config
