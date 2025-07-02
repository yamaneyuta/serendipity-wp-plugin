module.exports = {
  extends: ['@commitlint/config-conventional'],
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
