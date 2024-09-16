<?php

namespace Cornix\Serendipity\Core\Lib\Repository;

class HandleName {

	public function blockScript(): string {
		// 『src/block/index.js』(文字列)のMD5ハッシュ値。
		return '6e7ba80738b3f81da8c4f83d13e6a344';
	}

	public function adminScript(): string {
		// 『public/admin/index.js』(文字列)のMD5ハッシュ値
		return '4c452b4ecb0e32a9563a7a76a9d5ee2c';
	}

	public function viewScript(): string {
		// 『public/view/index.js』(文字列)のMD5ハッシュ値
		return '7f21752c82485b2bc9afb940ba2a6794';
	}
}
