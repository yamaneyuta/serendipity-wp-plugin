---
applyTo: "apps/wp-plugin/**/*.php"
---

## 開発方針
- PHPのソースコードは`apps/wp-plugin/includes/classes/`以下に配置します。
  - DDDの開発方針に従ってディレクトリ構成を決定してください。
- PHPのテストコードは`apps/wp-plugin/tests/classes/`以下に配置します。
  - PHPのソースコードとディレクトリ構成を合わせます。
  - テスト対象となるファイル名でまずディレクトリを作成し、その中に`HappyPathTest`,`ErrorPathTest`,`SecurityTest`のファイルを作成します。
  - テストケースは`UnitTestCaseBase`クラスを継承して作成してください
  - テストは基本的に結合テストで行います。極力mockを使用しないテストコードを記述してください。
- Resolverのテストは`tests/classes/Presentation/GraphQL/Resolver`以下のファイルを参考に作成してください。
- PHPのテストは`apps/wp-plugin`ディレクトリに移動後、`npm run test:php`で行えます。  
  特定のファイルだけテストしたい場合は`apps/wp-plugin`ディレクトリに移動後、`npm run test:php [PHPファイルの相対パス]`で行えます。
