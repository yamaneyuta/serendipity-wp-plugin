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
			// config: {
			// 	fetcher: './fetcher.ts',
			// isReactHook: true,
			// exposeQueryKeys: true,
			// },
		}
	},
}

export default config
