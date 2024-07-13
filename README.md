# serendipity-wp-plugin
[![CI](https://github.com/yamaneyuta/serendipity-wp-plugin/actions/workflows/ci.yml/badge.svg)](https://github.com/yamaneyuta/serendipity-wp-plugin/actions/workflows/ci.yml)

## 開発
[Dev Containers](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)による開発環境を提供しています。
起動後は、[wp-env](https://ja.wordpress.org/team/handbook/block-editor/reference-guides/packages/packages-env/)によって開発用及びテスト用のWordPress(以下WP)環境が構築されます。


### 開発環境(URL)
通常の開発用の環境です。
| 項目                | URL                              | 説明                                             |
|:--------------------|:---------------------------------| :----------------------------------------------- |
| 開発環境URL         | http://localhost:8888            |                                                  |
| 開発環境管理画面    | http://localhost:8888/wp-admin/  |                                                  |
| phpMyAdmin		  | http://phpmyadmin.local/         |                                                  |
| 開発用 RPC URL 1    | http://privatenet-1.local/       | Oracleがデプロイされているネットワーク(L1相当)   |
| 開発用 RPC URL 2    | http://privatenet-2.local/       | Oracleがデプロイされていないネットワーク(L2相当) |

※ `.local`で終わるドメインは`hosts`ファイルにマッピングを追加してください。
```text
127.0.0.1       phpmyadmin.local
127.0.0.1       privatenet-1.local
127.0.0.1       privatenet-2.local
```


### テスト環境(URL)
テストを実施するための環境です。
| 項目                | URL                             |
|:--------------------|:--------------------------------|
| WPトップページ      | http://localhost:8889           |
| WP管理画面          | http://localhost:8889/wp-admin/ |

### 各種設定値
開発用環境及びテスト用環境で設定されている値の一覧です。

| 項目         | 値                                                     | 開発 | テスト |
|:-------------|:-------------------------------------------------------|:----:|:------:|
| WPユーザー名 | admin                                                  |  ✓  |   ✓   |
| WPパスワード | password                                               |  ✓  |   ✓   |
| WPバージョン | [.wp-env.override.json](./.wp-env.override.json)で指定 |  ✓  |   ✓   |
| PHPバージョン| [.wp-env.override.json](./.wp-env.override.json)で指定 |  ✓  |   ✓   |

