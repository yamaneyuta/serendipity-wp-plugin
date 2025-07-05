module.exports = {
  extends: ['@commitlint/config-conventional'],
  ignores: [
    // fixup! と squash! で始まるコミットメッセージを無視
    (message) => message.startsWith('fixup!'),
    (message) => message.startsWith('squash!')
  ],
  rules: {
    'scope-enum': [
      2,
      'always',
      [
        // アプリケーション/パッケージ
        'wp-plugin',
        'config',
        'docs',
        'scripts',
        // tools
        'export-license',
        'php-asset-gen',
        // ルートレベル
        'root',
        // インフラ
        'block-explorer',
        'privatenet',
        // 横断的
        'deps',
        'ci'
      ]
    ],
    'scope-empty': [2, 'never'],
    'subject-case': [0, 'never'],
    'type-enum': [
      2,
      'always',
      [
        'feat',
        'fix',
        'docs',
        'style',
        'refactor',
        'perf',
        'test',
        'build',
        'ci',
        'chore',
        'revert'
      ]
    ]
  }
};
