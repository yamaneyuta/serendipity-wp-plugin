# プロジェクト規約
これはプロジェクトの規約です。**常にこの規約に従ってください。**

## コーディングルール

### PHP
- PHPのコードは`includes/classes/`以下に配置します。
  - DDDの開発方針に従ってディレクトリ構成を決定してください。
- PHPのコードは基本的にWordPressのコーディング規約に従ってください。
	- [PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
	  - 配列は`[]`ではなく`array()`を使用します。
	- PHPのコードを整形するコマンドは`npm run format:php`です。ファイルに変更を加えた後はこのコマンドを実行してください。

## テストルール
- PHPのテストファイルは`tests/classes/`以下に配置します。
  テスト対象となるファイル名でまずディレクトリを作成し、その中に`HappyPathTest`,`ErrorPathTest`,`SecurityTest`のファイルを作成します。
- テストケースは以下のいずれかのクラスを継承して作成してください
  - `UnitTestCaseBase`
	- `BlockchainTestCaseBase`
- テストファイルのnamespaceは他のテストファイルを参考にしてください。
- テストは基本的に結合テストで行います。極力mockを使用しないテストコードを記述してください。
- Resolverのテストは`tests/classes/Presentation/GraphQL/Resolver`以下のファイルを参考に作成してください。
- PHPのテスト実行コマンドは`npm run test:php`です。個別に実行したい場合は`npm run test:php [PHPファイルの相対パス]`を実行してください。

