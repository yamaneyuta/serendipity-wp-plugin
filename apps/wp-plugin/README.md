# serendipity-wp-plugin
![Static Badge](https://img.shields.io/badge/License-Split_License-97ca00) [![CI](https://github.com/yamaneyuta/serendipity-wp-plugin/actions/workflows/ci.yml/badge.svg)](https://github.com/yamaneyuta/serendipity-wp-plugin/actions/workflows/ci.yml)



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

| 項目         | 値                                                                         | 開発 | テスト |
|:-------------|:---------------------------------------------------------------------------|:----:|:------:|
| WPユーザー名 | admin                                                                      |  ✓  |   ✓   |
| WPパスワード | password                                                                   |  ✓  |   ✓   |
| WPバージョン | [compose.yml](./.devcontainer/compose.yml)で指定 |  ✓  |   ✓   |
| PHPバージョン| [compose.yml](./.devcontainer/compose.yml)で指定 |  ✓  |   ✓   |


### 開発用ブロックチェーン環境

#### チェーンID
| ネットワーク         | チェーンID   |
|:---------------------|:-------------|
| プライベートネット1  | 31337        |
| プライベートネット2  | 1337         |
#### コントラクト

##### App
| Network             | Contract address                           | Owner address                              |
|:--------------------|:-------------------------------------------|:-------------------------------------------|
| プライベートネット1 | 0x5FbDB2315678afecb367f032d93F642f64180aa3 | 0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266 |
| プライベートネット2 | 0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512 | 0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266 |

##### ERC20
| Network             | Symbol | Contract address                           | Deployer                                   |
|:--------------------|:-------|:-------------------------------------------|:-------------------------------------------|
| プライベートネット1 | TJPY   | 0x22a762Ba5e1BB196C89feC59a4438D515a13b8f9 | 0x6dAf30933bFbc075276802DDa42E9e28887956D9 |
| プライベートネット1 | TUSD   | 0x429035C67ACEA53E5Ae8d18e39294eF7Dadd52BF | 0x5B691E0971AD6aE7457eA13d0ADc9b1be44da56C |
| プライベートネット1 | TLINK  | 0x13960969dCa9c5f4C12D4AE2CFABCeE415a5247C | 0x0b94110c40e58307dEeA72F068bC76FcE449e745 |
| プライベートネット2 | TJPY   | 0x850d911A7baEe310281Bd914b73613734803b7aF | 0x3F2a483B561B5ad00Fb9DF808186A746D8CaC8FC |
| プライベートネット2 | TUSD   | 0x731A82e658305cE90316A7376092F54473b56681 | 0xe7e07C1209deaAD2653F21b32A3b9030605d8B90 |
| プライベートネット2 | TLINK  | 0xDE3C8E5E979A2b95DD59cF5444b702ddd6681B2f | 0x0EBBA38ADe5334289B741Da17DD86b484FcDe438 |

##### Oracle
Oracleは、プライベートネット1にのみデプロイされています。

| Network             | Symbol    | Contract address                           | Deployer                                   |
|:--------------------|:----------|:-------------------------------------------|:-------------------------------------------|
| プライベートネット1 | ETH/USD   | 0x3F3B6a555F3a7DeD78241C787e0cDD8E431A64A8 | 0x1E59ACC87b18DF0FBF793e3De9f7eA2bf7b82Ecd |
| プライベートネット1 | JPY/USD   | 0xc886d2C1BEC5819b4B8F84f35A9885519869A8EE | 0x91dA746760691610AA275cc33D0B612bF01AC5E0 |
| プライベートネット1 | LINK/USD  | 0xa13Fc5DadBBE59fb81aa2B4815970aA50caf1C7b | 0xFE68f31206fB5d9079F9D57d66574d8f074B4b87 |
