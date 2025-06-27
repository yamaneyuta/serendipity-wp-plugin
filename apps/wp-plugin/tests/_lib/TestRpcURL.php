<?php
declare(strict_types=1);

/**  */
class TestRpcUrl {
	// ※ 2025/3/17以降、AnkrのRPC URLはサインインが必要になるためコメントアウト
	// ```
	// Effective March 17, to interact with the following chains, you will need to sign in on the Web3 API platform.
	// This change will help us ensure a higher level of security, prevent abuse, and improve the overall quality of service.
	// This measure has been taken to maintain a reliable platform for all users, ensuring fair and secure usage of our API endpoints.
	// ```
	// https://www.ankr.com/docs/whats-new/#free-apis-via-sign-in
	//
	// public const ETH_MAINNET = 'https://rpc.ankr.com/eth';
	//
	// PublicNodeのURLを使用
	public const ETH_MAINNET = 'https://ethereum-rpc.publicnode.com';
}
