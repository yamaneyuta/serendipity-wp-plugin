# @serendipity/config

このパッケージは、プロジェクト全体で使用される設定ファイルを一元管理するためのものです。

## 目的

モノレポ内の各パッケージ（アプリケーションやライブラリ）で共通の設定を共有することにより、コーディングスタイルや品質基準を統一し、開発効率を向上させることを目的としています。

## 提供される設定

このパッケージは、以下の設定ファイルを提供します。

### ESLint

- **パス:** `@serendipity/config/eslint`
- **説明:** `@wordpress/eslint-plugin` をベースとした、プロジェクト共通のESLint設定です。コードの静的解析と品質チェックに使用します。

### Prettier

- **パス:** `@serendipity/config/prettier`
- **説明:** `@wordpress/prettier-config` をベースとした、コードフォーマッターの設定です。一貫したコードスタイルを維持します。

### Jest

- **パス:** `@serendipity/config/jest-config-react`
- **説明:** Reactコンポーネントのテストを行うためのJest設定です。`ts-jest` と `jsdom` を使用するように構成されています。

### TypeScript

- **`tsconfig.base.json`:** プロジェクト全体で共有される基本的なTypeScriptのコンパイラオプションです。
- **`tsconfig.react.json`:** Reactプロジェクト（`.tsx`ファイルなど）向けに `tsconfig.base.json` を拡張した設定です。

## 使用方法

各パッケージの `package.json` で `devDependencies` に `@serendipity/config` を `workspace:*` を使って追加します。

```json
"devDependencies": {
  "@serendipity/config": "workspace:*"
}
```

その後、各設定ファイルでこのパッケージ内の設定を `require` または `extends` して使用します。

**例: `.eslintrc.js`**
```javascript
module.exports = require('@serendipity/config/eslint');
```

**例: `tsconfig.json`**
```json
{
  "extends": "@serendipity/config/tsconfig-react",
  "compilerOptions": {
    // ... package specific options
  }
}
```
