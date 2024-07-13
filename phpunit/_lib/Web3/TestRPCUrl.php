<?php
declare(strict_types=1);

class TestRPCUrl {
	public function privatenetL1(): string {
		// メインネット相当のテスト用RPC URL
		return 'http://tests-privatenet-1.local';   // (compose.ymlに記載のhostname)
	}

	public function privatenetL2(): string {
		// Polygonネットワーク相当のテスト用RPC URL
		return 'http://tests-privatenet-2.local';   // (compose.ymlに記載のhostname)
	}
}
